<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('media_folders')->nullOnDelete();
            $table->string('name', 120);
            $table->string('slug', 140);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['parent_id', 'slug']);
            $table->index(['parent_id', 'name']);
        });
        Schema::create('media_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_folder_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 180);
            $table->string('original_name', 255);
            $table->string('disk', 30);
            $table->string('file_path', 2048);
            $table->string('mime_type', 150)->index();
            $table->string('extension', 20);
            $table->string('kind', 20)->index();
            $table->unsignedBigInteger('file_size');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt_text', 255)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_optimized')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['disk', 'file_path']);
            $table->index(['media_folder_id', 'kind', 'created_at']);
        });
        Schema::create('media_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_asset_id')->constrained()->cascadeOnDelete();
            $table->nullableMorphs('usable');
            $table->string('field', 100);
            $table->timestamps();
            $table->unique(['media_asset_id', 'usable_type', 'usable_id', 'field'], 'media_usage_unique');
            $table->index(['usable_type', 'usable_id', 'field'], 'media_usage_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_usages');
        Schema::dropIfExists('media_assets');
        Schema::dropIfExists('media_folders');
    }
};
