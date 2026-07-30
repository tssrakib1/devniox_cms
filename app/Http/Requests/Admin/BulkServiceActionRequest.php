<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkServiceActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->is_active;
    }

    public function rules(): array
    {
        return ['service_ids' => ['required', 'array', 'min:1', 'max:200'], 'service_ids.*' => ['integer', 'distinct', 'exists:services,id'], 'action' => ['required', Rule::in(['publish', 'draft', 'archive', 'delete', 'feature', 'unfeature'])]];
    }
}
