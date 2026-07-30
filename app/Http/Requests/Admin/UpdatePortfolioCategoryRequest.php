<?php

namespace App\Http\Requests\Admin;

class UpdatePortfolioCategoryRequest extends StorePortfolioCategoryRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('portfolio_category'));
    }

    public function rules(): array
    {
        return $this->categoryRules($this->route('portfolio_category'));
    }
}
