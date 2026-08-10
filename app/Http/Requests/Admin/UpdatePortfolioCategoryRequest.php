<?php

namespace App\Http\Requests\Admin;

use App\Models\PortfolioCategory;

class UpdatePortfolioCategoryRequest extends StorePortfolioCategoryRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->category());
    }

    public function rules(): array
    {
        return $this->categoryRules($this->category());
    }

    private function category(): PortfolioCategory
    {
        $category = $this->route('portfolio_category');

        return $category instanceof PortfolioCategory ? $category : PortfolioCategory::findOrFail($category);
    }
}
