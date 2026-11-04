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
            ->get();
        $salesInvoiceIds = DB::table('sales_invoices')
            ->orderBy('created_at', 'desc')
            ->whereNull('deleted_at')
            ->pluck('id');
        $salesInvoices = DB::table('sales_invoices')
            ->join('customers', 'sales_invoices.customer_id', '=', 'customers.id')
            ->select(
                'sales_invoices.id as sales_invoice_id',
                'customers.id as customer_id',
                'customers.name as customer_name',
                'customers.contact_info as customer_phone',
                'sales_invoices.payment_method as payment_method',
                'sales_invoices.total_amount as total_invoice',
                'sales_invoices.paid_amount as invoice_paid_amount',
                'sales_invoices.remaining_amount as invoice_remaning_amount',
                'sales_invoices.created_at as invoice_date',
                'sales_invoices.updated_at as invoice_update'
            )
            ->whereNull('sales_invoices.deleted_at')
            ->get();

        $customers = DB::table('customers')
            ->whereNull('deleted_at')
            ->get();

        return view('sales.sales_invoice', compact('categories', 'products', 'salesInvoiceIds', 'salesInvoices', 'customers'));
    }

    public function allInvoices(Request $request)
    {
        $salesInvoices = DB::table('sales_invoices')
        ->join('customers', 'sales_invoices.customer_id', '=', 'customers.id')
        ->select(
            'sales_invoices.id as sales_invoice_id',
            'customers.id as customer_id',
            'customers.name as customer_name',
            'customers.contact_info as customer_phone',
            'sales_invoices.payment_method as payment_method',
            'sales_invoices.total_amount as total_invoice',
            'sales_invoices.paid_amount as invoice_paid_amount',
            'sales_invoices.remaining_amount as invoice_remaning_amount',
            'sales_invoices.created_at as invoice_date',
            'sales_invoices.updated_at as invoice_update',
    
            // ✅ total paid from transactions
            DB::raw('(
                SELECT COALESCE(SUM(customer_payment_transactions.amount_paid), 0)
                FROM customer_payment_transactions
                WHERE customer_payment_transactions.sales_invoice_id = sales_invoices.id
            ) as total_paid_transactions'),
    
            // ✅ adjusted remaining amount
            DB::raw('(
                sales_invoices.remaining_amount - (
                    SELECT COALESCE(SUM(customer_payment_transactions.amount_paid), 0)
                    FROM customer_payment_transactions
                    WHERE customer_payment_transactions.sales_invoice_id = sales_invoices.id
                )
            ) as adjusted_remaining_amount')
        )
        ->whereNull('sales_invoices.deleted_at')
        ->get();
        $products = DB::table('products')
            ->whereNull('deleted_at')
            ->get();
        $salesInvoicesDetails = DB::table('sales_invoices')
            ->select(
                'sales_invoices.id as sales_invoice_id',
                'customers.id as customer_id',
                'customers.name as customer_name',
                'customers.contact_info as customer_phone',
                'sales_invoices.payment_method as payment_method',
                'sales_invoices.total_amount as total_invoice',
                'sales_invoices.paid_amount as invoice_paid_amount',
                'sales_invoices.remaining_amount as invoice_remaning_amount',
                'sales_invoices.created_at as invoice_date',
                'sales_invoices.updated_at as invoice_update',
                'products.id as product_id',
                'products.name as product_name',
                'sales_invoice_details.quantity as product_quantity',
                'sales_invoice_details.unit_price as sales_price',
                'sales_invoice_details.total_amount as product_total_amount'
            )
            ->join('customers', 'sales_invoices.customer_id', '=', 'customers.id')
            ->rightJoin('sales_invoice_details', 'sales_invoices.id', '=', 'sales_invoice_details.invoice_id')
            ->join('products', 'sales_invoice_details.product_id', '=', 'products.id')
            ->whereNull('sales_invoices.deleted_at')
            ->get();

        return view('sales.all_sales_invoices', compact('salesInvoices', 'salesInvoicesDetails', 'products'));
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
            'customer.name.required' => 'اسم العميل مطلوب.',
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
            'customer.name' => 'required|string|max:255',
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

        try {
            // Validation passed
            $cart = $validated['cart'];
        
            // Calculate totals
            $grossTotal = array_sum(array_map(function ($item) {
                return $item['quantity'] * $item['price'];
            }, $cart));
        
            $deduction = is_numeric($validated['deduction']) ? $validated['deduction'] : 0;
            $netTotal = $grossTotal - $deduction;
        
            $remainingAmount = ($validated['paymentMethod'] === 'credit') ?
                max(0, $netTotal - ($validated['payAmount'] ?? 0)) : 0;
        
            // Start transaction
            DB::beginTransaction();
        
            // Handle optional phone
            $phone = $request['customer']['phone'] ?? null;
        
            $customer = null;
        
            if ($phone) {
                $customer = DB::table('customers')
                    ->where('contact_info', $phone)
                    ->first();
            }
        
            if (! $customer) {
                // Insert new customer
                $customerId = DB::table('customers')->insertGetId([
                    'name' => $validated['customer']['name'],
                    'contact_info' => $phone, // nullable
                    'created_at' => $request->datetime ? $request->datetime : now(),
                    'updated_at' => now(),
                ]);
            } else {
                $customerId = $customer->id;
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
            DB::rollBack();
        
            return back()->with('error', $e->getMessage());
        }catch (\Exception $e) {
            DB::rollBack();

            // Redirect back with error message
            return redirect()->back()
                ->with('error', 'حدث خطأ في تسجيل فاتورة المبيعات: '.$e->getMessage());
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

        // Check if customer exists
        if (! $customer) {
            return redirect()->back()->with('error', 'Customer data not found.');
        }

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
        // Begin a transaction to ensure data integrity
        DB::beginTransaction();

        try {
            // Find the sales invoice by its ID
            $invoice = DB::table('sales_invoices')->where('sales_invoice_id', $id)->first();

            if (! $invoice) {
                throw new \Exception('Invoice not found');
            }

            // Update the sales invoice data
            DB::table('sales_invoices')
                ->where('sales_invoice_id', $id)
                ->update([
                    'customer_name' => $request->edit_sales_customer_name,
                    'customer_phone' => $request->edit_sales_customer_phone,
                    'invoice_date' => $invoice->invoice_date, // Assuming this doesn't change
                    'payment_method' => $request->edit_sales_payment_method,
                    'invoice_paid_amount' => $request->edit_sales_amount_paid,
                    'invoice_remaning_amount' => $request->edit_sales_remaining_amount,
                    'total_invoice' => $request->edit_sales_invoices_amount,
                ]);

            // Loop through the invoice details and update them
            foreach ($request->product_name as $index => $productId) {
                $quantity = $request->quantity[$index];
                $unitPrice = $request->unit_price[$index];
                $total = $request->total[$index];

                // Find the corresponding sales invoice detail
                $invoiceDetail = DB::table('sales_invoice_details')
                    ->where('sales_invoice_id', $id)
                    ->where('product_id', $productId)
                    ->first();

                if ($invoiceDetail) {
                    // Update the sales invoice detail
                    DB::table('sales_invoice_details')
                        ->where('sales_invoice_id', $id)
                        ->where('product_id', $productId)
                        ->update([
                            'product_quantity' => $quantity,
                            'sales_price' => $unitPrice,
                            'product_total_amount' => $total,
                        ]);
                } else {
                    // If there's no existing invoice detail, create a new one
                    DB::table('sales_invoice_details')->insert([
                        'sales_invoice_id' => $id,
                        'product_id' => $productId,
                        'product_quantity' => $quantity,
                        'sales_price' => $unitPrice,
                        'product_total_amount' => $total,
                    ]);
                }
            }

            // Commit the transaction
            DB::commit();

            // Redirect back with a success message
            return redirect()->route('invoice.index')->with('success', 'Invoice updated successfully.');

        } catch (\Exception $e) {
            // If any error occurs, rollback the transaction
            DB::rollBack();

            // Return with error message
            return back()->with('error', 'An error occurred while updating the invoice.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // استرجاع تفاصيل الفاتورة من جدول sales_invoices_details
        $invoiceDetails = DB::table('sales_invoice_details')
            ->where('invoice_id', $id)
            ->get();

        // تحديث كميات المنتجات في جدول products
        foreach ($invoiceDetails as $detail) {
            DB::table('products')
                ->where('id', $detail->product_id)
                ->increment('quantity', $detail->quantity);
        }

        // تحديث `deleted_at` في جدول sales_invoices_details
        DB::table('sales_invoice_details')
            ->where('invoice_id', $id)
            ->update(['deleted_at' => now()]);

        // تحديث `deleted_at` في جدول sales_invoices
        DB::table('sales_invoices')
            ->where('id', $id)
            ->update(['deleted_at' => now()]);

        // توجيه المستخدم بعد الحذف
        return redirect()->route('all_sales')->with('success', 'تم حذف الفاتورة وإعادة الكميات بنجاح');
    }
}
