<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class LeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function commonRules(): array
    {
        return ['name' => ['required', 'string', 'max:160'], 'company' => ['nullable', 'string', 'max:180'], 'email' => ['required', 'email:rfc', 'max:254'], 'phone' => ['nullable', 'string', 'max:40', 'regex:/^[0-9+()\-\s.]+$/'], 'landing_url' => ['nullable', 'url:http,https', 'max:2048'], 'website_confirmation' => ['nullable', 'prohibited']];
    }

    public function messages(): array
    {
        return ['website_confirmation.prohibited' => 'Your submission could not be accepted.', 'phone.regex' => 'Enter a valid phone number.'];
    }
}
