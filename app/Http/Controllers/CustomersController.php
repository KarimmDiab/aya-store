<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CustomersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $returnTotals = DB::table('sales_return_invoices')
            ->whereNull('deleted_at')
            ->select('original_invoice_id')
            ->selectRaw('SUM(total_amount) as total_returned')
            ->groupBy('original_invoice_id');

        $customers = DB::table('customers')
        ->leftJoin('sales_invoices', function ($join) {
            $join->on('customers.id', '=', 'sales_invoices.customer_id')
                ->whereNull('sales_invoices.deleted_at');
        })
        ->leftJoinSub($returnTotals, 'return_totals', function ($join) {
            $join->on('return_totals.original_invoice_id', '=', 'sales_invoices.id');
        })
        ->whereNull('customers.deleted_at')
        ->whereNull('sales_invoices.deleted_at')
        ->select(
            'customers.id',
            'customers.name',
            'customers.contact_info',
            'customers.updated_at',
            DB::raw('COUNT(sales_invoices.id) AS count_invoices'),
            DB::raw('COALESCE(SUM(GREATEST(COALESCE(sales_invoices.net_total, sales_invoices.total_amount - COALESCE(sales_invoices.deduction, 0)) - COALESCE(return_totals.total_returned, 0), 0)), 0) AS total_invoices')
        )
        ->groupBy('customers.id', 'customers.name', 'customers.contact_info', 'customers.updated_at')
        ->get();
    
        return view('customers.all_customers', compact('customers'));
    }


    public function creditCustomers()
    {
        $paymentTotals = DB::table('customer_payment_transactions')
            ->whereNull('deleted_at')
            ->select('sales_invoice_id')
            ->selectRaw('SUM(amount_paid) as total_paid')
            ->groupBy('sales_invoice_id');
        $returnTotals = DB::table('sales_return_invoices')
            ->whereNull('deleted_at')
            ->select('original_invoice_id')
            ->selectRaw('SUM(total_amount) as total_returned')
            ->groupBy('original_invoice_id');

        $invoiceBalances = DB::table('sales_invoices as invoices')
            ->leftJoinSub($paymentTotals, 'payments', function ($join) {
                $join->on('payments.sales_invoice_id', '=', 'invoices.id');
            })
            ->leftJoinSub($returnTotals, 'returns', function ($join) {
                $join->on('returns.original_invoice_id', '=', 'invoices.id');
            })
            ->whereNull('invoices.deleted_at')
            ->where('invoices.payment_method', 'credit')
            ->select('invoices.customer_id', 'invoices.id as invoice_id')
            ->selectRaw('GREATEST(COALESCE(invoices.net_total, invoices.total_amount - COALESCE(invoices.deduction, 0)) - COALESCE(invoices.paid_amount, 0) - COALESCE(payments.total_paid, 0) - COALESCE(returns.total_returned, 0), 0) as outstanding');

        $results = DB::query()
            ->fromSub($invoiceBalances, 'credit_balances')
            ->join('customers', 'customers.id', '=', 'credit_balances.customer_id')
            ->whereNull('customers.deleted_at')
            ->where('credit_balances.outstanding', '>', 0)
            ->select(
                'customers.id',
                'customers.name',
                'customers.contact_info',
                'customers.updated_at',
                DB::raw("'credit' as payment_method"),
                DB::raw('COUNT(credit_balances.invoice_id) AS count_invoices'),
                DB::raw('SUM(credit_balances.outstanding) AS total_invoices')
            )
            ->groupBy('customers.id', 'customers.name', 'customers.contact_info', 'customers.updated_at')
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
        $validated = $request->validate([
            'payment_date' => 'required|date',
            'amount_paid' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ], [
            'payment_date.required' => 'تاريخ الدفع مطلوب.',
            'payment_date.date' => 'يجب أن يكون تاريخ الدفع صحيحًا.',
            'amount_paid.required' => 'المبلغ المدفوع مطلوب.',
            'amount_paid.numeric' => 'يجب أن يكون المبلغ المدفوع رقمًا.',
            'amount_paid.min' => 'يجب أن يكون المبلغ المدفوع أكبر من صفر.',
        ]);

        try {
            DB::transaction(function () use ($validated, $sales_invoice_id, $customer_id) {
                $invoice = DB::table('sales_invoices')
                    ->where('id', $sales_invoice_id)
                    ->where('customer_id', $customer_id)
                    ->where('payment_method', 'credit')
                    ->whereNull('deleted_at')
                    ->lockForUpdate()
                    ->first();

                if (! $invoice) {
                    throw ValidationException::withMessages(['invoice' => 'فاتورة الأجل غير موجودة لهذا العميل.']);
                }

                $returns = (float) DB::table('sales_return_invoices')
                    ->where('original_invoice_id', $invoice->id)
                    ->whereNull('deleted_at')
                    ->sum('total_amount');
                $previousPayments = (float) DB::table('customer_payment_transactions')
                    ->where('customer_id', $customer_id)
                    ->where('sales_invoice_id', $invoice->id)
                    ->whereNull('deleted_at')
                    ->sum('amount_paid');
                $invoiceNet = (float) ($invoice->net_total ?? ($invoice->total_amount - $invoice->deduction));
                $outstanding = max(0, $invoiceNet - (float) $invoice->paid_amount - $returns - $previousPayments);

                if ((float) $validated['amount_paid'] > $outstanding) {
                    throw ValidationException::withMessages([
                        'amount_paid' => 'مبلغ السداد أكبر من المديونية المتبقية بعد احتساب المرتجعات.',
                    ]);
                }

                DB::table('customer_payment_transactions')->insert([
                    'sales_invoice_id' => $invoice->id,
                    'customer_id' => $customer_id,
                    'payment_date' => $validated['payment_date'],
                    'amount_paid' => $validated['amount_paid'],
                    'transaction_remaining_amount' => max(0, $outstanding - (float) $validated['amount_paid']),
                    'notes' => $validated['notes'] ?? null,
                    'user_id' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $remaining = max(0, $invoiceNet - (float) $invoice->paid_amount - $returns);
                $payments = DB::table('customer_payment_transactions')
                    ->where('customer_id', $customer_id)
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
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            return back()->with('error', 'تعذر تسجيل حركة السداد. حاول مرة أخرى.');
        }

        return back()->with('success', 'تمت إضافة حركة السداد بنجاح.');
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
        $paymentTotals = DB::table('customer_payment_transactions')
            ->whereNull('deleted_at')
            ->select('sales_invoice_id')
            ->selectRaw('SUM(amount_paid) as total_paid')
            ->groupBy('sales_invoice_id');
        $returnTotals = DB::table('sales_return_invoices')
            ->whereNull('deleted_at')
            ->select('original_invoice_id')
            ->selectRaw('SUM(total_amount) as total_returned')
            ->groupBy('original_invoice_id');

        $customerInvoices = DB::table('sales_invoices')
            ->join('customers', 'customers.id', '=', 'sales_invoices.customer_id')
            ->leftJoin('users', 'users.id', '=', 'sales_invoices.user_id')
            ->leftJoinSub($paymentTotals, 'payment_totals', function ($join) {
                $join->on('payment_totals.sales_invoice_id', '=', 'sales_invoices.id');
            })
            ->leftJoinSub($returnTotals, 'return_totals', function ($join) {
                $join->on('return_totals.original_invoice_id', '=', 'sales_invoices.id');
            })
            ->select(
                'customers.id',
                'customers.name',
                'customers.contact_info',
                'sales_invoices.created_at as invoice_date',
                'sales_invoices.payment_method',
                'sales_invoices.paid_amount',
                'sales_invoices.updated_at',
                'sales_invoices.id as sales_invoice_id',
                'sales_invoices.total_amount',
                'sales_invoices.created_at',
                'sales_invoices.deduction',
                'sales_invoices.net_total',
                'sales_invoices.remaining_amount',
                'users.name as username',
                DB::raw('COALESCE(payment_totals.total_paid, 0) as total_paid_transactions'),
                DB::raw('COALESCE(return_totals.total_returned, 0) as total_returned'),
                DB::raw('COALESCE(sales_invoices.paid_amount, 0) + COALESCE(payment_totals.total_paid, 0) as total_paid_before_returns'),
                DB::raw('LEAST(COALESCE(return_totals.total_returned, 0), COALESCE(sales_invoices.paid_amount, 0) + COALESCE(payment_totals.total_paid, 0)) as paid_amount_deducted_for_returns'),
                DB::raw('GREATEST(COALESCE(sales_invoices.paid_amount, 0) + COALESCE(payment_totals.total_paid, 0) - COALESCE(return_totals.total_returned, 0), 0) as total_paid_after_returns'),
                DB::raw('GREATEST(COALESCE(sales_invoices.net_total, sales_invoices.total_amount - COALESCE(sales_invoices.deduction, 0)) - COALESCE(return_totals.total_returned, 0), 0) as adjusted_total'),
                DB::raw('GREATEST(COALESCE(sales_invoices.net_total, sales_invoices.total_amount - COALESCE(sales_invoices.deduction, 0)) - COALESCE(sales_invoices.paid_amount, 0) - COALESCE(payment_totals.total_paid, 0) - COALESCE(return_totals.total_returned, 0), 0) as outstanding_balance')
            )
            ->whereNull('sales_invoices.deleted_at')
            ->where('customers.id', $id)
            ->orderByDesc('sales_invoices.id')
            ->get();

        $creditInvoices = $customerInvoices
            ->where('payment_method', 'credit')
            ->values();
        $customerInfo = DB::table('customers')
            ->select('name', 'contact_info', 'id')
            ->whereNull('deleted_at')
            ->where('id', $id)
            ->first();
        $invoicesInfo = (object) [
            'invoices_number' => $customerInvoices->count(),
            'total_all_invoices' => $customerInvoices->sum('adjusted_total'),
            'total_returned' => $customerInvoices->sum('total_returned'),
        ];
        $noOfCredit = $creditInvoices->where('outstanding_balance', '>', 0)->count();

        $customerReturns = DB::table('sales_return_invoices')
            ->where('customer_id', $id)
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->get([
                'id', 'original_invoice_id', 'total_amount', 'discount_amount',
                'reason', 'created_at',
            ]);
        $customerReturnsByInvoice = $customerReturns->groupBy('original_invoice_id');
        $returnDetailsByReturnId = DB::table('sales_return_invoice_details')
            ->leftJoin('products', 'sales_return_invoice_details.product_id', '=', 'products.id')
            ->whereIn('sales_return_invoice_details.return_invoice_id', $customerReturns->pluck('id'))
            ->whereNull('sales_return_invoice_details.deleted_at')
            ->select(
                'sales_return_invoice_details.return_invoice_id',
                DB::raw("COALESCE(products.name, 'منتج محذوف') as product_name"),
                'sales_return_invoice_details.quantity',
                'sales_return_invoice_details.unit_price',
                'sales_return_invoice_details.total_amount'
            )
            ->get()
            ->groupBy('return_invoice_id');

        $invoiceDetails = DB::table('sales_invoice_details')
            ->join('products', 'sales_invoice_details.product_id', '=', 'products.id')
            ->whereIn('sales_invoice_details.invoice_id', $customerInvoices->pluck('sales_invoice_id'))
            ->whereNull('sales_invoice_details.deleted_at')
            ->select(
                'sales_invoice_details.invoice_id',
                'products.name as product_name',
                'sales_invoice_details.quantity',
                'sales_invoice_details.unit_price',
                'sales_invoice_details.total_amount as total_product'
            )
            ->get()
            ->groupBy('invoice_id');

        $cstTransaction = DB::table('customer_payment_transactions')
            ->join('sales_invoices', 'customer_payment_transactions.sales_invoice_id', '=', 'sales_invoices.id')
            ->leftJoin('users', 'sales_invoices.user_id', '=', 'users.id')
            ->where('customer_payment_transactions.customer_id', $id)
            ->whereNull('customer_payment_transactions.deleted_at')
            ->whereNull('sales_invoices.deleted_at')
            ->orderBy('customer_payment_transactions.payment_date')
            ->orderBy('customer_payment_transactions.id')
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
            ->get();
        $transaction = $cstTransaction;
        return view('customers.customer_profile', compact(
            'customerInvoices' , 'customerInfo' , 
            'invoicesInfo' , 'noOfCredit' , 
            'invoiceDetails' , 'creditInvoices' ,
             'cstTransaction' , 'transaction',
             'customerReturnsByInvoice', 'returnDetailsByReturnId' ));
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
            DB::transaction(function () use ($validated, $customerId, $invoice_id, $transactionId) {
                $invoice = DB::table('sales_invoices')->where('id', $invoice_id)
                    ->where('customer_id', $customerId)->whereNull('deleted_at')->lockForUpdate()->first();
                $payment = DB::table('customer_payment_transactions')->where('id', $transactionId)
                    ->where('customer_id', $customerId)->where('sales_invoice_id', $invoice_id)
                    ->whereNull('deleted_at')->first();

                if (! $invoice || ! $payment) {
                    throw ValidationException::withMessages(['amount_paid' => 'الفاتورة أو حركة السداد غير موجودة.']);
                }

                $baseDue = max(0, (float) ($invoice->net_total ?? ((float) $invoice->total_amount - (float) $invoice->deduction)) - (float) $invoice->paid_amount);
                $returns = (float) DB::table('sales_return_invoices')
                    ->where('original_invoice_id', $invoice_id)
                    ->whereNull('deleted_at')
                    ->sum('total_amount');
                $available = max(0, $baseDue - $returns);
                $otherPayments = (float) DB::table('customer_payment_transactions')
                    ->where('customer_id', $customerId)->where('sales_invoice_id', $invoice_id)
                    ->whereNull('deleted_at')->where('id', '<>', $transactionId)->sum('amount_paid');

                if ((float) $validated['amount_paid'] + $otherPayments > $available + 0.00001) {
                    throw ValidationException::withMessages(['amount_paid' => 'قيمة الدفعات تتجاوز المديونية المتبقية بعد احتساب المرتجعات.']);
                }

                DB::table('customer_payment_transactions')->where('id', $transactionId)->update([
                    'payment_date' => $validated['payment_date'], 'amount_paid' => $validated['amount_paid'],
                    'notes' => $validated['notes'] ?? null, 'updated_at' => now(),
                ]);

                $remaining = $available;
                $payments = DB::table('customer_payment_transactions')->where('customer_id', $customerId)
                    ->where('sales_invoice_id', $invoice_id)->whereNull('deleted_at')
                    ->orderBy('payment_date')->orderBy('id')->get(['id', 'amount_paid']);
                foreach ($payments as $row) {
                    $remaining = max(0, $remaining - (float) $row->amount_paid);
                    DB::table('customer_payment_transactions')->where('id', $row->id)->update([
                        'transaction_remaining_amount' => $remaining, 'updated_at' => now(),
                    ]);
                }
            });

            return redirect()->back()->with('success', 'تم تحديث حركة السداد بنجاح.');
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            return redirect()->back()->with('error', 'حدث خطأ أثناء تحديث حركة السداد.');
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
