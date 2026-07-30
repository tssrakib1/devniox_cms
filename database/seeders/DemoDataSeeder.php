<?php

namespace Database\Seeders;

use App\Services\SimplePdfService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    private Carbon $now;

    private ?int $userId;

    private array $inserted = [];

    private array $skipped = [];

    private array $media = [];

    public function run(): void
    {
        $this->now = now();
        $this->userId = DB::table('users')->orderBy('id')->value('id');
        $this->seedWebsite();
        $this->seedMedia();
        if ($this->userId === null) {
            $this->skipped[] = 'User-dependent modules (installer-created user required by foreign keys)';
        } else {
            $this->seedProducts();
            $this->seedServices();
            $this->seedPortfolio();
            $this->seedBlog();
            $this->seedCommunication();
            $this->seedOrders();
            $this->seedFinance();
            $this->seedNotifications();
        }
        Cache::forget('home.featured-content.v1');
        Cache::forget('home.featured-content.v2');
        Cache::forget('blog.rss.v1');
        Cache::forget('seo.sitemap.v1');
        foreach ($this->inserted as $module => $count) {
            $this->command?->info("{$module}: {$count} inserted");
        }
        foreach ($this->skipped as $module) {
            $this->command?->warn("Skipped: {$module}");
        }
    }

    private function seedWebsite(): void
    {
        if (! DB::table('settings')->exists()) {
            $settings = [
                ['company', 'name', 'DevNiox', 'string', 1],
                ['company', 'tagline', 'Business software engineered around real operations.', 'string', 1],
                ['contact', 'email', 'hello@devniox.com', 'email', 1],
                ['contact', 'phone', '+880 1700-000000', 'string', 1],
                ['contact', 'address', 'Dhaka, Bangladesh', 'text', 1],
                ['hours', 'weekdays', 'Sunday–Thursday, 9:00 AM–6:00 PM', 'string', 1],
                ['seo', 'meta_title', 'DevNiox — Business Software and Engineering', 'string', 1],
                ['seo', 'meta_description', 'DevNiox builds commercial business software, connected operational systems, and maintainable digital products.', 'text', 1],
            ];
            DB::transaction(function () use ($settings) {
                foreach ($settings as [$group, $key, $value, $type, $public]) {
                    DB::table('settings')->insert(['group' => $group, 'key' => $key, 'value' => $value, 'type' => $type, 'is_public' => $public, 'created_at' => $this->now, 'updated_at' => $this->now]);
                }
            });
            $this->inserted['Website settings'] = count($settings);
        } else {
            $this->skipped[] = 'Website settings (not empty)';
        }
        if (! DB::table('cms_pages')->exists()) {
            DB::transaction(function () {
                $home = DB::table('cms_pages')->insertGetId($this->page('home', 'DevNiox — Business Software Built for Operations', 'Commercial software for organisations that need accountable workflows, reliable data, and room to grow.'));
                DB::table('cms_home_content')->insert(['cms_page_id' => $home, 'hero_heading' => 'Business software built around how your company actually works.', 'hero_subheading' => 'Products, custom systems, and connected operations', 'hero_description' => 'DevNiox builds commercial software for distributors, service companies, schools, healthcare teams, restaurants, ISPs, and multi-branch operations.', 'primary_button_text' => 'Explore products', 'primary_button_url' => '/products', 'secondary_button_text' => 'Discuss your requirements', 'secondary_button_url' => '/contact', 'intro_title' => 'One engineering partner from operating model to production', 'intro_description' => 'We map users, decisions, exceptions, data ownership, integrations, and reporting before writing software.', 'products_title' => 'Commercial software products', 'products_description' => 'Proven foundations configured around your teams and controls.', 'services_title' => 'Engineering capabilities', 'services_description' => 'Strategy, development, integrations, deployment, and technical ownership.', 'ai_title' => '', 'ai_description' => null, 'portfolio_title' => 'Systems delivered for real operations', 'portfolio_description' => 'Representative engagements across important business sectors.', 'articles_title' => 'Practical software decisions', 'articles_description' => 'Clear guidance for owners and operations leaders.', 'ecosystem_enabled' => true, 'ecosystem_label' => 'OUR ECOSYSTEM', 'ecosystem_title' => 'Powerful Platforms. One Parent Company.', 'ecosystem_description' => 'Ravoltify Technologies builds and manages a growing ecosystem of software products and digital platforms designed to help businesses operate more efficiently.', 'ecosystem_note' => 'All platforms are developed, maintained and supported by Ravoltify Technologies.', 'created_at' => $this->now, 'updated_at' => $this->now]);
                foreach ([['Workflow before features', 'We design around ownership, approvals, exceptions, and measurable outcomes.', 'diagram-3'], ['Production discipline', 'Security, testing, deployment, and recovery are part of delivery.', 'shield-check'], ['Long-term maintainability', 'Readable architecture and documentation protect future investment.', 'boxes'], ['Commercial accountability', 'Scope, risks, and progress remain visible.', 'clipboard-check']] as $i => [$title, $description, $icon]) {
                    DB::table('home_why_items')->insert(compact('title', 'description', 'icon') + ['cms_page_id' => $home, 'sort_order' => $i, 'created_at' => $this->now, 'updated_at' => $this->now]);
                }
                foreach ([['10+', 'Software products', 'box-seam'], ['8', 'Engineering capabilities', 'code-square'], ['6', 'Industries represented', 'buildings'], ['100%', 'Production ownership', 'check2-circle']] as $i => [$value, $title, $icon]) {
                    DB::table('home_statistics')->insert(compact('title', 'value', 'icon') + ['cms_page_id' => $home, 'description' => null, 'sort_order' => $i, 'created_at' => $this->now, 'updated_at' => $this->now]);
                }
                $about = DB::table('cms_pages')->insertGetId($this->page('about', 'About DevNiox', 'Meet the engineering company behind DevNiox business software.'));
                DB::table('cms_about_content')->insert(['cms_page_id' => $about, 'hero_heading' => 'We build software that becomes part of the operation.', 'hero_description' => 'DevNiox is a product-focused software company in Bangladesh building systems for people, money, inventory, customers, and decisions.', 'story_title' => 'Founded to close the gap between software delivery and business reality', 'story_description' => 'DevNiox was founded after seeing capable teams held back by fragmented spreadsheets, rigid products, and projects that solved screens without solving operations.', 'mission_title' => 'Make dependable operational software accessible', 'mission_description' => 'We turn important workflows into secure, understandable systems with current information and useful visibility.', 'vision_title' => 'Build a respected software product company from Bangladesh', 'vision_description' => 'Our vision is a durable product portfolio and an engineering practice known for honest decisions and maintainable architecture.', 'created_at' => $this->now, 'updated_at' => $this->now]);
                foreach ([['Product thinking', 'We judge features by the workflow they improve.', 'lightbulb'], ['Engineering clarity', 'Simple boundaries and explicit data reduce risk.', 'code-slash'], ['Quality without theatre', 'Tests, review, security, and recovery plans must work.', 'patch-check'], ['Long-term ownership', 'We design for the team operating the system after launch.', 'people']] as $i => [$title, $description, $icon]) {
                    DB::table('about_core_values')->insert(compact('title', 'description', 'icon') + ['cms_page_id' => $about, 'sort_order' => $i, 'created_at' => $this->now, 'updated_at' => $this->now]);
                }
                foreach ([['Discover the operation', 'Map users, responsibilities, constraints, and evidence.'], ['Define the product boundary', 'Prioritise a coherent release.'], ['Build and verify', 'Deliver reviewable increments with tests.'], ['Launch and learn', 'Deploy safely, train owners, and measure adoption.']] as $i => [$title, $description]) {
                    DB::table('about_work_items')->insert(compact('title', 'description') + ['cms_page_id' => $about, 'icon' => 'arrow-right-circle', 'sort_order' => $i, 'created_at' => $this->now, 'updated_at' => $this->now]);
                }
                $contact = DB::table('cms_pages')->insertGetId($this->page('contact', 'Contact DevNiox', 'Discuss software products, custom development, integrations, and automation.'));
                DB::table('cms_contact_content')->insert(['cms_page_id' => $contact, 'hero_heading' => 'Tell us what the business needs to improve.', 'hero_description' => 'Share the workflow, users, current tools, urgency, and result needed. We respond within one business day.', 'company_name' => 'DevNiox', 'address' => 'Dhaka, Bangladesh', 'email' => 'hello@devniox.com', 'phone' => '+880 1700-000000', 'whatsapp' => '+880 1700-000000', 'success_message' => 'Thank you. We will respond within one business day.', 'auto_reply_enabled' => 1, 'auto_reply_subject' => 'DevNiox received your request', 'auto_reply_message' => 'Thank you for contacting DevNiox. We will respond with a practical next step.', 'created_at' => $this->now, 'updated_at' => $this->now]);
                for ($day = 0; $day < 7; $day++) {
                    $closed = in_array($day, [5, 6]);
                    DB::table('cms_business_hours')->insert(['cms_page_id' => $contact, 'day_of_week' => $day, 'is_closed' => $closed, 'opens_at' => $closed ? null : '09:00', 'closes_at' => $closed ? null : '18:00']);
                }
            });
            $this->inserted['CMS pages'] = 3;
        } else {
            $this->skipped[] = 'CMS pages (not empty)';
        }
        if (! DB::table('cms_navigation_items')->exists()) {
            $items = [['Home', '/'], ['Products', '/products'], ['Services', '/services'], ['Portfolio', '/portfolio'], ['Blog', '/blog'], ['About', '/about'], ['Contact', '/contact']];
            foreach ($items as $i => [$label, $url]) {
                DB::table('cms_navigation_items')->insert(['location' => 'header', 'label' => $label, 'url' => $url, 'open_new_tab' => 0, 'is_visible' => 1, 'display_order' => $i, 'created_at' => $this->now, 'updated_at' => $this->now]);
            }
            $this->inserted['Navigation'] = count($items);
        } else {
            $this->skipped[] = 'Navigation (not empty)';
        }
        if (! DB::table('cms_footer_content')->exists()) {
            DB::table('cms_footer_content')->insert(['copyright' => '© '.$this->now->year.' DevNiox. All rights reserved.', 'short_description' => 'Commercial business software and custom systems engineered in Bangladesh.', 'quick_links_heading' => 'Company', 'products_heading' => 'Products', 'services_heading' => 'Engineering', 'ai_heading' => '', 'blog_heading' => 'Knowledge', 'privacy_url' => null, 'terms_url' => null, 'cookies_url' => null, 'updated_by' => $this->userId, 'created_at' => $this->now, 'updated_at' => $this->now]);
            $this->inserted['Footer'] = 1;
        } else {
            $this->skipped[] = 'Footer (not empty)';
        }
    }

    private function seedMedia(): void
    {
        if (DB::table('media_assets')->exists()) {
            $this->skipped[] = 'Media Library (not empty)';

            return;
        }
        DB::transaction(function () {
            foreach ([['Brand', 'brand'], ['Products', 'products'], ['Portfolio', 'portfolio'], ['Knowledge Center', 'knowledge'], ['Documents', 'documents']] as [$name, $slug]) {
                DB::table('media_folders')->insert(['name' => $name, 'slug' => $slug, 'created_at' => $this->now, 'updated_at' => $this->now]);
            }
            $folders = DB::table('media_folders')->pluck('id', 'slug');
            foreach (['company-logo' => ['brand', '#1967d2', 'DevNiox company mark'], 'product-dashboard' => ['products', '#0f766e', 'Business software dashboard'], 'operations-overview' => ['products', '#7c3aed', 'Operational reporting interface'], 'portfolio-platform' => ['portfolio', '#c2410c', 'Client platform case study'], 'knowledge-banner' => ['knowledge', '#1d4ed8', 'DevNiox knowledge center']] as $key => [$folder, $accent, $alt]) {
                $path = 'demo/media/'.$key.'.png';
                $content = $this->png($accent);
                Storage::disk('public')->put($path, $content);
                $id = DB::table('media_assets')->insertGetId(['media_folder_id' => $folders[$folder], 'uploaded_by' => $this->userId, 'name' => Str::headline($key), 'original_name' => $key.'.png', 'disk' => 'public', 'file_path' => $path, 'mime_type' => 'image/png', 'extension' => 'png', 'kind' => 'image', 'file_size' => strlen($content), 'sha256' => hash('sha256', $content), 'width' => 1200, 'height' => 675, 'alt_text' => $alt, 'description' => 'DevNiox demonstration artwork for '.$alt.'.', 'is_optimized' => 1, 'created_at' => $this->now, 'updated_at' => $this->now]);
                $this->media[$key] = compact('id', 'path') + ['disk' => 'public'];
            }
            $pdf = app(SimplePdfService::class)->make(['DevNiox Product Portfolio', '', 'Commercial software for orders, inventory, billing, people, education, healthcare, and multi-branch operations.', '', 'hello@devniox.com', 'Dhaka, Bangladesh']);
            $path = 'demo/documents/devniox-product-portfolio.pdf';
            Storage::disk('local')->put($path, $pdf);
            $id = DB::table('media_assets')->insertGetId(['media_folder_id' => $folders['documents'], 'uploaded_by' => $this->userId, 'name' => 'DevNiox Product Portfolio', 'original_name' => 'devniox-product-portfolio.pdf', 'disk' => 'local', 'file_path' => $path, 'mime_type' => 'application/pdf', 'extension' => 'pdf', 'kind' => 'document', 'file_size' => strlen($pdf), 'sha256' => hash('sha256', $pdf), 'description' => 'Overview of DevNiox commercial software products.', 'is_optimized' => 0, 'created_at' => $this->now, 'updated_at' => $this->now]);
            $this->media['brochure'] = compact('id', 'path') + ['disk' => 'local'];
        });
        $this->inserted['Media Library'] = 11;
    }

    private function seedProducts(): void
    {
        if (DB::table('products')->exists() || DB::table('product_categories')->exists()) {
            $this->skipped[] = 'Products (product or category table is not empty)';

            return;
        }
        $rows = [
            ['DevNiox Order Management System', 'order-management-system', 'Commerce Operations', 'Control sales orders, fulfilment, delivery, returns, and payment status from one operational record.', ['Order Capture', 'Fulfilment', 'Delivery', 'Returns']],
            ['DevNiox ISP Billing', 'isp-billing', 'Subscription Operations', 'Manage subscribers, packages, recurring billing, collections, support history, and service status.', ['Subscribers', 'Packages', 'Billing', 'Support']],
            ['DevNiox POS', 'point-of-sale', 'Commerce Operations', 'Run counter sales with products, pricing, payments, shifts, stock movement, and outlet reporting.', ['Sales', 'Shifts', 'Payments', 'Outlet Reports']],
            ['DevNiox Inventory', 'inventory-management', 'Commerce Operations', 'Maintain dependable stock across purchasing, warehouses, transfers, adjustments, and reorder decisions.', ['Catalogue', 'Purchasing', 'Warehouses', 'Stock Ledger']],
            ['DevNiox Restaurant QR Ordering', 'restaurant-qr-ordering', 'Hospitality Systems', 'Connect digital menus, table orders, kitchen routing, billing, and outlet performance.', ['Digital Menu', 'Table Ordering', 'Kitchen Display', 'Billing']],
            ['DevNiox School ERP', 'school-erp', 'Institution Management', 'Coordinate admissions, students, attendance, fees, examinations, guardians, and reporting.', ['Admissions', 'Students', 'Fees', 'Examinations']],
            ['DevNiox Hospital Management', 'hospital-management', 'Institution Management', 'Coordinate appointments, patient records, diagnostics, pharmacy requests, and billing.', ['Appointments', 'Patients', 'Clinical Records', 'Billing']],
            ['DevNiox HR & Payroll', 'hr-payroll', 'Workforce Systems', 'Unify employee records, attendance, leave, payroll, deductions, approvals, and reports.', ['Employees', 'Attendance', 'Leave', 'Payroll']],
            ['DevNiox E-Commerce', 'e-commerce-platform', 'Commerce Operations', 'Operate catalogue, promotions, checkout, orders, delivery, customers, and reporting.', ['Catalogue', 'Checkout', 'Orders', 'Customers']],
            ['DevNiox Alumni Management', 'alumni-management', 'Institution Management', 'Maintain verified alumni records, chapters, events, contributions, and communications.', ['Directory', 'Chapters', 'Events', 'Contributions']],
        ];
        DB::transaction(function () use ($rows) {
            $categories = [];
            foreach (collect($rows)->pluck(2)->unique()->values() as $i => $name) {
                $categories[$name] = DB::table('product_categories')->insertGetId(['name' => $name, 'slug' => Str::slug($name), 'description' => "DevNiox {$name} products for accountable operations.", 'icon' => 'grid', 'sort_order' => $i, 'is_active' => 1, 'seo_title' => Str::limit($name.' Software | DevNiox', 70, ''), 'seo_description' => "Commercial {$name} software from DevNiox.", 'created_at' => $this->now, 'updated_at' => $this->now]);
            }
            foreach ($rows as $i => [$name, $slug, $category, $summary, $modules]) {
                $image = $i % 2 ? ($this->media['operations-overview'] ?? null) : ($this->media['product-dashboard'] ?? null);
                $id = DB::table('products')->insertGetId(['product_category_id' => $categories[$category], 'created_by' => $this->userId, 'updated_by' => $this->userId, 'name' => $name, 'slug' => $slug, 'version' => '1.'.($i + 1).'.0', 'status' => 'published', 'is_featured' => $i < 4, 'display_order' => $i, 'short_description' => $summary, 'full_description' => $summary.' DevNiox configures roles, approvals, master data, integrations, and reports around the operating model. The implementation replaces fragmented records with a controlled source of truth.', 'thumbnail_path' => $image['path'] ?? null, 'banner_path' => $image['path'] ?? null, 'published_at' => $this->now->copy()->subDays(30 - $i), 'created_at' => $this->now, 'updated_at' => $this->now]);
                foreach ($modules as $j => $module) {
                    DB::table('product_modules')->insert(['product_id' => $id, 'name' => $module, 'description' => "{$module} records, responsibilities, exceptions, and reports in one workflow.", 'sort_order' => $j, 'created_at' => $this->now, 'updated_at' => $this->now]);
                }
                foreach ([['Operational clarity', 'Give each team current status and explicit ownership.'], ['Controlled decisions', 'Use permissions, approvals, and audit history.'], ['Management visibility', 'Turn live records into useful reports.']] as $j => [$title, $description]) {
                    DB::table('product_highlights')->insert(compact('title', 'description') + ['product_id' => $id, 'icon' => 'check2-circle', 'sort_order' => $j, 'created_at' => $this->now, 'updated_at' => $this->now]);
                    DB::table('product_features')->insert(compact('title', 'description') + ['product_id' => $id, 'icon' => 'shield-check', 'sort_order' => $j, 'created_at' => $this->now, 'updated_at' => $this->now]);
                }
                DB::table('product_requirements')->insert(['product_id' => $id, 'php_version' => '8.2+', 'laravel_version' => '12.x', 'database' => 'MySQL 8.0+ or MariaDB 10.6+', 'hosting' => 'Managed VPS or equivalent', 'browser_support' => 'Current Chrome, Edge, Firefox, and Safari', 'server_requirements' => 'HTTPS, scheduler, queue worker, backups, and monitored storage.', 'created_at' => $this->now, 'updated_at' => $this->now]);
                foreach ([['Starter', 75000, 'one_time', 0], ['Business', 185000, 'one_time', 1], ['Enterprise', null, 'custom', 0]] as $j => [$plan, $price, $billing, $highlight]) {
                    $planId = DB::table('product_pricing_plans')->insertGetId(['product_id' => $id, 'name' => $plan, 'price' => $price, 'currency' => 'BDT', 'billing_type' => $billing, 'description' => $plan.' scope with onboarding and support.', 'is_highlighted' => $highlight, 'sort_order' => $j, 'is_active' => 1, 'created_at' => $this->now, 'updated_at' => $this->now]);
                    foreach (['Role-based access', 'Implementation onboarding', $plan === 'Enterprise' ? 'Custom integrations and SLA' : 'Standard reporting and support'] as $k => $feature) {
                        DB::table('product_pricing_plan_features')->insert(['product_pricing_plan_id' => $planId, 'feature' => $feature, 'sort_order' => $k, 'created_at' => $this->now, 'updated_at' => $this->now]);
                    }
                }
                foreach ([['Can DevNiox migrate existing records?', 'Yes. We assess structure, quality, ownership, and reconciliation before migration.'], ['Can workflows and permissions be configured?', 'Yes. Discovery maps roles, approvals, exceptions, and reporting.']] as $j => [$question, $answer]) {
                    DB::table('product_faqs')->insert(compact('question', 'answer') + ['product_id' => $id, 'sort_order' => $j, 'created_at' => $this->now, 'updated_at' => $this->now]);
                }
                DB::table('product_seo')->insert(['product_id' => $id, 'meta_title' => Str::limit($name.' | DevNiox', 70, ''), 'meta_description' => Str::limit($summary, 160, ''), 'keywords' => Str::lower($name.', business software, Bangladesh, DevNiox'), 'open_graph_image_path' => $image['path'] ?? null, 'is_indexable' => 1, 'created_at' => $this->now, 'updated_at' => $this->now]);
                if ($image) {
                    DB::table('product_gallery_images')->insert(['product_id' => $id, 'image_path' => $image['path'], 'alt_text' => $name.' dashboard overview', 'sort_order' => 0, 'created_at' => $this->now, 'updated_at' => $this->now]);
                    $this->usage($image['id'], 'App\\Models\\Product', $id, 'thumbnail_path');
                }
            }
        });
        $this->inserted['Products'] = count($rows);
    }

    private function seedServices(): void
    {
        if (DB::table('services')->exists() || DB::table('service_categories')->exists()) {
            $this->skipped[] = 'Services (service or category table is not empty)';

            return;
        }
        $rows = [['Custom Software Development', 'custom-software-development', 'Build operational systems around actual users, controls, and growth plans.'], ['Web Application Development', 'web-application-development', 'Build secure browser applications for teams, customers, partners, and management.'], ['Mobile App Development', 'mobile-app-development', 'Deliver focused mobile experiences connected to dependable APIs.'], ['UI/UX Design', 'ui-ux-design', 'Turn complex workflows into clear interfaces and interaction patterns.'], ['Cloud Deployment', 'cloud-deployment', 'Prepare production environments, monitoring, backups, releases, and recovery.'], ['API Integration', 'api-integration', 'Connect payment, logistics, accounting, identity, and partner systems.'], ['Application Maintenance', 'application-maintenance', 'Keep production systems secure, observable, supported, and ready for change.'], ['IT Consulting', 'it-consulting', 'Assess software decisions, architecture, delivery risk, and roadmaps.']];
        DB::transaction(function () use ($rows) {
            $category = DB::table('service_categories')->insertGetId(['name' => 'Engineering Services', 'slug' => 'engineering-services', 'description' => 'End-to-end product engineering and production ownership.', 'icon' => 'code-square', 'sort_order' => 0, 'status' => 'published', 'seo_title' => 'Software Engineering Services | DevNiox', 'seo_description' => 'Custom software, applications, integrations, deployment, design, and consulting.', 'created_at' => $this->now, 'updated_at' => $this->now]);
            foreach ($rows as $i => [$name, $slug, $summary]) {
                $image = $i % 2 ? ($this->media['operations-overview'] ?? null) : ($this->media['product-dashboard'] ?? null);
                $id = DB::table('services')->insertGetId(['service_category_id' => $category, 'created_by' => $this->userId, 'updated_by' => $this->userId, 'name' => $name, 'slug' => $slug, 'cover_image_path' => $image['path'] ?? null, 'featured_image_path' => $image['path'] ?? null, 'status' => 'published', 'is_featured' => $i < 4, 'display_order' => $i, 'short_description' => $summary, 'full_description' => $summary.' We begin with business evidence, define a reviewable scope, deliver in controlled increments, and hand over a system the operating team can own.', 'published_at' => $this->now->copy()->subDays(20 - $i), 'created_at' => $this->now, 'updated_at' => $this->now]);
                foreach ([['Discovery', 'Map users, workflow, constraints, risks, and outcomes.'], ['Architecture', 'Define data ownership, integrations, security, and deployment.'], ['Delivery', 'Build and verify against acceptance criteria.'], ['Production', 'Deploy, monitor, document, and support adoption.']] as $j => [$title, $description]) {
                    DB::table('service_process_steps')->insert(['service_id' => $id, 'step_number' => $j + 1, 'title' => $title, 'description' => $description, 'sort_order' => $j, 'created_at' => $this->now, 'updated_at' => $this->now]);
                }
                foreach ([['Solution brief', 'A shared statement of problem, users, scope, and outcomes.'], ['Production release', 'A tested release with agreed functionality and controls.'], ['Operations handover', 'Deployment, backup, recovery, and ownership documentation.']] as $j => [$title, $description]) {
                    DB::table('service_deliverables')->insert(compact('title', 'description') + ['service_id' => $id, 'sort_order' => $j, 'created_at' => $this->now, 'updated_at' => $this->now]);
                    DB::table('service_benefits')->insert(compact('title', 'description') + ['service_id' => $id, 'icon' => 'check2-circle', 'sort_order' => $j, 'created_at' => $this->now, 'updated_at' => $this->now]);
                }
                foreach (['Laravel', 'PHP', 'MySQL', 'REST APIs', 'Bootstrap'] as $j => $technology) {
                    DB::table('service_technologies')->insert(['service_id' => $id, 'name' => $technology, 'icon' => 'code-slash', 'sort_order' => $j, 'created_at' => $this->now, 'updated_at' => $this->now]);
                }
                foreach ([['How is the timeline confirmed?', 'After discovery clarifies scope, dependencies, owners, and acceptance criteria.'], ['Can DevNiox work with an existing system?', 'Yes. We assess architecture, data, deployment, tests, and risk first.']] as $j => [$question, $answer]) {
                    DB::table('service_faqs')->insert(compact('question', 'answer') + ['service_id' => $id, 'sort_order' => $j, 'created_at' => $this->now, 'updated_at' => $this->now]);
                }
                DB::table('service_seo')->insert(['service_id' => $id, 'meta_title' => Str::limit($name.' | DevNiox', 70, ''), 'meta_description' => Str::limit($summary, 160, ''), 'meta_keywords' => Str::lower($name.', software engineering, DevNiox'), 'is_indexable' => 1, 'created_at' => $this->now, 'updated_at' => $this->now]);
                if ($image) {
                    DB::table('service_gallery_images')->insert(['service_id' => $id, 'image_path' => $image['path'], 'alt_text' => $name.' delivery workflow', 'sort_order' => 0, 'created_at' => $this->now, 'updated_at' => $this->now]);
                    $this->usage($image['id'], 'App\\Models\\Service', $id, 'cover_image_path');
                }
            }
        });
        $this->inserted['Services'] = count($rows);
    }

    private function seedPortfolio(): void
    {
        if (DB::table('portfolio_projects')->exists() || DB::table('portfolio_categories')->exists()) {
            $this->skipped[] = 'Portfolio (project or category table is not empty)';

            return;
        }
        $rows = [
            ['Apex Garments Production ERP', 'Apex Apparels Ltd.', 'Apparel Manufacturing', 'Production planning and material status were spread across spreadsheets.', 'A role-based ERP connected orders, materials, production, quality, and shipment readiness.', 'Weekly reconciliation time fell by 60% and order-risk visibility improved.'],
            ['Bistro 71 Restaurant POS', 'Bistro 71', 'Hospitality', 'Counter orders, kitchen tickets, and sales reports were disconnected.', 'A POS workflow connected QR orders, counter sales, kitchen status, payments, and closing.', 'Order handoff errors declined and outlet closing became a 15-minute process.'],
            ['MetroNet ISP CRM', 'MetroNet Communications', 'Telecommunications', 'Subscriber records, invoices, and support history lived in separate tools.', 'A CRM unified service plans, billing, collections, tickets, and account history.', 'Collection follow-up became measurable and agents gained one customer record.'],
            ['Rahman Traders Inventory', 'Rahman Traders', 'Wholesale Distribution', 'Stock availability was unreliable across warehouses and a showroom.', 'A stock ledger connected purchasing, receiving, transfers, sales, and adjustments.', 'Stock variance reduced and purchasing moved to reorder evidence.'],
            ['Northgate School ERP', 'Northgate Model School', 'Education', 'Admissions, fees, attendance, results, and communication required repeated entry.', 'One student record connected admissions, academics, accounts, and communication.', 'Fee reconciliation and result preparation became faster.'],
            ['CarePoint Clinic Operations', 'CarePoint Medical Centre', 'Healthcare', 'Appointments, consultations, diagnostics, and billing lacked a shared workflow.', 'A clinic platform coordinated visits, notes, service requests, payments, and reports.', 'Waiting time improved and billing exceptions became traceable.'],
            ['SwiftDrop Courier Control', 'SwiftDrop Logistics', 'Logistics', 'Parcel status and cash collection reports arrived late from field operations.', 'A courier workflow connected booking, hubs, assignments, events, and COD reconciliation.', 'Operations gained same-day exception visibility and reliable settlement.'],
            ['ForgeWorks Manufacturing ERP', 'ForgeWorks Engineering', 'Manufacturing', 'Material demand, work orders, output, and costing were reconciled after production.', 'An ERP connected demand, materials, work orders, consumption, output, and quality.', 'Shortages surfaced earlier and month-end costing became reproducible.'],
        ];
        DB::transaction(function () use ($rows) {
            $category = DB::table('portfolio_categories')->insertGetId(['name' => 'Business Systems', 'slug' => 'business-systems', 'description' => 'Representative DevNiox operational software engagements.', 'icon' => 'window-stack', 'sort_order' => 0, 'status' => 'published', 'seo_title' => 'Software Case Studies | DevNiox', 'seo_description' => 'Operational software case studies across Bangladesh.', 'created_at' => $this->now, 'updated_at' => $this->now]);
            foreach ($rows as $i => [$name, $client, $industry, $challenge, $solution, $result]) {
                $image = $this->media['portfolio-platform'] ?? null;
                $id = DB::table('portfolio_projects')->insertGetId(['portfolio_category_id' => $category, 'created_by' => $this->userId, 'updated_by' => $this->userId, 'name' => $name, 'slug' => Str::slug($name), 'client_name' => $client, 'industry' => $industry, 'completion_date' => $this->now->copy()->subMonths(15 - $i)->toDateString(), 'status' => 'published', 'is_featured' => $i < 4, 'display_order' => $i, 'thumbnail_path' => $image['path'] ?? null, 'cover_image_path' => $image['path'] ?? null, 'short_description' => $solution, 'full_description' => "{$client} engaged DevNiox to replace fragmented records with a controlled system teams could adopt and management could trust.", 'published_at' => $this->now->copy()->subMonths(14 - $i), 'created_at' => $this->now, 'updated_at' => $this->now]);
                foreach ([['objectives', 'Business challenge', $challenge], ['solutions', 'Delivered solution', $solution], ['results', 'Measured result', $result]] as [$suffix, $title, $description]) {
                    DB::table('portfolio_project_'.$suffix)->insert(compact('title', 'description') + ['portfolio_project_id' => $id, 'sort_order' => 0, 'created_at' => $this->now, 'updated_at' => $this->now]);
                }
                foreach (['Laravel 12', 'PHP 8.3', 'MySQL', 'Bootstrap 5', 'REST APIs'] as $j => $technology) {
                    DB::table('portfolio_project_technologies')->insert(['portfolio_project_id' => $id, 'name' => $technology, 'icon' => 'code-slash', 'sort_order' => $j, 'created_at' => $this->now, 'updated_at' => $this->now]);
                }
                DB::table('portfolio_project_seo')->insert(['portfolio_project_id' => $id, 'meta_title' => Str::limit($name.' Case Study | DevNiox', 70, ''), 'meta_description' => Str::limit($solution, 160, ''), 'meta_keywords' => Str::lower($industry.', ERP case study, DevNiox'), 'open_graph_image_path' => $image['path'] ?? null, 'is_indexable' => 1, 'created_at' => $this->now, 'updated_at' => $this->now]);
                if ($image) {
                    DB::table('portfolio_project_gallery_images')->insert(['portfolio_project_id' => $id, 'image_path' => $image['path'], 'alt_text' => $name.' software interface', 'sort_order' => 0, 'created_at' => $this->now, 'updated_at' => $this->now]);
                    $this->usage($image['id'], 'App\\Models\\PortfolioProject', $id, 'thumbnail_path');
                }
            }
        });
        $this->inserted['Portfolio projects'] = count($rows);
    }

    private function seedBlog(): void
    {
        if (DB::table('blog_posts')->exists() || DB::table('blog_categories')->exists()) {
            $this->skipped[] = 'Blog (post or category table is not empty)';

            return;
        }
        $topics = [['How ERP Improves Day-to-Day Business Control', 'ERP'], ['When Custom Software Is the Better Commercial Decision', 'Software Strategy'], ['A Practical Automation Roadmap for Growing Businesses', 'Automation'], ['Restaurant QR Ordering: What Happens After the Scan', 'POS & Retail'], ['Inventory Controls That Prevent Costly Stock Confusion', 'Inventory'], ['Why Laravel Works for Maintainable Enterprise Applications', 'Laravel'], ['Digital Transformation Starts With an Operating Constraint', 'Business'], ['How to Prepare Your Team for ERP Discovery', 'ERP'], ['API Integration Needs Ownership, Not Just Connectivity', 'Software Development'], ['Cyber Security Questions Every Software Buyer Should Ask', 'Cyber Security'], ['Building a POS That Finance and Operations Can Both Trust', 'POS & Retail'], ['What a Production-Ready Software Handover Should Include', 'Software Development']];
        DB::transaction(function () use ($topics) {
            $categories = [];
            foreach (collect($topics)->pluck(1)->unique()->values() as $i => $name) {
                $categories[$name] = DB::table('blog_categories')->insertGetId(['name' => $name, 'slug' => Str::slug($name), 'description' => "Practical DevNiox guidance about {$name} decisions.", 'icon' => 'journal-text', 'sort_order' => $i, 'status' => 'published', 'seo_title' => Str::limit($name.' Insights | DevNiox', 70, ''), 'seo_description' => "Business-focused {$name} guidance from DevNiox.", 'created_at' => $this->now, 'updated_at' => $this->now]);
            }
            foreach ($topics as $i => [$title, $category]) {
                $summary = "A practical guide to {$title}, focused on ownership, operating risk, and measurable outcomes.";
                $body = $summary."\n\nStart with operating evidence\n\nDocument users, the current workflow, exceptions, repeated work, and decisions affected.\n\nDesign ownership and controls\n\nDefine who creates, reviews, approves, corrects, and reports each important record. Include failure handling before automation.\n\nRelease a coherent first outcome\n\nPrioritise a complete workflow users can adopt, measure the result, and use production evidence to decide what follows.\n\nThe DevNiox approach\n\nWe connect product decisions to the operating model so software remains useful after launch.";
                $image = $this->media['knowledge-banner'] ?? null;
                $id = DB::table('blog_posts')->insertGetId(['blog_category_id' => $categories[$category], 'author_id' => $this->userId, 'updated_by' => $this->userId, 'title' => $title, 'slug' => Str::slug($title), 'status' => 'published', 'is_featured' => $i < 3, 'published_at' => $this->now->copy()->subDays(($i + 1) * 5), 'reading_time' => 6 + ($i % 4), 'display_order' => $i, 'views_count' => 380 + ($i * 137), 'featured_image_path' => $image['path'] ?? null, 'social_image_path' => $image['path'] ?? null, 'summary' => $summary, 'body' => $body, 'created_at' => $this->now, 'updated_at' => $this->now]);
                DB::table('blog_post_seo')->insert(['blog_post_id' => $id, 'meta_title' => Str::limit($title.' | DevNiox', 70, ''), 'meta_description' => Str::limit($summary, 160, ''), 'meta_keywords' => Str::lower($category.', business software, DevNiox'), 'open_graph_image_path' => $image['path'] ?? null, 'is_indexable' => 1, 'created_at' => $this->now, 'updated_at' => $this->now]);
                DB::table('blog_post_faqs')->insert(['blog_post_id' => $id, 'question' => 'What should a business do first?', 'answer' => 'Document the current workflow, accountable owners, exceptions, and measurable result that must improve.', 'sort_order' => 0, 'created_at' => $this->now, 'updated_at' => $this->now]);
                if ($image) {
                    $this->usage($image['id'], 'App\\Models\\BlogPost', $id, 'featured_image_path');
                }
            }
        });
        $this->inserted['Blog articles'] = count($topics);
    }

    private function seedCommunication(): void
    {
        if (DB::table('leads')->exists()) {
            $this->skipped[] = 'Communication and leads (leads table is not empty)';

            return;
        }
        $people = [['contact', 'replied', 'medium', 'Nusrat Jahan', 'Meridian Distribution', 'nusrat@meridian.example', 'Product catalogue and warehouse integration'], ['contact', 'closed', 'low', 'Arif Hossain', 'BrightPath Academy', 'arif@brightpath.example', 'School ERP migration questions'], ['contact', 'new', 'high', 'Tanvir Ahmed', 'Dhaka Fresh Foods', 'tanvir@freshfoods.example', 'Inventory visibility across outlets'], ['contact', 'viewed', 'medium', 'Sadia Rahman', 'CarePlus Diagnostics', 'sadia@careplus.example', 'Clinic workflow consultation'], ['demo', 'pending', 'high', 'Mahmudul Hasan', 'CityLink ISP', 'mahmud@citylink.example', 'ISP Billing demonstration'], ['demo', 'confirmed', 'medium', 'Farzana Islam', 'North Star School', 'farzana@northstar.example', 'School ERP demonstration'], ['demo', 'completed', 'medium', 'Rezaul Karim', 'Cafe District', 'rezaul@cafedistrict.example', 'Restaurant QR and POS demonstration'], ['demo', 'cancelled', 'low', 'Maliha Noor', 'Urban Cart', 'maliha@urbancart.example', 'E-commerce platform demonstration'], ['quote', 'quoted', 'high', 'Imran Chowdhury', 'Delta Engineering', 'imran@delta.example', 'Manufacturing inventory and work orders'], ['quote', 'negotiation', 'urgent', 'Shamima Akter', 'Swift Haul Logistics', 'shamima@swifthaul.example', 'Courier operations platform'], ['quote', 'accepted', 'high', 'Fahim Kabir', 'Prime Wholesale', 'fahim@primewholesale.example', 'Inventory and order management'], ['quote', 'converted', 'medium', 'Raisa Sultana', 'PeopleFirst Services', 'raisa@peoplefirst.example', 'HR and payroll implementation']];
        DB::transaction(function () use ($people) {
            $products = DB::table('products')->pluck('id')->values();
            $services = DB::table('services')->pluck('id')->values();
            foreach ($people as $i => [$type, $status, $priority, $name, $company, $email, $subject]) {
                $submitted = $this->now->copy()->subDays(18 - $i);
                $lead = DB::table('leads')->insertGetId(['type' => $type, 'status' => $status, 'priority' => $priority, 'name' => $name, 'company' => $company, 'email' => $email, 'phone' => '+880 171'.str_pad((string) (1000000 + $i * 2719), 7, '0', STR_PAD_LEFT), 'assigned_to' => $i % 3 ? $this->userId : null, 'ip_address' => '203.0.113.'.($i + 10), 'user_agent' => 'Mozilla/5.0 DevNiox demonstration inquiry', 'submitted_at' => $submitted, 'read_at' => $status === 'new' ? null : $submitted->copy()->addHours(2), 'replied_at' => in_array($status, ['replied', 'closed', 'quoted', 'negotiation', 'accepted', 'converted']) ? $submitted->copy()->addDay() : null, 'closed_at' => $status === 'closed' ? $submitted->copy()->addDays(3) : null, 'converted_at' => $status === 'converted' ? $submitted->copy()->addDays(5) : null, 'created_at' => $submitted, 'updated_at' => $submitted]);
                if ($type === 'contact') {
                    DB::table('contact_messages')->insert(['lead_id' => $lead, 'subject' => $subject, 'message' => "We are evaluating {$subject}. Please advise what information is required to assess fit, migration, timeline, and scope.", 'created_at' => $submitted, 'updated_at' => $submitted]);
                } elseif ($type === 'demo') {
                    DB::table('demo_requests')->insert(['lead_id' => $lead, 'item_type' => 'product', 'product_id' => $products[($i - 4) % max(1, $products->count())] ?? null, 'preferred_date' => $this->now->copy()->addDays($i + 2)->toDateString(), 'preferred_time' => '11:00', 'meeting_type' => $i % 2 ? 'online' : 'offline', 'message' => $subject, 'created_at' => $submitted, 'updated_at' => $submitted]);
                } else {
                    DB::table('quote_requests')->insert(['lead_id' => $lead, 'business_type' => 'Established business', 'item_type' => 'service', 'service_id' => $services[($i - 8) % max(1, $services->count())] ?? null, 'budget' => 'BDT '.number_format(250000 + ($i * 75000)), 'timeline' => (8 + $i).'–'.(12 + $i).' weeks', 'requirement_details' => $subject.'. Include discovery, migration, training, deployment, warranty, and support.', 'created_at' => $submitted, 'updated_at' => $submitted]);
                }
                DB::table('lead_status_histories')->insert(['lead_id' => $lead, 'changed_by' => $this->userId, 'from_status' => null, 'to_status' => $status, 'changed_at' => $submitted]);
                DB::table('lead_events')->insert(['lead_id' => $lead, 'actor_id' => $this->userId, 'event_type' => 'inquiry_received', 'description' => "{$company} submitted a {$type} inquiry.", 'occurred_at' => $submitted]);
                if ($i % 3 === 0) {
                    DB::table('lead_notes')->insert(['lead_id' => $lead, 'author_id' => $this->userId, 'note' => 'Confirm decision makers, current workflow, data sources, deadline, and acceptance criteria.', 'created_at' => $submitted->copy()->addHours(3), 'updated_at' => $submitted->copy()->addHours(3)]);
                }
                if (in_array($status, ['replied', 'closed', 'quoted', 'negotiation', 'accepted', 'converted'])) {
                    DB::table('communication_replies')->insert(['lead_id' => $lead, 'administrator_id' => $this->userId, 'direction' => 'outgoing', 'subject' => 'Next steps for '.$subject, 'message' => 'Thank you. We recommend a discovery call to confirm users, workflow, data, integrations, and the first measurable outcome.', 'replied_at' => $submitted->copy()->addDay(), 'created_at' => $submitted->copy()->addDay(), 'updated_at' => $submitted->copy()->addDay()]);
                }
            }
        });
        $this->inserted['Communication and leads'] = count($people);
    }

    private function seedOrders(): void
    {
        if (DB::table('orders')->exists()) {
            $this->skipped[] = 'Orders (not empty)';

            return;
        }
        $statuses = ['pending', 'requirement_gathering', 'development', 'qa_testing', 'client_review', 'revision', 'delivered', 'completed', 'cancelled', 'ui_ux_design'];
        $customers = [['Prime Wholesale', 'Fahim Kabir'], ['CityLink ISP', 'Mahmudul Hasan'], ['North Star School', 'Farzana Islam'], ['Cafe District', 'Rezaul Karim'], ['Delta Engineering', 'Imran Chowdhury'], ['CarePlus Diagnostics', 'Sadia Rahman'], ['Swift Haul Logistics', 'Shamima Akter'], ['PeopleFirst Services', 'Raisa Sultana'], ['Urban Cart', 'Maliha Noor'], ['Meridian Distribution', 'Nusrat Jahan']];
        DB::transaction(function () use ($statuses, $customers) {
            foreach ($customers as $i => [$company, $customer]) {
                $total = 180000 + ($i * 45000);
                $paid = in_array($statuses[$i], ['completed', 'delivered']) ? $total : (in_array($statuses[$i], ['development', 'qa_testing', 'client_review', 'revision']) ? $total * .5 : 0);
                $order = DB::table('orders')->insertGetId(['order_number' => 'DNX-'.$this->now->year.'-'.str_pad((string) ($i + 1), 5, '0', STR_PAD_LEFT), 'customer_name' => $customer, 'company_name' => $company, 'email' => Str::slug($customer, '.').'@'.Str::slug($company, '').'.example', 'phone' => '+880 1711'.str_pad((string) (100000 + $i), 6, '0', STR_PAD_LEFT), 'order_date' => $this->now->copy()->subDays(45 - $i * 3)->toDateString(), 'expected_delivery_date' => $this->now->copy()->addDays(20 + $i * 4)->toDateString(), 'priority' => $i % 4 === 0 ? 'high' : 'medium', 'status' => $statuses[$i], 'source' => $i % 3 === 0 ? 'lead' : 'direct', 'total_amount' => $total, 'discount' => 0, 'final_amount' => $total, 'paid_amount' => $paid, 'due_amount' => $total - $paid, 'payment_status' => $paid === 0 ? 'unpaid' : ($paid === $total ? 'paid' : 'partial'), 'payment_method' => $paid ? 'bank' : null, 'created_by' => $this->userId, 'updated_by' => $this->userId, 'created_at' => $this->now, 'updated_at' => $this->now]);
                foreach ([['Software implementation', 'software', $total * .8], ['Deployment and onboarding', 'custom_development', $total * .2]] as $j => [$name, $type, $price]) {
                    DB::table('order_items')->insert(['order_id' => $order, 'name' => $name, 'type' => $type, 'quantity' => 1, 'unit_price' => $price, 'discount' => 0, 'total' => $price, 'sort_order' => $j, 'created_at' => $this->now, 'updated_at' => $this->now]);
                }
                DB::table('order_notes')->insert(['order_id' => $order, 'author_id' => $this->userId, 'note' => 'Confirm scope owners, migration responsibilities, acceptance process, deployment window, and support contacts.', 'created_at' => $this->now, 'updated_at' => $this->now]);
                DB::table('order_events')->insert(['order_id' => $order, 'actor_id' => $this->userId, 'event_type' => 'order_created', 'description' => 'Commercial order created and assigned for delivery planning.', 'new_values' => json_encode(['status' => $statuses[$i]]), 'occurred_at' => $this->now->copy()->subDays(45 - $i * 3)]);
                if (isset($this->media['brochure']) && $i < 3) {
                    $media = $this->media['brochure'];
                    $attachment = DB::table('order_attachments')->insertGetId(['order_id' => $order, 'uploaded_by' => $this->userId, 'media_asset_id' => $media['id'], 'label' => 'DevNiox product portfolio', 'file_path' => $media['path'], 'original_name' => 'devniox-product-portfolio.pdf', 'mime_type' => 'application/pdf', 'file_size' => Storage::disk('local')->size($media['path']), 'created_at' => $this->now, 'updated_at' => $this->now]);
                    $this->usage($media['id'], 'App\\Models\\OrderAttachment', $attachment, 'attachment');
                }
            }
            $converted = DB::table('leads')->where('status', 'converted')->first();
            if ($converted) {
                $orderId = DB::table('orders')->orderBy('id')->value('id');
                DB::table('leads')->where('id', $converted->id)->update(['converted_order_id' => $orderId]);
                DB::table('orders')->where('id', $orderId)->update(['lead_id' => $converted->id, 'source' => 'lead']);
            }
        });
        $this->inserted['Orders'] = count($customers);
    }

    private function seedFinance(): void
    {
        if (DB::table('finance_transactions')->exists() || DB::table('income_categories')->exists() || DB::table('expense_categories')->exists()) {
            $this->skipped[] = 'Finance (transaction or category table is not empty)';

            return;
        }
        $incomeNames = ['Software Sales', 'Hosting', 'Maintenance', 'Domain', 'Consultation'];
        $expenseNames = ['Office Rent', 'Internet', 'Marketing', 'Salary', 'Software Subscription', 'Hosting Cost'];
        DB::transaction(function () use ($incomeNames, $expenseNames) {
            $income = $expense = [];
            foreach ($incomeNames as $name) {
                $income[] = DB::table('income_categories')->insertGetId(['name' => $name, 'slug' => Str::slug($name), 'description' => "Revenue from {$name}.", 'active' => 1, 'created_at' => $this->now, 'updated_at' => $this->now]);
            }
            foreach ($expenseNames as $name) {
                $expense[] = DB::table('expense_categories')->insertGetId(['name' => $name, 'slug' => Str::slug($name), 'description' => "Operating cost for {$name}.", 'active' => 1, 'created_at' => $this->now, 'updated_at' => $this->now]);
            }
            for ($i = 0; $i < 30; $i++) {
                $isIncome = $i % 3 !== 0;
                $categoryId = $isIncome ? $income[$i % count($income)] : $expense[$i % count($expense)];
                $categoryName = $isIncome ? $incomeNames[$i % count($incomeNames)] : $expenseNames[$i % count($expenseNames)];
                $amount = $isIncome ? 35000 + (($i % 7) * 28000) : 12000 + (($i % 6) * 14500);
                DB::table('finance_transactions')->insert(['transaction_number' => 'TXN-'.$this->now->format('ym').'-'.str_pad((string) ($i + 1), 5, '0', STR_PAD_LEFT), 'type' => $isIncome ? 'income' : 'expense', 'source' => $isIncome && $i % 5 === 0 ? 'maintenance' : 'manual', 'reference' => 'DNX-FIN-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT), 'income_category_id' => $isIncome ? $categoryId : null, 'expense_category_id' => $isIncome ? null : $categoryId, 'title' => $categoryName.' — '.($isIncome ? 'client receipt' : 'operating payment'), 'description' => $isIncome ? 'Received against an approved commercial invoice.' : 'Approved operating expense with supporting documentation.', 'amount' => $amount, 'payment_method' => ['bank', 'mobile_banking', 'cash'][$i % 3], 'transaction_date' => $this->now->copy()->subDays(90 - $i * 3)->toDateString(), 'status' => $i % 11 === 0 ? 'pending' : 'completed', 'created_by' => $this->userId, 'updated_by' => $this->userId, 'created_at' => $this->now, 'updated_at' => $this->now]);
            }
        });
        $this->inserted['Finance categories'] = count($incomeNames) + count($expenseNames);
        $this->inserted['Finance transactions'] = 30;
    }

    private function seedNotifications(): void
    {
        if (DB::table('notifications')->exists()) {
            $this->skipped[] = 'Notifications (not empty)';

            return;
        }
        $titles = ['New contact message', 'Demo request awaiting confirmation', 'Quote request received', 'High-priority lead assigned', 'Client replied to proposal', 'Order moved to development', 'QA review due today', 'Client review feedback received', 'Order marked delivered', 'Outstanding payment reminder', 'Monthly finance summary ready', 'Media storage review', 'Scheduled article published', 'Website settings reviewed', 'Maintenance window completed'];
        foreach ($titles as $i => $title) {
            DB::table('notifications')->insert(['user_id' => $this->userId, 'type' => 'demo.'.Str::slug($title, '_'), 'title' => $title, 'message' => 'A DevNiox demonstration event requires review in the appropriate admin module.', 'action_url' => $i < 5 ? '/admin/leads' : '/admin', 'read_at' => $i > 9 ? $this->now->copy()->subHours($i) : null, 'created_at' => $this->now->copy()->subHours(15 - $i), 'updated_at' => $this->now->copy()->subHours(15 - $i)]);
        }
        $this->inserted['Notifications'] = count($titles);
    }

    private function usage(int $asset, string $type, int $id, string $field): void
    {
        DB::table('media_usages')->insertOrIgnore(['media_asset_id' => $asset, 'usable_type' => $type, 'usable_id' => $id, 'field' => $field, 'created_at' => $this->now, 'updated_at' => $this->now]);
    }

    private function png(string $accent): string
    {
        [$red, $green, $blue] = sscanf(ltrim($accent, '#'), '%02x%02x%02x');
        $width = 1200;
        $height = 675;
        $pixels = '';
        for ($y = 0; $y < $height; $y++) {
            $pixels .= "\0";
            for ($x = 0; $x < $width; $x++) {
                $panel = $x > 120 && $x < 1080 && $y > 90 && $y < 585;
                $line = $panel && (($y - 90) % 72 < 2 || ($x - 120) % 210 < 2);
                $factor = $panel ? .24 : .12;
                $pixels .= chr((int) min(255, 12 + $red * $factor + ($line ? 45 : 0))).chr((int) min(255, 20 + $green * $factor + ($line ? 45 : 0))).chr((int) min(255, 36 + $blue * $factor + ($line ? 45 : 0)));
            }
        }
        $chunk = fn (string $type, string $data): string => pack('N', strlen($data)).$type.$data.pack('N', crc32($type.$data));

        return "\x89PNG\r\n\x1a\n".$chunk('IHDR', pack('NNCCCCC', $width, $height, 8, 2, 0, 0, 0)).$chunk('IDAT', gzcompress($pixels, 9)).$chunk('IEND', '');
    }

    private function page(string $key, string $title, string $description): array
    {
        return ['key' => $key, 'status' => 'published', 'meta_title' => $title, 'meta_description' => $description, 'is_indexable' => 1, 'updated_by' => $this->userId, 'created_at' => $this->now, 'updated_at' => $this->now];
    }
}

