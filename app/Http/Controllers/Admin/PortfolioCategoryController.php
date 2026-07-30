<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePortfolioCategoryRequest;
use App\Http\Requests\Admin\UpdatePortfolioCategoryRequest;
use App\Models\PortfolioCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortfolioCategoryController extends Controller
{
    public function index(Request $r): View
    {
        $this->authorize('viewAny', PortfolioCategory::class);
        $f = $r->validate(['search' => ['nullable', 'string', 'max:180'], 'status' => ['nullable', 'in:draft,published'], 'trashed' => ['nullable', 'in:0,1'], 'sort' => ['nullable', 'in:name,sort_order,status,updated_at'], 'direction' => ['nullable', 'in:asc,desc']]);
        $categories = PortfolioCategory::query()->when(($f['trashed'] ?? null) === '1', fn ($q) => $q->onlyTrashed())->when($f['search'] ?? null, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))->when($f['status'] ?? null, fn ($q, $s) => $q->where('status', $s))->withCount('projects')->orderBy($f['sort'] ?? 'sort_order', $f['direction'] ?? 'asc')->paginate(20)->withQueryString();

        return view('admin.portfolio-categories.index', compact('categories'));
    }

    public function create(): View
    {
        $this->authorize('create', PortfolioCategory::class);

        return view('admin.portfolio-categories.form', ['category' => new PortfolioCategory]);
    }

    public function store(StorePortfolioCategoryRequest $r): RedirectResponse
    {
        PortfolioCategory::create($r->validated());

        return redirect()->route('admin.portfolio-categories.index')->with('success', 'Portfolio category created.');
    }

    public function edit(PortfolioCategory $portfolio_category): View
    {
        $this->authorize('update', $portfolio_category);

        return view('admin.portfolio-categories.form', ['category' => $portfolio_category]);
    }

    public function update(UpdatePortfolioCategoryRequest $r, PortfolioCategory $portfolio_category): RedirectResponse
    {
        $portfolio_category->update($r->validated());

        return redirect()->route('admin.portfolio-categories.index')->with('success', 'Portfolio category updated.');
    }

    public function destroy(PortfolioCategory $portfolio_category): RedirectResponse
    {
        $this->authorize('delete', $portfolio_category);
        if ($portfolio_category->projects()->withTrashed()->exists()) {
            return back()->withErrors(['category' => 'A category assigned to portfolio projects cannot be deleted.']);
        }$portfolio_category->delete();

        return back()->with('success', 'Portfolio category deleted.');
    }

    public function restore(int $category): RedirectResponse
    {
        $c = PortfolioCategory::onlyTrashed()->findOrFail($category);
        $this->authorize('restore', $c);
        $c->restore();

        return back()->with('success', 'Portfolio category restored.');
    }
}
