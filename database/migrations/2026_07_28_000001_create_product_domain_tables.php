<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->text('description')->nullable();
            $table->string('icon', 100)->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->string('seo_title', 70)->nullable();
            $table->string('seo_description', 160)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_category_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 180);
            $table->string('slug', 200)->unique();
            $table->string('version', 40);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft')->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedInteger('display_order')->default(0)->index();
            $table->string('short_description', 300);
            $table->longText('full_description');
            $table->string('thumbnail_path', 500)->nullable();
            $table->string('banner_path', 500)->nullable();
            $table->string('logo_path', 500)->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['product_category_id', 'status', 'display_order']);
            $table->index(['status', 'is_featured', 'published_at']);
        });

        Schema::create('product_highlights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('title', 160);
            $table->text('description');
            $table->string('icon', 100)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['product_id', 'sort_order']);
        });

        Schema::create('product_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name', 160);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['product_id', 'sort_order']);
        });

        Schema::create('product_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('title', 160);
            $table->text('description');
            $table->string('icon', 100)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['product_id', 'sort_order']);
        });

        Schema::create('product_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('php_version', 80)->nullable();
            $table->string('laravel_version', 80)->nullable();
            $table->string('database', 160)->nullable();
            $table->string('hosting', 255)->nullable();
            $table->string('browser_support', 255)->nullable();
            $table->text('server_requirements')->nullable();
            $table->timestamps();
        });

        Schema::create('product_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('live_demo_url', 2048)->nullable();
            $table->string('video_url', 2048)->nullable();
            $table->string('documentation_url', 2048)->nullable();
            $table->timestamps();
        });

        Schema::create('product_seo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('meta_title', 70)->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->string('keywords', 500)->nullable();
            $table->string('open_graph_image_path', 500)->nullable();
            $table->string('canonical_url', 2048)->nullable();
            $table->boolean('is_indexable')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('product_gallery_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('image_path', 500);
            $table->string('alt_text', 180)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['product_id', 'sort_order']);
        });

        Schema::create('product_pricing_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->decimal('price', 12, 2)->nullable();
            $table->char('currency', 3)->default('USD');
            $table->enum('billing_type', ['one_time', 'monthly', 'yearly', 'custom']);
            $table->text('description')->nullable();
            $table->boolean('is_highlighted')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->index(['product_id', 'sort_order']);
        });

        Schema::create('product_pricing_plan_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_pricing_plan_id')->constrained()->cascadeOnDelete();
            $table->string('feature', 255);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['product_pricing_plan_id', 'sort_order'], 'product_plan_features_sort_index');
        });

        Schema::create('product_faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('question', 255);
            $table->text('answer');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['product_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_faqs');
        Schema::dropIfExists('product_pricing_plan_features');
        Schema::dropIfExists('product_pricing_plans');
        Schema::dropIfExists('product_gallery_images');
        Schema::dropIfExists('product_seo');
        Schema::dropIfExists('product_links');
        Schema::dropIfExists('product_requirements');
        Schema::dropIfExists('product_features');
        Schema::dropIfExists('product_modules');
        Schema::dropIfExists('product_highlights');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_categories');
    }
};
