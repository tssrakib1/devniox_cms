<?php

namespace App\Http\Requests;

use App\Enums\LeadItemType;
use Illuminate\Validation\Rule;

class StoreQuoteRequest extends LeadRequest
{
    public function rules(): array
    {
        return $this->commonRules() + ['item_type' => ['required', Rule::enum(LeadItemType::class)], 'product_id' => ['nullable', 'required_if:item_type,product', 'prohibited_unless:item_type,product', Rule::exists('products', 'id')->where('status', 'published')->whereNull('deleted_at')], 'service_id' => ['nullable', 'required_if:item_type,service', 'prohibited_unless:item_type,service', Rule::exists('services', 'id')->where('status', 'published')->whereNull('deleted_at')], 'portfolio_project_id' => ['nullable', 'required_if:item_type,portfolio', 'prohibited_unless:item_type,portfolio', Rule::exists('portfolio_projects', 'id')->where('status', 'published')->whereNull('deleted_at')], 'budget' => ['nullable', 'string', 'max:120'], 'timeline' => ['nullable', 'string', 'max:120'], 'requirement_details' => ['required', 'string', 'min:20', 'max:30000'], 'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,webp', 'max:10240']];
    }
}
