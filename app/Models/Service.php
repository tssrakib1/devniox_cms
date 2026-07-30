<?php

namespace App\Models;

use App\Enums\ServiceStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = ['service_category_id', 'created_by', 'updated_by', 'name', 'slug', 'cover_image_path', 'featured_image_path', 'status', 'is_featured', 'display_order', 'short_description', 'full_description', 'published_at'];

    protected function casts(): array
    {
        return ['status' => ServiceStatus::class, 'is_featured' => 'boolean', 'published_at' => 'datetime'];
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
        Cache::forget('blog.rss.v1');
        Cache::forget('seo.sitemap.v1');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function benefits(): HasMany
    {
        return $this->hasMany(ServiceBenefit::class)->orderBy('sort_order');
    }

    public function processSteps(): HasMany
    {
        return $this->hasMany(ServiceProcessStep::class)->orderBy('sort_order');
    }

    public function features(): HasMany
    {
        return $this->hasMany(ServiceFeature::class)->orderBy('sort_order');
    }

    public function technologies(): HasMany
    {
        return $this->hasMany(ServiceTechnology::class)->orderBy('sort_order');
    }

    public function deliverables(): HasMany
    {
        return $this->hasMany(ServiceDeliverable::class)->orderBy('sort_order');
    }

    public function galleryImages(): HasMany
    {
        return $this->hasMany(ServiceGalleryImage::class)->orderBy('sort_order');
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(ServiceFaq::class)->orderBy('sort_order');
    }

    public function seo(): HasOne
    {
        return $this->hasOne(ServiceSeo::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ServiceStatus::Published)->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }
}
