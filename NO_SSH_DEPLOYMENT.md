# DevNiox no-SSH deployment

This package is intended for hosting where only File Manager, phpMyAdmin, domain settings, and PHP settings are available. Node.js, npm, Composer, and Artisan are not required on the live server for initial boot.

## Package contents

The ZIP includes the Laravel application, production `vendor/`, compiled `public/build/` assets, and public CMS media. It excludes `node_modules/`, `.git/`, the local `.env`, development caches, sessions, and logs.

Required runtime: PHP 8.2+ with `pdo`, `pdo_mysql`, `openssl`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, and `zip`. The application also requires a writable PHP upload/temp directory and MySQL 8-compatible database access.

## Update order

1. Back up the existing site files and database in the hosting panel.
2. Upload the production ZIP using File Manager.
3. Extract it once into the intended application directory. Do not create a nested `Devniox/Devniox` directory.
4. Set the domain document root to the extracted `public/` directory.
5. Copy `.env.production.example` to `.env` and fill in the live database, mail, URL, and administrator values. For an existing installation, preserve the existing live `APP_KEY`.
6. Import only the SQL/schema changes required by the release in phpMyAdmin. This release contains no required database migration changes; do not import a destructive full dump over an existing database.
7. Ensure the `storage/` and `bootstrap/cache/` directories are writable by the web-server account using File Manager permissions. Use the hosting account’s normal user/group write permission; do not use `777` unless the host provides no safer option.
8. Confirm PHP 8.2+ and the extensions listed above in the hosting PHP selector.
9. Open `/up` if the application’s health route is enabled, then open the homepage.
10. Test admin login, public CSS/JS, images, a representative CMS page, and one image upload.
11. Confirm `/install` redirects to login or is otherwise unavailable after installation.
12. Confirm `APP_DEBUG=false` and HTTPS is active before accepting traffic.

## Public media strategy

Public uploads are served from `public/storage`, which contains only public CMS media. Private attachments remain on the non-public local disk under `storage/app/private`. Do not expose `storage/logs`, sessions, cache, framework data, private attachments, or `.env` through the document root.

If the hosting File Manager supports symlinks, it may create `public/storage` pointing to `storage/app/public`; the link target must be on the same account and must not expose `storage/app/private`. If symlinks are unavailable, use the packaged `public/storage` directory and ensure the live environment keeps the public filesystem root configured to that directory. Do not copy private storage content into it.

## Database and installer

This release does not require a live migration command. The installer remains available only until the installation lock exists; after installation, `/install/*` is blocked by the application. Never expose an unauthenticated migration or command-execution endpoint.

## Verification checklist

- Homepage loads without Node.js or Composer.
- `public/build/manifest.json` resolves all `@vite()` references.
- CSS, JavaScript, fonts, and CMS images load.
- Admin login succeeds.
- Image upload accepts PNG/JPEG/WebP and rejects SVG.
- Public contact map renders only an approved HTTPS Google Maps URL.
- `/install` is locked after installation.
- No `.env`, logs, sessions, cache, or private attachments are publicly reachable.
