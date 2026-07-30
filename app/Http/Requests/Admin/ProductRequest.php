<?php

namespace App\Http\Requests\Admin;

use App\Enums\BillingType;
use App\Enums\ProductStatus;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class ProductRequest extends FormRequest
{
    protected function productRules(?Product $product = null): array
    {
        $image = ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'];

        return [
            'product_category_id' => ['required', 'integer', Rule::exists('product_categories', 'id')->where('is_active', true)],
            'name' => ['required', 'string', 'max:180'],
            'slug' => ['required', 'string', 'max:200', 'alpha_dash:ascii', Rule::unique('products', 'slug')->ignore($product)],
            'version' => ['required', 'string', 'max:40', 'regex:/^[0-9]+(?:\.[0-9]+){1,3}(?:[-+][0-9A-Za-z.-]+)?$/'],
            'status' => ['required', Rule::enum(ProductStatus::class)],
            'is_featured' => ['required', 'boolean'],
            'display_order' => ['required', 'integer', 'min:0', 'max:1000000'],
            'short_description' => ['required', 'string', 'max:300'],
            'full_description' => ['required', 'string', 'max:100000'],
            'thumbnail' => $image,
            'banner' => $image,
            'logo' => $image,
            'highlights' => ['nullable', 'array', 'max:100'],
            'highlights.*.title' => ['required', 'string', 'max:160'],
            'highlights.*.description' => ['required', 'string', 'max:5000'],
            'highlights.*.icon' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/'],
            'highlights.*.sort_order' => ['required', 'integer', 'min:0', 'max:1000000'],
            'modules' => ['nullable', 'array', 'max:200'],
            'modules.*.name' => ['required', 'string', 'max:160'],
            'modules.*.description' => ['nullable', 'string', 'max:5000'],
            'modules.*.sort_order' => ['required', 'integer', 'min:0', 'max:1000000'],
            'features' => ['nullable', 'array', 'max:300'],
            'features.*.title' => ['required', 'string', 'max:160'],
            'features.*.description' => ['required', 'string', 'max:5000'],
            'features.*.icon' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/'],
            'features.*.sort_order' => ['required', 'integer', 'min:0', 'max:1000000'],
            'requirements.php_version' => ['nullable', 'string', 'max:80'],
            'requirements.laravel_version' => ['nullable', 'string', 'max:80'],
            'requirements.database' => ['nullable', 'string', 'max:160'],
            'requirements.hosting' => ['nullable', 'string', 'max:255'],
            'requirements.browser_support' => ['nullable', 'string', 'max:255'],
            'requirements.server_requirements' => ['nullable', 'string', 'max:10000'],
            'links.live_demo_url' => ['nullable', 'url:http,https', 'max:2048'],
            'links.video_url' => ['nullable', 'url:http,https', 'max:2048'],
            'links.documentation_url' => ['nullable', 'url:http,https', 'max:2048'],
            'seo.meta_title' => ['nullable', 'string', 'max:70'],
            'seo.meta_description' => ['nullable', 'string', 'max:160'],
            'seo.keywords' => ['nullable', 'string', 'max:500'],
            'seo.open_graph_image' => $image,
            'seo.canonical_url' => ['nullable', 'url:http,https', 'max:2048'],
            'seo.is_indexable' => ['required', 'boolean'],
            'gallery_images' => ['nullable', 'array', 'max:30'],
            'gallery_images.*' => $image,
            'gallery_existing' => ['nullable', 'array'],
            'gallery_existing.*.alt_text' => ['nullable', 'string', 'max:180'],
            'gallery_existing.*.sort_order' => ['required', 'integer', 'min:0', 'max:1000000'],
            'gallery_remove' => ['nullable', 'array'],
            'gallery_remove.*' => ['integer'],
            'gallery_replacements' => ['nullable', 'array'],
            'gallery_replacements.*' => $image,
            'pricing_plans' => ['nullable', 'array', 'max:50'],
            'pricing_plans.*.id' => ['nullable', 'integer'],
            'pricing_plans.*.name' => ['required', 'string', 'max:120'],
            'pricing_plans.*.price' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'pricing_plans.*.currency' => ['required', 'string', 'size:3', 'uppercase'],
            'pricing_plans.*.billing_type' => ['required', Rule::enum(BillingType::class)],
            'pricing_plans.*.description' => ['nullable', 'string', 'max:5000'],
            'pricing_plans.*.is_highlighted' => ['required', 'boolean'],
            'pricing_plans.*.sort_order' => ['required', 'integer', 'min:0', 'max:1000000'],
            'pricing_plans.*.is_active' => ['required', 'boolean'],
            'pricing_plans.*.feature_list' => ['nullable', 'string', 'max:10000'],
            'faqs' => ['nullable', 'array', 'max:100'],
            'faqs.*.question' => ['required', 'string', 'max:255'],
            'faqs.*.answer' => ['required', 'string', 'max:10000'],
            'faqs.*.sort_order' => ['required', 'integer', 'min:0', 'max:1000000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_featured' => $this->boolean('is_featured'),
            'seo' => array_merge($this->input('seo', []), ['is_indexable' => $this->boolean('seo.is_indexable')]),
        ]);
    }
}
