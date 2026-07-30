<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('users.create') ?? false;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email:rfc', 'max:255', 'unique:users,email'], 'phone' => ['nullable', 'string', 'max:40', 'regex:/^[0-9+()\-\.\s]+$/'], 'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'], 'password' => ['required', 'confirmed', Password::defaults()], 'role_id' => ['required', 'exists:roles,id'], 'is_active' => ['sometimes', 'boolean']];
    }
}
