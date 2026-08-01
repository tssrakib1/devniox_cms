@extends('layouts.admin')
@section('title', 'My Profile')
@section('heading', 'My Profile')
@section('content')
<div class="row g-4">
    <div class="col-12 col-xl-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header"><h2 class="h5 mb-0">Profile Information</h2></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="row g-4 align-items-start">
                        <div class="col-12 col-md-auto">
                            <div class="profile-avatar" aria-label="Current profile avatar">
                                @if($user->avatar_path)<img src="{{ asset('storage/'.$user->avatar_path) }}" alt="{{ $user->name }} profile avatar" width="128" height="128">@else<span aria-hidden="true">{{ str($user->name)->explode(' ')->take(2)->map(fn ($part) => str($part)->substr(0, 1))->implode('') }}</span>@endif
                            </div>
                        </div>
                        <div class="col">
                            <div class="row g-3">
                                <div class="col-12 col-lg-6"><label class="form-label" for="profile-name">Full Name <span class="text-danger" aria-hidden="true">*</span></label><input class="form-control @error('name') is-invalid @enderror" id="profile-name" name="name" value="{{ old('name', $user->name) }}" required maxlength="255" autocomplete="name">@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                <div class="col-12 col-lg-6"><label class="form-label" for="profile-email">Email Address <span class="text-danger" aria-hidden="true">*</span></label><input class="form-control @error('email') is-invalid @enderror" type="email" id="profile-email" name="email" value="{{ old('email', $user->email) }}" required maxlength="255" autocomplete="email">@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                <div class="col-12 col-lg-6"><label class="form-label" for="profile-phone">Phone</label><input class="form-control @error('phone') is-invalid @enderror" type="tel" id="profile-phone" name="phone" value="{{ old('phone', $user->phone) }}" maxlength="40" autocomplete="tel">@error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                <div class="col-12"><label class="form-label" for="profile-avatar">Avatar</label><input class="form-control @error('avatar') is-invalid @enderror" type="file" id="profile-avatar" name="avatar" accept="image/jpeg,image/png,image/webp" aria-describedby="avatar-help">@error('avatar')<div class="invalid-feedback">{{ $message }}</div>@enderror<div class="form-text" id="avatar-help">JPG, PNG or WebP. Square images work best. Maximum 2 MB.</div></div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-4"><button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle me-1" aria-hidden="true"></i>Save Profile</button></div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header"><h2 class="h5 mb-0">Change Password</h2></div>
            <div class="card-body">
                <p class="text-body-secondary">Changing your password signs you out of all active sessions. You will need to sign in again.</p>
                <form method="POST" action="{{ route('admin.profile.password') }}">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-12"><label class="form-label" for="current-password">Current Password <span class="text-danger" aria-hidden="true">*</span></label><input class="form-control @error('current_password', 'passwordUpdate') is-invalid @enderror" type="password" id="current-password" name="current_password" required autocomplete="current-password">@error('current_password', 'passwordUpdate')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="col-12 col-lg-6"><label class="form-label" for="new-password">New Password <span class="text-danger" aria-hidden="true">*</span></label><input class="form-control @error('password', 'passwordUpdate') is-invalid @enderror" type="password" id="new-password" name="password" required autocomplete="new-password" aria-describedby="password-help">@error('password', 'passwordUpdate')<div class="invalid-feedback">{{ $message }}</div>@enderror<div class="form-text" id="password-help">At least 12 characters with uppercase, lowercase, number and symbol.</div></div>
                        <div class="col-12 col-lg-6"><label class="form-label" for="password-confirmation">Confirm New Password <span class="text-danger" aria-hidden="true">*</span></label><input class="form-control" type="password" id="password-confirmation" name="password_confirmation" required autocomplete="new-password"></div>
                    </div>
                    <div class="d-flex justify-content-end mt-4"><button class="btn btn-warning" type="submit"><i class="bi bi-shield-lock me-1" aria-hidden="true"></i>Change Password</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card shadow-sm mb-4"><div class="card-header"><h2 class="h5 mb-0">Avatar</h2></div><div class="card-body text-center"><div class="profile-avatar profile-avatar-lg mx-auto mb-3">@if($user->avatar_path)<img src="{{ asset('storage/'.$user->avatar_path) }}" alt="{{ $user->name }} profile avatar" width="160" height="160">@else<span aria-hidden="true">{{ str($user->name)->explode(' ')->take(2)->map(fn ($part) => str($part)->substr(0, 1))->implode('') }}</span>@endif</div><h3 class="h5 mb-1">{{ $user->name }}</h3><p class="text-body-secondary mb-2">{{ $user->email }}</p><span class="badge text-bg-primary">{{ $user->managedRole?->name ?? Str::headline($user->role->value) }}</span> <span class="badge {{ $user->is_active ? 'text-bg-success' : 'text-bg-danger' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></div></div>

        <div class="card shadow-sm"><div class="card-header"><h2 class="h5 mb-0">Account Information</h2></div><div class="card-body"><dl class="profile-meta mb-0">
            <div><dt>User ID</dt><dd>#{{ $user->id }}</dd></div>
            <div><dt>Role</dt><dd>{{ $user->managedRole?->name ?? Str::headline($user->role->value) }}</dd></div>
            <div><dt>Account Status</dt><dd>{{ $user->is_active ? 'Active' : 'Inactive' }}</dd></div>
            <div><dt>Member Since</dt><dd><time datetime="{{ $user->created_at->toIso8601String() }}">{{ $user->created_at->format('M j, Y') }}</time></dd></div>
            <div><dt>Last Login</dt><dd>{{ $lastLogin ? \Illuminate\Support\Carbon::parse($lastLogin)->diffForHumans() : 'Not recorded' }}</dd></div>
            <div><dt>Last Activity</dt><dd>{{ $lastActivity ? \Illuminate\Support\Carbon::parse($lastActivity)->diffForHumans() : 'Not recorded' }}</dd></div>
            <div><dt>Last Password Change</dt><dd>{{ $lastPasswordChange ? \Illuminate\Support\Carbon::parse($lastPasswordChange)->diffForHumans() : 'Not recorded' }}</dd></div>
            <div><dt>Registered IP</dt><dd><code>{{ $registeredIp ?? 'Not recorded' }}</code></dd></div>
        </dl></div></div>
    </div>
</div>
@endsection
