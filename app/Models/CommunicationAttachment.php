<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationAttachment extends Model
{
    protected $fillable = ['media_asset_id', 'lead_id', 'communication_reply_id', 'uploaded_by', 'label', 'file_path', 'original_name', 'mime_type', 'file_size'];

    public function mediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function reply(): BelongsTo
    {
        return $this->belongsTo(CommunicationReply::class, 'communication_reply_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
