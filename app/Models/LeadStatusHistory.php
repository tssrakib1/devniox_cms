<?php

namespace App\Models;

use App\Enums\LeadStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadStatusHistory extends Model
{
    public $timestamps = false;

    protected $fillable = ['changed_by', 'from_status', 'to_status', 'changed_at'];

    protected function casts(): array
    {
        return ['from_status' => LeadStatus::class, 'to_status' => LeadStatus::class, 'changed_at' => 'datetime'];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
