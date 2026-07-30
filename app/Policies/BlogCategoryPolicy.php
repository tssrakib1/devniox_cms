<?php

namespace App\Policies;

use App\Models\BlogCategory;
use App\Models\User;

class BlogCategoryPolicy
{
    public function before(User $u): ?bool
    {
        return $u->is_active && $u->isAdmin() ? true : null;
    }

    public function viewAny(User $u): bool
    {
        return false;
    }

    public function create(User $u): bool
    {
        return false;
    }

    public function update(User $u, BlogCategory $category): bool
    {
        return false;
    }

    public function delete(User $u, BlogCategory $category): bool
    {
        return false;
    }

    public function restore(User $u, BlogCategory $category): bool
    {
        return false;
    }
}
