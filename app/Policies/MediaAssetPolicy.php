<?php

namespace App\Policies;

use App\Models\MediaAsset;
use App\Models\User;

class MediaAssetPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, MediaAsset $asset): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, MediaAsset $asset): bool
    {
        return $user->is_active;
    }

    public function delete(User $user, MediaAsset $asset): bool
    {
        return false;
    }

    public function restore(User $user, MediaAsset $asset): bool
    {
        return false;
    }

    public function forceDelete(User $user, MediaAsset $asset): bool
    {
        return false;
    }
}
