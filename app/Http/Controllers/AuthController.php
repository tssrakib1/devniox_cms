<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\ActivityLogService;
use App\Services\PermissionResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request, PermissionResolver $permissions)
    {
        $request->authenticate();
        $request->session()->regenerate();
        $permissions->forget($request->session());
        ActivityLogService::log('authentication', 'login', 'Administrator logged in.', $request->user());

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request, PermissionResolver $permissions)
    {
        $user = $request->user();
        ActivityLogService::log('authentication', 'logout', 'Administrator logged out.', $user);
        $permissions->forget($request->session());
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
