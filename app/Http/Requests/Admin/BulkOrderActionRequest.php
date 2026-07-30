<?php

namespace App\Http\Requests\Admin;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkOrderActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Order::class);
    }

    public function rules(): array
    {
        return [
            'order_ids' => ['required', 'array', 'min:1', 'max:200'],
            'order_ids.*' => ['integer', 'distinct', 'exists:orders,id'],
            'action' => ['required', Rule::in(['status', 'archive', 'delete'])],
            'status' => ['nullable', 'required_if:action,status', Rule::enum(OrderStatus::class)],
        ];
    }
}
