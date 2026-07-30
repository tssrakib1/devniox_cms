<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserManagementService
{
    public function __construct(private readonly ManagedImageService $images) {}

    public function createAdministrator(array $data): User
    {
        $data['role_id'] = Role::where('slug', 'administrator')->firstOrFail()->id;
        $data['is_active'] = true;

        return $this->create($data);
    }

    public function create(array $data): User
    {
        $newPath = null;
        try {
            return DB::transaction(function () use ($data, &$newPath) {
                $role = Role::findOrFail($data['role_id']);
                $user = User::create($this->attributes($data, $role));
                if (($data['avatar'] ?? null) instanceof UploadedFile) {
                    $newPath = $this->images->store($data['avatar'], 'profiles/'.$user->id, 512, 512);
                    $user->update(['avatar_path' => $newPath]);
                }
                ActivityLogService::log('users', 'created', "User {$user->email} created.", $user, null, $user->only(['name', 'email', 'phone', 'avatar_path', 'role_id', 'is_active']));

                return $user;
            });
        } catch (\Throwable $exception) {
            $this->images->delete($newPath);
            throw $exception;
        }
    }

    public function update(User $user, array $data): User
    {
        $oldAvatar = $user->avatar_path;
        $newPath = null;
        try {
            $updated = DB::transaction(function () use ($user, $data, &$newPath) {
                $role = Role::findOrFail($data['role_id']);
                $this->guardLastAdministrator($user, $role->slug !== 'administrator' || ! ($data['is_active'] ?? false));
                $before = $user->only(['name', 'email', 'phone', 'avatar_path', 'role_id', 'is_active']);
                $user->update($this->attributes($data, $role));
                if (($data['avatar'] ?? null) instanceof UploadedFile) {
                    $newPath = $this->images->store($data['avatar'], 'profiles/'.$user->id, 512, 512);
                    $user->update(['avatar_path' => $newPath]);
                }
                ActivityLogService::log('users', 'updated', "User {$user->email} updated.", $user, $before, $user->only(array_keys($before)));

                return $user;
            });
        } catch (\Throwable $exception) {
            $this->images->delete($newPath);
            throw $exception;
        }
        if ($newPath) {
            $this->images->delete($oldAvatar);
        }

        return $updated;
    }

    public function delete(User $user): void
    {
        if ($user->is(auth()->user())) {
            throw ValidationException::withMessages(['user' => 'You cannot delete your own account.']);
        }
        $this->guardLastAdministrator($user, true);
        if (DB::table('blog_posts')->where('author_id', $user->id)->exists() || DB::table('lead_notes')->where('author_id', $user->id)->exists()) {
            throw ValidationException::withMessages(['user' => 'This user owns retained content or audit history. Deactivate the account instead of deleting it.']);
        }
        DB::transaction(function () use ($user) {
            $before = $user->only(['name', 'email', 'role_id', 'is_active']);
            ActivityLogService::log('users', 'deleted', "User {$user->email} deleted.", $user, $before);
            $user->delete();
        });
        $this->images->delete($user->avatar_path);
    }

    private function attributes(array $data, Role $role): array
    {
        $attributes = Arr::only($data, ['name', 'email', 'phone', 'password', 'role_id']);
        if (blank($attributes['password'] ?? null)) {
            unset($attributes['password']);
        }
        $attributes['is_active'] = (bool) ($data['is_active'] ?? false);
        $attributes['role'] = $role->slug === 'administrator' ? UserRole::Admin : UserRole::Editor;
        $attributes['email_verified_at'] = now();

        return $attributes;
    }

    private function guardLastAdministrator(User $user, bool $removingAccess): void
    {
        if (! $removingAccess || ! $user->isAdmin()) {
            return;
        }
        $count = User::query()->where('is_active', true)->where(function ($q) {
            $q->where('role', UserRole::Admin)->orWhereHas('managedRole', fn ($r) => $r->where('slug', 'administrator'));
        })->lockForUpdate()->count();
        if ($count <= 1) {
            throw ValidationException::withMessages(['user' => 'The last active Administrator cannot be deleted, deactivated, or reassigned.']);
        }
    }
}
