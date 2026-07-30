<?php

namespace App\Http\Controllers;

use App\Http\Requests\Installer\AdministratorRequest;
use App\Http\Requests\Installer\ApplicationConfigurationRequest;
use App\Http\Requests\Installer\DatabaseConfigurationRequest;
use App\Services\InstallationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class InstallerController extends Controller
{
    public function __construct(private readonly InstallationService $installer) {}

    public function welcome()
    {
        return view('installer.welcome');
    }

    public function requirements()
    {
        $requirements = $this->installer->requirements();

        return view('installer.requirements', ['requirements' => $requirements, 'passed' => collect($requirements)->every('passed')]);
    }

    public function acceptRequirements(Request $request)
    {
        if (! $this->installer->requirementsPass()) {
            return back()->withErrors(['requirements' => 'Resolve every failed requirement before continuing.']);
        }
        $request->session()->put('installer.requirements', true);

        return redirect()->route('install.database');
    }

    public function database(Request $request)
    {
        if ($redirect = $this->missingState($request, 'requirements', 'install.requirements')) {
            return $redirect;
        }

        return view('installer.database', ['database' => $request->session()->get('installer.database', [
            'host' => '127.0.0.1', 'port' => 3306, 'database' => '', 'username' => '', 'password' => '',
        ])]);
    }

    public function storeDatabase(DatabaseConfigurationRequest $request)
    {
        $data = $request->validated();
        try {
            $this->installer->testDatabase($data);
        } catch (Throwable $exception) {
            throw ValidationException::withMessages(['database' => 'Connection failed: '.$exception->getMessage()]);
        }
        $request->session()->put('installer.database', $data);

        return redirect()->route('install.application')->with('status', 'Database connection verified.');
    }

    public function application(Request $request)
    {
        if ($redirect = $this->missingState($request, 'database', 'install.database')) {
            return $redirect;
        }

        return view('installer.application', ['application' => $request->session()->get('installer.application', [
            'name' => config('app.name', 'DevNiox'),
            'url' => $request->root(),
            'timezone' => 'Asia/Dhaka',
            'currency' => 'USD',
            'environment' => 'production',
        ])]);
    }

    public function storeApplication(ApplicationConfigurationRequest $request)
    {
        $request->session()->put('installer.application', $request->validated());

        return redirect()->route('install.administrator');
    }

    public function administrator(Request $request)
    {
        if ($redirect = $this->missingState($request, 'application', 'install.application')) {
            return $redirect;
        }

        return view('installer.administrator', ['administrator' => $request->session()->get('installer.administrator', [])]);
    }

    public function storeAdministrator(AdministratorRequest $request)
    {
        $request->session()->put('installer.administrator', $request->validated());

        return redirect()->route('install.demo');
    }

    public function demo(Request $request)
    {
        if ($redirect = $this->missingState($request, 'administrator', 'install.administrator')) {
            return $redirect;
        }

        return view('installer.demo', ['installDemo' => $request->session()->get('installer.demo.install_demo', false)]);
    }

    public function storeDemo(Request $request)
    {
        $data = $request->validate(['install_demo' => ['nullable', 'boolean']]);
        $request->session()->put('installer.demo', ['install_demo' => (bool) ($data['install_demo'] ?? false)]);

        return redirect()->route('install.install');
    }

    public function install(Request $request)
    {
        if ($redirect = $this->missingState($request, 'demo', 'install.demo')) {
            return $redirect;
        }

        return view('installer.install', ['summary' => $request->session()->get('installer')]);
    }

    public function run(Request $request): StreamedResponse
    {
        foreach (['requirements', 'database', 'application', 'administrator', 'demo'] as $step) {
            if (! $request->session()->has('installer.'.$step)) {
                abort(422, "Installer step '{$step}' is incomplete.");
            }
        }
        $state = $request->session()->pull('installer');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->stream(function () use ($state) {
            $send = function (array $message): void {
                echo json_encode($message, JSON_UNESCAPED_SLASHES)."\n";
                if (ob_get_level() > 0) {
                    @ob_flush();
                }
                flush();
            };
            try {
                $result = $this->installer->install($state, fn (string $step, string $message) => $send(['type' => 'progress', 'step' => $step, 'message' => $message]));
                $send(['type' => 'complete'] + $result);
            } catch (Throwable $exception) {
                Log::error('Installation failed.', ['exception' => $exception]);
                $send(['type' => 'error', 'message' => $exception->getMessage()]);
            }
        }, 200, [
            'Content-Type' => 'application/x-ndjson',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function missingState(Request $request, string $key, string $route)
    {
        return $request->session()->has('installer.'.$key) ? null : redirect()->route($route);
    }
}
