<?php

namespace App\Http\Requests\Installer;

use Illuminate\Foundation\Http\FormRequest;

class DatabaseConfigurationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'between:1,65535'],
            'database' => ['required', 'string', 'max:128', 'regex:/^[A-Za-z0-9_.-]+$/'],
            'username' => ['required', 'string', 'max:128'],
            'password' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return ['database.regex' => 'The database name may contain only letters, numbers, dots, dashes, and underscores.'];
    }
}
