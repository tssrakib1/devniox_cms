<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreServiceCategoryRequest;
use App\Http\Requests\Admin\UpdateServiceCategoryRequest;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ServiceCategory::class);
        $f = $request->validate(['search' => ['nullable', 'string', 'max:180'], 'status' => ['nullable', 'in:draft,published'], 'trashed' => ['nullable', 'in:0,1']]);
        $categories = ServiceCategory::query()->when(($f['trashed'] ?? null) === '1', fn ($q) => $q->onlyTrashed())->when($f['search'] ?? null, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))->when($f['status'] ?? null, fn ($q, $s) => $q->where('status', $s))->withCount('services')->orderBy('sort_order')->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.service-categories.index', compact('categories'));
    }

    public function create(): View
    {
        $this->authorize('create', ServiceCategory::class);

        return view('admin.service-categories.form', ['category' => new ServiceCategory]);
    }

    public function store(StoreServiceCategoryRequest $r): RedirectResponse
    {
        ServiceCategory::create($r->validated());

        return redirect()->route('admin.service-categories.index')->with('success', 'Service category created.');
    }

    public function edit(ServiceCategory $category): View
    {
        $this->authorize('update', $category);

        return view('admin.service-categories.form', compact('category'));
    }

    public function update(UpdateServiceCategoryRequest $r, ServiceCategory $category): RedirectResponse
    {
        $category->update($r->validated());

        return redirect()->route('admin.service-categories.index')->with('success', 'Service category updated.');
    }

    public function destroy(ServiceCategory $category): RedirectResponse
    {
        $this->authorize('delete', $category);
        if ($category->services()->withTrashed()->exists()) {
            return back()->withErrors(['category' => 'A category assigned to services cannot be deleted.']);
        }$category->delete();

        return back()->with('success', 'Service category deleted.');
    }

    public function restore(int $category): RedirectResponse
    {
        $model = ServiceCategory::onlyTrashed()->findOrFail($category);
        $this->authorize('restore', $model);
        $model->restore();

        return back()->with('success', 'Service category restored.');
    }
}
