<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('access_roles')) {
            Schema::create('access_roles', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('guard_name')->default('web');
                $table->string('description')->nullable();
                $table->timestamps();
            });
        } elseif (! Schema::hasColumn('access_roles', 'description')) {
            Schema::table('access_roles', function (Blueprint $table) {
                $table->string('description')->nullable();
            });
        }

        if (! Schema::hasTable('access_model_has_roles')) {
            Schema::create('access_model_has_roles', function (Blueprint $table) {
                $table->unsignedBigInteger('role_id');
                $table->string('model_type', 125);
                $table->unsignedBigInteger('model_id');
                $table->index(['model_id', 'model_type'], 'access_model_roles_model_index');
                $table->foreign('role_id')->references('id')->on('access_roles')->onDelete('cascade');
                $table->primary(['role_id', 'model_id', 'model_type'], 'access_model_roles_primary');
            });
        }

        if (! Schema::hasTable('access_role_has_permissions')) {
            Schema::create('access_role_has_permissions', function (Blueprint $table) {
                $table->unsignedBigInteger('permission_id');
                $table->unsignedBigInteger('role_id');
                $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
                $table->foreign('role_id')->references('id')->on('access_roles')->onDelete('cascade');
                $table->primary(['permission_id', 'role_id'], 'access_role_permissions_primary');
            });
        }

        // Preserve existing role IDs so users.role continues to point at the
        // same logical role when it is used by older screens/reports.
        if (Schema::hasTable('roles') && Schema::hasColumn('roles', 'role_id') && Schema::hasColumn('roles', 'role_name')) {
            foreach (DB::table('roles')->whereNull('deleted_at')->get() as $legacyRole) {
                DB::table('access_roles')->updateOrInsert(
                    ['id' => $legacyRole->role_id],
                    [
                        'name' => $legacyRole->role_name,
                        'guard_name' => $legacyRole->guard_name ?: 'web',
                        'description' => $legacyRole->description,
                        'created_at' => $legacyRole->created_at,
                        'updated_at' => $legacyRole->updated_at,
                    ]
                );
            }

            if (Schema::hasTable('users') && Schema::hasColumn('users', 'role')) {
                $users = DB::table('users')->whereNotNull('role')->get(['id', 'role']);
                foreach ($users as $user) {
                    if (DB::table('access_roles')->where('id', $user->role)->exists()
                        && ! DB::table('access_model_has_roles')->where('model_id', $user->id)->where('model_type', 'App\\Models\\User')->exists()) {
                        DB::table('access_model_has_roles')->insert(['role_id' => $user->role, 'model_type' => 'App\\Models\\User', 'model_id' => $user->id]);
                    }
                }
            }

            foreach (DB::table('model_has_roles')->get() as $assignment) {
                if (DB::table('access_roles')->where('id', $assignment->role_id)->exists()) {
                    DB::table('access_model_has_roles')->updateOrInsert(
                        ['role_id' => $assignment->role_id, 'model_id' => $assignment->model_id, 'model_type' => $assignment->model_type], []
                    );
                }
            }

            foreach (DB::table('role_has_permissions')->get() as $assignment) {
                if (DB::table('access_roles')->where('id', $assignment->role_id)->exists()
                    && DB::table('permissions')->where('id', $assignment->permission_id)->exists()) {
                    DB::table('access_role_has_permissions')->updateOrInsert(
                        ['role_id' => $assignment->role_id, 'permission_id' => $assignment->permission_id], []
                    );
                }
            }
        }

        $modules = [
            'dashboard' => 'الرئيسية', 'sales' => 'المبيعات', 'customers' => 'العملاء',
            'purchases' => 'المشتريات', 'suppliers' => 'الموردين', 'products' => 'المنتجات',
            'warehouses' => 'المخازن والفروع', 'expenses' => 'المصروفات', 'reports' => 'التقارير',
            'users' => 'المستخدمين والصلاحيات',
        ];
        foreach ($modules as $module => $label) {
            foreach (['view', 'create', 'update', 'delete'] as $action) {
                DB::table('permissions')->updateOrInsert(
                    ['name' => $module.'.'.$action, 'guard_name' => 'web'],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        if (! DB::table('access_roles')->where('name', 'مدير النظام')->where('guard_name', 'web')->exists()) {
            $adminRoleId = DB::table('access_roles')->insertGetId([
                'name' => 'مدير النظام', 'guard_name' => 'web', 'description' => 'صلاحيات كاملة على النظام',
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $permissionIds = DB::table('permissions')->where('guard_name', 'web')->pluck('id');
            foreach ($permissionIds as $permissionId) {
                DB::table('access_role_has_permissions')->updateOrInsert(['permission_id' => $permissionId, 'role_id' => $adminRoleId], []);
            }
        }

        $allPermissionIds = DB::table('permissions')->where('guard_name', 'web')->pluck('id');
        foreach (DB::table('access_roles')->whereIn('name', ['مدير النظام', 'Super admin'])->pluck('id') as $adminRoleId) {
            foreach ($allPermissionIds as $permissionId) {
                DB::table('access_role_has_permissions')->updateOrInsert(['permission_id' => $permissionId, 'role_id' => $adminRoleId], []);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('access_role_has_permissions');
        Schema::dropIfExists('access_model_has_roles');
        Schema::dropIfExists('access_roles');
    }
};
