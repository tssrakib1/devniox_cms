<?php

namespace App\Http\Requests\Admin;

use App\Models\ExpenseCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpenseCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $category = $this->route('expense_category');

        return $category ? $this->user()->can('update', $category) : $this->user()->can('create', ExpenseCategory::class);
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:120'], 'slug' => ['required', 'string', 'max:140', 'alpha_dash', Rule::unique('expense_categories')->ignore($this->route('expense_category'))], 'description' => ['nullable', 'string', 'max:3000'], 'active' => ['required', 'boolean']];
    }
}
