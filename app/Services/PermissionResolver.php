<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Cache;

class PermissionResolver
{
    private const SESSION_KEY = 'authorization.resolved_permissions';

    public function allows(User $user, string $permission): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if (! $user->role_id) {
            return $this->legacyEditorAllows($user, $permission);
        }

        return in_array($permission, $this->permissions($user), true);
    }

    public function forget(?Session $session = null): void
    {
        $session ??= request()->hasSession() ? request()->session() : null;
        $session?->forget(self::SESSION_KEY);
    }

    private function permissions(User $user): array
    {
        if (! request()->hasSession() || auth()->id() !== $user->id) {
            return $this->query($user);
        }

        $session = request()->session();
        $role = $user->managedRole;
        $signature = $user->id.':'.$user->role_id.':'.($role?->updated_at?->getTimestamp() ?? 0).':'.Cache::get($this->versionKey($user->role_id), 0);
        $cached = $session->get(self::SESSION_KEY);
        if (! is_array($cached) || ($cached['signature'] ?? null) !== $signature) {
            $cached = ['signature' => $signature, 'permissions' => $this->query($user)];
            $session->put(self::SESSION_KEY, $cached);
        }

        return $cached['permissions'];
    }

    private function query(User $user): array
    {
        return $user->managedRole?->permissions()
            ->get(['module', 'action'])
            ->map(fn ($permission) => $permission->module.'.'.$permission->action)
            ->all() ?? [];
    }

    private function legacyEditorAllows(User $user, string $permission): bool
    {
        $legacy = [
            'dashboard.view',
            'products.view', 'products.create', 'products.edit', 'products.delete',
            'services.view', 'services.create', 'services.edit', 'services.delete',
            'portfolio.view', 'portfolio.create', 'portfolio.edit', 'portfolio.delete',
            'blog.view', 'blog.create', 'blog.edit', 'blog.delete',
            'leads.view', 'leads.create', 'leads.edit',
            'orders.view', 'orders.create', 'orders.edit',
            'finance.view', 'finance.create', 'finance.edit',
        ];

        return $user->role === UserRole::Editor && in_array($permission, $legacy, true);
    }

    public function bumpRole(int $roleId): void
    {
        $key = $this->versionKey($roleId);
        Cache::add($key, 0, now()->addYear());
        Cache::increment($key);
        $this->forget();
    }

    private function versionKey(int $roleId): string
    {
        return 'authorization.role.'.$roleId.'.version';
    }
}
