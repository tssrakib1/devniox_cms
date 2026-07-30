<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSeo extends Model
{
    protected $table = 'product_seo';

    protected $fillable = ['meta_title', 'meta_description', 'keywords', 'open_graph_image_path', 'canonical_url', 'is_indexable'];

    protected function casts(): array
    {
        return ['is_indexable' => 'boolean'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
