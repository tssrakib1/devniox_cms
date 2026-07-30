<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('roles.edit') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->route('role')?->is_system) {
            $this->merge(['name' => $this->route('role')->name]);
        }
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:80', Rule::unique('roles', 'name')->ignore($this->route('role'))], 'permissions' => ['array'], 'permissions.*' => ['integer', 'exists:permissions,id']];
    }
}
