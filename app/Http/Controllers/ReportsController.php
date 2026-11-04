<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = DB::table("products")
            ->whereNull(columns: "deleted_at")
            ->get();
        $branches = DB::table("branches")
            ->whereNull(columns: "deleted_at")
            ->get();
        $customers = DB::table("customers")
            ->whereNull(columns: "deleted_at")
            ->get();
        $users = DB::table("users")
            ->whereNull(columns: "deleted_at")
            ->get();

        return view(
            "reports.reports_main_page",
            compact("products", "branches", "customers", "users")
        );
    }

    public function salesPerPeriod(Request $request)
    {
        // Validate Request
        $request->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ]);
    
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
    
        $total_sales = DB::table('products as p')
            ->leftJoin('sales_invoice_details as s', 'p.id', '=', 's.product_id')
            ->select([
                'p.id as product_id',
                'p.name as product_name',
                DB::raw('SUM(COALESCE(s.quantity, 0)) as total_sold_quantity'),
                DB::raw('ROUND(COALESCE(AVG(s.unit_price), 0), 2) as avg_selling_price'),
                DB::raw('COALESCE(SUM(s.total_amount), 0) as total_sales'),
                DB::raw('(
                    SELECT ROUND(COALESCE(
                        SUM(pi.quantity * pi.price_per_pices) / NULLIF(SUM(pi.quantity), 0), 0), 2)
                    FROM purchase_invoices_details pi 
                    WHERE pi.product_id = p.id
                    LIMIT 1
                ) AS avg_buying_price'),
                DB::raw('(
                    SELECT ROUND(COALESCE(
                        SUM(s.total_amount) - (SUM(s.quantity) * 
                            COALESCE(
                                (SELECT SUM(pi.quantity * pi.price_per_pices) / NULLIF(SUM(pi.quantity), 0) 
                                FROM purchase_invoices_details pi 
                                WHERE pi.product_id = p.id
                                LIMIT 1), 0)
                        ), 0), 2)
                ) AS net_profit')
            ])
            ->whereBetween('s.created_at', [$startDate, $endDate])
            ->whereNull('p.deleted_at')
            ->groupBy('p.id', 'p.name')
            ->get();
    
        return view("reports.sub_reports.sales_per_product", compact("total_sales"  , "startDate" , "endDate"));
    }


    public function totalSales(Request $request)
    {
        // Validate Request
        $request->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ]);
    
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
    
        $total_sales = DB::table('sales_invoices')
            ->selectRaw('DATE(created_at) as invoice_date, COUNT(id) as number_of_invoices, SUM(total_amount) as total_amount_of_invoices')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->groupBy(DB::raw('DATE(created_at)'))
        ->get();

        $reportStatics = DB::table('sales_invoices')
            ->selectRaw('COUNT(id) AS number_of_invoices, SUM(total_amount) AS total_amount_of_invoices, AVG(total_amount) AS avg_amount_of_invoices')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
        ->first(); // Fetch a single object instead of a collection
    
    
        return view("reports.sub_reports.total_sales", compact("total_sales"  , "startDate" , "endDate" , "reportStatics"));
    }
    
    
    public function salesPerCustomer(Request $request)
    {
        // Validate Request
        $request->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ]);
    
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        
        $customers = DB::table('customers')
            ->select([
                'customers.id as customer_id',
                'customers.name as customer_name',
                'customers.contact_info as customer_phone',
                DB::raw('COUNT(sales_invoices.id) as invoices_number'),
                DB::raw('SUM(sales_invoices.net_total) as total_amount'),
                DB::raw('MAX(sales_invoices.payment_method) as payment_method'),
                DB::raw('(SELECT cpt.transaction_remaining_amount 
                          FROM customer_payment_transactions cpt 
                          WHERE cpt.customer_id = customers.id 
                          ORDER BY cpt.id DESC 
                          LIMIT 1) as last_transaction_remaining_amount')
            ])
            ->join('sales_invoices', 'customers.id', '=', 'sales_invoices.customer_id')
            ->whereBetween('sales_invoices.created_at', [$startDate, $endDate]) // تصفية بين تاريخين
            ->whereNull('sales_invoices.deleted_at')
            ->whereNull('customers.deleted_at')
            ->groupBy('customers.id')
            ->get();
        
        return view("reports.sub_reports.sales_per_customer", compact("customers" , "startDate" , "endDate"));
    }
    
        
    public function salesPerUser (Request $request)
    {
        // Validate Request
        $request->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ]);
    
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        
        $salesReportUser = DB::table('users')
        ->select(
            'users.id as user_id',
            'users.name as user_name',
            'users.mobile_number as user_phone',
            'users.email as user_email',
            'users.gender as user_gender',
            DB::raw('COUNT(sales_invoices.id) as count_sales_invoice'),
            DB::raw('SUM(sales_invoices.net_total) as sum_invoices_amount')
        )
        ->join('sales_invoices', 'users.id', '=', 'sales_invoices.user_id')
        ->whereNull('users.deleted_at')
        ->whereNull('sales_invoices.deleted_at')
        ->groupBy('users.id')
        ->get();
        
        return view("reports.sub_reports.sales_per_user", compact("salesReportUser" , "startDate" , "endDate"));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }




    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
