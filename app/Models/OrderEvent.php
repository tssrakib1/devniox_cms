<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderEvent extends Model
{
    public $timestamps = false;

    protected $fillable = ['order_id', 'actor_id', 'event_type', 'description', 'old_values', 'new_values', 'occurred_at'];

    protected function casts(): array
    {
        return ['old_values' => 'array', 'new_values' => 'array', 'occurred_at' => 'datetime'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
