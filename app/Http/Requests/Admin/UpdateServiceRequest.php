<?php

namespace App\Http\Requests\Admin;

class UpdateServiceRequest extends ServiceRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('service'));
    }

    public function rules(): array
    {
        return $this->serviceRules($this->route('service'));
    }
}
