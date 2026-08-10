<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductCategory;

class UpdateProductCategoryRequest extends StoreProductCategoryRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->category());
    }

    public function rules(): array
    {
        return $this->categoryRules($this->category());
    }

    private function category(): ProductCategory
    {
        $category = $this->route('category');

        return $category instanceof ProductCategory ? $category : ProductCategory::findOrFail($category);
    }
}
