<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_home_content', function (Blueprint $table) {
            foreach ([
                'intro_label' => 'What DevNiox builds',
                'products_label' => 'Flagship software',
                'products_link_text' => 'Explore all products',
                'services_label' => 'Enterprise delivery',
                'services_link_text' => 'View capabilities',
                'portfolio_label' => 'Proven delivery',
                'portfolio_link_text' => 'See our work',
                'articles_label' => 'Knowledge center',
                'articles_link_text' => 'Read all articles',
                'why_label' => 'Why DevNiox',
                'why_title' => 'Decisions that survive production, growth, and handover.',
                'why_description' => 'The work is organised around business rules and software ownership—not a queue of disconnected feature requests.',
                'industries_label' => 'Industries we serve',
                'industries_title' => 'Operations with real rules, roles, and consequences.',
                'industries_description' => 'Our strongest fit is where software coordinates people, transactions, inventory, service delivery, or management reporting.',
                'process_label' => 'Development process',
                'process_title' => 'From operating problem to maintainable product.',
                'process_description' => 'Each stage produces decisions and evidence that the next stage can rely on.',
                'technology_label' => 'Technology we use',
                'technology_title' => 'Conventional foundations. Deliberate intelligence.',
                'technology_description' => 'Laravel, PHP, relational data, accessible web interfaces, automated testing, and carefully bounded AI integrations form a stack that can be operated and extended.',
                'faq_label' => 'Frequently asked questions',
                'faq_title' => 'What business and technical teams usually ask first.',
                'hero_showcase_label' => 'Enterprise software interface preview',
                'hero_window_title' => 'DevNiox Business Suite',
                'hero_dashboard_label' => 'Operations overview',
                'hero_dashboard_title' => 'Business dashboard',
                'hero_metric_one_label' => 'Software products',
                'hero_metric_one_status' => 'Release managed',
                'hero_metric_two_label' => 'Portfolio delivery',
                'hero_metric_two_status' => 'Outcome measured',
                'hero_metric_three_label' => 'Enterprise services',
                'hero_metric_three_status' => 'Outcome scoped',
                'hero_chart_title' => 'Performance',
                'hero_chart_label' => 'Live overview',
                'hero_floating_one_label' => 'Product systems',
                'hero_floating_one_title' => 'Release managed',
                'hero_floating_two_label' => 'AI assistant',
                'hero_floating_two_title' => 'Automation active',
                'hero_floating_three_label' => 'System status',
                'hero_floating_three_title' => 'Production monitored',
            ] as $column => $default) {
                if (! Schema::hasColumn('cms_home_content', $column)) {
                    $table->text($column)->nullable()->after('intro_image_path');
                }
            }
            foreach (['industries_enabled', 'process_enabled', 'technology_enabled', 'faq_enabled'] as $column) {
                if (! Schema::hasColumn('cms_home_content', $column)) {
                    $table->boolean($column)->default(true)->after('intro_image_path');
                }
            }
        });

        foreach (['home_trust_items', 'home_industry_items', 'home_process_items', 'home_technology_items', 'home_faq_items'] as $name) {
            if (! Schema::hasTable($name)) {
                Schema::create($name, function (Blueprint $table) use ($name) {
                    $table->id();
                    $table->foreignId('cms_page_id')->constrained()->cascadeOnDelete();
                    $table->string('title', 180);
                    $table->text('description')->nullable();
                    $table->string('icon', 100)->nullable();
                    $table->unsignedInteger('sort_order')->default(0);
                    $table->timestamps();
                    $table->index(['cms_page_id', 'sort_order']);
                });
            }
        }

        DB::table('cms_home_content')->update([
            'intro_label' => 'What DevNiox builds',
            'products_label' => 'Flagship software',
            'products_link_text' => 'Explore all products',
            'services_label' => 'Enterprise delivery',
            'services_link_text' => 'View capabilities',
            'portfolio_label' => 'Proven delivery',
            'portfolio_link_text' => 'See our work',
            'articles_label' => 'Knowledge center',
            'articles_link_text' => 'Read all articles',
            'why_label' => 'Why DevNiox',
            'why_title' => 'Decisions that survive production, growth, and handover.',
            'why_description' => 'The work is organised around business rules and software ownership—not a queue of disconnected feature requests.',
            'industries_enabled' => true,
            'industries_label' => 'Industries we serve',
            'industries_title' => 'Operations with real rules, roles, and consequences.',
            'industries_description' => 'Our strongest fit is where software coordinates people, transactions, inventory, service delivery, or management reporting.',
            'process_enabled' => true,
            'process_label' => 'Development process',
            'process_title' => 'From operating problem to maintainable product.',
            'process_description' => 'Each stage produces decisions and evidence that the next stage can rely on.',
            'technology_enabled' => true,
            'technology_label' => 'Technology we use',
            'technology_title' => 'Conventional foundations. Deliberate intelligence.',
            'technology_description' => 'Laravel, PHP, relational data, accessible web interfaces, automated testing, and carefully bounded AI integrations form a stack that can be operated and extended.',
            'faq_enabled' => true,
            'faq_label' => 'Frequently asked questions',
            'faq_title' => 'What business and technical teams usually ask first.',
            'hero_showcase_label' => 'Enterprise software interface preview',
            'hero_window_title' => 'DevNiox Business Suite',
            'hero_dashboard_label' => 'Operations overview',
            'hero_dashboard_title' => 'Business dashboard',
            'hero_metric_one_label' => 'Software products',
            'hero_metric_one_status' => 'Release managed',
            'hero_metric_two_label' => 'Portfolio delivery',
            'hero_metric_two_status' => 'Outcome measured',
            'hero_metric_three_label' => 'Enterprise services',
            'hero_metric_three_status' => 'Outcome scoped',
            'hero_chart_title' => 'Performance',
            'hero_chart_label' => 'Live overview',
            'hero_floating_one_label' => 'Product systems',
            'hero_floating_one_title' => 'Release managed',
            'hero_floating_two_label' => 'AI assistant',
            'hero_floating_two_title' => 'Automation active',
            'hero_floating_three_label' => 'System status',
            'hero_floating_three_title' => 'Production monitored',
        ]);

        $homeIds = DB::table('cms_pages')->where('key', 'home')->pluck('id');
        foreach ($homeIds as $pageId) {
            $this->seedRows('home_trust_items', $pageId, [
                ['ERP and operations', null, 'check-circle-fill'],
                ['Order and finance workflows', null, 'check-circle-fill'],
                ['AI-assisted systems', null, 'check-circle-fill'],
                ['Long-term product ownership', null, 'check-circle-fill'],
            ]);
            $this->seedRows('home_industry_items', $pageId, [
                ['Retail & distribution', 'Orders, inventory visibility, customer records, purchasing, and financial control.', 'shop'],
                ['Hospitality & restaurants', 'QR ordering, menu operations, fulfilment, outlet workflows, and performance reporting.', 'cup-hot'],
                ['Professional operations', 'Clients, projects, approvals, documents, billing, and management information in one workflow.', 'building'],
                ['Growing businesses', 'Replace fragile spreadsheets and disconnected tools with controlled, measurable processes.', 'graph-up-arrow'],
            ]);
            $this->seedRows('home_process_items', $pageId, [
                ['Discover', 'Map the business objective, users, constraints, integrations, and measures of success.', null],
                ['Design', 'Define workflows, information architecture, technical boundaries, and delivery priorities.', null],
                ['Engineer', 'Build in reviewable increments with validation, testing, and deployment readiness included.', null],
                ['Operate', 'Measure adoption, resolve friction, and plan improvements against operational evidence.', null],
            ]);
            $this->seedRows('home_technology_items', $pageId, [
                ['Relational by design', 'Business data keeps explicit relationships, constraints, and ownership.', null],
                ['Web-native delivery', 'Responsive interfaces work across devices without unnecessary client-side complexity.', null],
                ['Automation with controls', 'AI and workflow automation support decisions while permissions and review remain clear.', null],
                ['Release-ready engineering', 'Validation, tests, deployment configuration, and recovery concerns are part of delivery.', null],
            ]);
            $this->seedRows('home_faq_items', $pageId, [
                ['Can we evaluate a product before committing?', 'Yes. A guided demo focuses on your users, current workflow, required controls, and deployment questions—not a generic feature tour.', null],
                ['When is custom software the right choice?', 'Custom development is appropriate when the workflow creates operational value and standard tools force costly workarounds, duplicated data, or weak control.', null],
                ['Can DevNiox integrate with existing systems?', 'Integration is assessed during discovery. We document data ownership, authentication, failure handling, and reconciliation needs before committing to an approach.', null],
                ['What information helps you prepare a useful proposal?', 'Share the business objective, primary users, current process, known integrations, timing constraints, and the result you need to measure.', null],
            ]);
        }
    }

    public function down(): void
    {
        foreach (['home_faq_items', 'home_technology_items', 'home_process_items', 'home_industry_items', 'home_trust_items'] as $name) {
            Schema::dropIfExists($name);
        }
    }

    private function seedRows(string $table, int $pageId, array $rows): void
    {
        if (DB::table($table)->where('cms_page_id', $pageId)->exists()) {
            return;
        }

        foreach ($rows as $index => [$title, $description, $icon]) {
            DB::table($table)->insert([
                'cms_page_id' => $pageId,
                'title' => $title,
                'description' => $description,
                'icon' => $icon,
                'sort_order' => $index,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};