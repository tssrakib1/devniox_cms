<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Services\RoleManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function __construct(private readonly RoleManagementService $manager) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->hasPermission('roles.view'), 403);

        return view('admin.roles.index', ['roles' => Role::withCount('users')->orderBy('name')->get()]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->hasPermission('roles.create'), 403);

        return view('admin.roles.create', $this->formData());
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->manager->create($request->validated());

        return to_route('admin.roles.index')->with('success', 'Role created.');
    }

    public function edit(Request $request, Role $role): View
    {
        abort_unless($request->user()->hasPermission('roles.edit'), 403);
        $role->load('permissions');

        return view('admin.roles.edit', $this->formData() + compact('role'));
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $this->manager->update($role, $request->validated());

        return to_route('admin.roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('roles.delete'), 403);
        $this->manager->delete($role);

        return to_route('admin.roles.index')->with('success', 'Role deleted.');
    }

    private function formData(): array
    {
        return ['permissionMatrix' => Permission::orderBy('module')->orderByRaw("CASE action WHEN 'view' THEN 1 WHEN 'create' THEN 2 WHEN 'edit' THEN 3 ELSE 4 END")->get()->groupBy('module'), 'copyableRoles' => Role::with('permissions:id')->orderBy('name')->get()];
    }
}
