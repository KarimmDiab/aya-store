<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesReturnsController extends Controller
{
    public function index(Request $request)
    {
        $invoiceId = trim((string) $request->query('invoice_id', ''));
        $sourceInvoice = null;
        $sourceItems = collect();

        if ($invoiceId !== '' && ctype_digit($invoiceId)) {
            $sourceInvoice = DB::table('sales_invoices')
                ->leftJoin('customers', 'sales_invoices.customer_id', '=', 'customers.id')
                ->where('sales_invoices.id', (int) $invoiceId)
                ->whereNull('sales_invoices.deleted_at')
                ->select(
                    'sales_invoices.id',
                    'sales_invoices.created_at',
                    'sales_invoices.net_total',
                    'customers.name as customer_name',
                    'customers.contact_info as customer_phone'
                )
                ->first();

            if ($sourceInvoice) {
                $sourceItems = DB::table('sales_invoice_details')
                    ->join('products', 'sales_invoice_details.product_id', '=', 'products.id')
                    ->where('sales_invoice_details.invoice_id', $sourceInvoice->id)
                    ->whereNull('sales_invoice_details.deleted_at')
                    ->select(
                        'sales_invoice_details.product_id',
                        'products.name as product_name',
                        DB::raw('SUM(sales_invoice_details.quantity) as sold_quantity'),
                        DB::raw('SUM(sales_invoice_details.quantity * sales_invoice_details.unit_price) as sold_total')
                    )
                    ->groupBy('sales_invoice_details.product_id', 'products.name')
                    ->get();

                $returnedQuantities = DB::table('sales_return_invoice_details')
                    ->where('original_invoice_id', $sourceInvoice->id)
                    ->whereNull('deleted_at')
                    ->select('product_id')
                    ->selectRaw('SUM(quantity) as returned_quantity')
                    ->groupBy('product_id')
                    ->pluck('returned_quantity', 'product_id');

                $sourceItems = $sourceItems->map(function ($item) use ($returnedQuantities) {
                    $item->returned_quantity = (int) ($returnedQuantities[$item->product_id] ?? 0);
                    $item->available_quantity = max(0, (int) $item->sold_quantity - $item->returned_quantity);
                    $item->unit_price = $item->sold_quantity > 0
                        ? round($item->sold_total / $item->sold_quantity, 2)
                        : 0;

                    return $item;
                });
            }
        }

        $returns = DB::table('sales_return_invoices')
            ->leftJoin('customers', 'sales_return_invoices.customer_id', '=', 'customers.id')
            ->whereNull('sales_return_invoices.deleted_at')
            ->select(
                'sales_return_invoices.id',
                'sales_return_invoices.original_invoice_id',
                'sales_return_invoices.total_amount',
                'sales_return_invoices.reason',
                'sales_return_invoices.created_at',
                'customers.name as customer_name'
            )
            ->orderByDesc('sales_return_invoices.id')
            ->paginate(20, ['*'], 'returns_page')
            ->withQueryString();

        return view('sales.returns', compact('invoiceId', 'sourceInvoice', 'sourceItems', 'returns'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'original_invoice_id' => 'required|integer|min:1',
            'items' => 'required|array',
            'items.*' => 'nullable|integer|min:0',
            'reason' => 'nullable|string|max:500',
        ], [
            'original_invoice_id.required' => 'رقم فاتورة البيع مطلوب.',
            'items.required' => 'حدد كمية صنف واحد على الأقل لإرجاعه.',
        ]);

        try {
            $returnId = DB::transaction(function () use ($validated) {
                $invoice = DB::table('sales_invoices')
                    ->where('id', $validated['original_invoice_id'])
                    ->whereNull('deleted_at')
                    ->lockForUpdate()
                    ->first();

                if (! $invoice) {
                    throw ValidationException::withMessages([
                        'original_invoice_id' => 'فاتورة البيع غير موجودة أو محذوفة.',
                    ]);
                }

                $soldItems = DB::table('sales_invoice_details')
                    ->join('products', 'sales_invoice_details.product_id', '=', 'products.id')
                    ->where('sales_invoice_details.invoice_id', $invoice->id)
                    ->whereNull('sales_invoice_details.deleted_at')
                    ->select(
                        'sales_invoice_details.product_id',
                        DB::raw('SUM(sales_invoice_details.quantity) as sold_quantity'),
                        DB::raw('SUM(sales_invoice_details.quantity * sales_invoice_details.unit_price) as sold_total')
                    )
                    ->groupBy('sales_invoice_details.product_id')
                    ->get()
                    ->keyBy('product_id');

                $returnedQuantities = DB::table('sales_return_invoice_details')
                    ->where('original_invoice_id', $invoice->id)
                    ->whereNull('deleted_at')
                    ->select('product_id')
                    ->selectRaw('SUM(quantity) as returned_quantity')
                    ->groupBy('product_id')
                    ->pluck('returned_quantity', 'product_id');

                $lines = [];
                $total = 0;

                foreach ($validated['items'] as $productId => $quantity) {
                    $quantity = (int) ($quantity ?? 0);
                    if ($quantity === 0) {
                        continue;
                    }

                    $soldItem = $soldItems->get((int) $productId);
                    $alreadyReturned = (int) ($returnedQuantities[$productId] ?? 0);
                    $remaining = $soldItem
                        ? (int) $soldItem->sold_quantity - $alreadyReturned
                        : 0;

                    if (! $soldItem || $quantity > $remaining) {
                        throw ValidationException::withMessages([
                            'items' => 'كمية المرتجع أكبر من الكمية المتبقية من أحد أصناف الفاتورة.',
                        ]);
                    }

                    $unitPrice = round($soldItem->sold_total / $soldItem->sold_quantity, 2);
                    $lineTotal = round($unitPrice * $quantity, 2);
                    $total += $lineTotal;
                    $lines[] = [
                        'product_id' => (int) $productId,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_amount' => $lineTotal,
                    ];
                }

                if ($lines === []) {
                    throw ValidationException::withMessages([
                        'items' => 'أدخل كمية مرتجع أكبر من صفر لصنف واحد على الأقل.',
                    ]);
                }

                $invoiceDeduction = (float) ($invoice->deduction ?? 0);
                $previousReturnDiscount = (float) DB::table('sales_return_invoices')
                    ->where('original_invoice_id', $invoice->id)
                    ->whereNull('deleted_at')
                    ->sum('discount_amount');
                $remainingDeduction = max(0, $invoiceDeduction - $previousReturnDiscount);
                $discountAmount = (float) $invoice->total_amount > 0
                    ? min($remainingDeduction, round($invoiceDeduction * $total / (float) $invoice->total_amount, 2))
                    : 0;
                $creditedTotal = max(0, round($total - $discountAmount, 2));

                $now = now();
                $returnId = DB::table('sales_return_invoices')->insertGetId([
                    'original_invoice_id' => $invoice->id,
                    'customer_id' => $invoice->customer_id,
                    'user_id' => Auth::id(),
                    'total_amount' => $creditedTotal,
                    'discount_amount' => $discountAmount,
                    'reason' => $validated['reason'] ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                foreach ($lines as $line) {
                    DB::table('sales_return_invoice_details')->insert($line + [
                        'return_invoice_id' => $returnId,
                        'original_invoice_id' => $invoice->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    DB::table('products')
                        ->where('id', $line['product_id'])
                        ->update([
                            'quantity' => DB::raw('COALESCE(quantity, 0) + ' . (int) $line['quantity']),
                            'updated_at' => $now,
                        ]);
                }

                $totalReturns = (float) DB::table('sales_return_invoices')
                    ->where('original_invoice_id', $invoice->id)
                    ->whereNull('deleted_at')
                    ->sum('total_amount');
                $remainingAfterReturns = max(
                    0,
                    (float) $invoice->net_total - (float) $invoice->paid_amount - $totalReturns
                );
                $totalPaid = 0;
                $payments = DB::table('customer_payment_transactions')
                    ->where('sales_invoice_id', $invoice->id)
                    ->whereNull('deleted_at')
                    ->orderBy('payment_date')
                    ->orderBy('id')
                    ->get(['id', 'amount_paid']);

                foreach ($payments as $payment) {
                    $totalPaid += (float) $payment->amount_paid;
                    DB::table('customer_payment_transactions')->where('id', $payment->id)->update([
                        'transaction_remaining_amount' => max(0, $remainingAfterReturns - $totalPaid),
                        'updated_at' => $now,
                    ]);
                }

                return $returnId;
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'تعذر تسجيل المرتجع. حاول مرة أخرى.');
        }

        return redirect()->route('sales.returns.show', $returnId)
            ->with('success', 'تم تسجيل مرتجع المبيعات وإعادة الكمية إلى المخزون.');
    }

    public function show(int $id)
    {
        $return = DB::table('sales_return_invoices')
            ->leftJoin('customers', 'sales_return_invoices.customer_id', '=', 'customers.id')
            ->leftJoin('users', 'sales_return_invoices.user_id', '=', 'users.id')
            ->where('sales_return_invoices.id', $id)
            ->whereNull('sales_return_invoices.deleted_at')
            ->select(
                'sales_return_invoices.*',
                'customers.name as customer_name',
                'customers.contact_info as customer_phone',
                'users.name as user_name'
            )
            ->first();

        abort_unless($return, 404);

        $items = DB::table('sales_return_invoice_details')
            ->join('products', 'sales_return_invoice_details.product_id', '=', 'products.id')
            ->where('return_invoice_id', $id)
            ->whereNull('sales_return_invoice_details.deleted_at')
            ->select(
                'products.name as product_name',
                'sales_return_invoice_details.quantity',
                'sales_return_invoice_details.unit_price',
                'sales_return_invoice_details.total_amount'
            )
            ->get();

        return view('sales.return_invoice', compact('return', 'items'));
    }
}
