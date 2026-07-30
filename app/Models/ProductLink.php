<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductLink extends Model
{
    protected $fillable = ['live_demo_url', 'video_url', 'documentation_url'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
