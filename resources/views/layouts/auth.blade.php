<!doctype html>
<html lang="{{ $siteSettings['general.default_language'] ?? app()->getLocale() }}" data-bs-theme="light"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>@yield('title') - {{ $siteSettings['general.site_name'] ?? config('app.name') }}</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body><main class="auth-shell"><div class="auth-card">
<a class="navbar-brand d-block text-center mb-4" href="{{ route('home') }}">@if(filled($siteSettings['branding.login_logo'] ?? null))<img class="brand-logo" src="{{ Storage::url($siteSettings['branding.login_logo']) }}" alt="{{ $siteSettings['general.site_name'] ?? config('app.name') }}">@else<span class="brand-mark">{{ Str::upper(Str::substr($siteSettings['general.site_name'] ?? config('app.name'),0,1)) }}</span> {{ $siteSettings['general.site_name'] ?? config('app.name') }}@endif</a>
@if(session('status')) <x-alert type="success">{{ session('status') }}</x-alert> @endif
@if($errors->any()) <x-alert type="danger">Please correct the highlighted fields.</x-alert> @endif
<div class="card shadow-sm"><div class="card-body p-4">@yield('content')</div></div>
</div></main></body></html>
