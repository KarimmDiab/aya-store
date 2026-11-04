<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CustomersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = DB::table('customers')
        ->join('sales_invoices', 'customers.id', '=', 'sales_invoices.customer_id')
        ->whereNull('customers.deleted_at')
        ->select(
            'customers.id',
            'customers.name',
            'customers.contact_info',
            'customers.updated_at',
            DB::raw('COUNT(sales_invoices.customer_id) AS count_invoices'),
            DB::raw('SUM(sales_invoices.total_amount) AS total_invoices')
        )
        ->groupBy('sales_invoices.customer_id')
        ->whereNull('sales_invoices.deleted_at')
        ->get();
    
        return view('customers.all_customers', compact('customers'));
    }


    public function creditCustomers()
    {
        $results = DB::table('customers')
        ->join('sales_invoices', 'customers.id', '=', 'sales_invoices.customer_id')
        ->whereNull('customers.deleted_at')
        ->where('sales_invoices.payment_method', 'credit')
        ->select(
            'customers.id',
            'customers.name',
            'customers.contact_info',
            'customers.updated_at',
            'sales_invoices.payment_method',
            DB::raw('COUNT(sales_invoices.customer_id) AS count_invoices'),
            DB::raw('SUM(sales_invoices.total_amount) AS total_invoices')
        )
        ->groupBy(
            'customers.id', 
            'customers.name', 
            'customers.contact_info', 
            'customers.updated_at', 
            'sales_invoices.payment_method'
        )
        ->whereNull('sales_invoices.deleted_at')
        ->get();

        return view('customers.credit_customers', compact('results'));
    }



    public function printInvoice($salesInvoiceId , $customer_id)
    {
        // Retrieve the invoice details
        $invoice = DB::table('sales_invoices')
                    ->where('id', $salesInvoiceId)
                    ->where('customer_id', $customer_id)
                    ->first();
        
        // Check if invoice exists
        if (!$invoice) {
            return redirect()->back()->with('error', 'Invoice not found.');
        }
        
        // Retrieve customer details
        $customer = DB::table('customers')
                    ->where('id', $invoice->customer_id)
                    ->select('id', 'name', 'contact_info as phone') // Add the phone number column
                    ->first();
        
        // Check if customer exists
        if (!$customer) {
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
            'finalTotal' => $finalTotal
        ]);
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

     public function store(Request $request, $sales_invoice_id, $customer_id)
     {
         $messages = [
             'payment_date.required' => 'تاريخ الدفع مطلوب.',
             'payment_date.date' => 'يجب أن يكون تاريخ الدفع صحيحًا.',
             'amount_paid.required' => 'المبلغ المدفوع مطلوب.',
             'amount_paid.numeric' => 'يجب أن يكون المبلغ المدفوع رقمًا.',
             'amount_paid.min' => 'يجب أن يكون المبلغ المدفوع أكبر من 0.',
             'notes.string' => 'يجب أن تكون الملاحظات نصًا.',
         ];
     
         $request->validate([
             'payment_date' => 'required|date',
             'amount_paid' => 'required|numeric|min:0.01',
             'notes' => 'nullable|string',
         ], $messages);
     
         // التأكد من أن الفاتورة والعميل موجودان
         $salesInvoiceExists = DB::table('sales_invoices')->where('id', $sales_invoice_id)->exists();
         $customerExists = DB::table('customers')->where('id', $customer_id)->exists();
     
         if (!$salesInvoiceExists || !$customerExists) {
             return back()->withErrors(['error' => 'رقم الفاتورة أو العميل غير موجود.']);
         }
     
         // جلب إجمالي المبلغ المتبقي للفاتورة
         $totalAmount = DB::table('sales_invoices')->where('id', $sales_invoice_id)->value('remaining_amount');
     
         // حساب إجمالي المدفوع حتى الآن
         $totalPaid = (int) DB::table('customer_payment_transactions')
             ->where('customer_id', $customer_id)
             ->where('sales_invoice_id', $sales_invoice_id)
             ->sum('amount_paid');
     
         // حساب المبلغ المتبقي بعد السداد الجديد
         $finalRemaining = $totalAmount - ($totalPaid + $request->amount_paid);
     
         // إدراج عملية الدفع في جدول الحركات
        DB::table('customer_payment_transactions')->insert([
            'sales_invoice_id' => $sales_invoice_id,
            'customer_id' => $customer_id,
            'payment_date' => $request->payment_date,
            'amount_paid' => $request->amount_paid,
            'transaction_remaining_amount' => max($finalRemaining, 0), // التأكد من أن المبلغ لا يكون سالبًا
            'notes' => $request->notes,
            'user_id' => Auth::id(), // إضافة معرف المستخدم الحالي
            'created_at' => now(),
            'updated_at' => now(),
        ]);

     
         return back()->with('success', 'تمت إضافة حركة السداد بنجاح!');
     }
     
    

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            // Decrypt the ID
            $id = decrypt($id);
        } catch (DecryptException $e) {
            // If decryption fails (invalid token), handle the error
            return redirect()->route('error.page')->with('error', 'رمز العميل غير صالح');
        }
    
        // Find the customer by decrypted ID
        $customer = DB::table('customers')->where('id', $id)->first();
    
        if (!$customer) {
            // If customer not found, redirect to error page
            return redirect()->route('error.page')->with('error', 'العميل غير موجود');
        }
        $customerInvoices = DB::table('sales_invoices')
            ->join('customers', 'customers.id', '=', 'sales_invoices.customer_id')
            ->join('users', 'users.id', '=', 'sales_invoices.user_id')
            ->select(
                'customers.id',
                'customers.name',
                'customers.contact_info',
                'sales_invoices.created_at AS invoice_date',
                'sales_invoices.payment_method',
                'sales_invoices.paid_amount',
                'sales_invoices.updated_at',
                'sales_invoices.id AS sales_invoice_id',
                'sales_invoices.total_amount AS total_amount',
                'sales_invoices.created_at AS created_at',
                'sales_invoices.deduction',
                'sales_invoices.net_total',
                'sales_invoices.remaining_amount',
                'users.name AS username'
            )
            ->whereNull('sales_invoices.deleted_at')
            ->where('customers.id', $id)
            ->get();

            $creditInvoices = DB::table('sales_invoices')
            ->join('customers', 'customers.id', '=', 'sales_invoices.customer_id')
            ->join('users', 'users.id', '=', 'sales_invoices.user_id')
            ->leftJoin(
                DB::raw('(SELECT sales_invoice_id, SUM(amount_paid) as total_paid FROM customer_payment_transactions GROUP BY sales_invoice_id) as transactions'),
                'transactions.sales_invoice_id',
                '=',
                'sales_invoices.id'
            )
            ->select(
                'customers.id',
                'customers.name',
                'customers.contact_info',
                'sales_invoices.created_at AS invoice_date',
                'sales_invoices.payment_method',
                'sales_invoices.paid_amount',
                'sales_invoices.updated_at',
                'sales_invoices.id AS sales_invoice_id',
                'sales_invoices.total_amount AS total_amount',
                'sales_invoices.created_at AS created_at',
                'sales_invoices.deduction',
                'sales_invoices.net_total',
                'sales_invoices.remaining_amount',
                'users.name AS username',
                DB::raw('COALESCE(transactions.total_paid, 0) as total_paid')
            )
            ->whereNull('sales_invoices.deleted_at')
            ->where('customers.id', $id)
            ->where('sales_invoices.payment_method', 'credit')
            ->get();
        
    
        //if ($customerInvoices->isEmpty()) {
            // Optional: Show a message or redirect if no data is found
            //return redirect()->back()->with('error', 'No invoices found for this customer.');
       // }

        $customerInfo = DB::table('customers')
            ->select(
                'customers.name',
                'customers.contact_info',
                'customers.id'
            
            )
            ->whereNull('customers.deleted_at')
            ->where('customers.id' , $id)
            ->first();

        $invoicesInfo = DB::table('sales_invoices')
            ->selectRaw('COUNT(sales_invoices.id) AS invoices_number, SUM(sales_invoices.total_amount) AS total_all_invoices')
            ->where('sales_invoices.customer_id', $id)
            ->whereNull('deleted_at')
            ->first(); // Fetch a single record

        $noOfCredit = DB::table('sales_invoices')
            ->where('sales_invoices.payment_method', 'credit')
            ->where('sales_invoices.customer_id', $id)
            ->whereNull('deleted_at')
            ->count();

        $invoiceDetails = DB::table('sales_invoice_details')
            ->join('products', 'sales_invoice_details.product_id', '=', 'products.id')
            ->select(
            'sales_invoice_details.invoice_id',
            'products.name as product_name',
            'sales_invoice_details.quantity',
            'sales_invoice_details.unit_price',
            'sales_invoice_details.total_amount as total_product'
            )
            ->get();

            $cstTransaction = DB::table('customer_payment_transactions')
            ->select(
                'customer_payment_transactions.id',
                'sales_invoices.id as sales_invoice_id',
                'sales_invoices.remaining_amount as sales_invoice_remaining_amount',
                'users.name as user_name',
                'customer_payment_transactions.payment_date',
                'customer_payment_transactions.amount_paid',
                'customer_payment_transactions.transaction_remaining_amount',
                'customer_payment_transactions.notes',
                'sales_invoices.customer_id',
                'sales_invoices.created_at'
            )
            ->join('sales_invoices', 'customer_payment_transactions.sales_invoice_id', '=', 'sales_invoices.id')
            ->join('users', 'sales_invoices.user_id', '=', 'users.id')
            ->where('customer_payment_transactions.customer_id', $id)
            ->get();

            
            $transaction = DB::table('customer_payment_transactions')
            ->select(
                'customer_payment_transactions.id',
                'sales_invoices.id as sales_invoice_id',
                'sales_invoices.remaining_amount as sales_invoice_remaining_amount',
                'users.name as user_name',
                'customer_payment_transactions.payment_date',
                'customer_payment_transactions.amount_paid',
                'customer_payment_transactions.transaction_remaining_amount',
                'customer_payment_transactions.notes',
                'sales_invoices.customer_id',
                'sales_invoices.created_at'
            )
            ->join('sales_invoices', 'customer_payment_transactions.sales_invoice_id', '=', 'sales_invoices.id')
            ->join('users', 'sales_invoices.user_id', '=', 'users.id')
            ->where('customer_payment_transactions.customer_id', $id)
            ->get();
        
        
    
    
        return view('customers.customer_profile', compact(
            'customerInvoices' , 'customerInfo' , 
            'invoicesInfo' , 'noOfCredit' , 
            'invoiceDetails' , 'creditInvoices' ,
             'cstTransaction' , 'transaction' ));
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
        try {
            // Validate input
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'regex:/^[\p{Arabic}A-Za-z0-9\s\-]+$/u', // Arabic, English, numbers, hyphens, spaces
                    'max:255'
                ],
                'phone' => [
                    'required',
                    'string',
                    'regex:/^01[0-9]{9}$/', // Starts with 01 and has 11 digits
                ],
            ], [
                // Custom error messages for `name`
                'name.required' => 'اسم المجموعة مطلوب.',
                'name.regex' => 'اسم المجموعة يجب أن يحتوي فقط على أحرف عربية أو إنجليزية، أرقام، شرطات، ومسافات.',
                'name.max' => 'اسم المجموعة يجب ألا يتجاوز 255 حرفًا.',
            
                // Custom error messages for `phone`
                'phone.required' => 'رقم الهاتف مطلوب.',
                'phone.regex' => 'رقم الهاتف يجب أن يبدأ بـ 01 ويتكون من 11 رقمًا.',
            ]);
    
            // Update record in the database
            DB::table('customers')
                ->where('id', $id)
                ->update([
                    'name' => $request->name,
                    'contact_info' => $request->phone,
                    'updated_at' => now() // Update the timestamp
                ]);
    
            // Redirect with success message
            return redirect()->back()->with('success', 'تم تحديث بيانات العميل بنجاح.');
        } catch (\Exception $e) {
            // Handle errors and redirect with error message
            return redirect()->back()->with('error', 'حدث خطأ أثناء تحديث البيانات: ' . $e->getMessage());
        }
    }




    public function customerTransaction(Request $request, $customerId, $invoice_id , $transactionId)
    {
        $validated = $request->validate(
            [
                'payment_date' => 'required|date',
                'amount_paid' => 'required|numeric|min:0',
                //'transaction_remaining_amount' => 'required|numeric|min:0',
                'notes' => 'nullable|string',
            ],
            [
                'payment_date.required' => 'تاريخ السداد مطلوب.',
                'payment_date.date' => 'يرجى إدخال تاريخ صحيح.',
                'amount_paid.required' => 'المبلغ المدفوع مطلوب.',
                'amount_paid.numeric' => 'يرجى إدخال المبلغ المدفوع بشكل صحيح.',
                //'transaction_remaining_amount.required' => 'المبلغ المتبقي مطلوب.',
                //'transaction_remaining_amount.numeric' => 'يرجى إدخال المبلغ المتبقي بشكل صحيح.',
            ]
        );
    
        try {
            // تحديث سجل الدفع في جدول customer_payment_transactions
            $updated = DB::table('customer_payment_transactions')
                ->where('id', $transactionId)
                ->where('customer_id', $customerId)
                ->where('sales_invoice_id', $invoice_id)
                ->update([
                    'payment_date' => $validated['payment_date'],
                    'amount_paid' => $validated['amount_paid'],
                    'notes' => $validated['notes'] ?? null,
                    'updated_at' => now(),
                ]);
    
                $totalPaid = (int) DB::table('customer_payment_transactions')
                ->where('customer_id', $customerId)
                ->where('sales_invoice_id', $invoice_id)
                ->sum('amount_paid');
            
            
            // جلب المبلغ المتبقي للفاتورة 
            $totalAmount = DB::table('sales_invoices')
                ->where('id', $invoice_id)
                ->value('remaining_amount');
    
            // حساب المبلغ المتبقي بعد السداد
            $finalRemaining = $totalAmount - $totalPaid;
    
            // تحديث المبلغ المتبقي في جدول الفواتير (sales_invoices)
            DB::table('customer_payment_transactions')
                ->where('id', $transactionId)
                ->update([
                    'transaction_remaining_amount' => max($finalRemaining, 0), // التأكد من أن المبلغ المتبقي لا يكون سالبًا
                    'updated_at' => now(),
                ]);
    
            if ($updated) {
                if ($finalRemaining > $totalAmount && $finalRemaining < 0) {
                    return redirect()->back()->with('warning', 'تنبيه: المبلغ المدفوع أكبر من قيمة الفاتورة!');
                } else {
                    return redirect()->back()->with('success', 'تم تحديث حركة السداد بنجاح.');
                }
            } else {
                return redirect()->back()->with('error', 'لم يتم العثور على حركة السداد أو لم يتم تحديثها.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء تحديث حركة السداد: ' . $e->getMessage());
        }
    }
    
    
    
    
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            // Soft delete the customer using the DB facade
            DB::table('customers')
                ->whereIn('id', explode(',', $id)) // Allows for multiple IDs if needed
                ->update(['deleted_at' => now()]);

            // Redirect back with success message
            return redirect()->back()->with('success', 'تم حذف بيانات العميل بنجاح.');
        } catch (\Exception $e) {
            // Handle errors and redirect with error message
            return redirect()->back()->with('error', 'حدث خطأ أثناء حذف بيانات العميل: ' . $e->getMessage());
        }
    }

}
