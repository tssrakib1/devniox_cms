<?php

namespace App\Policies;

use App\Models\FinanceTransaction;
use App\Models\User;

class FinanceTransactionPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('finance.view');
    }

    public function view(User $user, FinanceTransaction $transaction): bool
    {
        return $user->hasPermission('finance.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('finance.create');
    }

    public function update(User $user, FinanceTransaction $transaction): bool
    {
        return $user->hasPermission('finance.edit');
    }

    public function manageAttachment(User $user, FinanceTransaction $transaction): bool
    {
        return $user->hasPermission('finance.edit');
    }

    public function archive(User $user, FinanceTransaction $transaction): bool
    {
        return $user->hasPermission('finance.delete');
    }

    public function delete(User $user, FinanceTransaction $transaction): bool
    {
        return false;
    }

    public function restore(User $user, FinanceTransaction $transaction): bool
    {
        return false;
    }
}
