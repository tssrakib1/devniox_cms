@extends('layouts.admin')
@section('title', $service->exists ? 'Edit Service' : 'New Service')
@section('heading', $service->exists ? 'Edit Service' : 'New Service')
@section('content')
<form method="post" enctype="multipart/form-data" action="{{ $service->exists ? route('admin.services.update', $service) : route('admin.services.store') }}">
    @csrf
    @if($service->exists)@method('PUT')@endif

    <div class="row g-4">
        <div class="col-xl-8">
            <x-card title="Basic information">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label" for="name">Service name</label><input class="form-control @error('name') is-invalid @enderror" id="name" name="name" required maxlength="180" value="{{ old('name', $service->name) }}" data-slug-source>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label class="form-label" for="slug">Slug</label><input class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" required maxlength="200" value="{{ old('slug', $service->slug) }}" data-slug-target>@error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-12"><label class="form-label" for="short_description">Short description</label><textarea class="form-control @error('short_description') is-invalid @enderror" id="short_description" name="short_description" required maxlength="300" rows="3">{{ old('short_description', $service->short_description) }}</textarea>@error('short_description')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-12"><label class="form-label" for="full_description">Full description</label><textarea class="form-control @error('full_description') is-invalid @enderror" id="full_description" name="full_description" rows="10" required>{{ old('full_description', $service->full_description) }}</textarea>@error('full_description')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                </div>
            </x-card>

            <x-card title="Media" class="mt-4">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label" for="featured_image">Featured image</label>@if($service->featured_image_path)<img class="image-preview mb-2" src="{{ Storage::url($service->featured_image_path) }}" alt="Current featured image">@endif<input class="form-control @error('featured_image') is-invalid @enderror" id="featured_image" type="file" name="featured_image" accept="image/png,image/jpeg,image/webp" data-image-preview-input><div data-image-preview></div>@error('featured_image')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label class="form-label" for="cover_image">Banner image</label>@if($service->cover_image_path)<img class="image-preview mb-2" src="{{ Storage::url($service->cover_image_path) }}" alt="Current banner image">@endif<input class="form-control @error('cover_image') is-invalid @enderror" id="cover_image" type="file" name="cover_image" accept="image/png,image/jpeg,image/webp" data-image-preview-input><div data-image-preview></div>@error('cover_image')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                </div>
            </x-card>

            <x-card title="SEO" class="mt-4">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label" for="meta_title">Meta title</label><input class="form-control @error('seo.meta_title') is-invalid @enderror" id="meta_title" name="seo[meta_title]" maxlength="70" value="{{ old('seo.meta_title', $service->seo?->meta_title) }}">@error('seo.meta_title')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label class="form-label" for="canonical_url">Canonical URL</label><input class="form-control @error('seo.canonical_url') is-invalid @enderror" id="canonical_url" type="url" name="seo[canonical_url]" value="{{ old('seo.canonical_url', $service->seo?->canonical_url) }}">@error('seo.canonical_url')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-12"><label class="form-label" for="meta_description">Meta description</label><textarea class="form-control @error('seo.meta_description') is-invalid @enderror" id="meta_description" name="seo[meta_description]" maxlength="160" rows="3">{{ old('seo.meta_description', $service->seo?->meta_description) }}</textarea>@error('seo.meta_description')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label class="form-label" for="meta_keywords">Meta keywords</label><input class="form-control @error('seo.meta_keywords') is-invalid @enderror" id="meta_keywords" name="seo[meta_keywords]" maxlength="500" value="{{ old('seo.meta_keywords', $service->seo?->meta_keywords) }}">@error('seo.meta_keywords')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label class="form-label" for="open_graph_image">Meta image</label>@if($service->seo?->open_graph_image_path)<img class="image-preview mb-2" src="{{ Storage::url($service->seo->open_graph_image_path) }}" alt="Current meta image">@endif<input class="form-control @error('seo.open_graph_image') is-invalid @enderror" id="open_graph_image" type="file" name="seo[open_graph_image]" accept="image/png,image/jpeg,image/webp">@error('seo.open_graph_image')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-12"><input type="hidden" name="seo[is_indexable]" value="0"><label class="form-check"><input class="form-check-input" type="checkbox" name="seo[is_indexable]" value="1" @checked(old('seo.is_indexable', $service->seo?->is_indexable ?? true))> Allow search indexing</label></div>
                </div>
            </x-card>
        </div>

        <div class="col-xl-4"><div class="sticky-xl-top product-publish-panel"><x-card title="Display"><div class="mb-3"><label class="form-label" for="category">Category</label><select class="form-select @error('service_category_id') is-invalid @enderror" id="category" name="service_category_id" required><option value="">Choose category</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string)old('service_category_id', $service->service_category_id) === (string)$category->id)>{{ $category->name }}</option>@endforeach</select>@error('service_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="mb-3"><label class="form-label" for="status">Status</label><select class="form-select" id="status" name="status" required>@foreach($statuses as $status)<option value="{{ $status->value }}" @selected(old('status', $service->status?->value ?? 'draft') === $status->value)>{{ Str::headline($status->value) }}</option>@endforeach</select></div><x-form.input name="display_order" label="Display order" type="number" :value="$service->display_order ?? 0" min="0" required /><input type="hidden" name="is_featured" value="0"><label class="form-check mb-3"><input class="form-check-input" id="featured" type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $service->is_featured))> Featured service</label><button class="btn btn-primary w-100">Save service</button><a class="btn btn-outline-secondary w-100 mt-2" href="{{ route('admin.services.index') }}">Cancel</a></x-card></div></div>
    </div>
</form>
@endsection
