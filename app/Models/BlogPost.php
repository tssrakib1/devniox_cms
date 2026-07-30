<?php

namespace App\Models;

use App\Enums\BlogStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class BlogPost extends Model
{
    use SoftDeletes;

    protected $fillable = ['blog_category_id', 'author_id', 'updated_by', 'title', 'slug', 'status', 'is_featured', 'published_at', 'reading_time', 'display_order', 'views_count', 'featured_image_path', 'social_image_path', 'summary', 'body'];

    protected function casts(): array
    {
        return ['status' => BlogStatus::class, 'is_featured' => 'boolean', 'published_at' => 'datetime'];
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

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function tags()
    {
        return $this->belongsToMany(BlogTag::class, 'blog_post_tag');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'blog_post_product');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'blog_post_service');
    }

    public function downloads()
    {
        return $this->hasMany(BlogPostDownload::class)->orderBy('sort_order');
    }

    public function faqs()
    {
        return $this->hasMany(BlogPostFaq::class)->orderBy('sort_order');
    }

    public function seo()
    {
        return $this->hasOne(BlogPostSeo::class);
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', BlogStatus::Published)->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function scopeFeatured(Builder $q): Builder
    {
        return $q->where('is_featured', true);
    }
}
