<?php

namespace App\Http\Requests\Admin;

class UpdateProductCategoryRequest extends StoreProductCategoryRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('category'));
    }

    public function rules(): array
    {
        return $this->categoryRules($this->route('category'));
    }
}
