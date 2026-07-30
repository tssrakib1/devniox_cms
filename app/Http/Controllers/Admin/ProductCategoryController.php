<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductCategoryRequest;
use App\Http\Requests\Admin\UpdateProductCategoryRequest;
use App\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductCategoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', ProductCategory::class);

        return view('admin.product-categories.index', ['categories' => ProductCategory::withCount('products')->orderBy('sort_order')->orderBy('name')->paginate(20)]);
    }

    public function create(): View
    {
        $this->authorize('create', ProductCategory::class);

        return view('admin.product-categories.form', ['category' => new ProductCategory]);
    }

    public function store(StoreProductCategoryRequest $request): RedirectResponse
    {
        ProductCategory::create($request->validated());

        return redirect()->route('admin.product-categories.index')->with('success', 'Product category created.');
    }

    public function edit(ProductCategory $category): View
    {
        $this->authorize('update', $category);

        return view('admin.product-categories.form', compact('category'));
    }

    public function update(UpdateProductCategoryRequest $request, ProductCategory $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('admin.product-categories.index')->with('success', 'Product category updated.');
    }

    public function destroy(ProductCategory $category): RedirectResponse
    {
        $this->authorize('delete', $category);
        if ($category->products()->withTrashed()->exists()) {
            return back()->withErrors(['category' => 'A category assigned to products cannot be deleted.']);
        }
        $category->delete();

        return back()->with('success', 'Product category deleted.');
    }
}
