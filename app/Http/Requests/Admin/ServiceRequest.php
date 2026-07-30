<?php

namespace App\Http\Requests\Admin;

use App\Enums\ServiceStatus;
use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class ServiceRequest extends FormRequest
{
    protected function serviceRules(?Service $service = null): array
    {
        $image = ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'];

        return ['service_category_id' => ['required', 'integer', Rule::exists('service_categories', 'id')->where('status', 'published')->whereNull('deleted_at')], 'name' => ['required', 'string', 'max:180'], 'slug' => ['required', 'string', 'max:200', 'alpha_dash:ascii', Rule::unique('services', 'slug')->ignore($service)], 'cover_image' => $image, 'featured_image' => $image, 'status' => ['required', Rule::enum(ServiceStatus::class)], 'is_featured' => ['required', 'boolean'], 'display_order' => ['required', 'integer', 'min:0', 'max:1000000'], 'short_description' => ['required', 'string', 'max:300'], 'full_description' => ['required', 'string', 'max:100000'],
            'benefits' => ['nullable', 'array', 'max:100'], 'benefits.*.title' => ['required', 'string', 'max:160'], 'benefits.*.description' => ['required', 'string', 'max:5000'], 'benefits.*.icon' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/'], 'benefits.*.sort_order' => ['required', 'integer', 'min:0'],
            'process_steps' => ['nullable', 'array', 'max:100'], 'process_steps.*.step_number' => ['required', 'integer', 'min:1', 'distinct'], 'process_steps.*.title' => ['required', 'string', 'max:160'], 'process_steps.*.description' => ['required', 'string', 'max:5000'], 'process_steps.*.sort_order' => ['required', 'integer', 'min:0'],
            'features' => ['nullable', 'array', 'max:200'], 'features.*.title' => ['required', 'string', 'max:160'], 'features.*.description' => ['required', 'string', 'max:5000'], 'features.*.icon' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/'], 'features.*.sort_order' => ['required', 'integer', 'min:0'],
            'technologies' => ['nullable', 'array', 'max:100'], 'technologies.*.name' => ['required', 'string', 'max:120'], 'technologies.*.icon' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/'], 'technologies.*.sort_order' => ['required', 'integer', 'min:0'], 'technology_images' => ['nullable', 'array'], 'technology_images.*' => $image,
            'deliverables' => ['nullable', 'array', 'max:100'], 'deliverables.*.title' => ['required', 'string', 'max:180'], 'deliverables.*.description' => ['nullable', 'string', 'max:5000'], 'deliverables.*.sort_order' => ['required', 'integer', 'min:0'],
            'gallery_images' => ['nullable', 'array', 'max:30'], 'gallery_images.*' => $image, 'gallery_existing' => ['nullable', 'array'], 'gallery_existing.*.alt_text' => ['nullable', 'string', 'max:180'], 'gallery_existing.*.sort_order' => ['required', 'integer', 'min:0'], 'gallery_remove' => ['nullable', 'array'], 'gallery_remove.*' => ['integer'], 'gallery_replacements' => ['nullable', 'array'], 'gallery_replacements.*' => $image,
            'faqs' => ['nullable', 'array', 'max:100'], 'faqs.*.question' => ['required', 'string', 'max:255'], 'faqs.*.answer' => ['required', 'string', 'max:10000'], 'faqs.*.sort_order' => ['required', 'integer', 'min:0'],
            'seo.meta_title' => ['nullable', 'string', 'max:70'], 'seo.meta_description' => ['nullable', 'string', 'max:160'], 'seo.meta_keywords' => ['nullable', 'string', 'max:500'], 'seo.canonical_url' => ['nullable', 'url:http,https', 'max:2048'], 'seo.open_graph_image' => $image, 'seo.is_indexable' => ['required', 'boolean']];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_featured' => $this->boolean('is_featured'), 'seo' => array_merge($this->input('seo', []), ['is_indexable' => $this->boolean('seo.is_indexable')])]);
    }
}
