<?php

namespace App\Models;

use App\Enums\FinancePaymentMethod;
use App\Enums\FinanceTransactionSource;
use App\Enums\FinanceTransactionStatus;
use App\Enums\FinanceTransactionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'transaction_number', 'type', 'source', 'reference_type', 'reference_id', 'reference',
        'income_category_id', 'expense_category_id', 'title', 'description', 'amount', 'payment_method',
        'transaction_date', 'status', 'attachment_count', 'archived_at', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => FinanceTransactionType::class, 'source' => FinanceTransactionSource::class,
            'payment_method' => FinancePaymentMethod::class, 'status' => FinanceTransactionStatus::class,
            'amount' => 'decimal:2', 'transaction_date' => 'date', 'archived_at' => 'datetime',
            'attachment_count' => 'integer',
        ];
    }

    public function incomeCategory(): BelongsTo
    {
        return $this->belongsTo(IncomeCategory::class);
    }

    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TransactionAttachment::class)->latest();
    }

    public function getCategoryAttribute(): IncomeCategory|ExpenseCategory|null
    {
        return $this->type === FinanceTransactionType::Income ? $this->incomeCategory : $this->expenseCategory;
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(fn (Builder $inner) => $inner->where('transaction_number', 'like', "%{$search}%")
            ->orWhere('title', 'like', "%{$search}%")->orWhere('reference', 'like', "%{$search}%")
            ->orWhere('description', 'like', "%{$search}%"));
    }
}
