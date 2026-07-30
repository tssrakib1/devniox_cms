<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title') · DevNiox Installer</title>
    @vite(['resources/css/app.css'])
    <style>
        body{background:#f4f6fb;min-height:100vh}.installer-shell{max-width:1040px;margin:auto;padding:32px 20px 56px}.installer-brand{align-items:center;color:#172033;display:flex;font-size:1.1rem;font-weight:800;gap:.7rem;text-decoration:none}.installer-mark{align-items:center;background:linear-gradient(135deg,#635bff,#3b82f6);border-radius:.7rem;color:#fff;display:inline-flex;height:2.35rem;justify-content:center;width:2.35rem}.installer-progress{display:grid;gap:.4rem;grid-template-columns:repeat(8,1fr);margin:28px 0}.installer-progress span{background:#dfe3ed;border-radius:1rem;height:.35rem}.installer-progress span.done,.installer-progress span.active{background:#635bff}.installer-card{background:#fff;border:1px solid #e2e6ef;border-radius:1.15rem;box-shadow:0 18px 55px rgb(20 32 60 / 9%);overflow:hidden}.installer-card-header{border-bottom:1px solid #e8ebf2;padding:28px 32px}.installer-card-body{padding:32px}.installer-card-footer{align-items:center;background:#fafbfe;border-top:1px solid #e8ebf2;display:flex;gap:12px;justify-content:space-between;padding:20px 32px}.step-label{color:#635bff;font-size:.78rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase}.requirement{align-items:center;border-bottom:1px solid #edf0f5;display:flex;justify-content:space-between;padding:13px 0}.requirement:last-child{border:0}.status-icon{align-items:center;border-radius:50%;display:inline-flex;height:1.8rem;justify-content:center;width:1.8rem}.status-icon.pass{background:#e8f8ef;color:#16864b}.status-icon.fail{background:#fff0f0;color:#c93030}.form-label{font-weight:650}.form-text{color:#667085}.install-log{background:#101522;border-radius:.85rem;color:#d5dbea;min-height:260px;padding:20px}.install-log-item{align-items:center;border-bottom:1px solid rgb(255 255 255 / 8%);display:flex;gap:12px;padding:10px 0}.install-log-item:last-child{border:0}.summary-row{border-bottom:1px solid #edf0f5;display:flex;justify-content:space-between;gap:20px;padding:12px 0}.summary-row:last-child{border:0}@media(max-width:767px){.installer-shell{padding:20px 12px 36px}.installer-card-header,.installer-card-body{padding:22px}.installer-card-footer{align-items:stretch;flex-direction:column-reverse;padding:18px 22px}.installer-card-footer .btn{width:100%}.installer-progress{margin:20px 0}.summary-row{display:block}.summary-row strong{display:block;margin-top:4px}}
    </style>
</head>
<body>
<main class="installer-shell">
    <header class="d-flex align-items-center justify-content-between">
        <span class="installer-brand"><span class="installer-mark">D</span> DevNiox</span>
        <span class="text-secondary small">v{{ config('app.version') }}</span>
    </header>
    <div class="installer-progress" aria-label="Installation progress">@for($i=1;$i<=8;$i++)<span class="{{ $i < ($step ?? 1) ? 'done' : ($i === ($step ?? 1) ? 'active' : '') }}"></span>@endfor</div>
    @if($errors->any())<div class="alert alert-danger" role="alert"><strong>Unable to continue.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if(session('status'))<div class="alert alert-success" role="status">{{ session('status') }}</div>@endif
    @yield('content')
</main>
@stack('scripts')
</body>
</html>
