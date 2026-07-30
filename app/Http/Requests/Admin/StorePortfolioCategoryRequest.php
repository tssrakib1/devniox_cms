<?php

namespace App\Http\Requests\Admin;

use App\Enums\PortfolioCategoryStatus;
use App\Models\PortfolioCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePortfolioCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', PortfolioCategory::class);
    }

    public function rules(): array
    {
        return $this->categoryRules();
    }

    protected function categoryRules(?PortfolioCategory $c = null): array
    {
        return ['name' => ['required', 'string', 'max:120'], 'slug' => ['required', 'string', 'max:140', 'alpha_dash:ascii', Rule::unique('portfolio_categories', 'slug')->ignore($c)], 'description' => ['nullable', 'string', 'max:5000'], 'icon' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/'], 'sort_order' => ['required', 'integer', 'min:0', 'max:1000000'], 'status' => ['required', Rule::enum(PortfolioCategoryStatus::class)], 'seo_title' => ['nullable', 'string', 'max:70'], 'seo_description' => ['nullable', 'string', 'max:160']];
    }
}
