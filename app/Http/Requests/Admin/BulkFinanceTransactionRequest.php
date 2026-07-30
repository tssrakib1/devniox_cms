<?php

namespace App\Http\Requests\Admin;

use App\Models\FinanceTransaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkFinanceTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', FinanceTransaction::class);
    }

    public function rules(): array
    {
        return ['transaction_ids' => ['required', 'array', 'min:1', 'max:200'], 'transaction_ids.*' => ['integer', 'distinct'], 'action' => ['required', Rule::in(['archive', 'delete', 'restore'])]];
    }
}
