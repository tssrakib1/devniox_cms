<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $name = (string) config('auth.system_administrator.name');
        $email = (string) config('auth.system_administrator.email');
        $password = (string) config('auth.system_administrator.password');

        if ($email === '' || $password === '') {
            throw new RuntimeException('ADMIN_EMAIL and ADMIN_PASSWORD must be configured before seeding the system administrator.');
        }

        $role = Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator', 'is_system' => true]);
        $administrator = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role' => UserRole::Admin,
                'role_id' => $role->id,
                'is_active' => true,
            ],
        );

        $administrator->forceFill([
            'email_verified_at' => now(),
        ])->save();
    }
}
