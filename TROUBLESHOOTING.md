# Troubleshooting

## Installer requirements do not pass

Confirm PHP 8.2+, all extensions in `REQUIREMENTS.md`, and write access to `storage`, `bootstrap/cache`, and `public`. On shared hosting, verify the website uses the intended PHP version for both the web server and CLI.

## Database connection fails

Use an empty MySQL or MariaDB database and a dedicated database user. Confirm the host and port supplied by the hosting provider; shared hosting commonly uses `localhost`, while containers and managed databases use a service hostname. Verify the user can create and alter tables in only the selected database.

## The website returns 404 after extraction

Set the domain document root to the package's `public` directory and enable the web server's rewrite support. For Apache, allow the bundled `public/.htaccess` rules. Do not browse the project root directly.

## Uploaded images are missing

Run `php artisan storage:link`, confirm `storage/app/public` is persistent and writable, and ensure the host permits symbolic links. If symbolic links are unavailable, ask the hosting provider for its supported public-storage mapping.

## A 500 response appears after deployment

Keep `APP_DEBUG=false`. Review `storage/logs/laravel.log` privately, verify `.env`, permissions, the generated application key, and the selected PHP version, then run `php artisan optimize:clear` followed by `php artisan optimize`.

## Installer redirects to the login page

This is expected after successful installation. The permanent lock prevents the installer from running again. Restore a complete pre-installation backup in a non-public environment if a genuinely fresh installation is required.

## Scheduled posts or queued work do not run

Configure the scheduler and supervised queue worker exactly as documented in `README.md` and `DEPLOYMENT.md`. Verify them under the same operating-system user and PHP binary used by the website.
