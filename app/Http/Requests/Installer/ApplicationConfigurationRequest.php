<?php

namespace App\Http\Requests\Installer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApplicationConfigurationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'url' => ['required', 'url:http,https', 'max:255'],
            'timezone' => ['required', 'timezone'],
            'currency' => ['required', 'string', 'size:3', 'regex:/^[A-Za-z]{3}$/'],
            'environment' => ['required', Rule::in(['production', 'staging', 'local'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'url' => rtrim((string) $this->input('url'), '/'),
            'currency' => strtoupper((string) $this->input('currency')),
        ]);
    }
}
