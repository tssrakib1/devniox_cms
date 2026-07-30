<?php

namespace App\Services;

use App\Models\FinanceTransaction;
use Illuminate\Database\Eloquent\Builder;

class FinanceReportService
{
    public function query(array $filters): Builder
    {
        [$from, $to] = $this->dates($filters);

        return FinanceTransaction::query()->with(['incomeCategory', 'expenseCategory'])
            ->whereNull('archived_at')->whereBetween('transaction_date', [$from, $to])
            ->when($filters['type'] ?? null, fn (Builder $query, string $type) => $query->where('type', $type))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['income_category_id'] ?? null, fn (Builder $query, int $id) => $query->where('income_category_id', $id))
            ->when($filters['expense_category_id'] ?? null, fn (Builder $query, int $id) => $query->where('expense_category_id', $id))
            ->orderBy('transaction_date')->orderBy('id');
    }

    public function summary(Builder $query): array
    {
        $values = (clone $query)->reorder()->selectRaw("SUM(CASE WHEN type='income' AND status='completed' THEN amount ELSE 0 END) income, SUM(CASE WHEN type='expense' AND status='completed' THEN amount ELSE 0 END) expense, SUM(CASE WHEN type='income' AND status='pending' THEN amount ELSE 0 END) pending_income, SUM(CASE WHEN type='expense' AND status='pending' THEN amount ELSE 0 END) pending_expense")->first();
        $income = (float) ($values->income ?? 0);
        $expense = (float) ($values->expense ?? 0);

        return ['income' => $income, 'expense' => $expense, 'net' => $income - $expense, 'pending_income' => (float) ($values->pending_income ?? 0), 'pending_expense' => (float) ($values->pending_expense ?? 0)];
    }

    public function dates(array $filters): array
    {
        return match ($filters['period'] ?? 'month') {
            'today' => [today()->toDateString(), today()->toDateString()],
            'week' => [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()],
            'year' => [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()],
            'custom' => [$filters['date_from'] ?? now()->startOfMonth()->toDateString(), $filters['date_to'] ?? now()->endOfMonth()->toDateString()],
            default => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
        };
    }
}
