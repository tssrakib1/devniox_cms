<?php

namespace App\Services;

use App\Models\FinanceTransaction;
use Illuminate\Support\Facades\Cache;

class FinanceDashboardService
{
    public function stats(): array
    {
        return Cache::remember('finance.dashboard.stats.v1', now()->addMinute(), function () {
            $base = FinanceTransaction::query()->whereNull('archived_at');
            $all = (clone $base)->where('status', 'completed')->selectRaw("SUM(CASE WHEN type='income' THEN amount ELSE 0 END) income, SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) expense")->first();
            $month = (clone $base)->where('status', 'completed')->whereBetween('transaction_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])->selectRaw("SUM(CASE WHEN type='income' THEN amount ELSE 0 END) income, SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) expense")->first();
            $pending = (clone $base)->where('status', 'pending')->selectRaw("SUM(CASE WHEN type='income' THEN amount ELSE 0 END) income, SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) expense")->first();

            $income = (float) ($month->income ?? 0);
            $expense = (float) ($month->expense ?? 0);

            return ['cash_balance' => (float) ($all->income ?? 0) - (float) ($all->expense ?? 0), 'month_income' => $income, 'month_expense' => $expense, 'pending_receivables' => (float) ($pending->income ?? 0), 'pending_expenses' => (float) ($pending->expense ?? 0), 'month_net_profit' => $income - $expense];
        });
    }

    public static function forget(): void
    {
        Cache::forget('finance.dashboard.stats.v1');
        Cache::forget('admin.dashboard.stats.v3');
    }
}
