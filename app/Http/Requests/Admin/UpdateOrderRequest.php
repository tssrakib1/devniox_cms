<?php

namespace App\Http\Requests\Admin;

class UpdateOrderRequest extends OrderRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('order'));
    }
}
