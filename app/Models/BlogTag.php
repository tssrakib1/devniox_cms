<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class BlogTag extends Model
{
    use SoftDeletes;

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

    protected $fillable = ['name', 'slug', 'description'];

    public function posts()
    {
        return $this->belongsToMany(BlogPost::class, 'blog_post_tag');
    }
}
