<?php

namespace App\Http\Requests\Admin;

use App\Enums\FinancePaymentMethod;
use App\Enums\FinanceTransactionSource;
use App\Enums\FinanceTransactionStatus;
use App\Enums\FinanceTransactionType;
use App\Models\FinanceTransaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinanceTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $transaction = $this->route('transaction');

        return $transaction ? $this->user()->can('update', $transaction) : $this->user()->can('create', FinanceTransaction::class);
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(FinanceTransactionType::class)],
            'source' => ['required', Rule::enum(FinanceTransactionSource::class)],
            'income_category_id' => ['nullable', 'required_if:type,income', 'integer', 'exists:income_categories,id'],
            'expense_category_id' => ['nullable', 'required_if:type,expense', 'integer', 'exists:expense_categories,id'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:10000'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:999999999999.99'],
            'payment_method' => ['required', Rule::enum(FinancePaymentMethod::class)],
            'transaction_date' => ['required', 'date'],
            'status' => ['required', Rule::enum(FinanceTransactionStatus::class)],
            'reference' => ['nullable', 'string', 'max:255'],
        ];
    }
}
