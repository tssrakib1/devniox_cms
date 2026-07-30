<?php

namespace App\Http\Requests\Admin;

use App\Models\BlogPost;

class StoreBlogPostRequest extends BlogPostRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', BlogPost::class);
    }

    public function rules(): array
    {
        return $this->postRules();
    }
}
