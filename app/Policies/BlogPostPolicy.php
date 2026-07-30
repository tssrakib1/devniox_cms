<?php

namespace App\Policies;

use App\Enums\BlogStatus;
use App\Models\BlogPost;
use App\Models\User;

class BlogPostPolicy
{
    public function before(User $u): ?bool
    {
        return $u->isAdmin() ? true : null;
    }

    public function viewAny(User $u): bool
    {
        return $u->hasPermission('blog.view');
    }

    public function create(User $u): bool
    {
        return $u->hasPermission('blog.create');
    }

    public function update(User $u, BlogPost $p): bool
    {
        return $u->hasPermission('blog.edit');
    }

    public function publish(User $u, BlogPost $p): bool
    {
        return $u->hasPermission('blog.edit');
    }

    public function feature(User $u, BlogPost $p): bool
    {
        return false;
    }

    public function delete(User $u, BlogPost $p): bool
    {
        return $u->hasPermission('blog.delete') && $p->status !== BlogStatus::Published;
    }

    public function restore(User $u, BlogPost $p): bool
    {
        return false;
    }

    public function forceDelete(User $u, BlogPost $p): bool
    {
        return false;
    }
}
