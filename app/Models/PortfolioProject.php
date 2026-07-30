<?php

namespace App\Models;

use App\Enums\PortfolioStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class PortfolioProject extends Model
{
    use SoftDeletes;

    protected $fillable = ['portfolio_category_id', 'created_by', 'updated_by', 'name', 'slug', 'client_name', 'industry', 'completion_date', 'status', 'is_featured', 'display_order', 'thumbnail_path', 'cover_image_path', 'short_description', 'full_description', 'published_at'];

    protected function casts(): array
    {
        return ['completion_date' => 'date', 'status' => PortfolioStatus::class, 'is_featured' => 'boolean', 'published_at' => 'datetime'];
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
        return $this->belongsTo(PortfolioCategory::class, 'portfolio_category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function objectives(): HasMany
    {
        return $this->hasMany(PortfolioProjectObjective::class)->orderBy('sort_order');
    }

    public function solutions(): HasMany
    {
        return $this->hasMany(PortfolioProjectSolution::class)->orderBy('sort_order');
    }

    public function features(): HasMany
    {
        return $this->hasMany(PortfolioProjectFeature::class)->orderBy('sort_order');
    }

    public function galleryImages(): HasMany
    {
        return $this->hasMany(PortfolioProjectGalleryImage::class)->orderBy('sort_order');
    }

    public function technologies(): HasMany
    {
        return $this->hasMany(PortfolioProjectTechnology::class)->orderBy('sort_order');
    }

    public function links(): HasOne
    {
        return $this->hasOne(PortfolioProjectLink::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(PortfolioProjectResult::class)->orderBy('sort_order');
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(PortfolioProjectFaq::class)->orderBy('sort_order');
    }

    public function seo(): HasOne
    {
        return $this->hasOne(PortfolioProjectSeo::class);
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', PortfolioStatus::Published)->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function scopeFeatured(Builder $q): Builder
    {
        return $q->where('is_featured', true);
    }
}
