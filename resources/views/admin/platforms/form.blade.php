@extends('layouts.admin')
@section('title',$platform->exists ? 'Edit Platform' : 'New Platform')
@section('heading',$platform->exists ? 'Edit Platform' : 'New Platform')
@section('content')
<form method="post" enctype="multipart/form-data" action="{{ $platform->exists ? route('admin.platforms.update',$platform) : route('admin.platforms.store') }}">@csrf @if($platform->exists)@method('PUT')@endif
<div class="row g-4"><div class="col-xl-8">
<x-card title="Platform information">
<div class="row g-3"><div class="col-md-7"><x-form.input name="name" label="Platform name" :value="$platform->name" required maxlength="160" data-slug-source /></div><div class="col-md-5"><x-form.input name="slug" label="Slug" :value="$platform->slug" required maxlength="180" data-slug-target /></div></div>
<div class="mb-3"><label class="form-label" for="description">Short description</label><textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" maxlength="500" rows="4" required>{{ old('description',$platform->description) }}</textarea>@error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<x-form.input name="website_url" label="Website URL" type="url" :value="$platform->website_url" required maxlength="255" />
</x-card>
<x-card title="Branding" class="mt-4">
<div class="row g-3"><div class="col-md-6"><label class="form-label" for="logo">Logo</label>@if($platform->logo)<img class="image-preview mb-2" src="{{ Storage::url($platform->logo) }}" alt="Current {{ $platform->name }} logo">@endif<input class="form-control @error('logo') is-invalid @enderror" id="logo" name="logo" type="file" accept=".png,.jpg,.jpeg,.webp,.svg" data-image-input>@error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-md-3"><x-form.input name="badge" label="Badge" :value="$platform->badge" maxlength="80" placeholder="New" /></div><div class="col-md-3"><x-form.input name="brand_color" label="Brand color" type="color" :value="$platform->brand_color ?: '#635bff'" /></div></div>
</x-card>
</div><div class="col-xl-4"><div class="sticky-xl-top product-publish-panel"><x-card title="Publishing"><x-form.input name="display_order" label="Display order" type="number" :value="$platform->display_order ?? 0" min="0" required /><div class="mb-3"><label class="form-label" for="status">Status</label><select class="form-select" id="status" name="status" required>@foreach(['active','inactive'] as $status)<option value="{{ $status }}" @selected(old('status',$platform->status ?? 'active')===$status)>{{ Str::headline($status) }}</option>@endforeach</select></div><input type="hidden" name="open_in_new_tab" value="0"><label class="form-check mb-3"><input class="form-check-input" type="checkbox" name="open_in_new_tab" value="1" @checked(old('open_in_new_tab',$platform->open_in_new_tab ?? true))> Open in new tab</label><button class="btn btn-primary w-100">Save platform</button><a class="btn btn-outline-secondary w-100 mt-2" href="{{ route('admin.platforms.index') }}">Cancel</a></x-card></div></div></div>
</form>
@endsection
