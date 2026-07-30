@extends('layouts.admin')
@section('title', 'Website Settings')
@section('heading', 'Website Settings')
@section('content')
<form method="post" enctype="multipart/form-data" action="{{ route('admin.settings.update') }}">
    @csrf @method('PUT')
    <div class="row">
        <div class="col-lg-3"><div class="list-group sticky-top mb-3">
            @foreach(['general','branding','contact','social','seo','analytics','email','integrations','maintenance'] as $name)<a class="list-group-item list-group-item-action {{ $section===$name?'active':'' }}" href="{{ route('admin.settings.section',$name) }}">{{ Str::headline($name) }}</a>@endforeach<a class="list-group-item list-group-item-action" href="{{ route('admin.settings.system') }}">System & Cache</a>
            @foreach($records as $group => $items)<a class="list-group-item list-group-item-action text-capitalize" href="#group-{{ $group }}">{{ $group }}</a>@endforeach
        </div></div>
        <div class="col-lg-9">
            @foreach($records as $group => $items)
                <x-card :title="Str::headline($group)" class="mb-4 setting-group" id="group-{{ $group }}">
                    @foreach($items as $setting)
                        <div class="mb-3">
                            <label class="form-label" for="setting-{{ $setting->id }}">{{ Str::headline($setting->key) }}</label>
                            @if($setting->type === 'image')
                                @if($setting->value)<div><img src="{{ Storage::url($setting->value) }}" alt="" loading="lazy" class="img-thumbnail mb-2 setting-image-preview"></div>@endif
                                <input class="form-control" id="setting-{{ $setting->id }}" name="{{ $setting->key }}" type="file" accept=".png,.jpg,.jpeg,.webp{{ $setting->key === 'favicon' ? ',.ico' : '' }}">
                            @elseif($setting->type === 'text')
                                <textarea class="form-control" id="setting-{{ $setting->id }}" name="settings[{{ $group }}.{{ $setting->key }}]" rows="3">{{ old('settings.'.$group.'.'.$setting->key, $setting->value) }}</textarea>
                            @else
                                <input class="form-control" id="setting-{{ $setting->id }}" name="settings[{{ $group }}.{{ $setting->key }}]" type="{{ in_array($setting->type, ['email','url']) ? $setting->type : ($setting->type === 'secret' ? 'password' : 'text') }}" value="{{ old('settings.'.$group.'.'.$setting->key, $setting->type === 'secret' ? '' : $setting->value) }}">
                            @endif
                        </div>
                    @endforeach
                </x-card>
            @endforeach
            @if(!$section||$section==='social')<x-card title="Social Media" class="mb-4">@foreach($socialLinks as $link)<div class="row g-2 mb-3"><div class="col-md-3"><label class="form-label">{{ ucfirst($link->platform) }}</label></div><div class="col-md-5"><input class="form-control" type="url" name="social_links[{{ $link->platform }}][url]" value="{{ $link->url }}" placeholder="https://"></div><div class="col-md-2"><input class="form-control" type="number" min="0" name="social_links[{{ $link->platform }}][display_order]" value="{{ $link->display_order }}" aria-label="Display order"></div><div class="col-md-2"><input type="hidden" name="social_links[{{ $link->platform }}][is_visible]" value="0"><label class="form-check"><input class="form-check-input" type="checkbox" name="social_links[{{ $link->platform }}][is_visible]" value="1" @checked($link->is_visible)> Visible</label></div></div>@endforeach</x-card>@endif
            <div class="text-end mb-5"><x-button type="submit"><i class="bi bi-check-lg"></i> Save settings</x-button></div>
        </div>
    </div>
</form>
@endsection
