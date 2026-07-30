@extends('layouts.admin')
@section('title', 'Blog Tags')
@section('heading', 'Blog Tags')
@section('content')
<form class="row g-2 mb-4" method="post" action="{{ route('admin.blog-tags.store') }}">
    @csrf
    <div class="col-md"><label class="visually-hidden" for="tag-name">Tag name</label><input class="form-control" id="tag-name" name="name" placeholder="Tag name" required></div>
    <div class="col-md"><label class="visually-hidden" for="tag-slug">Tag slug</label><input class="form-control" id="tag-slug" name="slug" placeholder="tag-slug" required></div>
    <div class="col-md"><label class="visually-hidden" for="tag-description">Description</label><input class="form-control" id="tag-description" name="description" placeholder="Description"></div>
    <div class="col-md-auto"><button class="btn btn-primary w-100"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add tag</button></div>
</form>
<div class="card table-responsive">
    <table class="table mb-0">
        <thead><tr><th scope="col">Name</th><th scope="col">Slug</th><th scope="col">Posts</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
        <tbody>
        @forelse($tags as $tag)
            <tr><td>{{ $tag->name }}</td><td>{{ $tag->slug }}</td><td>{{ $tag->posts_count }}</td><td class="text-end"><form method="post" action="{{ route('admin.blog-tags.destroy', $tag) }}" data-confirm="Delete this tag?">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr>
        @empty
            <tr><td colspan="4"><x-empty-state title="No blog tags yet">Add a tag above to improve article discovery.</x-empty-state></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $tags->links() }}</div>
@endsection
