# Installation

These instructions install DevNiox Portfolio Website v1.0.0 on a clean production host.

## Browser installation wizard

The commercial ZIP already includes production Composer dependencies and compiled assets. Extract it, point the domain document root to the `public` directory, grant the temporary writable permissions described below, create an empty database, and open `/install`. The wizard verifies PHP and filesystem requirements, tests the database, writes the environment configuration, generates the application key, runs migrations, creates the first Administrator through the existing user-management service, optionally installs official demo content, builds production caches, and permanently locks installer access.

The wizard supports XAMPP, Apache, Nginx, cPanel/shared hosting, and VPS deployments. The application root, storage, bootstrap/cache, and public directories must be writable during installation. Restore normal least-privilege ownership after setup.

The manual process below remains available for automated deployments.

For MySQL regression verification, create a disposable database whose name ends in `_test`, update the credentials in `phpunit.mysql.xml` or provide the documented `DEVNIOX_MYSQL_TEST_*` environment variables, then run `vendor/bin/phpunit -c phpunit.mysql.xml` (use `vendor\bin\phpunit` on Windows). The test bootstrap refuses any MySQL database without the `_test` suffix.

## 1. Prepare the host

Install the requirements in [REQUIREMENTS.md](REQUIREMENTS.md). Create an empty UTF-8 database and a dedicated user with privileges only on that database. Configure the virtual host document root as `<project>/public`; deny direct access to the project root, `.env`, `storage`, and `vendor`.

## 2. Install the application

```bash
cd /path/to/devniox
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

Edit `.env`. At minimum set `APP_URL`, database credentials, `ADMIN_EMAIL`, a unique strong `ADMIN_PASSWORD`, and production mail settings. Keep `APP_VERSION=1.0.0`, `APP_ENV=production`, `APP_DEBUG=false`, and `SESSION_SECURE_COOKIE=true` for HTTPS.

## 3. Build assets and database

```bash
npm ci
npm run build
php artisan migrate --seed --force
php artisan storage:link
```

Seeding is idempotent. Production seeding refuses to run when `ADMIN_EMAIL` or `ADMIN_PASSWORD` is blank, preventing a default administrative password.

## 4. Permissions

The application user must be able to write to `bootstrap/cache`, `storage/app/private`, `storage/app/public`, `storage/framework`, and `storage/logs`. Source, configuration, and `public` should otherwise be read-only. Do not make the project world-writable.

## 5. Optimize and start

```bash
php artisan optimize
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

Supervise the queue process and configure the scheduler as documented in README and DEPLOYMENT. Verify `/up`, the homepage, `/admin/login`, and `/sitemap.xml`.

## Windows/XAMPP notes

Use `copy .env.example .env`, set Apache's document root to `C:\xampp\htdocs\devniox\public`, and run commands from the project directory. Use Windows Task Scheduler every minute for `php artisan schedule:run` and a service wrapper for `php artisan queue:work`.
