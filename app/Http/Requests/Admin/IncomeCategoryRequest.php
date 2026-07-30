<?php

namespace App\Http\Requests\Admin;

use App\Models\IncomeCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IncomeCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $category = $this->route('income_category');

        return $category ? $this->user()->can('update', $category) : $this->user()->can('create', IncomeCategory::class);
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:120'], 'slug' => ['required', 'string', 'max:140', 'alpha_dash', Rule::unique('income_categories')->ignore($this->route('income_category'))], 'description' => ['nullable', 'string', 'max:3000'], 'active' => ['required', 'boolean']];
    }
}
