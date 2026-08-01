<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsSimplePageContent extends Model
{
    protected $table = 'cms_simple_page_content';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['steps' => 'array', 'bullets' => 'array'];
    }
}
