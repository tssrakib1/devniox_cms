@extends('layouts.admin')
@section('title', 'Website Settings')
@section('heading', 'Website Settings')
@section('content')
@php
    $orderedGroups = ['general','branding','contact','social','seo','analytics','email','integrations','maintenance'];
    $fieldOrder = [
        'general' => ['site_name','tagline','default_language','timezone'],
        'branding' => ['logo','dark_logo','favicon','admin_logo','login_logo','theme_color'],
        'contact' => ['company_name','address','phone','mobile','whatsapp','email','support_email','sales_email','google_maps_embed'],
        'seo' => ['meta_title','meta_description','meta_keywords','open_graph_image','canonical_base_url','robots_meta','organization'],
        'analytics' => ['ga4_measurement_id','gtm_id','clarity_id','facebook_pixel_id'],
        'email' => ['smtp_host','smtp_port','smtp_username','smtp_password','smtp_encryption','from_name','from_email'],
        'integrations' => ['webhook_url'],
        'maintenance' => ['enabled','message','estimated_return','allow_admin'],
    ];
    $labels = [
        'general.site_name' => 'Site Name', 'general.tagline' => 'Tagline', 'general.default_language' => 'Default Language', 'general.timezone' => 'Timezone',
        'branding.logo' => 'Website Logo', 'branding.dark_logo' => 'Dark Logo', 'branding.favicon' => 'Favicon', 'branding.admin_logo' => 'Admin Logo', 'branding.login_logo' => 'Login Logo', 'branding.theme_color' => 'Browser Theme Color',
        'contact.company_name' => 'Company Name', 'contact.whatsapp' => 'WhatsApp', 'contact.google_maps_embed' => 'Google Maps Embed',
        'seo.meta_title' => 'Default Meta Title', 'seo.meta_description' => 'Default Meta Description', 'seo.meta_keywords' => 'Default Keywords', 'seo.open_graph_image' => 'Open Graph Image', 'seo.canonical_base_url' => 'Canonical Base URL', 'seo.robots_meta' => 'Robots', 'seo.organization' => 'Organization Name',
        'analytics.ga4_measurement_id' => 'Google Analytics (GA4 Measurement ID)', 'analytics.gtm_id' => 'Google Tag Manager ID', 'analytics.clarity_id' => 'Microsoft Clarity ID', 'analytics.facebook_pixel_id' => 'Facebook Pixel ID',
        'email.smtp_host' => 'Host', 'email.smtp_port' => 'Port', 'email.smtp_username' => 'Username', 'email.smtp_password' => 'Password', 'email.smtp_encryption' => 'Encryption', 'email.from_name' => 'From Name', 'email.from_email' => 'From Email',
        'integrations.webhook_url' => 'Webhook URL',
        'maintenance.enabled' => 'Enable Maintenance Mode', 'maintenance.message' => 'Maintenance Message', 'maintenance.estimated_return' => 'Estimated Return Time', 'maintenance.allow_admin' => 'Allow Admin Access',
    ];
@endphp
<form method="post" enctype="multipart/form-data" action="{{ route('admin.settings.update') }}">
    @csrf @method('PUT')
    <div class="row">
        <div class="col-lg-3"><div class="list-group sticky-top mb-3">
            @foreach($orderedGroups as $name)
                <a class="list-group-item list-group-item-action {{ $section===$name?'active':'' }}" href="{{ route('admin.settings.section',$name) }}">{{ Str::headline($name) }}</a>
            @endforeach
            <a class="list-group-item list-group-item-action" href="{{ route('admin.settings.system') }}">System & Cache</a>
        </div></div>
        <div class="col-lg-9">
            @foreach($orderedGroups as $group)
                @php
                    $items = ($records[$group] ?? collect())->sortBy(fn ($setting) => array_search($setting->key, $fieldOrder[$group] ?? [], true));
                @endphp
                @continue($items->isEmpty())
                <x-card :title="Str::headline($group)" class="mb-4 setting-group" id="group-{{ $group }}">
                    <div class="row g-3">
                    @foreach($items as $setting)
                        @php
                            $key = $group.'.'.$setting->key;
                            $fieldName = 'settings['.$group.']['.$setting->key.']';
                            $imageFieldName = 'image_settings['.$group.']['.$setting->key.']';
                            $inputId = 'setting-'.$setting->id;
                            $label = $labels[$key] ?? Str::headline($setting->key);
                            $value = old('settings.'.$group.'.'.$setting->key, $setting->type === 'secret' ? '' : $setting->value);
                            $column = in_array($setting->type, ['text', 'image'], true) ? 'col-12' : 'col-md-6';
                        @endphp
                        <div class="{{ $column }}">
                            @if($setting->type === 'boolean')
                                <input type="hidden" name="{{ $fieldName }}" value="0">
                                <label class="form-check mt-4" for="{{ $inputId }}"><input class="form-check-input" id="{{ $inputId }}" type="checkbox" name="{{ $fieldName }}" value="1" @checked(old('settings.'.$group.'.'.$setting->key, (bool)$setting->value))> {{ $label }}</label>
                            @else
                                <label class="form-label" for="{{ $inputId }}">{{ $label }}</label>
                                @if($setting->type === 'image')
                                    @if($setting->value)<div><img src="{{ Storage::url($setting->value) }}" alt="Current {{ $label }}" loading="lazy" class="img-thumbnail mb-2 setting-image-preview"></div><input type="hidden" name="remove_image_settings[{{ $group }}][{{ $setting->key }}]" value="0"><label class="form-check mb-2"><input class="form-check-input" type="checkbox" name="remove_image_settings[{{ $group }}][{{ $setting->key }}]" value="1"> Remove current {{ Str::lower($label) }}</label>@endif
                                    <input class="form-control @error('image_settings.'.$key) is-invalid @enderror" id="{{ $inputId }}" name="{{ $imageFieldName }}" type="file" accept=".png,.jpg,.jpeg,.webp,.ico">
                                    @error('image_settings.'.$key)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                @elseif($setting->type === 'text')
                                    <textarea class="form-control @error('settings.'.$key) is-invalid @enderror" id="{{ $inputId }}" name="{{ $fieldName }}" rows="{{ str_contains($setting->key, 'embed') ? 5 : 3 }}">{{ $value }}</textarea>
                                @elseif($setting->key === 'theme_color')
                                    <input class="form-control form-control-color @error('settings.'.$key) is-invalid @enderror" id="{{ $inputId }}" name="{{ $fieldName }}" type="color" value="{{ $value ?: '#0d6efd' }}">
                                @elseif(str_contains($setting->key, 'estimated_return'))
                                    <input class="form-control @error('settings.'.$key) is-invalid @enderror" id="{{ $inputId }}" name="{{ $fieldName }}" type="datetime-local" value="{{ $value ? \Illuminate\Support\Carbon::parse($value)->format('Y-m-d\TH:i') : '' }}">
                                @elseif(str_contains($setting->key, 'encryption'))
                                    <select class="form-select @error('settings.'.$key) is-invalid @enderror" id="{{ $inputId }}" name="{{ $fieldName }}"><option value="">None</option><option value="tls" @selected($value === 'tls')>TLS</option><option value="ssl" @selected($value === 'ssl')>SSL</option></select>
                                @elseif($setting->key === 'robots_meta')
                                    <select class="form-select @error('settings.'.$key) is-invalid @enderror" id="{{ $inputId }}" name="{{ $fieldName }}">@foreach(['index,follow','index,nofollow','noindex,follow','noindex,nofollow'] as $option)<option value="{{ $option }}" @selected($value === $option)>{{ $option }}</option>@endforeach</select>
                                @else
                                    <input class="form-control @error('settings.'.$key) is-invalid @enderror" id="{{ $inputId }}" name="{{ $fieldName }}" type="{{ in_array($setting->type, ['email','url']) ? $setting->type : ($setting->type === 'integer' ? 'number' : ($setting->type === 'secret' ? 'password' : 'text')) }}" value="{{ $value }}" @if($setting->type === 'secret') autocomplete="new-password" placeholder="Leave blank to keep current value" @endif>
                                @endif
                                @error('settings.'.$key)<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @endif
                        </div>
                    @endforeach
                    </div>
                </x-card>
            @endforeach
            @if(!$section||$section==='social')<x-card title="Social Media" class="mb-4">@foreach($socialLinks as $link)<div class="row g-2 mb-3"><div class="col-md-3"><label class="form-label">{{ $link->platform === 'x' ? 'X (Twitter)' : Str::headline($link->platform) }}</label></div><div class="col-md-5"><input class="form-control" type="url" name="social_links[{{ $link->platform }}][url]" value="{{ $link->url }}" placeholder="https://"></div><div class="col-md-2"><input class="form-control" type="number" min="0" name="social_links[{{ $link->platform }}][display_order]" value="{{ $link->display_order }}" aria-label="Display order"></div><div class="col-md-2"><input type="hidden" name="social_links[{{ $link->platform }}][is_visible]" value="0"><label class="form-check"><input class="form-check-input" type="checkbox" name="social_links[{{ $link->platform }}][is_visible]" value="1" @checked($link->is_visible)> Visible</label></div></div>@endforeach</x-card>@endif
            <div class="text-end mb-5"><x-button type="submit"><i class="bi bi-check-lg"></i> Save settings</x-button></div>
        </div>
    </div>
</form>
@if(!$section || $section === 'email')
<x-card title="Test Email" class="mb-4"><form class="row g-3" method="post" action="{{ route('admin.settings.test-email') }}">@csrf<div class="col-md-8"><label class="form-label" for="test-email">Recipient Email</label><input class="form-control" id="test-email" type="email" name="test_email" required placeholder="you@example.com"></div><div class="col-md-4 align-self-end"><button class="btn btn-outline-primary w-100">Send Test Email</button></div></form></x-card>
@endif
@endsection


