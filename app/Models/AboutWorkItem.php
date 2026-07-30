<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutWorkItem extends Model
{
    protected $table = 'about_work_items';

    protected $fillable = ['title', 'description', 'icon', 'sort_order'];
}
