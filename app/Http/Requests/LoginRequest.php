<?php

namespace App\Http\Requests;

use App\Services\ActivityLogService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['email' => ['required', 'email'], 'password' => ['required', 'string'], 'remember' => ['nullable', 'boolean']];
    }

    public function authenticate(): void
    {
        if (! Auth::attempt($this->safe()->only('email', 'password'), $this->boolean('remember')) || ! Auth::user()?->is_active) {
            Auth::logout();
            ActivityLogService::log('authentication', 'failed_login', 'Failed login attempt for '.$this->string('email')->value().'.', null, null, ['email' => $this->string('email')->value()]);
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }
    }
}
