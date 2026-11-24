<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseReturnsController extends Controller
{
    public function index(Request $request)
    {
        $invoiceId = trim((string) $request->query('invoice_id', ''));
        $sourceInvoice = null;
        $sourceItems = collect();

        if ($invoiceId !== '' && ctype_digit($invoiceId)) {
            $sourceInvoice = DB::table('purchase_invoices')
                ->join('suppliers', 'purchase_invoices.supplier_id', '=', 'suppliers.id')
                ->where('purchase_invoices.id', (int) $invoiceId)
                ->whereNull('purchase_invoices.deleted_at')
                ->whereNull('suppliers.deleted_at')
                ->select('purchase_invoices.id', 'purchase_invoices.date', 'suppliers.name as supplier_name')
                ->first();

            if ($sourceInvoice) {
                $sourceItems = DB::table('purchase_invoices_details')
                    ->join('products', 'purchase_invoices_details.product_id', '=', 'products.id')
                    ->where('purchase_invoices_details.purchase_invoices_id', $sourceInvoice->id)
                    ->whereNull('purchase_invoices_details.deleted_at')
                    ->select(
                        'purchase_invoices_details.product_id',
                        'products.name as product_name',
                        DB::raw('SUM(purchase_invoices_details.quantity) as purchased_quantity'),
                        DB::raw('SUM(purchase_invoices_details.quantity * purchase_invoices_details.price_per_pices) as purchased_total')
                    )
                    ->groupBy('purchase_invoices_details.product_id', 'products.name')
                    ->get();

                $returned = DB::table('purchase_return_invoice_details')
                    ->where('original_invoice_id', $sourceInvoice->id)
                    ->whereNull('deleted_at')
                    ->select('product_id')->selectRaw('SUM(quantity) as returned_quantity')
                    ->groupBy('product_id')->pluck('returned_quantity', 'product_id');

                $sourceItems = $sourceItems->map(function ($item) use ($returned) {
                    $item->returned_quantity = (int) ($returned[$item->product_id] ?? 0);
                    $item->available_quantity = max(0, (int) $item->purchased_quantity - $item->returned_quantity);
                    $item->unit_price = $item->purchased_quantity > 0
                        ? round($item->purchased_total / $item->purchased_quantity, 2) : 0;
                    return $item;
                });
            }
        }

        $returns = DB::table('purchase_return_invoices')
            ->leftJoin('suppliers', 'purchase_return_invoices.supplier_id', '=', 'suppliers.id')
            ->whereNull('purchase_return_invoices.deleted_at')
            ->select('purchase_return_invoices.*', 'suppliers.name as supplier_name')
            ->orderByDesc('purchase_return_invoices.id')
            ->paginate(20, ['*'], 'returns_page')->withQueryString();

        return view('purchases.returns', compact('invoiceId', 'sourceInvoice', 'sourceItems', 'returns'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'original_invoice_id' => 'required|integer|min:1',
            'items' => 'required|array',
            'items.*' => 'nullable|integer|min:0',
            'reason' => 'nullable|string|max:500',
        ], [
            'original_invoice_id.required' => 'رقم فاتورة المشتريات مطلوب.',
            'items.required' => 'حدد كمية صنف واحد على الأقل لإرجاعه.',
        ]);

        $returnId = DB::transaction(function () use ($validated) {
            $invoice = DB::table('purchase_invoices')->where('id', $validated['original_invoice_id'])
                ->whereNull('deleted_at')->lockForUpdate()->first();
            if (! $invoice) {
                throw ValidationException::withMessages(['original_invoice_id' => 'فاتورة المشتريات غير موجودة أو محذوفة.']);
            }

            $purchasedItems = DB::table('purchase_invoices_details')
                ->where('purchase_invoices_id', $invoice->id)->whereNull('deleted_at')
                ->select('product_id')->selectRaw('SUM(quantity) as purchased_quantity')
                ->selectRaw('SUM(quantity * price_per_pices) as purchased_total')
                ->groupBy('product_id')->get()->keyBy('product_id');
            $returned = DB::table('purchase_return_invoice_details')->where('original_invoice_id', $invoice->id)
                ->whereNull('deleted_at')->select('product_id')->selectRaw('SUM(quantity) as returned_quantity')
                ->groupBy('product_id')->pluck('returned_quantity', 'product_id');
            $lines = [];
            $total = 0;

            foreach ($validated['items'] as $productId => $quantity) {
                $quantity = (int) ($quantity ?? 0);
                if ($quantity === 0) continue;
                $item = $purchasedItems->get((int) $productId);
                $available = $item ? (int) $item->purchased_quantity - (int) ($returned[$productId] ?? 0) : 0;
                if (! $item || $quantity > $available) {
                    throw ValidationException::withMessages(['items' => 'كمية المرتجع أكبر من الكمية المتبقية من أحد أصناف الفاتورة.']);
                }

                $product = DB::table('products')->where('id', $productId)->whereNull('deleted_at')->lockForUpdate()->first();
                if (! $product || (float) $product->quantity + 0.000001 < $quantity) {
                    throw ValidationException::withMessages(['items' => 'المخزون الحالي لا يكفي لإرجاع هذه الكمية للمورد.']);
                }

                $unitPrice = round((float) $item->purchased_total / (int) $item->purchased_quantity, 2);
                $lineTotal = round($unitPrice * $quantity, 2);
                $total += $lineTotal;
                $lines[] = ['product_id' => (int) $productId, 'quantity' => $quantity, 'unit_price' => $unitPrice, 'total_amount' => $lineTotal];
            }

            if (! $lines) throw ValidationException::withMessages(['items' => 'أدخل كمية مرتجع أكبر من صفر لصنف واحد على الأقل.']);

            $now = now();
            $returnId = DB::table('purchase_return_invoices')->insertGetId([
                'original_invoice_id' => $invoice->id, 'supplier_id' => $invoice->supplier_id,
                'user_id' => Auth::id(), 'total_amount' => round($total, 2), 'reason' => $validated['reason'] ?? null,
                'created_at' => $now, 'updated_at' => $now,
            ]);

            foreach ($lines as $line) {
                DB::table('purchase_return_invoice_details')->insert($line + [
                    'return_invoice_id' => $returnId, 'original_invoice_id' => $invoice->id,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
                DB::table('products')->where('id', $line['product_id'])->decrement('quantity', $line['quantity'], ['updated_at' => $now]);
            }

            return $returnId;
        });

        return redirect()->route('purchases.returns.show', $returnId)->with('success', 'تم تسجيل مرتجع المشتريات وخصم الكمية من المخزون.');
    }

    public function show(int $id)
    {
        $return = DB::table('purchase_return_invoices')
            ->join('suppliers', 'purchase_return_invoices.supplier_id', '=', 'suppliers.id')
            ->leftJoin('users', 'purchase_return_invoices.user_id', '=', 'users.id')
            ->where('purchase_return_invoices.id', $id)->whereNull('purchase_return_invoices.deleted_at')
            ->select('purchase_return_invoices.*', 'suppliers.name as supplier_name', 'users.name as user_name')->first();
        abort_unless($return, 404);

        $items = DB::table('purchase_return_invoice_details')->join('products', 'purchase_return_invoice_details.product_id', '=', 'products.id')
            ->where('return_invoice_id', $id)->whereNull('purchase_return_invoice_details.deleted_at')
            ->select('products.name as product_name', 'purchase_return_invoice_details.quantity', 'purchase_return_invoice_details.unit_price', 'purchase_return_invoice_details.total_amount')->get();

        return view('purchases.return_invoice', compact('return', 'items'));
    }
}
