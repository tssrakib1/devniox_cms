<?php

namespace App\Policies;

use App\Models\MediaFolder;
use App\Models\User;

class MediaFolderPolicy
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

    public function update(User $user, MediaFolder $folder): bool
    {
        return $user->is_active;
    }

    public function delete(User $user, MediaFolder $folder): bool
    {
        return false;
    }
}
