<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\UserManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private readonly UserManagementService $manager) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->hasPermission('users.view'), 403);
        $users = User::with('managedRole')->when($request->filled('search'), fn ($q) => $q->where(fn ($x) => $x->where('name', 'like', '%'.$request->string('search').'%')->orWhere('email', 'like', '%'.$request->string('search').'%')))->latest()->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->hasPermission('users.create'), 403);

        return view('admin.users.create', ['roles' => Role::orderBy('name')->get()]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->manager->create($request->validated());

        return to_route('admin.users.index')->with('success', 'User created.');
    }

    public function edit(Request $request, User $user): View
    {
        abort_unless($request->user()->hasPermission('users.edit'), 403);

        return view('admin.users.edit', ['user' => $user, 'roles' => Role::orderBy('name')->get()]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->manager->update($user, $request->validated());

        return to_route('admin.users.index')->with('success', 'User updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('users.delete'), 403);
        $this->manager->delete($user);

        return to_route('admin.users.index')->with('success', 'User deleted.');
    }
}
