@extends('layouts.admin')
@section('title', $category->exists ? 'Edit Category' : 'New Category')
@section('heading', $category->exists ? 'Edit Product Category' : 'New Product Category')
@section('content')
<form method="post" action="{{ $category->exists ? route('admin.product-categories.update', $category) : route('admin.product-categories.store') }}">@csrf @if($category->exists) @method('PUT') @endif
<div class="row justify-content-center"><div class="col-xl-8"><x-card title="Category details">
<div class="row"><div class="col-md-6"><x-form.input name="name" label="Name" :value="$category->name" required data-slug-source /></div><div class="col-md-6"><x-form.input name="slug" label="Slug" :value="$category->slug" required data-slug-target /></div></div>
<div class="mb-3"><label class="form-label" for="description">Description</label><textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $category->description) }}</textarea></div>
<div class="row"><div class="col-md-4"><x-form.input name="icon" label="Bootstrap icon name" :value="$category->icon" placeholder="box" /></div><div class="col-md-4"><x-form.input name="sort_order" label="Sort order" type="number" :value="$category->sort_order ?? 0" min="0" required /></div><div class="col-md-4"><label class="form-label d-block">Status</label><input type="hidden" name="is_active" value="0"><div class="form-check form-switch"><input class="form-check-input" id="is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->exists ? $category->is_active : true))><label class="form-check-label" for="is_active">Active</label></div></div></div>
</x-card><x-card title="SEO" class="mt-4"><x-form.input name="seo_title" label="SEO title" :value="$category->seo_title" maxlength="70" /><div class="mb-3"><label class="form-label" for="seo_description">SEO description</label><textarea class="form-control" id="seo_description" name="seo_description" maxlength="160" rows="3">{{ old('seo_description', $category->seo_description) }}</textarea></div></x-card>
<div class="d-flex justify-content-end gap-2 mt-4"><a class="btn btn-outline-secondary" href="{{ route('admin.product-categories.index') }}">Cancel</a><button class="btn btn-primary" type="submit">Save category</button></div></div></div>
</form>
@endsection
