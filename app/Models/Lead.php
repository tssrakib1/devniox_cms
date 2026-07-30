<?php

namespace App\Models;

use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
use App\Enums\LeadType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;

    protected $fillable = ['type', 'status', 'priority', 'name', 'company', 'email', 'phone', 'assigned_to', 'ip_address', 'user_agent', 'referrer', 'landing_url', 'submitted_at', 'read_at', 'replied_at', 'closed_at', 'converted_at', 'converted_order_id'];

    protected function casts(): array
    {
        return ['type' => LeadType::class, 'status' => LeadStatus::class, 'priority' => LeadPriority::class, 'submitted_at' => 'datetime', 'read_at' => 'datetime', 'replied_at' => 'datetime', 'closed_at' => 'datetime', 'converted_at' => 'datetime'];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function contactMessage(): HasOne
    {
        return $this->hasOne(ContactMessage::class);
    }

    public function demoRequest(): HasOne
    {
        return $this->hasOne(DemoRequest::class);
    }

    public function quoteRequest(): HasOne
    {
        return $this->hasOne(QuoteRequest::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(LeadNote::class)->latest();
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(LeadStatusHistory::class)->latest('changed_at');
    }

    public function events(): HasMany
    {
        return $this->hasMany(LeadEvent::class)->latest('occurred_at');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class)->latest();
    }

    public function replies(): HasMany
    {
        return $this->hasMany(CommunicationReply::class)->latest('replied_at');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(CommunicationAttachment::class)->latest();
    }

    public function convertedOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'converted_order_id');
    }

    public function scopeSearch(Builder $q, string $s): Builder
    {
        return $q->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('company', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%")->orWhere('id', $s)
            ->orWhereHas('contactMessage', fn ($q) => $q->where('subject', 'like', "%{$s}%"))
            ->orWhereHas('demoRequest.product', fn ($q) => $q->where('name', 'like', "%{$s}%"))
            ->orWhereHas('demoRequest.service', fn ($q) => $q->where('name', 'like', "%{$s}%"))
            ->orWhereHas('quoteRequest.product', fn ($q) => $q->where('name', 'like', "%{$s}%"))
            ->orWhereHas('quoteRequest.service', fn ($q) => $q->where('name', 'like', "%{$s}%")));
    }

    public function getItemAttribute(): Product|Service|PortfolioProject|null
    {
        return $this->demoRequest?->item ?? $this->quoteRequest?->item;
    }
}
