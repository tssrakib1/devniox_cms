<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    public function before(User $u): ?bool
    {
        return $u->isAdmin() ? true : null;
    }

    public function viewAny(User $u): bool
    {
        return $u->hasPermission('leads.view');
    }

    public function view(User $u, Lead $l): bool
    {
        return $u->hasPermission('leads.view');
    }

    public function update(User $u, Lead $l): bool
    {
        return $u->hasPermission('leads.edit');
    }

    public function delete(User $u, Lead $l): bool
    {
        return $u->hasPermission('leads.delete');
    }

    public function restore(User $u, Lead $l): bool
    {
        return false;
    }

    public function forceDelete(User $u, Lead $l): bool
    {
        return false;
    }
}
