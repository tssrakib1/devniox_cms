<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class MediaAsset extends Model
{
    use SoftDeletes;

    protected $fillable = ['media_folder_id', 'uploaded_by', 'name', 'original_name', 'disk', 'file_path', 'mime_type', 'extension', 'kind', 'file_size', 'sha256', 'width', 'height', 'alt_text', 'description', 'is_optimized'];

    protected function casts(): array
    {
        return ['is_optimized' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('media.library.stats.v1'));
        static::deleted(fn () => Cache::forget('media.library.stats.v1'));
        static::restored(fn () => Cache::forget('media.library.stats.v1'));
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'media_folder_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(MediaUsage::class);
    }

    public function scopeSearch(Builder $q, string $search): Builder
    {
        return $q->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('original_name', 'like', "%{$search}%")->orWhere('alt_text', 'like', "%{$search}%"));
    }

    public function isPublic(): bool
    {
        return $this->disk === 'public';
    }
}
