# Production Deployment

## Release procedure

Back up before changing a release. Use maintenance mode when an atomic deployment is unavailable:

```bash
php artisan down --retry=60
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan optimize
php artisan queue:restart
php artisan up
```

For atomic releases, share `.env` and `storage`, build in the new release, migrate before switching the current symlink, then restart workers.

## Web server and TLS

- Serve only `public`, redirect HTTP to HTTPS, and set the canonical HTTPS `APP_URL`.
- Keep `APP_ENV=production`, `APP_DEBUG=false`, and secure session cookies enabled.
- Preserve application security headers and monitor `/up`.

## Scheduler

Due blog posts are published every minute with overlap protection:

```cron
* * * * * cd /var/www/devniox && php artisan schedule:run >> /dev/null 2>&1
```

Run one scheduler for a single-server deployment; on a cluster, designate one host. Verify with `php artisan schedule:list`.

## Queue

The example uses `QUEUE_CONNECTION=database`. Supervise:

```bash
php artisan queue:work --sleep=3 --tries=3 --backoff=10 --max-time=3600
```

Run `php artisan queue:restart` after releases. Monitor `php artisan queue:failed`.

## Cache

`php artisan optimize` builds configuration, event, route, and view caches. Managed application caches invalidate on record changes. Use `php artisan cache:clear` only when operationally necessary.

## Storage

- Public images: `storage/app/public`, exposed through `public/storage`.
- Lead attachments and blog downloads: `storage/app/private`, delivered through controlled routes.
- Persist all of `storage/app` across releases. PHP/web-server upload limits must support application validation.
- GD enables optimized WebP output.

## Backup and recovery

Back up the database with a transaction-consistent native dump, both `storage/app/public` and `storage/app/private`, and the production `.env` in restricted secret storage. Test restores regularly.

Restore by loading the database and `storage/app`, restoring `.env`, deploying the matching tag, installing optimized dependencies, recreating the storage link, rebuilding caches, restarting workers, and verifying health, authentication, downloads, and public pages.

## Post-deployment checks

Verify homepage, catalogues, portfolio, blog/RSS, CMS pages, lead forms, sitemap, admin dashboard, settings, uploads, protected downloads, queue, and scheduler. Review logs without exposing them publicly.

## Rollback

Switch to the previous release and restore its matching database/media backup when required. Never roll back source independently of incompatible database changes. Version 1.0.0 has no prior supported upgrade path.
