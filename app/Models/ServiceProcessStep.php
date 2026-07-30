<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceProcessStep extends Model
{
    protected $fillable = ['step_number', 'title', 'description', 'sort_order'];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
