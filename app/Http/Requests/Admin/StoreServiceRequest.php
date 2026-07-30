<?php

namespace App\Http\Requests\Admin;

use App\Models\Service;

class StoreServiceRequest extends ServiceRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Service::class);
    }

    public function rules(): array
    {
        return $this->serviceRules();
    }
}
