<form action="{{ route('purchase_invoices_update', ['invoice_id' => $invoices->id]) }}"
      method="POST" id="editPurchaseInvoiceForm" novalidate>
    @csrf
    @method('PUT')

    <div class="pi-edit-error alert alert-danger d-none" id="editInvoiceErrors" role="alert"></div>

    <div class="row">
        <div class="col-md-4 form-group">
            <label for="editInvoiceNumber">رقم الفاتورة</label>
            <input class="form-control" id="editInvoiceNumber" name="purchase_invoices_no"
                   value="{{ $invoices->id }}" readonly>
        </div>
        <div class="col-md-4 form-group">
            <label for="editInvoiceDate">تاريخ الفاتورة <span class="text-danger">*</span></label>
            <input class="form-control" id="editInvoiceDate" name="purchase_invoices_date"
                   type="date" value="{{ $invoices->date }}" required>
        </div>
        <div class="col-md-4 form-group">
            <label for="editInvoiceSupplier">المورد <span class="text-danger">*</span></label>
            <select class="form-control" id="editInvoiceSupplier" name="purchase_invoices_supplier" required>
                <option value="">اختر المورد</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" {{ (string) $invoices->supplier_id === (string) $supplier->id ? 'selected' : '' }}>
                        {{ $supplier->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="pi-edit-lines-heading d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h6 class="mb-1">أصناف الفاتورة</h6>
            <small class="text-muted">عدّل الكمية أو تكلفة الوحدة أو سعر البيع. تكلفة الوحدة ستنعكس على بيانات المنتج.</small>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary mt-2 mt-md-0" id="editAddPurchaseLine">
            <i class="fas fa-plus ml-1"></i> إضافة صنف
        </button>
    </div>

    <div class="table-responsive pi-edit-lines-wrap">
        <table class="table table-bordered pi-edit-lines mb-0">
            <thead>
                <tr>
                    <th>كود المنتج</th>
                    <th>اسم المنتج</th>
                    <th>التصنيف</th>
                    <th>الكمية</th>
                    <th>تكلفة الوحدة</th>
                    <th>سعر البيع</th>
                    <th>إجمالي الصنف</th>
                    <th>الربح</th>
                    <th>حذف</th>
                </tr>
            </thead>
            <tbody id="editPurchaseInvoiceLines">
                @foreach ($invoiceDetails as $detail)
                    <tr>
                        <td><input class="form-control edit-product-id" name="product_id[]" value="{{ $detail->product_id }}" inputmode="numeric" required></td>
                        <td><input class="form-control edit-product-name" name="product[]" value="{{ $detail->product_name }}" maxlength="150" required></td>
                        <td>
                            <select class="form-control edit-category" name="category_name[]" required>
                                <option value="">اختر</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->category_id }}" {{ (string) $detail->product_category === (string) $category->category_id ? 'selected' : '' }}>
                                        {{ $category->category_name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td><input class="form-control edit-line-quantity" type="number" name="quantity[]" value="{{ $detail->quantity_product }}" min="0.001" step="0.001" required></td>
                        <td><input class="form-control edit-line-cost" type="number" name="unit_price[]" value="{{ $detail->purchase_price }}" min="0" step="0.01" required></td>
                        <td><input class="form-control edit-line-sale" type="number" name="sales_price[]" value="{{ $detail->product_price ?? 0 }}" min="0" step="0.01" required></td>
                        <td><input class="form-control edit-line-total" type="number" value="{{ $detail->total_product }}" readonly></td>
                        <td><input class="form-control edit-line-profit" type="number" value="{{ ($detail->quantity_product * ($detail->product_price ?? 0)) - ($detail->quantity_product * $detail->purchase_price) }}" readonly></td>
                        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger edit-remove-line" aria-label="حذف الصنف"><i class="fas fa-trash-alt"></i></button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="row mt-3">
        <div class="col-md-3 form-group">
            <label for="editInvoiceTotal">إجمالي الفاتورة</label>
            <input class="form-control pi-readonly" id="editInvoiceTotal" readonly>
        </div>
        <div class="col-md-3 form-group">
            <label for="editInvoicePaid">المدفوع عند الشراء</label>
            <input class="form-control" id="editInvoicePaid" name="amount_paid" type="number" value="{{ $invoices->paid_amount }}" min="0" step="0.01" required>
        </div>
        <div class="col-md-3 form-group">
            <label>مدفوعات لاحقة</label>
            <input class="form-control pi-readonly" id="editInvoiceLaterPaid" value="{{ number_format($laterPayments, 2, '.', '') }}" readonly>
        </div>
        <div class="col-md-3 form-group">
            <label for="editInvoiceRemaining">المتبقي بعد السداد</label>
            <input class="form-control pi-readonly" id="editInvoiceRemaining" readonly>
        </div>
    </div>
    <div class="alert alert-warning d-none mb-0" id="editInvoicePaymentWarning">
        إجمالي المدفوعات (الدفعة الأولى وحركات السداد) يتجاوز إجمالي الفاتورة.
    </div>

    <div class="pi-edit-footer d-flex justify-content-end flex-wrap">
        <button type="button" class="btn btn-light ml-2" data-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary" id="savePurchaseInvoiceChanges">
            <i class="fas fa-save ml-1"></i> حفظ التعديلات
        </button>
    </div>
</form>

<template id="editPurchaseInvoiceLineTemplate">
    <tr>
        <td><input class="form-control edit-product-id" name="product_id[]" inputmode="numeric" required></td>
        <td><input class="form-control edit-product-name" name="product[]" maxlength="150" required></td>
        <td>
            <select class="form-control edit-category" name="category_name[]" required>
                <option value="">اختر</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->category_id }}">{{ $category->category_name }}</option>
                @endforeach
            </select>
        </td>
        <td><input class="form-control edit-line-quantity" type="number" name="quantity[]" min="0.001" step="0.001" required></td>
        <td><input class="form-control edit-line-cost" type="number" name="unit_price[]" min="0" step="0.01" required></td>
        <td><input class="form-control edit-line-sale" type="number" name="sales_price[]" min="0" step="0.01" required></td>
        <td><input class="form-control edit-line-total" type="number" readonly></td>
        <td><input class="form-control edit-line-profit" type="number" readonly></td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger edit-remove-line" aria-label="حذف الصنف"><i class="fas fa-trash-alt"></i></button></td>
    </tr>
</template>
