<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_return_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_invoice_id')->index();
            $table->unsignedBigInteger('customer_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->decimal('total_amount', 12, 2);
            $table->string('reason', 500)->nullable();
            $table->timestamps();
        });

        Schema::create('sales_return_invoice_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('return_invoice_id')->index();
            $table->unsignedBigInteger('original_invoice_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_amount', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_return_invoice_details');
        Schema::dropIfExists('sales_return_invoices');
    }
};
