<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IncomeCategoryRequest;
use App\Models\IncomeCategory;
use App\Services\FinanceCategoryManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class IncomeCategoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', IncomeCategory::class);

        return view('admin.finance.categories.index', ['type' => 'income', 'categories' => IncomeCategory::withCount('transactions')->orderBy('name')->paginate(25)]);
    }

    public function create(): View
    {
        $this->authorize('create', IncomeCategory::class);

        return view('admin.finance.categories.form', ['type' => 'income', 'category' => new IncomeCategory]);
    }

    public function store(IncomeCategoryRequest $request, FinanceCategoryManager $manager): RedirectResponse
    {
        $manager->create('income', $request->validated(), $request->user()->id);

        return redirect()->route('admin.finance.income-categories.index')->with('success', 'Income category created.');
    }

    public function edit(IncomeCategory $incomeCategory): View
    {
        $this->authorize('update', $incomeCategory);

        return view('admin.finance.categories.form', ['type' => 'income', 'category' => $incomeCategory]);
    }

    public function update(IncomeCategoryRequest $request, IncomeCategory $incomeCategory, FinanceCategoryManager $manager): RedirectResponse
    {
        $manager->update('income', $incomeCategory, $request->validated(), $request->user()->id);

        return back()->with('success', 'Income category updated.');
    }

    public function destroy(IncomeCategory $incomeCategory, FinanceCategoryManager $manager): RedirectResponse
    {
        $this->authorize('delete', $incomeCategory);
        $manager->delete('income', $incomeCategory, auth()->id());

        return back()->with('success', 'Income category deleted.');
    }
}
