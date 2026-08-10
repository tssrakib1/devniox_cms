<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;

class UpdateProductRequest extends ProductRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->product());
    }

    public function rules(): array
    {
        return $this->productRules($this->product());
    }

    private function product(): Product
    {
        $product = $this->route('product');

        return $product instanceof Product ? $product : Product::findOrFail($product);
    }
}
