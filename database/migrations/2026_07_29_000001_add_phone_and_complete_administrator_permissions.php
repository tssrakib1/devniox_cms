<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->string('phone', 40)->nullable()->after('email'));
        $administrator = DB::table('roles')->where('slug', 'administrator')->value('id');
        if ($administrator) {
            $assigned = DB::table('permission_role')->where('role_id', $administrator)->pluck('permission_id');
            $missing = DB::table('permissions')->whereNotIn('id', $assigned)->pluck('id');
            if ($missing->isNotEmpty()) {
                DB::table('permission_role')->insert($missing->map(fn ($permission) => ['role_id' => $administrator, 'permission_id' => $permission])->all());
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('phone'));
    }
};
