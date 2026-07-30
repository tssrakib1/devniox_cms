<?php

namespace App\Http\Requests\Admin;

use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('lead'));
    }

    public function rules(): array
    {
        return ['status' => ['required', Rule::enum(LeadStatus::class)], 'priority' => ['required', Rule::enum(LeadPriority::class)], 'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')->where('is_active', true)]];
    }
}
