<?php

namespace App\Policies;

use App\Models\ServiceCategory;
use App\Models\User;

class ServiceCategoryPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, ServiceCategory $category): bool
    {
        return false;
    }

    public function delete(User $user, ServiceCategory $category): bool
    {
        return false;
    }

    public function restore(User $user, ServiceCategory $category): bool
    {
        return false;
    }
}
