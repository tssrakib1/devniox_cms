<?php

namespace App\Http\Requests\Admin;

use App\Models\Order;

class StoreOrderRequest extends OrderRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Order::class);
    }
}
