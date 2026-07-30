<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class StoreDemoRequest extends LeadRequest
{
    public function rules(): array
    {
        return $this->commonRules() + ['item_type' => ['required', Rule::in(['product', 'service'])], 'product_id' => ['nullable', 'required_if:item_type,product', 'prohibited_unless:item_type,product', Rule::exists('products', 'id')->where('status', 'published')->whereNull('deleted_at')], 'service_id' => ['nullable', 'required_if:item_type,service', 'prohibited_unless:item_type,service', Rule::exists('services', 'id')->where('status', 'published')->whereNull('deleted_at')], 'preferred_date' => ['nullable', 'date', 'after_or_equal:today'], 'preferred_time' => ['nullable', 'date_format:H:i'], 'meeting_type' => ['nullable', Rule::in(['online', 'offline'])], 'message' => ['nullable', 'string', 'max:10000']];
    }
}
