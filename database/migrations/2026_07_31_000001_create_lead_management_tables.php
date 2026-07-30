<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $t) {
            $t->id();
            $t->string('type', 30)->index();
            $t->string('status', 30)->default('new')->index();
            $t->string('priority', 20)->default('medium')->index();
            $t->string('name', 160);
            $t->string('company', 180)->nullable();
            $t->string('email', 254)->index();
            $t->string('phone', 40)->nullable();
            $t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $t->ipAddress('ip_address')->nullable();
            $t->string('user_agent', 1000)->nullable();
            $t->string('referrer', 2048)->nullable();
            $t->string('landing_url', 2048)->nullable();
            $t->timestamp('submitted_at')->index();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['type', 'status', 'priority', 'created_at']);
        });
        Schema::create('contact_messages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('lead_id')->unique()->constrained()->cascadeOnDelete();
            $t->string('subject', 200);
            $t->text('message');
            $t->string('website', 2048)->nullable();
            $t->timestamps();
        });
        Schema::create('demo_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('lead_id')->unique()->constrained()->cascadeOnDelete();
            $t->string('item_type', 30)->index();
            $t->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('ai_solution_id')->nullable()->constrained()->nullOnDelete();
            $t->date('preferred_date')->nullable();
            $t->time('preferred_time')->nullable();
            $t->text('message')->nullable();
            $t->timestamps();
        });
        Schema::create('quote_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('lead_id')->unique()->constrained()->cascadeOnDelete();
            $t->string('item_type', 30)->index();
            $t->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('ai_solution_id')->nullable()->constrained()->nullOnDelete();
            $t->string('budget', 120)->nullable();
            $t->string('timeline', 120)->nullable();
            $t->text('requirement_details');
            $t->string('attachment_path')->nullable();
            $t->string('attachment_original_name')->nullable();
            $t->string('attachment_mime', 120)->nullable();
            $t->unsignedBigInteger('attachment_size')->nullable();
            $t->timestamps();
        });
        Schema::create('lead_notes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $t->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $t->text('note');
            $t->timestamps();
            $t->index(['lead_id', 'created_at']);
        });
        Schema::create('lead_status_histories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $t->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->string('from_status', 30)->nullable();
            $t->string('to_status', 30);
            $t->timestamp('changed_at');
            $t->index(['lead_id', 'changed_at']);
        });
        Schema::create('lead_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $t->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('event_type', 50)->index();
            $t->string('description', 500);
            $t->timestamp('occurred_at');
            $t->index(['lead_id', 'occurred_at']);
        });
        Schema::table('notifications', function (Blueprint $t) {
            $t->foreignId('lead_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $t->index(['lead_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $t) {
            $t->dropForeign(['lead_id']);
            $t->dropIndex(['lead_id', 'created_at']);
            $t->dropColumn('lead_id');
        });
        foreach (['lead_events', 'lead_status_histories', 'lead_notes', 'quote_requests', 'demo_requests', 'contact_messages', 'leads'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
