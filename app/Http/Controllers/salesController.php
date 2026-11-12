<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class salesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = DB::table('category')
            ->whereNull('deleted_at')
            ->get();
        $products = DB::table('products')
            ->whereNull('deleted_at')
            ->where('quantity', '>', 0)
            ->select('id', 'name', 'category', 'quantity', 'price', 'image')
            ->get();
        $customers = DB::table('customers')
            ->whereNull('deleted_at')
            ->select('id', 'name', 'contact_info')
            ->get();

        return view('sales.sales_invoice', compact('categories', 'products', 'customers'));
    }

    public function allInvoices(Request $request)
    {
        $filters = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ], [
            'date_from.date' => 'تاريخ البداية غير صحيح.',
            'date_to.date' => 'تاريخ النهاية غير صحيح.',
            'date_to.after_or_equal' => 'تاريخ النهاية يجب أن يكون بعد تاريخ البداية أو مساويًا له.',
        ]);

        $transactionTotals = DB::table('customer_payment_transactions')
            ->whereNull('deleted_at')
            ->select('sales_invoice_id')
            ->selectRaw('SUM(amount_paid) as total_paid_transactions')
            ->groupBy('sales_invoice_id');
        $returnTotals = DB::table('sales_return_invoices')
            ->whereNull('deleted_at')
            ->select('original_invoice_id')
            ->selectRaw('SUM(total_amount) as total_returned, COUNT(*) as active_return_count')
            ->groupBy('original_invoice_id');
        $salesInvoices = DB::table('sales_invoices')
        ->leftJoin('customers', 'sales_invoices.customer_id', '=', 'customers.id')
        ->leftJoinSub($transactionTotals, 'transaction_totals', function ($join) {
            $join->on('transaction_totals.sales_invoice_id', '=', 'sales_invoices.id');
        })
        ->leftJoinSub($returnTotals, 'return_totals', function ($join) {
            $join->on('return_totals.original_invoice_id', '=', 'sales_invoices.id');
        })
        ->select(
            'sales_invoices.id as sales_invoice_id',
            'customers.id as customer_id',
            DB::raw("COALESCE(customers.name, 'بدون بيانات عميل') as customer_name"),
            'customers.contact_info as customer_phone',
            'sales_invoices.payment_method as payment_method',
            'sales_invoices.total_amount as total_invoice',
            'sales_invoices.total_amount as gross_total',
            'sales_invoices.deduction as invoice_discount',
            'sales_invoices.net_total',
            'sales_invoices.paid_amount as invoice_paid_amount',
            'sales_invoices.remaining_amount as invoice_remaning_amount',
            'sales_invoices.created_at as invoice_date',
            'sales_invoices.updated_at as invoice_update',
    
            DB::raw('COALESCE(transaction_totals.total_paid_transactions, 0) as total_paid_transactions'),
            DB::raw('COALESCE(return_totals.total_returned, 0) as total_returned'),
            DB::raw('COALESCE(return_totals.active_return_count, 0) as return_count'),
            DB::raw('COALESCE(sales_invoices.net_total, sales_invoices.total_amount - COALESCE(sales_invoices.deduction, 0)) as net_after_discount'),
            DB::raw('GREATEST(COALESCE(sales_invoices.net_total, sales_invoices.total_amount - COALESCE(sales_invoices.deduction, 0)) - COALESCE(return_totals.total_returned, 0), 0) as net_after_returns'),
            DB::raw('COALESCE(sales_invoices.paid_amount, 0) + COALESCE(transaction_totals.total_paid_transactions, 0) as total_paid_before_returns'),
            DB::raw('LEAST(COALESCE(return_totals.total_returned, 0), COALESCE(sales_invoices.paid_amount, 0) + COALESCE(transaction_totals.total_paid_transactions, 0)) as paid_amount_deducted_for_returns'),
            DB::raw('GREATEST(COALESCE(sales_invoices.paid_amount, 0) + COALESCE(transaction_totals.total_paid_transactions, 0) - COALESCE(return_totals.total_returned, 0), 0) as total_paid_amount'),
            DB::raw('GREATEST(COALESCE(sales_invoices.net_total, sales_invoices.total_amount - COALESCE(sales_invoices.deduction, 0)) - COALESCE(return_totals.total_returned, 0) - COALESCE(sales_invoices.paid_amount, 0) - COALESCE(transaction_totals.total_paid_transactions, 0), 0) as adjusted_remaining_amount')
        )
        ->whereNull('sales_invoices.deleted_at')
        ->when(! empty($filters['date_from']), function ($query) use ($filters) {
            $query->whereDate('sales_invoices.created_at', '>=', $filters['date_from']);
        })
        ->when(! empty($filters['date_to']), function ($query) use ($filters) {
            $query->whereDate('sales_invoices.created_at', '<=', $filters['date_to']);
        })
        ->orderByDesc('sales_invoices.id')
        ->paginate(50)
        ->withQueryString();

        $invoiceIds = $salesInvoices->getCollection()->pluck('sales_invoice_id');
        $salesInvoicesDetails = DB::table('sales_invoices')
            ->select(
                'sales_invoices.id as sales_invoice_id',
                'products.id as product_id',
                'products.name as product_name',
                'sales_invoice_details.quantity as product_quantity',
                'sales_invoice_details.unit_price as sales_price',
                'sales_invoice_details.total_amount as product_total_amount'
            )
            ->join('sales_invoice_details', 'sales_invoices.id', '=', 'sales_invoice_details.invoice_id')
            ->join('products', 'sales_invoice_details.product_id', '=', 'products.id')
            ->whereNull('sales_invoices.deleted_at')
            ->whereNull('sales_invoice_details.deleted_at')
            ->whereIn('sales_invoices.id', $invoiceIds)
            ->get();
        $salesInvoicesDetailsByInvoice = $salesInvoicesDetails->groupBy('sales_invoice_id');

        return view('sales.all_sales_invoices', compact('salesInvoices', 'salesInvoicesDetailsByInvoice'));
    }

    public function searchProducts(Request $request)
    {
        $term = trim((string) $request->query('q', ''));

        $products = DB::table('products')
            ->whereNull('deleted_at')
            ->when($term !== '', function ($query) use ($term) {
                $query->where(function ($query) use ($term) {
                    $query->where('name', 'like', '%' . $term . '%')
                        ->orWhere('id', 'like', '%' . $term . '%');
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'price'])
            ->map(fn ($product) => ['id' => $product->id, 'text' => $product->name, 'price' => $product->price]);

        return response()->json(['results' => $products]);
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
        // Decode 'cart' JSON if necessary
        $cart = $request->has('cart') ? json_decode($request->input('cart'), true) : [];
        $userId = Auth::id();  // This returns the ID of the currently authenticated user
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($cart)) {
            return redirect()->back()->withErrors(['cart' => 'Invalid format for cart items.']);
        }

        // Add cart to the request for validation
        $request->merge(['cart' => $cart]);

        // Custom error messages
        $messages = [
            'customer.name.required' => 'اسم العميل مطلوب عند اختيار تسجيل عميل جديد.',
            'customer.name.string' => 'اسم العميل يجب أن يكون نص.',
            'customer.name.max' => 'اسم العميل يجب أن لا يتجاوز 255 حرفًا.',
            //'customer.phone.required' => 'رقم الهاتف مطلوب.',
            'customer.phone.regex' => 'رقم الهاتف غير صالح. يجب أن يبدأ برقم 01 ويتبع بـ 9 أرقام.',
            'cart.required' => 'عربة التسوق مطلوبة.',
            'cart.array' => 'عربة التسوق يجب أن تكون مصفوفة.',
            'cart.min' => 'عربة التسوق يجب أن تحتوي على عنصر واحد على الأقل.',
            'cart.*.id.required' => 'معرف المنتج مطلوب.',
            'cart.*.id.integer' => 'معرف المنتج يجب أن يكون رقمًا صحيحًا.',
            'cart.*.id.exists' => 'المنتج مع هذا المعرف غير موجود.',
            'cart.*.quantity.required' => 'الكمية مطلوبة.',
            'cart.*.quantity.integer' => 'الكمية يجب أن تكون رقمًا صحيحًا.',
            'cart.*.quantity.min' => 'الحد الأدنى للكمية هو 1.',
            'cart.*.price.required' => 'السعر مطلوب.',
            'cart.*.price.numeric' => 'السعر يجب أن يكون رقمًا.',
            'cart.*.price.min' => 'السعر يجب أن يكون أكبر من أو يساوي 0.',
            'cart.*.total.required' => 'المجموع مطلوب.',
            'cart.*.total.numeric' => 'المجموع يجب أن يكون رقمًا.',
            'cart.*.total.min' => 'المجموع يجب أن يكون أكبر من أو يساوي 0.',
            'total.required' => 'المجموع الكلي مطلوب.',
            'total.numeric' => 'المجموع الكلي يجب أن يكون رقمًا.',
            'total.min' => 'المجموع الكلي يجب أن يكون أكبر من أو يساوي 0.',
            'deduction.numeric' => 'الخصم يجب أن يكون رقمًا.',
            'deduction.min' => 'الخصم يجب أن يكون أكبر من أو يساوي 0.',
            'finalTotal.required' => 'المجموع النهائي مطلوب.',
            'finalTotal.numeric' => 'المجموع النهائي يجب أن يكون رقمًا.',
            'finalTotal.min' => 'المجموع النهائي يجب أن يكون أكبر من أو يساوي 0.',
            'paymentMethod.required' => 'طريقة الدفع مطلوبة.',
            'paymentMethod.string' => 'طريقة الدفع يجب أن تكون نصًا.',
            'paymentMethod.in' => 'طريقة الدفع يجب أن تكون واحدة من القيم: cash, visa, credit.',
            'payAmount.numeric' => 'المبلغ المدفوع يجب أن يكون رقمًا.',
            'payAmount.min' => 'المبلغ المدفوع يجب أن يكون أكبر من أو يساوي 0.',
            'remainingAmount.numeric' => 'المبلغ المتبقي يجب أن يكون رقمًا.',
            'remainingAmount.min' => 'المبلغ المتبقي يجب أن يكون أكبر من أو يساوي 0.',
        ];

        // Validation rules for the cart and other fields
        $validated = $request->validate([
            'customer_type' => 'nullable|in:none,new,existing',
            'customer_id' => 'required_if:customer_type,existing|nullable|integer',
            'customer.name' => 'required_if:customer_type,new|nullable|string|max:255',
            'customer.phone' => ['nullable', 'regex:/^01[0-9]{9}$/'],
            //'customer.phone' => 'required|regex:/^01[0-9]{9}$/',
            'cart' => 'required|array|min:1',
            'cart.*.id' => 'required|integer|exists:products,id',
            'cart.*.quantity' => 'required|integer|min:1',
            'cart.*.price' => 'required|numeric|min:0',
            'cart.*.total' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'deduction' => 'nullable|numeric|min:0',
            'finalTotal' => 'required|numeric|min:0',
            'paymentMethod' => 'required|string|in:cash,visa,credit',
            'payAmount' => 'numeric|min:0',
            'remainingAmount' => 'numeric|min:0',
        ], $messages);

        if ($validated['paymentMethod'] === 'credit' && ($validated['customer_type'] ?? 'none') === 'none') {
            return back()->withInput()->withErrors([
                'customer' => 'البيع الآجل يتطلب تسجيل عميل أو اختيار عميل دائم لحفظ المديونية على حسابه.',
            ]);
        }

        try {
            // Validation passed
            $cart = $validated['cart'];
        
            // Calculate totals
            $grossTotal = array_sum(array_map(function ($item) {
                return $item['quantity'] * $item['price'];
            }, $cart));
        
            $deduction = is_numeric($validated['deduction'] ?? null) ? (float) $validated['deduction'] : 0;
            if ($deduction > $grossTotal) {
                return back()->withInput()->withErrors(['deduction' => 'الخصم لا يمكن أن يكون أكبر من إجمالي الفاتورة.']);
            }
            $netTotal = $grossTotal - $deduction;

            if ($validated['paymentMethod'] === 'credit' && (float) ($validated['payAmount'] ?? 0) > $netTotal) {
                return back()->withInput()->withErrors(['payAmount' => 'المبلغ المدفوع أكبر من الإجمالي بعد الخصم.']);
            }
        
            $remainingAmount = ($validated['paymentMethod'] === 'credit') ?
                max(0, $netTotal - ($validated['payAmount'] ?? 0)) : 0;
        
            // Start transaction
            DB::beginTransaction();
        
            // Handle optional phone
            $customer = null;
            $customerId = null;
            $customerType = $validated['customer_type'] ?? 'none';

            if ($customerType === 'existing') {
                $customer = DB::table('customers')
                    ->where('id', $validated['customer_id'])
                    ->whereNull('deleted_at')
                    ->first();

                if (! $customer) {
                    DB::rollBack();
                    return back()->withInput()->withErrors(['customer_id' => 'العميل الدائم المحدد غير موجود. اختر العميل مرة أخرى.']);
                }
                $customerId = $customer->id;
            } elseif ($customerType === 'new') {
                $phone = $request->filled('customer.phone') ? trim($request->input('customer.phone')) : null;
                $customer = $phone
                    ? DB::table('customers')->where('contact_info', $phone)->whereNull('deleted_at')->first()
                    : null;

                if ($customer) {
                    $customerId = $customer->id;
                } else {
                    $customerId = DB::table('customers')->insertGetId([
                        'name' => $validated['customer']['name'],
                        'contact_info' => $phone,
                        'created_at' => $request->datetime ?: now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        
            // Insert into sales_invoices
            $invoiceId = DB::table('sales_invoices')->insertGetId([
                'customer_id' => $customerId,
                'total_amount' => $grossTotal,
                'deduction' => $deduction,
                'net_total' => $netTotal,
                'payment_method' => $validated['paymentMethod'],
                'paid_amount' => $validated['payAmount'] ?? $netTotal,
                'remaining_amount' => $remainingAmount,
                'user_id' => $userId,
                'created_at' => $request->datetime ? $request->datetime : now(),
                'updated_at' => now(),
            ]);
        
            // Insert invoice details
            $invoiceDetails = array_map(function ($item) use ($invoiceId) {
                return [
                    'invoice_id' => $invoiceId,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_amount' => $item['total'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $cart);
        
            DB::table('sales_invoice_details')->insert($invoiceDetails);
        
            // Check stock
            $productIds = array_column($cart, 'id');
            $products = DB::table('products')
                ->whereIn('id', $productIds)
                ->get();
        
            foreach ($cart as $item) {
                $product = $products->firstWhere('id', $item['id']);
        
                if (! $product) {
                    throw new \Exception('المنتج غير موجود');
                }
        
                if ($item['quantity'] > $product->quantity) {
                    throw new \Exception('لا يوجد مخزون كافي من المنتج: ' . $product->name);
                }
            }
        
            // Decrement stock
            foreach ($cart as $item) {
                DB::table('products')
                    ->where('id', $item['id'])
                    ->decrement('quantity', $item['quantity']);
            }
        
            // Commit transaction
            DB::commit();
        
            $orderData = $request->all();
        
            return redirect()->route('invoice.show', ['id' => $invoiceId])
                ->with('success', 'تم تسجيل فاتورة المبيعات بنجاح')
                ->with('orderData', $orderData);
        
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($salesInvoiceId)
    {
        // Retrieve the invoice details
        $invoice = DB::table('sales_invoices')
            ->where('id', $salesInvoiceId)
            ->first();

        // Check if invoice exists
        if (! $invoice) {
            return redirect()->back()->with('error', 'Invoice not found.');
        }

        // Retrieve customer details
        $customer = DB::table('customers')
            ->where('id', $invoice->customer_id)
            ->select('id', 'name', 'contact_info as phone') // Add the phone number column
            ->first();

        // Retrieve invoice items
        $invoiceItems = DB::table('sales_invoice_details')
            ->join('products', 'sales_invoice_details.product_id', '=', 'products.id')
            ->where('sales_invoice_details.invoice_id', $salesInvoiceId)
            ->select('products.name as product_name', 'sales_invoice_details.quantity', 'sales_invoice_details.unit_price', 'sales_invoice_details.total_amount')
            ->get();

        // Retrieve the user who created the invoice, if the user_id exists
        $user = null;
        if (isset($invoice->user_id)) {
            $user = DB::table('users')
                ->where('id', $invoice->user_id) // Assuming the invoice has a user_id column
                ->select('id', 'name')
                ->first();
        }

        // Calculate the totals
        $total = $invoiceItems->sum('total_amount');
        $deduction = $invoice->deduction ?? 0;  // Assuming deduction is saved in the invoice
        $finalTotal = $total - $deduction;

        // Prepare the data to pass to the view
        return view('sales.invoice', [
            'invoice' => $invoice,
            'customer' => $customer,
            'invoiceItems' => $invoiceItems,
            'user' => $user, // Pass user data
            'total' => $total,
            'deduction' => $deduction,
            'finalTotal' => $finalTotal,
        ]);
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
        $validated = $request->validate([
            'edit_sales_customer_name' => 'nullable|string|max:255',
            'edit_sales_customer_phone' => 'nullable|string|max:255',
            'edit_sales_payment_method' => 'required|in:cash,visa,credit',
            'edit_sales_amount_paid' => 'required|numeric|min:0',
            'product_name' => 'required|array|min:1',
            'product_name.*' => 'required|integer|exists:products,id',
            'quantity' => 'required|array|min:1',
            'quantity.*' => 'required|integer|min:1',
            'unit_price' => 'required|array|min:1',
            'unit_price.*' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($validated, $id) {
                $invoice = DB::table('sales_invoices')
                    ->where('id', $id)
                    ->whereNull('deleted_at')
                    ->lockForUpdate()
                    ->first();
                if (! $invoice) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['invoice' => 'فاتورة المبيعات غير موجودة.']);
                }

                $hasReturns = DB::table('sales_return_invoices')
                    ->where('original_invoice_id', $invoice->id)
                    ->whereNull('deleted_at')
                    ->exists();
                if ($hasReturns) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'invoice' => 'لا يمكن تعديل فاتورة لها مرتجعات مسجلة حتى لا يختل رصيد المخزون والحسابات.',
                    ]);
                }

                $newLines = [];
                $newQuantities = [];
                $grossTotal = 0;
                foreach ($validated['product_name'] as $index => $productId) {
                    $productId = (int) $productId;
                    $quantity = (int) $validated['quantity'][$index];
                    $unitPrice = (float) $validated['unit_price'][$index];
                    $lineTotal = round($quantity * $unitPrice, 2);
                    $grossTotal += $lineTotal;
                    $newQuantities[$productId] = ($newQuantities[$productId] ?? 0) + $quantity;
                    $newLines[] = [
                        'invoice_id' => $invoice->id,
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_amount' => $lineTotal,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                $oldQuantities = DB::table('sales_invoice_details')
                    ->where('invoice_id', $invoice->id)
                    ->whereNull('deleted_at')
                    ->select('product_id')
                    ->selectRaw('SUM(quantity) as quantity')
                    ->groupBy('product_id')
                    ->pluck('quantity', 'product_id');
                $productIds = collect(array_keys($oldQuantities->all()))
                    ->merge(array_keys($newQuantities))->unique()->values();
                $products = DB::table('products')->whereIn('id', $productIds)
                    ->lockForUpdate()->get()->keyBy('id');

                foreach ($newQuantities as $productId => $quantity) {
                    if (! $products->has($productId) || $products->get($productId)->deleted_at !== null) {
                        throw \Illuminate\Validation\ValidationException::withMessages(['product_name' => 'أحد المنتجات المحددة غير متاح.']);
                    }
                }
                foreach ($productIds as $productId) {
                    $stockChange = (int) ($oldQuantities[$productId] ?? 0) - (int) ($newQuantities[$productId] ?? 0);
                    $product = $products->get($productId);
                    if ($stockChange < 0 && (int) ($product->quantity ?? 0) < abs($stockChange)) {
                        throw \Illuminate\Validation\ValidationException::withMessages(['quantity' => 'المخزون الحالي لا يكفي للكمية الجديدة.']);
                    }
                    if ($stockChange !== 0) {
                        DB::table('products')->where('id', $productId)->update([
                            'quantity' => DB::raw('COALESCE(quantity, 0) + ' . $stockChange),
                            'updated_at' => now(),
                        ]);
                    }
                }

                $deduction = (float) ($invoice->deduction ?? 0);
                if ($grossTotal < $deduction) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'quantity' => 'إجمالي الأصناف لا يكفي لتغطية خصم الفاتورة الحالي.',
                    ]);
                }
                $netTotal = round($grossTotal - $deduction, 2);
                $paidAmount = (float) $validated['edit_sales_amount_paid'];
                $additionalPayments = (float) DB::table('customer_payment_transactions')
                    ->where('sales_invoice_id', $invoice->id)
                    ->whereNull('deleted_at')
                    ->sum('amount_paid');
                if ($paidAmount + $additionalPayments > $netTotal + 0.00001) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'edit_sales_amount_paid' => 'إجمالي الدفعة الأولى ودفعات الأجل يتجاوز صافي الفاتورة.',
                    ]);
                }
                if ($validated['edit_sales_payment_method'] !== 'credit' && $paidAmount + $additionalPayments + 0.00001 < $netTotal) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'edit_sales_amount_paid' => 'يجب سداد صافي الفاتورة بالكامل عند اختيار الدفع النقدي أو البطاقة.',
                    ]);
                }
                $remainingAmount = $validated['edit_sales_payment_method'] === 'credit' ? max(0, $netTotal - $paidAmount) : 0;

                if ($invoice->customer_id) {
                    $customerUpdates = ['updated_at' => now()];
                    if (isset($validated['edit_sales_customer_name'])) {
                        $customerUpdates['name'] = $validated['edit_sales_customer_name'];
                    }
                    if (isset($validated['edit_sales_customer_phone'])) {
                        $customerUpdates['contact_info'] = $validated['edit_sales_customer_phone'];
                    }
                    if (count($customerUpdates) > 1) {
                        DB::table('customers')->where('id', $invoice->customer_id)->update($customerUpdates);
                    }
                }
                DB::table('sales_invoice_details')->where('invoice_id', $invoice->id)
                    ->whereNull('deleted_at')->update(['deleted_at' => now(), 'updated_at' => now()]);
                DB::table('sales_invoice_details')->insert($newLines);
                DB::table('sales_invoices')->where('id', $invoice->id)->update([
                    'total_amount' => $grossTotal,
                    'net_total' => $netTotal,
                    'payment_method' => $validated['edit_sales_payment_method'],
                    'paid_amount' => $paidAmount,
                    'remaining_amount' => $remainingAmount,
                    'updated_at' => now(),
                ]);

                $remaining = max(0, $netTotal - $paidAmount);
                $payments = DB::table('customer_payment_transactions')
                    ->where('sales_invoice_id', $invoice->id)
                    ->whereNull('deleted_at')
                    ->orderBy('payment_date')
                    ->orderBy('id')
                    ->get(['id', 'amount_paid']);
                foreach ($payments as $payment) {
                    $remaining = max(0, $remaining - (float) $payment->amount_paid);
                    DB::table('customer_payment_transactions')->where('id', $payment->id)->update([
                        'transaction_remaining_amount' => $remaining,
                        'updated_at' => now(),
                    ]);
                }
            });
        } catch (\Illuminate\Validation\ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            return back()->withInput()->with('error', 'تعذر تحديث فاتورة المبيعات. تحقق من البيانات وحاول مرة أخرى.');
        }

        return redirect()->route('all_sales')->with('success', 'تم تحديث فاتورة المبيعات بنجاح.');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $invoice = DB::table('sales_invoices')
                    ->where('id', $id)
                    ->whereNull('deleted_at')
                    ->lockForUpdate()
                    ->first();

                if (! $invoice) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'invoice' => 'فاتورة المبيعات غير موجودة أو تم حذفها بالفعل.',
                    ]);
                }

                $soldQuantities = DB::table('sales_invoice_details')
                    ->where('invoice_id', $invoice->id)
                    ->whereNull('deleted_at')
                    ->select('product_id')
                    ->selectRaw('SUM(quantity) as quantity')
                    ->groupBy('product_id')
                    ->pluck('quantity', 'product_id');

                $returnedQuantities = DB::table('sales_return_invoice_details as details')
                    ->join('sales_return_invoices as returns', 'returns.id', '=', 'details.return_invoice_id')
                    ->where('details.original_invoice_id', $invoice->id)
                    ->whereNull('details.deleted_at')
                    ->whereNull('returns.deleted_at')
                    ->select('details.product_id')
                    ->selectRaw('SUM(details.quantity) as quantity')
                    ->groupBy('details.product_id')
                    ->pluck('quantity', 'product_id');

                $productIds = $soldQuantities->keys()->merge($returnedQuantities->keys())->unique()->values();
                $products = DB::table('products')->whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');
                $now = now();

                foreach ($productIds as $productId) {
                    $quantityToRestore = (int) ($soldQuantities[$productId] ?? 0) - (int) ($returnedQuantities[$productId] ?? 0);
                    if ($quantityToRestore < 0 && $products->has($productId)
                        && (int) ($products->get($productId)->quantity ?? 0) < abs($quantityToRestore)) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'invoice' => 'لا يمكن إلغاء الفاتورة لأن المخزون الحالي لا يكفي لعكس كمية مرتجعة سابقة.',
                        ]);
                    }
                    if ($quantityToRestore !== 0 && $products->has($productId)) {
                        DB::table('products')->where('id', $productId)->update([
                            'quantity' => DB::raw('COALESCE(quantity, 0) + ' . $quantityToRestore),
                            'updated_at' => $now,
                        ]);
                    }
                }

                DB::table('customer_payment_transactions')
                    ->where('sales_invoice_id', $invoice->id)
                    ->whereNull('deleted_at')
                    ->update(['deleted_at' => $now, 'updated_at' => $now]);

                DB::table('sales_return_invoice_details')
                    ->where('original_invoice_id', $invoice->id)
                    ->whereNull('deleted_at')
                    ->update(['deleted_at' => $now, 'updated_at' => $now]);

                DB::table('sales_return_invoices')
                    ->where('original_invoice_id', $invoice->id)
                    ->whereNull('deleted_at')
                    ->update(['deleted_at' => $now, 'updated_at' => $now]);

                DB::table('sales_invoice_details')
                    ->where('invoice_id', $invoice->id)
                    ->whereNull('deleted_at')
                    ->update(['deleted_at' => $now, 'updated_at' => $now]);

                DB::table('sales_invoices')->where('id', $invoice->id)->update([
                    'deleted_at' => $now,
                    'updated_at' => $now,
                ]);
            });
        } catch (\Illuminate\Validation\ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            return back()->with('error', 'تعذر حذف فاتورة المبيعات وإلغاء أثرها. حاول مرة أخرى.');
        }

        return redirect()->route('all_sales')->with('success', 'تم حذف الفاتورة وإعادة صافي الكمية وإلغاء أثرها المالي.');
    }
}
