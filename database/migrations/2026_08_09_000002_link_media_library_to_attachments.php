<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['order_attachments', 'transaction_attachments', 'communication_attachments'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->foreignId('media_asset_id')->nullable()->after('id')->constrained()->nullOnDelete());
        }
    }

    public function down(): void
    {
        foreach (['order_attachments', 'transaction_attachments', 'communication_attachments'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->dropConstrainedForeignId('media_asset_id'));
        }
    }
};
