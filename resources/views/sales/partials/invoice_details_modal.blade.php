<div class="modal fade invoice-modal" id="detailsSalesInvoice{{ $invoice->sales_invoice_id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header invoice-modal-header">
                <div>
                    <h5 class="modal-title">تفاصيل فاتورة المبيعات #{{ $invoice->sales_invoice_id }}</h5>
                    <small>{{ $invoice->invoice_date }}</small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="إغلاق"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="invoice-customer-strip">
                    <div><span>العميل</span><strong>{{ $invoice->customer_name ?: 'بدون بيانات عميل' }}</strong></div>
                    <div><span>رقم الهاتف</span><strong>{{ $invoice->customer_phone ?: '—' }}</strong></div>
                    <div><span>طريقة الدفع</span><strong>{{ ['cash' => 'نقداً', 'visa' => 'بطاقة', 'credit' => 'آجل'][$invoice->payment_method] ?? $invoice->payment_method }}</strong></div>
                </div>

                <div class="invoice-summary-grid">
                    <div class="invoice-summary-item"><span>الإجمالي قبل الخصم</span><strong>{{ number_format($invoice->gross_total, 2) }} ج.م</strong></div>
                    <div class="invoice-summary-item"><span>الخصم</span><strong>{{ number_format($invoice->invoice_discount, 2) }} ج.م</strong></div>
                    <div class="invoice-summary-item"><span>الصافي بعد الخصم</span><strong>{{ number_format($invoice->net_after_discount, 2) }} ج.م</strong></div>
                    <div class="invoice-summary-item"><span>قيمة المرتجعات</span><strong>{{ number_format($invoice->total_returned, 2) }} ج.م</strong></div>
                    <div class="invoice-summary-item"><span>الصافي بعد المرتجعات</span><strong>{{ number_format($invoice->net_after_returns, 2) }} ج.م</strong></div>
                    <div class="invoice-summary-item"><span>إجمالي المقبوض قبل المرتجعات</span><strong>{{ number_format($invoice->total_paid_before_returns, 2) }} ج.م</strong></div>
                    <div class="invoice-summary-item"><span>المخصوم من المدفوع مقابل المرتجع</span><strong>{{ number_format($invoice->paid_amount_deducted_for_returns, 2) }} ج.م</strong></div>
                    <div class="invoice-summary-item"><span>صافي المدفوع بعد خصم المرتجعات</span><strong>{{ number_format($invoice->total_paid_amount, 2) }} ج.م</strong></div>
                    <div class="invoice-summary-item invoice-summary-due"><span>المتبقي الحالي</span><strong>{{ number_format($invoice->adjusted_remaining_amount, 2) }} ج.م</strong></div>
                </div>

                <div class="invoice-section-heading">الأصناف</div>
                <div class="table-responsive">
                    <table class="table invoice-items-table">
                        <thead><tr><th>#</th><th>الصنف</th><th>الكمية</th><th>سعر الوحدة</th><th>الإجمالي</th></tr></thead>
                        <tbody>
                            @forelse($salesInvoicesDetailsByInvoice->get($invoice->sales_invoice_id, collect()) as $invoiceDetail)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $invoiceDetail->product_name }}</td>
                                    <td>{{ $invoiceDetail->product_quantity }}</td>
                                    <td>{{ number_format($invoiceDetail->sales_price, 2) }} ج.م</td>
                                    <td>{{ number_format($invoiceDetail->product_total_amount, 2) }} ج.م</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">لا توجد أصناف في الفاتورة.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <a class="btn btn-outline-primary" href="{{ route('invoice.show', $invoice->sales_invoice_id) }}" target="_blank" rel="noopener">طباعة الفاتورة</a>
                <button type="button" class="btn btn-light" data-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>
