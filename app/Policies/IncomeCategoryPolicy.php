<?php

namespace App\Policies;

use App\Models\IncomeCategory;
use App\Models\User;

class IncomeCategoryPolicy
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

    public function update(User $user, IncomeCategory $category): bool
    {
        return $user->is_active;
    }

    public function delete(User $user, IncomeCategory $category): bool
    {
        return false;
    }

    public function restore(User $user, IncomeCategory $category): bool
    {
        return false;
    }
}
