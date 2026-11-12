<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $today = Carbon::today();
        $todayDate = $today->toDateString();
        $monthStart = $today->copy()->startOfMonth()->toDateString();

        $todaySales = (float) DB::table('sales_invoices')
            ->whereDate('created_at', $todayDate)->whereNull('deleted_at')
            ->sum(DB::raw('COALESCE(net_total, total_amount - COALESCE(deduction, 0))'));
        $todayReturns = (float) DB::table('sales_return_invoices')
            ->whereDate('created_at', $todayDate)->whereNull('deleted_at')->sum('total_amount');
        $todayInvoiceCount = (int) DB::table('sales_invoices')
            ->whereDate('created_at', $todayDate)->whereNull('deleted_at')->count();

        $monthSales = (float) DB::table('sales_invoices')
            ->whereDate('created_at', '>=', $monthStart)->whereDate('created_at', '<=', $todayDate)
            ->whereNull('deleted_at')
            ->sum(DB::raw('COALESCE(net_total, total_amount - COALESCE(deduction, 0))'));
        $monthReturns = (float) DB::table('sales_return_invoices')
            ->whereDate('created_at', '>=', $monthStart)->whereDate('created_at', '<=', $todayDate)
            ->whereNull('deleted_at')->sum('total_amount');
        $monthPurchases = (float) DB::table('purchase_invoices')
            ->whereDate('date', '>=', $monthStart)->whereDate('date', '<=', $todayDate)
            ->whereNull('deleted_at')->sum('total_amount');
        $monthExpenses = (float) DB::table('expenses_details')
            ->whereDate('expenses_date', '>=', $monthStart)->whereDate('expenses_date', '<=', $todayDate)
            ->whereNull('deleted_at')->sum('expenses_cost');

        $customerPayments = DB::table('customer_payment_transactions')->whereNull('deleted_at')
            ->select('sales_invoice_id')->selectRaw('SUM(amount_paid) as paid_amount')->groupBy('sales_invoice_id');
        $customerReturns = DB::table('sales_return_invoices')->whereNull('deleted_at')
            ->select('original_invoice_id')->selectRaw('SUM(total_amount) as returned_amount')->groupBy('original_invoice_id');
        $customerBalances = DB::table('sales_invoices as invoices')
            ->leftJoinSub($customerPayments, 'payments', function ($join) {
                $join->on('payments.sales_invoice_id', '=', 'invoices.id');
            })
            ->leftJoinSub($customerReturns, 'returns', function ($join) {
                $join->on('returns.original_invoice_id', '=', 'invoices.id');
            })
            ->whereNull('invoices.deleted_at')->where('invoices.payment_method', 'credit')
            ->sum(DB::raw('GREATEST(COALESCE(invoices.net_total, invoices.total_amount - COALESCE(invoices.deduction, 0)) - COALESCE(invoices.paid_amount, 0) - COALESCE(payments.paid_amount, 0) - COALESCE(returns.returned_amount, 0), 0)'));

        $supplierPayments = DB::table('supplier_payments')->whereNull('deleted_at')
            ->select('purchases_invoice_id')->selectRaw('SUM(amount_paid) as paid_amount')->groupBy('purchases_invoice_id');
        $supplierBalances = DB::table('purchase_invoices as invoices')
            ->leftJoinSub($supplierPayments, 'payments', function ($join) {
                $join->on('payments.purchases_invoice_id', '=', 'invoices.id');
            })
            ->whereNull('invoices.deleted_at')
            ->sum(DB::raw('GREATEST(COALESCE(invoices.remaining_amount, invoices.total_amount - COALESCE(invoices.paid_amount, 0)) - COALESCE(payments.paid_amount, 0), 0)'));

        $activeProducts = DB::table('products')->whereNull('deleted_at');
        $productCount = (int) (clone $activeProducts)->count();
        $stockUnits = (float) (clone $activeProducts)->sum('quantity');
        $stockValue = (float) (clone $activeProducts)->sum(DB::raw('COALESCE(quantity, 0) * COALESCE(cost_price, 0)'));
        $lowStockProducts = (clone $activeProducts)->where('quantity', '<=', 5)
            ->orderBy('quantity')->orderBy('name')->limit(6)->get(['id', 'name', 'quantity']);
        $lowStockCount = (int) (clone $activeProducts)->where('quantity', '<=', 5)->count();

        $dailySalesRows = DB::table('sales_invoices')
            ->whereDate('created_at', '>=', $today->copy()->subDays(29)->toDateString())
            ->whereDate('created_at', '<=', $todayDate)->whereNull('deleted_at')
            ->selectRaw('DATE(created_at) as report_date, SUM(COALESCE(net_total, total_amount - COALESCE(deduction, 0))) as sales_amount')
            ->groupBy(DB::raw('DATE(created_at)'))->get()->keyBy('report_date');
        $dailyReturnRows = DB::table('sales_return_invoices')
            ->whereDate('created_at', '>=', $today->copy()->subDays(29)->toDateString())
            ->whereDate('created_at', '<=', $todayDate)->whereNull('deleted_at')
            ->selectRaw('DATE(created_at) as report_date, SUM(total_amount) as returned_amount')
            ->groupBy(DB::raw('DATE(created_at)'))->get()->keyBy('report_date');
        $dailyLabels = [];
        $dailyNetSales = [];
        $dailyReturnAmounts = [];
        for ($day = $today->copy()->subDays(29); $day->lte($today); $day->addDay()) {
            $date = $day->toDateString();
            $dailyLabels[] = $day->format('d/m');
            $dailyNetSales[] = round((float) ($dailySalesRows->get($date)->sales_amount ?? 0)
                - (float) ($dailyReturnRows->get($date)->returned_amount ?? 0), 2);
            $dailyReturnAmounts[] = round((float) ($dailyReturnRows->get($date)->returned_amount ?? 0), 2);
        }

        $returnTotals = DB::table('sales_return_invoices')->whereNull('deleted_at')
            ->select('original_invoice_id')->selectRaw('SUM(total_amount) as returned_amount')->groupBy('original_invoice_id');
        $recentInvoices = DB::table('sales_invoices as invoices')
            ->leftJoin('customers', 'customers.id', '=', 'invoices.customer_id')
            ->leftJoinSub($returnTotals, 'returns', function ($join) {
                $join->on('returns.original_invoice_id', '=', 'invoices.id');
            })
            ->whereNull('invoices.deleted_at')
            ->orderByDesc('invoices.id')->limit(6)
            ->get([
                'invoices.id', 'invoices.created_at', 'invoices.payment_method',
                'invoices.net_total', 'invoices.total_amount', 'invoices.deduction',
                'invoices.paid_amount', 'customers.name as customer_name', 'returns.returned_amount',
            ]);
        $recentExpenses = DB::table('expenses_details as details')
            ->leftJoin('expenses_items as items', 'items.expenses_item_id', '=', 'details.expenses_item_id')
            ->whereNull('details.deleted_at')->orderByDesc('details.expenses_date')->orderByDesc('details.expenses_details_id')
            ->limit(5)->get(['details.expenses_date', 'details.expenses_cost', 'details.expenses_note', 'items.expenses_name']);

        $counts = [
            'customers' => (int) DB::table('customers')->whereNull('deleted_at')->count(),
            'suppliers' => (int) DB::table('suppliers')->whereNull('deleted_at')->count(),
            'warehouses' => (int) DB::table('warehouses')->whereNull('deleted_at')->count(),
            'branches' => (int) DB::table('branches')->whereNull('deleted_at')->count(),
            'users' => (int) DB::table('users')->whereNull('deleted_at')->count(),
            'categories' => (int) DB::table('category')->whereNull('deleted_at')->count(),
        ];

        $dashboardStats = [
            'today_sales' => round($todaySales - $todayReturns, 2),
            'today_gross_sales' => round($todaySales, 2),
            'today_returns' => round($todayReturns, 2),
            'today_invoice_count' => $todayInvoiceCount,
            'month_sales' => round($monthSales - $monthReturns, 2),
            'month_returns' => round($monthReturns, 2),
            'month_purchases' => round($monthPurchases, 2),
            'month_expenses' => round($monthExpenses, 2),
            'customer_balances' => round((float) $customerBalances, 2),
            'supplier_balances' => round((float) $supplierBalances, 2),
            'product_count' => $productCount,
            'stock_units' => round($stockUnits, 2),
            'stock_value' => round($stockValue, 2),
            'low_stock_count' => $lowStockCount,
            'purchase_invoice_count' => (int) DB::table('purchase_invoices')->whereDate('date', '>=', $monthStart)->whereDate('date', '<=', $todayDate)->whereNull('deleted_at')->count(),
        ];

        return view('dashboard', [
            'dashboardStats' => $dashboardStats,
            'counts' => $counts,
            'lowStockProducts' => $lowStockProducts,
            'recentInvoices' => $recentInvoices,
            'recentExpenses' => $recentExpenses,
            'dailyLabels' => $dailyLabels,
            'dailyNetSales' => $dailyNetSales,
            'dailyReturnAmounts' => $dailyReturnAmounts,
            'dashboardDate' => $today->locale('ar')->translatedFormat('l، j F Y'),
            'userName' => Auth::user()->name ?? 'مرحبًا بك',
        ]);
    }
}
