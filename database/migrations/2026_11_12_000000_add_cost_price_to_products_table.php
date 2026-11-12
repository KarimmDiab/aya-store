<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products') || Schema::hasColumn('products', 'cost_price')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('cost_price', 12, 2)->default(0)->after('price');
        });

        if (! Schema::hasTable('purchase_invoices_details') || ! Schema::hasTable('purchase_invoices')) {
            return;
        }

        $latestPurchaseDetails = DB::table('purchase_invoices_details as details')
            ->join('purchase_invoices as invoices', 'invoices.id', '=', 'details.purchase_invoices_id')
            ->whereNull('details.deleted_at')
            ->whereNull('invoices.deleted_at')
            ->select('details.product_id')
            ->selectRaw('MAX(details.id) AS latest_detail_id')
            ->groupBy('details.product_id');

        DB::table('products as products')
            ->joinSub($latestPurchaseDetails, 'latest_details', function ($join) {
                $join->on('latest_details.product_id', '=', 'products.id');
            })
            ->join('purchase_invoices_details as details', 'details.id', '=', 'latest_details.latest_detail_id')
            ->update(['products.cost_price' => DB::raw('details.price_per_pices')]);
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'cost_price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('cost_price');
            });
        }
    }
};
