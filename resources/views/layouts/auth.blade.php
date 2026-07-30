<!doctype html>
<html lang="en" data-bs-theme="light"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>@yield('title') — DevNiox</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body><main class="auth-shell"><div class="auth-card">
<a class="navbar-brand d-block text-center mb-4" href="{{ route('home') }}"><span class="brand-mark">D</span> DevNiox</a>
@if(session('status')) <x-alert type="success">{{ session('status') }}</x-alert> @endif
@if($errors->any()) <x-alert type="danger">Please correct the highlighted fields.</x-alert> @endif
<div class="card shadow-sm"><div class="card-body p-4">@yield('content')</div></div>
</div></main></body></html>
