<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_return_invoices', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable()->index();
        });

        Schema::table('sales_return_invoice_details', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('sales_return_invoice_details', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('sales_return_invoices', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};
