<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProfilePasswordRequest;
use App\Http\Requests\Admin\UpdateProfileRequest;
use App\Models\ActivityLog;
use App\Services\ProfileManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        $user = request()->user();
        $activity = ActivityLog::where('user_id', $user->id);

        return view('admin.profile.show', [
            'user' => $user,
            'lastLogin' => (clone $activity)->where('module', 'authentication')->where('action', 'login')->latest('created_at')->value('created_at'),
            'lastPasswordChange' => (clone $activity)->whereIn('action', ['password_changed'])->latest('created_at')->value('created_at'),
            'lastActivity' => (clone $activity)->latest('created_at')->value('created_at'),
            'registeredIp' => (clone $activity)->whereNotNull('ip_address')->oldest('created_at')->value('ip_address'),
        ]);
    }

    public function update(UpdateProfileRequest $request, ProfileManager $manager): RedirectResponse
    {
        $manager->update($request->user(), $request->validated());

        return back()->with('success', 'Your profile has been updated.');
    }

    public function password(UpdateProfilePasswordRequest $request, ProfileManager $manager): RedirectResponse
    {
        $user = $request->user();
        $manager->updatePassword($user, $request->validated('password'));

        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Password changed successfully. Please sign in again.');
    }
}
