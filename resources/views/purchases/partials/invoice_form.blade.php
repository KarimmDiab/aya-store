<div class="collapse {{ request()->boolean('create') || $errors->any() ? 'show' : '' }}" id="purchaseInvoiceFormPanel">
    <div class="card card-body border-primary mb-4">
        <h5 class="mb-3">تسجيل فاتورة مشتريات</h5>
        <form action="{{ route('add_purchase_invoices') }}" method="POST" enctype="multipart/form-data" id="purchaseInvoiceForm">
            @csrf
            <div class="row">
                <div class="col-md-4 form-group">
                    <label for="purchaseSupplier">المورد</label>
                    <select class="form-control" name="purchase_invoices_supplier" id="purchaseSupplier" required>
                        <option value="">اختر المورد</option>
                        @foreach ($suppliers as $supplierOption)
                            <option value="{{ $supplierOption->id }}" {{ (int) old('purchase_invoices_supplier', $selectedSupplierId) === (int) $supplierOption->id ? 'selected' : '' }}>{{ $supplierOption->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 form-group">
                    <label for="purchaseInvoiceNumber">رقم الفاتورة</label>
                    <input class="form-control" id="purchaseInvoiceNumber" name="purchase_invoices_no" value="{{ old('purchase_invoices_no') }}" required maxlength="100">
                </div>
                <div class="col-md-4 form-group">
                    <label for="purchaseInvoiceDate">تاريخ الفاتورة</label>
                    <input class="form-control" type="date" id="purchaseInvoiceDate" name="purchase_invoices_date" value="{{ old('purchase_invoices_date', now()->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="purchaseInvoiceFile">ملف الفاتورة (اختياري)</label>
                    <input class="form-control" type="file" id="purchaseInvoiceFile" name="invoice_file" accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered text-center" id="purchaseInvoiceLines">
                    <thead><tr><th>التصنيف</th><th>كود المنتج</th><th>اسم المنتج</th><th>الكمية</th><th>تكلفة الوحدة</th><th>سعر البيع</th><th>الإجمالي</th><th></th></tr></thead>
                    <tbody id="purchaseInvoiceLinesBody">
                        @php $oldLineCount = max(1, count(old('product_id', []))); @endphp
                        @for ($lineIndex = 0; $lineIndex < $oldLineCount; $lineIndex++)
                            <tr>
                                <td><select class="form-control" name="category_name[]" required><option value="">اختر</option>@foreach ($categories as $categoryOption)<option value="{{ $categoryOption->category_id }}" {{ (string) old('category_name.'.$lineIndex) === (string) $categoryOption->category_id ? 'selected' : '' }}>{{ $categoryOption->category_name }}</option>@endforeach</select></td>
                                <td><input class="form-control product-id" name="product_id[]" value="{{ old('product_id.'.$lineIndex) }}" inputmode="numeric" required></td>
                                <td><input class="form-control" name="product[]" value="{{ old('product.'.$lineIndex) }}" required maxlength="150"></td>
                                <td><input class="form-control line-quantity" type="number" name="quantity[]" value="{{ old('quantity.'.$lineIndex) }}" min="0.001" step="0.001" required></td>
                                <td><input class="form-control line-unit-price" type="number" name="unit_price[]" value="{{ old('unit_price.'.$lineIndex) }}" min="0" step="0.01" required></td>
                                <td><input class="form-control" type="number" name="sales_price[]" value="{{ old('sales_price.'.$lineIndex) }}" min="0" step="0.01" required></td>
                                <td><input class="form-control line-total" type="number" readonly></td>
                                <td><button type="button" class="btn btn-outline-danger remove-purchase-line" aria-label="حذف الصنف"><i class="fas fa-times"></i></button></td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
            <button type="button" class="btn btn-outline-info mb-3" id="addPurchaseLine"><i class="fas fa-plus ml-1"></i> إضافة صنف</button>

            <div class="row">
                <div class="col-md-4 form-group"><label>إجمالي الفاتورة</label><input class="form-control" type="number" id="purchaseInvoiceTotal" readonly></div>
                <div class="col-md-4 form-group"><label for="purchaseInvoicePaid">المدفوع عند الشراء</label><input class="form-control" type="number" id="purchaseInvoicePaid" name="amount_paid" value="{{ old('amount_paid', 0) }}" min="0" step="0.01" required></div>
                <div class="col-md-4 form-group"><label>المتبقي</label><input class="form-control" type="number" id="purchaseInvoiceRemaining" readonly></div>
            </div>
            <div class="alert alert-danger d-none" id="purchaseInvoiceAmountError">المدفوع لا يمكن أن يتجاوز إجمالي الفاتورة.</div>
            <button class="btn btn-primary" type="submit"><i class="fas fa-save ml-1"></i> حفظ الفاتورة</button>
            <button class="btn btn-light" type="button" data-toggle="collapse" data-target="#purchaseInvoiceFormPanel">إلغاء</button>
        </form>
    </div>
</div>

<template id="purchaseInvoiceLineTemplate">
    <tr>
        <td><select class="form-control" name="category_name[]" required><option value="">اختر</option>@foreach ($categories as $categoryOption)<option value="{{ $categoryOption->category_id }}">{{ $categoryOption->category_name }}</option>@endforeach</select></td>
        <td><input class="form-control product-id" name="product_id[]" inputmode="numeric" required></td>
        <td><input class="form-control" name="product[]" required maxlength="150"></td>
        <td><input class="form-control line-quantity" type="number" name="quantity[]" min="0.001" step="0.001" required></td>
        <td><input class="form-control line-unit-price" type="number" name="unit_price[]" min="0" step="0.01" required></td>
        <td><input class="form-control" type="number" name="sales_price[]" min="0" step="0.01" required></td>
        <td><input class="form-control line-total" type="number" readonly></td>
        <td><button type="button" class="btn btn-outline-danger remove-purchase-line" aria-label="حذف الصنف"><i class="fas fa-times"></i></button></td>
    </tr>
</template>
