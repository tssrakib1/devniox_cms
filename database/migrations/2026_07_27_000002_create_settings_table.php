<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->enum('group', ['company', 'branding', 'contact', 'hours', 'social', 'seo', 'mail', 'analytics'])->index();
            $table->string('key', 100);
            $table->text('value')->nullable();
            $table->enum('type', ['string', 'text', 'boolean', 'integer', 'url', 'email', 'image', 'secret'])->default('string');
            $table->boolean('is_public')->default(false)->index();
            $table->timestamps();
            $table->unique(['group', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
