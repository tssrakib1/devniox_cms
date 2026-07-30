<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeWhyItem extends Model
{
    protected $fillable = ['title', 'description', 'icon', 'sort_order'];
}
