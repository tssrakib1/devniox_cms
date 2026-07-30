<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortfolioProjectFaq extends Model
{
    protected $fillable = ['question', 'answer', 'sort_order'];
}
