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

            'seo.meta_title' => ['nullable', 'string', 'max:70'], 'seo.meta_description' => ['nullable', 'string', 'max:160'], 'seo.meta_keywords' => ['nullable', 'string', 'max:500'], 'seo.canonical_url' => ['nullable', 'url:http,https', 'max:2048'], 'seo.open_graph_image' => $image, 'seo.is_indexable' => ['required', 'boolean']];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_featured' => $this->boolean('is_featured'), 'seo' => array_merge($this->input('seo', []), ['is_indexable' => $this->boolean('seo.is_indexable')])]);
    }
}

