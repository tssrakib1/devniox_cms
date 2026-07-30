<?php

namespace App\Http\Requests\Admin;

use App\Enums\LeadType;
use App\Enums\OrderItemType;
use App\Models\Lead;
use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConvertQuoteToOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Order::class) && $this->user()->can('update', $this->route('lead'));
    }

    public function rules(): array
    {
        return ['item_name' => ['required', 'string', 'max:200'], 'item_type' => ['required', Rule::enum(OrderItemType::class)], 'amount' => ['required', 'numeric', 'min:0', 'max:999999999999.99'], 'expected_delivery_date' => ['nullable', 'date', 'after_or_equal:today']];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $lead = $this->route('lead');
            if (! $lead instanceof Lead || $lead->type !== LeadType::Quote) {
                $validator->errors()->add('lead', 'Only quote requests can be converted.');
            } elseif ($lead->converted_order_id) {
                $validator->errors()->add('lead', 'This quote has already been converted.');
            }
        });
    }
}
