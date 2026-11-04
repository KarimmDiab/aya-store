<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class purchasesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoices = DB::table('purchase_invoices')
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
            //->where('purchase_invoices.payment_method', 'credit')
            ->groupBy(
                'purchase_invoices.id',
                'purchase_invoices.remaining_amount', // مهم عشان تستخدمه في الحساب
                'users.id',
                'users.name'
            )
            ->get(); // Returns a collection

        $suppliers = DB::table('suppliers')
            ->whereNull('deleted_at')
            ->get(); // Returns a collection

        $products = DB::table('products')
            ->whereNull('deleted_at')
            ->get(); // Returns a collection

        $invoiceDetails = DB::table('purchase_invoices')
            ->join('purchase_invoices_details', 'purchase_invoices.id', '=', 'purchase_invoices_details.purchase_invoices_id')
            ->join('suppliers', 'purchase_invoices.supplier_id', '=', 'suppliers.id')
            ->join('products', 'purchase_invoices_details.product_id', '=', 'products.id')
            ->select(
                'purchase_invoices.id as purchase_invoice_id',
                'purchase_invoices.date as purchase_invoice_date',
                'suppliers.id as supplier_id',
                'suppliers.name as supplier_name',
                'products.id as product_id',
                'products.name as product_name',
                'products.price as product_price',
                'purchase_invoices_details.quantity as quantity_product',
                'purchase_invoices_details.price_per_pices as purchase_price',
                DB::raw('(purchase_invoices_details.quantity * purchase_invoices_details.price_per_pices) as total_product'),
                'purchase_invoices.total_amount as total_invoice',
                'purchase_invoices.paid_amount as invoice_paid_amount',
                'purchase_invoices.remaining_amount as invoice_remaining_amount'
            )
            ->get();

        $supplierCat = DB::table('supplier_category_tb')
            ->select(
                'supplier_category_tb.supplier_id',
                'suppliers.name',
                'supplier_category_tb.category_id',
                'category.category_name'
            )
            ->join('suppliers', 'supplier_category_tb.supplier_id', '=', 'suppliers.id')
            ->join('category', 'supplier_category_tb.category_id', '=', 'category.category_id')
            ->get();

        return view('purchases.purchase_invoices', compact('invoices', 'suppliers', 'products', 'invoiceDetails', 'supplierCat'));
    }


        public function editPricesInPurchaseInvoice()
    {
        $invoices = DB::table('purchase_invoices')
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
            //->where('purchase_invoices.payment_method', 'credit')
            ->groupBy(
                'purchase_invoices.id',
                'purchase_invoices.remaining_amount', // مهم عشان تستخدمه في الحساب
                'users.id',
                'users.name'
            )
            ->get(); // Returns a collection

        $suppliers = DB::table('suppliers')
            ->whereNull('deleted_at')
            ->get(); // Returns a collection

        $products = DB::table('products')
            ->whereNull('deleted_at')
            ->get(); // Returns a collection

        $invoiceDetails = DB::table('purchase_invoices')
            ->join('purchase_invoices_details', 'purchase_invoices.id', '=', 'purchase_invoices_details.purchase_invoices_id')
            ->join('suppliers', 'purchase_invoices.supplier_id', '=', 'suppliers.id')
            ->join('products', 'purchase_invoices_details.product_id', '=', 'products.id')
            ->select(
                'purchase_invoices.id as purchase_invoice_id',
                'purchase_invoices.date as purchase_invoice_date',
                'suppliers.id as supplier_id',
                'suppliers.name as supplier_name',
                'products.id as product_id',
                'products.name as product_name',
                'products.price as product_price',
                'purchase_invoices_details.quantity as quantity_product',
                'purchase_invoices_details.price_per_pices as purchase_price',
                DB::raw('(purchase_invoices_details.quantity * purchase_invoices_details.price_per_pices) as total_product'),
                'purchase_invoices.total_amount as total_invoice',
                'purchase_invoices.paid_amount as invoice_paid_amount',
                'purchase_invoices.remaining_amount as invoice_remaining_amount'
            )
            ->get();

        $supplierCat = DB::table('supplier_category_tb')
            ->select(
                'supplier_category_tb.supplier_id',
                'suppliers.name',
                'supplier_category_tb.category_id',
                'category.category_name'
            )
            ->join('suppliers', 'supplier_category_tb.supplier_id', '=', 'suppliers.id')
            ->join('category', 'supplier_category_tb.category_id', '=', 'category.category_id')
            ->get();

        return view('purchases.editPricesInPurchaseInvoice', compact('invoices', 'suppliers', 'products', 'invoiceDetails', 'supplierCat'));
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
        try {
            DB::beginTransaction(); // Start a database transaction

            $validatedData = $request->validate([
                'purchase_invoices_no' => 'required|string|unique:purchase_invoices,id',
                'purchase_invoices_date' => 'required|date',
                'purchase_invoices_supplier' => 'required|integer',
                'purchase_invoices_amount' => 'required|numeric',
                'amount_paid' => 'required|numeric',
                'remaining_amount' => 'nullable|numeric',
                'product_id' => 'required|array',
                'product_id.*' => 'required|integer|exists:products,id',
                'quantity' => 'required|array',
                'quantity.*' => 'required|numeric|min:1',
                'unit_price' => 'required|array',
                'unit_price.*' => 'required|numeric|min:0',
                'sales_price' => 'required|array',
                'sales_price.*' => 'required|numeric|min:0',
                'invoice_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ], [
                'purchase_invoices_no.required' => 'رقم الفاتورة مطلوب.',
                'purchase_invoices_no.string' => 'رقم الفاتورة يجب أن يكون نصًا.',
                'purchase_invoices_no.unique' => 'رقم الفاتورة مسجل من قبل.',
                'purchase_invoices_date.required' => 'تاريخ الفاتورة مطلوب.',
                'purchase_invoices_date.date' => 'تاريخ الفاتورة غير صالح.',
                'purchase_invoices_supplier.required' => 'المورد مطلوب.',
                'purchase_invoices_supplier.integer' => 'المورد يجب أن يكون رقمًا صحيحًا.',
                'purchase_invoices_amount.required' => 'إجمالي مبلغ الفاتورة مطلوب.',
                'purchase_invoices_amount.numeric' => 'إجمالي مبلغ الفاتورة يجب أن يكون رقمًا.',
                'amount_paid.required' => 'المبلغ المدفوع مطلوب.',
                'amount_paid.numeric' => 'المبلغ المدفوع يجب أن يكون رقمًا.',
                'product_id.required' => 'يجب اختيار منتج واحد على الأقل.',
                'product_id.*.required' => 'معرف المنتج مطلوب.',
                'product_id.*.integer' => 'معرف المنتج يجب أن يكون رقمًا صحيحًا.',
                'product_id.*.exists' => 'المنتج المحدد غير موجود.',
                'quantity.required' => 'الكمية مطلوبة.',
                'quantity.*.required' => 'يجب إدخال الكمية.',
                'quantity.*.numeric' => 'الكمية يجب أن تكون رقمًا.',
                'quantity.*.min' => 'الكمية يجب أن تكون على الأقل 1.',
                'unit_price.required' => 'سعر الوحدة مطلوب.',
                'unit_price.*.required' => 'سعر الوحدة مطلوب.',
                'unit_price.*.numeric' => 'سعر الوحدة يجب أن يكون رقمًا.',
                'unit_price.*.min' => 'سعر الوحدة لا يمكن أن يكون سالبًا.',
                'sales_price.required' => 'سعر البيع مطلوب.',
                'sales_price.*.required' => 'سعر البيع مطلوب.',
                'sales_price.*.numeric' => 'سعر البيع يجب أن يكون رقمًا.',
                'sales_price.*.min' => 'سعر البيع لا يمكن أن يكون سالبًا.',
                'invoice_file.file' => 'الملف يجب أن يكون صورة أو PDF.',
                'invoice_file.mimes' => 'الملف يجب أن يكون بصيغة jpg، jpeg، png أو pdf.',
                'invoice_file.max' => 'حجم الملف يجب ألا يتجاوز 2 ميجابايت.',
            ]);

            // Handle file upload
            $fileName = null;
            if ($request->hasFile('invoice_file')) {
                $file = $request->file('invoice_file');
                $fileName = time().'.'.$file->extension();
                $file->move(public_path('images/purchases_invoices'), $fileName);
            }

            // Calculate remaining amount
            $remainingAmount = $validatedData['purchase_invoices_amount'] - $validatedData['amount_paid'];

            // Determine payment method
            $paymentMethod = $remainingAmount > 0 ? 'credit' : 'cash';
            $userId = $request->user()->id;

            // Insert invoice into `purchase_invoices`
            $invoiceId = DB::table('purchase_invoices')->insertGetId([
                'id' => $validatedData['purchase_invoices_no'],
                'date' => $validatedData['purchase_invoices_date'],
                'supplier_id' => $validatedData['purchase_invoices_supplier'],
                'total_amount' => $validatedData['purchase_invoices_amount'],
                'paid_amount' => $validatedData['amount_paid'],
                'remaining_amount' => $remainingAmount,
                'payment_method' => $paymentMethod,
                'purchase_invoices_pic' => $fileName,
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Get warehouse ID
            $warehouseId = $request->warehouse_id ?? DB::table('warehouses')->orderBy('id')->value('id');

            // Insert products into `purchase_invoices_details` and update stock
            foreach ($validatedData['product_id'] as $index => $productId) {
                DB::table('purchase_invoices_details')->insert([
                    'purchase_invoices_id' => $invoiceId,
                    'product_id' => $productId,
                    'quantity' => $validatedData['quantity'][$index],
                    'price_per_pices' => $validatedData['unit_price'][$index],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Update stock quantity
                DB::table('products')
                    ->where('id', $productId)
                    ->increment('quantity', $validatedData['quantity'][$index]);

                // Update product sales price
                DB::table('products')
                    ->where('id', $productId)
                    ->update(['price' => $validatedData['sales_price'][$index]]);
            }

            DB::commit(); // Commit the transaction
            session()->flash('success', 'تم تسجيل فاتورة المشتريات بنجاح');

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback on error
            Log::error('Error saving invoice:', ['error' => $e->getMessage()]);
            session()->flash('error', 'خطأ في حفظ الفاتورة: '.$e->getMessage());
        }

        return redirect()->back();
    }

    public function newStoreInvoice(Request $request, $supplier)
    {
        // فك تشفير الـ Supplier ID
        $supplier_id = decrypt($supplier);

        $validator = Validator::make($request->all(), [
            'purchase_invoices_no' => 'required|string|unique:purchase_invoices,id',
            'purchase_invoices_date' => 'required|date',
            'invoice_file' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'purchase_invoices_amount' => 'required|numeric|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'remaining_amount' => 'required|numeric|min:0',
            'category_name.*' => 'required|string',
            'product_id.*' => 'required|numeric',
            'product.*' => 'required|string',
            'quantity.*' => 'required|numeric|min:1',
            'unit_price.*' => 'required|numeric|min:0',
            'sales_price.*' => 'required|numeric|min:0',
        ], [
            // رسائل الخطأ المخصصة
            'purchase_invoices_no.required' => 'رقم الفاتورة مطلوب.',
            'purchase_invoices_no.unique' => 'رقم الفاتورة مسجل مسبقًا.',
            'purchase_invoices_date.required' => 'تاريخ الفاتورة مطلوب.',
            'purchase_invoices_date.date' => 'يجب أن يكون التاريخ صالحًا.',
            'invoice_file.mimes' => 'يجب أن يكون الملف من نوع: jpeg, png, jpg, pdf.',
            'invoice_file.max' => 'يجب ألا يتجاوز حجم الملف 2MB.',
            'purchase_invoices_amount.required' => 'إجمالي الفاتورة مطلوب.',
            'purchase_invoices_amount.numeric' => 'يجب أن يكون إجمالي الفاتورة رقمًا.',
            'amount_paid.required' => 'المبلغ المدفوع مطلوب.',
            'amount_paid.numeric' => 'يجب أن يكون المبلغ المدفوع رقمًا.',
            'remaining_amount.required' => 'المبلغ المتبقي مطلوب.',
            'remaining_amount.numeric' => 'يجب أن يكون المبلغ المتبقي رقمًا.',
            'category_name.*.required' => 'اسم الفئة مطلوب.',
            'product_id.*.required' => 'رقم المنتج مطلوب.',
            'product_id.*.numeric' => 'يجب أن يكون رقم المنتج رقمًا صحيحًا.',
            'product.*.required' => 'اسم المنتج مطلوب.',
            'quantity.*.required' => 'الكمية مطلوبة.',
            'quantity.*.numeric' => 'يجب أن تكون الكمية رقمًا.',
            'quantity.*.min' => 'يجب أن تكون الكمية على الأقل 1.',
            'unit_price.*.required' => 'سعر الوحدة مطلوب.',
            'unit_price.*.numeric' => 'يجب أن يكون سعر الوحدة رقمًا.',
            'unit_price.*.min' => 'يجب أن يكون سعر الوحدة قيمة موجبة.',
            'sales_price.*.required' => 'سعر البيع مطلوب.',
            'sales_price.*.numeric' => 'يجب أن يكون سعر البيع رقمًا.',
            'sales_price.*.min' => 'يجب أن يكون سعر البيع قيمة موجبة.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction(); // بدء المعاملة

            // Handle the image upload if there's a file
            if ($request->hasFile('invoice_file')) {
                // Get the file's extension
                $imageExtension = $request->invoice_file->extension();

                // Create a unique image name
                $imageName = time().'.'.$imageExtension;

                // Move the image to the public directory
                $request->invoice_file->move(public_path('images/purchases_invoices'), $imageName);
            } else {
                $imageName = 0; // If no image is uploaded, set the name to null
            }

            // **3. تحديد طريقة الدفع**
            $paymentMethod = $request->amount_paid < $request->purchase_invoices_amount ? 'credit' : 'cash';

            // **4. إدخال بيانات الفاتورة**
            $purchaseInvoiceId = DB::table('purchase_invoices')->insertGetId([
                'id' => $request->purchase_invoices_no,
                'date' => $request->purchase_invoices_date,
                'supplier_id' => $supplier_id,
                'total_amount' => $request->purchase_invoices_amount,
                'paid_amount' => $request->amount_paid,
                'remaining_amount' => $request->remaining_amount,
                'payment_method' => $paymentMethod,
                'purchase_invoices_pic' => $imageName,
                'user_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $addInvoiceDetails = [];

            foreach ($request->product_id as $index => $product_id) {
                // التحقق مما إذا كان المنتج موجودًا بالفعل
                $existingProduct = DB::table('products')->where('id', $product_id)->first();
                $warehoue = DB::table('warehouses')->whereNull('deleted_at')->first();

                if (! $existingProduct) {
                    // إدخال المنتج الجديد
                    DB::table('products')->insert([
                        'id' => $product_id,
                        'name' => $request->product[$index],
                        'category' => $request->category_name[$index],
                        'quantity' => $request->quantity[$index],
                        'price' => $request->sales_price[$index],
                        'warehouse_id' => $warehoue->id,
                        'created_at' => now(),
                        //'updated_at' => now()
                    ]);
                } else {
                    // تحديث كمية المنتج إذا كان موجودًا
                    DB::table('products')->where('id', $product_id)
                        ->increment('quantity', $request->quantity[$index]);
                }

                // تحضير بيانات purchase_invoices_details
                $addInvoiceDetails[] = [
                    'purchase_invoices_id' => $purchaseInvoiceId,
                    'product_id' => $product_id,
                    'quantity' => $request->quantity[$index],
                    'price_per_pices' => $request->unit_price[$index],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // **5. إدخال بيانات تفاصيل الفاتورة دفعة واحدة**
            DB::table('purchase_invoices_details')->insert($addInvoiceDetails);

            DB::commit(); // تأكيد المعاملة

            return redirect()->back()->with('success', 'تمت إضافة الفاتورة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack(); // إلغاء المعاملة عند الخطأ

            // **تسجيل الخطأ في logs**
            \Log::error('خطأ أثناء إضافة الفاتورة: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            // **إرجاع رسالة خطأ تفصيلية**
            return redirect()->back()->with('error', 'حدث خطأ أثناء حفظ الفاتورة: '.$e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $invoice_id)
    {
        $invoices = DB::table('purchase_invoices')
            ->whereNull('deleted_at')
            ->where('purchase_invoices.id', $invoice_id)
            ->first(); // ✅ Returns a single object instead of a collection

        if (! $invoices) {
            return redirect()->back()->with('error', 'Invoice not found.');
        }

        $invoiceDetails = DB::table('purchase_invoices')
            ->join('purchase_invoices_details', 'purchase_invoices.id', '=', 'purchase_invoices_details.purchase_invoices_id')
            ->join('suppliers', 'purchase_invoices.supplier_id', '=', 'suppliers.id')
            ->join('products', 'purchase_invoices_details.product_id', '=', 'products.id')
            ->select(
                'purchase_invoices.id as purchase_invoice_id',
                'purchase_invoices.date as purchase_invoice_date',
                'suppliers.id as supplier_id',
                'suppliers.name as supplier_name',
                'products.id as product_id',
                'products.name as product_name',
                'products.price as product_price',
                'purchase_invoices_details.quantity as quantity_product',
                'purchase_invoices_details.price_per_pices as purchase_price',
                DB::raw('(purchase_invoices_details.quantity * purchase_invoices_details.price_per_pices) as total_product'),
                'purchase_invoices.total_amount as total_invoice',
                'purchase_invoices.paid_amount as invoice_paid_amount',
                'purchase_invoices.remaining_amount as invoice_remaining_amount'
            )
            ->where('purchase_invoices.id', $invoice_id)
            ->get();

        $suppliers = DB::table('suppliers')
            ->whereNull('deleted_at')
            ->get(); // Returns a collection

        $products = DB::table('products')
            ->whereNull('deleted_at')
            ->get(); // Returns a collection

        return view('purchases.update_purchase_invoices', compact('invoices', 'invoiceDetails', 'products', 'suppliers'));
    }



        public function editPricePurchaseInvoice(int $invoice_id)
    {
        $invoices = DB::table('purchase_invoices')
            ->whereNull('deleted_at')
            ->where('purchase_invoices.id', $invoice_id)
            ->first(); // ✅ Returns a single object instead of a collection

        if (! $invoices) {
            return redirect()->back()->with('error', 'Invoice not found.');
        }

        $invoiceDetails = DB::table('purchase_invoices')
            ->join('purchase_invoices_details', 'purchase_invoices.id', '=', 'purchase_invoices_details.purchase_invoices_id')
            ->join('suppliers', 'purchase_invoices.supplier_id', '=', 'suppliers.id')
            ->join('products', 'purchase_invoices_details.product_id', '=', 'products.id')
            ->select(
                'purchase_invoices.id as purchase_invoice_id',
                'purchase_invoices.date as purchase_invoice_date',
                'suppliers.id as supplier_id',
                'suppliers.name as supplier_name',
                'products.id as product_id',
                'products.name as product_name',
                'products.price as product_price',
                'purchase_invoices_details.quantity as quantity_product',
                'purchase_invoices_details.price_per_pices as purchase_price',
                DB::raw('(purchase_invoices_details.quantity * purchase_invoices_details.price_per_pices) as total_product'),
                'purchase_invoices.total_amount as total_invoice',
                'purchase_invoices.paid_amount as invoice_paid_amount',
                'purchase_invoices.remaining_amount as invoice_remaining_amount'
            )
            ->where('purchase_invoices.id', $invoice_id)
            ->get();

        $suppliers = DB::table('suppliers')
            ->whereNull('deleted_at')
            ->get(); // Returns a collection

        $products = DB::table('products')
            ->whereNull('deleted_at')
            ->get(); // Returns a collection

        return view('purchases.update_price_purchase_invoices', compact('invoices', 'invoiceDetails', 'products', 'suppliers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'purchase_invoices_no' => 'required|string',
            'purchase_invoices_date' => 'required|date',
            'purchase_invoices_supplier' => 'required|integer',
            'purchase_invoices_amount' => 'required|numeric',
            'amount_paid' => 'required|numeric',
            'product_id' => 'required|array',
            'quantity' => 'required|array',
            'unit_price' => 'required|array',
            'sales_price' => 'required|array',
            'price_percentage' => 'nullable|numeric|min:0', // Added percentage validation
        ], [
            'purchase_invoices_no.required' => 'رقم الفاتورة مطلوب.',
            'purchase_invoices_no.string' => 'رقم الفاتورة يجب أن يكون نصًا.',
            'purchase_invoices_date.required' => 'تاريخ الفاتورة مطلوب.',
            'purchase_invoices_date.date' => 'تاريخ الفاتورة غير صالح.',
            'purchase_invoices_supplier.required' => 'المورد مطلوب.',
            'purchase_invoices_supplier.integer' => 'المورد يجب أن يكون رقمًا صحيحًا.',
            'purchase_invoices_amount.required' => 'إجمالي مبلغ الفاتورة مطلوب.',
            'purchase_invoices_amount.numeric' => 'إجمالي مبلغ الفاتورة يجب أن يكون رقمًا.',
            'amount_paid.required' => 'المبلغ المدفوع مطلوب.',
            'amount_paid.numeric' => 'المبلغ المدفوع يجب أن يكون رقمًا.',
            'product_id.required' => 'يجب اختيار منتج واحد على الأقل.',
            'product_id.array' => 'المنتجات يجب أن تكون في شكل قائمة.',
            'quantity.required' => 'الكمية مطلوبة.',
            'quantity.array' => 'الكمية يجب أن تكون في شكل قائمة.',
            'unit_price.required' => 'سعر الوحدة مطلوب.',
            'unit_price.array' => 'سعر الوحدة يجب أن يكون في شكل قائمة.',
            'sales_price.required' => 'سعر البيع مطلوب.',
            'sales_price.array' => 'سعر البيع يجب أن يكون في شكل قائمة.',
        ]);
        DB::beginTransaction();

        try {

            // 1️⃣ تحديث بيانات الفاتورة
            $remaining = $validatedData['purchase_invoices_amount'] - $validatedData['amount_paid'];

            DB::table('purchase_invoices')
                ->where('id', $id)
                ->update([
                    'date' => $validatedData['purchase_invoices_date'],
                    'supplier_id' => $validatedData['purchase_invoices_supplier'],
                    'total_amount' => $validatedData['purchase_invoices_amount'],
                    'paid_amount' => $validatedData['amount_paid'],
                    'remaining_amount' => $remaining,
                    'payment_method' => $remaining > 0 ? 'credit' : 'cash', // 👈 هنا الشرط
                    'updated_at' => now(),
                ]);

            // 2️⃣ جلب كل تفاصيل الفاتورة القديمة (قبل التعديل)
            $oldDetails = DB::table('purchase_invoices_details')
                ->where('purchase_invoices_id', $id)
                ->get();

            // 3️⃣ رجّع الكميات القديمة للمخزون (reset)
            foreach ($oldDetails as $item) {
                DB::table('products')
                    ->where('id', $item->product_id)
                    ->decrement('quantity', $item->quantity);
            }

            // 4️⃣ Soft Delete للعناصر المحذوفة
            if (! empty($request->deleted_items)) {

                $deletedIds = explode(',', $request->deleted_items);

                DB::table('purchase_invoices_details')
                    ->whereIn('id', $deletedIds)
                    ->update([
                        'deleted_at' => now(),
                    ]);
            }

            // 5️⃣ حذف كل التفاصيل القديمة (Hard delete منطقي بعد ما رجعنا المخزون)
            DB::table('purchase_invoices_details')
                ->where('purchase_invoices_id', $id)
                ->delete();

            // 6️⃣ إدخال البيانات الجديدة
            foreach ($validatedData['product_id'] as $index => $productId) {

                $quantity = $validatedData['quantity'][$index];
                $unitPrice = $validatedData['unit_price'][$index];
                $salesPrice = $validatedData['sales_price'][$index];

                DB::table('purchase_invoices_details')->insert([
                    'purchase_invoices_id' => $id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price_per_pices' => $unitPrice,
                    'created_at' => now(),
                ]);

                // 7️⃣ تحديث المخزون (إضافة الكمية الجديدة)
                DB::table('products')
                    ->where('id', $productId)
                    ->increment('quantity', $quantity);

                // 8️⃣ تحديث سعر البيع للمنتج
                DB::table('products')
                    ->where('id', $productId)
                    ->update(['price' => $salesPrice]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'تم تحديث الفاتورة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'حدث خطأ أثناء تحديث الفاتورة: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        if (! is_array($id)) {
            $id = [$id];
        }

        DB::beginTransaction();
        try {
            // استرجاع تفاصيل الفاتورة قبل حذفها
            $invoiceDetails = DB::table('purchase_invoices_details')
                ->whereIn('purchase_invoices_id', $id)
                ->select('product_id', 'quantity')
                ->get();

            foreach ($invoiceDetails as $detail) {
                // جلب بيانات المنتج من جدول المنتجات
                $product = DB::table('products')
                    ->where('id', $detail->product_id)
                    ->select('id', 'quantity')
                    ->first();

                // التحقق مما إذا كان المنتج موجودًا أم لا
                if (! $product) {
                    DB::rollBack();

                    return redirect()->back()->with('error', 'حدث خطأ: المنتج غير موجود في المخزون!');
                }

                // التحقق من الكمية المتاحة قبل الحذف
                if ($product->quantity < $detail->quantity) {
                    DB::rollBack();

                    return redirect()->back()->with('error', 'لقد قمت بعمليات بيع من هذا المنتج بعد فاتورة المشتريات حتى أصبح عدد المنتجات أقل من العدد الذي قمت بشرائه من خلال الفاتورة.');
                }
            }

            // تقليل الكمية في جدول المنتجات بعد التأكد من كفاية المخزون
            foreach ($invoiceDetails as $detail) {
                DB::table('products')
                    ->where('id', $detail->product_id)
                    ->decrement('quantity', $detail->quantity);
            }

            // حذف تفاصيل الفاتورة
            DB::table('purchase_invoices_details')
                ->whereIn('purchase_invoices_id', $id)
                ->update(['deleted_at' => now()]);

            // تطبيق Soft Delete على الفاتورة
            DB::table('purchase_invoices')
                ->whereIn('id', $id)
                ->update(['deleted_at' => now()]);

            DB::commit();

            return redirect()->back()->with('success', 'تم حذف الفاتورة بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'حدث خطأ أثناء حذف الفاتورة، يرجى المحاولة مرة أخرى.');
        }
    }
}
