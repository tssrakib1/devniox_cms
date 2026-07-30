<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsContactContent extends Model
{
    protected $table = 'cms_contact_content';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['auto_reply_enabled' => 'boolean'];
    }
}
