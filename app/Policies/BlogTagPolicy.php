<?php

namespace App\Policies;

use App\Models\BlogTag;
use App\Models\User;

class BlogTagPolicy
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

    public function update(User $u, BlogTag $tag): bool
    {
        return false;
    }

    public function delete(User $u, BlogTag $tag): bool
    {
        return false;
    }

    public function restore(User $u, BlogTag $tag): bool
    {
        return false;
    }

    public function merge(User $u): bool
    {
        return false;
    }
}
