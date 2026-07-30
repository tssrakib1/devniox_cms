<?php

namespace App\Policies;

use App\Models\PortfolioCategory;
use App\Models\User;

class PortfolioCategoryPolicy
{
    public function before(User $u): ?bool
    {
        return $u->isAdmin() ? true : null;
    }

    public function viewAny(User $u): bool
    {
        return false;
    }

    public function create(User $u): bool
    {
        return false;
    }

    public function update(User $u, PortfolioCategory $c): bool
    {
        return false;
    }

    public function delete(User $u, PortfolioCategory $c): bool
    {
        return false;
    }

    public function restore(User $u, PortfolioCategory $c): bool
    {
        return false;
    }
}
