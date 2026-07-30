<?php

namespace App\Models;

use App\Enums\BlogCategoryStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class BlogCategory extends Model
{
    use SoftDeletes;

    protected $fillable = ['parent_id', 'name', 'slug', 'description', 'icon', 'sort_order', 'status', 'seo_title', 'seo_description'];

    protected function casts(): array
    {
        return ['status' => BlogCategoryStatus::class];
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

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function posts()
    {
        return $this->hasMany(BlogPost::class);
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', BlogCategoryStatus::Published);
    }
}
