@extends('layouts.admin')
@section('title', 'Product Categories')
@section('heading', 'Product Categories')
@section('content')
<div class="d-flex justify-content-end mb-3"><a class="btn btn-primary" href="{{ route('admin.product-categories.create') }}"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add category</a></div>
<x-card>
<div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Name</th><th>Slug</th><th>Products</th><th>Order</th><th>Status</th><th class="text-end">Actions</th></tr></thead><tbody>
@forelse($categories as $category)<tr><td><i class="bi bi-{{ $category->icon ?: 'folder' }} me-2" aria-hidden="true"></i><strong>{{ $category->name }}</strong></td><td><code>{{ $category->slug }}</code></td><td>{{ $category->products_count }}</td><td>{{ $category->sort_order }}</td><td><span class="badge text-bg-{{ $category->is_active ? 'success' : 'secondary' }}">{{ $category->is_active ? 'Active' : 'Inactive' }}</span></td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.product-categories.edit', $category) }}">Edit</a><form class="d-inline" method="post" action="{{ route('admin.product-categories.destroy', $category) }}" data-confirm="Delete this category?">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" type="submit">Delete</button></form></td></tr>
@empty<tr><td colspan="6"><x-empty-state title="No product categories">Create a category before adding products.</x-empty-state></td></tr>@endforelse
</tbody></table></div>{{ $categories->links() }}
</x-card>
@endsection
