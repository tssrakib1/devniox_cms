<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductRequirement extends Model
{
    protected $fillable = ['php_version', 'laravel_version', 'database', 'hosting', 'browser_support', 'server_requirements'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
