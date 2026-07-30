<?php

namespace App\Policies;

use App\Enums\ServiceStatus;
use App\Models\Service;
use App\Models\User;

class ServicePolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('services.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('services.create');
    }

    public function update(User $user, Service $service): bool
    {
        return $user->hasPermission('services.edit');
    }

    public function publish(User $user, Service $service): bool
    {
        return $user->hasPermission('services.edit');
    }

    public function archive(User $user, Service $service): bool
    {
        return false;
    }

    public function feature(User $user, Service $service): bool
    {
        return false;
    }

    public function delete(User $user, Service $service): bool
    {
        return $user->hasPermission('services.delete') && $service->status !== ServiceStatus::Published;
    }
}
