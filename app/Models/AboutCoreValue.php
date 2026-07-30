<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutCoreValue extends Model
{
    protected $table = 'about_core_values';

    protected $fillable = ['title', 'description', 'icon', 'sort_order'];
}
