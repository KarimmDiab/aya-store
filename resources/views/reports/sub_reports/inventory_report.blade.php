@extends('layouts.master3')
<title>معرض أيه - تقارير المخزون</title>

@php
    $reportMeta = [
        'summary' => ['title' => 'أرصدة المخزون', 'subtitle' => 'قارن رصيد كل منتج في بداية الفترة ونهايتها.', 'icon' => 'fa-boxes'],
        'movements' => ['title' => 'حركة المخزون', 'subtitle' => 'راجع الكميات المستلمة والمصروفة والمرتجعة خلال الفترة.', 'icon' => 'fa-exchange-alt'],
        'low_stock' => ['title' => 'تنبيهات إعادة الطلب', 'subtitle' => 'المنتجات التي وصل رصيدها إلى حد التنبيه أو أقل بنهاية الفترة.', 'icon' => 'fa-exclamation-triangle'],
    ][$reportType];

    $reportRoutes = [
        'summary' => route('inventory_stock_summary', ['startDate' => $startDate, 'endDate' => $endDate]),
        'movements' => route('inventory_movements', ['startDate' => $startDate, 'endDate' => $endDate]),
        'low_stock' => route('inventory_low_stock', ['startDate' => $startDate, 'endDate' => $endDate]),
    ];

    if (in_array($reportType, ['summary', 'low_stock'], true)) {
        $chartProducts = $reportType === 'summary'
            ? $products->sortByDesc(fn ($product) => $product->purchased_quantity + $product->purchase_returned_quantity + $product->sold_quantity + $product->returned_quantity)->take(8)->values()
            : $products->take(10)->values();
    }
@endphp

<style>
    .inv-page { --inv-ink:#18243b; --inv-muted:#77839a; --inv-line:#e7ebf2; --inv-blue:#1769e0; --inv-bg:#f5f7fb; direction:rtl; color:var(--inv-ink); }
    .inv-hero { display:flex; align-items:flex-start; justify-content:space-between; gap:18px; padding:24px 26px; margin:0 0 20px; border:1px solid var(--inv-line); border-radius:18px; background:linear-gradient(120deg,#fff 45%,#f0f6ff); box-shadow:0 8px 24px rgba(20,42,82,.05); }
    .inv-title { display:flex; align-items:flex-start; gap:14px; }
    .inv-title-icon { display:grid; place-items:center; width:50px; height:50px; flex:0 0 50px; border-radius:15px; color:#fff; background:linear-gradient(145deg,#1769e0,#58a0ff); font-size:20px; }
    .inv-eyebrow { color:var(--inv-blue); font-size:12px; font-weight:800; letter-spacing:.04em; margin-bottom:4px; }
    .inv-title h1 { margin:0; color:var(--inv-ink); font-size:24px; font-weight:800; }
    .inv-title p { margin:6px 0 0; color:var(--inv-muted); font-size:14px; }
    .inv-actions { display:flex; flex-wrap:wrap; gap:8px; }
    .inv-actions .btn { min-height:40px; border-radius:10px; font-weight:700; }
    .inv-period { display:inline-flex; align-items:center; gap:7px; margin-top:12px; padding:6px 11px; border-radius:999px; background:#edf4ff; color:#275da8; font-size:12px; font-weight:700; }
    .inv-tabs { display:flex; gap:8px; overflow:auto; padding:0 2px 5px; margin-bottom:18px; }
    .inv-tab { display:inline-flex; align-items:center; gap:8px; white-space:nowrap; padding:10px 14px; border:1px solid var(--inv-line); border-radius:11px; background:#fff; color:#59677f; font-size:13px; font-weight:700; text-decoration:none; transition:.15s ease; }
    .inv-tab:hover { color:var(--inv-blue); border-color:#bfd5fb; background:#f7faff; text-decoration:none; }
    .inv-tab.active { color:#fff; border-color:var(--inv-blue); background:var(--inv-blue); box-shadow:0 5px 14px rgba(23,105,224,.2); }
    .inv-notice { padding:11px 14px; margin-bottom:18px; border:1px solid #dce8fb; border-radius:11px; background:#f8fbff; color:#536984; font-size:13px; }
    .inv-kpis { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:12px; margin-bottom:18px; }
    .inv-kpi { min-width:0; padding:15px 16px; border:1px solid var(--inv-line); border-radius:14px; background:#fff; box-shadow:0 4px 14px rgba(20,42,82,.035); }
    .inv-kpi-label { display:flex; align-items:center; gap:8px; color:var(--inv-muted); font-size:12px; font-weight:700; }
    .inv-kpi-label i { color:var(--inv-blue); }
    .inv-kpi-value { display:block; overflow:hidden; margin-top:8px; color:var(--inv-ink); font-size:23px; font-weight:800; text-overflow:ellipsis; white-space:nowrap; }
    .inv-kpi small { color:var(--inv-muted); font-size:11px; }
    .inv-card { overflow:hidden; border:1px solid var(--inv-line); border-radius:15px; background:#fff; box-shadow:0 6px 20px rgba(20,42,82,.04); }
    .inv-card-head { display:flex; align-items:center; justify-content:space-between; gap:14px; padding:16px 18px; border-bottom:1px solid var(--inv-line); }
    .inv-card-head h2 { margin:0; color:var(--inv-ink); font-size:16px; font-weight:800; }
    .inv-card-head p { margin:4px 0 0; color:var(--inv-muted); font-size:12px; }
    .inv-search { width:min(100%,300px); }
    .inv-table-wrap { overflow:auto; }
    .inv-table { width:100%; min-width:780px; margin:0; border-collapse:separate; border-spacing:0; }
    .inv-table thead th { position:sticky; top:0; z-index:1; padding:12px 13px; border:0; background:#f5f8fd; color:#647189; font-size:12px; font-weight:800; white-space:nowrap; }
    .inv-table tbody td { padding:12px 13px; border-top:1px solid #edf0f5; color:#35435a; font-size:13px; vertical-align:middle; }
    .inv-table tbody tr:hover { background:#f9fbff; }
    .inv-product { display:flex; flex-direction:column; gap:2px; min-width:145px; }
    .inv-product strong { color:var(--inv-ink); font-weight:800; }
    .inv-product small { color:#929db0; font-size:11px; }
    .inv-number { font-variant-numeric:tabular-nums; font-weight:700; white-space:nowrap; }
    .inv-positive { color:#13865c !important; }
    .inv-negative { color:#d74e55 !important; }
    .inv-neutral { color:#65738a !important; }
    .inv-badge { display:inline-flex; align-items:center; gap:6px; padding:5px 9px; border-radius:999px; font-size:11px; font-weight:800; white-space:nowrap; }
    .inv-badge.purchase { color:#117a54; background:#e8f7f0; }
    .inv-badge.sale { color:#b7444e; background:#fff0f0; }
    .inv-badge.return { color:#8058ba; background:#f2edfb; }
    .inv-badge.return-out { color:#bd3f49; background:#ffeded; }
    .inv-badge.low { color:#a56508; background:#fff5df; }
    .inv-badge.out { color:#bd3f49; background:#ffeded; }
    .inv-empty { padding:42px 16px !important; color:var(--inv-muted) !important; text-align:center; }
    .inv-chart { height:310px; padding:17px 20px; margin-top:18px; border:1px solid var(--inv-line); border-radius:15px; background:#fff; }
    .inv-chart h2 { margin:0 0 12px; color:var(--inv-ink); font-size:15px; font-weight:800; }
    .inv-pagination { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:13px 16px; border-top:1px solid var(--inv-line); color:var(--inv-muted); font-size:12px; }
    .inv-pagination nav { margin-inline-start:auto; }
    .inv-pagination .pagination { margin:0; }
    .inv-pagination .page-link { border-radius:7px !important; margin:0 2px; }
    .inv-footer-note { margin-top:13px; color:var(--inv-muted); font-size:11px; }
    @media (max-width:1050px) { .inv-kpis { grid-template-columns:repeat(3,minmax(0,1fr)); } }
    @media (max-width:680px) { .inv-hero { flex-direction:column; padding:18px; } .inv-title h1 { font-size:20px; } .inv-kpis { grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px; } .inv-kpi { padding:12px; } .inv-kpi-value { font-size:20px; } .inv-card-head { align-items:flex-start; flex-direction:column; } .inv-search { width:100%; } .inv-chart { height:260px; } }
    @media print { .inv-actions,.inv-tabs,.inv-search,.inv-pagination { display:none !important; } .inv-hero,.inv-kpi,.inv-card,.inv-chart { box-shadow:none; break-inside:avoid; } .inv-table { min-width:0; } .inv-table thead th { position:static; } body { background:#fff !important; } }
</style>

@section('content')
<div class="container-fluid inv-page">
    <header class="inv-hero">
        <div>
            <div class="inv-title">
                <span class="inv-title-icon"><i class="fas {{ $reportMeta['icon'] }}"></i></span>
                <div>
                    <div class="inv-eyebrow">إدارة المخزون / التقارير</div>
                    <h1>{{ $reportMeta['title'] }}</h1>
                    <p>{{ $reportMeta['subtitle'] }}</p>
                    <span class="inv-period"><i class="far fa-calendar-alt"></i> {{ $startDate }} <span>—</span> {{ $endDate }}</span>
                </div>
            </div>
        </div>
        <div class="inv-actions">
            <button type="button" class="btn btn-outline-primary" onclick="exportInventoryReport()"><i class="fas fa-file-excel ml-1"></i> تصدير Excel</button>
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fas fa-print ml-1"></i> طباعة</button>
            <a href="{{ route('home_reports') }}" class="btn btn-light"><i class="fas fa-arrow-right ml-1"></i> التقارير</a>
        </div>
    </header>

    <nav class="inv-tabs" aria-label="تقارير المخزون">
        <a class="inv-tab {{ $reportType === 'summary' ? 'active' : '' }}" href="{{ $reportRoutes['summary'] }}"><i class="fas fa-boxes"></i> أرصدة المنتجات</a>
        <a class="inv-tab {{ $reportType === 'movements' ? 'active' : '' }}" href="{{ $reportRoutes['movements'] }}"><i class="fas fa-exchange-alt"></i> سجل الحركة</a>
        <a class="inv-tab {{ $reportType === 'low_stock' ? 'active' : '' }}" href="{{ $reportRoutes['low_stock'] }}"><i class="fas fa-exclamation-triangle"></i> حد إعادة الطلب</a>
    </nav>

    <div class="inv-notice"><i class="fas fa-info-circle ml-1"></i> الأرصدة التاريخية محسوبة عكسيًا من الكمية الحالية وحركات الشراء والبيع والمرتجعات المسجلة. حد التنبيه الحالي {{ $threshold }} وحدات.</div>

    @if ($reportType === 'summary')
        <section class="inv-kpis" aria-label="ملخص الأرصدة">
            <div class="inv-kpi"><span class="inv-kpi-label"><i class="fas fa-cubes"></i> المنتجات</span><strong class="inv-kpi-value">{{ number_format($stats->product_count) }}</strong><small>جميع المنتجات المسجلة</small></div>
            <div class="inv-kpi"><span class="inv-kpi-label"><i class="fas fa-box-open"></i> رصيد بداية الفترة</span><strong class="inv-kpi-value">{{ number_format($stats->opening_quantity) }}</strong><small>وحدة</small></div>
            <div class="inv-kpi"><span class="inv-kpi-label"><i class="fas fa-arrow-down"></i> الوارد خلال الفترة</span><strong class="inv-kpi-value inv-positive">{{ number_format($stats->received_quantity) }}</strong><small>مشتريات ومرتجعات</small></div>
            <div class="inv-kpi"><span class="inv-kpi-label"><i class="fas fa-arrow-up"></i> المنصرف خلال الفترة</span><strong class="inv-kpi-value inv-negative">{{ number_format($stats->sold_quantity) }}</strong><small>وحدات مباعة</small></div>
            <div class="inv-kpi"><span class="inv-kpi-label"><i class="fas fa-warehouse"></i> رصيد نهاية الفترة</span><strong class="inv-kpi-value">{{ number_format($stats->closing_quantity) }}</strong><small>وحدة</small></div>
        </section>

        <section class="inv-card">
            <div class="inv-card-head"><div><h2>رصيد المخزون حسب المنتج</h2><p>صافي الوارد = المشتريات − مرتجعات المورد + مرتجعات المبيعات.</p></div><input class="form-control inv-search" type="search" data-table-search placeholder="ابحث باسم المنتج أو التصنيف"></div>
            <div class="inv-table-wrap">
                <table class="inv-table table" id="inventoryTable">
                    <thead><tr><th>المنتج</th><th>التصنيف</th><th>رصيد البداية</th><th>مشتريات</th><th>مرتجع للمورد</th><th>مرتجع من عميل</th><th>صافي الوارد</th><th>مبيعات</th><th>رصيد النهاية</th></tr></thead>
                    <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td><div class="inv-product"><strong>{{ $product->product_name }}</strong><small>كود المنتج: {{ $product->product_id }}</small></div></td>
                            <td>{{ $product->category_name }}</td>
                            <td class="inv-number">{{ number_format($product->opening_quantity) }}</td>
                            <td class="inv-number inv-positive">+{{ number_format($product->purchased_quantity) }}</td>
                            <td class="inv-number inv-negative">−{{ number_format($product->purchase_returned_quantity) }}</td>
                            <td class="inv-number inv-positive">+{{ number_format($product->returned_quantity) }}</td>
                            <td class="inv-number inv-positive">+{{ number_format($product->received_quantity) }}</td>
                            <td class="inv-number inv-negative">−{{ number_format($product->sold_quantity) }}</td>
                            <td class="inv-number">{{ number_format($product->closing_quantity) }}</td>
                        </tr>
                    @empty
                        <tr><td class="inv-empty" colspan="9">لا توجد منتجات لعرضها.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        @if ($chartProducts->isNotEmpty())
            <section class="inv-chart"><h2>الأصناف الأعلى حركة خلال الفترة</h2><canvas id="inventoryChart"></canvas></section>
        @endif

    @elseif ($reportType === 'movements')
        <section class="inv-kpis" aria-label="ملخص حركة المخزون">
            <div class="inv-kpi"><span class="inv-kpi-label"><i class="fas fa-truck"></i> مشتريات مستلمة</span><strong class="inv-kpi-value inv-positive">{{ number_format($stats->purchased_quantity) }}</strong><small>وحدة واردة</small></div>
            <div class="inv-kpi"><span class="inv-kpi-label"><i class="fas fa-undo"></i> مرتجعات المبيعات</span><strong class="inv-kpi-value inv-positive">{{ number_format($stats->returned_quantity) }}</strong><small>وحدة عادت للمخزون</small></div>
            <div class="inv-kpi"><span class="inv-kpi-label"><i class="fas fa-truck-loading"></i> مرتجعات للمورد</span><strong class="inv-kpi-value inv-negative">{{ number_format($stats->purchase_returned_quantity) }}</strong><small>وحدة خرجت من المخزون</small></div>
            <div class="inv-kpi"><span class="inv-kpi-label"><i class="fas fa-cash-register"></i> مبيعات مصروفة</span><strong class="inv-kpi-value inv-negative">{{ number_format($stats->sold_quantity) }}</strong><small>وحدة منصرفة</small></div>
            <div class="inv-kpi"><span class="inv-kpi-label"><i class="fas fa-exchange-alt"></i> صافي التغير</span><strong class="inv-kpi-value">{{ number_format($stats->received_quantity - $stats->sold_quantity) }}</strong><small>الوارد ناقص المنصرف</small></div>
            <div class="inv-kpi"><span class="inv-kpi-label"><i class="fas fa-list"></i> إجمالي الحركات</span><strong class="inv-kpi-value">{{ number_format($movements->total()) }}</strong><small>حركة مسجلة بالفترة</small></div>
        </section>
        <section class="inv-card">
            <div class="inv-card-head"><div><h2>سجل حركات المخزون</h2><p>التاريخ المرجعي هو تاريخ فاتورة الشراء أو البيع أو تسجيل المرتجع.</p></div><input class="form-control inv-search" type="search" data-table-search placeholder="ابحث بالمنتج أو نوع الحركة أو رقم الفاتورة"></div>
            <div class="inv-table-wrap">
                <table class="inv-table table" id="inventoryTable">
                    <thead><tr><th>التاريخ</th><th>نوع الحركة</th><th>المنتج</th><th>المرجع</th><th>وارد</th><th>منصرف</th></tr></thead>
                    <tbody>
                    @forelse ($movements as $movement)
                        @php $badge = $movement->movement_type === 'استلام شراء' ? 'purchase' : ($movement->movement_type === 'صرف مبيعات' ? 'sale' : ($movement->movement_type === 'مرتجع مشتريات' ? 'return-out' : 'return')); @endphp
                        <tr>
                            <td class="inv-number">{{ $movement->movement_date }}</td>
                            <td><span class="inv-badge {{ $badge }}">{{ $movement->movement_type }}</span></td>
                            <td><div class="inv-product"><strong>{{ $movement->product_name }}</strong><small>كود المنتج: {{ $movement->product_id }}</small></div></td>
                            <td class="inv-number">#{{ $movement->reference }}</td>
                            <td class="inv-number {{ $movement->quantity_in ? 'inv-positive' : 'inv-neutral' }}">{{ $movement->quantity_in ? '+'.number_format($movement->quantity_in) : '—' }}</td>
                            <td class="inv-number {{ $movement->quantity_out ? 'inv-negative' : 'inv-neutral' }}">{{ $movement->quantity_out ? '−'.number_format($movement->quantity_out) : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td class="inv-empty" colspan="6">لا توجد حركات مخزون خلال الفترة المحددة.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if ($movements->hasPages())
                <div class="inv-pagination"><span>عرض {{ $movements->firstItem() }}–{{ $movements->lastItem() }} من {{ number_format($movements->total()) }} حركة</span>{{ $movements->links() }}</div>
            @endif
        </section>
        <section class="inv-chart"><h2>مقارنة الوارد والمنصرف</h2><canvas id="inventoryChart"></canvas></section>

    @else
        <section class="inv-kpis" aria-label="ملخص المنتجات منخفضة المخزون">
            <div class="inv-kpi"><span class="inv-kpi-label"><i class="fas fa-triangle-exclamation"></i> منتجات تحتاج متابعة</span><strong class="inv-kpi-value">{{ number_format($stats->low_stock_count) }}</strong><small>رصيدها {{ $threshold }} وحدات أو أقل</small></div>
            <div class="inv-kpi"><span class="inv-kpi-label"><i class="fas fa-times-circle"></i> منتجات نفد مخزونها</span><strong class="inv-kpi-value inv-negative">{{ number_format($stats->out_of_stock_count) }}</strong><small>رصيد صفر أو أقل</small></div>
            <div class="inv-kpi"><span class="inv-kpi-label"><i class="fas fa-shopping-cart"></i> كمية مقترحة لإعادة الطلب</span><strong class="inv-kpi-value">{{ number_format($products->sum('reorder_quantity')) }}</strong><small>للوصول إلى حد {{ $threshold }} وحدات لكل صنف</small></div>
            <div class="inv-kpi"><span class="inv-kpi-label"><i class="fas fa-boxes"></i> إجمالي المنتجات</span><strong class="inv-kpi-value">{{ number_format($stats->product_count) }}</strong><small>في سجل المخزون</small></div>
            <div class="inv-kpi"><span class="inv-kpi-label"><i class="fas fa-warehouse"></i> رصيد نهاية الفترة</span><strong class="inv-kpi-value">{{ number_format($stats->closing_quantity) }}</strong><small>لكل المنتجات</small></div>
        </section>
        <section class="inv-card">
            <div class="inv-card-head"><div><h2>الأصناف عند حد إعادة الطلب</h2><p>الحد موحد حاليًا عند {{ $threshold }} وحدات للصنف.</p></div><input class="form-control inv-search" type="search" data-table-search placeholder="ابحث باسم المنتج أو التصنيف"></div>
            <div class="inv-table-wrap">
                <table class="inv-table table" id="inventoryTable">
                    <thead><tr><th>المنتج</th><th>التصنيف</th><th>رصيد بداية الفترة</th><th>الوارد</th><th>المنصرف</th><th>رصيد النهاية</th><th>الكمية المقترحة للطلب</th><th>الحالة</th></tr></thead>
                    <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td><div class="inv-product"><strong>{{ $product->product_name }}</strong><small>كود المنتج: {{ $product->product_id }}</small></div></td>
                            <td>{{ $product->category_name }}</td>
                            <td class="inv-number">{{ number_format($product->opening_quantity) }}</td>
                            <td class="inv-number inv-positive">+{{ number_format($product->received_quantity) }}</td>
                            <td class="inv-number inv-negative">−{{ number_format($product->sold_quantity) }}</td>
                            <td class="inv-number">{{ number_format($product->closing_quantity) }}</td>
                            <td class="inv-number">{{ number_format($product->reorder_quantity) }}</td>
                            <td><span class="inv-badge {{ $product->closing_quantity <= 0 ? 'out' : 'low' }}">{{ $product->closing_quantity <= 0 ? 'نفد المخزون' : 'مخزون منخفض' }}</span></td>
                        </tr>
                    @empty
                        <tr><td class="inv-empty" colspan="8"><i class="fas fa-check-circle ml-1"></i> لا توجد منتجات تحت حد إعادة الطلب بنهاية الفترة.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        @if ($chartProducts->isNotEmpty())
            <section class="inv-chart"><h2>الأصناف الأقرب إلى النفاد</h2><canvas id="inventoryChart"></canvas></section>
        @endif
    @endif

    <p class="inv-footer-note">تُعرض الكميات كوحدات صحيحة، ويشمل التقرير الحركات النشطة فقط بعد استبعاد الفواتير والتفاصيل المحذوفة.</p>
</div>
@endsection

@section('js')
<script src="{{ URL::asset('assets/js/chart.js') }}"></script>
<script src="{{ URL::asset('assets/js/xlsx.full.min.js') }}"></script>
<script>
    document.querySelector('[data-table-search]')?.addEventListener('input', function () {
        const query = this.value.trim().toLocaleLowerCase();
        document.querySelectorAll('#inventoryTable tbody tr').forEach(row => {
            row.hidden = !row.textContent.toLocaleLowerCase().includes(query);
        });
    });

    window.exportInventoryReport = function () {
        const table = document.getElementById('inventoryTable');
        if (!table) return;
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, XLSX.utils.table_to_sheet(table), 'تقرير المخزون');
        XLSX.writeFile(workbook, 'تقرير_المخزون_{{ $reportType }}_{{ $startDate }}_{{ $endDate }}.xlsx');
    };

    @if (in_array($reportType, ['summary', 'low_stock'], true) && $chartProducts->isNotEmpty())
        const productLabels = @json($chartProducts->pluck('product_name'));
        @if ($reportType === 'summary')
            const openingStocks = @json($chartProducts->pluck('opening_quantity'));
            const closingStocks = @json($chartProducts->pluck('closing_quantity'));
            new Chart(document.getElementById('inventoryChart').getContext('2d'), {
                type: 'bar',
                data: { labels: productLabels, datasets: [
                    { label: 'رصيد البداية', data: openingStocks, backgroundColor: 'rgba(104, 128, 162, .55)', borderRadius: 5 },
                    { label: 'رصيد النهاية', data: closingStocks, backgroundColor: 'rgba(23, 105, 224, .75)', borderRadius: 5 }
                ] },
                options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
            });
        @else
            const closingStocks = @json($chartProducts->pluck('closing_quantity'));
            new Chart(document.getElementById('inventoryChart').getContext('2d'), {
                type: 'bar',
                data: { labels: productLabels, datasets: [
                    { label: 'رصيد نهاية الفترة', data: closingStocks, backgroundColor: closingStocks.map(value => value <= 0 ? 'rgba(215, 78, 85, .75)' : 'rgba(245, 169, 56, .75)'), borderRadius: 5 },
                    { label: 'حد إعادة الطلب', data: productLabels.map(() => {{ $threshold }}), type: 'line', borderColor: '#d74e55', pointRadius: 0, borderDash: [6, 5] }
                ] },
                options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, scales: { x: { beginAtZero: true } } }
            });
        @endif
    @elseif ($reportType === 'movements')
        new Chart(document.getElementById('inventoryChart').getContext('2d'), {
            type: 'bar',
            data: { labels: ['مشتريات', 'مرتجعات المبيعات', 'مرتجعات المورد', 'مبيعات'], datasets: [{ label: 'كمية الوحدات', data: [{{ $stats->purchased_quantity }}, {{ $stats->returned_quantity }}, {{ $stats->purchase_returned_quantity }}, {{ $stats->sold_quantity }}], backgroundColor: ['rgba(19, 134, 92, .72)', 'rgba(128, 88, 186, .72)', 'rgba(215, 78, 85, .72)', 'rgba(215, 78, 85, .5)'], borderRadius: 6 }] },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
        });
    @endif
</script>
@endsection
