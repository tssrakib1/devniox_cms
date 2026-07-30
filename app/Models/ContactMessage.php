<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMessage extends Model
{
    protected $fillable = ['subject', 'message', 'website'];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
