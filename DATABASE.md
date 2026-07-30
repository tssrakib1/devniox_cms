# Database Summary

The schema uses normalized relational tables, foreign keys, targeted indexes, timestamps and soft deletion where restoration is required.

Key aggregates:

- Content: `products`, `services`, `portfolio_projects`, `blog_posts` and their category, gallery, SEO and detail tables.
- Communications: `leads`, typed contact/demo/quote detail tables, replies, attachments, notes, status history and events.
- Operations: `orders`, items, attachments, notes and events.
- Finance: income/expense categories, transactions and attachments.
- Media: `media_folders`, `media_assets`, `media_usages`; attachment tables optionally reference shared assets.
- Platform: users, settings, CMS records, notifications and activity logs.

Run `php artisan migrate:status` before and after deployment. Back up the database and uploaded storage before production migrations.
