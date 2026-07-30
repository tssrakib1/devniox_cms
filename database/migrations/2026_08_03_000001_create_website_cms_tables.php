<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_pages', function (Blueprint $t) {
            $t->id();
            $t->string('key', 30)->unique();
            $t->string('status', 20)->default('published')->index();
            $t->string('meta_title', 70)->nullable();
            $t->string('meta_description', 160)->nullable();
            $t->string('meta_keywords', 500)->nullable();
            $t->string('canonical_url', 2048)->nullable();
            $t->string('open_graph_image_path')->nullable();
            $t->boolean('is_indexable')->default(true);
            $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
        Schema::create('cms_home_content', function (Blueprint $t) {
            $t->id();
            $t->foreignId('cms_page_id')->unique()->constrained()->cascadeOnDelete();
            $t->string('hero_heading', 200);
            $t->string('hero_subheading', 200)->nullable();
            $t->text('hero_description');
            $t->string('hero_background_path')->nullable();
            $t->string('primary_button_text', 80)->nullable();
            $t->string('primary_button_url', 2048)->nullable();
            $t->string('secondary_button_text', 80)->nullable();
            $t->string('secondary_button_url', 2048)->nullable();
            $t->string('intro_title', 180);
            $t->text('intro_description');
            $t->string('intro_image_path')->nullable();
            $t->string('products_title', 180);
            $t->text('products_description')->nullable();
            $t->string('services_title', 180);
            $t->text('services_description')->nullable();
            $t->string('ai_title', 180);
            $t->text('ai_description')->nullable();
            $t->string('portfolio_title', 180);
            $t->text('portfolio_description')->nullable();
            $t->string('articles_title', 180);
            $t->text('articles_description')->nullable();
            $t->boolean('ecosystem_enabled')->default(true);
            $t->string('ecosystem_label', 80)->default('OUR ECOSYSTEM');
            $t->string('ecosystem_title', 180)->default('Powerful Platforms. One Parent Company.');
            $t->text('ecosystem_description')->nullable();
            $t->text('ecosystem_note')->nullable();
            $t->timestamps();
        });
        foreach (['home_why_items', 'home_statistics'] as $table) {
            Schema::create($table, function (Blueprint $t) use ($table) {
                $t->id();
                $t->foreignId('cms_page_id')->constrained()->cascadeOnDelete();
                $t->string('title', 180);
                $t->text('description')->nullable();
                if ($table === 'home_statistics') {
                    $t->string('value', 80);
                }$t->string('icon', 100)->nullable();
                $t->unsignedInteger('sort_order')->default(0);
                $t->timestamps();
                $t->index(['cms_page_id', 'sort_order']);
            });
        }Schema::create('cms_about_content', function (Blueprint $t) {
            $t->id();
            $t->foreignId('cms_page_id')->unique()->constrained()->cascadeOnDelete();
            $t->string('hero_heading', 200);
            $t->text('hero_description');
            $t->string('hero_banner_path')->nullable();
            $t->string('story_title', 180);
            $t->text('story_description');
            $t->string('story_image_path')->nullable();
            $t->string('mission_title', 180);
            $t->text('mission_description');
            $t->string('vision_title', 180);
            $t->text('vision_description');
            $t->timestamps();
        });
        foreach (['about_core_values', 'about_work_items'] as $table) {
            Schema::create($table, function (Blueprint $t) {
                $t->id();
                $t->foreignId('cms_page_id')->constrained()->cascadeOnDelete();
                $t->string('title', 180);
                $t->text('description');
                $t->string('icon', 100)->nullable();
                $t->unsignedInteger('sort_order')->default(0);
                $t->timestamps();
                $t->index(['cms_page_id', 'sort_order']);
            });
        }Schema::create('cms_contact_content', function (Blueprint $t) {
            $t->id();
            $t->foreignId('cms_page_id')->unique()->constrained()->cascadeOnDelete();
            $t->string('hero_heading', 200);
            $t->text('hero_description');
            $t->string('hero_banner_path')->nullable();
            $t->string('company_name', 180);
            $t->text('address')->nullable();
            $t->string('email', 254);
            $t->string('phone', 50)->nullable();
            $t->string('whatsapp', 50)->nullable();
            $t->string('map_embed_url', 2048)->nullable();
            $t->string('success_message', 300);
            $t->boolean('auto_reply_enabled')->default(false);
            $t->string('auto_reply_subject', 180)->nullable();
            $t->text('auto_reply_message')->nullable();
            $t->timestamps();
        });
        Schema::create('cms_business_hours', function (Blueprint $t) {
            $t->id();
            $t->foreignId('cms_page_id')->constrained()->cascadeOnDelete();
            $t->unsignedTinyInteger('day_of_week');
            $t->boolean('is_closed')->default(false);
            $t->time('opens_at')->nullable();
            $t->time('closes_at')->nullable();
            $t->unique(['cms_page_id', 'day_of_week']);
        });
        Schema::create('cms_navigation_items', function (Blueprint $t) {
            $t->id();
            $t->string('location', 10)->index();
            $t->foreignId('parent_id')->nullable()->constrained('cms_navigation_items')->cascadeOnDelete();
            $t->string('label', 100);
            $t->string('url', 2048);
            $t->boolean('open_new_tab')->default(false);
            $t->boolean('is_visible')->default(true);
            $t->unsignedInteger('display_order')->default(0);
            $t->timestamps();
            $t->index(['location', 'is_visible', 'display_order']);
        });
        Schema::create('cms_footer_content', function (Blueprint $t) {
            $t->id();
            $t->string('copyright', 255);
            $t->text('short_description');
            $t->string('quick_links_heading', 100);
            $t->string('products_heading', 100);
            $t->string('services_heading', 100);
            $t->string('ai_heading', 100);
            $t->string('blog_heading', 100);
            $t->string('privacy_url', 2048)->nullable();
            $t->string('terms_url', 2048)->nullable();
            $t->string('cookies_url', 2048)->nullable();
            $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['cms_footer_content', 'cms_navigation_items', 'cms_business_hours', 'cms_contact_content', 'about_work_items', 'about_core_values', 'cms_about_content', 'home_statistics', 'home_why_items', 'cms_home_content', 'cms_pages'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};



