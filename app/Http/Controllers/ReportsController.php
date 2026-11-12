<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    private const LOW_STOCK_THRESHOLD = 5;

    public function index()
    {
        $products = DB::table('products')->whereNull('deleted_at')->get();
        $branches = DB::table('branches')->whereNull('deleted_at')->get();
        $customers = DB::table('customers')->whereNull('deleted_at')->get();
        $users = DB::table('users')->whereNull('deleted_at')->get();

        return view('reports.reports_main_page', compact('products', 'branches', 'customers', 'users'));
    }

    public function salesPerPeriod(Request $request)
    {
        [$startDate, $endDate] = $this->validatedPurchasePeriod($request);

        $invoiceLines = DB::table('sales_invoice_details')
            ->whereNull('deleted_at')
            ->selectRaw('invoice_id, SUM(total_amount) as gross_amount')
            ->groupBy('invoice_id');

        $sales = DB::table('sales_invoice_details as details')
            ->join('sales_invoices as invoices', 'invoices.id', '=', 'details.invoice_id')
            ->joinSub($invoiceLines, 'invoice_lines', function ($join) {
                $join->on('invoice_lines.invoice_id', '=', 'invoices.id');
            })
            ->leftJoin('products', 'products.id', '=', 'details.product_id')
            ->whereDate('invoices.created_at', '>=', $startDate)
            ->whereDate('invoices.created_at', '<=', $endDate)
            ->whereNull('invoices.deleted_at')
            ->whereNull('details.deleted_at')
            ->selectRaw('details.product_id, COALESCE(products.name, ?) as product_name, SUM(details.quantity) as total_sold_quantity, SUM(details.total_amount) as gross_sales, SUM(details.total_amount * COALESCE(invoices.net_total, invoices.total_amount - COALESCE(invoices.deduction, 0)) / NULLIF(invoice_lines.gross_amount, 0)) as sales_amount', ['منتج محذوف'])
            ->groupBy('details.product_id', 'products.name')
            ->get()
            ->keyBy('product_id');

        // Allocate each return invoice's net credit across its items proportionally,
        // so the product totals reconcile to the actual credit issued.
        $returnItems = DB::table('sales_return_invoice_details as details')
            ->join('sales_return_invoices as returns', 'returns.id', '=', 'details.return_invoice_id')
            ->leftJoin('products', 'products.id', '=', 'details.product_id')
            ->whereDate('returns.created_at', '>=', $startDate)
            ->whereDate('returns.created_at', '<=', $endDate)
            ->whereNull('returns.deleted_at')
            ->whereNull('details.deleted_at')
            ->selectRaw('details.product_id, COALESCE(products.name, ?) as product_name, SUM(details.quantity) as returned_quantity, SUM(details.total_amount) as gross_return_amount, SUM(details.total_amount * returns.total_amount / NULLIF(return_lines.gross_amount, 0)) as returned_amount', ['منتج محذوف'])
            ->leftJoinSub(
                DB::table('sales_return_invoice_details')
                    ->whereNull('deleted_at')
                    ->selectRaw('return_invoice_id, SUM(total_amount) as gross_amount')
                    ->groupBy('return_invoice_id'),
                'return_lines',
                'return_lines.return_invoice_id',
                '=',
                'details.return_invoice_id'
            )
            ->groupBy('details.product_id', 'products.name')
            ->get()
            ->keyBy('product_id');

        $productIds = $sales->keys()->merge($returnItems->keys())->unique();
        $total_sales = $productIds->map(function ($productId) use ($sales, $returnItems) {
            $sale = $sales->get($productId);
            $return = $returnItems->get($productId);
            $soldQuantity = (int) ($sale->total_sold_quantity ?? 0);
            $returnedQuantity = (int) ($return->returned_quantity ?? 0);
            $grossSales = (float) ($sale->gross_sales ?? 0);
            $salesAmount = (float) ($sale->sales_amount ?? 0);
            $returnedAmount = (float) ($return->returned_amount ?? 0);

            return (object) [
                'product_id' => $productId,
                'product_name' => $sale->product_name ?? $return->product_name,
                'total_sold_quantity' => $soldQuantity,
                'returned_quantity' => $returnedQuantity,
                'net_sold_quantity' => $soldQuantity - $returnedQuantity,
                'avg_selling_price' => $soldQuantity ? round($grossSales / $soldQuantity, 2) : 0,
                'gross_sales' => round($grossSales, 2),
                'sales_amount' => round($salesAmount, 2),
                'returned_amount' => round($returnedAmount, 2),
                'net_sales' => round($salesAmount - $returnedAmount, 2),
            ];
        })->sortBy('product_name')->values();

        $stats = (object) [
            'row_count' => $total_sales->count(),
            'sold_quantity' => $total_sales->sum('total_sold_quantity'),
            'returned_quantity' => $total_sales->sum('returned_quantity'),
            'sales_amount' => round($total_sales->sum('sales_amount'), 2),
            'returned_amount' => round($total_sales->sum('returned_amount'), 2),
            'net_sales' => round($total_sales->sum('net_sales'), 2),
        ];

        return view('reports.sub_reports.sales_report', [
            'reportType' => 'products', 'rows' => $total_sales, 'stats' => $stats,
            'startDate' => $startDate, 'endDate' => $endDate,
        ]);
    }

    public function totalSales(Request $request)
    {
        [$startDate, $endDate] = $this->validatedPurchasePeriod($request);

        $salesByDate = DB::table('sales_invoices')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->whereNull('deleted_at')
            ->selectRaw('DATE(created_at) as invoice_date, COUNT(id) as number_of_invoices, SUM(COALESCE(net_total, total_amount - COALESCE(deduction, 0))) as invoice_sales')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get()
            ->keyBy('invoice_date');

        $returnsByDate = DB::table('sales_return_invoices')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->whereNull('deleted_at')
            ->selectRaw('DATE(created_at) as return_date, SUM(total_amount) as returned_amount')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get()
            ->keyBy('return_date');

        $total_sales = collect();
        $date = Carbon::parse($startDate);
        $lastDate = Carbon::parse($endDate);
        $invoiceCount = 0;
        $netSales = 0.0;

        while ($date->lte($lastDate)) {
            $day = $date->toDateString();
            $invoices = $salesByDate->get($day);
            $dailyInvoiceSales = (float) ($invoices->invoice_sales ?? 0);
            $dailyReturns = (float) ($returnsByDate->get($day)->returned_amount ?? 0);
            $dailyNetSales = $dailyInvoiceSales - $dailyReturns;

            $total_sales->push((object) [
                'invoice_date' => $day,
                'number_of_invoices' => (int) ($invoices->number_of_invoices ?? 0),
                'invoice_sales' => round($dailyInvoiceSales, 2),
                'returned_amount' => round($dailyReturns, 2),
                'total_amount_of_invoices' => round($dailyNetSales, 2),
            ]);

            $invoiceCount += (int) ($invoices->number_of_invoices ?? 0);
            $netSales += $dailyNetSales;
            $date->addDay();
        }

        $reportStatics = (object) [
            'number_of_invoices' => $invoiceCount,
            'invoice_sales' => round($salesByDate->sum('invoice_sales'), 2),
            'returned_amount' => round($returnsByDate->sum('returned_amount'), 2),
            'total_amount_of_invoices' => round($netSales, 2),
            'avg_amount_of_invoices' => $invoiceCount ? round($netSales / $invoiceCount, 2) : 0,
        ];

        return view('reports.sub_reports.sales_report', [
            'reportType' => 'daily', 'rows' => $total_sales, 'stats' => $reportStatics,
            'startDate' => $startDate, 'endDate' => $endDate,
        ]);
    }

    public function salesPerCustomer(Request $request)
    {
        [$startDate, $endDate] = $this->validatedPurchasePeriod($request);

        $sales = DB::table('sales_invoices')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->whereNull('deleted_at')
            ->selectRaw('customer_id, COUNT(id) as invoices_number, SUM(COALESCE(net_total, total_amount - COALESCE(deduction, 0))) as invoice_sales')
            ->groupBy('customer_id')
            ->get()
            ->keyBy('customer_id');

        $returns = DB::table('sales_return_invoices')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->whereNull('deleted_at')
            ->selectRaw('customer_id, COUNT(id) as return_count, SUM(total_amount) as returned_amount')
            ->groupBy('customer_id')
            ->get()
            ->keyBy('customer_id');

        $customerIds = $sales->keys()->merge($returns->keys())->unique();
        $customerDetails = DB::table('customers')
            ->whereIn('id', $customerIds)
            ->get(['id', 'name', 'contact_info'])
            ->keyBy('id');

        $customers = $customerIds->map(function ($customerId) use ($sales, $returns, $customerDetails) {
            $sale = $sales->get($customerId);
            $return = $returns->get($customerId);
            $customer = $customerDetails->get($customerId);

            return (object) [
                'customer_id' => $customerId,
                'customer_name' => $customer->name ?? 'عميل محذوف',
                'customer_phone' => $customer->contact_info ?? '',
                'invoices_number' => (int) ($sale->invoices_number ?? 0),
                'sales_amount' => (float) ($sale->invoice_sales ?? 0),
                'return_count' => (int) ($return->return_count ?? 0),
                'returned_amount' => (float) ($return->returned_amount ?? 0),
                'net_sales' => round((float) ($sale->invoice_sales ?? 0) - (float) ($return->returned_amount ?? 0), 2),
            ];
        })->sortByDesc('net_sales')->values();
        $stats = (object) [
            'row_count' => $customers->count(),
            'invoice_count' => $customers->sum('invoices_number'),
            'sales_amount' => round($customers->sum('sales_amount'), 2),
            'returned_amount' => round($customers->sum('returned_amount'), 2),
            'net_sales' => round($customers->sum('net_sales'), 2),
        ];

        return view('reports.sub_reports.sales_report', [
            'reportType' => 'customers', 'rows' => $customers, 'stats' => $stats,
            'startDate' => $startDate, 'endDate' => $endDate,
        ]);
    }

    public function customerSalesReport(Request $request)
    {
        [$startDate, $endDate] = $this->validatedPurchasePeriod($request);
        $sales = DB::table('sales_invoices')
            ->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)
            ->whereNull('deleted_at')
            ->selectRaw('customer_id, COUNT(id) as invoice_count, SUM(COALESCE(net_total, total_amount - COALESCE(deduction, 0))) as sales_amount')
            ->groupBy('customer_id')->get()->keyBy('customer_id');
        $returns = DB::table('sales_return_invoices')
            ->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)
            ->whereNull('deleted_at')
            ->selectRaw('customer_id, COUNT(id) as return_count, SUM(total_amount) as returned_amount')
            ->groupBy('customer_id')->get()->keyBy('customer_id');
        $customerIds = $sales->keys()->merge($returns->keys())->unique();
        $customerDetails = DB::table('customers')->whereIn('id', $customerIds)
            ->get(['id', 'name', 'contact_info'])->keyBy('id');
        $customers = $customerIds->map(function ($customerId) use ($sales, $returns, $customerDetails) {
            $sale = $sales->get($customerId);
            $return = $returns->get($customerId);
            $customer = $customerDetails->get($customerId);
            $salesAmount = (float) ($sale->sales_amount ?? 0);
            $returnedAmount = (float) ($return->returned_amount ?? 0);

            return (object) [
                'customer_id' => $customerId,
                'customer_name' => $customer->name ?? 'بدون بيانات عميل',
                'customer_phone' => $customer->contact_info ?? '',
                'invoice_count' => (int) ($sale->invoice_count ?? 0),
                'sales_amount' => $salesAmount,
                'return_count' => (int) ($return->return_count ?? 0),
                'returned_amount' => $returnedAmount,
                'net_sales' => $salesAmount - $returnedAmount,
            ];
        })->sortByDesc('net_sales')->values();
        $stats = (object) [
            'customer_count' => $customers->count(),
            'invoice_count' => $customers->sum('invoice_count'),
            'sales_amount' => round($customers->sum('sales_amount'), 2),
            'returned_amount' => round($customers->sum('returned_amount'), 2),
            'net_sales' => round($customers->sum('net_sales'), 2),
        ];

        return view('reports.sub_reports.customer_report', [
            'reportType' => 'sales', 'startDate' => $startDate, 'endDate' => $endDate,
            'customers' => $customers, 'stats' => $stats,
        ]);
    }

    public function customerBalancesReport(Request $request)
    {
        [$startDate, $endDate] = $this->validatedPurchasePeriod($request);
        $balances = $this->customerBalanceSnapshots($startDate, $endDate);
        $sales = DB::table('sales_invoices')
            ->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)
            ->whereNull('deleted_at')
            ->selectRaw('customer_id, COUNT(id) as invoice_count, SUM(COALESCE(net_total, total_amount - COALESCE(deduction, 0))) as sales_amount')
            ->groupBy('customer_id')->get()->keyBy('customer_id');
        $returns = DB::table('sales_return_invoices')
            ->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)
            ->whereNull('deleted_at')
            ->selectRaw('customer_id, SUM(total_amount) as returned_amount')
            ->groupBy('customer_id')->get()->keyBy('customer_id');
        $collections = $this->customerCollectionsByCustomer($startDate, $endDate);
        $customerIds = $balances->keys()->merge($sales->keys())->merge($returns->keys())->merge($collections->keys())->unique();
        $customerDetails = DB::table('customers')->whereIn('id', $customerIds)
            ->get(['id', 'name', 'contact_info'])->keyBy('id');
        $customers = $customerIds->map(function ($customerId) use ($balances, $sales, $returns, $collections, $customerDetails) {
            $balance = $balances->get($customerId);
            $sale = $sales->get($customerId);
            $return = $returns->get($customerId);
            $collection = $collections->get($customerId);
            $customer = $customerDetails->get($customerId);

            return (object) [
                'customer_id' => $customerId,
                'customer_name' => $customer->name ?? 'بدون بيانات عميل',
                'customer_phone' => $customer->contact_info ?? '',
                'invoice_count' => (int) ($sale->invoice_count ?? 0),
                'opening_balance' => (float) ($balance->opening_balance ?? 0),
                'sales_amount' => (float) ($sale->sales_amount ?? 0),
                'returned_amount' => (float) ($return->returned_amount ?? 0),
                'collected_amount' => (float) ($collection->total_collected ?? 0),
                'closing_balance' => (float) ($balance->closing_balance ?? 0),
            ];
        })->filter(fn ($customer) => $customer->opening_balance > 0.009 || $customer->closing_balance > 0.009
            || $customer->invoice_count > 0 || $customer->returned_amount > 0.009 || $customer->collected_amount > 0.009)
            ->sortByDesc('closing_balance')->values();
        $stats = (object) [
            'customer_count' => $customers->count(),
            'opening_balance' => round($customers->sum('opening_balance'), 2),
            'sales_amount' => round($customers->sum('sales_amount'), 2),
            'returned_amount' => round($customers->sum('returned_amount'), 2),
            'collected_amount' => round($customers->sum('collected_amount'), 2),
            'closing_balance' => round($customers->sum('closing_balance'), 2),
        ];

        return view('reports.sub_reports.customer_report', [
            'reportType' => 'balances', 'startDate' => $startDate, 'endDate' => $endDate,
            'customers' => $customers, 'stats' => $stats,
        ]);
    }

    public function customerCollectionsReport(Request $request)
    {
        [$startDate, $endDate] = $this->validatedPurchasePeriod($request);
        $collections = $this->customerCollectionsByCustomer($startDate, $endDate);
        $customerIds = $collections->keys();
        $customerDetails = DB::table('customers')->whereIn('id', $customerIds)
            ->get(['id', 'name', 'contact_info'])->keyBy('id');
        $customers = $collections->map(function ($collection, $customerId) use ($customerDetails) {
            $customer = $customerDetails->get($customerId);

            return (object) [
                'customer_id' => $customerId,
                'customer_name' => $customer->name ?? 'بدون بيانات عميل',
                'customer_phone' => $customer->contact_info ?? '',
                'invoice_count' => (int) $collection->invoice_count,
                'invoice_payments' => (float) $collection->invoice_payments,
                'later_payments' => (float) $collection->later_payments,
                'total_collected' => (float) $collection->total_collected,
            ];
        })->sortByDesc('total_collected')->values();
        $stats = (object) [
            'customer_count' => $customers->count(),
            'invoice_count' => $customers->sum('invoice_count'),
            'invoice_payments' => round($customers->sum('invoice_payments'), 2),
            'later_payments' => round($customers->sum('later_payments'), 2),
            'total_collected' => round($customers->sum('total_collected'), 2),
        ];

        return view('reports.sub_reports.customer_report', [
            'reportType' => 'collections', 'startDate' => $startDate, 'endDate' => $endDate,
            'customers' => $customers, 'stats' => $stats,
        ]);
    }

    public function salesPerUser(Request $request)
    {
        [$startDate, $endDate] = $this->validatedPurchasePeriod($request);

        $sales = DB::table('sales_invoices')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->whereNull('deleted_at')
            ->selectRaw('user_id, COUNT(id) as invoice_count, SUM(COALESCE(net_total, total_amount - COALESCE(deduction, 0))) as invoice_sales')
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $returns = DB::table('sales_return_invoices as returns')
            ->join('sales_invoices as original_invoice', 'original_invoice.id', '=', 'returns.original_invoice_id')
            ->whereDate('returns.created_at', '>=', $startDate)
            ->whereDate('returns.created_at', '<=', $endDate)
            ->whereNull('returns.deleted_at')
            ->selectRaw('original_invoice.user_id, COUNT(returns.id) as return_count, SUM(returns.total_amount) as returned_amount')
            ->groupBy('original_invoice.user_id')
            ->get()
            ->keyBy('user_id');

        $userIds = $sales->keys()->merge($returns->keys())->unique();
        $userDetails = DB::table('users')
            ->whereIn('id', $userIds)
            ->get(['id', 'name', 'mobile_number', 'email', 'gender'])
            ->keyBy('id');

        $salesReportUser = $userIds->map(function ($userId) use ($sales, $returns, $userDetails) {
            $sale = $sales->get($userId);
            $return = $returns->get($userId);
            $user = $userDetails->get($userId);

            return (object) [
                'user_id' => $userId,
                'user_name' => $user->name ?? 'موظف محذوف',
                'user_phone' => $user->mobile_number ?? '',
                'user_email' => $user->email ?? '',
                'user_gender' => $user->gender ?? null,
                'count_sales_invoice' => (int) ($sale->invoice_count ?? 0),
                'sales_amount' => (float) ($sale->invoice_sales ?? 0),
                'return_count' => (int) ($return->return_count ?? 0),
                'returned_amount' => (float) ($return->returned_amount ?? 0),
                'net_sales' => round((float) ($sale->invoice_sales ?? 0) - (float) ($return->returned_amount ?? 0), 2),
            ];
        })->sortByDesc('net_sales')->values();

        $stats = (object) [
            'row_count' => $salesReportUser->count(),
            'invoice_count' => $salesReportUser->sum('count_sales_invoice'),
            'sales_amount' => round($salesReportUser->sum('sales_amount'), 2),
            'returned_amount' => round($salesReportUser->sum('returned_amount'), 2),
            'net_sales' => round($salesReportUser->sum('net_sales'), 2),
        ];

        return view('reports.sub_reports.sales_report', [
            'reportType' => 'users', 'rows' => $salesReportUser, 'stats' => $stats,
            'startDate' => $startDate, 'endDate' => $endDate,
        ]);
    }

    public function inventoryStockSummary(Request $request)
    {
        [$startDate, $endDate] = $this->validatedInventoryPeriod($request);
        [$products, $stats] = $this->inventorySnapshot($startDate, $endDate);

        return view('reports.sub_reports.inventory_report', [
            'reportType' => 'summary',
            'products' => $products,
            'stats' => $stats,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'threshold' => self::LOW_STOCK_THRESHOLD,
        ]);
    }

    public function inventoryMovements(Request $request)
    {
        [$startDate, $endDate] = $this->validatedInventoryPeriod($request);
        [, $stats] = $this->inventorySnapshot($startDate, $endDate);

        $purchaseMovements = DB::table('purchase_invoices_details as details')
            ->join('purchase_invoices as invoices', 'invoices.id', '=', 'details.purchase_invoices_id')
            ->leftJoin('products', 'products.id', '=', 'details.product_id')
            ->whereDate('invoices.date', '>=', $startDate)
            ->whereDate('invoices.date', '<=', $endDate)
            ->whereNull('invoices.deleted_at')
            ->whereNull('details.deleted_at')
            ->selectRaw("invoices.date as movement_date, details.id as movement_id, details.product_id, COALESCE(products.name, 'منتج محذوف') as product_name, 'استلام شراء' as movement_type, invoices.id as reference, details.quantity as quantity_in, 0 as quantity_out");

        $salesMovements = DB::table('sales_invoice_details as details')
            ->join('sales_invoices as invoices', 'invoices.id', '=', 'details.invoice_id')
            ->leftJoin('products', 'products.id', '=', 'details.product_id')
            ->whereDate('invoices.created_at', '>=', $startDate)
            ->whereDate('invoices.created_at', '<=', $endDate)
            ->whereNull('invoices.deleted_at')
            ->whereNull('details.deleted_at')
            ->selectRaw("DATE(invoices.created_at) as movement_date, details.invoice_id as movement_id, details.product_id, COALESCE(products.name, 'منتج محذوف') as product_name, 'صرف مبيعات' as movement_type, invoices.id as reference, 0 as quantity_in, details.quantity as quantity_out");

        $returnMovements = DB::table('sales_return_invoice_details as details')
            ->join('sales_return_invoices as returns', 'returns.id', '=', 'details.return_invoice_id')
            ->leftJoin('products', 'products.id', '=', 'details.product_id')
            ->whereDate('returns.created_at', '>=', $startDate)
            ->whereDate('returns.created_at', '<=', $endDate)
            ->whereNull('returns.deleted_at')
            ->whereNull('details.deleted_at')
            ->selectRaw("DATE(returns.created_at) as movement_date, details.id as movement_id, details.product_id, COALESCE(products.name, 'منتج محذوف') as product_name, 'مرتجع مبيعات' as movement_type, returns.id as reference, details.quantity as quantity_in, 0 as quantity_out");

        $movementUnion = $purchaseMovements->unionAll($salesMovements)->unionAll($returnMovements);
        $movements = DB::query()
            ->fromSub($movementUnion, 'movements')
            ->orderByDesc('movement_date')
            ->orderByDesc('movement_id')
            ->paginate(50)
            ->appends($request->query());

        return view('reports.sub_reports.inventory_report', [
            'reportType' => 'movements',
            'movements' => $movements,
            'stats' => $stats,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'threshold' => self::LOW_STOCK_THRESHOLD,
        ]);
    }

    public function inventoryLowStock(Request $request)
    {
        [$startDate, $endDate] = $this->validatedInventoryPeriod($request);
        [$products, $stats] = $this->inventorySnapshot($startDate, $endDate);
        $lowStockProducts = $products
            ->filter(fn ($product) => $product->closing_quantity <= self::LOW_STOCK_THRESHOLD)
            ->sortBy('closing_quantity')
            ->values();

        return view('reports.sub_reports.inventory_report', [
            'reportType' => 'low_stock',
            'products' => $lowStockProducts,
            'stats' => $stats,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'threshold' => self::LOW_STOCK_THRESHOLD,
        ]);
    }

    public function purchaseTotals(Request $request)
    {
        [$startDate, $endDate] = $this->validatedPurchasePeriod($request);
        $invoices = DB::table('purchase_invoices')
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->whereNull('deleted_at')
            ->selectRaw('DATE(date) as report_date, COUNT(id) as invoice_count, SUM(total_amount) as purchase_amount, SUM(paid_amount) as invoice_paid')
            ->groupBy(DB::raw('DATE(date)'))
            ->get()
            ->keyBy('report_date');

        $payments = $this->supplierPaymentTotalsByDate($startDate, $endDate);
        $dailyRows = collect();
        $day = \Illuminate\Support\Carbon::parse($startDate);
        $lastDay = \Illuminate\Support\Carbon::parse($endDate);
        $invoiceCount = 0;
        $purchaseAmount = 0.0;
        $invoicePaid = 0.0;

        while ($day->lte($lastDay)) {
            $date = $day->toDateString();
            $invoice = $invoices->get($date);
            $payment = $payments->get($date);
            $dailyRows->push((object) [
                'report_date' => $date,
                'invoice_count' => (int) ($invoice->invoice_count ?? 0),
                'purchase_amount' => (float) ($invoice->purchase_amount ?? 0),
                'invoice_paid' => (float) ($invoice->invoice_paid ?? 0),
                'later_payments' => (float) ($payment->payment_amount ?? 0),
                'paid_amount' => (float) ($invoice->invoice_paid ?? 0) + (float) ($payment->payment_amount ?? 0),
            ]);

            $invoiceCount += (int) ($invoice->invoice_count ?? 0);
            $purchaseAmount += (float) ($invoice->purchase_amount ?? 0);
            $invoicePaid += (float) ($invoice->invoice_paid ?? 0);
            $day->addDay();
        }

        $balanceSnapshots = $this->supplierBalanceSnapshots($startDate, $endDate);
        $opening = $balanceSnapshots->sum('opening_payable');
        $closing = $balanceSnapshots->sum('closing_payable');
        $laterPaymentTotal = $payments->sum('payment_amount');
        $stats = (object) [
            'invoice_count' => $invoiceCount,
            'purchase_amount' => round($purchaseAmount, 2),
            'invoice_paid' => round($invoicePaid, 2),
            'later_payments' => round($laterPaymentTotal, 2),
            'paid_amount' => round($invoicePaid + $laterPaymentTotal, 2),
            'opening_payable' => round($opening, 2),
            'closing_payable' => round($closing, 2),
            'average_invoice' => $invoiceCount ? round($purchaseAmount / $invoiceCount, 2) : 0,
        ];

        return view('reports.sub_reports.purchase_report', [
            'reportType' => 'total', 'startDate' => $startDate, 'endDate' => $endDate,
            'dailyRows' => $dailyRows, 'stats' => $stats,
        ]);
    }

    public function purchasesPerSupplier(Request $request)
    {
        [$startDate, $endDate] = $this->validatedPurchasePeriod($request);
        $purchaseRows = DB::table('purchase_invoices')
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->whereNull('deleted_at')
            ->selectRaw('supplier_id, COUNT(id) as invoice_count, SUM(total_amount) as purchase_amount, SUM(paid_amount) as invoice_paid')
            ->groupBy('supplier_id')
            ->get()
            ->keyBy('supplier_id');

        $paymentRows = $this->supplierPaymentsBySupplier($startDate, $endDate);
        $supplierIds = $purchaseRows->keys()->merge($paymentRows->keys())->unique();
        $supplierDetails = DB::table('suppliers')->whereIn('id', $supplierIds)
            ->get(['id', 'name', 'contact_info'])->keyBy('id');
        $suppliers = $supplierIds->map(function ($supplierId) use ($purchaseRows, $paymentRows, $supplierDetails) {
            $purchase = $purchaseRows->get($supplierId);
            $payment = $paymentRows->get($supplierId);
            $supplier = $supplierDetails->get($supplierId);

            return (object) [
                'supplier_id' => $supplierId,
                'supplier_name' => $supplier->name ?? 'مورد محذوف',
                'supplier_phone' => $supplier->contact_info ?? '',
                'invoice_count' => (int) ($purchase->invoice_count ?? 0),
                'purchase_amount' => (float) ($purchase->purchase_amount ?? 0),
                'invoice_paid' => (float) ($purchase->invoice_paid ?? 0),
                'later_payments' => (float) ($payment->payment_amount ?? 0),
                'paid_amount' => (float) ($purchase->invoice_paid ?? 0) + (float) ($payment->payment_amount ?? 0),
            ];
        })->sortByDesc('purchase_amount')->values();

        $stats = (object) [
            'supplier_count' => $suppliers->count(),
            'invoice_count' => $suppliers->sum('invoice_count'),
            'purchase_amount' => round($suppliers->sum('purchase_amount'), 2),
            'paid_amount' => round($suppliers->sum('paid_amount'), 2),
        ];

        return view('reports.sub_reports.purchase_report', [
            'reportType' => 'suppliers', 'startDate' => $startDate, 'endDate' => $endDate,
            'suppliers' => $suppliers, 'stats' => $stats,
        ]);
    }

    public function purchasesPerProduct(Request $request)
    {
        [$startDate, $endDate] = $this->validatedPurchasePeriod($request);
        $products = DB::table('purchase_invoices_details as details')
            ->join('purchase_invoices as invoices', 'invoices.id', '=', 'details.purchase_invoices_id')
            ->leftJoin('products', 'products.id', '=', 'details.product_id')
            ->whereDate('invoices.date', '>=', $startDate)
            ->whereDate('invoices.date', '<=', $endDate)
            ->whereNull('invoices.deleted_at')
            ->whereNull('details.deleted_at')
            ->groupBy('details.product_id', 'products.name')
            ->selectRaw("details.product_id, COALESCE(products.name, 'منتج محذوف') as product_name, SUM(details.quantity) as quantity_purchased, SUM(details.quantity * details.price_per_pices) as purchase_amount, COUNT(DISTINCT invoices.id) as invoice_count, SUM(details.quantity * details.price_per_pices) / NULLIF(SUM(details.quantity), 0) as average_unit_cost")
            ->orderByDesc('purchase_amount')
            ->get();

        $stats = (object) [
            'product_count' => $products->count(),
            'quantity_purchased' => $products->sum('quantity_purchased'),
            'purchase_amount' => round($products->sum('purchase_amount'), 2),
            'invoice_count' => (int) DB::table('purchase_invoices')
                ->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate)->whereNull('deleted_at')->count(),
        ];

        return view('reports.sub_reports.purchase_report', [
            'reportType' => 'products', 'startDate' => $startDate, 'endDate' => $endDate,
            'products' => $products, 'stats' => $stats,
        ]);
    }

    public function supplierPaymentsReport(Request $request)
    {
        [$startDate, $endDate] = $this->validatedPurchasePeriod($request);
        $purchaseRows = DB::table('purchase_invoices')
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->whereNull('deleted_at')
            ->selectRaw('supplier_id, COUNT(id) as invoice_count, SUM(total_amount) as purchase_amount, SUM(paid_amount) as invoice_paid')
            ->groupBy('supplier_id')->get()->keyBy('supplier_id');
        $periodPayments = $this->supplierPaymentsBySupplier($startDate, $endDate);
        $balanceSnapshots = $this->supplierBalanceSnapshots($startDate, $endDate);
        $supplierIds = $purchaseRows->keys()
            ->merge($periodPayments->keys())
            ->merge($balanceSnapshots->keys())
            ->unique();
        $supplierDetails = DB::table('suppliers')->whereIn('id', $supplierIds)
            ->get(['id', 'name', 'contact_info'])->keyBy('id');
        $suppliers = $supplierIds->map(function ($supplierId) use ($purchaseRows, $periodPayments, $balanceSnapshots, $supplierDetails) {
            $purchase = $purchaseRows->get($supplierId);
            $payment = $periodPayments->get($supplierId);
            $supplier = $supplierDetails->get($supplierId);

            return (object) [
                'supplier_id' => $supplierId,
                'supplier_name' => $supplier->name ?? 'مورد محذوف',
                'supplier_phone' => $supplier->contact_info ?? '',
                'invoice_count' => (int) ($purchase->invoice_count ?? 0),
                'opening_payable' => (float) ($balanceSnapshots->get($supplierId)->opening_payable ?? 0),
                'purchase_amount' => (float) ($purchase->purchase_amount ?? 0),
                'paid_amount' => (float) ($purchase->invoice_paid ?? 0) + (float) ($payment->payment_amount ?? 0),
                'closing_payable' => (float) ($balanceSnapshots->get($supplierId)->closing_payable ?? 0),
            ];
        })->sortByDesc('closing_payable')->values();

        $stats = (object) [
            'supplier_count' => $suppliers->count(),
            'opening_payable' => round($suppliers->sum('opening_payable'), 2),
            'purchase_amount' => round($suppliers->sum('purchase_amount'), 2),
            'paid_amount' => round($suppliers->sum('paid_amount'), 2),
            'closing_payable' => round($suppliers->sum('closing_payable'), 2),
        ];

        return view('reports.sub_reports.purchase_report', [
            'reportType' => 'payments', 'startDate' => $startDate, 'endDate' => $endDate,
            'suppliers' => $suppliers, 'stats' => $stats,
        ]);
    }

    public function supplierPaidReport(Request $request)
    {
        [$startDate, $endDate] = $this->validatedPurchasePeriod($request);
        $purchases = DB::table('purchase_invoices')
            ->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate)
            ->whereNull('deleted_at')
            ->selectRaw('supplier_id, COUNT(id) as invoice_count, SUM(total_amount) as purchase_amount, SUM(paid_amount) as invoice_paid')
            ->groupBy('supplier_id')->get()->keyBy('supplier_id');
        $laterPayments = $this->supplierPaymentsBySupplier($startDate, $endDate);
        $supplierIds = $purchases->keys()->merge($laterPayments->keys())->unique();
        $supplierDetails = DB::table('suppliers')->whereIn('id', $supplierIds)
            ->get(['id', 'name', 'contact_info'])->keyBy('id');
        $suppliers = $supplierIds->map(function ($supplierId) use ($purchases, $laterPayments, $supplierDetails) {
            $purchase = $purchases->get($supplierId);
            $payment = $laterPayments->get($supplierId);
            $supplier = $supplierDetails->get($supplierId);

            return (object) [
                'supplier_id' => $supplierId,
                'supplier_name' => $supplier->name ?? 'مورد محذوف',
                'supplier_phone' => $supplier->contact_info ?? '',
                'invoice_count' => (int) ($purchase->invoice_count ?? 0),
                'purchase_amount' => (float) ($purchase->purchase_amount ?? 0),
                'invoice_paid' => (float) ($purchase->invoice_paid ?? 0),
                'later_paid' => (float) ($payment->payment_amount ?? 0),
                'paid_amount' => (float) ($purchase->invoice_paid ?? 0) + (float) ($payment->payment_amount ?? 0),
            ];
        })->sortByDesc('paid_amount')->values();
        $stats = (object) [
            'supplier_count' => $suppliers->count(),
            'invoice_count' => $suppliers->sum('invoice_count'),
            'purchase_amount' => round($suppliers->sum('purchase_amount'), 2),
            'invoice_paid' => round($suppliers->sum('invoice_paid'), 2),
            'later_paid' => round($suppliers->sum('later_paid'), 2),
            'paid_amount' => round($suppliers->sum('paid_amount'), 2),
        ];

        return view('reports.sub_reports.supplier_report', [
            'reportType' => 'paid', 'startDate' => $startDate, 'endDate' => $endDate,
            'suppliers' => $suppliers, 'stats' => $stats,
        ]);
    }

    public function supplierBalancesReport(Request $request)
    {
        [$startDate, $endDate] = $this->validatedPurchasePeriod($request);
        $purchases = DB::table('purchase_invoices')
            ->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate)
            ->whereNull('deleted_at')
            ->selectRaw('supplier_id, COUNT(id) as invoice_count, SUM(total_amount) as purchase_amount, SUM(paid_amount) as invoice_paid')
            ->groupBy('supplier_id')->get()->keyBy('supplier_id');
        $payments = $this->supplierPaymentsBySupplier($startDate, $endDate);
        $balances = $this->supplierBalanceSnapshots($startDate, $endDate);
        $supplierIds = $purchases->keys()->merge($payments->keys())->merge($balances->keys())->unique();
        $supplierDetails = DB::table('suppliers')->whereIn('id', $supplierIds)
            ->get(['id', 'name', 'contact_info'])->keyBy('id');
        $suppliers = $supplierIds->map(function ($supplierId) use ($purchases, $payments, $balances, $supplierDetails) {
            $purchase = $purchases->get($supplierId);
            $payment = $payments->get($supplierId);
            $balance = $balances->get($supplierId);
            $supplier = $supplierDetails->get($supplierId);

            return (object) [
                'supplier_id' => $supplierId,
                'supplier_name' => $supplier->name ?? 'مورد محذوف',
                'supplier_phone' => $supplier->contact_info ?? '',
                'invoice_count' => (int) ($purchase->invoice_count ?? 0),
                'opening_payable' => (float) ($balance->opening_payable ?? 0),
                'purchase_amount' => (float) ($purchase->purchase_amount ?? 0),
                'paid_amount' => (float) ($payment->payment_amount ?? 0)
                    + (float) ($purchases->get($supplierId)->invoice_paid ?? 0),
                'closing_payable' => (float) ($balance->closing_payable ?? 0),
            ];
        })->sortByDesc('closing_payable')->values();
        $stats = (object) [
            'supplier_count' => $suppliers->count(),
            'opening_payable' => round($suppliers->sum('opening_payable'), 2),
            'purchase_amount' => round($suppliers->sum('purchase_amount'), 2),
            'paid_amount' => round($suppliers->sum('paid_amount'), 2),
            'closing_payable' => round($suppliers->sum('closing_payable'), 2),
        ];

        return view('reports.sub_reports.supplier_report', [
            'reportType' => 'balances', 'startDate' => $startDate, 'endDate' => $endDate,
            'suppliers' => $suppliers, 'stats' => $stats,
        ]);
    }

    public function profitSummary(Request $request)
    {
        [$startDate, $endDate] = $this->validatedPurchasePeriod($request);
        $profitData = $this->profitPeriodData($startDate, $endDate);

        return view('reports.sub_reports.profit_report', [
            'reportType' => 'summary', 'startDate' => $startDate, 'endDate' => $endDate,
            ...$profitData,
        ]);
    }

    public function profitComparison(Request $request)
    {
        [$startDate, $endDate] = $this->validatedPurchasePeriod($request);
        $current = $this->profitPeriodData($startDate, $endDate);
        $periodDays = \Illuminate\Support\Carbon::parse($startDate)->diffInDays(\Illuminate\Support\Carbon::parse($endDate)) + 1;
        $previousEnd = \Illuminate\Support\Carbon::parse($startDate)->subDay()->toDateString();
        $previousStart = \Illuminate\Support\Carbon::parse($previousEnd)->subDays($periodDays - 1)->toDateString();
        $previous = $this->profitPeriodData($previousStart, $previousEnd);

        return view('reports.sub_reports.profit_report', [
            'reportType' => 'comparison', 'startDate' => $startDate, 'endDate' => $endDate,
            'previousStartDate' => $previousStart, 'previousEndDate' => $previousEnd,
            'currentStats' => $current['stats'], 'previousStats' => $previous['stats'],
        ]);
    }

    private function profitPeriodData(string $startDate, string $endDate): array
    {
        $invoiceLines = DB::table('sales_invoice_details')
            ->whereNull('deleted_at')
            ->selectRaw('invoice_id, SUM(total_amount) as gross_amount')
            ->groupBy('invoice_id');
        $sales = DB::table('sales_invoice_details as details')
            ->join('sales_invoices as invoices', 'invoices.id', '=', 'details.invoice_id')
            ->joinSub($invoiceLines, 'invoice_lines', function ($join) {
                $join->on('invoice_lines.invoice_id', '=', 'invoices.id');
            })
            ->whereDate('invoices.created_at', '>=', $startDate)
            ->whereDate('invoices.created_at', '<=', $endDate)
            ->whereNull('invoices.deleted_at')->whereNull('details.deleted_at')
            ->selectRaw('details.product_id, SUM(details.quantity) as sold_quantity, SUM(details.total_amount * COALESCE(invoices.net_total, invoices.total_amount - COALESCE(invoices.deduction, 0)) / NULLIF(invoice_lines.gross_amount, 0)) as sales_amount')
            ->groupBy('details.product_id')->get()->keyBy('product_id');
        $returnLines = DB::table('sales_return_invoice_details')
            ->whereNull('deleted_at')
            ->selectRaw('return_invoice_id, SUM(total_amount) as gross_amount')
            ->groupBy('return_invoice_id');
        $returns = DB::table('sales_return_invoice_details as details')
            ->join('sales_return_invoices as returns', 'returns.id', '=', 'details.return_invoice_id')
            ->leftJoinSub($returnLines, 'return_lines', function ($join) {
                $join->on('return_lines.return_invoice_id', '=', 'details.return_invoice_id');
            })
            ->whereDate('returns.created_at', '>=', $startDate)
            ->whereDate('returns.created_at', '<=', $endDate)
            ->whereNull('returns.deleted_at')->whereNull('details.deleted_at')
            ->selectRaw('details.product_id, SUM(details.quantity) as returned_quantity, SUM(details.total_amount * returns.total_amount / NULLIF(return_lines.gross_amount, 0)) as returned_amount')
            ->groupBy('details.product_id')->get()->keyBy('product_id');
        $purchaseCosts = DB::table('purchase_invoices_details as details')
            ->join('purchase_invoices as invoices', 'invoices.id', '=', 'details.purchase_invoices_id')
            ->whereDate('invoices.date', '>=', $startDate)
            ->whereDate('invoices.date', '<=', $endDate)
            ->whereNull('invoices.deleted_at')->whereNull('details.deleted_at')
            ->selectRaw('details.product_id, SUM(details.quantity) as purchased_quantity, SUM(details.quantity * details.price_per_pices) as purchase_cost')
            ->groupBy('details.product_id')->get()->keyBy('product_id');
        $expensesAmount = (float) DB::table('expenses_details')
            ->whereDate('expenses_date', '>=', $startDate)
            ->whereDate('expenses_date', '<=', $endDate)
            ->whereNull('deleted_at')
            ->sum('expenses_cost');

        $productIds = $sales->keys()->merge($returns->keys())->unique();
        $productDetails = DB::table('products')->whereIn('id', $productIds)
            ->get(['id', 'name', 'cost_price'])->keyBy('id');
        $products = $productIds->map(function ($productId) use ($sales, $returns, $purchaseCosts, $productDetails) {
            $sale = $sales->get($productId);
            $return = $returns->get($productId);
            $purchase = $purchaseCosts->get($productId);
            $product = $productDetails->get($productId);
            $purchasedQuantity = (float) ($purchase->purchased_quantity ?? 0);
            $weightedCost = $purchasedQuantity > 0
                ? (float) $purchase->purchase_cost / $purchasedQuantity
                : (float) ($product->cost_price ?? 0);
            $hasCost = $weightedCost > 0;
            $netQuantity = (float) ($sale->sold_quantity ?? 0) - (float) ($return->returned_quantity ?? 0);
            $netSales = (float) ($sale->sales_amount ?? 0) - (float) ($return->returned_amount ?? 0);
            $costOfGoods = $hasCost ? $netQuantity * $weightedCost : 0;

            return (object) [
                'product_id' => $productId,
                'product_name' => $product->name ?? 'منتج محذوف',
                'sold_quantity' => (float) ($sale->sold_quantity ?? 0),
                'returned_quantity' => (float) ($return->returned_quantity ?? 0),
                'net_sales' => $netSales,
                'unit_cost' => $hasCost ? $weightedCost : null,
                'cost_source' => $purchasedQuantity > 0 ? 'متوسط شراء الفترة' : ($hasCost ? 'التكلفة الحالية' : null),
                'cost_of_goods' => $costOfGoods,
                'gross_profit' => $hasCost ? $netSales - $costOfGoods : null,
                'has_cost' => $hasCost,
            ];
        })->sortByDesc(fn ($product) => $product->gross_profit ?? -INF)->values();

        $costedProducts = $products->where('has_cost', true);
        $costedSales = $costedProducts->sum('net_sales');
        $profit = $costedProducts->sum('gross_profit');
        $stats = (object) [
            'sales_amount' => round($products->sum('net_sales'), 2),
            'costed_sales' => round($costedSales, 2),
            'unpriced_sales' => round($products->where('has_cost', false)->sum('net_sales'), 2),
            'cost_of_goods' => round($costedProducts->sum('cost_of_goods'), 2),
            'gross_profit' => round($profit, 2),
            'expenses_amount' => round($expensesAmount, 2),
            'net_profit' => round($profit - $expensesAmount, 2),
            'gross_margin' => abs($costedSales) > 0.0001 ? round($profit / $costedSales * 100, 2) : 0,
            'product_count' => $products->count(),
            'uncosted_product_count' => $products->where('has_cost', false)->count(),
        ];

        return compact('products', 'stats');
    }

    private function supplierPaymentTotalsByDate(string $startDate, string $endDate): \Illuminate\Support\Collection
    {
        return DB::table('supplier_payments as payments')
            ->join('purchase_invoices as invoices', 'invoices.id', '=', 'payments.purchases_invoice_id')
            ->whereDate('payments.payment_date', '>=', $startDate)
            ->whereDate('payments.payment_date', '<=', $endDate)
            ->whereNull('payments.deleted_at')->whereNull('invoices.deleted_at')
            ->selectRaw('payments.payment_date as payment_date, SUM(payments.amount_paid) as payment_amount')
            ->groupBy('payments.payment_date')->get()->keyBy('payment_date');
    }

    private function customerBalanceSnapshots(string $startDate, string $endDate): \Illuminate\Support\Collection
    {
        $paymentTotals = DB::table('customer_payment_transactions')
            ->whereNull('deleted_at')
            ->select('sales_invoice_id')
            ->selectRaw('SUM(CASE WHEN payment_date < ? THEN amount_paid ELSE 0 END) as paid_before_period', [$startDate])
            ->selectRaw('SUM(CASE WHEN payment_date <= ? THEN amount_paid ELSE 0 END) as paid_by_period_end', [$endDate])
            ->groupBy('sales_invoice_id');
        $returnTotals = DB::table('sales_return_invoices')
            ->whereNull('deleted_at')
            ->select('original_invoice_id')
            ->selectRaw('SUM(CASE WHEN DATE(created_at) < ? THEN total_amount ELSE 0 END) as returned_before_period', [$startDate])
            ->selectRaw('SUM(CASE WHEN DATE(created_at) <= ? THEN total_amount ELSE 0 END) as returned_by_period_end', [$endDate])
            ->groupBy('original_invoice_id');

        return DB::table('sales_invoices as invoices')
            ->leftJoinSub($paymentTotals, 'payments', function ($join) {
                $join->on('payments.sales_invoice_id', '=', 'invoices.id');
            })
            ->leftJoinSub($returnTotals, 'returns', function ($join) {
                $join->on('returns.original_invoice_id', '=', 'invoices.id');
            })
            ->whereNull('invoices.deleted_at')
            ->whereDate('invoices.created_at', '<=', $endDate)
            ->select('invoices.customer_id')
            ->selectRaw('SUM(CASE WHEN DATE(invoices.created_at) < ? THEN GREATEST(COALESCE(invoices.net_total, invoices.total_amount - COALESCE(invoices.deduction, 0)) - COALESCE(invoices.paid_amount, 0) - COALESCE(payments.paid_before_period, 0) - COALESCE(returns.returned_before_period, 0), 0) ELSE 0 END) as opening_balance', [$startDate])
            ->selectRaw('SUM(GREATEST(COALESCE(invoices.net_total, invoices.total_amount - COALESCE(invoices.deduction, 0)) - COALESCE(invoices.paid_amount, 0) - COALESCE(payments.paid_by_period_end, 0) - COALESCE(returns.returned_by_period_end, 0), 0)) as closing_balance')
            ->groupBy('invoices.customer_id')->get()->keyBy('customer_id');
    }

    private function customerCollectionsByCustomer(string $startDate, string $endDate): \Illuminate\Support\Collection
    {
        $invoicePayments = DB::table('sales_invoices')
            ->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)
            ->whereNull('deleted_at')
            ->select('customer_id')
            ->selectRaw('SUM(CASE WHEN COALESCE(paid_amount, 0) > 0 THEN 1 ELSE 0 END) as invoice_count')
            ->selectRaw('SUM(COALESCE(paid_amount, 0)) as invoice_payments')
            ->groupBy('customer_id')->get()->keyBy('customer_id');
        $laterPayments = DB::table('customer_payment_transactions as payments')
            ->join('sales_invoices as invoices', 'invoices.id', '=', 'payments.sales_invoice_id')
            ->whereDate('payments.payment_date', '>=', $startDate)->whereDate('payments.payment_date', '<=', $endDate)
            ->whereNull('payments.deleted_at')->whereNull('invoices.deleted_at')
            ->select('invoices.customer_id')
            ->selectRaw('SUM(payments.amount_paid) as later_payments')
            ->groupBy('invoices.customer_id')->get()->keyBy('customer_id');
        $customerIds = $invoicePayments->keys()->merge($laterPayments->keys())->unique();

        return $customerIds->mapWithKeys(function ($customerId) use ($invoicePayments, $laterPayments) {
            $invoice = $invoicePayments->get($customerId);
            $payment = $laterPayments->get($customerId);
            $initialPayments = (float) ($invoice->invoice_payments ?? 0);
            $laterAmount = (float) ($payment->later_payments ?? 0);

            return [$customerId => (object) [
                'invoice_count' => (int) ($invoice->invoice_count ?? 0),
                'invoice_payments' => $initialPayments,
                'later_payments' => $laterAmount,
                'total_collected' => $initialPayments + $laterAmount,
            ]];
        });
    }

    private function supplierPaymentsBySupplier(string $startDate, string $endDate): \Illuminate\Support\Collection
    {
        return DB::table('supplier_payments as payments')
            ->join('purchase_invoices as invoices', 'invoices.id', '=', 'payments.purchases_invoice_id')
            ->whereDate('payments.payment_date', '>=', $startDate)
            ->whereDate('payments.payment_date', '<=', $endDate)
            ->whereNull('payments.deleted_at')->whereNull('invoices.deleted_at')
            ->selectRaw('invoices.supplier_id, SUM(payments.amount_paid) as payment_amount')
            ->groupBy('invoices.supplier_id')->get()->keyBy('supplier_id');
    }

    private function supplierBalanceSnapshots(string $startDate, string $endDate): \Illuminate\Support\Collection
    {
        $paymentTotals = DB::table('supplier_payments')
            ->whereNull('deleted_at')
            ->select('purchases_invoice_id')
            ->selectRaw('SUM(CASE WHEN payment_date < ? THEN amount_paid ELSE 0 END) as paid_before_period', [$startDate])
            ->selectRaw('SUM(CASE WHEN payment_date <= ? THEN amount_paid ELSE 0 END) as paid_by_period_end', [$endDate])
            ->groupBy('purchases_invoice_id');

        return DB::table('purchase_invoices as invoices')
            ->leftJoinSub($paymentTotals, 'payments', function ($join) {
                $join->on('payments.purchases_invoice_id', '=', 'invoices.id');
            })
            ->whereNull('invoices.deleted_at')
            ->whereDate('invoices.date', '<=', $endDate)
            ->select('invoices.supplier_id')
            ->selectRaw('SUM(CASE WHEN invoices.date < ? THEN GREATEST(invoices.total_amount - COALESCE(invoices.paid_amount, 0) - COALESCE(payments.paid_before_period, 0), 0) ELSE 0 END) as opening_payable', [$startDate])
            ->selectRaw('SUM(GREATEST(invoices.total_amount - COALESCE(invoices.paid_amount, 0) - COALESCE(payments.paid_by_period_end, 0), 0)) as closing_payable')
            ->groupBy('invoices.supplier_id')->get()->keyBy('supplier_id');
    }

    private function validatedPurchasePeriod(Request $request): array
    {
        $validated = $request->validate([
            'startDate' => ['required', 'date_format:Y-m-d'],
            'endDate' => ['required', 'date_format:Y-m-d', 'after_or_equal:startDate', 'before_or_equal:today'],
        ]);

        return [$validated['startDate'], $validated['endDate']];
    }

    /**
     * Reconstruct period balances from today's on-hand quantity and the recorded
     * purchase, sale, and return movements after the selected end date.
     */
    private function inventorySnapshot(string $startDate, string $endDate): array
    {
        $periodPurchases = $this->purchaseQuantities($startDate, $endDate);
        $futurePurchases = $this->purchaseQuantities(null, $endDate);
        $periodSales = $this->salesQuantities($startDate, $endDate);
        $futureSales = $this->salesQuantities(null, $endDate);
        $periodReturns = $this->returnQuantities($startDate, $endDate);
        $futureReturns = $this->returnQuantities(null, $endDate);

        $products = DB::table('products')
            ->leftJoin('category', 'category.category_id', '=', 'products.category')
            ->select('products.id', 'products.name', 'products.quantity', 'category.category_name')
            ->orderBy('products.name')
            ->get()
            ->map(function ($product) use (
                $periodPurchases,
                $futurePurchases,
                $periodSales,
                $futureSales,
                $periodReturns,
                $futureReturns
            ) {
                $id = $product->id;
                $purchased = (float) ($periodPurchases[$id] ?? 0);
                $sold = (float) ($periodSales[$id] ?? 0);
                $returned = (float) ($periodReturns[$id] ?? 0);
                $futureNet = (float) ($futurePurchases[$id] ?? 0)
                    + (float) ($futureReturns[$id] ?? 0)
                    - (float) ($futureSales[$id] ?? 0);
                $closing = (float) ($product->quantity ?? 0) - $futureNet;
                $opening = $closing - $purchased - $returned + $sold;

                return (object) [
                    'product_id' => $id,
                    'product_name' => $product->name,
                    'category_name' => $product->category_name ?: 'بدون تصنيف',
                    'current_quantity' => (float) ($product->quantity ?? 0),
                    'opening_quantity' => $opening,
                    'purchased_quantity' => $purchased,
                    'returned_quantity' => $returned,
                    'received_quantity' => $purchased + $returned,
                    'sold_quantity' => $sold,
                    'closing_quantity' => $closing,
                    'reorder_quantity' => max(0, self::LOW_STOCK_THRESHOLD - $closing),
                ];
            });

        $stats = (object) [
            'product_count' => $products->count(),
            'opening_quantity' => $products->sum('opening_quantity'),
            'purchased_quantity' => $products->sum('purchased_quantity'),
            'returned_quantity' => $products->sum('returned_quantity'),
            'received_quantity' => $products->sum('received_quantity'),
            'sold_quantity' => $products->sum('sold_quantity'),
            'closing_quantity' => $products->sum('closing_quantity'),
            'low_stock_count' => $products->filter(fn ($product) => $product->closing_quantity <= self::LOW_STOCK_THRESHOLD)->count(),
            'out_of_stock_count' => $products->filter(fn ($product) => $product->closing_quantity <= 0)->count(),
        ];

        return [$products, $stats];
    }

    private function purchaseQuantities(?string $startDate, string $endDate): \Illuminate\Support\Collection
    {
        $query = DB::table('purchase_invoices_details as details')
            ->join('purchase_invoices as invoices', 'invoices.id', '=', 'details.purchase_invoices_id')
            ->whereNull('invoices.deleted_at')
            ->whereNull('details.deleted_at');

        $query = $startDate === null
            ? $query->whereDate('invoices.date', '>', $endDate)
            : $query->whereDate('invoices.date', '>=', $startDate)->whereDate('invoices.date', '<=', $endDate);

        return $query->selectRaw('details.product_id, SUM(details.quantity) as quantity')
            ->groupBy('details.product_id')
            ->pluck('quantity', 'product_id');
    }

    private function salesQuantities(?string $startDate, string $endDate): \Illuminate\Support\Collection
    {
        $query = DB::table('sales_invoice_details as details')
            ->join('sales_invoices as invoices', 'invoices.id', '=', 'details.invoice_id')
            ->whereNull('invoices.deleted_at')
            ->whereNull('details.deleted_at');

        $query = $startDate === null
            ? $query->whereDate('invoices.created_at', '>', $endDate)
            : $query->whereDate('invoices.created_at', '>=', $startDate)->whereDate('invoices.created_at', '<=', $endDate);

        return $query->selectRaw('details.product_id, SUM(details.quantity) as quantity')
            ->groupBy('details.product_id')
            ->pluck('quantity', 'product_id');
    }

    private function returnQuantities(?string $startDate, string $endDate): \Illuminate\Support\Collection
    {
        $query = DB::table('sales_return_invoice_details as details')
            ->join('sales_return_invoices as returns', 'returns.id', '=', 'details.return_invoice_id')
            ->whereNull('returns.deleted_at')
            ->whereNull('details.deleted_at');

        $query = $startDate === null
            ? $query->whereDate('returns.created_at', '>', $endDate)
            : $query->whereDate('returns.created_at', '>=', $startDate)->whereDate('returns.created_at', '<=', $endDate);

        return $query->selectRaw('details.product_id, SUM(details.quantity) as quantity')
            ->groupBy('details.product_id')
            ->pluck('quantity', 'product_id');
    }

    private function validatedInventoryPeriod(Request $request): array
    {
        $validated = $request->validate([
            'startDate' => ['required', 'date_format:Y-m-d'],
            'endDate' => ['required', 'date_format:Y-m-d', 'after_or_equal:startDate', 'before_or_equal:today'],
        ]);

        return [$validated['startDate'], $validated['endDate']];
    }

    private function validatedPeriod(Request $request): array
    {
        $validated = $request->validate([
            'startDate' => ['required', 'date_format:Y-m-d'],
            'endDate' => ['required', 'date_format:Y-m-d', 'after_or_equal:startDate'],
        ]);

        return [$validated['startDate'], $validated['endDate']];
    }
}
