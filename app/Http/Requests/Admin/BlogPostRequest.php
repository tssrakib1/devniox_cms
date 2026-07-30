<?php

namespace App\Http\Requests\Admin;

use App\Enums\BlogStatus;
use App\Models\BlogPost;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class BlogPostRequest extends FormRequest
{
    protected function postRules(?BlogPost $p = null): array
    {
        return ['blog_category_id' => ['required', 'exists:blog_categories,id'], 'author_id' => ['required', 'exists:users,id'], 'title' => ['required', 'string', 'max:200'], 'slug' => ['required', 'alpha_dash:ascii', 'max:220', Rule::unique('blog_posts', 'slug')->ignore($p)], 'status' => ['required', Rule::enum(BlogStatus::class)], 'is_featured' => ['required', 'boolean'], 'published_at' => ['nullable', 'date', Rule::requiredIf($this->input('status') === 'scheduled')], 'display_order' => ['required', 'integer', 'min:0'], 'featured_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'], 'social_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'], 'summary' => ['required', 'string', 'min:40', 'max:320'], 'body' => ['required', 'string', 'min:100', 'max:500000'], 'tag_ids' => ['nullable', 'array'], 'tag_ids.*' => ['integer', 'exists:blog_tags,id'], 'product_ids' => ['nullable', 'array'], 'product_ids.*' => ['integer', 'exists:products,id'], 'service_ids' => ['nullable', 'array'], 'service_ids.*' => ['integer', 'exists:services,id'], 'faqs' => ['nullable', 'array', 'max:100'], 'faqs.*.question' => ['required', 'string', 'max:255'], 'faqs.*.answer' => ['required', 'string', 'max:10000'], 'faqs.*.sort_order' => ['required', 'integer', 'min:0'], 'downloads' => ['nullable', 'array', 'max:20'], 'downloads.*' => ['file', 'mimes:pdf,doc,docx,xls,xlsx,zip', 'max:20480'], 'seo.meta_title' => ['nullable', 'string', 'max:70'], 'seo.meta_description' => ['nullable', 'string', 'max:160'], 'seo.meta_keywords' => ['nullable', 'string', 'max:500'], 'seo.canonical_url' => ['nullable', 'url:http,https', 'max:2048'], 'seo.open_graph_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'], 'seo.is_indexable' => ['required', 'boolean']];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_featured' => $this->boolean('is_featured'), 'seo' => array_merge($this->input('seo', []), ['is_indexable' => $this->boolean('seo.is_indexable')])]);
    }
}
