<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseInvoiceService
{
    public function create(array $data, ?object $file, int $userId): string
    {
        $fileName = $this->storeInvoiceFile($file);

        try {
            return (string) DB::transaction(function () use ($data, $fileName, $userId) {
                $this->assertActiveSupplier((int) $data['purchase_invoices_supplier']);
                $lines = $this->normalizeLines($data);
                $total = $this->linesTotal($lines);
                $paid = round((float) $data['amount_paid'], 2);
                $this->assertPaymentsDoNotExceedTotal($total, $paid, 0);

                $warehouseId = DB::table('warehouses')->whereNull('deleted_at')->orderBy('id')->value('id');
                foreach ($lines as $line) {
                    $this->ensureProduct($line, $warehouseId);
                }
                $this->adjustStock([], $lines);

                $invoiceId = (string) $data['purchase_invoices_no'];
                $remaining = round($total - $paid, 2);
                DB::table('purchase_invoices')->insert([
                    'id' => $invoiceId,
                    'date' => $data['purchase_invoices_date'],
                    'supplier_id' => (int) $data['purchase_invoices_supplier'],
                    'total_amount' => $total,
                    'paid_amount' => $paid,
                    'remaining_amount' => $remaining,
                    'payment_method' => $remaining > 0.009 ? 'credit' : 'cash',
                    'purchase_invoices_pic' => $fileName,
                    'user_id' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->insertDetails($invoiceId, $lines);
                $this->refreshProductCostPrices(array_column($lines, 'product_id'));

                return $invoiceId;
            });
        } catch (\Throwable $exception) {
            $this->removeInvoiceFile($fileName);
            throw $exception;
        }
    }

    public function update(string $invoiceId, array $data): void
    {
        DB::transaction(function () use ($invoiceId, $data) {
            $invoice = DB::table('purchase_invoices')
                ->where('id', $invoiceId)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->first();
            if (! $invoice) {
                throw ValidationException::withMessages(['purchase_invoices_no' => 'فاتورة المشتريات غير موجودة.']);
            }

            $supplierId = (int) $data['purchase_invoices_supplier'];
            $this->assertActiveSupplier($supplierId);
            $lines = $this->normalizeLines($data);
            $total = $this->linesTotal($lines);
            $paid = round((float) $data['amount_paid'], 2);
            $laterPayments = (float) DB::table('supplier_payments')
                ->where('purchases_invoice_id', $invoiceId)
                ->whereNull('deleted_at')
                ->sum('amount_paid');
            $this->assertPaymentsDoNotExceedTotal($total, $paid, $laterPayments);

            $oldDetails = DB::table('purchase_invoices_details')
                ->where('purchase_invoices_id', $invoiceId)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->get(['product_id', 'quantity']);

            $warehouseId = DB::table('warehouses')->whereNull('deleted_at')->orderBy('id')->value('id');
            foreach ($lines as $line) {
                $this->ensureProduct($line, $warehouseId);
            }
            $this->adjustStock($oldDetails->all(), $lines);

            DB::table('purchase_invoices_details')
                ->where('purchase_invoices_id', $invoiceId)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => now(), 'updated_at' => now()]);

            $remaining = round($total - $paid, 2);
            DB::table('purchase_invoices')->where('id', $invoiceId)->update([
                'date' => $data['purchase_invoices_date'],
                'supplier_id' => $supplierId,
                'total_amount' => $total,
                'paid_amount' => $paid,
                // This field remains the balance after the initial payment;
                // later supplier payments are stored and subtracted separately.
                'remaining_amount' => $remaining,
                'payment_method' => ($remaining > 0.009 || $invoice->payment_method === 'credit') ? 'credit' : 'cash',
                'updated_at' => now(),
            ]);

            if ($laterPayments > 0) {
                DB::table('supplier_payments')
                    ->where('purchases_invoice_id', $invoiceId)
                    ->whereNull('deleted_at')
                    ->update(['supplier_id' => $supplierId]);
                $this->recalculatePaymentBalances($invoiceId, $remaining);
            }

            $this->insertDetails($invoiceId, $lines);
            $affectedProductIds = array_merge(
                $oldDetails->pluck('product_id')->all(),
                array_column($lines, 'product_id')
            );
            $this->refreshProductCostPrices($affectedProductIds);
        });
    }

    public function delete(string $invoiceId): void
    {
        DB::transaction(function () use ($invoiceId) {
            $invoice = DB::table('purchase_invoices')
                ->where('id', $invoiceId)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->first();
            if (! $invoice) {
                throw ValidationException::withMessages(['invoice' => 'فاتورة المشتريات غير موجودة.']);
            }

            $details = DB::table('purchase_invoices_details')
                ->where('purchase_invoices_id', $invoiceId)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->get(['product_id', 'quantity']);
            $affectedProductIds = $details->pluck('product_id')->all();

            // Reverse the stock effect only when the full quantity is still available.
            $this->adjustStock($details->all(), []);

            DB::table('purchase_invoices_details')
                ->where('purchase_invoices_id', $invoiceId)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => now(), 'updated_at' => now()]);

            DB::table('supplier_payments')
                ->where('purchases_invoice_id', $invoiceId)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => now(), 'updated_at' => now()]);

            DB::table('purchase_invoices')->where('id', $invoiceId)->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);
            $this->refreshProductCostPrices($affectedProductIds);
        });
    }

    public function addPayment(string $invoiceId, int $supplierId, array $data, int $userId): void
    {
        DB::transaction(function () use ($invoiceId, $supplierId, $data, $userId) {
            $invoice = $this->lockCreditInvoice($invoiceId, $supplierId);
            $paid = round((float) $data['amount_paid'], 2);
            $alreadyPaid = (float) DB::table('supplier_payments')
                ->where('purchases_invoice_id', $invoiceId)
                ->whereNull('deleted_at')
                ->sum('amount_paid');
            $remaining = max(0, round((float) $invoice->remaining_amount - $alreadyPaid, 2));
            if ($paid > $remaining + 0.009) {
                throw ValidationException::withMessages(['amount_paid' => 'قيمة الدفعة تتجاوز المديونية الحالية.']);
            }

            DB::table('supplier_payments')->insert([
                'purchases_invoice_id' => $invoiceId,
                'supplier_id' => $supplierId,
                'payment_date' => $data['payment_date'],
                'amount_paid' => $paid,
                'notes' => $data['notes'] ?? null,
                'user_id' => $userId,
                'payment_remaning_amount' => max(0, round($remaining - $paid, 2)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->recalculatePaymentBalances($invoiceId, $invoice->remaining_amount);
            DB::table('purchase_invoices')->where('id', $invoiceId)->update(['updated_at' => now()]);
        });
    }

    public function updatePayment(string $invoiceId, int $supplierId, int $transactionId, array $data): void
    {
        DB::transaction(function () use ($invoiceId, $supplierId, $transactionId, $data) {
            $invoice = $this->lockCreditInvoice($invoiceId, $supplierId);
            $payment = DB::table('supplier_payments')
                ->where('supplier_payments_id', $transactionId)
                ->where('purchases_invoice_id', $invoiceId)
                ->where('supplier_id', $supplierId)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->first();
            if (! $payment) {
                throw ValidationException::withMessages(['amount_paid' => 'حركة السداد غير موجودة لهذه الفاتورة.']);
            }

            $otherPayments = (float) DB::table('supplier_payments')
                ->where('purchases_invoice_id', $invoiceId)
                ->whereNull('deleted_at')
                ->where('supplier_payments_id', '<>', $transactionId)
                ->sum('amount_paid');
            $paid = round((float) $data['amount_paid'], 2);
            $available = max(0, round((float) $invoice->remaining_amount - $otherPayments, 2));
            if ($paid > $available + 0.009) {
                throw ValidationException::withMessages(['amount_paid' => 'قيمة الدفعة تتجاوز المديونية الحالية بعد احتساب باقي الحركات.']);
            }

            DB::table('supplier_payments')->where('supplier_payments_id', $transactionId)->update([
                'payment_date' => $data['payment_date'],
                'amount_paid' => $paid,
                'notes' => $data['notes'] ?? null,
                'updated_at' => now(),
            ]);

            $this->recalculatePaymentBalances($invoiceId, $invoice->remaining_amount);
            DB::table('purchase_invoices')->where('id', $invoiceId)->update(['updated_at' => now()]);
        });
    }

    private function lockCreditInvoice(string $invoiceId, int $supplierId): object
    {
        $invoice = DB::table('purchase_invoices')
            ->where('id', $invoiceId)
            ->where('supplier_id', $supplierId)
            ->whereNull('deleted_at')
            ->lockForUpdate()
            ->first();
        if (! $invoice || $invoice->payment_method !== 'credit') {
            throw ValidationException::withMessages(['amount_paid' => 'الفاتورة الآجلة غير موجودة لهذا المورد.']);
        }

        return $invoice;
    }

    private function recalculatePaymentBalances(string $invoiceId, float $initialRemaining): void
    {
        $remaining = max(0, round($initialRemaining, 2));
        $payments = DB::table('supplier_payments')
            ->where('purchases_invoice_id', $invoiceId)
            ->whereNull('deleted_at')
            ->orderBy('payment_date')
            ->orderBy('supplier_payments_id')
            ->get(['supplier_payments_id', 'amount_paid']);

        foreach ($payments as $payment) {
            $remaining = max(0, round($remaining - (float) $payment->amount_paid, 2));
            DB::table('supplier_payments')->where('supplier_payments_id', $payment->supplier_payments_id)->update([
                'payment_remaning_amount' => $remaining,
                'updated_at' => now(),
            ]);
        }
    }

    private function assertActiveSupplier(int $supplierId): void
    {
        $exists = DB::table('suppliers')->where('id', $supplierId)->whereNull('deleted_at')->exists();
        if (! $exists) {
            throw ValidationException::withMessages(['purchase_invoices_supplier' => 'المورد المحدد غير موجود أو محذوف.']);
        }
    }

    private function normalizeLines(array $data): array
    {
        $ids = $data['product_id'] ?? [];
        $names = $data['product'] ?? [];
        $categories = $data['category_name'] ?? [];
        $quantities = $data['quantity'] ?? [];
        $unitPrices = $data['unit_price'] ?? [];
        $salesPrices = $data['sales_price'] ?? [];
        $count = count($ids);

        if ($count === 0 || count($quantities) !== $count || count($unitPrices) !== $count || count($salesPrices) !== $count) {
            throw ValidationException::withMessages(['product_id' => 'أكمل بيانات كل أصناف الفاتورة.']);
        }

        $lines = [];
        for ($index = 0; $index < $count; $index++) {
            $id = trim((string) $ids[$index]);
            $quantity = round((float) $quantities[$index], 3);
            $unitPrice = round((float) $unitPrices[$index], 2);
            $salesPrice = round((float) $salesPrices[$index], 2);
            $product = DB::table('products')->where('id', $id)->first(['name', 'category', 'deleted_at']);
            $name = trim((string) ($names[$index] ?? ($product->name ?? '')));
            $category = (int) ($categories[$index] ?? ($product->category ?? 0));

            if ($id === '' || ! ctype_digit($id) || $quantity <= 0 || $unitPrice < 0 || $salesPrice < 0) {
                throw ValidationException::withMessages(['product_id' => 'بيانات صنف الفاتورة غير صحيحة.']);
            }
            if (! DB::table('category')->where('category_id', $category)->whereNull('deleted_at')->exists()) {
                throw ValidationException::withMessages(['category_name' => 'اختر تصنيفًا صالحًا لكل صنف.']);
            }

            $lines[] = [
                'product_id' => $id,
                'name' => $name,
                'category_id' => $category,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'sales_price' => $salesPrice,
            ];
        }

        return $lines;
    }

    private function linesTotal(array $lines): float
    {
        return round(array_sum(array_map(function ($line) {
            return round($line['quantity'] * $line['unit_price'], 2);
        }, $lines)), 2);
    }

    private function assertPaymentsDoNotExceedTotal(float $total, float $initialPayment, float $laterPayments): void
    {
        if ($initialPayment < 0 || $initialPayment + $laterPayments > $total + 0.009) {
            throw ValidationException::withMessages(['amount_paid' => 'إجمالي المدفوعات لا يمكن أن يتجاوز إجمالي الفاتورة.']);
        }
    }

    private function ensureProduct(array $line, $warehouseId): void
    {
        $product = DB::table('products')->where('id', $line['product_id'])->lockForUpdate()->first();
        if ($product) {
            if ($product->deleted_at !== null) {
                throw ValidationException::withMessages(['product_id' => 'كود المنتج مستخدم لمنتج محذوف.']);
            }
            DB::table('products')->where('id', $line['product_id'])->update([
                'price' => $line['sales_price'],
                'updated_at' => now(),
            ]);
            return;
        }

        if ($line['name'] === '' || $warehouseId === null) {
            throw ValidationException::withMessages(['product_id' => 'أدخل اسم المنتج وتأكد من وجود مخزن نشط للمنتجات الجديدة.']);
        }

        DB::table('products')->insert([
            'id' => $line['product_id'],
            'name' => $line['name'],
            'category' => $line['category_id'],
            'quantity' => 0,
            'price' => $line['sales_price'],
            'warehouse_id' => $warehouseId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function adjustStock(array $oldLines, array $newLines): void
    {
        $deltas = [];
        foreach ($oldLines as $line) {
            $id = (string) $line->product_id;
            $deltas[$id] = ($deltas[$id] ?? 0) - (float) $line->quantity;
        }
        foreach ($newLines as $line) {
            $id = (string) $line['product_id'];
            $deltas[$id] = ($deltas[$id] ?? 0) + (float) $line['quantity'];
        }

        foreach ($deltas as $productId => $delta) {
            if (abs($delta) < 0.000001) {
                continue;
            }
            $product = DB::table('products')->where('id', $productId)->whereNull('deleted_at')->lockForUpdate()->first();
            if (! $product) {
                throw ValidationException::withMessages(['product_id' => 'تعذر العثور على منتج نشط لتحديث المخزون.']);
            }
            $newQuantity = round((float) $product->quantity + $delta, 3);
            if ($newQuantity < -0.000001) {
                throw ValidationException::withMessages(['product_id' => 'لا يمكن حذف أو تعديل الفاتورة لأن الكمية المتاحة أقل من الكمية المسجلة بها.']);
            }
            DB::table('products')->where('id', $productId)->update([
                'quantity' => max(0, $newQuantity),
                'updated_at' => now(),
            ]);
        }
    }

    private function insertDetails(string $invoiceId, array $lines): void
    {
        $now = now();
        $details = array_map(function ($line) use ($invoiceId, $now) {
            return [
                'purchase_invoices_id' => $invoiceId,
                'product_id' => $line['product_id'],
                'quantity' => $line['quantity'],
                'price_per_pices' => $line['unit_price'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $lines);

        DB::table('purchase_invoices_details')->insert($details);
    }

    private function refreshProductCostPrices(array $productIds): void
    {
        foreach (array_unique(array_map('strval', $productIds)) as $productId) {
            $latestCost = DB::table('purchase_invoices_details as details')
                ->join('purchase_invoices as invoices', 'invoices.id', '=', 'details.purchase_invoices_id')
                ->where('details.product_id', $productId)
                ->whereNull('details.deleted_at')
                ->whereNull('invoices.deleted_at')
                ->orderByDesc('details.id')
                ->value('details.price_per_pices');

            DB::table('products')->where('id', $productId)->update([
                'cost_price' => $latestCost ?? 0,
                'updated_at' => now(),
            ]);
        }
    }

    private function storeInvoiceFile(?object $file): ?string
    {
        if (! $file) {
            return null;
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $fileName = uniqid('purchase_', true).'.'.$extension;
        $file->move(public_path('images/purchases_invoices'), $fileName);

        return $fileName;
    }

    private function removeInvoiceFile(?string $fileName): void
    {
        if ($fileName) {
            $path = public_path('images/purchases_invoices/'.$fileName);
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }
}
