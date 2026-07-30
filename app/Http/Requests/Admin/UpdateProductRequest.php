<?php

namespace App\Http\Requests\Admin;

class UpdateProductRequest extends ProductRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('product'));
    }

    public function rules(): array
    {
        return $this->productRules($this->route('product'));
    }
}
