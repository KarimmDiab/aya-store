@extends('layouts.master')
<title>معرض أيه - لوحة التحكم</title>

@section('css')
<style>
    .db-page { --db-ink:#17253f; --db-muted:#77839a; --db-line:#e6ebf3; --db-blue:#3564c8; direction:rtl; color:var(--db-ink); padding-bottom:22px; }
    .db-hero { position:relative; display:flex; justify-content:space-between; align-items:center; gap:20px; padding:27px 30px; margin-bottom:22px; overflow:hidden; border:1px solid rgba(255,255,255,.16); border-radius:20px; color:white; background:radial-gradient(circle at 10% 15%,rgba(255,255,255,.18),transparent 28%),linear-gradient(120deg,#1d3d82,#3565c3 58%,#5e82d7); box-shadow:0 12px 28px rgba(35,75,155,.16); }
    .db-hero:after { content:""; position:absolute; left:36%; top:-105px; width:240px; height:240px; border:1px solid rgba(255,255,255,.11); border-radius:50%; box-shadow:0 0 0 28px rgba(255,255,255,.035),0 0 0 56px rgba(255,255,255,.025); pointer-events:none; }
    .db-hero-copy,.db-hero-actions { position:relative; z-index:1; }
    .db-live { display:inline-flex; align-items:center; gap:7px; margin-bottom:10px; padding:5px 9px; border:1px solid rgba(255,255,255,.2); border-radius:999px; background:rgba(255,255,255,.1); color:rgba(255,255,255,.92); font-size:10px; font-weight:700; }
    .db-live i { color:#8be0bb; font-size:7px; }
    .db-hero h1 { margin:0; color:#fff; font-size:25px; font-weight:800; letter-spacing:-.2px; }
    .db-hero p { margin:7px 0 0; color:rgba(255,255,255,.84); font-size:13px; }
    .db-date { display:inline-flex; align-items:center; gap:8px; margin-top:15px; padding:7px 12px; border:1px solid rgba(255,255,255,.22); border-radius:999px; background:rgba(255,255,255,.1); font-size:12px; font-weight:700; }
    .db-hero-actions { display:flex; flex-wrap:wrap; gap:9px; }
    .db-hero-actions .btn { min-height:42px; padding-inline:14px; border-radius:11px; font-size:12px; font-weight:700; box-shadow:0 3px 9px rgba(20,40,92,.08); }
    .db-hero-actions .btn-light { color:#254a98; }
    .db-section-head { display:flex; justify-content:space-between; align-items:end; gap:12px; margin:24px 2px 12px; }
    .db-section-head h2 { margin:0; color:var(--db-ink); font-size:17px; font-weight:800; letter-spacing:-.15px; }
    .db-section-head p { margin:4px 0 0; color:var(--db-muted); font-size:12px; }
    .db-kpis { display:grid; grid-template-columns:repeat(6,minmax(0,1fr)); gap:12px; }
    .db-kpi { position:relative; min-width:0; padding:16px; overflow:hidden; border:1px solid var(--db-line); border-radius:15px; background:linear-gradient(155deg,#fff,#fcfdff); box-shadow:0 4px 15px rgba(24,46,87,.035); transition:transform .18s ease,box-shadow .18s ease,border-color .18s ease; }
    .db-kpi:hover { transform:translateY(-2px); border-color:#d7e0f2; box-shadow:0 9px 21px rgba(24,46,87,.075); }
    .db-kpi:after { content:""; position:absolute; left:-18px; bottom:-25px; width:78px; height:78px; border-radius:50%; background:var(--db-soft); opacity:.7; }
    .db-kpi-top { display:flex; align-items:center; justify-content:space-between; gap:8px; }
    .db-kpi-label { color:var(--db-muted); font-size:12px; font-weight:700; }
    .db-kpi-icon { display:grid; place-items:center; width:38px; height:38px; flex:0 0 38px; border-radius:12px; color:var(--db-accent); background:var(--db-soft); font-size:15px; }
    .db-kpi-value { display:block; overflow:hidden; margin-top:12px; color:var(--db-ink); font-size:clamp(17px,1.3vw,22px); font-weight:800; font-variant-numeric:tabular-nums; text-overflow:ellipsis; white-space:nowrap; }
    .db-kpi small { display:block; min-height:16px; margin-top:3px; color:var(--db-muted); font-size:11px; line-height:1.45; }
    .db-tone-blue { --db-accent:#3564c8; --db-soft:#edf2ff; }
    .db-tone-green { --db-accent:#148367; --db-soft:#e8f7f1; }
    .db-tone-orange { --db-accent:#d36b24; --db-soft:#fff2e7; }
    .db-tone-red { --db-accent:#c84e58; --db-soft:#fff0f1; }
    .db-tone-violet { --db-accent:#8058ba; --db-soft:#f3edfb; }
    .db-tone-cyan { --db-accent:#1689a4; --db-soft:#e9f8fb; }
    .db-stat-strip { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); margin-top:14px; padding:15px 6px; border:1px solid var(--db-line); border-radius:14px; background:#fff; box-shadow:0 3px 12px rgba(24,46,87,.025); }
    .db-stat { padding:4px 15px; border-inline-start:1px solid var(--db-line); }
    .db-stat:nth-child(4n) { border-inline-start:0; }
    .db-stat strong { display:block; color:var(--db-ink); font-size:18px; font-weight:800; font-variant-numeric:tabular-nums; }
    .db-stat span { color:var(--db-muted); font-size:11px; }
    .db-grid-main { display:grid; grid-template-columns:minmax(0,1.65fr) minmax(300px,.85fr); gap:14px; }
    .db-card { min-width:0; overflow:hidden; border:1px solid var(--db-line); border-radius:16px; background:#fff; box-shadow:0 5px 18px rgba(24,46,87,.035); }
    .db-card-head { display:flex; justify-content:space-between; align-items:center; gap:10px; min-height:69px; padding:15px 18px; border-bottom:1px solid var(--db-line); }
    .db-card-head h3 { margin:0; color:var(--db-ink); font-size:14px; font-weight:800; }
    .db-card-head p { margin:4px 0 0; color:var(--db-muted); font-size:11px; }
    .db-card-head a { display:inline-flex; align-items:center; gap:5px; color:var(--db-blue); font-size:11px; font-weight:700; white-space:nowrap; }
    .db-card-head a:hover { color:#1f438e; text-decoration:underline; }
    .db-chart { height:300px; padding:18px 20px; }
    .db-table-wrap { overflow:auto; }
    .db-table { width:100%; min-width:560px; margin:0; }
    .db-table thead th { padding:12px 13px; border:0; background:#f5f7fb; color:#63718a; font-size:11px; font-weight:800; white-space:nowrap; }
    .db-table tbody td { padding:12px 13px; border-top:1px solid #eff2f7; color:#34425a; font-size:12px; vertical-align:middle; }
    .db-table tbody tr:hover { background:#fafbfe; }
    .db-invoice-link { color:var(--db-blue); font-weight:800; }
    .db-invoice-link:hover { color:#1f438e; text-decoration:underline; }
    .db-number { font-weight:800; font-variant-numeric:tabular-nums; white-space:nowrap; }
    .db-pill { display:inline-flex; align-items:center; padding:5px 9px; border-radius:99px; background:#eef2ff; color:#355bb0; font-size:10px; font-weight:700; }
    .db-pill.credit { background:#fff4de; color:#9d670b; }
    .db-pill.cash { background:#e8f7f1; color:#177a5c; }
    .db-lists { display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-top:14px; }
    .db-list { padding:5px 16px 12px; }
    .db-list-row { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px 0; border-bottom:1px solid #f0f2f6; }
    .db-list-row:last-child { border-bottom:0; }
    .db-list-main { min-width:0; }
    .db-list-main strong { display:block; overflow:hidden; color:var(--db-ink); font-size:13px; font-weight:700; text-overflow:ellipsis; white-space:nowrap; }
    .db-list-main small { color:var(--db-muted); font-size:11px; }
    .db-list-value { color:var(--db-ink); font-size:12px; font-weight:800; white-space:nowrap; }
    .db-list-value.warn { color:#b84952; }
    .db-empty { padding:24px 15px; color:var(--db-muted); font-size:12px; text-align:center; }
    .db-quick-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:11px; }
    .db-quick { display:flex; align-items:center; gap:11px; min-width:0; min-height:58px; padding:12px 14px; border:1px solid var(--db-line); border-radius:13px; background:#fff; color:var(--db-ink); text-decoration:none; transition:transform .18s,border-color .18s,box-shadow .18s; }
    .db-quick:hover { transform:translateY(-2px); border-color:#cbd7f0; box-shadow:0 6px 16px rgba(24,46,87,.07); color:var(--db-blue); text-decoration:none; }
    .db-quick-icon { display:grid; place-items:center; width:36px; height:36px; flex:0 0 36px; border-radius:11px; background:#edf2ff; color:#3564c8; }
    .db-quick:nth-child(4n + 2) .db-quick-icon { background:#e8f7f1; color:#148367; }
    .db-quick:nth-child(4n + 3) .db-quick-icon { background:#fff2e7; color:#d36b24; }
    .db-quick:nth-child(4n) .db-quick-icon { background:#f3edfb; color:#8058ba; }
    .db-quick span { overflow:hidden; font-size:12px; font-weight:700; text-overflow:ellipsis; white-space:nowrap; }
    .db-quick-search { width:min(100%,320px); height:39px; border:1px solid var(--db-line); border-radius:10px; font-size:12px; transition:border-color .15s,box-shadow .15s; }
    .db-quick-search:focus { border-color:#9cb3e8; box-shadow:0 0 0 3px rgba(53,100,200,.1); }
    [data-quick-link][hidden] { display:none !important; }
    .db-quick-empty { display:none; grid-column:1/-1; padding:22px; color:var(--db-muted); text-align:center; border:1px dashed var(--db-line); border-radius:12px; }
    .db-footnote { margin:12px 2px 0; color:var(--db-muted); font-size:10px; }
    @media (max-width:1200px) { .db-kpis { grid-template-columns:repeat(3,minmax(0,1fr)); } .db-quick-grid { grid-template-columns:repeat(3,minmax(0,1fr)); } .db-stat:nth-child(4n) { border-inline-start:1px solid var(--db-line); } .db-stat:nth-child(3n) { border-inline-start:0; } }
    @media (max-width:850px) { .db-grid-main { grid-template-columns:1fr; } .db-stat-strip { grid-template-columns:repeat(2,minmax(0,1fr)); row-gap:12px; } .db-stat:nth-child(3n) { border-inline-start:1px solid var(--db-line); } .db-stat:nth-child(2n) { border-inline-start:0; } }
    @media (max-width:600px) { .db-hero { align-items:flex-start; flex-direction:column; padding:21px; } .db-hero h1 { font-size:21px; line-height:1.5; } .db-hero-actions { width:100%; } .db-hero-actions .btn { flex:1 1 auto; } .db-kpis { grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px; } .db-kpi { padding:12px; } .db-kpi-label { font-size:11px; } .db-kpi-value { font-size:16px; } .db-stat-strip { grid-template-columns:repeat(2,minmax(0,1fr)); } .db-stat:nth-child(3n) { border-inline-start:1px solid var(--db-line); } .db-stat:nth-child(2n) { border-inline-start:0; } .db-lists { grid-template-columns:1fr; } .db-quick-grid { grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px; } .db-quick { padding:10px; } .db-quick span { font-size:11px; } .db-section-head { align-items:flex-start; flex-direction:column; } .db-section-head .db-quick-search { width:100%; } .db-chart { height:250px; padding:12px; } }
    @media (prefers-reduced-motion:reduce) { .db-kpi,.db-quick { transition:none; } .db-kpi:hover,.db-quick:hover { transform:none; } }
    @media print { .db-hero-actions,.db-quick-grid,.db-quick-search { display:none !important; } .db-card,.db-kpi { break-inside:avoid; box-shadow:none; } }
</style>
@endsection

@section('page-header')
<div class="breadcrumb-header justify-content-between"><div class="my-auto"><div class="d-flex"><h4 class="content-title mb-0 my-auto">لوحة التحكم</h4></div></div></div>
@endsection

@section('content')
<div class="db-page">
    <section class="db-hero">
        <div class="db-hero-copy">
            <span class="db-live"><i class="fas fa-circle"></i> بيانات محدثة من النظام</span>
            <h1>أهلاً {{ $userName }}، هذه لمحة عن نشاط المعرض</h1>
            <p>تابع المبيعات والمخزون والأرصدة والمصروفات من مكان واحد.</p>
            <span class="db-date"><i class="far fa-calendar-alt"></i> {{ $dashboardDate }}</span>
        </div>
        <div class="db-hero-actions">
            <a href="{{ route('sales_invoice') }}" class="btn btn-light"><i class="fas fa-cash-register ml-1"></i> عملية بيع</a>
            <a href="{{ route('purchase_invoices') }}" class="btn btn-outline-light"><i class="fas fa-cart-plus ml-1"></i> فاتورة شراء</a>
            <a href="{{ route('expenses_details') }}" class="btn btn-outline-light"><i class="fas fa-plus ml-1"></i> تسجيل مصروف</a>
        </div>
    </section>

    <div class="db-section-head"><div><h2>المؤشرات المالية والتشغيلية</h2><p>ملخص اليوم والشهر الحالي والأرصدة القائمة</p></div><a href="{{ route('home_reports') }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-chart-pie ml-1"></i> كل التقارير</a></div>
    <section class="db-kpis">
        <article class="db-kpi db-tone-blue"><div class="db-kpi-top"><span class="db-kpi-label">صافي مبيعات اليوم</span><span class="db-kpi-icon"><i class="fas fa-coins"></i></span></div><strong class="db-kpi-value">{{ number_format($dashboardStats['today_sales'], 2) }} ج.م</strong><small>{{ number_format($dashboardStats['today_invoice_count']) }} فاتورة · مرتجعات {{ number_format($dashboardStats['today_returns'], 2) }}</small></article>
        <article class="db-kpi db-tone-green"><div class="db-kpi-top"><span class="db-kpi-label">صافي مبيعات الشهر</span><span class="db-kpi-icon"><i class="fas fa-chart-line"></i></span></div><strong class="db-kpi-value">{{ number_format($dashboardStats['month_sales'], 2) }} ج.م</strong><small>بعد خصم مرتجعات {{ number_format($dashboardStats['month_returns'], 2) }} ج.م</small></article>
        <article class="db-kpi db-tone-orange"><div class="db-kpi-top"><span class="db-kpi-label">مشتريات الشهر</span><span class="db-kpi-icon"><i class="fas fa-truck-loading"></i></span></div><strong class="db-kpi-value">{{ number_format($dashboardStats['month_purchases'], 2) }} ج.م</strong><small>{{ number_format($dashboardStats['purchase_invoice_count']) }} فاتورة شراء</small></article>
        <article class="db-kpi db-tone-red"><div class="db-kpi-top"><span class="db-kpi-label">مصروفات الشهر</span><span class="db-kpi-icon"><i class="fas fa-receipt"></i></span></div><strong class="db-kpi-value">{{ number_format($dashboardStats['month_expenses'], 2) }} ج.م</strong><small>من سجل المصروفات النشط</small></article>
        <article class="db-kpi db-tone-violet"><div class="db-kpi-top"><span class="db-kpi-label">مستحقات العملاء</span><span class="db-kpi-icon"><i class="fas fa-user-clock"></i></span></div><strong class="db-kpi-value">{{ number_format($dashboardStats['customer_balances'], 2) }} ج.م</strong><small>مديونيات آجلة قائمة</small></article>
        <article class="db-kpi db-tone-cyan"><div class="db-kpi-top"><span class="db-kpi-label">مستحقات الموردين</span><span class="db-kpi-icon"><i class="fas fa-file-invoice-dollar"></i></span></div><strong class="db-kpi-value">{{ number_format($dashboardStats['supplier_balances'], 2) }} ج.م</strong><small>أرصدة فواتير الشراء القائمة</small></article>
    </section>

    <section class="db-stat-strip" aria-label="إحصاءات النظام">
        <div class="db-stat"><strong>{{ number_format($dashboardStats['product_count']) }}</strong><span>منتج نشط</span></div>
        <div class="db-stat"><strong>{{ number_format($dashboardStats['stock_units']) }}</strong><span>وحدة في المخزون</span></div>
        <div class="db-stat"><strong>{{ number_format($dashboardStats['stock_value'], 2) }} ج.م</strong><span>قيمة المخزون بالتكلفة</span></div>
        <div class="db-stat"><strong>{{ number_format($dashboardStats['low_stock_count']) }}</strong><span>منتج عند حد إعادة الطلب</span></div>
        <div class="db-stat"><strong>{{ number_format($counts['customers']) }}</strong><span>عميل نشط</span></div>
        <div class="db-stat"><strong>{{ number_format($counts['suppliers']) }}</strong><span>مورد نشط</span></div>
        <div class="db-stat"><strong>{{ number_format($counts['warehouses']) }} / {{ number_format($counts['branches']) }}</strong><span>مخزن / فرع</span></div>
        <div class="db-stat"><strong>{{ number_format($counts['users']) }} / {{ number_format($counts['categories']) }}</strong><span>مستخدم / تصنيف</span></div>
    </section>

    <div class="db-section-head"><div><h2>اتجاه المبيعات</h2><p>آخر 30 يومًا · المرتجعات تظهر منفصلة</p></div></div>
    <section class="db-grid-main">
        <article class="db-card">
            <header class="db-card-head"><div><h3>المبيعات اليومية</h3><p>صافي المبيعات بعد المرتجعات مع قيمة المرتجعات للمقارنة</p></div><span class="db-pill">آخر 30 يومًا</span></header>
            <div class="db-chart"><canvas id="dashboardSalesChart"></canvas></div>
        </article>
        <article class="db-card">
            <header class="db-card-head"><div><h3>فواتير المبيعات الأخيرة</h3><p>آخر العمليات المسجلة</p></div><a href="{{ route('all_sales') }}">عرض الفواتير</a></header>
            <div class="db-table-wrap"><table class="db-table table">
                <thead><tr><th>الفاتورة</th><th>العميل</th><th>الصافي</th><th>الدفع</th></tr></thead><tbody>
                    @forelse ($recentInvoices as $invoice)
                        @php $invoiceNet = max(0, (float) ($invoice->net_total ?? ($invoice->total_amount - $invoice->deduction)) - (float) ($invoice->returned_amount ?? 0)); @endphp
                        <tr><td><a href="{{ route('invoice.show', $invoice->id) }}" class="db-invoice-link">#{{ $invoice->id }}</a><br><small>{{ \Illuminate\Support\Carbon::parse($invoice->created_at)->format('Y-m-d') }}</small></td><td>{{ $invoice->customer_name ?: 'عميل نقدي' }}</td><td class="db-number">{{ number_format($invoiceNet, 2) }} ج.م</td><td><span class="db-pill {{ $invoice->payment_method === 'credit' ? 'credit' : 'cash' }}">{{ ['credit'=>'آجل','visa'=>'بطاقة','cash'=>'نقدي'][$invoice->payment_method] ?? $invoice->payment_method }}</span></td></tr>
                    @empty<tr><td colspan="4" class="db-empty">لا توجد فواتير مبيعات مسجلة.</td></tr>@endforelse
                </tbody>
            </table></div>
        </article>
    </section>

    <section class="db-lists">
        <article class="db-card">
            <header class="db-card-head"><div><h3>تنبيهات المخزون</h3><p>المنتجات التي تحتاج متابعة</p></div><a href="{{ route('inventory_low_stock') }}">تقرير المخزون</a></header>
            <div class="db-list">
                @forelse ($lowStockProducts as $product)
                    <div class="db-list-row"><div class="db-list-main"><strong>{{ $product->name }}</strong><small>كود المنتج: {{ $product->id }}</small></div><span class="db-list-value warn">{{ number_format($product->quantity) }} متاح</span></div>
                @empty<div class="db-empty">لا توجد منتجات عند حد إعادة الطلب.</div>@endforelse
            </div>
        </article>
        <article class="db-card">
            <header class="db-card-head"><div><h3>المصروفات الأخيرة</h3><p>أحدث القيود المسجلة</p></div><a href="{{ route('showDetails') }}">عرض المصروفات</a></header>
            <div class="db-list">
                @forelse ($recentExpenses as $expense)
                    <div class="db-list-row"><div class="db-list-main"><strong>{{ $expense->expenses_name ?: 'مصروف' }}</strong><small>{{ $expense->expenses_date }} · {{ $expense->expenses_note ?: 'بدون ملاحظات' }}</small></div><span class="db-list-value">{{ number_format($expense->expenses_cost, 2) }} ج.م</span></div>
                @empty<div class="db-empty">لا توجد مصروفات مسجلة.</div>@endforelse
            </div>
        </article>
    </section>

    <div class="db-section-head">
        <div><h2>الوصول السريع لأقسام البرنامج</h2><p>انتقل مباشرة إلى أهم الشاشات والعمليات</p></div>
        <input type="search" id="dashboardQuickSearch" class="form-control db-quick-search" placeholder="ابحث عن قسم..." aria-label="البحث في أقسام البرنامج">
    </div>
    <nav class="db-quick-grid" aria-label="أقسام البرنامج">
        <a class="db-quick" data-quick-link href="{{ route('all_sales') }}"><span class="db-quick-icon"><i class="fas fa-receipt"></i></span><span>فواتير المبيعات</span></a>
        <a class="db-quick" data-quick-link href="{{ route('sales.returns.index') }}"><span class="db-quick-icon"><i class="fas fa-undo"></i></span><span>مرتجعات المبيعات</span></a>
        <a class="db-quick" data-quick-link href="{{ route('purchase_invoices') }}"><span class="db-quick-icon"><i class="fas fa-file-invoice"></i></span><span>فواتير المشتريات</span></a>
        <a class="db-quick" data-quick-link href="{{ route('products') }}"><span class="db-quick-icon"><i class="fas fa-box-open"></i></span><span>المنتجات والمخزون</span></a>
        <a class="db-quick" data-quick-link href="{{ route('categories') }}"><span class="db-quick-icon"><i class="fas fa-tags"></i></span><span>تصنيفات المنتجات</span></a>
        <a class="db-quick" data-quick-link href="{{ route('all_customers') }}"><span class="db-quick-icon"><i class="fas fa-users"></i></span><span>العملاء</span></a>
        <a class="db-quick" data-quick-link href="{{ route('credit_customers') }}"><span class="db-quick-icon"><i class="fas fa-hand-holding-usd"></i></span><span>مديونيات العملاء</span></a>
        <a class="db-quick" data-quick-link href="{{ route('suppliers') }}"><span class="db-quick-icon"><i class="fas fa-truck"></i></span><span>الموردون</span></a>
        <a class="db-quick" data-quick-link href="{{ route('credit_suplliers') }}"><span class="db-quick-icon"><i class="fas fa-file-invoice-dollar"></i></span><span>مستحقات الموردين</span></a>
        <a class="db-quick" data-quick-link href="{{ route('showDetails') }}"><span class="db-quick-icon"><i class="fas fa-receipt"></i></span><span>المصروفات</span></a>
        <a class="db-quick" data-quick-link href="{{ route('Warehouses') }}"><span class="db-quick-icon"><i class="fas fa-warehouse"></i></span><span>المخازن</span></a>
        <a class="db-quick" data-quick-link href="{{ route('branches') }}"><span class="db-quick-icon"><i class="fas fa-store"></i></span><span>الفروع</span></a>
        <a class="db-quick" data-quick-link href="{{ route('all_users') }}"><span class="db-quick-icon"><i class="fas fa-user-shield"></i></span><span>المستخدمون</span></a>
        <a class="db-quick" data-quick-link href="{{ route('all_roles') }}"><span class="db-quick-icon"><i class="fas fa-users-cog"></i></span><span>الأدوار والصلاحيات</span></a>
        <a class="db-quick" data-quick-link href="{{ route('expenses') }}"><span class="db-quick-icon"><i class="fas fa-list-ul"></i></span><span>بنود المصروفات</span></a>
        <a class="db-quick" data-quick-link href="{{ route('home_reports') }}"><span class="db-quick-icon"><i class="fas fa-chart-pie"></i></span><span>التقارير</span></a>
        <div class="db-quick-empty" id="dashboardQuickEmpty">لا توجد أقسام تطابق البحث.</div>
    </nav>
    <p class="db-footnote">الأرقام تستند إلى السجلات النشطة. صافي المبيعات يخصم المرتجعات المسجلة، وقيمة المخزون تقديرية باستخدام تكلفة الأصناف المسجلة.</p>
</div>
@endsection

@section('js')
<script src="{{ URL::asset('assets/js/chart.js') }}"></script>
<script>
    document.getElementById('dashboardQuickSearch')?.addEventListener('input', function () {
        const query = this.value.trim().toLocaleLowerCase();
        const links = document.querySelectorAll('[data-quick-link]');
        let visibleCount = 0;
        links.forEach(link => {
            const matches = link.textContent.toLocaleLowerCase().includes(query);
            link.hidden = !matches;
            if (matches) visibleCount++;
        });
        document.getElementById('dashboardQuickEmpty').style.display = visibleCount ? 'none' : 'block';
    });

    new Chart(document.getElementById('dashboardSalesChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: @json($dailyLabels),
            datasets: [
                { label: 'صافي المبيعات (ج.م)', data: @json($dailyNetSales), borderColor: '#3564c8', backgroundColor: 'rgba(53,100,200,.12)', borderWidth: 2, pointRadius: 2, tension: .32, fill: true },
                { label: 'المرتجعات (ج.م)', data: @json($dailyReturnAmounts), borderColor: '#d36b24', backgroundColor: 'rgba(211,107,36,.08)', borderWidth: 1.5, pointRadius: 1.5, tension: .25 }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false, interaction: { intersect: false, mode: 'index' }, scales: { y: { beginAtZero: true } } }
    });
</script>
@endsection
