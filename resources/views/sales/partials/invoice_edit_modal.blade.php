<div class="modal fade invoice-modal" id="editSalesInvoice{{ $invoice->sales_invoice_id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header invoice-modal-header">
                <div>
                    <h5 class="modal-title">تعديل فاتورة #{{ $invoice->sales_invoice_id }}</h5>
                    <small>راجع الكميات والمبالغ قبل حفظ التعديلات</small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="إغلاق"><span aria-hidden="true">&times;</span></button>
            </div>

            <form action="{{ route('invoice.update', $invoice->sales_invoice_id) }}" method="POST" class="invoice-edit-form"
                data-additional-paid="{{ $invoice->total_paid_transactions }}"
                data-discount="{{ $invoice->invoice_discount }}"
                data-returns="{{ $invoice->total_returned }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="invoice-customer-strip mb-4">
                        <div><span>رقم الفاتورة</span><strong>#{{ $invoice->sales_invoice_id }}</strong></div>
                        <div><span>تاريخ الفاتورة</span><strong>{{ $invoice->invoice_date }}</strong></div>
                        @if($invoice->customer_id)
                            <div><span>اسم العميل</span><input class="form-control form-control-sm" name="edit_sales_customer_name" value="{{ $invoice->customer_name }}" maxlength="255"></div>
                            <div><span>رقم الهاتف</span><input class="form-control form-control-sm" name="edit_sales_customer_phone" value="{{ $invoice->customer_phone }}" maxlength="255"></div>
                        @else
                            <div><span>العميل</span><strong>بدون بيانات عميل</strong></div>
                        @endif
                    </div>

                    <div class="invoice-section-heading">أصناف الفاتورة</div>
                    <div class="table-responsive">
                        <table class="table invoice-items-table invoice-edit-items">
                            <thead><tr><th>الصنف</th><th>الكمية</th><th>سعر الوحدة</th><th>الإجمالي</th></tr></thead>
                            <tbody>
                                @foreach($salesInvoicesDetailsByInvoice->get($invoice->sales_invoice_id, collect()) as $invoiceDetail)
                                    <tr>
                                        <td>
                                            <select name="product_name[]" class="form-control sales-product-select" required>
                                                <option value="{{ $invoiceDetail->product_id }}" selected>{{ $invoiceDetail->product_name }}</option>
                                            </select>
                                        </td>
                                        <td><input type="number" name="quantity[]" class="form-control product-quantity" value="{{ $invoiceDetail->product_quantity }}" min="1" step="1" required></td>
                                        <td><input type="number" name="unit_price[]" class="form-control product-price" value="{{ number_format($invoiceDetail->sales_price, 2, '.', '') }}" min="0" step="0.01" readonly></td>
                                        <td><input type="number" name="total[]" class="form-control product-total" value="{{ number_format($invoiceDetail->product_total_amount, 2, '.', '') }}" readonly></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="invoice-section-heading mt-4">طريقة السداد والحساب</div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="edit_sales_payment_method_{{ $invoice->sales_invoice_id }}">طريقة الدفع</label>
                            <select id="edit_sales_payment_method_{{ $invoice->sales_invoice_id }}" name="edit_sales_payment_method" class="form-control" required>
                                <option value="cash" {{ $invoice->payment_method === 'cash' ? 'selected' : '' }}>نقداً</option>
                                <option value="visa" {{ $invoice->payment_method === 'visa' ? 'selected' : '' }}>بطاقة</option>
                                <option value="credit" {{ $invoice->payment_method === 'credit' ? 'selected' : '' }}>آجل</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="edit_sales_amount_paid_{{ $invoice->sales_invoice_id }}">الدفعة المسجلة عند البيع</label>
                            <input id="edit_sales_amount_paid_{{ $invoice->sales_invoice_id }}" class="form-control edit-invoice-initial-paid" name="edit_sales_amount_paid" type="number" value="{{ number_format($invoice->invoice_paid_amount, 2, '.', '') }}" min="0" step="0.01" required>
                        </div>
                    </div>

                    <div class="invoice-summary-grid invoice-edit-summary">
                        <div class="invoice-summary-item"><span>إجمالي الأصناف</span><strong class="edit-gross-total">{{ number_format($invoice->gross_total, 2) }} ج.م</strong></div>
                        <div class="invoice-summary-item"><span>الخصم الثابت</span><strong>{{ number_format($invoice->invoice_discount, 2) }} ج.م</strong></div>
                        <div class="invoice-summary-item"><span>المرتجعات</span><strong>{{ number_format($invoice->total_returned, 2) }} ج.م</strong></div>
                        <div class="invoice-summary-item"><span>صافي الفاتورة بعد الخصم والمرتجع</span><strong class="edit-net-total">{{ number_format($invoice->net_after_returns, 2) }} ج.م</strong></div>
                        <div class="invoice-summary-item"><span>دفعات الأجل السابقة</span><strong>{{ number_format($invoice->total_paid_transactions, 2) }} ج.م</strong></div>
                        <div class="invoice-summary-item invoice-summary-due"><span>المتبقي بعد التعديل</span><strong class="edit-due-total">{{ number_format($invoice->adjusted_remaining_amount, 2) }} ج.م</strong></div>
                    </div>
                    <input type="hidden" name="edit_sales_invoices_amount" class="edit-gross-input" value="{{ number_format($invoice->gross_total, 2, '.', '') }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                </div>
            </form>
        </div>
    </div>
</div>
