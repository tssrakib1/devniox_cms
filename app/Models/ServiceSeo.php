<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceSeo extends Model
{
    protected $table = 'service_seo';

    protected $fillable = ['meta_title', 'meta_description', 'meta_keywords', 'canonical_url', 'open_graph_image_path', 'is_indexable'];

    protected function casts(): array
    {
        return ['is_indexable' => 'boolean'];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
