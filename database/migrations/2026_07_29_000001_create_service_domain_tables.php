<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->text('description')->nullable();
            $table->string('icon', 100)->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->enum('status', ['draft', 'published'])->default('draft')->index();
            $table->string('seo_title', 70)->nullable();
            $table->string('seo_description', 160)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_category_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 180);
            $table->string('slug', 200)->unique();
            $table->string('cover_image_path', 500)->nullable();
            $table->string('featured_image_path', 500)->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft')->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedInteger('display_order')->default(0)->index();
            $table->string('short_description', 300);
            $table->longText('full_description');
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['service_category_id', 'status', 'display_order']);
            $table->index(['status', 'is_featured', 'published_at']);
        });

        Schema::create('service_benefits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('title', 160);
            $table->text('description');
            $table->string('icon', 100)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['service_id', 'sort_order']);
        });

        Schema::create('service_process_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('step_number');
            $table->string('title', 160);
            $table->text('description');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['service_id', 'step_number']);
            $table->index(['service_id', 'sort_order']);
        });

        Schema::create('service_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('title', 160);
            $table->text('description');
            $table->string('icon', 100)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['service_id', 'sort_order']);
        });

        Schema::create('service_technologies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('icon', 100)->nullable();
            $table->string('image_path', 500)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['service_id', 'sort_order']);
        });

        Schema::create('service_deliverables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['service_id', 'sort_order']);
        });

        Schema::create('service_gallery_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('image_path', 500);
            $table->string('alt_text', 180)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['service_id', 'sort_order']);
        });

        Schema::create('service_faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('question', 255);
            $table->text('answer');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['service_id', 'sort_order']);
        });

        Schema::create('service_seo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('meta_title', 70)->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->string('meta_keywords', 500)->nullable();
            $table->string('canonical_url', 2048)->nullable();
            $table->string('open_graph_image_path', 500)->nullable();
            $table->boolean('is_indexable')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_seo');
        Schema::dropIfExists('service_faqs');
        Schema::dropIfExists('service_gallery_images');
        Schema::dropIfExists('service_deliverables');
        Schema::dropIfExists('service_technologies');
        Schema::dropIfExists('service_features');
        Schema::dropIfExists('service_process_steps');
        Schema::dropIfExists('service_benefits');
        Schema::dropIfExists('services');
        Schema::dropIfExists('service_categories');
    }
};
