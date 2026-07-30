<?php

namespace App\Http\Requests\Admin;

use App\Enums\PortfolioStatus;
use App\Models\PortfolioProject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class PortfolioProjectRequest extends FormRequest
{
    protected function projectRules(?PortfolioProject $p = null): array
    {
        $image = ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'];
        $r = ['portfolio_category_id' => ['required', 'integer', Rule::exists('portfolio_categories', 'id')->where('status', 'published')->whereNull('deleted_at')], 'name' => ['required', 'string', 'max:180'], 'slug' => ['required', 'string', 'max:200', 'alpha_dash:ascii', Rule::unique('portfolio_projects', 'slug')->ignore($p)], 'client_name' => ['nullable', 'string', 'max:180'], 'industry' => ['nullable', 'string', 'max:160'], 'completion_date' => ['required', 'date', 'before_or_equal:today'], 'status' => ['required', Rule::enum(PortfolioStatus::class)], 'is_featured' => ['required', 'boolean'], 'display_order' => ['required', 'integer', 'min:0', 'max:1000000'], 'thumbnail' => $image, 'cover_image' => $image, 'short_description' => ['required', 'string', 'max:300'], 'full_description' => ['required', 'string', 'max:100000']];
        foreach (['objectives', 'solutions', 'results'] as $k) {
            $r[$k] = ['nullable', 'array', 'max:100'];
            $r[$k.'.*.title'] = ['required', 'string', 'max:180'];
            $r[$k.'.*.description'] = ['required', 'string', 'max:5000'];
            $r[$k.'.*.sort_order'] = ['required', 'integer', 'min:0'];
        }$r += ['features' => ['nullable', 'array', 'max:200'], 'features.*.title' => ['required', 'string', 'max:180'], 'features.*.description' => ['required', 'string', 'max:5000'], 'features.*.icon' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/'], 'features.*.sort_order' => ['required', 'integer', 'min:0'], 'technologies' => ['nullable', 'array', 'max:100'], 'technologies.*.name' => ['required', 'string', 'max:140'], 'technologies.*.icon' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/'], 'technologies.*.sort_order' => ['required', 'integer', 'min:0'], 'links.live_url' => ['nullable', 'url:http,https', 'max:2048'], 'links.demo_url' => ['nullable', 'url:http,https', 'max:2048'], 'links.github_url' => ['nullable', 'url:http,https', 'max:2048'], 'links.documentation_url' => ['nullable', 'url:http,https', 'max:2048'], 'gallery_images' => ['nullable', 'array', 'max:30'], 'gallery_images.*' => $image, 'gallery_existing' => ['nullable', 'array'], 'gallery_existing.*.alt_text' => ['nullable', 'string', 'max:180'], 'gallery_existing.*.sort_order' => ['required', 'integer', 'min:0'], 'gallery_remove' => ['nullable', 'array'], 'gallery_remove.*' => ['integer'], 'gallery_replacements' => ['nullable', 'array'], 'gallery_replacements.*' => $image, 'faqs' => ['nullable', 'array', 'max:100'], 'faqs.*.question' => ['required', 'string', 'max:255'], 'faqs.*.answer' => ['required', 'string', 'max:10000'], 'faqs.*.sort_order' => ['required', 'integer', 'min:0'], 'seo.meta_title' => ['nullable', 'string', 'max:70'], 'seo.meta_description' => ['nullable', 'string', 'max:160'], 'seo.meta_keywords' => ['nullable', 'string', 'max:500'], 'seo.canonical_url' => ['nullable', 'url:http,https', 'max:2048'], 'seo.open_graph_image' => $image, 'seo.is_indexable' => ['required', 'boolean']];

        return $r;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_featured' => $this->boolean('is_featured'), 'seo' => array_merge($this->input('seo', []), ['is_indexable' => $this->boolean('seo.is_indexable')])]);
    }

    public function messages(): array
    {
        return ['portfolio_category_id.exists' => 'Choose a published portfolio category.', 'completion_date.before_or_equal' => 'The completion date cannot be in the future.', 'slug.unique' => 'This portfolio slug is already in use.'];
    }
}
