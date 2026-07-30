<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->timestamp('read_at')->nullable()->after('submitted_at')->index();
            $table->timestamp('replied_at')->nullable()->after('read_at')->index();
            $table->timestamp('closed_at')->nullable()->after('replied_at')->index();
            $table->timestamp('converted_at')->nullable()->after('closed_at')->index();
            $table->foreignId('converted_order_id')->nullable()->after('converted_at')->constrained('orders')->nullOnDelete();
        });
        Schema::table('demo_requests', fn (Blueprint $table) => $table->string('meeting_type', 20)->default('online')->after('preferred_time')->index());
        Schema::table('quote_requests', fn (Blueprint $table) => $table->string('business_type', 160)->nullable()->after('lead_id'));
        Schema::create('communication_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('administrator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('direction', 20)->default('outgoing')->index();
            $table->string('subject', 200);
            $table->text('message');
            $table->timestamp('replied_at')->index();
            $table->timestamps();
            $table->index(['lead_id', 'replied_at']);
        });
        Schema::create('communication_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('communication_reply_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('label', 160)->nullable();
            $table->string('file_path', 2048);
            $table->string('original_name', 255);
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('file_size');
            $table->timestamps();
            $table->index(['lead_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_attachments');
        Schema::dropIfExists('communication_replies');
        Schema::table('quote_requests', fn (Blueprint $table) => $table->dropColumn('business_type'));
        Schema::table('demo_requests', fn (Blueprint $table) => $table->dropColumn('meeting_type'));
        Schema::table('leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('converted_order_id');
            $table->dropColumn(['read_at', 'replied_at', 'closed_at', 'converted_at']);
        });
    }
};
