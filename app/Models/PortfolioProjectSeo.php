<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortfolioProjectSeo extends Model
{
    protected $table = 'portfolio_project_seo';

    protected $fillable = ['meta_title', 'meta_description', 'meta_keywords', 'canonical_url', 'open_graph_image_path', 'is_indexable'];

    protected function casts(): array
    {
        return ['is_indexable' => 'boolean'];
    }
}
