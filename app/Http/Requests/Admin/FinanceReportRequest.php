<?php

namespace App\Http\Requests\Admin;

use App\Enums\FinanceTransactionStatus;
use App\Enums\FinanceTransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinanceReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_active === true;
    }

    public function rules(): array
    {
        return ['period' => ['nullable', Rule::in(['today', 'week', 'month', 'year', 'custom'])], 'date_from' => ['nullable', 'date'], 'date_to' => ['nullable', 'date', 'after_or_equal:date_from'], 'type' => ['nullable', Rule::enum(FinanceTransactionType::class)], 'status' => ['nullable', Rule::enum(FinanceTransactionStatus::class)], 'income_category_id' => ['nullable', 'integer', 'exists:income_categories,id'], 'expense_category_id' => ['nullable', 'integer', 'exists:expense_categories,id']];
    }
}
