<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $t) {
            $t->string('group', 40)->change();
        });
        Schema::create('social_links', function (Blueprint $t) {
            $t->id();
            $t->string('platform', 40)->unique();
            $t->string('url', 2048)->nullable();
            $t->boolean('is_visible')->default(false)->index();
            $t->unsignedInteger('display_order')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_links');
    }
};
