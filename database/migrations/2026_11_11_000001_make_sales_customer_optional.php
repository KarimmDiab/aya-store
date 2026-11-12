<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable()->change();
        });

        Schema::table('sales_return_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('sales_invoices') && Schema::hasColumn('sales_invoices', 'customer_id')
            && \Illuminate\Support\Facades\DB::table('sales_invoices')->whereNull('customer_id')->exists()) {
            throw new RuntimeException('Cannot restore required customer_id while anonymous sales invoices exist.');
        }

        if (Schema::hasTable('sales_return_invoices') && Schema::hasColumn('sales_return_invoices', 'customer_id')
            && \Illuminate\Support\Facades\DB::table('sales_return_invoices')->whereNull('customer_id')->exists()) {
            throw new RuntimeException('Cannot restore required customer_id while anonymous sales returns exist.');
        }

        Schema::table('sales_return_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable(false)->change();
        });

        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable(false)->change();
        });
    }
};
