<?php

namespace App\Policies;

use App\Models\ExpenseCategory;
use App\Models\User;

class ExpenseCategoryPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, ExpenseCategory $category): bool
    {
        return $user->is_active;
    }

    public function delete(User $user, ExpenseCategory $category): bool
    {
        return false;
    }

    public function restore(User $user, ExpenseCategory $category): bool
    {
        return false;
    }
}
