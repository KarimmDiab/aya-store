@extends('layouts.master3')
<title>معرض أيه - تقارير المبيعات</title>

@php
    $reportMeta = [
        'daily' => ['title' => 'ملخص المبيعات اليومية', 'subtitle' => 'متابعة قيمة الفواتير والمرتجعات وصافي المبيعات يومًا بيوم.', 'icon' => 'fa-calendar-day'],
        'products' => ['title' => 'المبيعات حسب المنتج', 'subtitle' => 'تحليل الكميات وقيمة البيع والمرتجعات لكل منتج.', 'icon' => 'fa-box-open'],
        'customers' => ['title' => 'المبيعات حسب العميل', 'subtitle' => 'مقارنة صافي مشتريات العملاء بعد احتساب المرتجعات.', 'icon' => 'fa-users'],
        'users' => ['title' => 'المبيعات حسب الموظف', 'subtitle' => 'متابعة الفواتير وصافي المبيعات والمرتجعات حسب الموظف.', 'icon' => 'fa-user-tie'],
    ];
    $current = $reportMeta[$reportType];
    $reportRoutes = [
        'daily' => route('total_sales', ['startDate' => $startDate, 'endDate' => $endDate]),
        'products' => route('sales_per_product', ['startDate' => $startDate, 'endDate' => $endDate]),
        'customers' => route('salesPerCustomer', ['startDate' => $startDate, 'endDate' => $endDate]),
        'users' => route('salesPerUser', ['startDate' => $startDate, 'endDate' => $endDate]),
    ];
    $chartRows = $rows->take(12);
@endphp

<style>
    .srp-page { --srp-ink:#18243b; --srp-muted:#77839a; --srp-line:#e7ebf2; --srp-orange:#d66b20; direction:rtl; color:var(--srp-ink); }
    .srp-hero { display:flex; justify-content:space-between; align-items:flex-start; gap:18px; padding:24px 26px; margin-bottom:20px; border:1px solid var(--srp-line); border-radius:18px; background:linear-gradient(120deg,#fff 45%,#fff6ee); box-shadow:0 8px 24px rgba(20,42,82,.05); }
    .srp-title { display:flex; gap:14px; align-items:flex-start; }
    .srp-icon { display:grid; place-items:center; width:50px; height:50px; flex:0 0 50px; border-radius:15px; color:#fff; background:linear-gradient(145deg,#d66b20,#f2a75f); font-size:20px; }
    .srp-eyebrow { margin-bottom:4px; color:var(--srp-orange); font-size:12px; font-weight:800; }
    .srp-title h1 { margin:0; color:var(--srp-ink); font-size:24px; font-weight:800; }
    .srp-title p { margin:6px 0 0; color:var(--srp-muted); font-size:14px; }
    .srp-period { display:inline-flex; align-items:center; gap:7px; margin-top:12px; padding:6px 11px; border-radius:999px; background:#fff0e2; color:#9d4e17; font-size:12px; font-weight:700; }
    .srp-actions { display:flex; flex-wrap:wrap; gap:8px; }
    .srp-actions .btn { min-height:40px; border-radius:10px; font-weight:700; }
    .srp-tabs { display:flex; gap:8px; overflow:auto; margin-bottom:18px; padding-bottom:5px; }
    .srp-tab { display:inline-flex; align-items:center; gap:8px; padding:10px 14px; white-space:nowrap; border:1px solid var(--srp-line); border-radius:11px; background:#fff; color:#59677f; font-size:13px; font-weight:700; text-decoration:none; }
    .srp-tab:hover { color:var(--srp-orange); border-color:#f1d2b9; background:#fffaf6; text-decoration:none; }
    .srp-tab.active { color:#fff; border-color:var(--srp-orange); background:var(--srp-orange); box-shadow:0 5px 14px rgba(214,107,32,.2); }
    .srp-note { padding:11px 14px; margin-bottom:18px; border:1px solid #f1ddcb; border-radius:11px; background:#fffaf6; color:#716252; font-size:13px; line-height:1.7; }
    .srp-kpis { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:12px; margin-bottom:18px; }
    .srp-kpi { min-width:0; padding:15px 16px; border:1px solid var(--srp-line); border-radius:14px; background:#fff; box-shadow:0 4px 14px rgba(20,42,82,.035); }
    .srp-label { display:flex; align-items:center; gap:8px; color:var(--srp-muted); font-size:12px; font-weight:700; }
    .srp-label i { color:var(--srp-orange); }
    .srp-value { display:block; overflow:hidden; margin-top:8px; color:var(--srp-ink); font-size:21px; font-weight:800; text-overflow:ellipsis; white-space:nowrap; }
    .srp-kpi small { color:var(--srp-muted); font-size:11px; }
    .srp-positive { color:#13865c !important; }
    .srp-return { color:#b34d55 !important; }
    .srp-card,.srp-chart { overflow:hidden; border:1px solid var(--srp-line); border-radius:15px; background:#fff; box-shadow:0 6px 20px rgba(20,42,82,.04); }
    .srp-card-head { display:flex; justify-content:space-between; align-items:center; gap:14px; padding:16px 18px; border-bottom:1px solid var(--srp-line); }
    .srp-card-head h2,.srp-chart h2 { margin:0; color:var(--srp-ink); font-size:16px; font-weight:800; }
    .srp-card-head p { margin:4px 0 0; color:var(--srp-muted); font-size:12px; }
    .srp-search { width:min(100%,320px); }
    .srp-table-wrap { overflow:auto; }
    .srp-table { width:100%; min-width:780px; margin:0; border-collapse:separate; border-spacing:0; }
    .srp-table thead th { position:sticky; top:0; z-index:1; padding:12px 13px; border:0; background:#fff6ee; color:#78634f; font-size:12px; font-weight:800; white-space:nowrap; }
    .srp-table tbody td { padding:12px 13px; border-top:1px solid #f2eee9; color:#35435a; font-size:13px; vertical-align:middle; }
    .srp-table tbody tr:hover { background:#fffcf9; }
    .srp-name { display:flex; flex-direction:column; gap:2px; min-width:145px; }
    .srp-name strong { color:var(--srp-ink); font-weight:800; }
    .srp-name small { color:#929db0; font-size:11px; }
    .srp-num { font-variant-numeric:tabular-nums; font-weight:700; white-space:nowrap; }
    .srp-empty { padding:42px 16px !important; color:var(--srp-muted) !important; text-align:center; }
    .srp-chart { height:310px; padding:18px 20px; margin-top:18px; }
    .srp-chart h2 { margin-bottom:12px; }
    .srp-foot { margin-top:13px; color:var(--srp-muted); font-size:11px; }
    @media (max-width:1050px) { .srp-kpis { grid-template-columns:repeat(3,minmax(0,1fr)); } }
    @media (max-width:680px) { .srp-hero { flex-direction:column; padding:18px; } .srp-title h1 { font-size:20px; } .srp-kpis { grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px; } .srp-kpi { padding:12px; } .srp-value { font-size:19px; } .srp-card-head { align-items:flex-start; flex-direction:column; } .srp-search { width:100%; } .srp-chart { height:260px; } }
    @media print { .srp-actions,.srp-tabs,.srp-search { display:none !important; } .srp-hero,.srp-kpi,.srp-card,.srp-chart { box-shadow:none; break-inside:avoid; } .srp-table { min-width:0; } .srp-table thead th { position:static; } body { background:#fff !important; } }
</style>

@section('content')
<div class="container-fluid srp-page">
    <header class="srp-hero">
        <div class="srp-title">
            <span class="srp-icon"><i class="fas {{ $current['icon'] }}"></i></span>
            <div>
                <div class="srp-eyebrow">التقارير المالية / المبيعات</div>
                <h1>{{ $current['title'] }}</h1>
                <p>{{ $current['subtitle'] }}</p>
                <span class="srp-period"><i class="far fa-calendar-alt"></i> {{ $startDate }} <span>—</span> {{ $endDate }}</span>
            </div>
        </div>
        <div class="srp-actions">
            <button type="button" class="btn btn-outline-primary" onclick="exportSalesReport()"><i class="fas fa-file-excel ml-1"></i> تصدير Excel</button>
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fas fa-print ml-1"></i> طباعة</button>
            <a href="{{ route('home_reports') }}" class="btn btn-light"><i class="fas fa-arrow-right ml-1"></i> التقارير</a>
        </div>
    </header>

    <nav class="srp-tabs" aria-label="تقارير المبيعات">
        <a class="srp-tab {{ $reportType === 'daily' ? 'active' : '' }}" href="{{ $reportRoutes['daily'] }}"><i class="fas fa-calendar-day"></i> الملخص اليومي</a>
        <a class="srp-tab {{ $reportType === 'products' ? 'active' : '' }}" href="{{ $reportRoutes['products'] }}"><i class="fas fa-box-open"></i> حسب المنتج</a>
        <a class="srp-tab {{ $reportType === 'customers' ? 'active' : '' }}" href="{{ $reportRoutes['customers'] }}"><i class="fas fa-users"></i> حسب العميل</a>
        <a class="srp-tab {{ $reportType === 'users' ? 'active' : '' }}" href="{{ $reportRoutes['users'] }}"><i class="fas fa-user-tie"></i> حسب الموظف</a>
    </nav>

    @if ($reportType === 'daily')
        <div class="srp-note"><i class="fas fa-info-circle ml-1"></i> تُسجل الفاتورة بتاريخ إنشائها والمرتجع بتاريخ تسجيله. صافي المبيعات اليومي يساوي صافي الفواتير بعد خصوماتها ناقص مرتجعات اليوم.</div>
        <section class="srp-kpis">
            <div class="srp-kpi"><span class="srp-label"><i class="fas fa-file-invoice"></i> الفواتير</span><strong class="srp-value">{{ number_format($stats->number_of_invoices) }}</strong><small>فاتورة بالفترة</small></div>
            <div class="srp-kpi"><span class="srp-label"><i class="fas fa-cart-plus"></i> صافي الفواتير</span><strong class="srp-value">{{ number_format($stats->invoice_sales, 2) }}</strong><small>ج.م بعد خصومات البيع</small></div>
            <div class="srp-kpi"><span class="srp-label"><i class="fas fa-undo"></i> المرتجعات</span><strong class="srp-value srp-return">{{ number_format($stats->returned_amount, 2) }}</strong><small>ج.م</small></div>
            <div class="srp-kpi"><span class="srp-label"><i class="fas fa-chart-line"></i> صافي المبيعات</span><strong class="srp-value srp-positive">{{ number_format($stats->total_amount_of_invoices, 2) }}</strong><small>ج.م بعد المرتجعات</small></div>
            <div class="srp-kpi"><span class="srp-label"><i class="fas fa-receipt"></i> متوسط الفاتورة</span><strong class="srp-value">{{ number_format($stats->avg_amount_of_invoices, 2) }}</strong><small>ج.م صافيًا</small></div>
        </section>
        <section class="srp-card">
            <div class="srp-card-head"><div><h2>المبيعات يومًا بيوم</h2><p>يشمل الأيام التي لا توجد بها حركة لإظهار استمرارية الفترة.</p></div><input type="search" class="form-control srp-search" data-sales-search placeholder="ابحث بالتاريخ"></div>
            <div class="srp-table-wrap"><table class="srp-table table" id="salesTable">
                <thead><tr><th>التاريخ</th><th>الفواتير</th><th>صافي الفواتير</th><th>المرتجعات</th><th>صافي المبيعات</th></tr></thead>
                <tbody>@forelse ($rows as $row)
                    <tr><td>{{ $row->invoice_date }}</td><td>{{ number_format($row->number_of_invoices) }}</td><td class="srp-num">{{ number_format($row->invoice_sales, 2) }} ج.م</td><td class="srp-num srp-return">{{ number_format($row->returned_amount, 2) }} ج.م</td><td class="srp-num srp-positive">{{ number_format($row->total_amount_of_invoices, 2) }} ج.م</td></tr>
                @empty<tr><td colspan="5" class="srp-empty">لا توجد بيانات للفترة المحددة.</td></tr>@endforelse</tbody>
            </table></div>
        </section>
        @if ($chartRows->isNotEmpty())<section class="srp-chart"><h2>اتجاه صافي المبيعات اليومي</h2><canvas id="salesChart"></canvas></section>@endif
    @elseif ($reportType === 'products')
        <div class="srp-note"><i class="fas fa-info-circle ml-1"></i> تُوزع خصومات الفواتير والمرتجعات على المنتجات بنسبة قيمتها، بحيث تتطابق المجاميع مع قيمة الفواتير والمرتجعات المسجلة.</div>
        <section class="srp-kpis">
            <div class="srp-kpi"><span class="srp-label"><i class="fas fa-boxes"></i> المنتجات</span><strong class="srp-value">{{ number_format($stats->row_count) }}</strong><small>لها بيع أو مرتجع</small></div>
            <div class="srp-kpi"><span class="srp-label"><i class="fas fa-cubes"></i> الكمية المباعة</span><strong class="srp-value">{{ number_format($stats->sold_quantity) }}</strong><small>وحدة</small></div>
            <div class="srp-kpi"><span class="srp-label"><i class="fas fa-undo"></i> الكمية المرتجعة</span><strong class="srp-value srp-return">{{ number_format($stats->returned_quantity) }}</strong><small>وحدة</small></div>
            <div class="srp-kpi"><span class="srp-label"><i class="fas fa-money-bill-wave"></i> صافي الفواتير</span><strong class="srp-value">{{ number_format($stats->sales_amount, 2) }}</strong><small>ج.م</small></div>
            <div class="srp-kpi"><span class="srp-label"><i class="fas fa-chart-line"></i> صافي المبيعات</span><strong class="srp-value srp-positive">{{ number_format($stats->net_sales, 2) }}</strong><small>ج.م بعد المرتجعات</small></div>
        </section>
        <section class="srp-card">
            <div class="srp-card-head"><div><h2>تفاصيل المبيعات حسب المنتج</h2><p>تظهر المرتجعات حتى لو كان البيع الأصلي في فترة سابقة.</p></div><input type="search" class="form-control srp-search" data-sales-search placeholder="ابحث باسم المنتج أو كوده"></div>
            <div class="srp-table-wrap"><table class="srp-table table" id="salesTable">
                <thead><tr><th>المنتج</th><th>الكمية المباعة</th><th>المرتجعة</th><th>صافي الكمية</th><th>قيمة الفواتير</th><th>قيمة المرتجعات</th><th>صافي المبيعات</th></tr></thead>
                <tbody>@forelse ($rows as $row)
                    <tr><td><div class="srp-name"><strong>{{ $row->product_name }}</strong><small>كود المنتج: {{ $row->product_id }}</small></div></td><td>{{ number_format($row->total_sold_quantity) }}</td><td>{{ number_format($row->returned_quantity) }}</td><td>{{ number_format($row->net_sold_quantity) }}</td><td class="srp-num">{{ number_format($row->sales_amount, 2) }} ج.م</td><td class="srp-num srp-return">{{ number_format($row->returned_amount, 2) }} ج.م</td><td class="srp-num srp-positive">{{ number_format($row->net_sales, 2) }} ج.م</td></tr>
                @empty<tr><td colspan="7" class="srp-empty">لا توجد مبيعات أو مرتجعات للمنتجات خلال الفترة.</td></tr>@endforelse</tbody>
            </table></div>
        </section>
        @if ($chartRows->isNotEmpty())<section class="srp-chart"><h2>أعلى المنتجات حسب صافي المبيعات</h2><canvas id="salesChart"></canvas></section>@endif
    @elseif ($reportType === 'customers')
        <div class="srp-note"><i class="fas fa-info-circle ml-1"></i> صافي المبيعات لكل عميل يساوي صافي فواتيره بعد خصومات البيع ناقص قيمة مرتجعاته خلال الفترة. تظهر المبيعات النقدية والآجلة.</div>
        <section class="srp-kpis">
            <div class="srp-kpi"><span class="srp-label"><i class="fas fa-users"></i> العملاء</span><strong class="srp-value">{{ number_format($stats->row_count) }}</strong><small>لهم بيع أو مرتجع</small></div>
            <div class="srp-kpi"><span class="srp-label"><i class="fas fa-file-invoice"></i> الفواتير</span><strong class="srp-value">{{ number_format($stats->invoice_count) }}</strong><small>فاتورة</small></div>
            <div class="srp-kpi"><span class="srp-label"><i class="fas fa-cart-plus"></i> صافي الفواتير</span><strong class="srp-value">{{ number_format($stats->sales_amount, 2) }}</strong><small>ج.م</small></div>
            <div class="srp-kpi"><span class="srp-label"><i class="fas fa-undo"></i> المرتجعات</span><strong class="srp-value srp-return">{{ number_format($stats->returned_amount, 2) }}</strong><small>ج.م</small></div>
            <div class="srp-kpi"><span class="srp-label"><i class="fas fa-chart-line"></i> صافي المبيعات</span><strong class="srp-value srp-positive">{{ number_format($stats->net_sales, 2) }}</strong><small>ج.م بعد المرتجعات</small></div>
        </section>
        <section class="srp-card">
            <div class="srp-card-head"><div><h2>مبيعات كل عميل</h2><p>مرتبة من الأعلى حسب صافي المبيعات.</p></div><input type="search" class="form-control srp-search" data-sales-search placeholder="ابحث باسم العميل أو الهاتف"></div>
            <div class="srp-table-wrap"><table class="srp-table table" id="salesTable">
                <thead><tr><th>العميل</th><th>الهاتف</th><th>الفواتير</th><th>عدد المرتجعات</th><th>صافي الفواتير</th><th>قيمة المرتجعات</th><th>صافي المبيعات</th></tr></thead>
                <tbody>@forelse ($rows as $row)
                    <tr><td><div class="srp-name"><strong>{{ $row->customer_name }}</strong><small>{{ $row->customer_id ? 'كود العميل: '.$row->customer_id : 'بدون ملف عميل' }}</small></div></td><td>{{ $row->customer_phone ?: '—' }}</td><td>{{ number_format($row->invoices_number) }}</td><td>{{ number_format($row->return_count) }}</td><td class="srp-num">{{ number_format($row->sales_amount, 2) }} ج.م</td><td class="srp-num srp-return">{{ number_format($row->returned_amount, 2) }} ج.م</td><td class="srp-num srp-positive">{{ number_format($row->net_sales, 2) }} ج.م</td></tr>
                @empty<tr><td colspan="7" class="srp-empty">لا توجد مبيعات أو مرتجعات للعملاء خلال الفترة.</td></tr>@endforelse</tbody>
            </table></div>
        </section>
        @if ($chartRows->isNotEmpty())<section class="srp-chart"><h2>أعلى العملاء حسب صافي المبيعات</h2><canvas id="salesChart"></canvas></section>@endif
    @else
        <div class="srp-note"><i class="fas fa-info-circle ml-1"></i> تُنسب قيمة المرتجعات إلى الموظف الذي أنشأ الفاتورة الأصلية، بينما تُسجل المبيعات بتاريخ الفاتورة.</div>
        <section class="srp-kpis">
            <div class="srp-kpi"><span class="srp-label"><i class="fas fa-user-tie"></i> الموظفون</span><strong class="srp-value">{{ number_format($stats->row_count) }}</strong><small>لهم مبيعات أو مرتجعات</small></div>
            <div class="srp-kpi"><span class="srp-label"><i class="fas fa-file-invoice"></i> الفواتير</span><strong class="srp-value">{{ number_format($stats->invoice_count) }}</strong><small>فاتورة</small></div>
            <div class="srp-kpi"><span class="srp-label"><i class="fas fa-cart-plus"></i> صافي الفواتير</span><strong class="srp-value">{{ number_format($stats->sales_amount, 2) }}</strong><small>ج.م</small></div>
            <div class="srp-kpi"><span class="srp-label"><i class="fas fa-undo"></i> المرتجعات</span><strong class="srp-value srp-return">{{ number_format($stats->returned_amount, 2) }}</strong><small>ج.م</small></div>
            <div class="srp-kpi"><span class="srp-label"><i class="fas fa-chart-line"></i> صافي المبيعات</span><strong class="srp-value srp-positive">{{ number_format($stats->net_sales, 2) }}</strong><small>ج.م بعد المرتجعات</small></div>
        </section>
        <section class="srp-card">
            <div class="srp-card-head"><div><h2>مبيعات كل موظف</h2><p>مرتبة حسب صافي المبيعات المنفذة.</p></div><input type="search" class="form-control srp-search" data-sales-search placeholder="ابحث باسم الموظف أو الهاتف"></div>
            <div class="srp-table-wrap"><table class="srp-table table" id="salesTable">
                <thead><tr><th>الموظف</th><th>الهاتف</th><th>البريد الإلكتروني</th><th>الفواتير</th><th>صافي الفواتير</th><th>قيمة المرتجعات</th><th>صافي المبيعات</th></tr></thead>
                <tbody>@forelse ($rows as $row)
                    <tr><td><div class="srp-name"><strong>{{ $row->user_name }}</strong><small>كود الموظف: {{ $row->user_id ?? '—' }}</small></div></td><td>{{ $row->user_phone ?: '—' }}</td><td>{{ $row->user_email ?: '—' }}</td><td>{{ number_format($row->count_sales_invoice) }}</td><td class="srp-num">{{ number_format($row->sales_amount, 2) }} ج.م</td><td class="srp-num srp-return">{{ number_format($row->returned_amount, 2) }} ج.م</td><td class="srp-num srp-positive">{{ number_format($row->net_sales, 2) }} ج.م</td></tr>
                @empty<tr><td colspan="7" class="srp-empty">لا توجد مبيعات أو مرتجعات للموظفين خلال الفترة.</td></tr>@endforelse</tbody>
            </table></div>
        </section>
        @if ($chartRows->isNotEmpty())<section class="srp-chart"><h2>أعلى الموظفين حسب صافي المبيعات</h2><canvas id="salesChart"></canvas></section>@endif
    @endif
    <p class="srp-foot">المبالغ بالجنيه المصري. تُستبعد الفواتير والمرتجعات المحذوفة من الإجماليات.</p>
</div>
@endsection

@section('js')
<script src="{{ URL::asset('assets/js/chart.js') }}"></script>
<script src="{{ URL::asset('assets/js/xlsx.full.min.js') }}"></script>
<script>
    document.querySelector('[data-sales-search]')?.addEventListener('input', function () {
        const query = this.value.trim().toLocaleLowerCase();
        document.querySelectorAll('#salesTable tbody tr').forEach(row => { row.hidden = !row.textContent.toLocaleLowerCase().includes(query); });
    });
    window.exportSalesReport = function () {
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, XLSX.utils.table_to_sheet(document.getElementById('salesTable')), 'تقرير المبيعات');
        XLSX.writeFile(workbook, 'تقرير_المبيعات_{{ $reportType }}_{{ $startDate }}_{{ $endDate }}.xlsx');
    };
    @if ($chartRows->isNotEmpty())
        @if ($reportType === 'daily')
            const chartLabels = @json($chartRows->pluck('invoice_date'));
            const chartSales = @json($chartRows->pluck('total_amount_of_invoices'));
            const chartTitle = 'صافي المبيعات اليومي (ج.م)';
            const chartColor = 'rgba(214,107,32,.72)';
            const chartType = 'line';
        @elseif ($reportType === 'products')
            const chartLabels = @json($chartRows->pluck('product_name'));
            const chartSales = @json($chartRows->pluck('net_sales'));
            const chartTitle = 'صافي المبيعات (ج.م)';
            const chartColor = 'rgba(214,107,32,.72)';
            const chartType = 'bar';
        @elseif ($reportType === 'customers')
            const chartLabels = @json($chartRows->pluck('customer_name'));
            const chartSales = @json($chartRows->pluck('net_sales'));
            const chartTitle = 'صافي المبيعات (ج.م)';
            const chartColor = 'rgba(56,103,200,.72)';
            const chartType = 'bar';
        @else
            const chartLabels = @json($chartRows->pluck('user_name'));
            const chartSales = @json($chartRows->pluck('net_sales'));
            const chartTitle = 'صافي المبيعات (ج.م)';
            const chartColor = 'rgba(20,147,109,.72)';
            const chartType = 'bar';
        @endif
        new Chart(document.getElementById('salesChart').getContext('2d'), {
            type: chartType,
            data: { labels: chartLabels, datasets: [{ label: chartTitle, data: chartSales, backgroundColor: chartColor, borderColor: chartColor, borderWidth: 1, borderRadius: 5, fill: chartType === 'line' }] },
            options: { responsive: true, maintainAspectRatio: false, indexAxis: chartType === 'bar' ? 'y' : 'x', scales: { y: { beginAtZero: true } } }
        });
    @endif
</script>
@endsection
