<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 30)->nullable()->unique();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name', 160);
            $table->string('company_name', 180)->nullable();
            $table->string('email', 254)->index();
            $table->string('phone', 40)->nullable();
            $table->string('whatsapp', 40)->nullable();
            $table->text('address')->nullable();
            $table->date('order_date')->index();
            $table->date('expected_delivery_date')->nullable()->index();
            $table->string('priority', 20)->default('medium')->index();
            $table->string('status', 40)->default('pending')->index();
            $table->string('source', 20)->default('direct')->index();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('final_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('due_amount', 15, 2)->default(0);
            $table->string('payment_status', 20)->default('unpaid')->index();
            $table->string('payment_method', 30)->nullable()->index();
            $table->timestamp('archived_at')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'priority', 'payment_status', 'order_date']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('name', 200);
            $table->string('type', 40)->index();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['order_id', 'sort_order']);
        });

        Schema::create('order_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('label', 160)->nullable();
            $table->string('file_path', 2048);
            $table->string('original_name', 255);
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('file_size');
            $table->timestamps();
            $table->index(['order_id', 'created_at']);
        });

        Schema::create('order_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note');
            $table->timestamps();
            $table->index(['order_id', 'created_at']);
        });

        Schema::create('order_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type', 60)->index();
            $table->string('description', 500);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->index(['order_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_events');
        Schema::dropIfExists('order_notes');
        Schema::dropIfExists('order_attachments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
