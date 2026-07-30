<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPostSeo extends Model
{
    protected $table = 'blog_post_seo';

    protected $fillable = ['meta_title', 'meta_description', 'meta_keywords', 'canonical_url', 'open_graph_image_path', 'is_indexable'];

    protected function casts(): array
    {
        return ['is_indexable' => 'boolean'];
    }
}
