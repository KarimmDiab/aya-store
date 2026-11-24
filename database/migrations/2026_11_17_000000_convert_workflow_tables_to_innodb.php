<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $tables = [
        'access_roles',
        'access_model_has_roles',
        'access_role_has_permissions',
        'sales_return_invoices',
        'sales_return_invoice_details',
    ];

    public function up(): void
    {
        $this->changeEngine('InnoDB');
    }

    public function down(): void
    {
        $this->changeEngine('MyISAM');
    }

    private function changeEngine(string $engine): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        foreach ($this->tables as $table) {
            $tableStatus = DB::selectOne(
                'SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
                [$table]
            );

            if ($tableStatus && strcasecmp($tableStatus->ENGINE, $engine) !== 0) {
                DB::statement("ALTER TABLE `{$table}` ENGINE={$engine}");
            }
        }
    }
};
