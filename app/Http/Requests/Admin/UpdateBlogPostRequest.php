<?php

namespace App\Http\Requests\Admin;

class UpdateBlogPostRequest extends BlogPostRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('post'));
    }

    public function rules(): array
    {
        return $this->postRules($this->route('post'));
    }
}
