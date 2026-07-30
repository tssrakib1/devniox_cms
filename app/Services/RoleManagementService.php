<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RoleManagementService
{
    public function create(array $data): Role
    {
        return DB::transaction(function () use ($data) {
            $role = Role::create(['name' => $data['name'], 'slug' => Str::slug($data['name']), 'is_system' => false]);
            $permissionIds = $data['permissions'] ?? [];
            if (! empty($data['copy_role_id'])) {
                $source = Role::with('permissions:id')->findOrFail($data['copy_role_id']);
                $permissionIds = $source->slug === 'administrator' ? Permission::pluck('id')->all() : $source->permissions->pluck('id')->all();
            }
            $role->permissions()->sync($permissionIds);
            ActivityLogService::log('roles', 'created', "Role {$role->name} created.", $role, null, ['permissions' => $role->permissions()->pluck('module')->all()]);

            return $role;
        });
    }

    public function update(Role $role, array $data): Role
    {
        return DB::transaction(function () use ($role, $data) {
            $before = ['name' => $role->name, 'permissions' => $role->permissions()->pluck('id')->all()];
            $role->update(['name' => $data['name'], 'slug' => $role->is_system ? $role->slug : Str::slug($data['name'])]);
            if ($role->slug !== 'administrator') {
                $role->permissions()->sync($data['permissions'] ?? []);
            }
            $role->touch();
            app(PermissionResolver::class)->bumpRole($role->id);
            ActivityLogService::log('roles', 'updated', "Role {$role->name} updated.", $role, $before, ['name' => $role->name, 'permissions' => $role->permissions()->pluck('id')->all()]);

            return $role;
        });
    }

    public function delete(Role $role): void
    {
        if ($role->is_system) {
            throw ValidationException::withMessages(['role' => 'System roles cannot be deleted.']);
        }
        if ($role->users()->exists()) {
            throw ValidationException::withMessages(['role' => 'A role assigned to users cannot be deleted.']);
        }
        ActivityLogService::log('roles', 'deleted', "Role {$role->name} deleted.", $role, $role->only(['name', 'slug']));
        $role->delete();
    }
}
