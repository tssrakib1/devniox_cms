<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Encryption\Encrypter;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class InstallationService
{
    public function __construct(
        private readonly UserManagementService $users,
        private readonly Filesystem $files,
    ) {}

    public function isInstalled(): bool
    {
        if (is_file(config('installer.lock_path'))) {
            return true;
        }

        try {
            return Schema::hasTable('users') && DB::table('users')->exists();
        } catch (Throwable) {
            return false;
        }
    }

    public function requirements(): array
    {
        $items = [
            ['label' => 'PHP '.config('installer.minimum_php').' or newer', 'passed' => version_compare(PHP_VERSION, config('installer.minimum_php'), '>=')],
        ];
        foreach (config('installer.extensions') as $extension) {
            $items[] = ['label' => 'PHP extension: '.$extension, 'passed' => extension_loaded($extension)];
        }
        if (config('installer.image_driver_required')) {
            $items[] = ['label' => 'Image processing: GD or Imagick', 'passed' => extension_loaded('gd') || extension_loaded('imagick')];
        }
        foreach ([
            storage_path() => 'Storage directory writable',
            base_path('bootstrap/cache') => 'Bootstrap cache writable',
            public_path() => 'Public directory writable',
        ] as $path => $label) {
            $items[] = ['label' => $label, 'passed' => is_dir($path) && is_writable($path)];
        }

        return $items;
    }

    public function requirementsPass(): bool
    {
        return collect($this->requirements())->every('passed');
    }

    public function testDatabase(array $database, bool $requireEmpty = true): void
    {
        $this->configureDatabase($database);
        DB::connection()->getPdo();
        if ($requireEmpty && $this->databaseTables() !== []) {
            throw new RuntimeException('The selected database is not empty. Choose a new empty database to protect existing data.');
        }
    }

    public function install(array $state, callable $progress): array
    {
        if ($this->isInstalled()) {
            throw new RuntimeException('This application is already installed.');
        }
        if (! $this->requirementsPass()) {
            throw new RuntimeException('One or more server requirements are no longer satisfied.');
        }

        $environmentPath = config('installer.environment_path');
        $lockPath = config('installer.lock_path');
        $storageLink = config('installer.storage_link');
        $databaseConnection = config('installer.database_connection');
        $environmentBackup = is_file($environmentPath) ? file_get_contents($environmentPath) : null;
        $migrationsStarted = false;
        $linkCreated = false;
        $mediaBefore = $this->publicMediaFiles();

        try {
            $progress('database', 'Verifying the empty database');
            $this->testDatabase($state['database']);

            $progress('environment', 'Writing secure application configuration');
            $key = 'base64:'.base64_encode(Encrypter::generateKey(config('app.cipher')));
            $this->writeEnvironment($environmentPath, $state, $key, $databaseConnection);
            $this->configureRuntime($state, $key, $databaseConnection);
            config(['cache.default' => 'array', 'queue.default' => 'sync', 'session.driver' => 'file']);
            Artisan::call('optimize:clear');

            $progress('migrations', 'Creating database tables');
            $migrationsStarted = true;
            $this->runArtisan('migrate', ['--force' => true]);

            $progress('foundation', 'Installing foundation data and administrator');
            DB::transaction(function () use ($state) {
                app(DatabaseSeeder::class)->seedFoundation();
                $administrator = $this->users->createAdministrator([
                    'name' => $state['administrator']['name'],
                    'email' => $state['administrator']['email'],
                    'password' => $state['administrator']['password'],
                ]);
                $role = Role::where('slug', 'administrator')->firstOrFail();
                $role->permissions()->sync(Permission::pluck('id'));
                Setting::where(['group' => 'company', 'key' => 'name'])->update(['value' => $state['application']['name']]);
                Setting::where(['group' => 'general', 'key' => 'website_name'])->update(['value' => $state['application']['name']]);
                Setting::where(['group' => 'general', 'key' => 'timezone'])->update(['value' => $state['application']['timezone']]);
                Setting::where(['group' => 'general', 'key' => 'currency'])->update(['value' => $state['application']['currency']]);
                ActivityLogService::log('system', 'installed', 'Application installation completed.', $administrator, null, ['version' => config('app.version')], $administrator->id);
            });

            if ($state['demo']['install_demo'] ?? false) {
                $progress('demo', 'Installing official DevNiox demo content');
                app(DemoDataSeeder::class)->run();
            }

            $progress('storage', 'Creating the public storage link');
            $linkCreated = $this->createStorageLink();

            $progress('optimization', 'Building production caches');
            $this->configureProductionDrivers();
            $this->runArtisan('config:cache');
            $this->runArtisan('route:cache');
            $this->runArtisan('view:cache');
            $this->configureRuntime($state, $key, $databaseConnection);
            $this->configureProductionDrivers();

            $progress('lock', 'Securing the installer');
            $this->writeLock($lockPath, $state['administrator']['email']);

            return [
                'email' => $state['administrator']['email'],
                'login_url' => route('login'),
                'website_url' => route('home'),
            ];
        } catch (Throwable $exception) {
            $this->removeLock($lockPath);
            if ($migrationsStarted) {
                try {
                    Artisan::call('migrate:reset', ['--force' => true]);
                } catch (Throwable) {
                    // Preserve the original actionable exception.
                }
            }
            $this->removeNewMedia($mediaBefore);
            if ($linkCreated) {
                $this->removeStorageLink($storageLink);
            }
            $this->restoreEnvironment($environmentPath, $environmentBackup);
            try {
                Artisan::call('optimize:clear');
            } catch (Throwable) {
                // Preserve the original actionable exception.
            }

            throw new RuntimeException('Installation failed: '.$exception->getMessage(), 0, $exception);
        }
    }

    private function configureDatabase(array $database, ?string $connection = null): void
    {
        $connection ??= config('installer.database_connection');
        config([
            'database.default' => $connection,
            "database.connections.{$connection}.host" => $database['host'],
            "database.connections.{$connection}.port" => $database['port'],
            "database.connections.{$connection}.database" => $database['database'],
            "database.connections.{$connection}.username" => $database['username'],
            "database.connections.{$connection}.password" => $database['password'] ?? '',
        ]);
        DB::purge($connection);
        DB::setDefaultConnection($connection);
    }

    private function databaseTables(): array
    {
        $connection = DB::connection();
        if ($connection->getDriverName() === 'mysql') {
            return $connection->table('information_schema.tables')
                ->where('table_schema', $connection->getDatabaseName())
                ->where('table_type', 'BASE TABLE')
                ->pluck('table_name')
                ->all();
        }

        return Schema::getTableListing();
    }

    private function configureRuntime(array $state, string $key, string $databaseConnection): void
    {
        $this->configureDatabase($state['database'], $databaseConnection);
        config([
            'app.name' => $state['application']['name'],
            'app.url' => $state['application']['url'],
            'app.env' => $state['application']['environment'],
            'app.debug' => false,
            'app.key' => $key,
            'app.timezone' => $state['application']['timezone'],
        ]);
    }

    private function configureProductionDrivers(): void
    {
        config([
            'cache.default' => 'database',
            'queue.default' => 'database',
            'session.driver' => 'database',
        ]);
    }

    private function writeEnvironment(string $path, array $state, string $key, string $databaseConnection): void
    {
        $content = is_file($path) ? (string) file_get_contents($path) : (string) file_get_contents(base_path('.env.example'));
        $values = [
            'APP_NAME' => $state['application']['name'],
            'APP_ENV' => $state['application']['environment'],
            'APP_KEY' => $key,
            'APP_DEBUG' => 'false',
            'APP_URL' => $state['application']['url'],
            'DB_CONNECTION' => $databaseConnection,
            'DB_HOST' => $state['database']['host'],
            'DB_PORT' => (string) $state['database']['port'],
            'DB_DATABASE' => $state['database']['database'],
            'DB_USERNAME' => $state['database']['username'],
            'DB_PASSWORD' => $state['database']['password'] ?? '',
            'ADMIN_EMAIL' => '',
            'ADMIN_PASSWORD' => '',
            'SESSION_SECURE_COOKIE' => str_starts_with($state['application']['url'], 'https://') ? 'true' : 'false',
        ];
        foreach ($values as $name => $value) {
            $line = $name.'='.$this->environmentValue((string) $value);
            $pattern = '/^'.preg_quote($name, '/').'=.*$/m';
            $content = preg_match($pattern, $content) ? preg_replace($pattern, $line, $content) : rtrim($content)."\n".$line."\n";
        }
        try {
            $this->files->replace($path, $content);
        } catch (Throwable) {
            throw new RuntimeException('The environment file could not be written. Check root-directory permissions.');
        }
    }

    private function environmentValue(string $value): string
    {
        if ($value === '' || preg_match('/[\s#="'."'".']/', $value)) {
            return '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $value).'"';
        }

        return $value;
    }

    private function createStorageLink(): bool
    {
        $link = config('installer.storage_link');
        $target = config('installer.storage_target');

        File::ensureDirectoryExists($target);

        if ($this->storageLinkWorks($link, $target)) {
            return false;
        }

        if (file_exists($link)) {
            throw new RuntimeException('The public storage path exists but does not expose storage/app/public. Remove or fix public/storage before installing.');
        }

        $this->runArtisan('storage:link');

        if (! $this->storageLinkWorks($link, $target)) {
            throw new RuntimeException('The public storage link was created but failed verification. Check public-directory permissions and symlink/junction support.');
        }

        return true;
    }

    private function storageLinkWorks(string $link, string $target): bool
    {
        if (! file_exists($link) || ! is_dir($target)) {
            return false;
        }

        $probe = '.installer-storage-probe-'.bin2hex(random_bytes(6));
        $targetProbe = $target.DIRECTORY_SEPARATOR.$probe;
        $linkProbe = $link.DIRECTORY_SEPARATOR.$probe;

        try {
            File::put($targetProbe, 'ok');

            return is_file($linkProbe) && trim((string) File::get($linkProbe)) === 'ok';
        } finally {
            if (is_file($targetProbe)) {
                File::delete($targetProbe);
            }
        }
    }

    private function removeStorageLink(string $link): void
    {
        if (is_link($link)) {
            File::delete($link);

            return;
        }

        if (is_dir($link)) {
            @rmdir($link);
        }
    }

    private function writeLock(string $path, string $email): void
    {
        File::ensureDirectoryExists(dirname($path));
        $payload = json_encode(['installed_at' => now()->toIso8601String(), 'version' => config('app.version'), 'administrator' => $email], JSON_PRETTY_PRINT);
        if (file_put_contents($path, $payload, LOCK_EX) === false) {
            throw new RuntimeException('The installation lock could not be written.');
        }
        @chmod($path, 0640);
    }

    private function removeLock(string $path): void
    {
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function restoreEnvironment(string $path, string|false|null $backup): void
    {
        if (is_string($backup)) {
            file_put_contents($path, $backup, LOCK_EX);
        } elseif (is_file($path)) {
            @unlink($path);
        }
    }

    private function runArtisan(string $command, array $arguments = []): void
    {
        $exit = Artisan::call($command, $arguments + ['--no-interaction' => true]);
        if ($exit !== 0) {
            throw new RuntimeException(trim(Artisan::output()) ?: "The {$command} command failed.");
        }
    }

    private function publicMediaFiles(): array
    {
        return collect(File::allFiles(storage_path('app/public')))->map->getPathname()->all();
    }

    private function removeNewMedia(array $before): void
    {
        foreach (array_diff($this->publicMediaFiles(), $before) as $path) {
            File::delete($path);
        }
    }
}
