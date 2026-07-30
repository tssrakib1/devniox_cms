<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_categories', function (Blueprint $t) {
            $t->id();
            $t->string('name', 120);
            $t->string('slug', 140)->unique();
            $t->text('description')->nullable();
            $t->string('icon', 100)->nullable();
            $t->unsignedInteger('sort_order')->default(0)->index();
            $t->string('status', 20)->default('draft')->index();
            $t->string('seo_title', 70)->nullable();
            $t->string('seo_description', 160)->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('portfolio_projects', function (Blueprint $t) {
            $t->id();
            $t->foreignId('portfolio_category_id')->constrained()->restrictOnDelete();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $t->string('name', 180);
            $t->string('slug', 200)->unique();
            $t->string('client_name', 180)->nullable();
            $t->string('industry', 160)->nullable()->index();
            $t->date('completion_date');
            $t->string('status', 20)->default('draft')->index();
            $t->boolean('is_featured')->default(false)->index();
            $t->unsignedInteger('display_order')->default(0);
            $t->string('thumbnail_path')->nullable();
            $t->string('cover_image_path')->nullable();
            $t->string('short_description', 300);
            $t->longText('full_description');
            $t->timestamp('published_at')->nullable()->index();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['portfolio_category_id', 'status', 'display_order'], 'portfolio_category_status_order_index');
        });
        foreach (['objectives', 'solutions', 'results'] as $n) {
            Schema::create('portfolio_project_'.$n, function (Blueprint $t) use ($n) {
                $t->id();
                $t->foreignId('portfolio_project_id')->constrained()->cascadeOnDelete();
                $t->string('title', 180);
                $t->text('description');
                $t->unsignedInteger('sort_order')->default(0);
                $t->timestamps();
                $t->index(['portfolio_project_id', 'sort_order'], "portfolio_{$n}_sort_index");
            });
        }Schema::create('portfolio_project_features', function (Blueprint $t) {
            $t->id();
            $t->foreignId('portfolio_project_id')->constrained()->cascadeOnDelete();
            $t->string('title', 180);
            $t->text('description');
            $t->string('icon', 100)->nullable();
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
            $t->index(['portfolio_project_id', 'sort_order'], 'portfolio_features_sort_index');
        });
        Schema::create('portfolio_project_gallery_images', function (Blueprint $t) {
            $t->id();
            $t->foreignId('portfolio_project_id')->constrained()->cascadeOnDelete();
            $t->string('image_path');
            $t->string('alt_text', 180)->nullable();
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
            $t->index(['portfolio_project_id', 'sort_order'], 'portfolio_gallery_sort_index');
        });
        Schema::create('portfolio_project_technologies', function (Blueprint $t) {
            $t->id();
            $t->foreignId('portfolio_project_id')->constrained()->cascadeOnDelete();
            $t->string('name', 140);
            $t->string('icon', 100)->nullable();
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
            $t->index(['portfolio_project_id', 'sort_order'], 'portfolio_technologies_sort_index');
        });
        Schema::create('portfolio_project_links', function (Blueprint $t) {
            $t->id();
            $t->foreignId('portfolio_project_id')->unique()->constrained()->cascadeOnDelete();
            $t->string('live_url', 2048)->nullable();
            $t->string('demo_url', 2048)->nullable();
            $t->string('github_url', 2048)->nullable();
            $t->string('documentation_url', 2048)->nullable();
            $t->timestamps();
        });
        Schema::create('portfolio_project_faqs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('portfolio_project_id')->constrained()->cascadeOnDelete();
            $t->string('question', 255);
            $t->text('answer');
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
            $t->index(['portfolio_project_id', 'sort_order'], 'portfolio_faqs_sort_index');
        });
        Schema::create('portfolio_project_seo', function (Blueprint $t) {
            $t->id();
            $t->foreignId('portfolio_project_id')->unique()->constrained()->cascadeOnDelete();
            $t->string('meta_title', 70)->nullable();
            $t->string('meta_description', 160)->nullable();
            $t->string('meta_keywords', 500)->nullable();
            $t->string('canonical_url', 2048)->nullable();
            $t->string('open_graph_image_path')->nullable();
            $t->boolean('is_indexable')->default(true);
            $t->timestamps();
        });
        Schema::table('quote_requests', function (Blueprint $t) {
            $t->foreignId('portfolio_project_id')->nullable()->after('ai_solution_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quote_requests', function (Blueprint $t) {
            $t->dropConstrainedForeignId('portfolio_project_id');
        });
        foreach (['portfolio_project_seo', 'portfolio_project_faqs', 'portfolio_project_links', 'portfolio_project_technologies', 'portfolio_project_gallery_images', 'portfolio_project_features', 'portfolio_project_results', 'portfolio_project_solutions', 'portfolio_project_objectives', 'portfolio_projects', 'portfolio_categories'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
