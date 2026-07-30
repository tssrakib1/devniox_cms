<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_categories', function (Blueprint $t) {
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
        Schema::create('ai_solutions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ai_category_id')->constrained()->restrictOnDelete();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $t->string('name', 180);
            $t->string('slug', 200)->unique();
            $t->string('cover_image_path')->nullable();
            $t->string('featured_image_path')->nullable();
            $t->string('status', 20)->default('draft')->index();
            $t->boolean('is_featured')->default(false)->index();
            $t->unsignedInteger('display_order')->default(0);
            $t->string('short_description', 300);
            $t->longText('full_description');
            $t->timestamp('published_at')->nullable()->index();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['ai_category_id', 'status', 'display_order']);
        });
        foreach (['capabilities', 'features'] as $n) {
            Schema::create('ai_solution_'.$n, function (Blueprint $t) {
                $t->id();
                $t->foreignId('ai_solution_id')->constrained()->cascadeOnDelete();
                $t->string('title', 160);
                $t->text('description');
                $t->string('icon', 100)->nullable();
                $t->unsignedInteger('sort_order')->default(0);
                $t->timestamps();
                $t->index(['ai_solution_id', 'sort_order']);
            });
        }Schema::create('ai_solution_use_cases', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ai_solution_id')->constrained()->cascadeOnDelete();
            $t->string('industry', 140);
            $t->string('title', 160);
            $t->text('description');
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
            $t->index(['ai_solution_id', 'sort_order']);
        });
        Schema::create('ai_solution_workflow_steps', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ai_solution_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('step_number');
            $t->string('title', 160);
            $t->text('description');
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
            $t->unique(['ai_solution_id', 'step_number']);
        });
        foreach (['technologies', 'integrations'] as $n) {
            Schema::create('ai_solution_'.$n, function (Blueprint $t) use ($n) {
                $t->id();
                $t->foreignId('ai_solution_id')->constrained()->cascadeOnDelete();
                $t->string('name', 140);
                $t->text('description')->nullable();
                $t->string('icon', 100)->nullable();
                if ($n === 'technologies') {
                    $t->string('image_path')->nullable();
                }$t->unsignedInteger('sort_order')->default(0);
                $t->timestamps();
                $t->index(['ai_solution_id', 'sort_order']);
            });
        }Schema::create('ai_solution_gallery_images', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ai_solution_id')->constrained()->cascadeOnDelete();
            $t->string('image_path');
            $t->string('alt_text', 180)->nullable();
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
            $t->index(['ai_solution_id', 'sort_order']);
        });
        Schema::create('ai_solution_faqs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ai_solution_id')->constrained()->cascadeOnDelete();
            $t->string('question', 255);
            $t->text('answer');
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
            $t->index(['ai_solution_id', 'sort_order']);
        });
        Schema::create('ai_solution_seo', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ai_solution_id')->unique()->constrained()->cascadeOnDelete();
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
        foreach (['ai_solution_seo', 'ai_solution_faqs', 'ai_solution_gallery_images', 'ai_solution_integrations', 'ai_solution_technologies', 'ai_solution_workflow_steps', 'ai_solution_use_cases', 'ai_solution_features', 'ai_solution_capabilities', 'ai_solutions', 'ai_categories'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
