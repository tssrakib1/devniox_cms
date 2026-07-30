<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Cache;

class MediaUsage extends Model
{
    protected $fillable = ['media_asset_id', 'usable_type', 'usable_id', 'field'];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('media.library.stats.v1'));
        static::deleted(fn () => Cache::forget('media.library.stats.v1'));
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'media_asset_id');
    }

    public function usable(): MorphTo
    {
        return $this->morphTo();
    }
}
