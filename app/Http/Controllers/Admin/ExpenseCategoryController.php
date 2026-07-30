<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExpenseCategoryRequest;
use App\Models\ExpenseCategory;
use App\Services\FinanceCategoryManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ExpenseCategoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', ExpenseCategory::class);

        return view('admin.finance.categories.index', ['type' => 'expense', 'categories' => ExpenseCategory::withCount('transactions')->orderBy('name')->paginate(25)]);
    }

    public function create(): View
    {
        $this->authorize('create', ExpenseCategory::class);

        return view('admin.finance.categories.form', ['type' => 'expense', 'category' => new ExpenseCategory]);
    }

    public function store(ExpenseCategoryRequest $request, FinanceCategoryManager $manager): RedirectResponse
    {
        $manager->create('expense', $request->validated(), $request->user()->id);

        return redirect()->route('admin.finance.expense-categories.index')->with('success', 'Expense category created.');
    }

    public function edit(ExpenseCategory $expenseCategory): View
    {
        $this->authorize('update', $expenseCategory);

        return view('admin.finance.categories.form', ['type' => 'expense', 'category' => $expenseCategory]);
    }

    public function update(ExpenseCategoryRequest $request, ExpenseCategory $expenseCategory, FinanceCategoryManager $manager): RedirectResponse
    {
        $manager->update('expense', $expenseCategory, $request->validated(), $request->user()->id);

        return back()->with('success', 'Expense category updated.');
    }

    public function destroy(ExpenseCategory $expenseCategory, FinanceCategoryManager $manager): RedirectResponse
    {
        $this->authorize('delete', $expenseCategory);
        $manager->delete('expense', $expenseCategory, auth()->id());

        return back()->with('success', 'Expense category deleted.');
    }
}
