<?php

namespace App\Models;

use App\Enums\OrderPriority;
use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_number', 'lead_id', 'customer_name', 'company_name', 'email', 'phone', 'whatsapp', 'address',
        'order_date', 'expected_delivery_date', 'priority', 'status', 'source', 'total_amount', 'discount',
        'final_amount', 'paid_amount', 'due_amount', 'payment_status', 'payment_method', 'archived_at',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date', 'expected_delivery_date' => 'date', 'archived_at' => 'datetime',
            'priority' => OrderPriority::class, 'status' => OrderStatus::class, 'source' => OrderSource::class,
            'payment_status' => PaymentStatus::class, 'payment_method' => PaymentMethod::class,
            'total_amount' => 'decimal:2', 'discount' => 'decimal:2', 'final_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2', 'due_amount' => 'decimal:2',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class)->orderBy('sort_order');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(OrderAttachment::class)->latest();
    }

    public function notes(): HasMany
    {
        return $this->hasMany(OrderNote::class)->latest();
    }

    public function events(): HasMany
    {
        return $this->hasMany(OrderEvent::class)->latest('occurred_at');
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(fn (Builder $inner) => $inner->where('order_number', 'like', "%{$search}%")
            ->orWhere('customer_name', 'like', "%{$search}%")->orWhere('company_name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"));
    }
}
