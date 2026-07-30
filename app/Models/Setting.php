<?php

namespace App\Models;

use App\Enums\SettingGroup;
use App\Services\SettingsService;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type', 'is_public'];

    protected function casts(): array
    {
        return ['group' => SettingGroup::class, 'is_public' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => app(SettingsService::class)->forget());
        static::deleted(fn () => app(SettingsService::class)->forget());
    }
}
