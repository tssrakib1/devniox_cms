<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Platform extends Model
{
    use SoftDeletes;

    protected $fillable = ['created_by', 'updated_by', 'name', 'slug', 'logo', 'description', 'website_url', 'badge', 'brand_color', 'display_order', 'open_in_new_tab', 'status'];

    protected function casts(): array
    {
        return ['open_in_new_tab' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::clearPublicCaches());
        static::deleted(fn () => static::clearPublicCaches());
        static::restored(fn () => static::clearPublicCaches());
    }

    public static function clearPublicCaches(): void
    {
        Cache::forget('home.featured-content.v2');
        Cache::forget('home.platforms.v1');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
