@extends('layouts.master3')
<title>معرض أيه - تقارير المشتريات</title>

@php
    $reportMeta = [
        'total' => ['title' => 'ملخص المشتريات', 'subtitle' => 'قيمة فواتير الشراء ومرتجعات المورد والمدفوعات اليومية.', 'icon' => 'fa-chart-pie'],
        'suppliers' => ['title' => 'المشتريات حسب المورد', 'subtitle' => 'قارن مشتريات كل مورد ومرتجعاته ومدفوعاته.', 'icon' => 'fa-truck'],
        'products' => ['title' => 'المشتريات حسب المنتج', 'subtitle' => 'راجع كميات الشراء والمرتجعة وصافي تكلفة كل صنف.', 'icon' => 'fa-box'],
        'payments' => ['title' => 'مدفوعات وأرصدة الموردين', 'subtitle' => 'تابع المشتريات والمرتجعات والمدفوعات ورصيد المورد.', 'icon' => 'fa-file-invoice-dollar'],
    ][$reportType];
    $reportRoutes = [
        'total' => route('purchase_report_total', ['startDate' => $startDate, 'endDate' => $endDate]),
        'suppliers' => route('purchase_report_suppliers', ['startDate' => $startDate, 'endDate' => $endDate]),
        'products' => route('purchase_report_products', ['startDate' => $startDate, 'endDate' => $endDate]),
        'payments' => route('purchase_report_payments', ['startDate' => $startDate, 'endDate' => $endDate]),
    ];
    $chartRows = match ($reportType) {
        'suppliers' => $suppliers->take(10),
        'products' => $products->take(10),
        default => collect(),
    };
@endphp

<style>
    .pr-page { --pr-ink:#18243b; --pr-muted:#77839a; --pr-line:#e7ebf2; --pr-purple:#8058ba; direction:rtl; color:var(--pr-ink); }
    .pr-hero { display:flex; align-items:flex-start; justify-content:space-between; gap:18px; padding:24px 26px; margin:0 0 20px; border:1px solid var(--pr-line); border-radius:18px; background:linear-gradient(120deg,#fff 45%,#f7f2ff); box-shadow:0 8px 24px rgba(20,42,82,.05); }
    .pr-title { display:flex; align-items:flex-start; gap:14px; }
    .pr-title-icon { display:grid; place-items:center; width:50px; height:50px; flex:0 0 50px; border-radius:15px; color:#fff; background:linear-gradient(145deg,#8058ba,#b28be3); font-size:20px; }
    .pr-eyebrow { color:var(--pr-purple); font-size:12px; font-weight:800; margin-bottom:4px; }
    .pr-title h1 { margin:0; color:var(--pr-ink); font-size:24px; font-weight:800; }
    .pr-title p { margin:6px 0 0; color:var(--pr-muted); font-size:14px; }
    .pr-period { display:inline-flex; align-items:center; gap:7px; margin-top:12px; padding:6px 11px; border-radius:999px; background:#f2edfb; color:#68449e; font-size:12px; font-weight:700; }
    .pr-actions { display:flex; flex-wrap:wrap; gap:8px; }
    .pr-actions .btn { min-height:40px; border-radius:10px; font-weight:700; }
    .pr-tabs { display:flex; gap:8px; overflow:auto; padding:0 2px 5px; margin-bottom:18px; }
    .pr-tab { display:inline-flex; align-items:center; gap:8px; white-space:nowrap; padding:10px 14px; border:1px solid var(--pr-line); border-radius:11px; background:#fff; color:#59677f; font-size:13px; font-weight:700; text-decoration:none; transition:.15s ease; }
    .pr-tab:hover { color:var(--pr-purple); border-color:#d9c8f0; background:#fcfaff; text-decoration:none; }
    .pr-tab.active { color:#fff; border-color:var(--pr-purple); background:var(--pr-purple); box-shadow:0 5px 14px rgba(128,88,186,.2); }
    .pr-note { padding:11px 14px; margin-bottom:18px; border:1px solid #e8def5; border-radius:11px; background:#fcfaff; color:#665a79; font-size:13px; }
    .pr-kpis { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:12px; margin-bottom:18px; }
    .pr-kpi { min-width:0; padding:15px 16px; border:1px solid var(--pr-line); border-radius:14px; background:#fff; box-shadow:0 4px 14px rgba(20,42,82,.035); }
    .pr-kpi-label { display:flex; align-items:center; gap:8px; color:var(--pr-muted); font-size:12px; font-weight:700; }
    .pr-kpi-label i { color:var(--pr-purple); }
    .pr-kpi-value { display:block; overflow:hidden; margin-top:8px; color:var(--pr-ink); font-size:22px; font-weight:800; text-overflow:ellipsis; white-space:nowrap; }
    .pr-kpi small { color:var(--pr-muted); font-size:11px; }
    .pr-card,.pr-chart { overflow:hidden; border:1px solid var(--pr-line); border-radius:15px; background:#fff; box-shadow:0 6px 20px rgba(20,42,82,.04); }
    .pr-card-head { display:flex; align-items:center; justify-content:space-between; gap:14px; padding:16px 18px; border-bottom:1px solid var(--pr-line); }
    .pr-card-head h2,.pr-chart h2 { margin:0; color:var(--pr-ink); font-size:16px; font-weight:800; }
    .pr-card-head p { margin:4px 0 0; color:var(--pr-muted); font-size:12px; }
    .pr-search { width:min(100%,320px); }
    .pr-table-wrap { overflow:auto; }
    .pr-table { width:100%; min-width:750px; margin:0; border-collapse:separate; border-spacing:0; }
    .pr-table thead th { position:sticky; top:0; z-index:1; padding:12px 13px; border:0; background:#f8f6fb; color:#6b6380; font-size:12px; font-weight:800; white-space:nowrap; }
    .pr-table tbody td { padding:12px 13px; border-top:1px solid #f0edf4; color:#35435a; font-size:13px; vertical-align:middle; }
    .pr-table tbody tr:hover { background:#fcfaff; }
    .pr-name { display:flex; flex-direction:column; gap:2px; min-width:145px; }
    .pr-name strong { color:var(--pr-ink); font-weight:800; }
    .pr-name small { color:#929db0; font-size:11px; }
    .pr-number { font-variant-numeric:tabular-nums; font-weight:700; white-space:nowrap; }
    .pr-positive { color:#13865c !important; }
    .pr-paid { color:#1769a7 !important; }
    .pr-due { color:#b34d55 !important; }
    .pr-empty { padding:42px 16px !important; color:var(--pr-muted) !important; text-align:center; }
    .pr-chart { height:310px; padding:18px 20px; margin-top:18px; }
    .pr-chart h2 { margin-bottom:12px; }
    .pr-footer-note { margin-top:13px; color:var(--pr-muted); font-size:11px; }
    @media (max-width:1050px) { .pr-kpis { grid-template-columns:repeat(3,minmax(0,1fr)); } }
    @media (max-width:680px) { .pr-hero { flex-direction:column; padding:18px; } .pr-title h1 { font-size:20px; } .pr-kpis { grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px; } .pr-kpi { padding:12px; } .pr-kpi-value { font-size:19px; } .pr-card-head { align-items:flex-start; flex-direction:column; } .pr-search { width:100%; } .pr-chart { height:260px; } }
    @media print { .pr-actions,.pr-tabs,.pr-search { display:none !important; } .pr-hero,.pr-kpi,.pr-card,.pr-chart { box-shadow:none; break-inside:avoid; } .pr-table { min-width:0; } .pr-table thead th { position:static; } body { background:#fff !important; } }
</style>

@section('content')
<div class="container-fluid pr-page">
    <header class="pr-hero">
        <div class="pr-title">
            <span class="pr-title-icon"><i class="fas {{ $reportMeta['icon'] }}"></i></span>
            <div>
                <div class="pr-eyebrow">إدارة المشتريات / التقارير</div>
                <h1>{{ $reportMeta['title'] }}</h1>
                <p>{{ $reportMeta['subtitle'] }}</p>
                <span class="pr-period"><i class="far fa-calendar-alt"></i> {{ $startDate }} <span>—</span> {{ $endDate }}</span>
            </div>
        </div>
        <div class="pr-actions">
            <button type="button" class="btn btn-outline-primary" onclick="exportPurchaseReport()"><i class="fas fa-file-excel ml-1"></i> تصدير Excel</button>
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fas fa-print ml-1"></i> طباعة</button>
            <a href="{{ route('home_reports') }}" class="btn btn-light"><i class="fas fa-arrow-right ml-1"></i> التقارير</a>
        </div>
    </header>

    <nav class="pr-tabs" aria-label="تقارير المشتريات">
        <a class="pr-tab {{ $reportType === 'total' ? 'active' : '' }}" href="{{ $reportRoutes['total'] }}"><i class="fas fa-chart-pie"></i> ملخص المشتريات</a>
        <a class="pr-tab {{ $reportType === 'suppliers' ? 'active' : '' }}" href="{{ $reportRoutes['suppliers'] }}"><i class="fas fa-truck"></i> حسب المورد</a>
        <a class="pr-tab {{ $reportType === 'products' ? 'active' : '' }}" href="{{ $reportRoutes['products'] }}"><i class="fas fa-box"></i> حسب المنتج</a>
        <a class="pr-tab {{ $reportType === 'payments' ? 'active' : '' }}" href="{{ $reportRoutes['payments'] }}"><i class="fas fa-file-invoice-dollar"></i> أرصدة الموردين</a>
    </nav>

    <div class="pr-note"><i class="fas fa-info-circle ml-1"></i> تُحسب المشتريات بتاريخ الفاتورة والمرتجعات بتاريخ تسجيلها. صافي المشتريات = قيمة الفواتير − قيمة مرتجعات المورد. تشمل المدفوعات الدفعات الأولى والسداد اللاحق.</div>

    @if ($reportType === 'total')
        <section class="pr-kpis">
            <div class="pr-kpi"><span class="pr-kpi-label"><i class="fas fa-file-invoice"></i> فواتير الشراء</span><strong class="pr-kpi-value">{{ number_format($stats->invoice_count) }}</strong><small>خلال الفترة</small></div>
            <div class="pr-kpi"><span class="pr-kpi-label"><i class="fas fa-cart-plus"></i> إجمالي المشتريات</span><strong class="pr-kpi-value">{{ number_format($stats->purchase_amount, 2) }}</strong><small>ج.م</small></div>
            <div class="pr-kpi"><span class="pr-kpi-label"><i class="fas fa-undo"></i> مرتجعات المورد</span><strong class="pr-kpi-value pr-due">{{ number_format($stats->return_amount, 2) }}</strong><small>{{ number_format($stats->return_count) }} مرتجع · ج.م</small></div>
            <div class="pr-kpi"><span class="pr-kpi-label"><i class="fas fa-scale-balanced"></i> صافي المشتريات</span><strong class="pr-kpi-value">{{ number_format($stats->net_purchase_amount, 2) }}</strong><small>ج.م بعد المرتجعات</small></div>
            <div class="pr-kpi"><span class="pr-kpi-label"><i class="fas fa-money-bill-wave"></i> إجمالي المدفوع</span><strong class="pr-kpi-value pr-paid">{{ number_format($stats->paid_amount, 2) }}</strong><small>ج.م خلال الفترة</small></div>
            <div class="pr-kpi"><span class="pr-kpi-label"><i class="fas fa-hourglass-start"></i> مستحقات أول الفترة</span><strong class="pr-kpi-value">{{ number_format($stats->opening_payable, 2) }}</strong><small>ج.م</small></div>
            <div class="pr-kpi"><span class="pr-kpi-label"><i class="fas fa-file-invoice-dollar"></i> مستحقات نهاية الفترة</span><strong class="pr-kpi-value pr-due">{{ number_format($stats->closing_payable, 2) }}</strong><small>ج.م</small></div>
        </section>
        <section class="pr-card">
            <div class="pr-card-head"><div><h2>المشتريات والمدفوعات حسب اليوم</h2><p>المدفوعات تجمع الدفعات الأولى والسدادات اللاحقة المسجلة في التاريخ.</p></div><input type="search" class="form-control pr-search" data-table-search placeholder="ابحث بالتاريخ"></div>
            <div class="pr-table-wrap"><table class="pr-table table" id="purchaseTable">
                <thead><tr><th>التاريخ</th><th>عدد الفواتير</th><th>قيمة المشتريات</th><th>قيمة المرتجعات</th><th>صافي المشتريات</th><th>دفعات أولى</th><th>سداد لاحق</th><th>إجمالي المدفوع</th></tr></thead>
                <tbody>
                @forelse ($dailyRows as $row)
                    <tr><td class="pr-number">{{ $row->report_date }}</td><td>{{ $row->invoice_count }}</td><td class="pr-number">{{ number_format($row->purchase_amount, 2) }} ج.م</td><td class="pr-number pr-due">{{ number_format($row->return_amount, 2) }} ج.م</td><td class="pr-number">{{ number_format($row->net_purchase_amount, 2) }} ج.م</td><td class="pr-number">{{ number_format($row->invoice_paid, 2) }} ج.م</td><td class="pr-number">{{ number_format($row->later_payments, 2) }} ج.م</td><td class="pr-number pr-paid">{{ number_format($row->paid_amount, 2) }} ج.م</td></tr>
                @empty
                    <tr><td colspan="8" class="pr-empty">لا توجد فواتير أو مرتجعات أو مدفوعات مسجلة خلال الفترة.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </section>
        <section class="pr-chart"><h2>المشتريات والمدفوعات اليومية</h2><canvas id="purchaseChart"></canvas></section>

    @elseif ($reportType === 'suppliers')
        <section class="pr-kpis">
            <div class="pr-kpi"><span class="pr-kpi-label"><i class="fas fa-truck"></i> الموردون</span><strong class="pr-kpi-value">{{ number_format($stats->supplier_count) }}</strong><small>لهم حركة بالفترة</small></div>
            <div class="pr-kpi"><span class="pr-kpi-label"><i class="fas fa-file-invoice"></i> الفواتير</span><strong class="pr-kpi-value">{{ number_format($stats->invoice_count) }}</strong><small>فاتورة شراء</small></div>
            <div class="pr-kpi"><span class="pr-kpi-label"><i class="fas fa-cart-plus"></i> إجمالي المشتريات</span><strong class="pr-kpi-value">{{ number_format($stats->purchase_amount, 2) }}</strong><small>ج.م</small></div>
            <div class="pr-kpi"><span class="pr-kpi-label"><i class="fas fa-undo"></i> قيمة المرتجعات</span><strong class="pr-kpi-value pr-due">{{ number_format($stats->return_amount, 2) }}</strong><small>ج.م</small></div>
            <div class="pr-kpi"><span class="pr-kpi-label"><i class="fas fa-scale-balanced"></i> صافي المشتريات</span><strong class="pr-kpi-value">{{ number_format($stats->net_purchase_amount, 2) }}</strong><small>ج.م</small></div>
            <div class="pr-kpi"><span class="pr-kpi-label"><i class="fas fa-money-bill-wave"></i> إجمالي المدفوع</span><strong class="pr-kpi-value pr-paid">{{ number_format($stats->paid_amount, 2) }}</strong><small>ج.م</small></div>
            <div class="pr-kpi"><span class="pr-kpi-label"><i class="fas fa-chart-line"></i> متوسط الفاتورة</span><strong class="pr-kpi-value">{{ number_format($stats->invoice_count ? $stats->purchase_amount / $stats->invoice_count : 0, 2) }}</strong><small>ج.م</small></div>
        </section>
        <section class="pr-card">
            <div class="pr-card-head"><div><h2>مشتريات الموردين</h2><p>يشمل إجمالي المدفوع دفعة الفاتورة الأولى والسداد اللاحق خلال الفترة.</p></div><input type="search" class="form-control pr-search" data-table-search placeholder="ابحث باسم المورد أو الهاتف"></div>
            <div class="pr-table-wrap"><table class="pr-table table" id="purchaseTable">
                <thead><tr><th>المورد</th><th>رقم الهاتف</th><th>عدد الفواتير</th><th>إجمالي المشتريات</th><th>مرتجعات المورد</th><th>صافي المشتريات</th><th>دفعات أولى</th><th>سداد لاحق</th><th>إجمالي المدفوع</th></tr></thead>
                <tbody>
                @forelse ($suppliers as $supplier)
                    <tr><td><div class="pr-name"><strong>{{ $supplier->supplier_name }}</strong><small>كود المورد: {{ $supplier->supplier_id }}</small></div></td><td>{{ $supplier->supplier_phone }}</td><td>{{ $supplier->invoice_count }}</td><td class="pr-number">{{ number_format($supplier->purchase_amount, 2) }} ج.م</td><td class="pr-number pr-due">{{ number_format($supplier->return_amount, 2) }} ج.م</td><td class="pr-number">{{ number_format($supplier->net_purchase_amount, 2) }} ج.م</td><td class="pr-number">{{ number_format($supplier->invoice_paid, 2) }} ج.م</td><td class="pr-number">{{ number_format($supplier->later_payments, 2) }} ج.م</td><td class="pr-number pr-paid">{{ number_format($supplier->paid_amount, 2) }} ج.م</td></tr>
                @empty
                    <tr><td colspan="9" class="pr-empty">لا توجد مشتريات أو مرتجعات أو مدفوعات للموردين خلال الفترة.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </section>
        @if ($chartRows->isNotEmpty())<section class="pr-chart"><h2>أعلى الموردين حسب قيمة المشتريات</h2><canvas id="purchaseChart"></canvas></section>@endif

    @elseif ($reportType === 'products')
        <section class="pr-kpis">
            <div class="pr-kpi"><span class="pr-kpi-label"><i class="fas fa-box"></i> المنتجات المشترَاة</span><strong class="pr-kpi-value">{{ number_format($stats->product_count) }}</strong><small>صنف</small></div>
            <div class="pr-kpi"><span class="pr-kpi-label"><i class="fas fa-cubes"></i> إجمالي الكمية</span><strong class="pr-kpi-value">{{ number_format($stats->quantity_purchased) }}</strong><small>وحدة</small></div>
            <div class="pr-kpi"><span class="pr-kpi-label"><i class="fas fa-undo"></i> كمية مرتجعة</span><strong class="pr-kpi-value pr-due">{{ number_format($stats->quantity_returned) }}</strong><small>وحدة</small></div>
            <div class="pr-kpi"><span class="pr-kpi-label"><i class="fas fa-boxes"></i> صافي الكمية</span><strong class="pr-kpi-value">{{ number_format($stats->net_quantity) }}</strong><small>وحدة</small></div>
            <div class="pr-kpi"><span class="pr-kpi-label"><i class="fas fa-coins"></i> قيمة المشتريات</span><strong class="pr-kpi-value">{{ number_format($stats->purchase_amount, 2) }}</strong><small>ج.م</small></div>
            <div class="pr-kpi"><span class="pr-kpi-label"><i class="fas fa-receipt"></i> قيمة المرتجعات</span><strong class="pr-kpi-value pr-due">{{ number_format($stats->return_amount, 2) }}</strong><small>ج.م</small></div>
            <div class="pr-kpi"><span class="pr-kpi-label"><i class="fas fa-scale-balanced"></i> صافي المشتريات</span><strong class="pr-kpi-value">{{ number_format($stats->net_purchase_amount, 2) }}</strong><small>ج.م</small></div>
            <div class="pr-kpi"><span class="pr-kpi-label"><i class="fas fa-file-invoice"></i> الفواتير</span><strong class="pr-kpi-value">{{ number_format($stats->invoice_count) }}</strong><small>خلال الفترة</small></div>
            <div class="pr-kpi"><span class="pr-kpi-label"><i class="fas fa-calculator"></i> متوسط صافي تكلفة الوحدة</span><strong class="pr-kpi-value">{{ number_format($stats->net_quantity > 0 ? $stats->net_purchase_amount / $stats->net_quantity : 0, 2) }}</strong><small>ج.م بعد المرتجعات</small></div>
        </section>
        <section class="pr-card">
            <div class="pr-card-head"><div><h2>تفاصيل المشتريات حسب المنتج</h2><p>متوسط التكلفة مرجّح بالكمية، لضمان قراءة أدق عند اختلاف أسعار الفواتير.</p></div><input type="search" class="form-control pr-search" data-table-search placeholder="ابحث باسم المنتج أو كوده"></div>
            <div class="pr-table-wrap"><table class="pr-table table" id="purchaseTable">
                <thead><tr><th>المنتج</th><th>عدد الفواتير</th><th>الكمية المشتراة</th><th>كمية مرتجعة</th><th>صافي الكمية</th><th>متوسط تكلفة الوحدة</th><th>قيمة الشراء</th><th>قيمة المرتجع</th><th>صافي قيمة الشراء</th></tr></thead>
                <tbody>
                @forelse ($products as $product)
                    <tr><td><div class="pr-name"><strong>{{ $product->product_name }}</strong><small>كود المنتج: {{ $product->product_id }}</small></div></td><td>{{ $product->invoice_count }}</td><td class="pr-number">{{ number_format($product->quantity_purchased) }}</td><td class="pr-number pr-due">{{ number_format($product->quantity_returned) }}</td><td class="pr-number">{{ number_format($product->net_quantity) }}</td><td class="pr-number">{{ number_format($product->average_unit_cost, 2) }} ج.م</td><td class="pr-number">{{ number_format($product->purchase_amount, 2) }} ج.م</td><td class="pr-number pr-due">{{ number_format($product->return_amount, 2) }} ج.م</td><td class="pr-number">{{ number_format($product->net_purchase_amount, 2) }} ج.م</td></tr>
                @empty
                    <tr><td colspan="9" class="pr-empty">لا توجد أصناف مشتراة أو مرتجعة خلال الفترة المحددة.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </section>
        @if ($chartRows->isNotEmpty())<section class="pr-chart"><h2>الأعلى قيمة في المشتريات</h2><canvas id="purchaseChart"></canvas></section>@endif

    @else
        <section class="pr-kpis">
            <div class="pr-kpi"><span class="pr-kpi-label"><i class="fas fa-hourglass-start"></i> مستحقات أول الفترة</span><strong class="pr-kpi-value">{{ number_format($stats->opening_payable, 2) }}</strong><small>ج.م</small></div>
            <div class="pr-kpi"><span class="pr-kpi-label"><i class="fas fa-cart-plus"></i> مشتريات الفترة</span><strong class="pr-kpi-value">{{ number_format($stats->purchase_amount, 2) }}</strong><small>ج.م</small></div>
            <div class="pr-kpi"><span class="pr-kpi-label"><i class="fas fa-undo"></i> مرتجعات الفترة</span><strong class="pr-kpi-value pr-due">{{ number_format($stats->return_amount, 2) }}</strong><small>ج.م</small></div>
            <div class="pr-kpi"><span class="pr-kpi-label"><i class="fas fa-money-bill-wave"></i> مدفوعات الفترة</span><strong class="pr-kpi-value pr-paid">{{ number_format($stats->paid_amount, 2) }}</strong><small>ج.م</small></div>
            <div class="pr-kpi"><span class="pr-kpi-label"><i class="fas fa-file-invoice-dollar"></i> مستحقات آخر الفترة</span><strong class="pr-kpi-value pr-due">{{ number_format($stats->closing_payable, 2) }}</strong><small>ج.م</small></div>
            <div class="pr-kpi"><span class="pr-kpi-label"><i class="fas fa-truck"></i> الموردون</span><strong class="pr-kpi-value">{{ number_format($stats->supplier_count) }}</strong><small>لهم رصيد أو حركة</small></div>
        </section>
        <section class="pr-card">
            <div class="pr-card-head"><div><h2>رصيد الموردين وحركة الفترة</h2><p>رصيد النهاية = رصيد البداية + المشتريات − مرتجعات المورد − المدفوعات المسجلة بالفترة.</p></div><input type="search" class="form-control pr-search" data-table-search placeholder="ابحث باسم المورد أو الهاتف"></div>
            <div class="pr-table-wrap"><table class="pr-table table" id="purchaseTable">
                <thead><tr><th>المورد</th><th>عدد الفواتير</th><th>رصيد أول الفترة</th><th>مشتريات الفترة</th><th>مرتجعات المورد</th><th>مدفوعات الفترة</th><th>رصيد آخر الفترة</th></tr></thead>
                <tbody>
                @forelse ($suppliers as $supplier)
                    <tr><td><div class="pr-name"><strong>{{ $supplier->supplier_name }}</strong><small>{{ $supplier->supplier_phone }} · كود {{ $supplier->supplier_id }}</small></div></td><td>{{ $supplier->invoice_count }}</td><td class="pr-number">{{ number_format($supplier->opening_payable, 2) }} ج.م</td><td class="pr-number">{{ number_format($supplier->purchase_amount, 2) }} ج.م</td><td class="pr-number pr-due">{{ number_format($supplier->return_amount, 2) }} ج.م</td><td class="pr-number pr-paid">{{ number_format($supplier->paid_amount, 2) }} ج.م</td><td class="pr-number pr-due">{{ number_format($supplier->closing_payable, 2) }} ج.م</td></tr>
                @empty
                    <tr><td colspan="7" class="pr-empty">لا توجد أرصدة أو حركات للموردين خلال الفترة.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </section>
        @if ($chartRows->isNotEmpty())<section class="pr-chart"><h2>أرصدة الموردين: أول الفترة وآخرها</h2><canvas id="purchaseChart"></canvas></section>@endif
    @endif

    <p class="pr-footer-note">تُحتسب قيمة المرتجعات من سجلات مرتجعات المشتريات النشطة بتاريخ تسجيلها، وتُخصم من صافي المشتريات ورصيد المورد.</p>
</div>
@endsection

@section('js')
<script src="{{ URL::asset('assets/js/chart.js') }}"></script>
<script src="{{ URL::asset('assets/js/xlsx.full.min.js') }}"></script>
<script>
    document.querySelector('[data-table-search]')?.addEventListener('input', function () {
        const query = this.value.trim().toLocaleLowerCase();
        document.querySelectorAll('#purchaseTable tbody tr').forEach(row => {
            row.hidden = !row.textContent.toLocaleLowerCase().includes(query);
        });
    });
    window.exportPurchaseReport = function () {
        const table = document.getElementById('purchaseTable');
        if (!table) return;
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, XLSX.utils.table_to_sheet(table), 'تقرير المشتريات');
        XLSX.writeFile(workbook, 'تقرير_المشتريات_{{ $reportType }}_{{ $startDate }}_{{ $endDate }}.xlsx');
    };

    @if ($reportType === 'total')
        new Chart(document.getElementById('purchaseChart').getContext('2d'), {
            type: 'bar',
            data: { labels: @json($dailyRows->pluck('report_date')), datasets: [
                { label: 'صافي المشتريات بعد المرتجعات', data: @json($dailyRows->pluck('net_purchase_amount')), backgroundColor: 'rgba(128, 88, 186, .7)', borderRadius: 4 },
                { label: 'إجمالي المدفوع', data: @json($dailyRows->pluck('paid_amount')), backgroundColor: 'rgba(23, 105, 167, .65)', borderRadius: 4 }
            ] },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
        });
    @elseif (in_array($reportType, ['suppliers', 'products'], true) && $chartRows->isNotEmpty())
        new Chart(document.getElementById('purchaseChart').getContext('2d'), {
            type: 'bar',
            data: { labels: @json($chartRows->pluck($reportType === 'suppliers' ? 'supplier_name' : 'product_name')), datasets: [{ label: 'صافي المشتريات بعد المرتجعات (ج.م)', data: @json($chartRows->pluck('net_purchase_amount')), backgroundColor: 'rgba(128, 88, 186, .7)', borderColor: '#8058ba', borderWidth: 1, borderRadius: 5 }] },
            options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', scales: { x: { beginAtZero: true } } }
        });
    @elseif ($reportType === 'payments' && $suppliers->isNotEmpty())
        new Chart(document.getElementById('purchaseChart').getContext('2d'), {
            type: 'bar',
            data: { labels: @json($suppliers->take(10)->pluck('supplier_name')), datasets: [
                { label: 'رصيد أول الفترة', data: @json($suppliers->take(10)->pluck('opening_payable')), backgroundColor: 'rgba(104, 128, 162, .55)', borderRadius: 4 },
                { label: 'مرتجعات المورد', data: @json($suppliers->take(10)->pluck('return_amount')), backgroundColor: 'rgba(215, 78, 85, .55)', borderRadius: 4 },
                { label: 'رصيد آخر الفترة', data: @json($suppliers->take(10)->pluck('closing_payable')), backgroundColor: 'rgba(128, 88, 186, .75)', borderRadius: 4 }
            ] },
            options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', scales: { x: { beginAtZero: true } } }
        });
    @endif
</script>
@endsection
