<?php

namespace App\Models;

use App\Enums\LeadItemType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemoRequest extends Model
{
    protected $fillable = ['item_type', 'product_id', 'service_id', 'preferred_date', 'preferred_time', 'meeting_type', 'message'];

    protected function casts(): array
    {
        return ['item_type' => LeadItemType::class, 'preferred_date' => 'date'];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function getItemAttribute(): Product|Service|null
    {
        return match ($this->item_type) {
            LeadItemType::Product => $this->product, LeadItemType::Service => $this->service
        };
    }
}
