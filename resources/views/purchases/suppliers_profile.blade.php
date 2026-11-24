@extends('layouts.master')

@section('title')
    معرض أيه - المورد
@endsection

@section('css')
<!-- Internal Data table css -->
<link href="{{ URL::asset('assets/plugins/datatable/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet" />
<link href="{{ URL::asset('assets/plugins/datatable/css/responsive.bootstrap4.min.css') }}" rel="stylesheet" />

<style>
    /* Everything is scoped to .supplier-page so nothing leaks into the rest of the app */

    /* ---------- Profile hero ---------- */
    .supplier-page .profile-card { border: 0; border-radius: 14px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,.06); }
    .supplier-page .profile-cover { height: 92px; background: linear-gradient(135deg, #0162e8 0%, #6f42c1 100%); }
    .supplier-page .profile-avatar { width: 96px; height: 96px; border-radius: 50%; border: 4px solid #fff; background: #fff; object-fit: cover; margin-top: -48px; box-shadow: 0 2px 8px rgba(0,0,0,.15); }
    .supplier-page .profile-name { font-weight: 700; margin: 0; }
    .supplier-page .contact-item { display: flex; align-items: center; gap: 10px; padding: 8px 0; }
    .supplier-page .contact-icon { width: 38px; height: 38px; border-radius: 10px; background: #e8f0ff; color: #0162e8; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .supplier-page .contact-label { font-size: 12px; color: #9aa0ae; display: block; line-height: 1.2; }
    .supplier-page .contact-value { font-weight: 600; color: #1f2430; }
    .supplier-page .ltr { direction: ltr; unicode-bidi: embed; display: inline-block; }

    /* ---------- Stat cards ---------- */
    .supplier-page .stat-card { border: 0; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.06); }
    .supplier-page .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }
    .supplier-page .stat-icon.blue   { background: #e8f0ff; color: #0162e8; }
    .supplier-page .stat-icon.green  { background: #e3f8ee; color: #09ad95; }
    .supplier-page .stat-icon.orange { background: #fff1e0; color: #f7931e; }
    .supplier-page .stat-value { font-size: 22px; font-weight: 700; line-height: 1.2; }
    .supplier-page .stat-label { font-size: 13px; color: #7b8190; }

    /* ---------- Tabs ---------- */
    .supplier-page .tabs-card { border: 0; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.06); }
    .supplier-page .supplier-tabs { border-bottom: 2px solid #eef0f6; flex-wrap: nowrap; overflow-x: auto; overflow-y: hidden; }
    .supplier-page .supplier-tabs .nav-link { border: 0; border-bottom: 3px solid transparent; color: #7b8190; font-weight: 600; white-space: nowrap; padding: 12px 18px; margin-bottom: -2px; }
    .supplier-page .supplier-tabs .nav-link:hover { color: #0162e8; }
    .supplier-page .supplier-tabs .nav-link.active { color: #0162e8; border-bottom-color: #0162e8; background: transparent; }
    .supplier-page .tab-count { background: #eef0f6; color: #5b6275; border-radius: 20px; padding: 1px 8px; font-size: 11px; margin-right: 6px; }
    .supplier-page .nav-link.active .tab-count { background: #e8f0ff; color: #0162e8; }
    .supplier-page .tab-intro { color: #7b8190; font-size: 13px; margin-bottom: 16px; }

    /* ---------- Tables ---------- */
    .supplier-page .data-table thead th { background: #f6f7fb; color: #4b5161; font-weight: 600; font-size: 13px; border-bottom: 1px solid #e6e8ef; white-space: nowrap; }
    .supplier-page .data-table tbody td { vertical-align: middle; }
    .supplier-page .data-table tbody tr:hover { background: #f9faff; }
    .supplier-page .badge-soft { padding: 5px 10px; border-radius: 20px; font-weight: 600; font-size: 12px; display: inline-block; white-space: nowrap; }
    .supplier-page .badge-soft.success   { background: #e3f8ee; color: #09ad95; }
    .supplier-page .badge-soft.warning   { background: #fff1e0; color: #d97706; }
    .supplier-page .badge-soft.secondary { background: #f0f1f5; color: #8a90a0; }
    .supplier-page .badge-soft.info      { background: #e8f0ff; color: #0162e8; }
    .supplier-page .money { font-weight: 700; white-space: nowrap; }
    .supplier-page .text-debt { color: #ec2e2e; }
    .supplier-page .date-text { font-size: 12px; color: #8a90a0; white-space: nowrap; }
    .supplier-page .action-group .btn { padding: 5px 10px; border-radius: 8px; }
    .supplier-page .mini-progress { height: 5px; border-radius: 5px; background: #eef0f6; margin-top: 5px; min-width: 90px; }
    .supplier-page .mini-progress > span { display: block; height: 100%; border-radius: 5px; background: #09ad95; }

    .supplier-page .empty-state { text-align: center; padding: 36px 10px; color: #9aa0ae; }
    .supplier-page .empty-state i { font-size: 44px; display: block; margin-bottom: 10px; color: #cfd3de; }

    /* ---------- Modals ---------- */
    .supplier-modal .modal-content { border: 0; border-radius: 14px; overflow: hidden; }
    .supplier-modal .modal-header { border-bottom: 0; }
    .supplier-modal .modal-header.bg-primary-grad { background: linear-gradient(135deg, #0162e8, #6f42c1); color: #fff; }
    .supplier-modal .modal-header.bg-success-grad { background: linear-gradient(135deg, #09ad95, #0a8f7c); color: #fff; }
    .supplier-modal .modal-header .close { color: #fff; opacity: .85; text-shadow: none; }
    .supplier-modal .info-tile { background: #f6f7fb; border-radius: 10px; padding: 10px 14px; height: 100%; }
    .supplier-modal .info-tile .k { font-size: 12px; color: #8a90a0; display: block; }
    .supplier-modal .info-tile .v { font-weight: 700; color: #1f2430; }
    .supplier-modal .section-title { font-size: 14px; font-weight: 700; color: #0162e8; margin: 18px 0 8px; }
    .supplier-modal .field-error, .supplier-page .field-error { color: #ec2e2e; font-size: 12px; margin-top: 4px; display: none; }
    .supplier-modal .form-control.is-invalid ~ .field-error { display: block; }

    /* ---------- Purchase invoice form ---------- */
    .supplier-page .items-table thead th { background: #343a40; color: #fff; font-size: 12px; white-space: nowrap; text-align: center; }
    .supplier-page .items-table td { padding: 6px; vertical-align: middle; }
    .supplier-page .items-table .form-control { min-width: 80px; }
    .supplier-page .items-table .col-cat  { min-width: 170px; }
    .supplier-page .items-table .col-name { min-width: 170px; }
    .supplier-page .items-table .form-control[readonly] { background: #f6f7fb; font-weight: 600; }
    .supplier-page .items-table .profit.negative { color: #ec2e2e; }
    .supplier-page .items-table .form-control.is-invalid, .supplier-page .invoice-form .form-control.is-invalid { border-color: #ec2e2e; }
    .supplier-page .summary-box { background: #f9faff; border: 1px solid #eef0f6; border-radius: 12px; padding: 16px 16px 4px; }
    .supplier-page .summary-box label { font-size: 12px; color: #7b8190; margin-bottom: 4px; }
    .supplier-page .summary-box .form-control[readonly] { background: #fff; font-weight: 700; }
    .supplier-page .remaining-negative { color: #ec2e2e; }
    .supplier-page .btn.is-loading { pointer-events: none; opacity: .75; }

    /* ---------- DataTables tweaks ---------- */
    .supplier-page .dataTables_filter input { border-radius: 8px; min-width: 200px; }
    .supplier-page .dataTables_length select { border-radius: 8px; }
</style>
@endsection

@section('page-header')
<!-- breadcrumb -->
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto">الموردون</h4>
            <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ ملف المورد</span>
        </div>
    </div>
</div>
<!-- breadcrumb -->
@endsection

@section('content')

@php
    // ---------- Helpers ----------
    $fmt   = fn ($d, $f = 'Y-m-d H:i') => $d ? \Carbon\Carbon::parse($d)->format($f) : '—';
    $ts    = fn ($d) => $d ? \Carbon\Carbon::parse($d)->timestamp : 0;
    $money = fn ($n) => number_format((float) $n, 2);
    $pm    = function ($m) {
        if ($m === 'credit') return ['آجل', 'warning'];
        if ($m === 'cash')   return ['نقداً', 'success'];
        return [$m ?: 'غير محدد', 'secondary'];
    };

    // ---------- Data prepared once ----------
    $supplierName    = optional($suppliersInfo)->supplier_name ?? $suppliers->name ?? 'بدون اسم';
    $invoicesCount   = (int) (optional($suppliersInfo)->number_of_invoice ?? 0);
    $isRegular       = $invoicesCount > 1;
    $totalAll        = (float) (optional($suppliersInfo)->total_of_all_invoices ?? 0);
    $creditCount     = (int) (optional($suppliersInfo)->number_of_credit_invoices ?? 0);
    $printSupplierId = optional($suppliersInfo)->id ?? $suppliers->id;

    $phone    = trim((string) $suppliers->contact_info);
    $waNumber = preg_match('/^01[0-9]{9}$/', $phone) ? '2' . $phone : null;

    $purchaseInvoices = collect($supplierPurchaseInvoice);
    $creditInvoices   = collect($creditInvoiceNumbers);
    $payments         = collect($transaction);

    // Group once instead of looping inside loops (this also removes the duplicated modals)
    $detailsByInvoice       = collect($purchaseDetails)->groupBy('purchase_invoices_id');
    $creditDetailsByInvoice = collect($creditDetails)->groupBy('purchase_invoices_id');
    $paymentsByInvoice      = $payments->groupBy('purchases_invoice_id');
    $purchaseReturnDetailsByInvoice = $purchaseReturnDetailsByInvoice ?? collect();
    $supplierPurchaseReturnAmount = collect($purchaseReturnSummaries ?? [])->sum('return_amount');

    // Barcode data per invoice (rendered by JS when printing)
    $barcodeMap = $detailsByInvoice->mapWithKeys(function ($items, $key) {
        return ['i' . $key => $items->map(fn ($d) => [
            'id'    => $d->product_id,
            'name'  => $d->product_name,
            'price' => $d->product_price,
            'qty'   => (int) $d->quantity,
        ])->values()];
    });
@endphp

<div class="supplier-page">

    {{-- ============ Flash messages ============ --}}
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
            <strong>هناك أخطاء في الإدخال:</strong>
            <ul class="mb-0 pr-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="إغلاق"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    {{-- ============ Profile hero ============ --}}
    <div class="card profile-card mg-b-20">
        <div class="profile-cover"></div>
        <div class="card-body pt-0">
            <div class="d-flex flex-wrap align-items-end">
                <img class="profile-avatar" alt="" src="{{ URL::asset('images/general_pic/customer_pic.png') }}">
                <div class="mr-3 mt-3">
                    <h4 class="profile-name">{{ $supplierName }}</h4>
                    <span class="badge-soft {{ $isRegular ? 'success' : 'info' }} mt-1">
                        <i class="fas {{ $isRegular ? 'fa-medal' : 'fa-seedling' }} ml-1"></i>
                        {{ $isRegular ? 'مورد دائم' : 'مورد جديد' }}
                    </span>
                </div>
            </div>

            <hr class="mg-y-15">

            <div class="row">
                <div class="col-12 col-md-4">
                    <div class="contact-item">
                        <span class="contact-icon"><i class="fas fa-phone-alt"></i></span>
                        <div>
                            <span class="contact-label">رقم المورد</span>
                            <span class="contact-value">
                                @if ($phone !== '')
                                    <a href="tel:{{ $phone }}" class="ltr text-dark">{{ $phone }}</a>
                                    @if ($waNumber)
                                        <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener" class="mr-2 text-success" title="واتساب">
                                            <i class="fab fa-whatsapp"></i>
                                        </a>
                                    @endif
                                @else
                                    —
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-8">
                    <div class="contact-item">
                        <span class="contact-icon"><i class="fas fa-map-marker-alt"></i></span>
                        <div>
                            <span class="contact-label">عنوان المورد</span>
                            <span class="contact-value">{{ $suppliers->address ?: '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============ Stats ============ --}}
    <div class="row row-sm">
        <div class="col-12 col-md-3 mb-3">
            <div class="card stat-card"><div class="card-body d-flex align-items-center">
                <div class="stat-icon blue ml-3"><i class="fas fa-layer-group"></i></div>
                <div>
                    <div class="stat-value">{{ $invoicesCount > 0 ? number_format($invoicesCount) : 'لا توجد فواتير' }}</div>
                    <div class="stat-label">عدد الفواتير</div>
                </div>
            </div></div>
        </div>
        <div class="col-12 col-md-3 mb-3">
            <div class="card stat-card"><div class="card-body d-flex align-items-center">
                <div class="stat-icon green ml-3"><i class="fas fa-money-bill-wave"></i></div>
                <div>
                    <div class="stat-value">{{ $suppliersInfo ? $money($totalAll) . ' ج.م' : 'لا توجد فواتير' }}</div>
                    <div class="stat-label">إجمالي مبلغ الفواتير</div>
                </div>
            </div></div>
        </div>
        <div class="col-12 col-md-3 mb-3">
            <div class="card stat-card"><div class="card-body d-flex align-items-center">
                <div class="stat-icon orange ml-3"><i class="fas fa-file-invoice"></i></div>
                <div>
                    <div class="stat-value">{{ $creditCount > 0 ? number_format($creditCount) : 'لا توجد فواتير آجلة' }}</div>
                    <div class="stat-label">عدد الفواتير الآجلة</div>
                </div>
            </div></div>
        </div>
        <div class="col-12 col-md-3 mb-3">
            <div class="card stat-card"><div class="card-body d-flex align-items-center">
                <div class="stat-icon orange ml-3"><i class="fas fa-undo"></i></div>
                <div><div class="stat-value">{{ $money($supplierPurchaseReturnAmount) }} ج.م</div><div class="stat-label">مرتجعات المشتريات</div></div>
            </div></div>
        </div>
    </div>

    {{-- ============ Tabs ============ --}}
    <div class="card tabs-card">
        <div class="card-body">
            <ul class="nav nav-tabs supplier-tabs" id="supplierTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#home" role="tab">
                        <i class="fas fa-file-invoice-dollar ml-1"></i> الفواتير
                        <span class="tab-count">{{ $purchaseInvoices->count() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#credit_invoice" role="tab">
                        <i class="fas fa-hourglass-half ml-1"></i> الفواتير الآجلة
                        <span class="tab-count">{{ $creditInvoices->count() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#transactions" role="tab">
                        <i class="fas fa-exchange-alt ml-1"></i> حركات سداد الفواتير الآجلة
                        <span class="tab-count">{{ $payments->count() }}</span>
                    </a>
                </li>
                <li class="nav-item mr-auto">
                    <a class="btn btn-primary" href="{{ route('purchase_invoices', ['supplier_id' => $suppliers->id, 'create' => 1]) }}">
                        <i class="fas fa-plus-circle ml-1"></i> تسجيل فاتورة مشتريات
                    </a>
                </li>
            </ul>

            <div class="tab-content pt-4">

                {{-- ===== Tab 1: purchase invoices ===== --}}
                <div class="tab-pane fade show active" id="home" role="tabpanel">
                    <p class="tab-intro">جميع فواتير المشتريات التي تم تسجيلها لهذا المورد.</p>

                    @if ($purchaseInvoices->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table text-md-nowrap data-table" id="invoicesTable">
                                <thead>
                                    <tr>
                                        <th>كود الفاتورة</th>
                                        <th>تاريخ الفاتورة</th>
                                        <th>المبلغ</th>
                                        <th>مرتجعات المورد</th>
                                        <th>طريقة الدفع</th>
                                        <th>المبلغ المدفوع</th>
                                        <th>تاريخ التسجيل</th>
                                        <th class="text-center no-sort">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($purchaseInvoices as $invoice)
                                        @php [$pmLabel, $pmClass] = $pm($invoice->payment_method); @endphp
                                        <tr>
                                            <td data-order="{{ (int) $invoice->id }}"><strong>#{{ $invoice->id }}</strong></td>
                                            <td data-order="{{ $ts($invoice->date) }}">{{ $fmt($invoice->date, 'Y-m-d') }}</td>
                                            <td data-order="{{ (float) $invoice->total_amount }}"><span class="money">{{ $money($invoice->total_amount) }} ج.م</span></td>
                                            <td data-order="{{ (float) $invoice->purchase_return_amount }}"><span class="money {{ $invoice->purchase_return_amount > 0 ? 'text-debt' : '' }}">{{ $money($invoice->purchase_return_amount) }} ج.م</span>@if($invoice->purchase_return_count > 0)<small class="d-block">{{ $invoice->purchase_return_count }} مرتجع · {{ number_format($invoice->purchase_return_quantity) }} وحدة</small>@endif</td>
                                            <td><span class="badge-soft {{ $pmClass }}">{{ $pmLabel }}</span></td>
                                            <td data-order="{{ (float) $invoice->total_paid_amount }}">{{ $money($invoice->total_paid_amount) }} ج.م</td>
                                            <td data-order="{{ $ts($invoice->created_at) }}"><span class="date-text"><i class="far fa-clock ml-1"></i>{{ $fmt($invoice->created_at) }}</span></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#invoiceDetailsModal{{ $invoice->id }}">
                                                    <i class="fas fa-eye ml-1"></i> عرض التفاصيل
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-file-invoice"></i>
                            لا توجد فواتير مسجلة لهذا المورد
                        </div>
                    @endif
                </div>

                {{-- ===== Tab 2: credit invoices ===== --}}
                <div class="tab-pane fade" id="credit_invoice" role="tabpanel">
                    <p class="tab-intro">الفواتير الآجلة الخاصة بهذا المورد وحالة سدادها. يمكنك تسجيل حركة سداد جديدة من عمود الإجراءات.</p>

                    @if ($creditInvoices->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table text-md-nowrap data-table" id="creditInvoicesTable" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th>كود الفاتورة</th>
                                        <th>تاريخ الفاتورة</th>
                                        <th>المبلغ</th>
                                        <th>مرتجعات المورد</th>
                                        <th>طريقة الدفع</th>
                                        <th>المبلغ المدفوع</th>
                                        <th>المبلغ المتبقي</th>
                                        <th>حالة السداد</th>
                                        <th class="text-center no-sort">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($creditInvoices as $invoice)
                                        @php
                                            [$pmLabel, $pmClass] = $pm($invoice->payment_method);
                                            $total     = (float) $invoice->total_amount;
                                            $remaining = (float) $invoice->payment_remaning_amount;
                                            $isPaid    = $remaining <= 0;
                                            $percent   = $total > 0 ? max(0, min(100, (($total - $remaining) / $total) * 100)) : 0;
                                        @endphp
                                        <tr>
                                            <td data-order="{{ (int) $invoice->purchase_invoices_id }}"><strong>#{{ $invoice->purchase_invoices_id }}</strong></td>
                                            <td data-order="{{ $ts($invoice->date) }}">{{ $fmt($invoice->date, 'Y-m-d') }}</td>
                                            <td data-order="{{ $total }}"><span class="money">{{ $money($total) }} ج.م</span></td>
                                            <td data-order="{{ (float) $invoice->purchase_return_amount }}"><span class="money {{ $invoice->purchase_return_amount > 0 ? 'text-debt' : '' }}">{{ $money($invoice->purchase_return_amount) }} ج.م</span>@if($invoice->purchase_return_count > 0)<small class="d-block">{{ $invoice->purchase_return_count }} مرتجع · {{ number_format($invoice->purchase_return_quantity) }} وحدة</small>@endif</td>
                                            <td><span class="badge-soft {{ $pmClass }}">{{ $pmLabel }}</span></td>
                                            <td data-order="{{ (float) $invoice->total_paid_amount }}">{{ $money($invoice->total_paid_amount) }} ج.م</td>
                                            <td data-order="{{ $remaining }}">
                                                <span class="money {{ $remaining > 0 ? 'text-debt' : '' }}">{{ $money($remaining) }} ج.م</span>
                                                <div class="mini-progress" title="نسبة السداد {{ round($percent) }}%"><span style="width: {{ $percent }}%"></span></div>
                                            </td>
                                            <td>
                                                @if ($isPaid)
                                                    <span class="badge-soft success"><i class="fas fa-check-circle ml-1"></i>تم السداد بالكامل</span>
                                                @else
                                                    <span class="badge-soft warning"><i class="fas fa-hourglass-half ml-1"></i>لم يتم استكمال السداد</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="action-group btn-group">
                                                    <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#creditDetailsModal{{ $invoice->purchase_invoices_id }}">
                                                        <i class="fas fa-eye ml-1"></i> التفاصيل
                                                    </button>
                                                    @if ($remaining > 0)
                                                        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#addPaymentModal{{ $invoice->purchase_invoices_id }}">
                                                            <i class="fas fa-plus ml-1"></i> حركة سداد
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-hourglass-half"></i>
                            لا توجد فواتير آجلة مسجلة لهذا المورد
                        </div>
                    @endif
                </div>

                {{-- ===== Tab 3: payment transactions ===== --}}
                <div class="tab-pane fade" id="transactions" role="tabpanel">
                    <p class="tab-intro">جميع حركات سداد الفواتير الآجلة لهذا المورد.</p>

                    @if ($payments->isNotEmpty())
                        <div class="row mb-3">
                            <div class="col-12 col-md-4">
                                <div class="card stat-card mb-0"><div class="card-body py-3 d-flex align-items-center">
                                    <div class="stat-icon green ml-3"><i class="fas fa-hand-holding-usd"></i></div>
                                    <div>
                                        <div class="stat-value">{{ $money($payments->sum(fn ($p) => (float) $p->amount_paid)) }} <small class="tx-12">ج.م</small></div>
                                        <div class="stat-label">إجمالي المسدد</div>
                                    </div>
                                </div></div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table text-md-nowrap data-table" id="paymentsTable" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th>كود الحركة</th>
                                        <th>كود الفاتورة</th>
                                        <th>تاريخ السداد</th>
                                        <th>المبلغ المدفوع</th>
                                        <th>المبلغ المتبقي</th>
                                        <th>ملاحظات</th>
                                        <th>تاريخ تسجيل الحركة</th>
                                        <th class="text-center no-sort">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($payments as $t)
                                        <tr>
                                            <td data-order="{{ (int) $t->supplier_payments_id }}">#{{ $t->supplier_payments_id }}</td>
                                            <td data-order="{{ (int) $t->purchases_invoice_id }}"><span class="badge-soft info">فاتورة #{{ $t->purchases_invoice_id }}</span></td>
                                            <td data-order="{{ $ts($t->payment_date) }}">{{ $fmt($t->payment_date, 'Y-m-d') }}</td>
                                            <td data-order="{{ (float) $t->amount_paid }}"><span class="money text-success">{{ $money($t->amount_paid) }} ج.م</span></td>
                                            <td data-order="{{ (float) $t->payment_remaning_amount }}">{{ $money($t->payment_remaning_amount) }} ج.م</td>
                                            <td style="max-width: 220px; white-space: normal;">{{ $t->notes ?: '—' }}</td>
                                            <td data-order="{{ $ts($t->created_at) }}"><span class="date-text"><i class="far fa-clock ml-1"></i>{{ $fmt($t->created_at) }}</span></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#transactionCreditDetailsModal{{ $t->supplier_payments_id }}">
                                                    <i class="fas fa-edit ml-1"></i> تعديل
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-exchange-alt"></i>
                            لا توجد عمليات سداد مسجلة لهذا المورد
                        </div>
                    @endif
                </div>

{{-- =====================================================================
     MODALS — rendered once each, outside of the tables
     ===================================================================== --}}

{{-- ---------- Purchase invoice details ---------- --}}
@foreach ($purchaseInvoices as $invoice)
    @php
        $items = $detailsByInvoice->get($invoice->id, collect());
        $purchaseReturns = $purchaseReturnDetailsByInvoice->get($invoice->id, collect());
        [$pmLabel, $pmClass] = $pm($invoice->payment_method);
    @endphp
    <div class="modal fade supplier-modal" id="invoiceDetailsModal{{ $invoice->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary-grad">
                    <h5 class="modal-title"><i class="fas fa-file-invoice-dollar ml-1"></i> تفاصيل الفاتورة #{{ $invoice->id }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="إغلاق"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-6 col-md-3 mb-2"><div class="info-tile"><span class="k">تاريخ الفاتورة</span><span class="v">{{ $fmt($invoice->date, 'Y-m-d') }}</span></div></div>
                        <div class="col-6 col-md-3 mb-2"><div class="info-tile"><span class="k">اسم المستخدم</span><span class="v">{{ $invoice->user_name ?? '—' }}</span></div></div>
                        <div class="col-6 col-md-3 mb-2"><div class="info-tile"><span class="k">المبلغ الإجمالي</span><span class="v text-success">{{ $money($invoice->total_amount) }} ج.م</span></div></div>
                        <div class="col-6 col-md-3 mb-2"><div class="info-tile"><span class="k">مرتجعات المورد</span><span class="v text-debt">{{ $money($invoice->purchase_return_amount) }} ج.م · {{ $invoice->purchase_return_count }} مرتجع</span></div></div>
                        <div class="col-6 col-md-3 mb-2"><div class="info-tile"><span class="k">طريقة الدفع</span><span class="badge-soft {{ $pmClass }}">{{ $pmLabel }}</span></div></div>
                    </div>

                    <div class="section-title"><i class="fas fa-boxes ml-1"></i> أصناف الفاتورة</div>
                    @if ($items->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm text-center mb-0">
                                <thead class="thead-light">
                                    <tr><th>اسم المنتج</th><th>الكمية</th><th>سعر الوحدة</th><th>الإجمالي</th><th>سعر البيع</th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $detail)
                                        <tr>
                                            <td>{{ $detail->product_name }}</td>
                                            <td>{{ $detail->quantity }}</td>
                                            <td>{{ $money($detail->price_per_pices) }} ج.م</td>
                                            <td>{{ $money($detail->total_price) }} ج.م</td>
                                            <td>{{ $detail->product_price }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-muted text-center py-3">لا توجد أصناف مسجلة لهذه الفاتورة.</div>
                    @endif

                    <div class="section-title"><i class="fas fa-undo ml-1"></i> مرتجعات المشتريات المسجلة</div>
                    @if ($purchaseReturns->isNotEmpty())
                        <div class="table-responsive"><table class="table table-bordered table-sm text-center mb-0">
                            <thead class="thead-light"><tr><th>رقم المرتجع</th><th>التاريخ</th><th>الصنف</th><th>الكمية</th><th>تكلفة الوحدة</th><th>الإجمالي</th></tr></thead>
                            <tbody>@foreach ($purchaseReturns as $purchaseReturn)<tr>
                                <td><a href="{{ route('purchases.returns.show', $purchaseReturn->return_invoice_id) }}">#{{ $purchaseReturn->return_invoice_id }}</a></td><td>{{ $fmt($purchaseReturn->return_date) }}</td><td>{{ $purchaseReturn->product_name ?? 'منتج محذوف' }}</td><td>{{ number_format($purchaseReturn->quantity) }}</td><td>{{ $money($purchaseReturn->unit_price) }} ج.م</td><td class="text-debt">{{ $money($purchaseReturn->total_amount) }} ج.م</td>
                            </tr>@endforeach</tbody>
                        </table></div>
                    @else
                        <div class="text-muted text-center py-2">لا توجد مرتجعات مسجلة لهذه الفاتورة.</div>
                    @endif
                </div>
                <div class="modal-footer">
                    <a class="btn btn-outline-danger" href="{{ route('purchases.returns.index', ['invoice_id' => $invoice->id]) }}"><i class="fas fa-undo ml-1"></i> تسجيل / عرض المرتجعات</a>
                    <a href="{{ route('suppliers.printInvoice', ['invoice_no' => $invoice->id, 'supplier_id' => $printSupplierId]) }}"
                       class="btn btn-info js-popup-print" target="_blank" rel="noopener"><i class="fas fa-print ml-1"></i> طباعة</a>
                    @if ($items->isNotEmpty())
                        <button type="button" class="btn btn-primary js-print-barcodes" data-invoice="{{ $invoice->id }}"><i class="fas fa-barcode ml-1"></i> طباعة جميع الباركود</button>
                    @endif
                    <button type="button" class="btn btn-light" data-dismiss="modal">إغلاق</button>
                </div>
            </div>
        </div>
    </div>
@endforeach

{{-- ---------- Credit invoice details + add payment ---------- --}}
@foreach ($creditInvoices as $invoice)
    @php
        $invId     = $invoice->purchase_invoices_id;
        $items     = $creditDetailsByInvoice->get($invId, collect());
        $purchaseReturns = $purchaseReturnDetailsByInvoice->get($invId, collect());
        $head      = $items->first();
        $invPays   = $paymentsByInvoice->get($invId, collect());
        $remaining = (float) $invoice->payment_remaning_amount;

        $hDate   = optional($head)->date ?? $invoice->date;
        $hUser   = optional($head)->username ?? '—';
        $hTotal  = optional($head)->total_amount ?? $invoice->total_amount;
        $hMethod = optional($head)->payment_method ?? $invoice->payment_method;
        $hPaid   = $invoice->total_paid_amount;
        $hRemain = $invoice->payment_remaning_amount;
        [$pmLabel, $pmClass] = $pm($hMethod);
    @endphp

    <!-- Credit invoice details -->
    <div class="modal fade supplier-modal" id="creditDetailsModal{{ $invId }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary-grad">
                    <h5 class="modal-title"><i class="fas fa-file-invoice-dollar ml-1"></i> تفاصيل الفاتورة الآجلة #{{ $invId }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="إغلاق"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-6 col-md-3 mb-2"><div class="info-tile"><span class="k">تاريخ الفاتورة</span><span class="v">{{ $fmt($hDate, 'Y-m-d') }}</span></div></div>
                        <div class="col-6 col-md-3 mb-2"><div class="info-tile"><span class="k">اسم المستخدم</span><span class="v">{{ $hUser }}</span></div></div>
                        <div class="col-6 col-md-3 mb-2"><div class="info-tile"><span class="k">طريقة الدفع</span><span class="badge-soft {{ $pmClass }}">{{ $pmLabel }}</span></div></div>
                        <div class="col-6 col-md-3 mb-2"><div class="info-tile"><span class="k">المبلغ الإجمالي</span><span class="v text-success">{{ $money($hTotal) }} ج.م</span></div></div>
                        <div class="col-6 col-md-3 mb-2"><div class="info-tile"><span class="k">مرتجعات المورد</span><span class="v text-debt">{{ $money($invoice->purchase_return_amount) }} ج.م · {{ $invoice->purchase_return_count }} مرتجع</span></div></div>
                        <div class="col-6 col-md-3 mb-2"><div class="info-tile"><span class="k">المبلغ المدفوع</span><span class="v text-success">{{ $money($hPaid) }} ج.م</span></div></div>
                        <div class="col-6 col-md-3 mb-2"><div class="info-tile"><span class="k">المبلغ المتبقي</span><span class="v text-debt">{{ $money($hRemain) }} ج.م</span></div></div>
                    </div>

                    <div class="section-title"><i class="fas fa-boxes ml-1"></i> تفاصيل المنتجات</div>
                    @if ($items->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm text-center mb-0">
                                <thead class="thead-light">
                                    <tr><th>اسم المنتج</th><th>الكمية</th><th>سعر الوحدة</th><th>الإجمالي</th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $item)
                                        <tr>
                                            <td>{{ $item->product_name }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>{{ $money($item->price_per_pices) }} ج.م</td>
                                            <td>{{ $money($item->total_price) }} ج.م</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-muted text-center py-3">لا توجد أصناف مسجلة لهذه الفاتورة.</div>
                    @endif

                    <div class="section-title"><i class="fas fa-undo ml-1"></i> مرتجعات المشتريات المسجلة</div>
                    @if ($purchaseReturns->isNotEmpty())
                        <div class="table-responsive"><table class="table table-bordered table-sm text-center mb-0">
                            <thead class="thead-light"><tr><th>رقم المرتجع</th><th>التاريخ</th><th>الصنف</th><th>الكمية</th><th>تكلفة الوحدة</th><th>الإجمالي</th></tr></thead>
                            <tbody>@foreach ($purchaseReturns as $purchaseReturn)<tr>
                                <td><a href="{{ route('purchases.returns.show', $purchaseReturn->return_invoice_id) }}">#{{ $purchaseReturn->return_invoice_id }}</a></td><td>{{ $fmt($purchaseReturn->return_date) }}</td><td>{{ $purchaseReturn->product_name ?? 'منتج محذوف' }}</td><td>{{ number_format($purchaseReturn->quantity) }}</td><td>{{ $money($purchaseReturn->unit_price) }} ج.م</td><td class="text-debt">{{ $money($purchaseReturn->total_amount) }} ج.م</td>
                            </tr>@endforeach</tbody>
                        </table></div>
                    @else
                        <div class="text-muted text-center py-2">لا توجد مرتجعات مسجلة لهذه الفاتورة.</div>
                    @endif

                    <div class="section-title"><i class="fas fa-history ml-1"></i> سجل الدفعات</div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm text-center mb-0">
                            <thead class="thead-light">
                                <tr><th>تاريخ السداد</th><th>المبلغ المدفوع</th><th>المبلغ المتبقي</th><th>ملاحظات</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($invPays as $p)
                                    <tr>
                                        <td>{{ $fmt($p->payment_date, 'Y-m-d') }}</td>
                                        <td class="text-success">{{ $money($p->amount_paid) }} ج.م</td>
                                        <td class="text-debt">{{ $money($p->payment_remaning_amount) }} ج.م</td>
                                        <td>{{ $p->notes ?: 'لا يوجد' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-muted">لا يوجد دفعات سداد</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <a class="btn btn-outline-danger" href="{{ route('purchases.returns.index', ['invoice_id' => $invId]) }}"><i class="fas fa-undo ml-1"></i> تسجيل / عرض المرتجعات</a>
                    <a href="{{ route('suppliers.printInvoice', ['invoice_no' => $invId, 'supplier_id' => $printSupplierId]) }}"
                       class="btn btn-info js-popup-print" target="_blank" rel="noopener"><i class="fas fa-print ml-1"></i> طباعة</a>
                    <button type="button" class="btn btn-light" data-dismiss="modal">إغلاق</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add payment -->
    <div class="modal fade supplier-modal" id="addPaymentModal{{ $invId }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success-grad">
                    <h5 class="modal-title"><i class="fas fa-money-bill-wave ml-1"></i> إضافة حركة سداد للفاتورة #{{ $invId }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="إغلاق"><span aria-hidden="true">&times;</span></button>
                </div>
                <form action="{{ route('supplierPaymentStore', ['purchase_id' => $invId, 'supplier_id' => $invoice->supplier_id]) }}"
                      method="POST" class="payment-form" data-remaining="{{ $remaining }}" novalidate>
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-light border py-2 tx-13">
                            المبلغ المتبقي على الفاتورة: <strong class="text-debt">{{ $money($remaining) }} ج.م</strong>
                        </div>

                        <div class="form-group">
                            <label>تاريخ السداد <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="payment_date" value="{{ now()->format('Y-m-d') }}" required>
                            <div class="field-error">يرجى إدخال تاريخ صحيح.</div>
                        </div>

                        <div class="form-group">
                            <label>المبلغ المدفوع <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="amount_paid" step="0.01" min="0.01" placeholder="0.00" required>
                                @if ($remaining > 0)
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-light border js-fill-remaining" data-remaining="{{ $remaining }}">سداد المتبقي</button>
                                    </div>
                                @endif
                            </div>
                            <div class="field-error">يرجى إدخال المبلغ المدفوع بشكل صحيح (0.01 على الأقل).</div>
                        </div>

                        <div class="form-group mb-0">
                            <label>ملاحظات</label>
                            <textarea class="form-control" name="notes" rows="3" maxlength="255" placeholder="اختياري"></textarea>
                            <div class="field-error">يجب ألا تتجاوز الملاحظات 255 حرفًا.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success"><i class="fas fa-save ml-1"></i> حفظ</button>
                        <button type="button" class="btn btn-light" data-dismiss="modal">إغلاق</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

{{-- ---------- Edit payment transaction ---------- --}}
@foreach ($payments as $t)
    <div class="modal fade supplier-modal" id="transactionCreditDetailsModal{{ $t->supplier_payments_id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary-grad">
                    <h5 class="modal-title">
                        <i class="fas fa-edit ml-1"></i> تعديل حركة السداد #{{ $t->supplier_payments_id }} — الفاتورة #{{ $t->purchases_invoice_id }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="إغلاق"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="supplierPaymentForm_{{ $t->supplier_payments_id }}" class="payment-form" novalidate method="POST"
                      action="{{ route('supplier-payment-transactions.supplierTransaction', ['supplierID' => $t->supplier_id, 'invoice_id' => $t->purchases_invoice_id, 'transactionId' => $t->supplier_payments_id]) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>تاريخ السداد <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" class="form-control" value="{{ substr((string) $t->payment_date, 0, 10) }}" required>
                                <div class="field-error">يرجى إدخال تاريخ صحيح.</div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>اسم المستخدم</label>
                                <input type="text" class="form-control" value="{{ $t->name }}" disabled>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>المبلغ المدفوع <span class="text-danger">*</span></label>
                                <input type="number" name="amount_paid" class="form-control" step="0.01" min="0.01" value="{{ $t->amount_paid }}" required>
                                <div class="field-error">يرجى إدخال المبلغ المدفوع بشكل صحيح (0.01 على الأقل).</div>
                            </div>
                            <div class="col-md-12 form-group mb-0">
                                <label>ملاحظات</label>
                                <textarea name="notes" class="form-control" rows="3" maxlength="255">{{ $t->notes }}</textarea>
                                <div class="field-error">يجب ألا تتجاوز الملاحظات 255 حرفًا.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success"><i class="fas fa-save ml-1"></i> تحديث</button>
                        <button type="button" class="btn btn-light" data-dismiss="modal">إغلاق</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endsection

@section('js')
<!-- Internal Data tables -->
<script src="{{ URL::asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('assets/plugins/datatable/js/dataTables.bootstrap4.js') }}"></script>
<script src="{{ URL::asset('assets/plugins/datatable/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ URL::asset('assets/plugins/datatable/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ URL::asset('assets/js/JsBarcode.all.min.js') }}"></script>

<script>
$(function () {

    /* =====================================================
     * DataTables (Arabic) — one init per table
     * ===================================================== */
    const dtLang = {
        search: '',
        searchPlaceholder: 'بحث...',
        lengthMenu: 'عرض _MENU_',
        info: 'عرض _START_ إلى _END_ من _TOTAL_',
        infoEmpty: 'لا توجد بيانات',
        infoFiltered: '(تمت التصفية من _MAX_)',
        zeroRecords: 'لا توجد نتائج مطابقة',
        emptyTable: 'لا توجد بيانات',
        paginate: { first: 'الأول', last: 'الأخير', next: 'التالي', previous: 'السابق' }
    };

    function initTable(selector, order) {
        if (!$(selector).length) return;
        $(selector).DataTable({
            responsive: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            order: order,
            columnDefs: [{ targets: 'no-sort', orderable: false, searchable: false }],
            language: dtLang
        });
    }
    initTable('#invoicesTable',        [[1, 'desc']]);
    initTable('#creditInvoicesTable',  [[1, 'desc']]);
    initTable('#paymentsTable',        [[2, 'desc']]);

    /* =====================================================
     * Tabs: remember the active tab + fix tables in hidden tabs
     * ===================================================== */
    const tabKey = 'supplierTab_{{ $suppliers->id }}';
    let initialTab = location.hash;
    try {
        if (!initialTab) initialTab = sessionStorage.getItem(tabKey) || '';
    } catch (e) {}
    if (initialTab && $('#supplierTabs a[href="' + initialTab + '"]').length) {
        $('#supplierTabs a[href="' + initialTab + '"]').tab('show');
    }

    $('#supplierTabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        const target = $(e.target).attr('href');
        try { sessionStorage.setItem(tabKey, target); } catch (err) {}
        if (history.replaceState) history.replaceState(null, '', target);
        try {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().responsive.recalc();
        } catch (err) {}
    });

    /* =====================================================
     * Popup invoice print
     * ===================================================== */
    $(document).on('click', '.js-popup-print', function (e) {
        const w = Math.max(700, Math.round(window.innerWidth * 0.6));
        const h = Math.max(600, Math.round(window.innerHeight * 0.8));
        const left = Math.round((window.innerWidth - w) / 2);
        const top  = Math.round((window.innerHeight - h) / 2);
        const popup = window.open(this.href, '_blank',
            'width=' + w + ',height=' + h + ',left=' + left + ',top=' + top + ',resizable=yes,scrollbars=yes');
        if (popup) e.preventDefault();      // popup opened; otherwise fall back to a normal new tab
    });

    /* =====================================================
     * Barcode printing (built on demand from data, not from hidden HTML)
     * ===================================================== */
    const barcodeData = @json($barcodeMap);

    function printBarcodes(invoiceId) {
        const items = (barcodeData['i' + invoiceId] || []).filter(it => it.id && it.qty > 0);
        const total = items.reduce((s, it) => s + it.qty, 0);
        if (!total) { alert('لا توجد منتجات لطباعة باركود لها.'); return; }
        if (total > 300 && !confirm('سيتم طباعة ' + total + ' بطاقة باركود. هل تريد المتابعة؟')) return;

        const grid = document.createElement('div');
        grid.className = 'label-grid';

        items.forEach(function (it) {
            // Render the barcode once, then clone it for every label
            const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            JsBarcode(svg, String(it.id), { format: 'CODE128', displayValue: true, fontSize: 20, width: 2, height: 50 });

            for (let i = 0; i < it.qty; i++) {
                const label = document.createElement('div');
                label.className = 'barcode-label';
                const name = document.createElement('p');
                name.innerHTML = '<strong></strong>';
                name.firstChild.textContent = it.name;           // textContent → no HTML injection
                const price = document.createElement('p');
                price.textContent = 'السعر: ' + it.price;
                label.append(name, price, svg.cloneNode(true));
                grid.appendChild(label);
            }
        });

        const html = `<!DOCTYPE html>
<html dir="rtl" lang="ar"><head><meta charset="utf-8"><title>طباعة الباركود</title>
<style>
    @@page { size: A4 portrait; margin: 10mm; }
    body { margin: 0; font-family: Tahoma, Arial, sans-serif; text-align: center; }
    .label-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 4mm; }
    .barcode-label { border: 1px solid #000; border-radius: 8px; padding: 3mm; break-inside: avoid; page-break-inside: avoid; }
    .barcode-label p { margin: 0 0 2px; font-size: 13px; }
    .barcode-label svg { max-width: 100%; height: auto; }
</style></head><body>${grid.outerHTML}</body></html>`;

        const w = window.open('', '_blank', 'height=700,width=900');
        if (!w) { alert('المتصفح منع نافذة الطباعة. من فضلك اسمح بالنوافذ المنبثقة لهذا الموقع.'); return; }
        w.document.open(); w.document.write(html); w.document.close();
        setTimeout(function () { w.focus(); w.print(); }, 400);
    }
    $(document).on('click', '.js-print-barcodes', function () { printBarcodes($(this).data('invoice')); });

    /* =====================================================
     * Payment forms (add + edit): inline validation
     * ===================================================== */
    function flag($el, bad) { $el.toggleClass('is-invalid', bad); return !bad; }

    $(document).on('click', '.js-fill-remaining', function () {
        $(this).closest('.input-group').find('[name="amount_paid"]').val(parseFloat($(this).data('remaining')).toFixed(2)).trigger('input');
    });

    $(document).on('input change', '.payment-form .form-control', function () { $(this).removeClass('is-invalid'); });

    $(document).on('submit', '.payment-form', function (e) {
        const $f = $(this);
        const $date = $f.find('[name="payment_date"]');
        const $amt  = $f.find('[name="amount_paid"]');
        const $note = $f.find('[name="notes"]');
        const amount = parseFloat($amt.val());

        let ok = true;
        ok = flag($date, !$date.val()) && ok;
        ok = flag($amt, isNaN(amount) || amount < 0.01) && ok;
        ok = flag($note, $note.val().length > 255) && ok;

        if (!ok) { e.preventDefault(); $f.find('.is-invalid').first().trigger('focus'); return; }

        // Soft warning when paying more than what is left on the invoice (add form only)
        const remaining = parseFloat($f.data('remaining'));
        if (!isNaN(remaining) && remaining > 0 && amount > remaining + 0.001) {
            if (!confirm('المبلغ المدخل أكبر من المبلغ المتبقي على الفاتورة (' + remaining.toFixed(2) + ' ج.م). هل تريد المتابعة؟')) {
                e.preventDefault(); return;
            }
        }
        $f.find('button[type="submit"]').addClass('is-loading')
          .html('<span class="spinner-border spinner-border-sm ml-1"></span> جاري الحفظ...');
    });

    $('.supplier-modal').on('hidden.bs.modal', function () { $(this).find('.is-invalid').removeClass('is-invalid'); });

});
</script>
@endsection
