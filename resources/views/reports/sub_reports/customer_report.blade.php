@extends('layouts.master3')
<title>معرض أيه - تقارير العملاء</title>

@php
    $reportTitles = [
        'sales' => ['title' => 'مبيعات العملاء', 'subtitle' => 'ترتيب العملاء حسب صافي المبيعات بعد خصم المرتجعات.', 'icon' => 'fa-chart-line'],
        'balances' => ['title' => 'أرصدة العملاء', 'subtitle' => 'متابعة المستحقات القائمة وحركة الرصيد خلال الفترة.', 'icon' => 'fa-file-invoice-dollar'],
        'collections' => ['title' => 'تحصيلات العملاء', 'subtitle' => 'الدفعات الأولى والسدادات اللاحقة بتاريخ تحصيلها.', 'icon' => 'fa-hand-holding-usd'],
    ];
    $current = $reportTitles[$reportType];
    $reportRoutes = [
        'sales' => route('customer_report_sales', ['startDate' => $startDate, 'endDate' => $endDate]),
        'balances' => route('customer_report_balances', ['startDate' => $startDate, 'endDate' => $endDate]),
        'collections' => route('customer_report_collections', ['startDate' => $startDate, 'endDate' => $endDate]),
    ];
    $chartCustomers = $customers->take(10);
@endphp

<style>
    .cr-page { --cr-ink:#18243b; --cr-muted:#77839a; --cr-line:#e7ebf2; --cr-blue:#3867c8; direction:rtl; color:var(--cr-ink); }
    .cr-hero { display:flex; justify-content:space-between; align-items:flex-start; gap:18px; padding:24px 26px; margin-bottom:20px; border:1px solid var(--cr-line); border-radius:18px; background:linear-gradient(120deg,#fff 45%,#f1f5ff); box-shadow:0 8px 24px rgba(20,42,82,.05); }
    .cr-title { display:flex; gap:14px; align-items:flex-start; }
    .cr-icon { display:grid; place-items:center; width:50px; height:50px; flex:0 0 50px; border-radius:15px; color:#fff; background:linear-gradient(145deg,#3867c8,#7695e8); font-size:20px; }
    .cr-eyebrow { margin-bottom:4px; color:var(--cr-blue); font-size:12px; font-weight:800; }
    .cr-title h1 { margin:0; color:var(--cr-ink); font-size:24px; font-weight:800; }
    .cr-title p { margin:6px 0 0; color:var(--cr-muted); font-size:14px; }
    .cr-period { display:inline-flex; align-items:center; gap:7px; margin-top:12px; padding:6px 11px; border-radius:999px; background:#edf2ff; color:#365ca5; font-size:12px; font-weight:700; }
    .cr-actions { display:flex; flex-wrap:wrap; gap:8px; }
    .cr-actions .btn { min-height:40px; border-radius:10px; font-weight:700; }
    .cr-tabs { display:flex; gap:8px; overflow:auto; margin-bottom:18px; padding-bottom:5px; }
    .cr-tab { display:inline-flex; align-items:center; gap:8px; padding:10px 14px; white-space:nowrap; border:1px solid var(--cr-line); border-radius:11px; background:#fff; color:#59677f; font-size:13px; font-weight:700; text-decoration:none; }
    .cr-tab:hover { color:var(--cr-blue); border-color:#c9d6f5; background:#f8faff; text-decoration:none; }
    .cr-tab.active { color:#fff; border-color:var(--cr-blue); background:var(--cr-blue); box-shadow:0 5px 14px rgba(56,103,200,.2); }
    .cr-note { padding:11px 14px; margin-bottom:18px; border:1px solid #dbe4f7; border-radius:11px; background:#f8faff; color:#536984; font-size:13px; line-height:1.7; }
    .cr-kpis { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:12px; margin-bottom:18px; }
    .cr-kpi { min-width:0; padding:15px 16px; border:1px solid var(--cr-line); border-radius:14px; background:#fff; box-shadow:0 4px 14px rgba(20,42,82,.035); }
    .cr-label { display:flex; align-items:center; gap:8px; color:var(--cr-muted); font-size:12px; font-weight:700; }
    .cr-label i { color:var(--cr-blue); }
    .cr-value { display:block; overflow:hidden; margin-top:8px; color:var(--cr-ink); font-size:21px; font-weight:800; text-overflow:ellipsis; white-space:nowrap; }
    .cr-kpi small { color:var(--cr-muted); font-size:11px; }
    .cr-positive { color:#13865c !important; }
    .cr-due { color:#b34d55 !important; }
    .cr-card,.cr-chart { overflow:hidden; border:1px solid var(--cr-line); border-radius:15px; background:#fff; box-shadow:0 6px 20px rgba(20,42,82,.04); }
    .cr-card-head { display:flex; justify-content:space-between; align-items:center; gap:14px; padding:16px 18px; border-bottom:1px solid var(--cr-line); }
    .cr-card-head h2,.cr-chart h2 { margin:0; color:var(--cr-ink); font-size:16px; font-weight:800; }
    .cr-card-head p { margin:4px 0 0; color:var(--cr-muted); font-size:12px; }
    .cr-search { width:min(100%,320px); }
    .cr-table-wrap { overflow:auto; }
    .cr-table { width:100%; min-width:780px; margin:0; border-collapse:separate; border-spacing:0; }
    .cr-table thead th { position:sticky; top:0; z-index:1; padding:12px 13px; border:0; background:#f2f5fc; color:#586780; font-size:12px; font-weight:800; white-space:nowrap; }
    .cr-table tbody td { padding:12px 13px; border-top:1px solid #edf1f6; color:#35435a; font-size:13px; vertical-align:middle; }
    .cr-table tbody tr:hover { background:#fafbff; }
    .cr-name { display:flex; flex-direction:column; gap:2px; min-width:145px; }
    .cr-name strong { color:var(--cr-ink); font-weight:800; }
    .cr-name small { color:#929db0; font-size:11px; }
    .cr-num { font-variant-numeric:tabular-nums; font-weight:700; white-space:nowrap; }
    .cr-empty { padding:42px 16px !important; color:var(--cr-muted) !important; text-align:center; }
    .cr-chart { height:310px; padding:18px 20px; margin-top:18px; }
    .cr-chart h2 { margin-bottom:12px; }
    .cr-foot { margin-top:13px; color:var(--cr-muted); font-size:11px; }
    @media (max-width:1050px) { .cr-kpis { grid-template-columns:repeat(3,minmax(0,1fr)); } }
    @media (max-width:680px) { .cr-hero { flex-direction:column; padding:18px; } .cr-title h1 { font-size:20px; } .cr-kpis { grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px; } .cr-kpi { padding:12px; } .cr-value { font-size:19px; } .cr-card-head { align-items:flex-start; flex-direction:column; } .cr-search { width:100%; } .cr-chart { height:260px; } }
    @media print { .cr-actions,.cr-tabs,.cr-search { display:none !important; } .cr-hero,.cr-kpi,.cr-card,.cr-chart { box-shadow:none; break-inside:avoid; } .cr-table { min-width:0; } .cr-table thead th { position:static; } body { background:#fff !important; } }
</style>

@section('content')
<div class="container-fluid cr-page">
    <header class="cr-hero">
        <div class="cr-title">
            <span class="cr-icon"><i class="fas {{ $current['icon'] }}"></i></span>
            <div>
                <div class="cr-eyebrow">التقارير المالية / العملاء</div>
                <h1>{{ $current['title'] }}</h1>
                <p>{{ $current['subtitle'] }}</p>
                <span class="cr-period"><i class="far fa-calendar-alt"></i> {{ $startDate }} <span>—</span> {{ $endDate }}</span>
            </div>
        </div>
        <div class="cr-actions">
            <button type="button" class="btn btn-outline-primary" onclick="exportCustomerReport()"><i class="fas fa-file-excel ml-1"></i> تصدير Excel</button>
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fas fa-print ml-1"></i> طباعة</button>
            <a href="{{ route('home_reports') }}" class="btn btn-light"><i class="fas fa-arrow-right ml-1"></i> التقارير</a>
        </div>
    </header>

    <nav class="cr-tabs" aria-label="تقارير العملاء">
        <a class="cr-tab {{ $reportType === 'sales' ? 'active' : '' }}" href="{{ $reportRoutes['sales'] }}"><i class="fas fa-chart-line"></i> المبيعات</a>
        <a class="cr-tab {{ $reportType === 'balances' ? 'active' : '' }}" href="{{ $reportRoutes['balances'] }}"><i class="fas fa-file-invoice-dollar"></i> الأرصدة</a>
        <a class="cr-tab {{ $reportType === 'collections' ? 'active' : '' }}" href="{{ $reportRoutes['collections'] }}"><i class="fas fa-hand-holding-usd"></i> التحصيلات</a>
    </nav>

    @if ($reportType === 'sales')
        <div class="cr-note"><i class="fas fa-info-circle ml-1"></i> صافي المبيعات = صافي الفواتير بعد خصومات البيع − قيمة مرتجعات الفترة. تظهر عمليات البيع النقدي والآجل، بما فيها الفواتير غير المرتبطة بملف عميل.</div>
        <section class="cr-kpis">
            <div class="cr-kpi"><span class="cr-label"><i class="fas fa-users"></i> العملاء</span><strong class="cr-value">{{ number_format($stats->customer_count) }}</strong><small>لهم مبيعات أو مرتجعات</small></div>
            <div class="cr-kpi"><span class="cr-label"><i class="fas fa-file-invoice"></i> فواتير المبيعات</span><strong class="cr-value">{{ number_format($stats->invoice_count) }}</strong><small>فاتورة خلال الفترة</small></div>
            <div class="cr-kpi"><span class="cr-label"><i class="fas fa-cart-plus"></i> صافي الفواتير</span><strong class="cr-value">{{ number_format($stats->sales_amount, 2) }}</strong><small>ج.م بعد خصومات البيع</small></div>
            <div class="cr-kpi"><span class="cr-label"><i class="fas fa-undo"></i> المرتجعات</span><strong class="cr-value cr-due">{{ number_format($stats->returned_amount, 2) }}</strong><small>ج.م</small></div>
            <div class="cr-kpi"><span class="cr-label"><i class="fas fa-chart-line"></i> صافي المبيعات</span><strong class="cr-value cr-positive">{{ number_format($stats->net_sales, 2) }}</strong><small>ج.م بعد المرتجعات</small></div>
        </section>
        <section class="cr-card">
            <div class="cr-card-head"><div><h2>مبيعات كل عميل</h2><p>مرتبة من الأعلى حسب صافي المبيعات.</p></div><input type="search" class="form-control cr-search" data-customer-search placeholder="ابحث باسم العميل أو الهاتف"></div>
            <div class="cr-table-wrap"><table class="cr-table table" id="customerTable">
                <thead><tr><th>العميل</th><th>الهاتف</th><th>الفواتير</th><th>قيمة الفواتير</th><th>عدد المرتجعات</th><th>قيمة المرتجعات</th><th>صافي المبيعات</th></tr></thead>
                <tbody>
                @forelse ($customers as $customer)
                    <tr><td><div class="cr-name"><strong>{{ $customer->customer_name }}</strong><small>{{ $customer->customer_id ? 'كود العميل: '.$customer->customer_id : 'بدون ملف عميل' }}</small></div></td><td>{{ $customer->customer_phone ?: '—' }}</td><td>{{ number_format($customer->invoice_count) }}</td><td class="cr-num">{{ number_format($customer->sales_amount, 2) }} ج.م</td><td>{{ number_format($customer->return_count) }}</td><td class="cr-num cr-due">{{ number_format($customer->returned_amount, 2) }} ج.م</td><td class="cr-num cr-positive">{{ number_format($customer->net_sales, 2) }} ج.م</td></tr>
                @empty
                    <tr><td colspan="7" class="cr-empty">لا توجد مبيعات أو مرتجعات للعملاء خلال الفترة المحددة.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </section>
        @if ($chartCustomers->isNotEmpty())<section class="cr-chart"><h2>أعلى العملاء حسب صافي المبيعات</h2><canvas id="customerChart"></canvas></section>@endif
    @elseif ($reportType === 'balances')
        <div class="cr-note"><i class="fas fa-info-circle ml-1"></i> الأرصدة محسوبة حتى نهاية كل تاريخ من الفواتير النشطة، بعد الدفعات الأولى والسدادات المسجلة والمرتجعات. رصيد أول الفترة يخص ما قبل تاريخ البداية.</div>
        <section class="cr-kpis">
            <div class="cr-kpi"><span class="cr-label"><i class="fas fa-users"></i> العملاء</span><strong class="cr-value">{{ number_format($stats->customer_count) }}</strong><small>لهم رصيد أو حركة</small></div>
            <div class="cr-kpi"><span class="cr-label"><i class="fas fa-hourglass-start"></i> رصيد أول الفترة</span><strong class="cr-value cr-due">{{ number_format($stats->opening_balance, 2) }}</strong><small>ج.م</small></div>
            <div class="cr-kpi"><span class="cr-label"><i class="fas fa-cart-plus"></i> صافي الفواتير</span><strong class="cr-value">{{ number_format($stats->sales_amount, 2) }}</strong><small>ج.م بعد خصومات البيع</small></div>
            <div class="cr-kpi"><span class="cr-label"><i class="fas fa-money-bill-wave"></i> التحصيلات</span><strong class="cr-value cr-positive">{{ number_format($stats->collected_amount, 2) }}</strong><small>ج.م خلال الفترة</small></div>
            <div class="cr-kpi"><span class="cr-label"><i class="fas fa-file-invoice-dollar"></i> رصيد آخر الفترة</span><strong class="cr-value cr-due">{{ number_format($stats->closing_balance, 2) }}</strong><small>ج.م مستحق</small></div>
        </section>
        <section class="cr-card">
            <div class="cr-card-head"><div><h2>حركة أرصدة العملاء</h2><p>الرصيد الختامي مبني على المديونية الفعلية لكل فاتورة نشطة.</p></div><input type="search" class="form-control cr-search" data-customer-search placeholder="ابحث باسم العميل أو الهاتف"></div>
            <div class="cr-table-wrap"><table class="cr-table table" id="customerTable">
                <thead><tr><th>العميل</th><th>الهاتف</th><th>فواتير الفترة</th><th>رصيد أول الفترة</th><th>صافي الفواتير</th><th>المرتجعات</th><th>التحصيلات</th><th>رصيد آخر الفترة</th></tr></thead>
                <tbody>
                @forelse ($customers as $customer)
                    <tr><td><div class="cr-name"><strong>{{ $customer->customer_name }}</strong><small>{{ $customer->customer_id ? 'كود العميل: '.$customer->customer_id : 'بدون ملف عميل' }}</small></div></td><td>{{ $customer->customer_phone ?: '—' }}</td><td>{{ number_format($customer->invoice_count) }}</td><td class="cr-num">{{ number_format($customer->opening_balance, 2) }} ج.م</td><td class="cr-num">{{ number_format($customer->sales_amount, 2) }} ج.م</td><td class="cr-num">{{ number_format($customer->returned_amount, 2) }} ج.م</td><td class="cr-num cr-positive">{{ number_format($customer->collected_amount, 2) }} ج.م</td><td class="cr-num cr-due">{{ number_format($customer->closing_balance, 2) }} ج.م</td></tr>
                @empty
                    <tr><td colspan="8" class="cr-empty">لا توجد أرصدة أو حركات للعملاء خلال الفترة المحددة.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </section>
        @if ($chartCustomers->isNotEmpty())<section class="cr-chart"><h2>أعلى الأرصدة المستحقة</h2><canvas id="customerChart"></canvas></section>@endif
    @else
        <div class="cr-note"><i class="fas fa-info-circle ml-1"></i> تشمل التحصيلات الدفعة الأولى بتاريخ الفاتورة والسداد اللاحق بتاريخ تسجيله. المرتجعات تخفض المبيعات/الرصيد ولا تُعامل كتحصيل نقدي.</div>
        <section class="cr-kpis">
            <div class="cr-kpi"><span class="cr-label"><i class="fas fa-users"></i> العملاء</span><strong class="cr-value">{{ number_format($stats->customer_count) }}</strong><small>لهم تحصيلات بالفترة</small></div>
            <div class="cr-kpi"><span class="cr-label"><i class="fas fa-file-invoice"></i> فواتير بدفعة أولى</span><strong class="cr-value">{{ number_format($stats->invoice_count) }}</strong><small>فاتورة</small></div>
            <div class="cr-kpi"><span class="cr-label"><i class="fas fa-cash-register"></i> دفعات أولى</span><strong class="cr-value">{{ number_format($stats->invoice_payments, 2) }}</strong><small>ج.م</small></div>
            <div class="cr-kpi"><span class="cr-label"><i class="fas fa-receipt"></i> سداد لاحق</span><strong class="cr-value">{{ number_format($stats->later_payments, 2) }}</strong><small>ج.م</small></div>
            <div class="cr-kpi"><span class="cr-label"><i class="fas fa-hand-holding-usd"></i> إجمالي التحصيل</span><strong class="cr-value cr-positive">{{ number_format($stats->total_collected, 2) }}</strong><small>ج.م خلال الفترة</small></div>
        </section>
        <section class="cr-card">
            <div class="cr-card-head"><div><h2>التحصيل حسب العميل</h2><p>مفصول إلى دفعات أولى وسداد لاحق لتوضيح مصدر التحصيل.</p></div><input type="search" class="form-control cr-search" data-customer-search placeholder="ابحث باسم العميل أو الهاتف"></div>
            <div class="cr-table-wrap"><table class="cr-table table" id="customerTable">
                <thead><tr><th>العميل</th><th>الهاتف</th><th>فواتير بدفعة أولى</th><th>الدفعات الأولى</th><th>السداد اللاحق</th><th>إجمالي التحصيل</th></tr></thead>
                <tbody>
                @forelse ($customers as $customer)
                    <tr><td><div class="cr-name"><strong>{{ $customer->customer_name }}</strong><small>{{ $customer->customer_id ? 'كود العميل: '.$customer->customer_id : 'بدون ملف عميل' }}</small></div></td><td>{{ $customer->customer_phone ?: '—' }}</td><td>{{ number_format($customer->invoice_count) }}</td><td class="cr-num">{{ number_format($customer->invoice_payments, 2) }} ج.م</td><td class="cr-num">{{ number_format($customer->later_payments, 2) }} ج.م</td><td class="cr-num cr-positive">{{ number_format($customer->total_collected, 2) }} ج.م</td></tr>
                @empty
                    <tr><td colspan="6" class="cr-empty">لا توجد تحصيلات للعملاء خلال الفترة المحددة.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </section>
        @if ($chartCustomers->isNotEmpty())<section class="cr-chart"><h2>أعلى العملاء حسب التحصيل</h2><canvas id="customerChart"></canvas></section>@endif
    @endif
    <p class="cr-foot">المبالغ بالجنيه المصري. يتم استبعاد الفواتير والدفعات والمرتجعات المحذوفة.</p>
</div>
@endsection

@section('js')
<script src="{{ URL::asset('assets/js/chart.js') }}"></script>
<script src="{{ URL::asset('assets/js/xlsx.full.min.js') }}"></script>
<script>
    document.querySelector('[data-customer-search]')?.addEventListener('input', function () {
        const query = this.value.trim().toLocaleLowerCase();
        document.querySelectorAll('#customerTable tbody tr').forEach(row => { row.hidden = !row.textContent.toLocaleLowerCase().includes(query); });
    });
    window.exportCustomerReport = function () {
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, XLSX.utils.table_to_sheet(document.getElementById('customerTable')), 'تقرير العملاء');
        XLSX.writeFile(workbook, 'تقرير_العملاء_{{ $reportType }}_{{ $startDate }}_{{ $endDate }}.xlsx');
    };
    @if ($chartCustomers->isNotEmpty())
        @if ($reportType === 'sales')
            const chartValues = @json($chartCustomers->pluck('net_sales'));
            const chartLabel = 'صافي المبيعات (ج.م)';
            const chartColor = 'rgba(56,103,200,.72)';
        @elseif ($reportType === 'balances')
            const chartValues = @json($chartCustomers->pluck('closing_balance'));
            const chartLabel = 'الرصيد المستحق (ج.م)';
            const chartColor = 'rgba(179,77,85,.72)';
        @else
            const chartValues = @json($chartCustomers->pluck('total_collected'));
            const chartLabel = 'إجمالي التحصيل (ج.م)';
            const chartColor = 'rgba(20,147,109,.72)';
        @endif
        new Chart(document.getElementById('customerChart').getContext('2d'), {
            type: 'bar',
            data: { labels: @json($chartCustomers->pluck('customer_name')), datasets: [{ label: chartLabel, data: chartValues, backgroundColor: chartColor, borderRadius: 5 }] },
            options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', scales: { x: { beginAtZero: true } } }
        });
    @endif
</script>
@endsection
