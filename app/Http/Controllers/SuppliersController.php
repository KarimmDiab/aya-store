<?php
namespace App\Http\Controllers;

use App\Models\Suppliers; // Correct model namespace
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\PurchaseInvoiceService;

class SuppliersController extends Controller
{
    public function index()
    {
        $suppliers = DB::table('suppliers')
            ->whereNull('deleted_at')
            ->get();

        $categories = DB::table('category')
            ->whereNull('deleted_at')
            ->get();

        // Fetch supplier categories (supplier_id and category_id)
        $supplierCategories = DB::table('supplier_category_tb')
            ->select('supplier_id', 'category_id')
            ->get();

        // Convert supplier categories to an array for easy lookup
        $selectedCategories = $supplierCategories->groupBy('supplier_id')->map(function ($items) {
            return $items->pluck('category_id')->toArray();
        });

        return view('purchases.suppliers', compact('suppliers', 'categories', 'selectedCategories'));
    }


    public function creditSuppliers()
    {
        $paymentTotals = DB::table('supplier_payments')
            ->whereNull('supplier_payments.deleted_at')
            ->select('supplier_payments.purchases_invoice_id')
            ->selectRaw('COALESCE(SUM(supplier_payments.amount_paid), 0) AS total_paid')
            ->selectRaw('MAX(COALESCE(supplier_payments.updated_at, supplier_payments.created_at)) AS last_payment_update')
            ->groupBy('supplier_payments.purchases_invoice_id');

        $creditInvoiceTotals = DB::table('purchase_invoices')
            ->leftJoinSub($paymentTotals, 'payment_totals', function ($join) {
                $join->on('payment_totals.purchases_invoice_id', '=', 'purchase_invoices.id');
            })
            ->whereNull('purchase_invoices.deleted_at')
            ->where('purchase_invoices.payment_method', 'credit')
            ->select('purchase_invoices.supplier_id')
            ->selectRaw('COUNT(purchase_invoices.id) AS count_invoices')
            ->selectRaw('COALESCE(SUM(purchase_invoices.total_amount), 0) AS total_all_invoices')
            ->selectRaw('COALESCE(SUM(COALESCE(purchase_invoices.paid_amount, 0) + COALESCE(payment_totals.total_paid, 0)), 0) AS total_paid')
            ->selectRaw('COALESCE(SUM(GREATEST(COALESCE(purchase_invoices.remaining_amount, 0) - COALESCE(payment_totals.total_paid, 0), 0)), 0) AS total_remaining')
            ->selectRaw('MAX(purchase_invoices.updated_at) AS last_invoice_update')
            ->selectRaw('MAX(payment_totals.last_payment_update) AS last_payment_update')
            ->groupBy('purchase_invoices.supplier_id');

        $results = DB::table('suppliers')
            ->joinSub($creditInvoiceTotals, 'credit_totals', function ($join) {
                $join->on('credit_totals.supplier_id', '=', 'suppliers.id');
            })
            ->whereNull('suppliers.deleted_at')
            ->select(
                'suppliers.id',
                'suppliers.name',
                'suppliers.contact_info',
                'suppliers.address',
                'credit_totals.count_invoices',
                'credit_totals.total_all_invoices',
                'credit_totals.total_paid',
                'credit_totals.total_remaining',
                DB::raw('GREATEST(COALESCE(credit_totals.last_invoice_update, \'1970-01-01 00:00:00\'), COALESCE(credit_totals.last_payment_update, \'1970-01-01 00:00:00\')) AS last_invoice_activity_at'),
                DB::raw("'credit' AS payment_method")
            )
            ->orderByDesc('credit_totals.total_all_invoices')
            ->get();

        $summary = (object) [
            'total_invoices' => $results->sum('total_all_invoices'),
            'total_paid' => $results->sum('total_paid'),
            'total_remaining' => $results->sum('total_remaining'),
        ];

        return view('purchases.credit_suppliers', compact('results', 'summary'));
    }



    private function allInvoicesForSupplier(int $supplierId)
    {
        $paymentTotals = DB::table('supplier_payments')
            ->whereNull('supplier_payments.deleted_at')
            ->select('supplier_payments.purchases_invoice_id')
            ->selectRaw('COALESCE(SUM(supplier_payments.amount_paid), 0) AS total_amount_paid')
            ->groupBy('supplier_payments.purchases_invoice_id');

        return DB::table('purchase_invoices')
            ->join('users', 'purchase_invoices.user_id', '=', 'users.id')
            ->leftJoinSub($paymentTotals, 'payment_totals', function ($join) {
                $join->on('payment_totals.purchases_invoice_id', '=', 'purchase_invoices.id');
            })
            ->whereNull('purchase_invoices.deleted_at')
            ->where('purchase_invoices.supplier_id', $supplierId)
            ->select(
                'purchase_invoices.*',
                'users.id AS user_id',
                'users.name AS user_name',
                DB::raw('COALESCE(payment_totals.total_amount_paid, 0) AS later_payments_amount'),
                DB::raw('COALESCE(purchase_invoices.paid_amount, 0) + COALESCE(payment_totals.total_amount_paid, 0) AS total_paid_amount'),
                DB::raw('GREATEST(COALESCE(purchase_invoices.remaining_amount, 0) - COALESCE(payment_totals.total_amount_paid, 0), 0) AS payment_remaning_amount')
            )
            ->orderByDesc('purchase_invoices.date')
            ->orderByDesc('purchase_invoices.id')
            ->get();
    }

    private function creditInvoicesForSupplier(int $supplierId)
    {
        $paymentTotals = DB::table('supplier_payments')
            ->whereNull('supplier_payments.deleted_at')
            ->select('supplier_payments.purchases_invoice_id')
            ->selectRaw('COALESCE(SUM(supplier_payments.amount_paid), 0) AS total_amount_paid')
            ->groupBy('supplier_payments.purchases_invoice_id');

        return DB::table('purchase_invoices')
            ->join('users', 'purchase_invoices.user_id', '=', 'users.id')
            ->leftJoinSub($paymentTotals, 'payment_totals', function ($join) {
                $join->on('payment_totals.purchases_invoice_id', '=', 'purchase_invoices.id');
            })
            ->whereNull('purchase_invoices.deleted_at')
            ->where('purchase_invoices.supplier_id', $supplierId)
            ->where('purchase_invoices.payment_method', 'credit')
            ->select(
                'purchase_invoices.*',
                'purchase_invoices.id AS purchase_invoices_id',
                'users.id AS user_id',
                'users.name AS user_name',
                DB::raw('COALESCE(payment_totals.total_amount_paid, 0) AS total_amount_paid'),
                DB::raw('COALESCE(purchase_invoices.paid_amount, 0) + COALESCE(payment_totals.total_amount_paid, 0) AS total_paid_amount'),
                DB::raw('GREATEST(COALESCE(purchase_invoices.remaining_amount, 0) - COALESCE(payment_totals.total_amount_paid, 0), 0) AS payment_remaning_amount')
            )
            ->orderByDesc('purchase_invoices.date')
            ->orderByDesc('purchase_invoices.id')
            ->get();
    }

    public function printInvoice($invoice_no, $supplier_id)
    {

        //fetch all suppliers data
        $suppliersInfo = DB::table('suppliers')->where('id', $supplier_id)->get();

        // fetch purchase invoice inner join suppliersInfo
        $purchaseInvoice = DB::table('purchase_invoices')
            ->join('suppliers', 'purchase_invoices.supplier_id', '=', 'suppliers.id')
            ->where('purchase_invoices.id', $invoice_no)
            ->where('purchase_invoices.supplier_id', $supplier_id)
            ->select('purchase_invoices.*', 'suppliers.*')
            ->first(); // or ->get() if expecting multiple results

        // fetch purchase invoice details related to purchase invoice
        $invoiceDetails = DB::table('purchase_invoices_details')
            ->join('purchase_invoices', 'purchase_invoices_details.purchase_invoices_id', '=', 'purchase_invoices.id')
            ->join('products', 'purchase_invoices_details.product_id', '=', 'products.id')
            ->join('users', 'purchase_invoices.user_id', '=', 'users.id')

            ->where('purchase_invoices_details.purchase_invoices_id', $invoice_no)
            ->where('purchase_invoices.supplier_id', $supplier_id)
            ->select([
                'products.name as product_name',
                'purchase_invoices_details.quantity as quantity_of_purchase',
                'purchase_invoices_details.price_per_pices as price_per_pices',
                DB::raw('(purchase_invoices_details.quantity * purchase_invoices_details.price_per_pices) as total_of_product'),
                'purchase_invoices.total_amount as total_amount',
                'purchase_invoices.paid_amount as paid_amount',
                'purchase_invoices.remaining_amount as remaining_amount',
                'users.name as user_name'
            ])
            ->get();

        // Return a view for printing the invoice
        return view('purchases.print_purchase_invoice', compact('suppliersInfo', 'purchaseInvoice', 'invoiceDetails'));
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Return your form view here
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|regex:/^[\pL\d\s\-\(\)]+$/u',  // Allows letters, numbers, spaces, hyphens, and parentheses
            'address' => 'required|regex:/^[\pL\d\s\-\(\)]+$/u', // Allows letters, numbers, spaces, hyphens, and parentheses
            'contact_info' => 'required|regex:/^01[0-9]{9}$/', // Starts with 01, followed by 9 digits
            'supplier_category' => 'required|array', // Ensure categories are provided
            'supplier_category.*' => 'exists:category,category_id' // Ensure each category exists
        ], [
            'name.required' => 'يجب إدخال الاسم.',
            'name.regex' => 'يجب أن يحتوي الاسم على حروف، أرقام، مسافات، شرطة (-)، أو أقواس () فقط.',
            'address.required' => 'يجب إدخال العنوان.',
            'address.regex' => 'يجب أن يحتوي العنوان على حروف، أرقام، مسافات، شرطة (-)، أو أقواس () فقط.',
            'contact_info.required' => 'يجب إدخال معلومات الاتصال.',
            'contact_info.regex' => 'يجب أن يكون رقم الهاتف صالحًا ويبدأ بـ 01 ويتكون من 11 رقمًا.',
            'supplier_category.required' => 'يجب اختيار تصنيف المورد.',
            'supplier_category.*.exists' => 'التصنيف المحدد غير صالح.',
        ]);


        // Insert the supplier into the database and get the supplier ID
        $supplier_id = DB::table('suppliers')->insertGetId([
            'name' => $request->name,
            'address' => $request->address,
            'contact_info' => $request->contact_info,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert selected categories into supplier_category_tb
        foreach ($request->supplier_category as $category_id) {
            DB::table('supplier_category_tb')->insert([
                'supplier_id' => $supplier_id,
                'category_id' => $category_id,
            ]);
        }

        return redirect()->back()->with('success', 'تم إضافة المورد بنجاح');
    }


    /**
     * Display the specified resource.
     */

    public function show($supplier_id)
    {
        // Decrypt the supplier ID
        $supplierId = decrypt($supplier_id);
        // $viewType = decrypt($viewType);


        // Fetch the specific supplier
        $suppliers = DB::table('suppliers')
            ->whereNull('deleted_at')
            ->where('id', $supplierId)
            ->first();

        //get all informations for the specific supplier
        $suppliersInfo = DB::table('suppliers')
            ->join('purchase_invoices', 'suppliers.id', '=', 'purchase_invoices.supplier_id')
            ->select(
                'suppliers.id',
                'suppliers.name AS supplier_name', // Rename to avoid confusion
                'suppliers.contact_info',
                'suppliers.address',
                DB::raw('COUNT(purchase_invoices.id) AS number_of_invoice'),
                DB::raw('SUM(purchase_invoices.total_amount) AS total_of_all_invoices'),
                DB::raw("SUM(CASE WHEN purchase_invoices.payment_method = 'credit' THEN 1 ELSE 0 END) AS number_of_credit_invoices")
            )
            ->whereNull('suppliers.deleted_at')
            ->whereNull('purchase_invoices.deleted_at')
            ->where('suppliers.id', $supplierId)
            ->groupBy('suppliers.id', 'suppliers.name', 'suppliers.contact_info', 'suppliers.address')
            ->first(); // Use first() instead of get()

        // Fetch credit invoices for this supplier
        $creditSuppliers = DB::table('suppliers')
            ->leftJoin('purchase_invoices', 'suppliers.id', '=', 'purchase_invoices.supplier_id')
            ->select(
                'suppliers.id',
                'suppliers.name',
                'suppliers.contact_info',
                'suppliers.address',
                'suppliers.updated_at',
                DB::raw('COUNT(purchase_invoices.id) AS count_invoices'),
                DB::raw('COALESCE(SUM(purchase_invoices.total_amount), 0) AS total_all_invoices'),
                DB::raw('ANY_VALUE(purchase_invoices.payment_method) AS payment_method')
            )
            ->whereNull('suppliers.deleted_at')
            ->whereNull('purchase_invoices.deleted_at')
            ->where('purchase_invoices.payment_method', 'credit')
            ->where('suppliers.id', $supplierId)
            ->groupBy(
                'suppliers.id',
                'suppliers.name',
                'suppliers.contact_info',
                'suppliers.address',
                'suppliers.updated_at'
            )
            ->get();

        // for first tab for all  invoices
        $supplierPurchaseInvoice = $this->allInvoicesForSupplier($supplierId);

        // for details table of credit invoices in first tab for all credit invoices
        $purchaseDetails = DB::table('suppliers')
            ->join('purchase_invoices', 'suppliers.id', '=', 'purchase_invoices.supplier_id')
            ->join('purchase_invoices_details', 'purchase_invoices.id', '=', 'purchase_invoices_details.purchase_invoices_id')
            ->join('products', 'purchase_invoices_details.product_id', '=', 'products.id')
            ->join('users', 'purchase_invoices.user_id', '=', 'users.id')
            ->select([
                'suppliers.id AS suppliers_id',
                'suppliers.name',
                'suppliers.contact_info',
                'suppliers.address',
                'purchase_invoices.id AS purchase_invoices_id',
                'purchase_invoices.total_amount',
                'purchase_invoices.date',
                'purchase_invoices.paid_amount',
                'purchase_invoices.remaining_amount',
                'purchase_invoices.payment_method',
                'users.name AS username',
                'purchase_invoices.created_at AS purchase_invoices_created_at',
                'purchase_invoices_details.id AS purchase_invoices_details_id',
                'products.id AS product_id',
                'products.price AS product_price',
                'products.name AS product_name',
                'purchase_invoices_details.quantity',
                'purchase_invoices_details.price_per_pices',
                DB::raw('purchase_invoices_details.quantity * purchase_invoices_details.price_per_pices AS total_price')
            ])
            ->whereNull('suppliers.deleted_at')
            ->whereNull('purchase_invoices.deleted_at')
            ->whereNull('purchase_invoices_details.deleted_at')
            ->whereNull('products.deleted_at')
            ->where('suppliers.id', $supplierId)
            ->get(); // Change from first() to get()

        // for second tab for all credit invoices only
        $creditInvoiceNumbers = $this->creditInvoicesForSupplier($supplierId);


        // for details table of credit invoices in second tab for all credit invoices
        $creditDetails = DB::table('suppliers')
            ->join('purchase_invoices', 'suppliers.id', '=', 'purchase_invoices.supplier_id')
            ->join('purchase_invoices_details', 'purchase_invoices.id', '=', 'purchase_invoices_details.purchase_invoices_id')
            ->join('products', 'purchase_invoices_details.product_id', '=', 'products.id')
            ->join('users', 'purchase_invoices.user_id', '=', 'users.id')
            ->select([
                'suppliers.id AS suppliers_id',
                'suppliers.name',
                'suppliers.contact_info',
                'suppliers.address',
                'purchase_invoices.id AS purchase_invoices_id',
                'purchase_invoices.total_amount',
                'purchase_invoices.date',
                'purchase_invoices.paid_amount',
                'purchase_invoices.remaining_amount',
                'purchase_invoices.payment_method',
                'users.name AS username',
                'purchase_invoices.created_at AS purchase_invoices_created_at',
                'purchase_invoices_details.id AS purchase_invoices_details_id',
                'products.id AS product_id',
                'products.price AS product_price',
                'products.name AS product_name',
                'purchase_invoices_details.quantity',
                'purchase_invoices_details.price_per_pices',
                DB::raw('purchase_invoices_details.quantity * purchase_invoices_details.price_per_pices AS total_price')
            ])
            ->whereNull('suppliers.deleted_at')
            ->whereNull('purchase_invoices.deleted_at')
            ->whereNull('purchase_invoices_details.deleted_at')
            ->whereNull('products.deleted_at')
            ->where('suppliers.id', $supplierId)
            ->get(); // Change from first() to get()

        //get tarnsactions payment for details credit invoice in second tab
        $transaction = DB::table('supplier_payments')
            ->join('users', 'supplier_payments.user_id', '=', 'users.id') // ربط جدول المستخدمين
            ->join('purchase_invoices', 'supplier_payments.purchases_invoice_id', '=', 'purchase_invoices.id')
            ->whereNull('supplier_payments.deleted_at')
            ->whereNull('purchase_invoices.deleted_at')
            ->where('supplier_payments.supplier_id', $supplierId)
            ->get();


        // تحديد الـ View بناءً على المتغير viewType
        //$viewName = ($viewType == 'all_suppliers') ? 'purchases.suppliers' : 'purchases.suppliers_profile';

        return view('purchases.suppliers_profile', compact(
            'suppliers',
            'creditSuppliers',
            'purchaseDetails',
            'creditInvoiceNumbers',
            'suppliersInfo',
            'transaction',
            'supplierPurchaseInvoice',
            'creditDetails'
        ));

    }




    public function showForAllSuppliers($supplier_id)
    {
        // Decrypt the supplier ID
        $supplierId = decrypt($supplier_id);
        // $viewType = decrypt($viewType);


        // Fetch the specific supplier
        $suppliers = DB::table('suppliers')
            ->whereNull('deleted_at')
            ->where('id', $supplierId)
            ->first(); // Use first() to get a single record


        //get all informations for the specific supplier
        $suppliersInfo = DB::table('suppliers')
            ->join('purchase_invoices', 'suppliers.id', '=', 'purchase_invoices.supplier_id')
            ->select(
                'suppliers.id',
                'suppliers.name AS supplier_name', // إعادة التسمية لتجنب الالتباس
                'suppliers.contact_info',
                'suppliers.address',
                DB::raw('COUNT(purchase_invoices.id) AS number_of_invoice'),
                DB::raw('SUM(purchase_invoices.total_amount) AS total_of_all_invoices'),
                DB::raw("COUNT(CASE WHEN purchase_invoices.payment_method = 'credit' THEN 1 END) AS number_of_credit_invoices")
            )
            ->whereNull('suppliers.deleted_at')
            ->whereNull('purchase_invoices.deleted_at')
            ->where('suppliers.id', $supplierId)
            ->groupBy('suppliers.id', 'suppliers.name', 'suppliers.contact_info', 'suppliers.address')
            ->first();
        // Use first() instead of get()


        // Fetch credit invoices for this supplier
        $creditSuppliers = DB::table('suppliers')
            ->leftJoin('purchase_invoices', 'suppliers.id', '=', 'purchase_invoices.supplier_id')
            ->select(
                'suppliers.id',
                'suppliers.name',
                'suppliers.contact_info',
                'suppliers.address',
                'suppliers.updated_at',
                DB::raw('COUNT(purchase_invoices.id) AS count_invoices'),
                DB::raw('COALESCE(SUM(purchase_invoices.total_amount), 0) AS total_all_invoices'),
                DB::raw('ANY_VALUE(purchase_invoices.payment_method) AS payment_method')
            )
            ->whereNull('suppliers.deleted_at')
            ->whereNull('purchase_invoices.deleted_at')
            ->where('purchase_invoices.payment_method', 'credit')
            ->where('suppliers.id', $supplierId)
            ->groupBy(
                'suppliers.id',
                'suppliers.name',
                'suppliers.contact_info',
                'suppliers.address',
                'suppliers.updated_at'
            )
            ->get();

        // for first tab for all  invoices
        $supplierPurchaseInvoice = $this->allInvoicesForSupplier($supplierId);

        // for details table of credit invoices in first tab for all credit invoices
        $purchaseDetails = DB::table('suppliers')
            ->join('purchase_invoices', 'suppliers.id', '=', 'purchase_invoices.supplier_id')
            ->join('purchase_invoices_details', 'purchase_invoices.id', '=', 'purchase_invoices_details.purchase_invoices_id')
            ->join('products', 'purchase_invoices_details.product_id', '=', 'products.id')
            ->join('users', 'purchase_invoices.user_id', '=', 'users.id')
            ->select([
                'suppliers.id AS suppliers_id',
                'suppliers.name',
                'suppliers.contact_info',
                'suppliers.address',
                'purchase_invoices.id AS purchase_invoices_id',
                'purchase_invoices.total_amount',
                'purchase_invoices.date',
                'purchase_invoices.paid_amount',
                'purchase_invoices.remaining_amount',
                'purchase_invoices.payment_method',
                'users.name AS username',
                'purchase_invoices.created_at AS purchase_invoices_created_at',
                'purchase_invoices_details.id AS purchase_invoices_details_id',
                'products.id AS product_id',
                'products.price AS product_price',
                'products.name AS product_name',
                'purchase_invoices_details.quantity',
                'purchase_invoices_details.price_per_pices',
                DB::raw('purchase_invoices_details.quantity * purchase_invoices_details.price_per_pices AS total_price')
            ])
            ->whereNull('suppliers.deleted_at')
            ->whereNull('purchase_invoices.deleted_at')
            ->whereNull('purchase_invoices_details.deleted_at')
            ->whereNull('products.deleted_at')
            ->where('suppliers.id', $supplierId)
            ->get(); // Change from first() to get()

        // for second tab for all credit invoices only
        $creditInvoiceNumbers = $this->creditInvoicesForSupplier($supplierId);

        // for details table of credit invoices in second tab for all credit invoices
        $creditDetails = DB::table('suppliers')
            ->join('purchase_invoices', 'suppliers.id', '=', 'purchase_invoices.supplier_id')
            ->join('purchase_invoices_details', 'purchase_invoices.id', '=', 'purchase_invoices_details.purchase_invoices_id')
            ->join('products', 'purchase_invoices_details.product_id', '=', 'products.id')
            ->join('users', 'purchase_invoices.user_id', '=', 'users.id')
            ->select([
                'suppliers.id AS suppliers_id',
                'suppliers.name',
                'suppliers.contact_info',
                'suppliers.address',
                'purchase_invoices.id AS purchase_invoices_id',
                'purchase_invoices.total_amount',
                'purchase_invoices.date',
                'purchase_invoices.paid_amount',
                'purchase_invoices.remaining_amount',
                'purchase_invoices.payment_method',
                'users.name AS username',
                'purchase_invoices.created_at AS purchase_invoices_created_at',
                'purchase_invoices_details.id AS purchase_invoices_details_id',
                'products.id AS product_id',
                'products.price AS product_price',
                'products.name AS product_name',
                'purchase_invoices_details.quantity',
                'purchase_invoices_details.price_per_pices',
                DB::raw('purchase_invoices_details.quantity * purchase_invoices_details.price_per_pices AS total_price')
            ])
            ->whereNull('suppliers.deleted_at')
            ->whereNull('purchase_invoices.deleted_at')
            ->whereNull('purchase_invoices_details.deleted_at')
            ->whereNull('products.deleted_at')
            ->where('suppliers.id', $supplierId)
            ->get(); // Change from first() to get()

        //get tarnsactions payment for details credit invoice in second tab
        $transaction = DB::table('supplier_payments')
            ->join('users', 'supplier_payments.user_id', '=', 'users.id') // ربط جدول المستخدمين
            ->join('purchase_invoices', 'supplier_payments.purchases_invoice_id', '=', 'purchase_invoices.id')
            ->whereNull('supplier_payments.deleted_at')
            ->whereNull('purchase_invoices.deleted_at')
            ->where('supplier_payments.supplier_id', $supplierId)
            ->get();

        return view('purchases.suppliers_profile', compact(
            'suppliers',
            'creditSuppliers',
            'purchaseDetails',
            'creditInvoiceNumbers',
            'suppliersInfo',
            'transaction',
            'supplierPurchaseInvoice',
            'creditDetails'
        ));

    }




    public function supplierPaymentStore(Request $request, $purchase_id, $supplier_id)
    {
        $data = $request->validate([
            'payment_date' => 'required|date',
            'amount_paid' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:255',
        ]);

        app(PurchaseInvoiceService::class)->addPayment(
            (string) $purchase_id,
            (int) $supplier_id,
            $data,
            (int) auth()->id()
        );

        return redirect()->back()->with('success', 'تم تسجيل حركة السداد وتحديث الرصيد بنجاح.');
    }

    public function updateSupplierPayment(Request $request, $supplierID, $invoice_id, $transactionId)
    {
        $data = $request->validate([
            'payment_date' => 'required|date',
            'amount_paid' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:255',
        ]);

        app(PurchaseInvoiceService::class)->updatePayment(
            (string) $invoice_id,
            (int) $supplierID,
            (int) $transactionId,
            $data
        );

        return redirect()->back()->with('success', 'تم تحديث حركة السداد وتحديث أرصدة الحركات بنجاح.');
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(suppliers $suppliers)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|regex:/^[\pL\d\s]+$/u',  // Letters, numbers, and spaces only
            'address' => 'required|regex:/^[\pL\d\s]+$/u', // Letters, numbers, and spaces only
            'contact_info' => 'required|regex:/^01[0-9]{9}$/', // Starts with 01, followed by 9 digits
            'supplier_category' => 'nullable|array',
            'supplier_category.*' => 'integer|exists:category,category_id',
        ], [
            'name.required' => 'يجب إدخال الاسم.',
            'name.regex' => 'يجب أن يحتوي الاسم على حروف، أرقام ومسافات فقط.',
            'address.required' => 'يجب إدخال العنوان.',
            'address.regex' => 'يجب أن يحتوي العنوان على حروف، أرقام ومسافات فقط.',
            'contact_info.required' => 'يجب إدخال معلومات الاتصال.',
            'contact_info.regex' => 'يجب أن يكون رقم الهاتف صالحًا ويبدأ بـ 01 ويتكون من 11 رقمًا.',
            'supplier_category.required' => 'يجب اختيار تصنيف واحد على الأقل للمورد.',
        ]);

        DB::transaction(function () use ($request, $id) {
            DB::table('suppliers')
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->update([
                    'name' => $request->name,
                    'address' => $request->address,
                    'contact_info' => $request->contact_info,
                    'updated_at' => now(),
                ]);

            // Forms that do not edit category assignments leave them untouched.
            if ($request->has('supplier_category')) {
                DB::table('supplier_category_tb')->where('supplier_id', $id)->delete();
                $supplierCategories = collect($request->input('supplier_category', []))
                    ->map(fn ($categoryId) => ['supplier_id' => $id, 'category_id' => $categoryId])
                    ->all();
                if ($supplierCategories) {
                    DB::table('supplier_category_tb')->insert($supplierCategories);
                }
            }
        });

        return redirect()->back()->with('success', 'تم تعديل بيانات المورد بنجاح');
    }


    // Delete a supplier
    public function destroy($id)
    {
        $hasInvoices = DB::table('purchase_invoices')
            ->where('supplier_id', $id)
            ->whereNull('deleted_at')
            ->exists();
        if ($hasInvoices) {
            return redirect()->back()->with('error', 'لا يمكن حذف مورد لديه فواتير مشتريات مسجلة.');
        }

        if (!is_array($id)) {
            $id = [$id];
        }
        DB::table('suppliers')
            ->whereIn('id', $id)
            ->update(['deleted_at' => now()]);
        ;

        return redirect()->back()->with('success', 'تم حذف المورد بنجاح');
    }
}
