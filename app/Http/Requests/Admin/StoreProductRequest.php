<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;

class StoreProductRequest extends ProductRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Product::class);
    }

    public function rules(): array
    {
        return $this->productRules();
    }
}
