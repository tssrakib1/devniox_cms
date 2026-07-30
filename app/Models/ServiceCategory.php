<?php

namespace App\Models;

use App\Enums\ServiceCategoryStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class ServiceCategory extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'description', 'icon', 'sort_order', 'status', 'seo_title', 'seo_description'];

    protected function casts(): array
    {
        return ['status' => ServiceCategoryStatus::class];
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

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ServiceCategoryStatus::Published);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->published();
    }
}
