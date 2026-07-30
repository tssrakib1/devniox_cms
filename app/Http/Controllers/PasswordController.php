<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    public function request()
    {
        return view('auth.forgot-password');
    }

    public function email(Request $r)
    {
        $r->validate(['email' => ['required', 'email']]);
        Password::sendResetLink($r->only('email'));

        return back()->with('status', 'If an account exists for that email, a reset link has been sent.');
    }

    public function reset(Request $r, string $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $r->email]);
    }

    public function update(Request $r)
    {
        $r->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(12)->mixedCase()->numbers()->symbols()],
        ]);
        $status = Password::reset($r->only('email', 'password', 'password_confirmation', 'token'), function (User $u, string $p) {
            $u->forceFill(['password' => Hash::make($p), 'remember_token' => Str::random(60)])->save();
            if (config('session.driver') === 'database') {
                DB::table(config('session.table', 'sessions'))->where('user_id', $u->id)->delete();
            }
            event(new PasswordReset($u));
            ActivityLogService::log('authentication', 'password_changed', 'Administrator password was changed.', $u, null, null, $u->id);
        });

        return $status === Password::PASSWORD_RESET ? redirect()->route('login')->with('status', __($status)) : back()->withErrors(['email' => __($status)]);
    }

    public function confirm()
    {
        return view('auth.confirm-password');
    }

    public function confirmed(Request $r)
    {
        $r->validate(['password' => 'required']);
        if (! Hash::check($r->password, $r->user()->password)) {
            throw ValidationException::withMessages(['password' => __('auth.password')]);
        }
        $r->session()->passwordConfirmed();

        return redirect()->intended(route('admin.dashboard'));
    }
}
