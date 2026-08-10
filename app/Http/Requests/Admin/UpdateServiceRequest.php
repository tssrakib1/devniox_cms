<?php

namespace App\Http\Requests\Admin;

use App\Models\Service;

class UpdateServiceRequest extends ServiceRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->service());
    }

    public function rules(): array
    {
        return $this->serviceRules($this->service());
    }

    private function service(): Service
    {
        $service = $this->route('service');

        return $service instanceof Service ? $service : Service::findOrFail($service);
    }
}
