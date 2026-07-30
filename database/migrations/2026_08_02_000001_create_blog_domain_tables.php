<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_categories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('parent_id')->nullable()->constrained('blog_categories')->nullOnDelete();
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
        Schema::create('blog_tags', function (Blueprint $t) {
            $t->id();
            $t->string('name', 100);
            $t->string('slug', 120)->unique();
            $t->text('description')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('blog_posts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('blog_category_id')->constrained()->restrictOnDelete();
            $t->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $t->string('title', 200);
            $t->string('slug', 220)->unique();
            $t->string('status', 20)->default('draft')->index();
            $t->boolean('is_featured')->default(false)->index();
            $t->timestamp('published_at')->nullable()->index();
            $t->unsignedSmallInteger('reading_time')->default(1);
            $t->unsignedInteger('display_order')->default(0);
            $t->unsignedBigInteger('views_count')->default(0);
            $t->string('featured_image_path')->nullable();
            $t->string('social_image_path')->nullable();
            $t->string('summary', 320);
            $t->longText('body');
            $t->timestamps();
            $t->softDeletes();
            $t->index(['blog_category_id', 'status', 'published_at']);
        });
        Schema::create('blog_post_tag', function (Blueprint $t) {
            $t->foreignId('blog_post_id')->constrained()->cascadeOnDelete();
            $t->foreignId('blog_tag_id')->constrained()->cascadeOnDelete();
            $t->primary(['blog_post_id', 'blog_tag_id']);
        });
        foreach (['product', 'service', 'ai_solution'] as $type) {
            Schema::create('blog_post_'.$type, function (Blueprint $t) use ($type) {
                $t->foreignId('blog_post_id')->constrained()->cascadeOnDelete();
                $t->foreignId($type.'_id')->constrained($type.'s')->cascadeOnDelete();
                $t->primary(['blog_post_id', $type.'_id']);
            });
        }
        Schema::create('blog_post_downloads', function (Blueprint $t) {
            $t->id();
            $t->foreignId('blog_post_id')->constrained()->cascadeOnDelete();
            $t->string('title', 180);
            $t->string('file_path');
            $t->string('original_name');
            $t->string('mime_type', 120);
            $t->unsignedBigInteger('file_size');
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
            $t->index(['blog_post_id', 'sort_order']);
        });
        Schema::create('blog_post_faqs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('blog_post_id')->constrained()->cascadeOnDelete();
            $t->string('question', 255);
            $t->text('answer');
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
            $t->index(['blog_post_id', 'sort_order']);
        });
        Schema::create('blog_post_seo', function (Blueprint $t) {
            $t->id();
            $t->foreignId('blog_post_id')->unique()->constrained()->cascadeOnDelete();
            $t->string('meta_title', 70)->nullable();
            $t->string('meta_description', 160)->nullable();
            $t->string('meta_keywords', 500)->nullable();
            $t->string('canonical_url', 2048)->nullable();
            $t->string('open_graph_image_path')->nullable();
            $t->boolean('is_indexable')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['blog_post_seo', 'blog_post_faqs', 'blog_post_downloads', 'blog_post_ai_solution', 'blog_post_service', 'blog_post_product', 'blog_post_tag', 'blog_posts', 'blog_tags', 'blog_categories'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
