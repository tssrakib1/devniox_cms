<?php

namespace App\Http\Requests\Admin;

use App\Enums\OrderItemType;
use App\Enums\OrderPriority;
use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class OrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:160'],
            'company_name' => ['nullable', 'string', 'max:180'],
            'email' => ['required', 'email:rfc', 'max:254'],
            'phone' => ['nullable', 'string', 'max:40'],
            'whatsapp' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:3000'],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'priority' => ['required', Rule::enum(OrderPriority::class)],
            'status' => ['required', Rule::enum(OrderStatus::class)],
            'source' => ['required', Rule::enum(OrderSource::class)],
            'lead_id' => ['nullable', 'required_if:source,lead', 'integer', 'exists:leads,id'],
            'discount' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'paid_amount' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'payment_method' => ['nullable', Rule::enum(PaymentMethod::class)],
            'sync_finance' => ['nullable', 'boolean'],
            'finance_income_category_id' => ['nullable', 'required_if:sync_finance,1', 'integer', 'exists:income_categories,id'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.name' => ['required', 'string', 'max:200'],
            'items.*.type' => ['required', Rule::enum(OrderItemType::class)],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'items.*.discount' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Add at least one order item.',
            'lead_id.required_if' => 'Select the source lead for a lead-based order.',
            'expected_delivery_date.after_or_equal' => 'The expected delivery date must be on or after the order date.',
        ];
    }
}
