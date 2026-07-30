<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CmsNavigationItem extends Model
{
    protected $fillable = ['location', 'parent_id', 'label', 'url', 'open_new_tab', 'is_visible', 'display_order'];

    protected function casts(): array
    {
        return ['open_new_tab' => 'boolean', 'is_visible' => 'boolean'];
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('display_order');
    }

    public function scopeVisible(Builder $q): Builder
    {
        return $q->where('is_visible', true);
    }
}
