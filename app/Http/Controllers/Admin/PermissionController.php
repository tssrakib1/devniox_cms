<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function __invoke(Request $request): View
    {
        abort_unless($request->user()->hasPermission('roles.view'), 403);

        return view('admin.permissions.index', ['permissions' => Permission::with('roles:id,name')->orderBy('module')->orderBy('action')->get()->groupBy('module'), 'roles' => Role::orderBy('name')->get()]);
    }
}
