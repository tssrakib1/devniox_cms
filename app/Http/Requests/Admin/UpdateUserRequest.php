<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('users.edit') ?? false;
    }

    public function rules(): array
    {
        $target = $this->route('user');

        return ['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($target)], 'phone' => ['nullable', 'string', 'max:40', 'regex:/^[0-9+()\-\.\s]+$/'], 'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'], 'password' => ['nullable', 'confirmed', Password::defaults()], 'role_id' => ['required', 'exists:roles,id'], 'is_active' => ['sometimes', 'boolean']];
    }
}
