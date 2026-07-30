<?php

namespace App\Http\Requests\Admin;

use App\Models\BlogPost;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkBlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', BlogPost::class);
    }

    public function rules(): array
    {
        return ['post_ids' => ['required', 'array', 'min:1', 'max:200'], 'post_ids.*' => ['integer', 'distinct', 'exists:blog_posts,id'], 'action' => ['required', Rule::in(['publish', 'draft', 'schedule', 'archive', 'delete', 'restore', 'feature', 'unfeature'])], 'published_at' => ['nullable', 'date', Rule::requiredIf($this->input('action') === 'schedule')]];
    }
}
