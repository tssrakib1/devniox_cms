<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

class ProfileManager
{
    public function __construct(private readonly ManagedImageService $images) {}

    public function update(User $user, array $data): User
    {
        $old = $user->only(['name', 'email', 'phone', 'avatar_path']);
        $newPath = null;

        try {
            $updated = DB::transaction(function () use ($user, $data, &$newPath) {
                if (($data['avatar'] ?? null) instanceof UploadedFile) {
                    $newPath = $this->images->store($data['avatar'], 'profiles/'.$user->id, 512, 512);
                    $data['avatar_path'] = $newPath;
                }

                $user->update([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'avatar_path' => $data['avatar_path'] ?? $user->avatar_path,
                ]);

                return $user->fresh();
            });
        } catch (Throwable $exception) {
            $this->images->delete($newPath);
            throw $exception;
        }

        if ($newPath) {
            $this->images->delete($old['avatar_path']);
        }

        ActivityLogService::log('profile', 'updated', 'Profile information updated.', $updated, $old, $updated->only(['name', 'email', 'phone', 'avatar_path']), $updated->id);
        if ($old['email'] !== $updated->email) {
            ActivityLogService::log('profile', 'email_changed', 'Profile email address changed.', $updated, ['email' => $old['email']], ['email' => $updated->email], $updated->id);
        }
        if ($newPath) {
            ActivityLogService::log('profile', 'avatar_changed', 'Profile avatar changed.', $updated, ['avatar_path' => $old['avatar_path']], ['avatar_path' => $newPath], $updated->id);
        }

        return $updated;
    }

    public function updatePassword(User $user, string $password): void
    {
        $user->forceFill([
            'password' => Hash::make($password),
            'remember_token' => Str::random(60),
        ])->save();

        ActivityLogService::log('profile', 'password_changed', 'Profile password changed.', $user, null, null, $user->id);
    }
}
