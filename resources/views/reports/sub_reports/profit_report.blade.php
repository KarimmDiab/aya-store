@extends('layouts.master3')
<title>معرض أيه - تقارير الأرباح</title>

@php
    $comparison = $reportType === 'comparison';
    $title = $comparison ? 'مقارنة الأرباح' : 'تقدير الربح الإجمالي';
    $subtitle = $comparison
        ? 'مقارنة الفترة المختارة بالفترة السابقة المساوية لها في عدد الأيام.'
        : 'صافي المبيعات وتكلفة البضاعة المباعة والربح الإجمالي التقديري.';
    $reportRoutes = [
        'summary' => route('profit_report_summary', ['startDate' => $startDate, 'endDate' => $endDate]),
        'comparison' => route('profit_report_comparison', ['startDate' => $startDate, 'endDate' => $endDate]),
    ];
    if ($comparison) {
        $profitChange = $currentStats->gross_profit - $previousStats->gross_profit;
        $netProfitChange = $currentStats->net_profit - $previousStats->net_profit;
        $netProfitChangePercent = abs($previousStats->net_profit) > .0001
            ? ($netProfitChange / abs($previousStats->net_profit)) * 100
            : null;
    } else {
        $chartProducts = $products->where('has_cost', true)->sortByDesc('gross_profit')->take(10)->values();
    }
@endphp

<style>
    .pf-page { --pf-ink:#18243b; --pf-muted:#77839a; --pf-line:#e7ebf2; --pf-green:#14936d; --pf-purple:#8058ba; direction:rtl; color:var(--pf-ink); }
    .pf-hero { display:flex; justify-content:space-between; align-items:flex-start; gap:18px; padding:24px 26px; margin-bottom:20px; border:1px solid var(--pf-line); border-radius:18px; background:linear-gradient(120deg,#fff 45%,#f0fbf6); box-shadow:0 8px 24px rgba(20,42,82,.05); }
    .pf-title { display:flex; gap:14px; align-items:flex-start; }
    .pf-icon { display:grid; place-items:center; width:50px; height:50px; flex:0 0 50px; border-radius:15px; color:#fff; background:linear-gradient(145deg,#14936d,#48c498); font-size:20px; }
    .pf-eyebrow { margin-bottom:4px; color:var(--pf-green); font-size:12px; font-weight:800; }
    .pf-title h1 { margin:0; color:var(--pf-ink); font-size:24px; font-weight:800; }
    .pf-title p { margin:6px 0 0; color:var(--pf-muted); font-size:14px; }
    .pf-period { display:inline-flex; align-items:center; gap:7px; margin-top:12px; padding:6px 11px; border-radius:999px; background:#e9f8f1; color:#177a5c; font-size:12px; font-weight:700; }
    .pf-actions { display:flex; flex-wrap:wrap; gap:8px; }
    .pf-actions .btn { min-height:40px; border-radius:10px; font-weight:700; }
    .pf-tabs { display:flex; gap:8px; overflow:auto; margin-bottom:18px; padding-bottom:5px; }
    .pf-tab { display:inline-flex; align-items:center; gap:8px; padding:10px 14px; white-space:nowrap; border:1px solid var(--pf-line); border-radius:11px; background:#fff; color:#59677f; font-size:13px; font-weight:700; text-decoration:none; }
    .pf-tab:hover { color:var(--pf-green); border-color:#bee8d6; background:#f8fffb; text-decoration:none; }
    .pf-tab.active { color:#fff; border-color:var(--pf-green); background:var(--pf-green); box-shadow:0 5px 14px rgba(20,147,109,.2); }
    .pf-note { padding:11px 14px; margin-bottom:18px; border:1px solid #d5ecdf; border-radius:11px; background:#f7fcf9; color:#536b5e; font-size:13px; line-height:1.7; }
    .pf-kpis { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; margin-bottom:18px; }
    .pf-kpi { min-width:0; padding:15px 16px; border:1px solid var(--pf-line); border-radius:14px; background:#fff; box-shadow:0 4px 14px rgba(20,42,82,.035); }
    .pf-label { display:flex; align-items:center; gap:8px; color:var(--pf-muted); font-size:12px; font-weight:700; }
    .pf-label i { color:var(--pf-green); }
    .pf-value { display:block; overflow:hidden; margin-top:8px; color:var(--pf-ink); font-size:21px; font-weight:800; text-overflow:ellipsis; white-space:nowrap; }
    .pf-kpi small { color:var(--pf-muted); font-size:11px; }
    .pf-profit { color:#13865c !important; }
    .pf-loss { color:#bd414a !important; }
    .pf-card,.pf-chart { overflow:hidden; border:1px solid var(--pf-line); border-radius:15px; background:#fff; box-shadow:0 6px 20px rgba(20,42,82,.04); }
    .pf-card-head { display:flex; justify-content:space-between; align-items:center; gap:14px; padding:16px 18px; border-bottom:1px solid var(--pf-line); }
    .pf-card-head h2,.pf-chart h2 { margin:0; color:var(--pf-ink); font-size:16px; font-weight:800; }
    .pf-card-head p { margin:4px 0 0; color:var(--pf-muted); font-size:12px; }
    .pf-search { width:min(100%,320px); }
    .pf-table-wrap { overflow:auto; }
    .pf-table { width:100%; min-width:810px; margin:0; border-collapse:separate; border-spacing:0; }
    .pf-table thead th { position:sticky; top:0; z-index:1; padding:12px 13px; border:0; background:#f2f9f5; color:#587267; font-size:12px; font-weight:800; white-space:nowrap; }
    .pf-table tbody td { padding:12px 13px; border-top:1px solid #edf2ef; color:#35435a; font-size:13px; vertical-align:middle; }
    .pf-table tbody tr:hover { background:#f9fdfb; }
    .pf-name { display:flex; flex-direction:column; gap:2px; min-width:145px; }
    .pf-name strong { color:var(--pf-ink); font-weight:800; }
    .pf-name small { color:#929db0; font-size:11px; }
    .pf-num { font-variant-numeric:tabular-nums; font-weight:700; white-space:nowrap; }
    .pf-unknown { display:inline-block; padding:4px 8px; border-radius:999px; background:#fff4dd; color:#9a650a; font-size:11px; font-weight:700; white-space:nowrap; }
    .pf-empty { padding:42px 16px !important; color:var(--pf-muted) !important; text-align:center; }
    .pf-chart { height:310px; padding:18px 20px; margin-top:18px; }
    .pf-chart h2 { margin-bottom:12px; }
    .pf-comparison { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; margin-bottom:18px; }
    .pf-period-card { padding:18px; border:1px solid var(--pf-line); border-radius:15px; background:#fff; }
    .pf-period-card.current { border-color:#bce8d3; background:linear-gradient(145deg,#fff,#f4fcf7); }
    .pf-period-card h2 { margin:0 0 12px; font-size:15px; font-weight:800; }
    .pf-period-card .pf-period-date { color:var(--pf-muted); font-size:12px; margin-bottom:15px; }
    .pf-period-metric { display:flex; justify-content:space-between; gap:12px; padding:8px 0; border-top:1px solid #edf1f4; font-size:13px; }
    .pf-period-metric strong { font-variant-numeric:tabular-nums; }
    .pf-delta { display:inline-block; margin-top:12px; padding:7px 10px; border-radius:999px; background:#e9f8f1; color:#177a5c; font-size:12px; font-weight:800; }
    .pf-delta.down { background:#fff0f0; color:#bd414a; }
    .pf-foot { margin-top:13px; color:var(--pf-muted); font-size:11px; }
    @media (max-width:1050px) { .pf-kpis { grid-template-columns:repeat(3,minmax(0,1fr)); } }
    @media (max-width:680px) { .pf-hero { flex-direction:column; padding:18px; } .pf-title h1 { font-size:20px; } .pf-kpis { grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px; } .pf-kpi { padding:12px; } .pf-value { font-size:19px; } .pf-card-head { align-items:flex-start; flex-direction:column; } .pf-search { width:100%; } .pf-chart { height:260px; } .pf-comparison { grid-template-columns:1fr; } }
    @media print { .pf-actions,.pf-tabs,.pf-search { display:none !important; } .pf-hero,.pf-kpi,.pf-card,.pf-chart,.pf-period-card { box-shadow:none; break-inside:avoid; } .pf-table { min-width:0; } .pf-table thead th { position:static; } body { background:#fff !important; } }
</style>

@section('content')
<div class="container-fluid pf-page">
    <header class="pf-hero">
        <div class="pf-title">
            <span class="pf-icon"><i class="fas fa-coins"></i></span>
            <div>
                <div class="pf-eyebrow">التقارير المالية / الأرباح</div>
                <h1>{{ $title }}</h1>
                <p>{{ $subtitle }}</p>
                <span class="pf-period"><i class="far fa-calendar-alt"></i> {{ $startDate }} <span>—</span> {{ $endDate }}</span>
            </div>
        </div>
        <div class="pf-actions">
            <button type="button" class="btn btn-outline-primary" onclick="exportProfitReport()"><i class="fas fa-file-excel ml-1"></i> تصدير Excel</button>
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fas fa-print ml-1"></i> طباعة</button>
            <a href="{{ route('home_reports') }}" class="btn btn-light"><i class="fas fa-arrow-right ml-1"></i> التقارير</a>
        </div>
    </header>

    <nav class="pf-tabs" aria-label="تقارير الأرباح">
        <a class="pf-tab {{ ! $comparison ? 'active' : '' }}" href="{{ $reportRoutes['summary'] }}"><i class="fas fa-coins"></i> تقدير الربح</a>
        <a class="pf-tab {{ $comparison ? 'active' : '' }}" href="{{ $reportRoutes['comparison'] }}"><i class="fas fa-balance-scale"></i> مقارنة الفترات</a>
    </nav>

    <div class="pf-note"><i class="fas fa-info-circle ml-1"></i> الربح الإجمالي تقديري، وصافي الربح يساوي الربح الإجمالي ناقص المصروفات المسجلة خلال الفترة، قبل الضرائب. يستبعد متوسط تكلفة الصنف قيمة وكميات مرتجعات المورد خلال الفترة. الأصناف التي لا تتوفر لها تكلفة مستبعدة من الربح وتظهر مبيعاتها كقيمة غير مسعّرة.</div>

    @if (! $comparison)
        <section class="pf-kpis">
            <div class="pf-kpi"><span class="pf-label"><i class="fas fa-money-bill-wave"></i> صافي المبيعات</span><strong class="pf-value">{{ number_format($stats->sales_amount, 2) }}</strong><small>ج.م بعد الخصومات والمرتجعات</small></div>
            <div class="pf-kpi"><span class="pf-label"><i class="fas fa-box-open"></i> تكلفة البضاعة المسعّرة</span><strong class="pf-value">{{ number_format($stats->cost_of_goods, 2) }}</strong><small>ج.م تقديريًا</small></div>
            <div class="pf-kpi"><span class="pf-label"><i class="fas fa-undo"></i> مرتجعات المشتريات</span><strong class="pf-value">{{ number_format($stats->purchase_return_amount, 2) }}</strong><small>{{ number_format($stats->purchase_return_quantity) }} وحدة · ج.م</small></div>
            <div class="pf-kpi"><span class="pf-label"><i class="fas fa-chart-line"></i> الربح الإجمالي التقديري</span><strong class="pf-value {{ $stats->gross_profit >= 0 ? 'pf-profit' : 'pf-loss' }}">{{ number_format($stats->gross_profit, 2) }}</strong><small>ج.م للأصناف ذات التكلفة</small></div>
            <div class="pf-kpi"><span class="pf-label"><i class="fas fa-receipt"></i> المصروفات المسجلة</span><strong class="pf-value">{{ number_format($stats->expenses_amount, 2) }}</strong><small>ج.م خلال الفترة</small></div>
            <div class="pf-kpi"><span class="pf-label"><i class="fas fa-wallet"></i> صافي الربح بعد المصروفات</span><strong class="pf-value {{ $stats->net_profit >= 0 ? 'pf-profit' : 'pf-loss' }}">{{ number_format($stats->net_profit, 2) }}</strong><small>ج.م قبل الضرائب</small></div>
            <div class="pf-kpi"><span class="pf-label"><i class="fas fa-percent"></i> هامش الربح</span><strong class="pf-value">{{ number_format($stats->gross_margin, 2) }}%</strong><small>من مبيعات الأصناف المسعّرة</small></div>
            <div class="pf-kpi"><span class="pf-label"><i class="fas fa-circle-question"></i> مبيعات بلا تكلفة</span><strong class="pf-value">{{ number_format($stats->unpriced_sales, 2) }}</strong><small>{{ number_format($stats->uncosted_product_count) }} أصناف · ج.م</small></div>
        </section>

        <section class="pf-card">
            <div class="pf-card-head"><div><h2>تقدير الربح حسب المنتج</h2><p>تكلفة الأصناف غير المتاحة لا تُفترض صفرًا؛ تظهر كغير مسعّرة.</p></div><input type="search" class="form-control pf-search" data-product-search placeholder="ابحث باسم المنتج أو كوده"></div>
            <div class="pf-table-wrap"><table class="pf-table table" id="profitTable">
                <thead><tr><th>المنتج</th><th>الكمية المباعة</th><th>صافي المبيعات</th><th>متوسط تكلفة الوحدة</th><th>تكلفة البضاعة</th><th>الربح الإجمالي</th><th>مصدر التكلفة</th></tr></thead>
                <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td><div class="pf-name"><strong>{{ $product->product_name }}</strong><small>كود المنتج: {{ $product->product_id }}</small></div></td>
                        <td class="pf-num">{{ number_format($product->sold_quantity - $product->returned_quantity) }}</td>
                        <td class="pf-num">{{ number_format($product->net_sales, 2) }} ج.م</td>
                        <td class="pf-num">{{ $product->unit_cost === null ? '—' : number_format($product->unit_cost, 2).' ج.م' }}</td>
                        <td class="pf-num">{{ $product->has_cost ? number_format($product->cost_of_goods, 2).' ج.م' : '—' }}</td>
                        <td class="pf-num {{ $product->gross_profit === null ? '' : ($product->gross_profit >= 0 ? 'pf-profit' : 'pf-loss') }}">{{ $product->gross_profit === null ? '—' : number_format($product->gross_profit, 2).' ج.م' }}</td>
                        <td>
                            @if ($product->cost_source)
                                {{ $product->cost_source }}
                            @else
                                <span class="pf-unknown">لا توجد تكلفة</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="pf-empty">لا توجد مبيعات أو مرتجعات خلال الفترة المحددة.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </section>
        @if ($chartProducts->isNotEmpty())<section class="pf-chart"><h2>أعلى المنتجات ربحًا تقديريًا</h2><canvas id="profitChart"></canvas></section>@endif

    @else
        @php
            $profitChangeClass = $netProfitChange >= 0 ? '' : 'down';
            $profitChangeText = $netProfitChange >= 0 ? 'زيادة' : 'انخفاض';
        @endphp
        <section class="pf-kpis">
            <div class="pf-kpi"><span class="pf-label"><i class="fas fa-wallet"></i> صافي ربح الفترة الحالية</span><strong class="pf-value {{ $currentStats->net_profit >= 0 ? 'pf-profit' : 'pf-loss' }}">{{ number_format($currentStats->net_profit, 2) }}</strong><small>ج.م بعد المصروفات</small></div>
            <div class="pf-kpi"><span class="pf-label"><i class="fas fa-history"></i> صافي ربح الفترة السابقة</span><strong class="pf-value {{ $previousStats->net_profit >= 0 ? 'pf-profit' : 'pf-loss' }}">{{ number_format($previousStats->net_profit, 2) }}</strong><small>ج.م بعد المصروفات</small></div>
            <div class="pf-kpi"><span class="pf-label"><i class="fas fa-arrows-alt-v"></i> التغير في صافي الربح</span><strong class="pf-value {{ $netProfitChange >= 0 ? 'pf-profit' : 'pf-loss' }}">{{ number_format($netProfitChange, 2) }}</strong><small>ج.م · {{ $profitChangeText }} {{ $netProfitChangePercent === null ? '' : number_format(abs($netProfitChangePercent), 2).'%' }}</small></div>
            <div class="pf-kpi"><span class="pf-label"><i class="fas fa-percent"></i> هامش الفترة الحالية</span><strong class="pf-value">{{ number_format($currentStats->gross_margin, 2) }}%</strong><small>مقابل {{ number_format($previousStats->gross_margin, 2) }}% سابقًا</small></div>
            <div class="pf-kpi"><span class="pf-label"><i class="fas fa-circle-question"></i> المبيعات بلا تكلفة</span><strong class="pf-value">{{ number_format($currentStats->unpriced_sales, 2) }}</strong><small>ج.م · الحالية</small></div>
        </section>

        <section class="pf-comparison">
            <article class="pf-period-card current">
                <h2><i class="fas fa-calendar-check ml-1"></i> الفترة الحالية</h2>
                <div class="pf-period-date">{{ $startDate }} — {{ $endDate }}</div>
                <div class="pf-period-metric"><span>صافي المبيعات</span><strong>{{ number_format($currentStats->sales_amount, 2) }} ج.م</strong></div>
                <div class="pf-period-metric"><span>تكلفة البضاعة المسعّرة</span><strong>{{ number_format($currentStats->cost_of_goods, 2) }} ج.م</strong></div>
                <div class="pf-period-metric"><span>مرتجعات المشتريات</span><strong>{{ number_format($currentStats->purchase_return_amount, 2) }} ج.م</strong></div>
                <div class="pf-period-metric"><span>الربح الإجمالي التقديري</span><strong class="{{ $currentStats->gross_profit >= 0 ? 'pf-profit' : 'pf-loss' }}">{{ number_format($currentStats->gross_profit, 2) }} ج.م</strong></div>
                <div class="pf-period-metric"><span>المصروفات المسجلة</span><strong>{{ number_format($currentStats->expenses_amount, 2) }} ج.م</strong></div>
                <div class="pf-period-metric"><span>صافي الربح بعد المصروفات</span><strong class="{{ $currentStats->net_profit >= 0 ? 'pf-profit' : 'pf-loss' }}">{{ number_format($currentStats->net_profit, 2) }} ج.م</strong></div>
                <span class="pf-delta {{ $profitChangeClass }}">{{ $profitChangeText }} في صافي الربح {{ number_format(abs($netProfitChange), 2) }} ج.م{{ $netProfitChangePercent === null ? '' : ' ('.number_format(abs($netProfitChangePercent), 2).'%)' }}</span>
            </article>
            <article class="pf-period-card">
                <h2><i class="fas fa-calendar-minus ml-1"></i> الفترة السابقة المساوية</h2>
                <div class="pf-period-date">{{ $previousStartDate }} — {{ $previousEndDate }}</div>
                <div class="pf-period-metric"><span>صافي المبيعات</span><strong>{{ number_format($previousStats->sales_amount, 2) }} ج.م</strong></div>
                <div class="pf-period-metric"><span>تكلفة البضاعة المسعّرة</span><strong>{{ number_format($previousStats->cost_of_goods, 2) }} ج.م</strong></div>
                <div class="pf-period-metric"><span>مرتجعات المشتريات</span><strong>{{ number_format($previousStats->purchase_return_amount, 2) }} ج.م</strong></div>
                <div class="pf-period-metric"><span>الربح الإجمالي التقديري</span><strong>{{ number_format($previousStats->gross_profit, 2) }} ج.م</strong></div>
                <div class="pf-period-metric"><span>المصروفات المسجلة</span><strong>{{ number_format($previousStats->expenses_amount, 2) }} ج.م</strong></div>
                <div class="pf-period-metric"><span>صافي الربح بعد المصروفات</span><strong class="{{ $previousStats->net_profit >= 0 ? 'pf-profit' : 'pf-loss' }}">{{ number_format($previousStats->net_profit, 2) }} ج.م</strong></div>
                <div class="pf-period-metric"><span>مبيعات بلا تكلفة</span><strong>{{ number_format($previousStats->unpriced_sales, 2) }} ج.م</strong></div>
            </article>
        </section>
        <section class="pf-card">
            <div class="pf-card-head"><div><h2>جدول مقارنة الفترتين</h2><p>الفترة السابقة لها نفس عدد أيام الفترة المختارة.</p></div></div>
            <div class="pf-table-wrap"><table class="pf-table table" id="profitComparisonTable">
                <thead><tr><th>المؤشر</th><th>الفترة السابقة ({{ $previousStartDate }} إلى {{ $previousEndDate }})</th><th>الفترة الحالية ({{ $startDate }} إلى {{ $endDate }})</th><th>التغير</th></tr></thead>
                <tbody>
                    <tr><td>صافي المبيعات</td><td class="pf-num">{{ number_format($previousStats->sales_amount, 2) }} ج.م</td><td class="pf-num">{{ number_format($currentStats->sales_amount, 2) }} ج.م</td><td class="pf-num">{{ number_format($currentStats->sales_amount - $previousStats->sales_amount, 2) }} ج.م</td></tr>
                    <tr><td>تكلفة البضاعة المسعّرة</td><td class="pf-num">{{ number_format($previousStats->cost_of_goods, 2) }} ج.م</td><td class="pf-num">{{ number_format($currentStats->cost_of_goods, 2) }} ج.م</td><td class="pf-num">{{ number_format($currentStats->cost_of_goods - $previousStats->cost_of_goods, 2) }} ج.م</td></tr>
                    <tr><td>مرتجعات المشتريات</td><td class="pf-num">{{ number_format($previousStats->purchase_return_amount, 2) }} ج.م</td><td class="pf-num">{{ number_format($currentStats->purchase_return_amount, 2) }} ج.م</td><td class="pf-num">{{ number_format($currentStats->purchase_return_amount - $previousStats->purchase_return_amount, 2) }} ج.م</td></tr>
                    <tr><td>الربح الإجمالي التقديري</td><td class="pf-num">{{ number_format($previousStats->gross_profit, 2) }} ج.م</td><td class="pf-num">{{ number_format($currentStats->gross_profit, 2) }} ج.م</td><td class="pf-num {{ $profitChange >= 0 ? 'pf-profit' : 'pf-loss' }}">{{ number_format($profitChange, 2) }} ج.م</td></tr>
                    <tr><td>المصروفات المسجلة</td><td class="pf-num">{{ number_format($previousStats->expenses_amount, 2) }} ج.م</td><td class="pf-num">{{ number_format($currentStats->expenses_amount, 2) }} ج.م</td><td class="pf-num">{{ number_format($currentStats->expenses_amount - $previousStats->expenses_amount, 2) }} ج.م</td></tr>
                    <tr><td>صافي الربح بعد المصروفات</td><td class="pf-num {{ $previousStats->net_profit >= 0 ? 'pf-profit' : 'pf-loss' }}">{{ number_format($previousStats->net_profit, 2) }} ج.م</td><td class="pf-num {{ $currentStats->net_profit >= 0 ? 'pf-profit' : 'pf-loss' }}">{{ number_format($currentStats->net_profit, 2) }} ج.م</td><td class="pf-num {{ $netProfitChange >= 0 ? 'pf-profit' : 'pf-loss' }}">{{ number_format($netProfitChange, 2) }} ج.م</td></tr>
                    <tr><td>مبيعات بلا تكلفة</td><td class="pf-num">{{ number_format($previousStats->unpriced_sales, 2) }} ج.م</td><td class="pf-num">{{ number_format($currentStats->unpriced_sales, 2) }} ج.م</td><td class="pf-num">{{ number_format($currentStats->unpriced_sales - $previousStats->unpriced_sales, 2) }} ج.م</td></tr>
                </tbody>
            </table></div>
        </section>
        <section class="pf-chart"><h2>مقارنة المبيعات والتكلفة والربح</h2><canvas id="profitChart"></canvas></section>
    @endif
    <p class="pf-foot">صافي الربح = الربح الإجمالي التقديري − المصروفات المسجلة في الفترة، ولا يشمل الضرائب أو المصروفات غير المسجلة. تبقى قيمة الأصناف غير المسعّرة خارج إجمالي الربح.</p>
</div>
@endsection

@section('js')
<script src="{{ URL::asset('assets/js/chart.js') }}"></script>
<script src="{{ URL::asset('assets/js/xlsx.full.min.js') }}"></script>
<script>
    document.querySelector('[data-product-search]')?.addEventListener('input', function () {
        const query = this.value.trim().toLocaleLowerCase();
        document.querySelectorAll('#profitTable tbody tr').forEach(row => { row.hidden = !row.textContent.toLocaleLowerCase().includes(query); });
    });
    window.exportProfitReport = function () {
        const table = document.getElementById('profitTable');
        if (table) {
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, XLSX.utils.table_to_sheet(table), 'تقدير الربح');
            XLSX.writeFile(workbook, 'تقرير_الربح_{{ $startDate }}_{{ $endDate }}.xlsx');
            return;
        }
        const comparisonTable = document.getElementById('profitComparisonTable');
        if (comparisonTable) {
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, XLSX.utils.table_to_sheet(comparisonTable), 'مقارنة الأرباح');
            XLSX.writeFile(workbook, 'مقارنة_الأرباح_{{ $startDate }}_{{ $endDate }}.xlsx');
        }
    };

    @if (! $comparison && $chartProducts->isNotEmpty())
        new Chart(document.getElementById('profitChart').getContext('2d'), {
            type: 'bar',
            data: { labels: @json($chartProducts->pluck('product_name')), datasets: [{ label: 'الربح الإجمالي التقديري (ج.م)', data: @json($chartProducts->pluck('gross_profit')), backgroundColor: 'rgba(20,147,109,.72)', borderColor: '#14936d', borderWidth: 1, borderRadius: 5 }] },
            options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', scales: { x: { beginAtZero: true } } }
        });
    @elseif ($comparison)
        new Chart(document.getElementById('profitChart').getContext('2d'), {
            type: 'bar',
            data: { labels: ['الربح الإجمالي', 'المصروفات المسجلة', 'صافي الربح بعد المصروفات'], datasets: [
                { label: 'الفترة السابقة', data: [{{ $previousStats->gross_profit }}, {{ $previousStats->expenses_amount }}, {{ $previousStats->net_profit }}], backgroundColor: 'rgba(104,128,162,.55)', borderRadius: 5 },
                { label: 'الفترة الحالية', data: [{{ $currentStats->gross_profit }}, {{ $currentStats->expenses_amount }}, {{ $currentStats->net_profit }}], backgroundColor: 'rgba(20,147,109,.72)', borderRadius: 5 }
            ] },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
        });
    @endif
</script>
@endsection
