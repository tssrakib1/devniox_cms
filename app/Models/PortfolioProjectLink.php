<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortfolioProjectLink extends Model
{
    protected $fillable = ['live_url', 'demo_url', 'github_url', 'documentation_url'];
}
