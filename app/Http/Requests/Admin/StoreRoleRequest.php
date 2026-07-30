<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('roles.create') ?? false;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:80', 'unique:roles,name'], 'copy_role_id' => ['nullable', 'integer', 'exists:roles,id'], 'permissions' => ['array'], 'permissions.*' => ['integer', 'exists:permissions,id']];
    }
}
