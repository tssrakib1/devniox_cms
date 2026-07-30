<?php

namespace App\Policies;

use App\Models\CmsPage;
use App\Models\User;

class CmsPagePolicy
{
    public function update(User $u, CmsPage $p): bool
    {
        return $u->is_active;
    }
}
