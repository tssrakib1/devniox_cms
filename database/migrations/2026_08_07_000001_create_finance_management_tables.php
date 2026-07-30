<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('income_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('finance_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number', 30)->nullable()->unique();
            $table->string('type', 20)->index();
            $table->string('source', 30)->default('manual')->index();
            $table->string('reference_type', 80)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference', 255)->nullable()->index();
            $table->foreignId('income_category_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('expense_category_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('payment_method', 30)->index();
            $table->date('transaction_date')->index();
            $table->string('status', 20)->default('completed')->index();
            $table->unsignedInteger('attachment_count')->default(0);
            $table->timestamp('archived_at')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['reference_type', 'reference_id']);
            $table->index(['transaction_date', 'type', 'status']);
            $table->index(['type', 'income_category_id', 'transaction_date'], 'finance_income_type_date_index');
            $table->index(['type', 'expense_category_id', 'transaction_date'], 'finance_expense_type_date_index');
        });

        Schema::create('transaction_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finance_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('label', 160)->nullable();
            $table->string('file_path', 2048);
            $table->string('original_name', 255);
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('file_size');
            $table->timestamps();
            $table->index(['finance_transaction_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_attachments');
        Schema::dropIfExists('finance_transactions');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('income_categories');
    }
};
