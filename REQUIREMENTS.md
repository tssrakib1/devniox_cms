# Production Requirements

DevNiox Portfolio Website v1.0.0 requires the following production environment.

## Runtime

- PHP 8.2 or newer
- MySQL 8.0+ or MariaDB 10.4+
- Apache 2.4, Nginx, LiteSpeed, or an equivalent web server
- HTTPS for production deployments

Required PHP extensions: BCMath, Ctype, cURL, DOM/XML, Fileinfo, JSON, Mbstring, OpenSSL, PDO, PDO MySQL, Tokenizer, and Zip. Enable either GD or Imagick for image processing.

## Writable paths

The PHP/web-server user must be able to write to `storage`, `bootstrap/cache`, and `public` during installation. After installation, retain write access only for `storage` and `bootstrap/cache`; `public` needs write access only when managing the storage link on hosts that require it.

The web document root must be the package's `public` directory. Never expose `.env`, `vendor`, `storage`, or the project root directly.

## Build and operations

The commercial package includes optimized Composer dependencies and compiled frontend assets. Composer 2, Node.js 20+, and npm 10+ are required only when rebuilding dependencies or assets.

Production operation requires a scheduler invocation every minute. A supervised queue worker is required when `QUEUE_CONNECTION` is asynchronous. Configure database and uploaded-media backups, mail delivery, TLS, log rotation, monitoring, and restore testing at the hosting layer.
