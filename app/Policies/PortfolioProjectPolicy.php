<?php

namespace App\Policies;

use App\Enums\PortfolioStatus;
use App\Models\PortfolioProject;
use App\Models\User;

class PortfolioProjectPolicy
{
    public function before(User $u): ?bool
    {
        return $u->isAdmin() ? true : null;
    }

    public function viewAny(User $u): bool
    {
        return $u->hasPermission('portfolio.view');
    }

    public function create(User $u): bool
    {
        return $u->hasPermission('portfolio.create');
    }

    public function update(User $u, PortfolioProject $p): bool
    {
        return $u->hasPermission('portfolio.edit');
    }

    public function publish(User $u, PortfolioProject $p): bool
    {
        return $u->hasPermission('portfolio.edit');
    }

    public function archive(User $u, PortfolioProject $p): bool
    {
        return false;
    }

    public function feature(User $u, PortfolioProject $p): bool
    {
        return false;
    }

    public function delete(User $u, PortfolioProject $p): bool
    {
        return $u->hasPermission('portfolio.delete') && $p->status !== PortfolioStatus::Published;
    }

    public function restore(User $u, PortfolioProject $p): bool
    {
        return false;
    }

    public function forceDelete(User $u, PortfolioProject $p): bool
    {
        return false;
    }
}
