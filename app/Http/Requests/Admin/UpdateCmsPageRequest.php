<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCmsPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('page'));
    }

    public function rules(): array
    {
        $base = ['status' => ['required', 'in:draft,published'], 'meta_title' => ['nullable', 'string', 'max:70'], 'meta_description' => ['nullable', 'string', 'max:160'], 'meta_keywords' => ['nullable', 'string', 'max:500'], 'canonical_url' => ['nullable', 'url:http,https', 'max:2048'], 'open_graph_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'], 'is_indexable' => ['required', 'boolean']];
        $images = ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'];
        if ($this->route('page')->key === 'home') {
            return $base + ['hero_heading' => ['required', 'max:200'], 'hero_subheading' => ['nullable', 'max:200'], 'hero_description' => ['required', 'max:5000'], 'hero_background' => $images, 'primary_button_text' => ['nullable', 'max:80'], 'primary_button_url' => ['nullable', 'url:http,https'], 'secondary_button_text' => ['nullable', 'max:80'], 'secondary_button_url' => ['nullable', 'url:http,https'], 'intro_title' => ['required', 'max:180'], 'intro_description' => ['required', 'max:10000'], 'intro_image' => $images, 'products_title' => ['required', 'max:180'], 'products_description' => ['nullable', 'max:1000'], 'services_title' => ['required', 'max:180'], 'services_description' => ['nullable', 'max:1000'], 'portfolio_title' => ['required', 'max:180'], 'portfolio_description' => ['nullable', 'max:1000'], 'articles_title' => ['required', 'max:180'], 'articles_description' => ['nullable', 'max:1000'], 'ecosystem_enabled' => ['required', 'boolean'], 'ecosystem_label' => ['required', 'max:80'], 'ecosystem_title' => ['required', 'max:180'], 'ecosystem_description' => ['required', 'max:2000'], 'ecosystem_note' => ['required', 'max:1000'], 'why_items' => ['nullable', 'array'], 'why_items.*.title' => ['required', 'max:180'], 'why_items.*.description' => ['nullable', 'max:2000'], 'why_items.*.icon' => ['nullable', 'max:100'], 'why_items.*.sort_order' => ['required', 'integer', 'min:0'], 'statistics' => ['nullable', 'array'], 'statistics.*.title' => ['required', 'max:180'], 'statistics.*.value' => ['required', 'max:80'], 'statistics.*.description' => ['nullable', 'max:2000'], 'statistics.*.icon' => ['nullable', 'max:100'], 'statistics.*.sort_order' => ['required', 'integer', 'min:0']];
        }if ($this->route('page')->key === 'about') {
            return $base + ['hero_heading' => ['required', 'max:200'], 'hero_description' => ['required', 'max:5000'], 'hero_banner' => $images, 'story_title' => ['required', 'max:180'], 'story_description' => ['required', 'max:10000'], 'story_image' => $images, 'mission_title' => ['required', 'max:180'], 'mission_description' => ['required', 'max:5000'], 'vision_title' => ['required', 'max:180'], 'vision_description' => ['required', 'max:5000'], 'core_values' => ['nullable', 'array'], 'work_items' => ['nullable', 'array']];
        }

        return $base + ['hero_heading' => ['required', 'max:200'], 'hero_description' => ['required', 'max:5000'], 'hero_banner' => $images, 'company_name' => ['required', 'max:180'], 'address' => ['nullable', 'max:2000'], 'email' => ['required', 'email'], 'phone' => ['nullable', 'max:50'], 'whatsapp' => ['nullable', 'max:50'], 'map_embed_url' => ['nullable', 'url:http,https'], 'success_message' => ['required', 'max:300'], 'auto_reply_enabled' => ['required', 'boolean'], 'auto_reply_subject' => ['nullable', 'max:180'], 'auto_reply_message' => ['nullable', 'max:10000'], 'business_hours' => ['required', 'array', 'size:7']];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_indexable' => $this->boolean('is_indexable'), 'auto_reply_enabled' => $this->boolean('auto_reply_enabled'), 'ecosystem_enabled' => $this->boolean('ecosystem_enabled')]);
    }
}

