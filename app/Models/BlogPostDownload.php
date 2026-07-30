<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPostDownload extends Model
{
    protected $fillable = ['title', 'file_path', 'original_name', 'mime_type', 'file_size', 'sort_order'];
}
