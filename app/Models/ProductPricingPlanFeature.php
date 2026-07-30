<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPricingPlanFeature extends Model
{
    protected $fillable = ['feature', 'sort_order'];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(ProductPricingPlan::class, 'product_pricing_plan_id');
    }
}
