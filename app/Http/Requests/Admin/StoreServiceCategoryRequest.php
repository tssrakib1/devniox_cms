<?php

namespace App\Http\Requests\Admin;

use App\Enums\ServiceCategoryStatus;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', ServiceCategory::class);
    }

    public function rules(): array
    {
        return $this->categoryRules();
    }

    protected function categoryRules(?ServiceCategory $category = null): array
    {
        return ['name' => ['required', 'string', 'max:120'], 'slug' => ['required', 'string', 'max:140', 'alpha_dash:ascii', Rule::unique('service_categories', 'slug')->ignore($category)], 'description' => ['nullable', 'string', 'max:5000'], 'icon' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/'], 'sort_order' => ['required', 'integer', 'min:0', 'max:1000000'], 'status' => ['required', Rule::enum(ServiceCategoryStatus::class)], 'seo_title' => ['nullable', 'string', 'max:70'], 'seo_description' => ['nullable', 'string', 'max:160']];
    }
}
