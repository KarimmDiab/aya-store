@if($customerReturnsByInvoice->has($sourceInvoiceId))
    <div class="table-responsive mt-4">
        <h5 class="text-warning">مرتجعات الفاتورة</h5>
        @foreach($customerReturnsByInvoice->get($sourceInvoiceId, collect()) as $return)
            @php
                $returnLines = $returnDetailsByReturnId->get($return->id, collect());
                $grossReturnAmount = (float) $returnLines->sum('total_amount');
            @endphp
            <div class="border rounded p-3 mb-3">
                <div class="d-flex flex-wrap justify-content-between">
                    <strong>مرتجع رقم {{ $return->id }} — {{ $return->created_at }}</strong>
                    <strong class="text-warning">صافي مبلغ المرتجع: {{ number_format($return->total_amount, 2) }} ج.م</strong>
                </div>
                <div class="small text-muted mt-2">
                    قيمة الأصناف المرتجعة: {{ number_format($grossReturnAmount, 2) }} ج.م
                    − خصم الفاتورة الموزع: {{ number_format($return->discount_amount, 2) }} ج.م
                    = صافي المرتجع: {{ number_format($return->total_amount, 2) }} ج.م
                </div>
                @if((float) $return->discount_amount > 0)
                    <div class="text-muted mt-1">يُخصم صافي المرتجع من قيمة الفاتورة.</div>
                @endif
                @if($return->reason)
                    <div class="text-muted mt-1">السبب: {{ $return->reason }}</div>
                @endif
                <table class="table table-sm table-bordered mt-2 mb-0 text-center">
                    <thead>
                        <tr><th>الصنف المرتجع</th><th>الكمية</th><th>سعر الوحدة</th><th>الإجمالي</th></tr>
                    </thead>
                    <tbody>
                        @foreach($returnLines as $line)
                            <tr>
                                <td>{{ $line->product_name }}</td>
                                <td>{{ $line->quantity }}</td>
                                <td>{{ number_format($line->unit_price, 2) }} ج.م</td>
                                <td>{{ number_format($line->total_amount, 2) }} ج.م</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>
@endif
