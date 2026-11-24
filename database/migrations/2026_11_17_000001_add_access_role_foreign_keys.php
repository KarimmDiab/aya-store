<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $relationships = [
        ['users', 'role', 'access_roles', 'id', 'users_access_role_foreign', 'RESTRICT'],
        ['access_model_has_roles', 'role_id', 'access_roles', 'id', 'access_model_has_roles_role_id_foreign', 'CASCADE'],
        ['access_role_has_permissions', 'role_id', 'access_roles', 'id', 'access_role_has_permissions_role_id_foreign', 'CASCADE'],
        ['access_role_has_permissions', 'permission_id', 'permissions', 'id', 'access_role_has_permissions_permission_id_foreign', 'CASCADE'],
    ];

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        // Check every relationship first so bad existing rows cannot leave a partial set of constraints.
        foreach ($this->relationships as [$child, $column, $parent, $parentColumn]) {
            $orphans = DB::table($child . ' as child')
                ->leftJoin($parent . ' as parent', 'child.' . $column, '=', 'parent.' . $parentColumn)
                ->whereNotNull('child.' . $column)
                ->whereNull('parent.' . $parentColumn)
                ->count();

            if ($orphans > 0) {
                throw new RuntimeException("Cannot add {$child}.{$column} foreign key: {$orphans} orphan row(s) exist.");
            }
        }

        foreach ($this->relationships as [$child, $column, $parent, $parentColumn, $constraint, $onDelete]) {
            if ($this->foreignKeyExists($child, $constraint)) {
                continue;
            }

            DB::statement(
                "ALTER TABLE `{$child}` ADD CONSTRAINT `{$constraint}` FOREIGN KEY (`{$column}`) " .
                "REFERENCES `{$parent}` (`{$parentColumn}`) ON DELETE {$onDelete}"
            );
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        foreach (array_reverse($this->relationships) as [$child, , , , $constraint]) {
            if ($this->foreignKeyExists($child, $constraint)) {
                DB::statement("ALTER TABLE `{$child}` DROP FOREIGN KEY `{$constraint}`");
            }
        }
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraint)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }
};
