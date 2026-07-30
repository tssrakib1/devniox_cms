<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeStatistic extends Model
{
    protected $table = 'home_statistics';

    protected $fillable = ['title', 'value', 'description', 'icon', 'sort_order'];
}
