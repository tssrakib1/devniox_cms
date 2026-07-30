<?php

namespace App\Models;

use App\Enums\LeadItemType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteRequest extends Model
{
    protected $fillable = ['business_type', 'item_type', 'product_id', 'service_id', 'portfolio_project_id', 'budget', 'timeline', 'requirement_details', 'attachment_path', 'attachment_original_name', 'attachment_mime', 'attachment_size'];

    protected function casts(): array
    {
        return ['item_type' => LeadItemType::class];
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

    public function portfolioProject(): BelongsTo
    {
        return $this->belongsTo(PortfolioProject::class);
    }

    public function getItemAttribute(): Product|Service|PortfolioProject|null
    {
        return match ($this->item_type) {
            LeadItemType::Product => $this->product, LeadItemType::Service => $this->service, LeadItemType::Portfolio => $this->portfolioProject
        };
    }
}
