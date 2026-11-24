@extends('layouts.master3')
<title>معرض أيه - تقارير الموردين</title>

@php
    $paidReport = $reportType === 'paid';
    $title = $paidReport ? 'مدفوعات الموردين' : 'أرصدة الموردين';
    $subtitle = $paidReport
        ? 'المبالغ المسددة لكل مورد، متضمنة الدفعة الأولى والسدادات اللاحقة.'
        : 'قارن الالتزامات القائمة على الموردين في بداية الفترة ونهايتها.';
    $reportRoutes = [
        'paid' => route('supplier_report_paid', ['startDate' => $startDate, 'endDate' => $endDate]),
        'balances' => route('supplier_report_balances', ['startDate' => $startDate, 'endDate' => $endDate]),
    ];
    $chartSuppliers = $suppliers->take(10);
@endphp

<style>
    .sr-page { --sr-ink:#18243b; --sr-muted:#77839a; --sr-line:#e7ebf2; --sr-cyan:#0b83a5; direction:rtl; color:var(--sr-ink); }
    .sr-hero { display:flex; justify-content:space-between; align-items:flex-start; gap:18px; padding:24px 26px; margin-bottom:20px; border:1px solid var(--sr-line); border-radius:18px; background:linear-gradient(120deg,#fff 45%,#effaff); box-shadow:0 8px 24px rgba(20,42,82,.05); }
    .sr-title { display:flex; gap:14px; align-items:flex-start; }
    .sr-icon { display:grid; place-items:center; width:50px; height:50px; flex:0 0 50px; border-radius:15px; color:white; background:linear-gradient(145deg,#0b83a5,#45bfd2); font-size:20px; }
    .sr-eyebrow { margin-bottom:4px; color:var(--sr-cyan); font-size:12px; font-weight:800; }
    .sr-title h1 { margin:0; font-size:24px; font-weight:800; color:var(--sr-ink); }
    .sr-title p { margin:6px 0 0; color:var(--sr-muted); font-size:14px; }
    .sr-period { display:inline-flex; gap:7px; align-items:center; margin-top:12px; padding:6px 11px; border-radius:999px; background:#e8f8fc; color:#146982; font-size:12px; font-weight:700; }
    .sr-actions { display:flex; flex-wrap:wrap; gap:8px; }
    .sr-actions .btn { min-height:40px; border-radius:10px; font-weight:700; }
    .sr-tabs { display:flex; gap:8px; overflow:auto; margin-bottom:18px; padding-bottom:5px; }
    .sr-tab { display:inline-flex; align-items:center; gap:8px; padding:10px 14px; white-space:nowrap; border:1px solid var(--sr-line); border-radius:11px; background:#fff; color:#59677f; font-size:13px; font-weight:700; text-decoration:none; }
    .sr-tab:hover { color:var(--sr-cyan); border-color:#b9e4ed; background:#f8fdff; text-decoration:none; }
    .sr-tab.active { color:white; border-color:var(--sr-cyan); background:var(--sr-cyan); box-shadow:0 5px 14px rgba(11,131,165,.2); }
    .sr-note { padding:11px 14px; margin-bottom:18px; border:1px solid #d8edf2; border-radius:11px; background:#f7fcfd; color:#536984; font-size:13px; }
    .sr-kpis { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:12px; margin-bottom:18px; }
    .sr-kpi { min-width:0; padding:15px 16px; border:1px solid var(--sr-line); border-radius:14px; background:#fff; box-shadow:0 4px 14px rgba(20,42,82,.035); }
    .sr-label { display:flex; gap:8px; align-items:center; color:var(--sr-muted); font-size:12px; font-weight:700; }
    .sr-label i { color:var(--sr-cyan); }
    .sr-value { display:block; overflow:hidden; margin-top:8px; color:var(--sr-ink); font-size:22px; font-weight:800; text-overflow:ellipsis; white-space:nowrap; }
    .sr-kpi small { color:var(--sr-muted); font-size:11px; }
    .sr-card,.sr-chart { overflow:hidden; border:1px solid var(--sr-line); border-radius:15px; background:#fff; box-shadow:0 6px 20px rgba(20,42,82,.04); }
    .sr-card-head { display:flex; justify-content:space-between; align-items:center; gap:14px; padding:16px 18px; border-bottom:1px solid var(--sr-line); }
    .sr-card-head h2,.sr-chart h2 { margin:0; color:var(--sr-ink); font-size:16px; font-weight:800; }
    .sr-card-head p { margin:4px 0 0; color:var(--sr-muted); font-size:12px; }
    .sr-search { width:min(100%,320px); }
    .sr-table-wrap { overflow:auto; }
    .sr-table { width:100%; min-width:760px; margin:0; border-collapse:separate; border-spacing:0; }
    .sr-table thead th { position:sticky; top:0; z-index:1; padding:12px 13px; border:0; background:#f2f9fb; color:#55717b; font-size:12px; font-weight:800; white-space:nowrap; }
    .sr-table tbody td { padding:12px 13px; border-top:1px solid #edf2f5; color:#35435a; font-size:13px; vertical-align:middle; }
    .sr-table tbody tr:hover { background:#f9fdff; }
    .sr-name { display:flex; flex-direction:column; gap:2px; min-width:145px; }
    .sr-name strong { color:var(--sr-ink); font-weight:800; }
    .sr-name small { color:#929db0; font-size:11px; }
    .sr-num { font-variant-numeric:tabular-nums; font-weight:700; white-space:nowrap; }
    .sr-paid { color:#13865c !important; }
    .sr-due { color:#b34d55 !important; }
    .sr-empty { padding:42px 16px !important; color:var(--sr-muted) !important; text-align:center; }
    .sr-chart { height:310px; padding:18px 20px; margin-top:18px; }
    .sr-chart h2 { margin-bottom:12px; }
    .sr-foot { margin-top:13px; color:var(--sr-muted); font-size:11px; }
    @media (max-width:1050px) { .sr-kpis { grid-template-columns:repeat(3,minmax(0,1fr)); } }
    @media (max-width:680px) { .sr-hero { flex-direction:column; padding:18px; } .sr-title h1 { font-size:20px; } .sr-kpis { grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px; } .sr-kpi { padding:12px; } .sr-value { font-size:19px; } .sr-card-head { align-items:flex-start; flex-direction:column; } .sr-search { width:100%; } .sr-chart { height:260px; } }
    @media print { .sr-actions,.sr-tabs,.sr-search { display:none !important; } .sr-hero,.sr-kpi,.sr-card,.sr-chart { box-shadow:none; break-inside:avoid; } .sr-table { min-width:0; } .sr-table thead th { position:static; } body { background:#fff !important; } }
</style>

@section('content')
<div class="container-fluid sr-page">
    <header class="sr-hero">
        <div class="sr-title">
            <span class="sr-icon"><i class="fas {{ $paidReport ? 'fa-wallet' : 'fa-file-invoice-dollar' }}"></i></span>
            <div>
                <div class="sr-eyebrow">إدارة الموردين / التقارير</div>
                <h1>{{ $title }}</h1>
                <p>{{ $subtitle }}</p>
                <span class="sr-period"><i class="far fa-calendar-alt"></i> {{ $startDate }} <span>—</span> {{ $endDate }}</span>
            </div>
        </div>
        <div class="sr-actions">
            <button type="button" class="btn btn-outline-primary" onclick="exportSupplierReport()"><i class="fas fa-file-excel ml-1"></i> تصدير Excel</button>
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fas fa-print ml-1"></i> طباعة</button>
            <a href="{{ route('home_reports') }}" class="btn btn-light"><i class="fas fa-arrow-right ml-1"></i> التقارير</a>
        </div>
    </header>

    <nav class="sr-tabs" aria-label="تقارير الموردين">
        <a class="sr-tab {{ $paidReport ? 'active' : '' }}" href="{{ $reportRoutes['paid'] }}"><i class="fas fa-wallet"></i> المدفوعات</a>
        <a class="sr-tab {{ ! $paidReport ? 'active' : '' }}" href="{{ $reportRoutes['balances'] }}"><i class="fas fa-file-invoice-dollar"></i> الأرصدة</a>
    </nav>
    <div class="sr-note"><i class="fas fa-info-circle ml-1"></i> تُحسب الدفعة الأولى بتاريخ فاتورة الشراء، والسداد اللاحق بتاريخ تسجيله. تُخصم مرتجعات المورد من الرصيد بتاريخ تسجيل المرتجع.</div>

    @if ($paidReport)
        <section class="sr-kpis">
            <div class="sr-kpi"><span class="sr-label"><i class="fas fa-truck"></i> الموردون</span><strong class="sr-value">{{ number_format($stats->supplier_count) }}</strong><small>لهم مدفوعات أو فواتير خلال الفترة</small></div>
            <div class="sr-kpi"><span class="sr-label"><i class="fas fa-file-invoice"></i> فواتير الشراء</span><strong class="sr-value">{{ number_format($stats->invoice_count) }}</strong><small>فاتورة</small></div>
            <div class="sr-kpi"><span class="sr-label"><i class="fas fa-cart-plus"></i> إجمالي المشتريات</span><strong class="sr-value">{{ number_format($stats->purchase_amount, 2) }}</strong><small>ج.م</small></div>
            <div class="sr-kpi"><span class="sr-label"><i class="fas fa-undo"></i> مرتجعات المورد</span><strong class="sr-value sr-due">{{ number_format($stats->return_amount, 2) }}</strong><small>ج.م</small></div>
            <div class="sr-kpi"><span class="sr-label"><i class="fas fa-money-bill-wave"></i> دفعات أولى</span><strong class="sr-value sr-paid">{{ number_format($stats->invoice_paid, 2) }}</strong><small>ج.م</small></div>
            <div class="sr-kpi"><span class="sr-label"><i class="fas fa-hand-holding-usd"></i> إجمالي المدفوع</span><strong class="sr-value sr-paid">{{ number_format($stats->paid_amount, 2) }}</strong><small>ج.م خلال الفترة</small></div>
        </section>
        <section class="sr-card">
            <div class="sr-card-head"><div><h2>المدفوعات حسب المورد</h2><p>تفصيل الدفعة الأولى والسداد اللاحق يمنع احتساب الدفعات مرتين.</p></div><input type="search" class="form-control sr-search" data-supplier-search placeholder="ابحث باسم المورد أو الهاتف"></div>
            <div class="sr-table-wrap"><table class="sr-table table" id="supplierTable">
                <thead><tr><th>المورد</th><th>الهاتف</th><th>عدد الفواتير</th><th>إجمالي المشتريات</th><th>مرتجعات المورد</th><th>الدفعات الأولى</th><th>السداد اللاحق</th><th>إجمالي المدفوع</th></tr></thead>
                <tbody>
                @forelse ($suppliers as $supplier)
                    <tr><td><div class="sr-name"><strong>{{ $supplier->supplier_name }}</strong><small>كود المورد: {{ $supplier->supplier_id }}</small></div></td><td>{{ $supplier->supplier_phone }}</td><td>{{ $supplier->invoice_count }}</td><td class="sr-num">{{ number_format($supplier->purchase_amount, 2) }} ج.م</td><td class="sr-num sr-due">{{ number_format($supplier->return_amount, 2) }} ج.م</td><td class="sr-num">{{ number_format($supplier->invoice_paid, 2) }} ج.م</td><td class="sr-num">{{ number_format($supplier->later_paid, 2) }} ج.م</td><td class="sr-num sr-paid">{{ number_format($supplier->paid_amount, 2) }} ج.م</td></tr>
                @empty
                    <tr><td colspan="8" class="sr-empty">لا توجد مدفوعات أو مرتجعات للموردين خلال الفترة المحددة.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </section>
        @if ($chartSuppliers->isNotEmpty())<section class="sr-chart"><h2>أعلى الموردين حسب المبلغ المدفوع</h2><canvas id="supplierChart"></canvas></section>@endif
    @else
        <section class="sr-kpis">
            <div class="sr-kpi"><span class="sr-label"><i class="fas fa-truck"></i> الموردون</span><strong class="sr-value">{{ number_format($stats->supplier_count) }}</strong><small>لهم رصيد أو حركة</small></div>
            <div class="sr-kpi"><span class="sr-label"><i class="fas fa-hourglass-start"></i> رصيد أول الفترة</span><strong class="sr-value">{{ number_format($stats->opening_payable, 2) }}</strong><small>ج.م</small></div>
            <div class="sr-kpi"><span class="sr-label"><i class="fas fa-cart-plus"></i> مشتريات الفترة</span><strong class="sr-value">{{ number_format($stats->purchase_amount, 2) }}</strong><small>ج.م</small></div>
            <div class="sr-kpi"><span class="sr-label"><i class="fas fa-undo"></i> مرتجعات المورد</span><strong class="sr-value sr-due">{{ number_format($stats->return_amount, 2) }}</strong><small>ج.م</small></div>
            <div class="sr-kpi"><span class="sr-label"><i class="fas fa-money-bill-wave"></i> مدفوعات الفترة</span><strong class="sr-value sr-paid">{{ number_format($stats->paid_amount, 2) }}</strong><small>ج.م</small></div>
            <div class="sr-kpi"><span class="sr-label"><i class="fas fa-file-invoice-dollar"></i> رصيد آخر الفترة</span><strong class="sr-value sr-due">{{ number_format($stats->closing_payable, 2) }}</strong><small>ج.م</small></div>
        </section>
        <section class="sr-card">
            <div class="sr-card-head"><div><h2>الأرصدة المستحقة لكل مورد</h2><p>رصيد النهاية يتضمن خصم مرتجعات المورد والمدفوعات المسجلة خلال الفترة.</p></div><input type="search" class="form-control sr-search" data-supplier-search placeholder="ابحث باسم المورد أو الهاتف"></div>
            <div class="sr-table-wrap"><table class="sr-table table" id="supplierTable">
                <thead><tr><th>المورد</th><th>الهاتف</th><th>فواتير الفترة</th><th>رصيد أول الفترة</th><th>المشتريات</th><th>مرتجعات المورد</th><th>المدفوعات</th><th>رصيد آخر الفترة</th></tr></thead>
                <tbody>
                @forelse ($suppliers as $supplier)
                    <tr><td><div class="sr-name"><strong>{{ $supplier->supplier_name }}</strong><small>كود المورد: {{ $supplier->supplier_id }}</small></div></td><td>{{ $supplier->supplier_phone }}</td><td>{{ $supplier->invoice_count }}</td><td class="sr-num">{{ number_format($supplier->opening_payable, 2) }} ج.م</td><td class="sr-num">{{ number_format($supplier->purchase_amount, 2) }} ج.م</td><td class="sr-num sr-due">{{ number_format($supplier->return_amount, 2) }} ج.م</td><td class="sr-num sr-paid">{{ number_format($supplier->paid_amount, 2) }} ج.م</td><td class="sr-num sr-due">{{ number_format($supplier->closing_payable, 2) }} ج.م</td></tr>
                @empty
                    <tr><td colspan="8" class="sr-empty">لا توجد أرصدة أو حركات للموردين خلال الفترة.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </section>
        @if ($chartSuppliers->isNotEmpty())<section class="sr-chart"><h2>الأرصدة المستحقة أول وآخر الفترة</h2><canvas id="supplierChart"></canvas></section>@endif
    @endif
    <p class="sr-foot">المبالغ بالجنيه المصري. يتم استبعاد الفواتير والسدادات التي تم حذفها.</p>
</div>
@endsection

@section('js')
<script src="{{ URL::asset('assets/js/chart.js') }}"></script>
<script src="{{ URL::asset('assets/js/xlsx.full.min.js') }}"></script>
<script>
    document.querySelector('[data-supplier-search]')?.addEventListener('input', function () {
        const query = this.value.trim().toLocaleLowerCase();
        document.querySelectorAll('#supplierTable tbody tr').forEach(row => { row.hidden = !row.textContent.toLocaleLowerCase().includes(query); });
    });
    window.exportSupplierReport = function () {
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, XLSX.utils.table_to_sheet(document.getElementById('supplierTable')), 'تقرير الموردين');
        XLSX.writeFile(workbook, 'تقرير_الموردين_{{ $reportType }}_{{ $startDate }}_{{ $endDate }}.xlsx');
    };
    @if ($chartSuppliers->isNotEmpty())
        new Chart(document.getElementById('supplierChart').getContext('2d'), {
            type: 'bar',
            data: { labels: @json($chartSuppliers->pluck('supplier_name')), datasets: [
                @if ($paidReport)
                    { label: 'المدفوع (ج.م)', data: @json($chartSuppliers->pluck('paid_amount')), backgroundColor: 'rgba(11,131,165,.72)', borderRadius: 5 }
                @else
                    { label: 'رصيد أول الفترة', data: @json($chartSuppliers->pluck('opening_payable')), backgroundColor: 'rgba(104,128,162,.55)', borderRadius: 5 },
                    { label: 'رصيد آخر الفترة', data: @json($chartSuppliers->pluck('closing_payable')), backgroundColor: 'rgba(11,131,165,.72)', borderRadius: 5 }
                @endif
            ] },
            options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', scales: { x: { beginAtZero: true } } }
        });
    @endif
</script>
@endsection
