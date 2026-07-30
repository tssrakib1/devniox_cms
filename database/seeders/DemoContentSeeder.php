<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $now = now();
            $uid = DB::table('users')->value('id');
            $pc = DB::table('product_categories')->updateOrInsert(['slug' => 'business-software'], ['name' => 'Business Software', 'description' => 'Operational systems for commerce, finance, people, and industry workflows.', 'icon' => 'grid', 'sort_order' => 0, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now]);
            $pcid = DB::table('product_categories')->where('slug', 'business-software')->value('id');
            $products = [['Order Management System', 'order-management-system', 'Centralise orders, fulfilment, returns, customer context, and delivery status across sales channels.', ['Order Capture', 'Fulfilment', 'Returns', 'Reporting']], ['Restaurant QR & POS', 'restaurant-qr-pos', 'Connect digital menus, QR ordering, counter sales, kitchen routing, payments, and outlet reporting.', ['QR Menu', 'POS', 'Kitchen Display', 'Outlet Reports']], ['ISP Billing & CRM', 'isp-billing-crm', 'Manage subscribers, packages, recurring invoices, collections, tickets, and service history in one record.', ['Subscribers', 'Billing', 'Collections', 'Support Desk']], ['Money Management', 'money-management', 'Control accounts, income, expenses, budgets, approvals, obligations, and financial reporting.', ['Accounts', 'Transactions', 'Budgets', 'Reports']], ['Inventory Management', 'inventory-management', 'Track items, purchasing, warehouses, transfers, adjustments, reorder levels, and stock history.', ['Catalogue', 'Purchasing', 'Warehouses', 'Stock Ledger']], ['HR & Payroll', 'hr-payroll', 'Unify employee records, attendance, leave, payroll inputs, deductions, and workforce reporting.', ['Employees', 'Attendance', 'Leave', 'Payroll']], ['School Management', 'school-management', 'Coordinate admissions, students, attendance, fees, examinations, guardians, and academic records.', ['Admissions', 'Students', 'Fees', 'Examinations']], ['Custom ERP Suite', 'custom-erp-suite', 'Configure sales, purchasing, inventory, finance, people, approvals, and reporting around one operating model.', ['Sales', 'Procurement', 'Inventory', 'Finance']], ['Clinic Operations', 'clinic-operations', 'Coordinate appointments, patient records, services, billing, pharmacy requests, and clinic reporting.', ['Appointments', 'Patients', 'Billing', 'Reports']], ['Manufacturing ERP', 'manufacturing-erp-product', 'Connect demand, materials, work orders, production stages, inventory, costing, and quality control.', ['Planning', 'Materials', 'Production', 'Costing']]];
            foreach ($products as $i => [$name,$slug,$desc,$mods]) {
                DB::table('products')->updateOrInsert(['slug' => $slug], ['product_category_id' => $pcid, 'created_by' => $uid, 'updated_by' => $uid, 'name' => $name, 'version' => '1.'.($i + 1).'.0', 'status' => 'published', 'is_featured' => $i < 4, 'display_order' => $i, 'short_description' => $desc, 'full_description' => $desc.' It replaces fragmented spreadsheets with accountable roles, current status, audit history, and management visibility. Implementation maps users, approvals, exceptions, integrations, and reporting before configuration.', 'published_at' => $now, 'created_at' => $now, 'updated_at' => $now]);
                $id = DB::table('products')->where('slug', $slug)->value('id');
                foreach ($mods as $j => $m) {
                    DB::table('product_modules')->updateOrInsert(['product_id' => $id, 'name' => $m], ['description' => $m.' records, controls, and reports are managed in one traceable workflow.', 'sort_order' => $j, 'created_at' => $now, 'updated_at' => $now]);
                }foreach ([['Clear ownership', 'Make roles, approvals, and exceptions explicit.'], ['Current visibility', 'Give teams one reliable operational status.'], ['Controlled growth', 'Extend modules without fragmenting core data.']] as $j => [$t,$d]) {
                    DB::table('product_highlights')->updateOrInsert(['product_id' => $id, 'title' => $t], ['description' => $d, 'icon' => 'check2-circle', 'sort_order' => $j, 'created_at' => $now, 'updated_at' => $now]);
                }foreach ([['Role-based access', 'Limit actions and data to operational responsibility.'], ['Audit history', 'Retain accountable changes for review.'], ['Management reports', 'Turn current records into useful decisions.']] as $j => [$t,$d]) {
                    DB::table('product_features')->updateOrInsert(['product_id' => $id, 'title' => $t], ['description' => $d, 'icon' => 'shield-check', 'sort_order' => $j, 'created_at' => $now, 'updated_at' => $now]);
                }foreach ([['Can roles be configured?', 'Yes. Discovery maps users, permissions, approvals, and exceptions before configuration.'], ['Can existing data be imported?', 'Migration is assessed for structure, quality, ownership, and reconciliation before production cutover.']] as $j => [$q,$a]) {
                    DB::table('product_faqs')->updateOrInsert(['product_id' => $id, 'question' => $q], ['answer' => $a, 'sort_order' => $j, 'created_at' => $now, 'updated_at' => $now]);
                }foreach ([['Starter', 49, 'monthly', 0], ['Business', 149, 'monthly', 1], ['Enterprise', null, 'custom', 0]] as $j => [$pn,$pr,$bt,$hi]) {
                    DB::table('product_pricing_plans')->updateOrInsert(['product_id' => $id, 'name' => $pn], ['price' => $pr, 'currency' => 'USD', 'billing_type' => $bt, 'description' => $pn.' operating scope with onboarding and support options.', 'is_highlighted' => $hi, 'sort_order' => $j, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now]);
                }DB::table('product_seo')->updateOrInsert(['product_id' => $id], ['meta_title' => Str::limit($name.' | DevNiox', 70, ''), 'meta_description' => Str::limit($desc, 160, ''), 'keywords' => Str::lower($name.', business software, DevNiox'), 'is_indexable' => 1, 'created_at' => $now, 'updated_at' => $now]);
            }
            $sc = DB::table('service_categories');
            $sc->updateOrInsert(['slug' => 'engineering-services'], ['name' => 'Engineering Services', 'description' => 'Product, ERP, integration, experience, and deployment services.', 'icon' => 'code-square', 'sort_order' => 0, 'status' => 'published', 'created_at' => $now, 'updated_at' => $now]);
            $scid = $sc->where('slug', 'engineering-services')->value('id');
            $services = [['Custom Software Development', 'custom-software-development'], ['ERP Development', 'erp-development'], ['SaaS Product Development', 'saas-product-development'], ['Mobile App Development', 'mobile-app-development'], ['API Integration', 'api-integration'], ['AI Automation', 'ai-automation'], ['UI/UX Design', 'ui-ux-design'], ['Cloud Deployment', 'cloud-deployment']];
            foreach ($services as $i => [$name,$slug]) {
                DB::table('services')->updateOrInsert(['slug' => $slug], ['service_category_id' => $scid, 'created_by' => $uid, 'updated_by' => $uid, 'name' => $name, 'status' => 'published', 'is_featured' => $i < 4, 'display_order' => $i, 'short_description' => $name.' organised around business constraints, accountable delivery, and production ownership.', 'full_description' => 'We begin with users, workflows, control gaps, integrations, and measurable outcomes before defining architecture and scope. Delivery moves through discovery, design, implementation, acceptance, deployment, and handover.', 'published_at' => $now, 'created_at' => $now, 'updated_at' => $now]);
                $id = DB::table('services')->where('slug', $slug)->value('id');
                foreach (['Discovery', 'Architecture', 'Delivery', 'Production'] as $j => $t) {
                    DB::table('service_process_steps')->updateOrInsert(['service_id' => $id, 'step_number' => $j + 1], ['title' => $t, 'description' => 'Reviewable '.$t.' work aligned to the approved outcome and acceptance criteria.', 'sort_order' => $j, 'created_at' => $now, 'updated_at' => $now]);
                }foreach (['Solution brief', 'Working release', 'Deployment runbook'] as $j => $t) {
                    DB::table('service_deliverables')->updateOrInsert(['service_id' => $id, 'title' => $t], ['description' => 'A reviewable deliverable matched to the engagement scope.', 'sort_order' => $j, 'created_at' => $now, 'updated_at' => $now]);
                }foreach (['Laravel', 'PHP', 'MySQL', 'REST APIs'] as $j => $t) {
                    DB::table('service_technologies')->updateOrInsert(['service_id' => $id, 'name' => $t], ['icon' => 'code-slash', 'sort_order' => $j, 'created_at' => $now, 'updated_at' => $now]);
                }foreach ([['How long does delivery take?', 'Timing is confirmed after scope, dependencies, integrations, and acceptance criteria are understood.'], ['Can you work with existing systems?', 'Yes. We assess architecture, data, deployment, tests, and operational risk before proposing changes.']] as $j => [$q,$a]) {
                    DB::table('service_faqs')->updateOrInsert(['service_id' => $id, 'question' => $q], ['answer' => $a, 'sort_order' => $j, 'created_at' => $now, 'updated_at' => $now]);
                }
            }
            $this->portfolio($uid, $now);
            $this->blog($uid, $now);
        });
    }

    private function portfolio($uid, $now): void
    {
        $c = ['name' => 'Business Systems', 'slug' => 'business-systems', 'description' => 'Representative operational software case studies.', 'icon' => 'window-stack', 'sort_order' => 0, 'status' => 'published', 'created_at' => $now, 'updated_at' => $now];
        DB::table('portfolio_categories')->updateOrInsert(['slug' => 'business-systems'], $c);
        $cid = DB::table('portfolio_categories')->where('slug', 'business-systems')->value('id');
        $rows = [['Garments ERP', 'Apparel Manufacturing'], ['Restaurant POS', 'Hospitality'], ['ISP CRM', 'Telecommunications'], ['Wholesale Inventory', 'Distribution'], ['School ERP', 'Education'], ['Clinic Management', 'Healthcare'], ['Courier Management', 'Logistics'], ['Accounting System', 'Professional Services'], ['HR Platform', 'Multi-branch Services'], ['Manufacturing ERP', 'Manufacturing'], ['Warehouse Control Platform', 'Warehousing'], ['Field Service Operations', 'Technical Services']];
        foreach ($rows as $i => [$name,$industry]) {
            $slug = Str::slug($name);
            DB::table('portfolio_projects')->updateOrInsert(['slug' => $slug], ['portfolio_category_id' => $cid, 'created_by' => $uid, 'updated_by' => $uid, 'name' => $name, 'client_name' => 'Representative DevNiox implementation', 'industry' => $industry, 'completion_date' => now()->subMonths(10 - $i), 'status' => 'published', 'is_featured' => $i < 4, 'display_order' => $i, 'short_description' => 'A controlled '.$industry.' platform connecting daily workflow, accountable data, and management reporting.', 'full_description' => 'The engagement replaced disconnected records and manual coordination with explicit roles, shared status, and reviewable operational reporting.', 'published_at' => $now, 'created_at' => $now, 'updated_at' => $now]);
            $id = DB::table('portfolio_projects')->where('slug', $slug)->value('id');
            foreach ([['objectives', 'Challenge', 'Teams worked from disconnected records with delayed visibility.'], ['solutions', 'Solution', 'DevNiox created one role-based workflow with current status and reporting.'], ['results', 'Business impact', 'The organisation reduced repeated entry and improved operational accountability.']] as [$table,$t,$d]) {
                DB::table('portfolio_project_'.$table)->updateOrInsert(['portfolio_project_id' => $id, 'title' => $t], ['description' => $d, 'sort_order' => 0, 'created_at' => $now, 'updated_at' => $now]);
            }foreach (['Laravel 12', 'PHP 8.3', 'MySQL', 'Bootstrap 5'] as $j => $t) {
                DB::table('portfolio_project_technologies')->updateOrInsert(['portfolio_project_id' => $id, 'name' => $t], ['icon' => 'code-slash', 'sort_order' => $j, 'created_at' => $now, 'updated_at' => $now]);
            }
        }
    }

    private function blog($uid, $now): void
    {
        DB::table('blog_categories')->updateOrInsert(['slug' => 'software-decisions'], ['name' => 'Software Decisions', 'description' => 'Practical guidance for operational technology decisions.', 'icon' => 'journal-text', 'sort_order' => 0, 'status' => 'published', 'created_at' => $now, 'updated_at' => $now]);
        $cid = DB::table('blog_categories')->where('slug', 'software-decisions')->value('id');
        $topics = ['How ERP Improves Business Operations', 'Choosing Custom Software or SaaS', 'Where AI Automation Creates Value', 'Restaurant QR Ordering Beyond the Menu', 'Inventory Controls That Prevent Stock Confusion', 'Laravel for Enterprise Development', 'Digital Transformation Starts With a Constraint', 'A Roadmap for Business Automation', 'Preparing for ERP Discovery', 'API Integration Requires Ownership', 'Building a SaaS MVP Without a Rewrite', 'What to Expect From Software Handover', 'Cloud Migration Without Operational Downtime', 'Inventory Forecasting for Growing Distributors', 'Why Custom Software Projects Lose Business Alignment'];
        foreach ($topics as $i => $title) {
            $summary = 'A practical decision guide connecting '.$title.' to workflow ownership, operating risk, and measurable business outcomes.';
            $body = $summary."\n\nStart with evidence\n\nDocument users, current work, exceptions, and the result that must improve before selecting technology.\n\nDesign the controls\n\nDefine ownership, permissions, approvals, failures, and reconciliation before automating the happy path.\n\nRelease and measure\n\nShip a coherent first release, observe adoption, and use operational evidence to prioritise what follows.";
            DB::table('blog_posts')->updateOrInsert(['slug' => Str::slug($title)], ['blog_category_id' => $cid, 'author_id' => $uid, 'updated_by' => $uid, 'title' => $title, 'status' => 'published', 'is_featured' => $i < 3, 'published_at' => now()->subDays(12 - $i), 'reading_time' => 5 + $i % 3, 'display_order' => $i, 'views_count' => 200 + $i * 73, 'summary' => $summary, 'body' => $body, 'created_at' => $now, 'updated_at' => $now]);
        }
    }
}
