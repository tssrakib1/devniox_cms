<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunicationReply extends Model
{
    protected $fillable = ['lead_id', 'administrator_id', 'direction', 'subject', 'message', 'replied_at'];

    protected function casts(): array
    {
        return ['replied_at' => 'datetime'];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function administrator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'administrator_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(CommunicationAttachment::class);
    }
}
