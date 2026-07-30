<?php

namespace App\Http\Requests\Admin;

use App\Enums\LeadStatus;
use App\Models\Lead;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkLeadActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Lead::class);
    }

    public function rules(): array
    {
        return ['lead_ids' => ['required', 'array', 'min:1', 'max:200'], 'lead_ids.*' => ['integer', 'distinct'], 'action' => ['required', Rule::in(['viewed', 'contacted', 'status', 'archive', 'restore', 'delete'])], 'status' => ['nullable', 'required_if:action,status', Rule::enum(LeadStatus::class)]];
    }
}
