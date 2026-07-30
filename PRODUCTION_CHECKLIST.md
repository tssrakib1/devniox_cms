# Production Checklist

## Before deployment

- Back up the database and both public/private uploaded storage.
- Set `APP_ENV=production`, `APP_DEBUG=false`, HTTPS `APP_URL`, secure cookies and real database/mail/cache/queue credentials.
- Set a unique `APP_KEY` and a strong `ADMIN_PASSWORD`; never commit `.env`.
- Run Composer security audit and review dependency changes.

## Build and deploy

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Ensure the web root is `public`, writable paths are limited to `storage` and `bootstrap/cache`, the scheduler runs every minute, and queue workers are supervised if enabled.

## Release verification

- Run Pint and the complete PHPUnit suite.
- Verify authentication, dashboard, public catalogues, lead forms, private downloads, Media Library, Orders, Finance, sitemap, RSS and error pages.
- Confirm System Health reports operational cache/storage and production debug is disabled.
- Confirm backups can be restored before announcing the release.
