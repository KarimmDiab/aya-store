@extends('layouts.master')

@section('title')
    معرض أيه - صفحة العميل
@endsection

@section('css')
<style>
    /* ---------- Profile header ---------- */
    .cp-profile { display:flex; flex-wrap:wrap; align-items:center; gap:18px; }
    .cp-avatar {
        width:76px; height:76px; border-radius:50%; flex:0 0 76px;
        display:flex; align-items:center; justify-content:center;
        background:linear-gradient(135deg,#0162e8,#4a90ff); color:#fff; font-size:32px; font-weight:700;
        box-shadow:0 6px 16px rgba(1,98,232,.25);
    }
    .cp-name { margin:0 0 6px; font-weight:700; color:#1f2a44; }
    .cp-tag { display:inline-block; padding:3px 12px; border-radius:50px; font-size:12px; font-weight:700; }
    .cp-tag.regular { background:#e6f7ee; color:#13804a; }
    .cp-tag.new     { background:#eaf2ff; color:#0162e8; }
    .cp-contact { color:#5b6477; font-size:14px; margin-top:8px; }
    .cp-contact a { color:#1f2a44; direction:ltr; unicode-bidi:embed; display:inline-block; }
    .cp-contact a:hover { color:#0162e8; text-decoration:none; }

    /* ---------- Stat cards ---------- */
    .cp-stats { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:14px; margin-bottom:20px; }
    .cp-stat { background:#fff; border:1px solid #e6eaf2; border-radius:14px; padding:16px 18px; display:flex; align-items:center; gap:14px; }
    .cp-stat .ico { width:46px; height:46px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:19px; flex:0 0 46px; }
    .cp-stat .lbl { font-size:12.5px; color:#8a93a6; margin-bottom:2px; }
    .cp-stat .val { font-size:20px; font-weight:700; color:#1f2a44; line-height:1.2; }
    .cp-stat .val small { font-size:12px; font-weight:500; color:#8a93a6; }
    .ico-blue   { background:#eaf2ff; color:#0162e8; }
    .ico-orange { background:#fff4e0; color:#e08a00; }
    .ico-green  { background:#e6f7ee; color:#13804a; }
    .ico-red    { background:#fdecee; color:#dc3545; }
    .ico-purple { background:#f1ecff; color:#7c5ce0; }
    .cp-stat.debt { border-color:#f5c6cb; background:#fffafa; }

    /* ---------- Tabs ---------- */
    .cp-tabs.nav-tabs { border-bottom:1px solid #e6eaf2; gap:4px; flex-wrap:wrap; }
    .cp-tabs .nav-link {
        border:0; border-bottom:3px solid transparent; color:#6b7385; font-weight:600;
        padding:12px 16px; background:transparent;
    }
    .cp-tabs .nav-link:hover { color:#0162e8; }
    .cp-tabs .nav-link.active { color:#0162e8; border-bottom-color:#0162e8; background:transparent; }
    .cp-tabs .count {
        display:inline-block; min-width:22px; padding:1px 7px; margin-right:6px; text-align:center;
        background:#f1f2f6; color:#6b7385; border-radius:50px; font-size:12px;
    }
    .cp-tabs .nav-link.active .count { background:#eaf2ff; color:#0162e8; }
    .cp-tab-intro { color:#8a93a6; font-size:13px; margin:18px 0 14px; }

    /* ---------- Tables ---------- */
    .cp-table { width:100% !important; border-collapse:separate; border-spacing:0; }
    .cp-table thead th {
        background:#f7f9fc; color:#5b6477; font-size:13px; font-weight:600; white-space:nowrap;
        text-align:center; border-top:0; border-bottom:1px solid #e6eaf2;
    }
    .cp-table tbody td { text-align:center; vertical-align:middle; font-size:14px; border-top:1px solid #f0f2f7; }
    .cp-table tbody tr:hover { background:#fafbff; }
    .cp-table .num { font-variant-numeric:tabular-nums; white-space:nowrap; }
    .cp-table .btn { padding:5px 10px; font-size:13px; }
    .cp-actions { white-space:nowrap; }
    .money-due  { color:#dc3545; font-weight:700; }
    .money-ok   { color:#13804a; font-weight:700; }
    .cp-pill { display:inline-block; padding:3px 11px; border-radius:50px; font-size:12px; font-weight:600; white-space:nowrap; }
    .pill-warning   { background:#fff4e0; color:#b26a00; }
    .pill-success   { background:#e6f7ee; color:#13804a; }
    .pill-primary   { background:#eaf2ff; color:#0162e8; }
    .pill-secondary { background:#f1f2f6; color:#6b7385; }
    .pill-info      { background:#e5f6fb; color:#0b7a96; }
    .pill-danger    { background:#fdecee; color:#b02a37; }

    .cp-empty { text-align:center; padding:46px 10px; color:#8a93a6; }
    .cp-empty i { font-size:44px; display:block; margin-bottom:10px; opacity:.45; }

    /* ---------- Modals ---------- */
    .modal-content { border:0; border-radius:12px; overflow:hidden; box-shadow:0 20px 50px rgba(20,30,60,.2); }
    .modal-header { border-bottom:1px solid #e6eaf2; background:#f7f9fc; }
    .modal-header .modal-title { font-weight:700; color:#1f2a44; }
    .modal-footer { background:#fafbfd; border-top:1px solid #eef0f6; }
    .cp-kv-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:10px; margin-bottom:18px; }
    .cp-kv { background:#f7f9fc; border-radius:10px; padding:10px 14px; }
    .cp-kv .k { font-size:12px; color:#8a93a6; margin-bottom:2px; }
    .cp-kv .v { font-size:15px; font-weight:700; color:#1f2a44; }
    .cp-section-title { font-size:15px; font-weight:700; color:#0162e8; margin:18px 0 10px; }
    .cp-mini th { background:#f7f9fc; font-size:13px; }
    .cp-mini td, .cp-mini th { text-align:center; vertical-align:middle; }
    .field-error { color:#dc3545; font-size:12px; margin-top:4px; }
    .form-control.is-invalid { border-color:#dc3545; }
    .btn.is-loading { pointer-events:none; opacity:.75; }

    @media (max-width:576px) {
        .cp-profile { flex-direction:column; text-align:center; }
        .cp-tabs .nav-link { padding:10px 12px; font-size:14px; }
    }
</style>
@endsection

@section('page-header')
<!-- breadcrumb -->
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto">العملاء</h4>
            <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ صفحة العميل</span>
        </div>
    </div>
</div>
<!-- breadcrumb -->
@endsection

@section('content')
@php
    $money = fn ($v) => number_format((float) $v, 2);

    // payment method => [label, pill class]
    $pmMap = [
        'credit' => ['آجل', 'pill-warning'],
        'cash'   => ['نقداً', 'pill-success'],
        'visa'   => ['فيزا', 'pill-primary'],
    ];
    $pm = fn ($m) => $pmMap[$m] ?? ['غير محدد', 'pill-secondary'];

    // credit settlement status (same rules as before)
    $creditStatus = function ($inv) {
        $paid = (float) ($inv->paid_amount ?? 0) + (float) ($inv->total_paid_transactions ?? 0);
        if ($inv->outstanding_balance <= 0 && $paid <= 0) return ['لا توجد مديونية', 'pill-info'];
        if ($inv->outstanding_balance <= 0)               return ['تمت التسوية بالكامل', 'pill-success'];
        return ['لم يتم استكمال السداد', 'pill-warning'];
    };

    $customerId   = $customerInfo->id ?? null;
    $isRegular    = ($invoicesInfo->invoices_number ?? 0) > 1;
    $totalDebt    = collect($creditInvoices)->sum(fn ($i) => max(0, (float) $i->outstanding_balance));

    // One details modal per invoice (credit invoices are merged in, de-duplicated)
    $allInvoices  = collect($customerInvoices)->concat(collect($creditInvoices))->unique('sales_invoice_id');

    // payment transactions grouped / keyed for the modals
    $txByInvoice  = collect($transaction)->groupBy('sales_invoice_id');
    $txById       = collect($transaction)->keyBy('id');

    $reopen = $errors->any() ? old('_form') : null;
@endphp

<!-- Flash messages -->
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle ml-1"></i> <strong>نجاح!</strong> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="إغلاق"><span aria-hidden="true">&times;</span></button>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle ml-1"></i> <strong>خطأ!</strong> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="إغلاق"><span aria-hidden="true">&times;</span></button>
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>يرجى تصحيح الأخطاء التالية:</strong>
        <ul class="mb-0 pr-3">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="إغلاق"><span aria-hidden="true">&times;</span></button>
    </div>
@endif

<div class="col-lg-12">

    <!-- Profile -->
    <div class="card mg-b-20">
        <div class="card-body">
            <div class="cp-profile">
                <div class="cp-avatar">{{ mb_substr($customerInfo->name ?? '؟', 0, 1) }}</div>
                <div>
                    <h4 class="cp-name">{{ $customerInfo->name ?? 'لا يوجد اسم' }}</h4>
                    <span class="cp-tag {{ $isRegular ? 'regular' : 'new' }}">{{ $isRegular ? 'عميل دائم' : 'عميل جديد' }}</span>
                    <div class="cp-contact">
                        <i class="fas fa-phone-alt ml-1"></i> رقم العميل:
                        @if (!empty($customerInfo->contact_info))
                            <a href="tel:{{ $customerInfo->contact_info }}">{{ $customerInfo->contact_info }}</a>
                        @else
                            —
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="cp-stats">
        <div class="cp-stat">
            <div class="ico ico-blue"><i class="fas fa-file-invoice"></i></div>
            <div>
                <div class="lbl">عدد الفواتير</div>
                <div class="val">{{ $invoicesInfo->invoices_number }}</div>
            </div>
        </div>
        <div class="cp-stat">
            <div class="ico ico-green"><i class="fas fa-money-bill-wave"></i></div>
            <div>
                <div class="lbl">إجمالي مبلغ الفواتير</div>
                <div class="val">{{ $money($invoicesInfo->total_all_invoices) }} <small>ج.م</small></div>
            </div>
        </div>
        <div class="cp-stat">
            <div class="ico ico-orange"><i class="fas fa-undo"></i></div>
            <div>
                <div class="lbl">إجمالي مبالغ المرتجعات</div>
                <div class="val">{{ $money($invoicesInfo->total_returned) }} <small>ج.م</small></div>
            </div>
        </div>
        <div class="cp-stat">
            <div class="ico ico-purple"><i class="fas fa-clock"></i></div>
            <div>
                <div class="lbl">عدد الفواتير الآجلة</div>
                <div class="val">
                    @if ($noOfCredit > 0) {{ $noOfCredit }} @else <small>لا توجد فواتير آجلة</small> @endif
                </div>
            </div>
        </div>
        <div class="cp-stat {{ $totalDebt > 0 ? 'debt' : '' }}">
            <div class="ico {{ $totalDebt > 0 ? 'ico-red' : 'ico-green' }}"><i class="fas fa-hand-holding-usd"></i></div>
            <div>
                <div class="lbl">إجمالي المديونية الحالية</div>
                <div class="val {{ $totalDebt > 0 ? 'money-due' : 'money-ok' }}">{{ $money($totalDebt) }} <small>ج.م</small></div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="card">
        <div class="card-body">
            <ul class="nav nav-tabs cp-tabs" id="customerTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" href="#home" data-toggle="tab" role="tab">
                        <i class="fas fa-file-invoice ml-1"></i> الفواتير <span class="count">{{ $customerInvoices->count() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#credit_invoice" data-toggle="tab" role="tab">
                        <i class="fas fa-clock ml-1"></i> الفواتير الآجلة <span class="count">{{ $creditInvoices->count() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#transactions" data-toggle="tab" role="tab">
                        <i class="fas fa-exchange-alt ml-1"></i> حركات سداد الفواتير الآجلة <span class="count">{{ $cstTransaction->count() }}</span>
                    </a>
                </li>
            </ul>

            <div class="tab-content pt-2">

                {{-- ============ All invoices ============ --}}
                <div class="tab-pane fade show active" id="home" role="tabpanel">
                    <p class="cp-tab-intro">جميع الفواتير التي قام بها العميل.</p>
                    @if ($customerInvoices->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table cp-table" id="invoicesTable">
                                <thead>
                                    <tr>
                                        <th>كود الفاتورة</th>
                                        <th>تاريخ الفاتورة</th>
                                        <th>طريقة الدفع</th>
                                        <th>الصافي بعد المرتجعات</th>
                                        <th>قيمة المرتجعات</th>
                                        <th>الخصم</th>
                                        <th>صافي المسدد بعد المرتجعات</th>
                                        <th>المتبقي الحالي</th>
                                        <th>تاريخ التسجيل</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($customerInvoices as $invoice)
                                        @php [$pmLabel, $pmClass] = $pm($invoice->payment_method); @endphp
                                        <tr>
                                            <td><b>#{{ $invoice->sales_invoice_id }}</b></td>
                                            <td class="num">{{ $invoice->invoice_date }}</td>
                                            <td><span class="cp-pill {{ $pmClass }}">{{ $pmLabel }}</span></td>
                                            <td class="num" data-order="{{ (float) $invoice->adjusted_total }}">{{ $money($invoice->adjusted_total) }}</td>
                                            <td class="num" data-order="{{ (float) $invoice->total_returned }}">{{ $money($invoice->total_returned) }}</td>
                                            <td class="num" data-order="{{ (float) $invoice->deduction }}">{{ $money($invoice->deduction) }}</td>
                                            <td class="num" data-order="{{ (float) $invoice->total_paid_after_returns }}">{{ $money($invoice->total_paid_after_returns) }}</td>
                                            <td class="num {{ $invoice->outstanding_balance > 0 ? 'money-due' : 'money-ok' }}" data-order="{{ (float) $invoice->outstanding_balance }}">
                                                {{ $money($invoice->outstanding_balance) }}
                                            </td>
                                            <td class="num">{{ $invoice->created_at }}</td>
                                            <td class="cp-actions">
                                                <button type="button" class="btn btn-outline-info" data-toggle="modal"
                                                        data-target="#invoiceModal{{ $invoice->sales_invoice_id }}">
                                                    <i class="fas fa-eye"></i> التفاصيل
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="cp-empty"><i class="fas fa-file-invoice"></i>لا يوجد فواتير مسجلة لهذا العميل</div>
                    @endif
                </div>

                {{-- ============ Credit invoices ============ --}}
                <div class="tab-pane fade" id="credit_invoice" role="tabpanel">
                    <p class="cp-tab-intro">جميع الفواتير الآجلة التي قام بها العميل، ومن هنا يمكنك تسجيل حركات السداد.</p>
                    @if ($creditInvoices->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table cp-table" id="creditTable">
                                <thead>
                                    <tr>
                                        <th>كود الفاتورة</th>
                                        <th>تاريخ الفاتورة</th>
                                        <th>الخصم</th>
                                        <th>الصافي بعد الخصم والمرتجعات</th>
                                        <th>قيمة المرتجعات</th>
                                        <th>صافي المسدد بعد المرتجعات</th>
                                        <th>المديونية الحالية</th>
                                        <th>حالة السداد</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($creditInvoices as $invoice)
                                        @php [$stLabel, $stClass] = $creditStatus($invoice); @endphp
                                        <tr>
                                            <td><b>#{{ $invoice->sales_invoice_id }}</b></td>
                                            <td class="num">{{ $invoice->invoice_date }}</td>
                                            <td class="num" data-order="{{ (float) $invoice->deduction }}">{{ $money($invoice->deduction) }}</td>
                                            <td class="num" data-order="{{ (float) $invoice->adjusted_total }}">{{ $money($invoice->adjusted_total) }}</td>
                                            <td class="num" data-order="{{ (float) $invoice->total_returned }}">{{ $money($invoice->total_returned) }}</td>
                                            <td class="num" data-order="{{ (float) $invoice->total_paid_after_returns }}">{{ $money($invoice->total_paid_after_returns) }}</td>
                                            <td class="num {{ $invoice->outstanding_balance > 0 ? 'money-due' : 'money-ok' }}" data-order="{{ (float) $invoice->outstanding_balance }}">
                                                {{ $money($invoice->outstanding_balance) }}
                                            </td>
                                            <td><span class="cp-pill {{ $stClass }}">{{ $stLabel }}</span></td>
                                            <td class="cp-actions">
                                                <button type="button" class="btn btn-outline-info" data-toggle="modal"
                                                        data-target="#invoiceModal{{ $invoice->sales_invoice_id }}">
                                                    <i class="fas fa-eye"></i> التفاصيل
                                                </button>
                                                @if ($invoice->outstanding_balance > 0)
                                                    <button type="button" class="btn btn-success" data-toggle="modal"
                                                            data-target="#addPaymentModal{{ $invoice->sales_invoice_id }}">
                                                        <i class="fas fa-plus"></i> حركة سداد
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="cp-empty"><i class="fas fa-clock"></i>لا توجد فواتير آجلة لهذا العميل</div>
                    @endif
                </div>

                {{-- ============ Payment transactions ============ --}}
                <div class="tab-pane fade" id="transactions" role="tabpanel">
                    <p class="cp-tab-intro">جميع حركات سداد الفواتير الآجلة التي قام بها العميل.</p>
                    @if ($cstTransaction->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table cp-table" id="txTable">
                                <thead>
                                    <tr>
                                        <th>كود الحركة</th>
                                        <th>كود الفاتورة</th>
                                        <th>تاريخ السداد</th>
                                        <th>المبلغ المدفوع</th>
                                        <th>المبلغ المتبقي</th>
                                        <th>ملاحظات</th>
                                        <th>تاريخ التسجيل</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($cstTransaction as $row)
                                        <tr>
                                            <td>#{{ $row->id }}</td>
                                            <td>#{{ $row->sales_invoice_id }}</td>
                                            <td class="num">{{ $row->payment_date }}</td>
                                            <td class="num money-ok" data-order="{{ (float) $row->amount_paid }}">{{ $money($row->amount_paid) }}</td>
                                            <td class="num" data-order="{{ (float) $row->transaction_remaining_amount }}">{{ $money($row->transaction_remaining_amount) }}</td>
                                            <td class="text-right">{{ $row->notes ?: '—' }}</td>
                                            <td class="num">{{ $row->created_at }}</td>
                                            <td class="cp-actions">
                                                <button type="button" class="btn btn-outline-primary" data-toggle="modal"
                                                        data-target="#editTxModal{{ $row->id }}">
                                                    <i class="fas fa-edit"></i> تعديل
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="cp-empty"><i class="fas fa-exchange-alt"></i>لا يوجد عمليات سداد مسجلة لهذا العميل</div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>

{{-- ==================================================================== --}}
{{-- Modals — rendered once each, outside tables and tabs                 --}}
{{-- ==================================================================== --}}

{{-- Invoice details (shared by the "all invoices" and "credit" tabs) --}}
@foreach ($allInvoices as $inv)
    @php
        [$pmLabel, $pmClass] = $pm($inv->payment_method);
        $netTotal  = $inv->net_total ?? ((float) ($inv->total_amount ?? 0) - (float) ($inv->deduction ?? 0));
        $invTx     = $txByInvoice->get($inv->sales_invoice_id, collect());
        $showPayHx = $inv->payment_method === 'credit' || $invTx->isNotEmpty();
    @endphp
    <div class="modal fade" id="invoiceModal{{ $inv->sales_invoice_id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-invoice-dollar ml-1 text-primary"></i> تفاصيل الفاتورة #{{ $inv->sales_invoice_id }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="إغلاق"><span aria-hidden="true">&times;</span></button>
                </div>

                <div class="modal-body">
                    <div class="cp-kv-grid">
                        <div class="cp-kv"><div class="k">تاريخ الفاتورة</div><div class="v">{{ $inv->invoice_date }}</div></div>
                        <div class="cp-kv"><div class="k">اسم المستخدم</div><div class="v">{{ $inv->username ?? '—' }}</div></div>
                        <div class="cp-kv"><div class="k">طريقة الدفع</div><div class="v"><span class="cp-pill {{ $pmClass }}">{{ $pmLabel }}</span></div></div>
                    </div>

                    <div class="cp-kv-grid">
                        <div class="cp-kv"><div class="k">الإجمالي بعد الخصم</div><div class="v text-success">{{ $money($netTotal) }} جنيه</div></div>
                        <div class="cp-kv"><div class="k">الخصم</div><div class="v text-danger">{{ $money($inv->deduction) }} جنيه</div></div>
                        <div class="cp-kv"><div class="k">قيمة المرتجعات</div><div class="v text-warning">{{ $money($inv->total_returned) }} جنيه</div></div>
                        <div class="cp-kv"><div class="k">الصافي بعد المرتجعات</div><div class="v">{{ $money($inv->adjusted_total) }} جنيه</div></div>
                        <div class="cp-kv"><div class="k">صافي المسدد بعد المرتجعات</div><div class="v text-success">{{ $money($inv->total_paid_after_returns) }} جنيه</div></div>
                        <div class="cp-kv"><div class="k">المتبقي / المديونية</div>
                            <div class="v {{ $inv->outstanding_balance > 0 ? 'money-due' : 'money-ok' }}">{{ $money($inv->outstanding_balance) }} جنيه</div>
                        </div>
                    </div>

                    <div class="cp-section-title"><i class="fas fa-box ml-1"></i> تفاصيل المنتجات</div>
                    <div class="table-responsive">
                        <table class="table table-bordered cp-mini mb-0">
                            <thead><tr><th>اسم المنتج</th><th>الكمية</th><th>سعر الوحدة</th><th>الإجمالي</th></tr></thead>
                            <tbody>
                                @forelse ($invoiceDetails->get($inv->sales_invoice_id, collect()) as $item)
                                    <tr>
                                        <td class="text-right">{{ $item->product_name }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ $money($item->unit_price) }} جنيه</td>
                                        <td>{{ $money($item->total_product) }} جنيه</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-muted">لا توجد منتجات</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($showPayHx)
                        <div class="cp-section-title"><i class="fas fa-history ml-1"></i> سجل الدفعات</div>
                        <div class="table-responsive">
                            <table class="table table-bordered cp-mini mb-0">
                                <thead><tr><th>تاريخ السداد</th><th>المبلغ المدفوع</th><th>المبلغ المتبقي</th><th>ملاحظات</th></tr></thead>
                                <tbody>
                                    @forelse ($invTx as $t)
                                        <tr>
                                            <td>{{ $t->payment_date }}</td>
                                            <td class="text-success">{{ $money($t->amount_paid) }} جنيه</td>
                                            <td class="text-warning">{{ $money($t->transaction_remaining_amount) }} جنيه</td>
                                            <td class="text-right">{{ $t->notes ?: 'لا يوجد' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-muted">لا يوجد دفعات سداد</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @include('customers.partials.invoice_returns', ['sourceInvoiceId' => $inv->sales_invoice_id])
                </div>

                <div class="modal-footer">
                    <a href="{{ route('invoices.print', ['invoice_id' => $inv->sales_invoice_id, 'customer_id' => $customerId]) }}"
                       class="btn btn-info" onclick="return openInvoicePopup(this.href);">
                        <i class="fas fa-print ml-1"></i> طباعة
                    </a>
                    <button type="button" class="btn btn-light" data-dismiss="modal">إغلاق</button>
                </div>
            </div>
        </div>
    </div>
@endforeach

{{-- Add payment (one per outstanding credit invoice) --}}
@foreach ($creditInvoices as $inv)
    @if ($inv->outstanding_balance > 0)
        @php $formKey = 'pay'.$inv->sales_invoice_id; $isOld = old('_form') === $formKey; @endphp
        <div class="modal fade" id="addPaymentModal{{ $inv->sales_invoice_id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <form action="{{ route('customer.payments.store', ['sales_invoice_id' => $inv->sales_invoice_id, 'customer_id' => $customerId]) }}"
                      method="POST" data-payment-form data-max="{{ (float) $inv->outstanding_balance }}" novalidate>
                    @csrf
                    <input type="hidden" name="_form" value="{{ $formKey }}">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-money-bill-wave ml-1 text-success"></i> إضافة حركة سداد — فاتورة #{{ $inv->sales_invoice_id }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="إغلاق"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="cp-kv mb-3">
                                <div class="k">المديونية الحالية على الفاتورة</div>
                                <div class="v money-due">{{ $money($inv->outstanding_balance) }} جنيه</div>
                            </div>
                            <div class="form-group">
                                <label>تاريخ السداد</label>
                                <input type="date" class="form-control js-today" name="payment_date"
                                       value="{{ $isOld ? old('payment_date') : '' }}" required>
                            </div>
                            <div class="form-group">
                                <label>المبلغ المدفوع</label>
                                <input type="number" class="form-control" name="amount_paid" step="0.01" min="0.01"
                                       max="{{ (float) $inv->outstanding_balance }}" inputmode="decimal"
                                       value="{{ $isOld ? old('amount_paid') : '' }}" required>
                            </div>
                            <div class="form-group mb-0">
                                <label>ملاحظات</label>
                                <textarea class="form-control" name="notes" rows="3">{{ $isOld ? old('notes') : '' }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success js-submit">حفظ</button>
                            <button type="button" class="btn btn-light" data-dismiss="modal">إلغاء</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endforeach

{{-- Edit payment transaction --}}
@foreach ($cstTransaction as $row)
    @php
        $tx      = $txById->get($row->id);
        $formKey = 'tx'.$row->id;
        $isOld   = old('_form') === $formKey;
        $txCustomerId = $tx->customer_id ?? $customerId;
    @endphp
    <div class="modal fade" id="editTxModal{{ $row->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form action="{{ route('customer-payment-transactions.customerTransaction', ['customerId' => $txCustomerId, 'invoice_id' => $row->sales_invoice_id, 'transactionId' => $row->id]) }}"
                  method="POST" data-payment-form novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="_form" value="{{ $formKey }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-edit ml-1 text-primary"></i> تعديل حركة السداد #{{ $row->id }} — فاتورة #{{ $row->sales_invoice_id }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="إغلاق"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>تاريخ السداد</label>
                            <input type="date" class="form-control" name="payment_date"
                                   value="{{ $isOld ? old('payment_date') : ($tx->payment_date ?? $row->payment_date) }}" required>
                        </div>
                        <div class="form-group">
                            <label>اسم المستخدم</label>
                            <input type="text" class="form-control" value="{{ $tx->user_name ?? '—' }}" disabled>
                        </div>
                        <div class="form-group">
                            <label>المبلغ المدفوع</label>
                            <input type="number" class="form-control" name="amount_paid" step="0.01" min="0.01" inputmode="decimal"
                                   value="{{ $isOld ? old('amount_paid') : ($tx->amount_paid ?? $row->amount_paid) }}" required>
                        </div>
                        <div class="form-group mb-0">
                            <label>ملاحظات</label>
                            <textarea class="form-control" name="notes" rows="3">{{ $isOld ? old('notes') : ($tx->notes ?? $row->notes) }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success js-submit">تحديث</button>
                        <button type="button" class="btn btn-light" data-dismiss="modal">إلغاء</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endforeach

@endsection

@section('js')
<script src="{{ URL::asset('assets/plugins/datatable/js/jquery.dataTables.js') }}"></script>
<script src="{{ URL::asset('assets/plugins/datatable/js/dataTables.bootstrap4.js') }}"></script>
<script src="{{ URL::asset('assets/plugins/datatable/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ URL::asset('assets/plugins/datatable/js/responsive.bootstrap4.min.js') }}"></script>

<script>
/* Print popup (half width, full height) */
function openInvoicePopup(url) {
    var w = window.innerWidth, h = window.innerHeight, pw = w * 0.5;
    window.open(url, '_blank', 'width=' + pw + ',height=' + h + ',left=' + ((w - pw) / 2) + ',top=0,scrollbars=yes');
    return false;
}

$(function () {

    /* ---------- DataTables ---------- */
    var arabic = {
        search: '', searchPlaceholder: 'بحث...',
        lengthMenu: 'عرض _MENU_',
        info: 'عرض _START_ إلى _END_ من أصل _TOTAL_',
        infoEmpty: 'لا توجد بيانات', infoFiltered: '(تمت التصفية من _MAX_)',
        zeroRecords: 'لا توجد نتائج مطابقة',
        paginate: { first: 'الأول', last: 'الأخير', next: 'التالي', previous: 'السابق' }
    };
    function initTable(sel, orderCol, noSortCol) {
        if (!$(sel).length) return;
        $(sel).DataTable({
            order: [[orderCol, 'desc']],
            pageLength: 10,
            lengthMenu: [10, 25, 50],
            columnDefs: [{ orderable: false, targets: noSortCol }],
            language: arabic
        });
    }
    initTable('#invoicesTable', 0, 9);
    initTable('#creditTable',   0, 8);
    initTable('#txTable',       0, 7);

    // tables initialised inside hidden tabs need a width recalculation when shown
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        try { sessionStorage.setItem('customerTab', $(e.target).attr('href')); } catch (err) {}
    });

    /* ---------- Restore last tab (e.g. after saving a payment) ---------- */
    var wanted = null;
    try { wanted = location.hash || sessionStorage.getItem('customerTab'); } catch (err) { wanted = location.hash; }
    if (wanted && $('#customerTabs a[href="' + wanted + '"]').length) {
        $('#customerTabs a[href="' + wanted + '"]').tab('show');
    }

    /* ---------- Default today's date on new payments ---------- */
    var d = new Date();
    var today = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    $('.js-today').each(function () { if (!this.value) this.value = today; });

    /* ---------- Reopen the modal whose server-side validation failed ---------- */
    @if ($reopen && \Illuminate\Support\Str::startsWith($reopen, 'pay'))
        $('#customerTabs a[href="#credit_invoice"]').tab('show');
        $('#addPaymentModal{{ \Illuminate\Support\Str::after($reopen, 'pay') }}').modal('show');
    @elseif ($reopen && \Illuminate\Support\Str::startsWith($reopen, 'tx'))
        $('#customerTabs a[href="#transactions"]').tab('show');
        $('#editTxModal{{ \Illuminate\Support\Str::after($reopen, 'tx') }}').modal('show');
    @endif

    /* ---------- Client-side validation for payment forms ---------- */
    function showError($input, msg) {
        $input.addClass('is-invalid');
        $input.closest('.form-group').append('<div class="field-error">' + msg + '</div>');
    }
    function clearErrors(form) {
        $(form).find('.field-error').remove();
        $(form).find('.is-invalid').removeClass('is-invalid');
    }

    $('form[data-payment-form]').on('submit', function (e) {
        var form = this, ok = true;
        clearErrors(form);

        var $date   = $(form).find('[name="payment_date"]');
        var $amount = $(form).find('[name="amount_paid"]');
        var amount  = parseFloat($amount.val());
        var max     = parseFloat($(form).data('max'));

        if (!$date.val()) { ok = false; showError($date, 'يرجى إدخال تاريخ صحيح.'); }
        if (isNaN(amount) || amount <= 0) {
            ok = false; showError($amount, 'يرجى إدخال مبلغ مدفوع أكبر من صفر.');
        } else if (!isNaN(max) && amount > max) {
            ok = false; showError($amount, 'المبلغ المدفوع أكبر من المديونية الحالية (' + max.toFixed(2) + ' جنيه).');
        }

        if (!ok) {
            e.preventDefault();
            e.stopImmediatePropagation();
            $(form).find('.is-invalid').first().focus();
        }
    });

    /* ---------- Prevent double submit ---------- */
    $('form[data-payment-form]').on('submit', function (e) {
        if (e.isDefaultPrevented()) return;
        $(this).find('.js-submit').addClass('is-loading').prop('disabled', true)
            .prepend('<span class="spinner-border spinner-border-sm ml-1" role="status"></span> ');
    });

    $('.modal').on('hidden.bs.modal', function () {
        var f = $(this).find('form')[0];
        if (f) clearErrors(f);
    });

    setTimeout(function () { $('.alert-success').fadeOut(400, function () { $(this).remove(); }); }, 5000);
});
</script>
@endsection
