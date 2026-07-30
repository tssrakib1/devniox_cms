# DevNiox Portfolio Website

DevNiox Portfolio Website v1.0.0 is a production Laravel application for a technology company website, content administration, commercial product catalogue, and lead workflow.

## Included modules

- Secure administration with Admin and Editor roles, notifications, dashboard statistics, settings, themes, and branded error pages
- Products, services, and portfolio projects with categories, galleries, SEO, publishing, featured content, and relational detail records
- Blog and knowledge center with categories, tags, scheduled publishing, RSS, downloads, and SEO
- Website CMS for home, about, contact, navigation, footer, branding, social links, and global settings
- Communication Hub for contact, demo, and quote workflows with assignment, replies, conversion, protected attachments, notes, notifications, and history
- Order Management, Money Management, centralized Activity Log, administrator profiles, System Health, and a reusable Media Library
- Public sitemap, canonical metadata, Open Graph, JSON-LD, responsive images, and cached shared content

## Requirements

- PHP 8.2+ and the extensions listed in [REQUIREMENTS.md](REQUIREMENTS.md), including GD or Imagick
- MySQL 8.0+ or MariaDB 10.4+
- Composer 2, Node.js 20+, and npm 10+
- A web server whose document root is the project's `public` directory
- Cron or an equivalent scheduler; a process supervisor is recommended for queues

## Installation

See [INSTALL.md](INSTALL.md) for the supported browser installer and manual deployment process. For a packaged release, upload the files, point the web root to `public`, and open `/install`. The installer creates `.env`, generates the key, migrates the database, creates the first Administrator, optionally installs official demo content, builds caches, and then locks itself.

For automated deployments, the equivalent manual flow is:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate --seed --force
php artisan storage:link
php artisan optimize
```

Set a strong `ADMIN_PASSWORD` before manual production seeding. The browser installer collects the Administrator securely and does not require the seeder. Never commit `.env`.

## Configuration

`.env.example` documents application, database, session, cache, queue, mail, administrator, storage, and optional integration variables. For production use HTTPS, `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, a dedicated database user, and real mail delivery settings.

The central runtime version is `config('app.version')`, populated by `APP_VERSION` and defaulting to `1.0.0`.

## Assets

Use `npm ci` for reproducible source builds. Commercial release archives already include compiled assets in `public/build`; customers do not need Node.js unless rebuilding frontend assets.

## Scheduler

The scheduler publishes due blog posts every minute without overlapping:

```cron
* * * * * cd /var/www/devniox && php artisan schedule:run >> /dev/null 2>&1
```

Use `php artisan schedule:list` to inspect registered tasks.

## Queue

The production example uses the database queue. Run a supervised worker:

```bash
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

Restart workers after every deployment with `php artisan queue:restart`.

## Cache and optimization

Build production caches with `php artisan optimize`. Clear stale caches before rebuilding with `php artisan optimize:clear`. Settings, CMS navigation/footer, homepage content, dashboard statistics, sitemap, and RSS use application caching with model-driven invalidation.

## Testing and code style

```bash
vendor/bin/pint --test
php artisan test
```

## Deployment and operations

See [DEPLOYMENT.md](DEPLOYMENT.md) for deployment, permissions, storage, backups, queues, scheduling, and rollback. See [UPGRADE.md](UPGRADE.md) before upgrading and [CHANGELOG.md](CHANGELOG.md) for release notes.

## Security

Keep keys and credentials outside source control, terminate TLS at the web server, restrict writable paths to `storage` and `bootstrap/cache`, and keep dependencies patched. Lead attachments and blog downloads use private storage and application-controlled delivery.

## License

Released under the [MIT License](LICENSE).
