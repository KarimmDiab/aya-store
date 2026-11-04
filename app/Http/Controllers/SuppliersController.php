<?php
namespace App\Http\Controllers;

use App\Models\Suppliers; // Correct model namespace
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $results = DB::table('suppliers')
            ->leftJoin('purchase_invoices', 'suppliers.id', '=', 'purchase_invoices.supplier_id')
            ->select(
                'suppliers.id',
                'suppliers.name',
                'suppliers.contact_info',
                'suppliers.address',
                'suppliers.updated_at',
                DB::raw('ANY_VALUE(purchase_invoices.payment_method) AS payment_method'),
                DB::raw('count(purchase_invoices.id) AS count_invoices'),
                DB::raw('sum(purchase_invoices.total_amount) AS total_all_invoices')
            )
            ->whereNull('suppliers.deleted_at')
            ->whereNull('purchase_invoices.deleted_at')
            ->where('purchase_invoices.payment_method', '=', 'credit')
            ->groupBy(
                'suppliers.id',
                'suppliers.name',
                'suppliers.contact_info',
                'suppliers.address'
            )
            ->get();

        return view('purchases.credit_suppliers', compact('results'));
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
            ->get();

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
        $supplierPurchaseInvoice = DB::table('purchase_invoices')
            ->join('users', 'purchase_invoices.user_id', '=', 'users.id') // Joining users table
            ->select(
                'purchase_invoices.*',
                'users.id AS user_id',
                'users.name AS user_name'
            )
            ->whereNull('purchase_invoices.deleted_at')
            ->where('supplier_id', $supplierId) // Filter by supplier ID
            ->get();

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
        $creditInvoiceNumbers = DB::table('purchase_invoices')
            ->join('users', 'purchase_invoices.user_id', '=', 'users.id') // ربط جدول المستخدمين
            ->leftJoin('supplier_payments', 'purchase_invoices.id', '=', 'supplier_payments.purchases_invoice_id') // ربط المدفوعات
            ->select(
                'purchase_invoices.*',
                'purchase_invoices.id AS purchase_invoices_id',
                'users.id AS user_id',
                'users.name AS user_name',
                'supplier_payment.payment_remaning_amount AS payment_remaning_amount',
                DB::raw('COALESCE(SUM(supplier_payments.amount_paid), 0) AS total_amount_paid') // حساب مجموع المدفوعات
            )
            ->whereNull('purchase_invoices.deleted_at')
            ->where('purchase_invoices.supplier_id', $supplierId) // تصفية حسب المورّد
            ->where('purchase_invoices.payment_method', 'credit')
            ->groupBy('purchase_invoices.id', 'users.id', 'users.name') // تجميع حسب الفاتورة والمستخدمين
            ->get();


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
            ->where('purchase_invoices.payment_method')
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
        $supplierPurchaseInvoice = DB::table('purchase_invoices')
            ->join('users', 'purchase_invoices.user_id', '=', 'users.id') // Joining users table
            ->select(
                'purchase_invoices.*',
                'users.id AS user_id',
                'users.name AS user_name'
            )
            ->whereNull('purchase_invoices.deleted_at')
            ->where('supplier_id', $supplierId) // Filter by supplier ID
            ->get();

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
$creditInvoiceNumbers = DB::table('purchase_invoices')
    ->join('users', 'purchase_invoices.user_id', '=', 'users.id')
    ->leftJoin('supplier_payments', 'purchase_invoices.id', '=', 'supplier_payments.purchases_invoice_id')
    ->select(
        'purchase_invoices.*',
        'purchase_invoices.id AS purchase_invoices_id',
        'users.id AS user_id',
        'users.name AS user_name',
        DB::raw('
            purchase_invoices.remaining_amount - COALESCE(SUM(supplier_payments.amount_paid), 0)
            AS payment_remaning_amount
        '),
        DB::raw('COALESCE(SUM(supplier_payments.amount_paid), 0) AS total_amount_paid')
    )
    ->whereNull('purchase_invoices.deleted_at')
    ->where('purchase_invoices.supplier_id', $supplierId)
    ->where('purchase_invoices.payment_method', 'credit')
    ->groupBy(
        'purchase_invoices.id',
        'purchase_invoices.remaining_amount', // مهم عشان تستخدمه في الحساب
        'users.id',
        'users.name'
    )
    ->get();

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

        $supplierCategories = DB::table('supplier_category_tb')
            ->leftJoin('suppliers', 'supplier_category_tb.supplier_id', '=', 'suppliers.id')
            ->leftJoin('category', 'supplier_category_tb.category_id', '=', 'category.category_id')
            ->select(
                'supplier_category_tb.supplier_id',
                'suppliers.name',
                'supplier_category_tb.category_id',
                'category.category_name'
            )
            ->where('supplier_category_tb.supplier_id', $supplierId) // Change 20 to a dynamic supplier ID
            ->get();

        $products = DB::table('products')
            ->whereNull('deleted_at')
            ->get();


        return view('purchases.suppliers_profile', compact(
            'suppliers',
            'creditSuppliers',
            'purchaseDetails',
            'creditInvoiceNumbers',
            'suppliersInfo',
            'transaction',
            'supplierPurchaseInvoice',
            'creditDetails',
            'supplierCategories',
            'products'
        ));

    }




    public function supplierPaymentStore(Request $request, $purchase_id, $supplier_id)
    {
        // Validation rules with custom messages
        $request->validate([
            'payment_date' => 'required|date',
            'amount_paid' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:255',
        ], [
            'payment_date.required' => 'يجب إدخال تاريخ السداد.',
            'payment_date.date' => 'يجب أن يكون تاريخ السداد تاريخًا صحيحًا.',
            'amount_paid.required' => 'يجب إدخال المبلغ المدفوع.',
            'amount_paid.numeric' => 'يجب أن يكون المبلغ المدفوع رقمًا صالحًا.',
            'amount_paid.min' => 'يجب أن يكون المبلغ المدفوع أكبر من 0.',
            'notes.string' => 'يجب أن تكون الملاحظات نصًا فقط.',
            'notes.max' => 'يجب ألا تتجاوز الملاحظات 255 حرفًا.',
        ]);

        DB::beginTransaction();
        try {
            // Fetch the current remaining amount from purchase_invoices
            $currentRemaining = DB::table('purchase_invoices')
                ->where('id', $purchase_id)
                ->value('remaining_amount');

            // Insert the new payment into supplier_payments
            DB::table('supplier_payments')->insert([
                'purchases_invoice_id' => $purchase_id,
                'supplier_id' => $supplier_id,
                'payment_date' => $request->input('payment_date'),
                'amount_paid' => $request->input('amount_paid'),
                'notes' => $request->input('notes'),
                'user_id' => auth()->id(),
                'payment_remaning_amount' => $currentRemaining, // Store previous remaining amount
                'created_at' => now(),
            ]);

            // Calculate the new remaining amount using SUM of all payments
            $totalPaid = DB::table('supplier_payments')
                ->where('purchases_invoice_id', $purchase_id)
                ->sum('amount_paid');

            $newRemaining = $currentRemaining - $totalPaid;

            // Update the latest supplier_payment record with the correct remaining amount
            DB::table('supplier_payments')
                ->where('purchases_invoice_id', $purchase_id)
                ->orderBy('supplier_payments_id', 'desc')
                ->limit(1)
                ->update(['payment_remaning_amount' => $newRemaining]);

            DB::commit();

            return redirect()->back()->with('success', 'تم تسجيل الدفع بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'حدث خطأ أثناء تسجيل الدفع: ' . $e->getMessage());
        }

    }

    public function updateSupplierPayment(Request $request, $supplierID, $invoice_id, $transactionId)
    {
        // التحقق من صحة البيانات
        $request->validate([
            'payment_date' => 'required|date',
            'amount_paid' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:255',
        ], [
            'payment_date.required' => 'يجب إدخال تاريخ السداد.',
            'payment_date.date' => 'يجب أن يكون تاريخ السداد تاريخًا صحيحًا.',
            'amount_paid.required' => 'يجب إدخال المبلغ المدفوع.',
            'amount_paid.numeric' => 'يجب أن يكون المبلغ المدفوع رقمًا صالحًا.',
            'amount_paid.min' => 'يجب أن يكون المبلغ المدفوع أكبر من 0.',
            'notes.string' => 'يجب أن تكون الملاحظات نصًا فقط.',
            'notes.max' => 'يجب ألا تتجاوز الملاحظات 255 حرفًا.',
        ]);

        DB::beginTransaction();
        try {
            // جلب المبلغ السابق للدفعة
            $previousPayment = DB::table('supplier_payments')
                ->where('supplier_payments_id', $transactionId)
                ->first();

            if (!$previousPayment) {
                return redirect()->back()->with('error', 'لم يتم العثور على الدفعة.');
            }

            // تحديث دفعة المورد
            DB::table('supplier_payments')
                ->where('supplier_payments_id', $transactionId)
                ->update([
                    'payment_date' => $request->input('payment_date'),
                    'amount_paid' => $request->input('amount_paid'),
                    'notes' => $request->input('notes'),
                    'purchases_invoice_id' => $invoice_id,
                    'supplier_id' => $supplierID,
                    'purchases_invoice_id' => $invoice_id,
                ]);

            // حساب إجمالي المدفوعات بعد التحديث
            $totalPaid = DB::table('supplier_payments')
                ->where('purchases_invoice_id', $invoice_id)
                ->sum('amount_paid');

            // جلب إجمالي الفاتورة
            $invoice = DB::table('purchase_invoices')
                ->where('id', $invoice_id)
                ->first();

            if (!$invoice) {
                return redirect()->back()->with('error', 'لم يتم العثور على الفاتورة.');
            }

            // حساب المبلغ المتبقي
            $newRemaining = $invoice->remaining_amount - $totalPaid;

            // تحديث المبلغ المتبقي في جدول الفواتير
            DB::table('supplier_payments')
                ->where('supplier_payments_id', $transactionId)
                ->update([
                    'payment_remaning_amount' => $newRemaining,
                    'updated_at' => now(),
                ]);

            DB::commit();

            return redirect()->back()->with('success', 'تم تحديث دفعة المورد بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'حدث خطأ أثناء تحديث الدفعة: ' . $e->getMessage());
        }
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
            'supplier_category' => 'array|required', // Ensure supplier_category is an array and required
        ], [
            'name.required' => 'يجب إدخال الاسم.',
            'name.regex' => 'يجب أن يحتوي الاسم على حروف، أرقام ومسافات فقط.',
            'address.required' => 'يجب إدخال العنوان.',
            'address.regex' => 'يجب أن يحتوي العنوان على حروف، أرقام ومسافات فقط.',
            'contact_info.required' => 'يجب إدخال معلومات الاتصال.',
            'contact_info.regex' => 'يجب أن يكون رقم الهاتف صالحًا ويبدأ بـ 01 ويتكون من 11 رقمًا.',
            'supplier_category.required' => 'يجب اختيار تصنيف واحد على الأقل للمورد.',
        ]);

        // Update supplier details
        DB::table('suppliers')
            ->where('id', $id)
            ->update([
                'name' => $request->name,
                'address' => $request->address,
                'contact_info' => $request->contact_info,
                'updated_at' => now(),
            ]);

        // Update supplier categories
        DB::table('supplier_category_tb')->where('supplier_id', $id)->delete(); // Remove old categories

        $supplierCategories = [];
        foreach ($request->supplier_category as $categoryId) {
            $supplierCategories[] = [
                'supplier_id' => $id,
                'category_id' => $categoryId,

            ];
        }

        // Insert new categories
        DB::table('supplier_category_tb')->insert($supplierCategories);

        return redirect()->back()->with('success', 'تم تعديل بيانات المورد بنجاح');
    }


    // Delete a supplier
    public function destroy($id)
    {
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
