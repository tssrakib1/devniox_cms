# Upgrade Guide

This process applies beginning with DevNiox Portfolio Website v1.0.0.

## Before every upgrade

1. Read the target release in `CHANGELOG.md`.
2. Verify platform requirements.
3. Back up the database, `storage/app`, and protected environment configuration.
4. Test using a recent production copy in a non-public environment.
5. Prepare and test rollback.

## Standard upgrade

```bash
php artisan down --retry=60
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
php artisan queue:restart
php artisan up
```

Run automated tests before deployment and the DEPLOYMENT post-release checklist afterward. Update `APP_VERSION` only when the deployed release requires it.

## Version 1.0.0

Version 1.0.0 is the first supported release. No earlier DevNiox database version has an automated upgrade path. Treat prototype imports as separate, tested data migrations; never copy raw database files.
