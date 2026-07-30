<?php

namespace App\Models;

use App\Enums\BillingType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductPricingPlan extends Model
{
    protected $fillable = ['name', 'price', 'currency', 'billing_type', 'description', 'is_highlighted', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'billing_type' => BillingType::class, 'is_highlighted' => 'boolean', 'is_active' => 'boolean'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function features(): HasMany
    {
        return $this->hasMany(ProductPricingPlanFeature::class)->orderBy('sort_order');
    }
}
