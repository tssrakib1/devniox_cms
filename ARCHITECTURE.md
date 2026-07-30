# DevNiox Architecture Summary

DevNiox is a Laravel 12 modular monolith. HTTP controllers remain thin; Form Requests own validation and authorization, policies enforce Admin/Editor boundaries, and transactional manager services coordinate aggregate changes, files, cache invalidation, notifications, and the centralized Activity Log.

## Application layers

- Public delivery: named routes, cached CMS/settings data, Blade components, Bootstrap 5, Vite assets, semantic SEO metadata, sitemap and RSS.
- Administration: AdminLTE 4 layout, resource controllers, policies, Form Requests, responsive tables/forms, notifications and dashboard statistics.
- Domain: normalized Eloquent models for Products, Services, Portfolio, Blog, Communications, Orders, Finance, Media, CMS and Settings.
- Services: aggregate managers use database transactions and post-commit file/cache cleanup.
- Storage: optimized public images and authorization-protected private documents. Media Library assets have stable identity, metadata and polymorphic usage records.
- Audit/security: activity logs, CSRF, output escaping, private downloads, rate limits, active-user middleware, security headers and production-safe errors.

The application version is centralized at `config('app.version')` and defaults to `1.0.0`.
