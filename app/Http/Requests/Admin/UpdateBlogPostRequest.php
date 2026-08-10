<?php

namespace App\Http\Requests\Admin;

use App\Models\BlogPost;

class UpdateBlogPostRequest extends BlogPostRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->post());
    }

    public function rules(): array
    {
        return $this->postRules($this->post());
    }

    private function post(): BlogPost
    {
        $post = $this->route('post');

        return $post instanceof BlogPost ? $post : BlogPost::findOrFail($post);
    }
}
