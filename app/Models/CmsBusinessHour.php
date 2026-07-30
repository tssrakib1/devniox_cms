<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsBusinessHour extends Model
{
    public $timestamps = false;

    protected $fillable = ['day_of_week', 'is_closed', 'opens_at', 'closes_at'];

    protected function casts(): array
    {
        return ['is_closed' => 'boolean'];
    }
}
