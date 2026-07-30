<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('slug', 80)->unique();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('module', 80);
            $table->string('action', 20);
            $table->timestamps();
            $table->unique(['module', 'action']);
        });
        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('role')->constrained()->nullOnDelete();
        });

        $now = now();
        $adminId = DB::table('roles')->insertGetId(['name' => 'Administrator', 'slug' => 'administrator', 'is_system' => true, 'created_at' => $now, 'updated_at' => $now]);
        $editorId = DB::table('roles')->insertGetId(['name' => 'Editor', 'slug' => 'editor', 'is_system' => true, 'created_at' => $now, 'updated_at' => $now]);
        foreach (['dashboard', 'products', 'services', 'portfolio', 'blog', 'leads', 'orders', 'finance', 'users', 'roles', 'website-settings'] as $module) {
            foreach ($module === 'dashboard' ? ['view'] : ['view', 'create', 'edit', 'delete'] as $action) {
                DB::table('permissions')->insert(['module' => $module, 'action' => $action, 'created_at' => $now, 'updated_at' => $now]);
            }
        }
        $editorPermissions = DB::table('permissions')->where(function ($query) {
            $query->where(fn ($q) => $q->where('module', 'dashboard')->where('action', 'view'));
            foreach (['products', 'services', 'portfolio', 'blog'] as $module) {
                $query->orWhere(fn ($q) => $q->where('module', $module)->whereIn('action', ['view', 'create', 'edit', 'delete']));
            }
            foreach (['leads', 'orders', 'finance'] as $module) {
                $query->orWhere(fn ($q) => $q->where('module', $module)->whereIn('action', ['view', 'create', 'edit']));
            }
        })->pluck('id');
        DB::table('permission_role')->insert($editorPermissions->map(fn ($id) => ['role_id' => $editorId, 'permission_id' => $id])->all());
        DB::table('users')->where('role', 'admin')->update(['role_id' => $adminId]);
        DB::table('users')->where('role', 'editor')->update(['role_id' => $editorId]);
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropConstrainedForeignId('role_id'));
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
