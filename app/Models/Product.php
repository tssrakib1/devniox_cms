<?php

namespace App\Models;

use App\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = ['product_category_id', 'created_by', 'updated_by', 'name', 'slug', 'version', 'status', 'is_featured', 'display_order', 'short_description', 'full_description', 'thumbnail_path', 'banner_path', 'logo_path', 'published_at'];

    protected function casts(): array
    {
        return ['status' => ProductStatus::class, 'is_featured' => 'boolean', 'published_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::clearPublicCaches());
        static::deleted(fn () => static::clearPublicCaches());
        static::restored(fn () => static::clearPublicCaches());
    }

    public static function clearPublicCaches(): void
    {
        Cache::forget('home.featured-content.v1');
        Cache::forget('home.featured-content.v2');
        Cache::forget('seo.sitemap.v1');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function highlights(): HasMany
    {
        return $this->hasMany(ProductHighlight::class)->orderBy('sort_order');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(ProductModule::class)->orderBy('sort_order');
    }

    public function features(): HasMany
    {
        return $this->hasMany(ProductFeature::class)->orderBy('sort_order');
    }

    public function requirements(): HasOne
    {
        return $this->hasOne(ProductRequirement::class);
    }

    public function links(): HasOne
    {
        return $this->hasOne(ProductLink::class);
    }

    public function seo(): HasOne
    {
        return $this->hasOne(ProductSeo::class);
    }

    public function galleryImages(): HasMany
    {
        return $this->hasMany(ProductGalleryImage::class)->orderBy('sort_order');
    }

    public function pricingPlans(): HasMany
    {
        return $this->hasMany(ProductPricingPlan::class)->orderBy('sort_order');
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(ProductFaq::class)->orderBy('sort_order');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ProductStatus::Published)->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }
}
