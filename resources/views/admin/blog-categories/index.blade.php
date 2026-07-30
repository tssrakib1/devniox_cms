@extends('layouts.admin')
@section('title', 'Blog Categories')
@section('heading', 'Blog Categories')
@section('content')
<div class="admin-toolbar justify-content-between mb-3">
    <form class="flex-grow-1" role="search">
        <label class="visually-hidden" for="category-search">Search categories</label>
        <input class="form-control" id="category-search" name="search" value="{{ request('search') }}" placeholder="Search categories">
    </form>
    <a class="btn btn-primary" href="{{ route('admin.blog-categories.create') }}"><i class="bi bi-plus-lg" aria-hidden="true"></i> New category</a>
</div>
<div class="card table-responsive">
    <table class="table mb-0">
        <thead><tr><th scope="col">Name</th><th scope="col">Parent</th><th scope="col">Status</th><th scope="col">Posts</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
        <tbody>
        @forelse($categories as $category)
            <tr><td>{{ $category->name }}</td><td>{{ $category->parent?->name ?: '—' }}</td><td>{{ ucfirst($category->status->value) }}</td><td>{{ $category->posts_count }}</td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.blog-categories.edit', $category) }}">Edit</a></td></tr>
        @empty
            <tr><td colspan="5"><x-empty-state title="No blog categories found">Create a category to organize published knowledge content.</x-empty-state></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $categories->links() }}</div>
@endsection
