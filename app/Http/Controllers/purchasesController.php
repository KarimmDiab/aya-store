<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Services\PurchaseInvoiceService;

class purchasesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $editId = trim((string) $request->query('edit', ''));
        $allowedPageSizes = [10, 15, 25, 50, 100];
        $requestedPageSize = (int) $request->query('per_page', 50);
        $perPage = in_array($requestedPageSize, $allowedPageSizes, true) ? $requestedPageSize : 50;
        $sortOptions = [
            'id' => 'purchase_invoices.id',
            'date' => 'purchase_invoices.date',
            'supplier' => 'suppliers.name',
            'total' => 'purchase_invoices.total_amount',
            'paid' => 'total_paid_amount',
            'remaining' => 'payment_remaning_amount',
            'created_at' => 'purchase_invoices.created_at',
        ];
        $sort = (string) $request->query('sort', 'date');
        if (! array_key_exists($sort, $sortOptions) && $sort !== 'status') {
            $sort = 'date';
        }
        $direction = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $paymentTotalsForSort = DB::table('supplier_payments')
            ->whereNull('deleted_at')
            ->select('purchases_invoice_id')
            ->selectRaw('COALESCE(SUM(amount_paid), 0) AS total_amount_paid')
            ->groupBy('purchases_invoice_id');

        $invoiceQuery = DB::table('purchase_invoices')
            ->join('users', 'purchase_invoices.user_id', '=', 'users.id')
            ->leftJoinSub($paymentTotalsForSort, 'supplier_payment_totals', function ($join) {
                $join->on('supplier_payment_totals.purchases_invoice_id', '=', 'purchase_invoices.id');
            })
            ->leftJoin('suppliers', function ($join) {
                $join->on('purchase_invoices.supplier_id', '=', 'suppliers.id')
                    ->whereNull('suppliers.deleted_at');
            })
            ->select(
                'purchase_invoices.*',
                'purchase_invoices.id AS purchase_invoices_id',
                'users.id AS user_id',
                'users.name AS user_name',
                'suppliers.name AS supplier_name',
                DB::raw('(COALESCE(purchase_invoices.paid_amount, 0) + COALESCE(supplier_payment_totals.total_amount_paid, 0)) AS total_paid_amount'),
                DB::raw('GREATEST(COALESCE(purchase_invoices.remaining_amount, 0) - COALESCE(supplier_payment_totals.total_amount_paid, 0), 0) AS payment_remaning_amount')
            )
            ->whereNull('purchase_invoices.deleted_at')
            ->when($editId !== '', function ($query) use ($editId) {
                $query->where('purchase_invoices.id', $editId);
            })
            ->when($editId === '' && $search !== '', function ($query) use ($search) {
                $query->where('purchase_invoices.id', 'like', '%' . $search . '%');
            });

        if ($sort === 'status') {
            $invoiceQuery->orderByRaw(
                "CASE WHEN payment_remaning_amount <= 0 THEN 0 WHEN total_paid_amount > 0 THEN 1 ELSE 2 END {$direction}"
            );
        } else {
            $invoiceQuery->orderBy($sortOptions[$sort], $direction);
        }
        if ($sort !== 'id') {
            $invoiceQuery->orderByDesc('purchase_invoices.id');
        }

        $invoices = $invoiceQuery->paginate($perPage)->withQueryString();

        // An old or manually edited page number can be beyond the last page.
        // Send the user to the last available page instead of rendering an empty table.
        if ($invoices->total() > 0 && $invoices->currentPage() > $invoices->lastPage()) {
            return redirect()->to($invoices->url($invoices->lastPage()));
        }

        $invoiceIds = $invoices->getCollection()->pluck('id');
        $purchaseReturnSummaries = DB::table('purchase_return_invoices')
            ->whereIn('original_invoice_id', $invoiceIds)
            ->whereNull('deleted_at')
            ->select('original_invoice_id')
            ->selectRaw('COUNT(id) as return_count, SUM(total_amount) as return_amount')
            ->groupBy('original_invoice_id')->get()->keyBy('original_invoice_id');
        $purchaseReturnQuantities = DB::table('purchase_return_invoice_details as details')
            ->join('purchase_return_invoices as returns', 'returns.id', '=', 'details.return_invoice_id')
            ->whereIn('returns.original_invoice_id', $invoiceIds)
            ->whereNull('returns.deleted_at')->whereNull('details.deleted_at')
            ->select('returns.original_invoice_id')->selectRaw('SUM(details.quantity) as return_quantity')
            ->groupBy('returns.original_invoice_id')->get()->keyBy('original_invoice_id');
        $purchaseReturnDetailsByInvoice = DB::table('purchase_return_invoice_details as details')
            ->join('purchase_return_invoices as returns', 'returns.id', '=', 'details.return_invoice_id')
            ->leftJoin('products', 'products.id', '=', 'details.product_id')
            ->whereIn('returns.original_invoice_id', $invoiceIds)
            ->whereNull('returns.deleted_at')->whereNull('details.deleted_at')
            ->orderBy('returns.created_at')->orderBy('details.id')
            ->get([
                'returns.original_invoice_id', 'returns.id as return_invoice_id', 'returns.created_at as return_date',
                'products.name as product_name', 'details.quantity', 'details.unit_price', 'details.total_amount',
            ])->groupBy('original_invoice_id');

        foreach ($invoices as $invoice) {
            $summary = $purchaseReturnSummaries->get($invoice->id);
            $quantity = $purchaseReturnQuantities->get($invoice->id);
            $invoice->purchase_return_count = (int) ($summary->return_count ?? 0);
            $invoice->purchase_return_amount = (float) ($summary->return_amount ?? 0);
            $invoice->purchase_return_quantity = (float) ($quantity->return_quantity ?? 0);
        }
        $paymentTotals = DB::table('supplier_payments')
            ->whereIn('purchases_invoice_id', $invoiceIds)
            ->whereNull('deleted_at')
            ->select('purchases_invoice_id')
            ->selectRaw('COALESCE(SUM(amount_paid), 0) AS total_amount_paid')
            ->groupBy('purchases_invoice_id')
            ->get()
            ->keyBy('purchases_invoice_id');

        foreach ($invoices as $invoice) {
            $invoice->total_amount_paid = $paymentTotals->get($invoice->id)->total_amount_paid ?? 0;
            $invoice->total_paid_amount = (float) $invoice->paid_amount + (float) $invoice->total_amount_paid;
            $invoice->payment_remaning_amount = max(0, (float) $invoice->remaining_amount - (float) $invoice->total_amount_paid);
        }

        $supplierPaymentsByInvoice = DB::table('supplier_payments')
            ->whereIn('purchases_invoice_id', $invoiceIds)
            ->whereNull('deleted_at')
            ->orderBy('payment_date')
            ->orderBy('supplier_payments_id')
            ->get([
                'purchases_invoice_id',
                'supplier_payments_id',
                'payment_date',
                'amount_paid',
                'payment_remaning_amount',
                'notes',
            ])
            ->groupBy('purchases_invoice_id');

        $invoiceDetails = DB::table('purchase_invoices')
            ->join('purchase_invoices_details', 'purchase_invoices.id', '=', 'purchase_invoices_details.purchase_invoices_id')
            ->join('products', 'purchase_invoices_details.product_id', '=', 'products.id')
            ->select(
                'purchase_invoices.id as purchase_invoice_id',
                'purchase_invoices.date as purchase_invoice_date',
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
            ->whereIn('purchase_invoices.id', $invoiceIds)
            ->whereNull('purchase_invoices_details.deleted_at')
            ->whereNull('products.deleted_at')
            ->get();
        $invoiceDetailsByInvoice = $invoiceDetails->groupBy('purchase_invoice_id');

        $suppliers = DB::table('suppliers')
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name']);
        $categories = DB::table('category')
            ->whereNull('deleted_at')
            ->orderBy('category_name')
            ->get(['category_id', 'category_name']);
        $selectedSupplierId = (int) $request->query('supplier_id', 0);

        return view('purchases.purchase_invoices', compact(
            'invoices', 'invoiceDetailsByInvoice', 'supplierPaymentsByInvoice', 'purchaseReturnDetailsByInvoice', 'suppliers', 'categories', 'selectedSupplierId'
        ));
    }


    public function editPricesInPurchaseInvoice()
    {
        return redirect()->route('purchase_invoices');
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'purchase_invoices_no' => 'required|string|max:100|unique:purchase_invoices,id',
            'purchase_invoices_date' => 'required|date',
            'purchase_invoices_supplier' => 'required|integer|exists:suppliers,id',
            'amount_paid' => 'required|numeric|min:0',
            'product_id' => 'required|array|min:1',
            'product_id.*' => 'required|integer|min:1',
            'product' => 'required|array|min:1',
            'product.*' => 'required|string|max:150',
            'category_name' => 'required|array|min:1',
            'category_name.*' => 'required|integer|exists:category,category_id',
            'quantity' => 'required|array|min:1',
            'quantity.*' => 'required|numeric|gt:0',
            'unit_price' => 'required|array|min:1',
            'unit_price.*' => 'required|numeric|min:0',
            'sales_price' => 'required|array|min:1',
            'sales_price.*' => 'required|numeric|min:0',
            'invoice_file' => [
                'nullable',
                'file',
                'max:2048',
                function ($attribute, $file, $fail) {
                    if (! $this->isValidPurchaseInvoiceFile($file)) {
                        $fail('ارفع ملف فاتورة صالحًا بصيغة JPG أو JPEG أو PNG أو PDF.');
                    }
                },
            ],
        ]);

        app(PurchaseInvoiceService::class)->create($data, $request->file('invoice_file'), (int) Auth::id());

        return redirect()->route('purchase_invoices')->with('success', 'تم تسجيل فاتورة المشتريات بنجاح.');
    }

    private function isValidPurchaseInvoiceFile($file): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        if (! $path || ! is_file($path)) {
            return false;
        }

        if (in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
            $imageInfo = @getimagesize($path);
            if (! is_array($imageInfo)) {
                return false;
            }

            $expectedType = $extension === 'png' ? IMAGETYPE_PNG : IMAGETYPE_JPEG;
            return ($imageInfo[2] ?? null) === $expectedType;
        }

        if ($extension === 'pdf') {
            $handle = @fopen($path, 'rb');
            if ($handle === false) {
                return false;
            }

            $signature = fread($handle, 5);
            fclose($handle);

            return $signature === '%PDF-';
        }

        return false;
    }

    public function newStoreInvoice(Request $request, $supplier)
    {
        try {
            $supplierId = (int) decrypt($supplier);
        } catch (\Throwable $exception) {
            return redirect()->route('purchase_invoices')->with('error', 'رابط المورد غير صالح.');
        }

        // Legacy endpoint now uses the same validation and accounting path as the unified form.
        $request->merge(['purchase_invoices_supplier' => $supplierId]);

        return $this->store($request);
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
    public function edit(Request $request, string $invoice_id)
    {
        if (! $request->ajax()) {
            return redirect()->route('purchase_invoices', ['edit' => $invoice_id]);
        }

        $invoices = DB::table('purchase_invoices')
            ->whereNull('deleted_at')
            ->where('purchase_invoices.id', $invoice_id)
            ->first(); // ✅ Returns a single object instead of a collection

        if (! $invoices) {
            abort(404, 'فاتورة المشتريات غير موجودة.');
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
                'products.category as product_category',
                'products.price as product_price',
                'purchase_invoices_details.quantity as quantity_product',
                'purchase_invoices_details.price_per_pices as purchase_price',
                DB::raw('(purchase_invoices_details.quantity * purchase_invoices_details.price_per_pices) as total_product'),
                'purchase_invoices.total_amount as total_invoice',
                'purchase_invoices.paid_amount as invoice_paid_amount',
                'purchase_invoices.remaining_amount as invoice_remaining_amount'
            )
            ->where('purchase_invoices.id', $invoice_id)
            ->whereNull('purchase_invoices_details.deleted_at')
            ->whereNull('products.deleted_at')
            ->get();

        $suppliers = DB::table('suppliers')
            ->whereNull('deleted_at')
            ->get(); // Returns a collection

        $categories = DB::table('category')
            ->whereNull('deleted_at')
            ->orderBy('category_name')
            ->get(['category_id', 'category_name']);

        $laterPayments = (float) DB::table('supplier_payments')
            ->where('purchases_invoice_id', $invoice_id)
            ->whereNull('deleted_at')
            ->sum('amount_paid');

        return view('purchases.update_purchase_invoices', compact(
            'invoices', 'invoiceDetails', 'suppliers', 'categories', 'laterPayments'
        ));
    }



    public function editPricePurchaseInvoice(string $invoice_id)
    {
        return redirect()->route('purchase_invoices', ['edit' => $invoice_id]);
    }
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'purchase_invoices_no' => 'required|string|max:100',
            'purchase_invoices_date' => 'required|date',
            'purchase_invoices_supplier' => 'required|integer|exists:suppliers,id',
            'amount_paid' => 'required|numeric|min:0',
            'product_id' => 'required|array|min:1',
            'product_id.*' => 'required|integer|min:1',
            'product' => 'nullable|array',
            'product.*' => 'nullable|string|max:150',
            'category_name' => 'nullable|array',
            'category_name.*' => 'nullable|integer|exists:category,category_id',
            'quantity' => 'required|array|min:1',
            'quantity.*' => 'required|numeric|gt:0',
            'unit_price' => 'required|array|min:1',
            'unit_price.*' => 'required|numeric|min:0',
            'sales_price' => 'required|array|min:1',
            'sales_price.*' => 'required|numeric|min:0',
        ]);

        if ((string) $data['purchase_invoices_no'] !== (string) $id) {
            return redirect()->back()->withErrors(['purchase_invoices_no' => 'رقم الفاتورة لا يمكن تغييره بعد تسجيلها.'])->withInput();
        }

        app(PurchaseInvoiceService::class)->update((string) $id, $data);

        if ($request->expectsJson()) {
            session()->flash('success', 'تم تحديث الفاتورة وتحديث المخزون وتكلفة المنتجات بنجاح.');
            return response()->json(['message' => 'تم تحديث الفاتورة بنجاح.']);
        }

        return redirect()->route('purchase_invoices')->with('success', 'تم تحديث الفاتورة ومراجعة رصيدها ومخزونها بنجاح.');
    }
/**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        app(PurchaseInvoiceService::class)->delete((string) $id);

        return redirect()->route('purchase_invoices')->with('success', 'تم حذف الفاتورة وعكس أثرها على المخزون.');
    }
}
