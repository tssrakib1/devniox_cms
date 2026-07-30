<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPostFaq extends Model
{
    protected $fillable = ['question', 'answer', 'sort_order'];
}
