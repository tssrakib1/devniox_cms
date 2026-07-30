# Route Summary

Public routes provide Home, Products, Services, Portfolio, Blog/RSS, About, Contact, Demo Request, Quote Request, sitemap and authentication.

Authenticated `/admin` routes provide Dashboard, Profile, Notifications, Activity Log, Communication Hub, Orders, Finance, Media Library, content modules, CMS, Settings and System Health. State-changing routes use POST/PUT/PATCH/DELETE with CSRF protection. Downloads and previews are controller-delivered after policy authorization.

Use these commands for the authoritative environment-specific inventory:

```bash
php artisan route:list
php artisan route:list --path=admin
php artisan route:list --except-vendor
```

Production route caching is supported.
