<?php

namespace App\Models;

use App\Services\SettingsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SocialLink extends Model
{
    protected $fillable = ['platform', 'url', 'is_visible', 'display_order'];

    protected function casts(): array
    {
        return ['is_visible' => 'boolean'];
    }

    public function scopeVisible(Builder $q): Builder
    {
        return $q->where('is_visible', true)->whereNotNull('url');
    }

    protected static function booted(): void
    {
        static::saved(fn () => app(SettingsService::class)->forget());
        static::deleted(fn () => app(SettingsService::class)->forget());
    }
}
