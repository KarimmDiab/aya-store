@extends('layouts.master3')

@section('title')
    معرض أيه - التقرير الموحد
@endsection

@section('content')
<style>
    .cs-page{--ink:#17243b;--muted:#718096;--line:#e5eaf1;direction:rtl;color:var(--ink)}
    .cs-hero{display:flex;justify-content:space-between;align-items:center;gap:20px;padding:24px;margin-bottom:18px;border:1px solid var(--line);border-radius:18px;background:linear-gradient(120deg,#fff,#f1f7ff);box-shadow:0 7px 22px #1a2b4c0b}
    .cs-heading{display:flex;align-items:center;gap:15px}.cs-icon{display:grid;place-items:center;width:52px;height:52px;border-radius:16px;background:linear-gradient(145deg,#1769d2,#54a1ff);color:#fff;font-size:21px}.cs-heading h1{margin:0;font-size:24px;font-weight:800}.cs-heading p{margin:5px 0 0;color:var(--muted);font-size:13px}
    .cs-actions{display:flex;gap:8px;flex-wrap:wrap}.cs-actions .btn{border-radius:10px;font-weight:700}.cs-period{display:flex;align-items:center;gap:7px;width:max-content;margin-top:10px;padding:6px 11px;border-radius:30px;background:#eaf2ff;color:#2360a8;font-size:12px;font-weight:700}
    .cs-filter,.cs-section{padding:18px;margin-bottom:18px;border:1px solid var(--line);border-radius:15px;background:#fff;box-shadow:0 5px 18px #1a2b4c08}.cs-filter form{display:flex;align-items:end;gap:10px;flex-wrap:wrap}.cs-field{min-width:175px}.cs-field label{display:block;margin-bottom:5px;color:var(--muted);font-size:12px}.cs-filter .form-control{height:40px}.cs-section-head{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:14px}.cs-section-head h2{margin:0;font-size:17px;font-weight:800}.cs-section-head p{margin:4px 0 0;color:var(--muted);font-size:12px}
    .cs-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}.cs-card{min-width:0;padding:15px;border:1px solid var(--line);border-radius:13px;background:#fff}.cs-label{display:flex;gap:8px;align-items:center;color:var(--muted);font-size:12px;font-weight:700}.cs-label i{color:#3880dc}.cs-value{display:block;margin-top:9px;font-size:22px;line-height:1.25;font-weight:800;font-variant-numeric:tabular-nums;overflow-wrap:anywhere}.cs-unit{display:block;margin-top:4px;color:#98a2b2;font-size:11px}.cs-good{color:#13845d}.cs-warn{color:#ba414c}.cs-highlight{background:linear-gradient(140deg,#f0fbf6,#fff);border-color:#cdebdc}.cs-profit{font-size:28px}.cs-summary{width:100%;border-collapse:collapse}.cs-summary th,.cs-summary td{padding:11px 12px;border-bottom:1px solid #edf0f4;text-align:right}.cs-summary th{color:#718096;font-size:12px}.cs-summary td{font-size:13px}.cs-summary tr:last-child td{border-bottom:0}.cs-note{padding:12px 14px;border:1px solid #e7edf5;border-radius:11px;background:#f8fafc;color:#64748b;font-size:12px;line-height:1.8}.cs-footer{margin:12px 2px;color:#96a0af;font-size:11px}
    @media(max-width:900px){.cs-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.cs-hero{align-items:flex-start;flex-direction:column}}
    @media(max-width:480px){.cs-grid{grid-template-columns:1fr 1fr;gap:8px}.cs-card{padding:11px}.cs-value{font-size:18px}.cs-filter,.cs-section{padding:13px}.cs-heading h1{font-size:20px}}
    @media print{.cs-actions,.cs-filter{display:none!important}.cs-hero,.cs-section,.cs-card{box-shadow:none;break-inside:avoid}.cs-page{padding:0}.cs-grid{grid-template-columns:repeat(4,minmax(0,1fr))}}
</style>
<div class="container-fluid cs-page">
    <header class="cs-hero">
        <div class="cs-heading"><span class="cs-icon"><i class="fas fa-chart-pie"></i></span><div><h1>التقرير الموحد</h1><p>ملخص حركة المشتريات والمبيعات والمخزون والنتيجة المالية</p><span class="cs-period"><i class="far fa-calendar-alt"></i>{{ $startDate }} <span>—</span> {{ $endDate }}</span></div></div>
        <div class="cs-actions"><button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fas fa-print ml-1"></i> طباعة</button><a href="{{ route('home_reports') }}" class="btn btn-light"><i class="fas fa-arrow-right ml-1"></i> التقارير</a></div>
    </header>

    <section class="cs-filter"><form method="GET" action="{{ route('reports.consolidated') }}">
        <div class="cs-field"><label for="startDate">من تاريخ</label><input class="form-control" type="date" id="startDate" name="startDate" value="{{ $startDate }}" required></div>
        <div class="cs-field"><label for="endDate">إلى تاريخ</label><input class="form-control" type="date" id="endDate" name="endDate" value="{{ $endDate }}" required></div>
        <button class="btn btn-primary" type="submit"><i class="fas fa-filter ml-1"></i> عرض التقرير</button>
    </form></section>

    <section class="cs-section">
        <div class="cs-section-head"><div><h2><i class="fas fa-cart-arrow-down ml-1" style="color:#8058ba"></i> المشتريات</h2><p>قيمة المشتريات ومرتجعات المورد المسجلة خلال الفترة</p></div></div>
        <div class="cs-grid">
            <article class="cs-card"><span class="cs-label"><i class="fas fa-boxes"></i> عدد القطع المشتراة</span><strong class="cs-value">{{ number_format($purchaseStats->quantity) }}</strong><small class="cs-unit">قطعة</small></article>
            <article class="cs-card"><span class="cs-label"><i class="fas fa-file-invoice"></i> فواتير الشراء</span><strong class="cs-value">{{ number_format($purchaseStats->invoice_count) }}</strong><small class="cs-unit">فاتورة</small></article>
            <article class="cs-card"><span class="cs-label"><i class="fas fa-coins"></i> إجمالي المشتريات</span><strong class="cs-value">{{ number_format($purchaseStats->amount, 2) }}</strong><small class="cs-unit">جنيه مصري</small></article>
            <article class="cs-card"><span class="cs-label"><i class="fas fa-undo"></i> مرتجعات المورد</span><strong class="cs-value cs-warn">{{ number_format($purchaseStats->return_amount, 2) }}</strong><small class="cs-unit">{{ number_format($purchaseStats->return_count) }} مرتجع · {{ number_format($purchaseStats->return_quantity) }} قطعة</small></article>
            <article class="cs-card"><span class="cs-label"><i class="fas fa-scale-balanced"></i> صافي المشتريات</span><strong class="cs-value">{{ number_format($purchaseStats->amount - $purchaseStats->return_amount, 2) }}</strong><small class="cs-unit">بعد خصم المرتجعات · جنيه مصري</small></article>
        </div>
    </section>

    <section class="cs-section">
        <div class="cs-section-head"><div><h2><i class="fas fa-cash-register ml-1" style="color:#2876d1"></i> المبيعات والمرتجعات</h2><p>إجمالي البيع قبل المرتجعات مع عرض المرتجعات بشكل مستقل</p></div></div>
        <div class="cs-grid">
            <article class="cs-card"><span class="cs-label"><i class="fas fa-box-open"></i> القطع المباعة</span><strong class="cs-value">{{ number_format($salesStats->quantity) }}</strong><small class="cs-unit">قطعة قبل المرتجعات</small></article>
            <article class="cs-card"><span class="cs-label"><i class="fas fa-file-invoice-dollar"></i> فواتير البيع</span><strong class="cs-value">{{ number_format($salesStats->invoice_count) }}</strong><small class="cs-unit">فاتورة</small></article>
            <article class="cs-card"><span class="cs-label"><i class="fas fa-money-bill-wave"></i> قيمة المبيعات</span><strong class="cs-value">{{ number_format($salesStats->amount, 2) }}</strong><small class="cs-unit">جنيه مصري بعد خصومات الفواتير</small></article>
            <article class="cs-card"><span class="cs-label"><i class="fas fa-undo"></i> المرتجعات</span><strong class="cs-value">{{ number_format($returns->quantity) }} قطعة</strong><small class="cs-unit">{{ number_format($returns->invoice_count) }} فاتورة · {{ number_format($returns->amount, 2) }} جنيه</small></article>
        </div>
    </section>

    <section class="cs-section">
        <div class="cs-section-head"><div><h2><i class="fas fa-warehouse ml-1" style="color:#db9c22"></i> المخزون المتبقي بنهاية الفترة</h2><p>الرصيد المحسوب حتى {{ $endDate }}</p></div></div>
        <div class="cs-grid">
            <article class="cs-card"><span class="cs-label"><i class="fas fa-boxes"></i> عدد القطع المتبقية</span><strong class="cs-value">{{ number_format($remainingQuantity) }}</strong><small class="cs-unit">قطعة</small></article>
            <article class="cs-card"><span class="cs-label"><i class="fas fa-tags"></i> تكلفة المخزون المتبقي</span><strong class="cs-value">{{ number_format($remainingStockCost, 2) }}</strong><small class="cs-unit">جنيه مصري تقديريًا</small></article>
        </div>
    </section>

    <section class="cs-section">
        <div class="cs-section-head"><div><h2><i class="fas fa-chart-line ml-1" style="color:#15936c"></i> النتيجة المالية</h2><p>الربح بعد احتساب تكلفة البضاعة والمرتجعات والمصروفات</p></div></div>
        <div class="cs-grid">
            <article class="cs-card"><span class="cs-label"><i class="fas fa-receipt"></i> إجمالي المصروفات</span><strong class="cs-value">{{ number_format($expensesAmount, 2) }}</strong><small class="cs-unit">جنيه مصري</small></article>
            <article class="cs-card"><span class="cs-label"><i class="fas fa-box"></i> تكلفة البضاعة المباعة</span><strong class="cs-value">{{ number_format($profitStats->cost_of_goods, 2) }}</strong><small class="cs-unit">تقديريًا بعد خصم كمية المرتجعات</small></article>
            <article class="cs-card cs-highlight"><span class="cs-label"><i class="fas fa-wallet"></i> صافي الربح بعد المصروفات والمرتجعات</span><strong class="cs-value cs-profit {{ $profitStats->net_profit >= 0 ? 'cs-good' : 'cs-warn' }}">{{ number_format($profitStats->net_profit, 2) }}</strong><small class="cs-unit">جنيه مصري · قبل الضرائب</small></article>
        </div>
    </section>

    <section class="cs-section">
        <div class="cs-section-head"><div><h2>ملخص الأرقام</h2><p>القيم المالية بالجنيه المصري</p></div></div>
        <table class="cs-summary"><thead><tr><th>المؤشر</th><th>القيمة</th></tr></thead><tbody>
            <tr><td>صافي المبيعات بعد المرتجعات</td><td>{{ number_format($profitStats->sales_amount, 2) }} ج.م</td></tr>
            <tr><td>الربح الإجمالي التقديري بعد المرتجعات وقبل المصروفات</td><td>{{ number_format($profitStats->gross_profit, 2) }} ج.م</td></tr>
            <tr><td>المصروفات المسجلة</td><td>{{ number_format($expensesAmount, 2) }} ج.م</td></tr>
            <tr><td><strong>صافي الربح بعد المصروفات</strong></td><td><strong>{{ number_format($profitStats->net_profit, 2) }} ج.م</strong></td></tr>
        </tbody></table>
    </section>

    <div class="cs-note"><i class="fas fa-info-circle ml-1"></i> صافي الربح تقديري: صافي المبيعات بعد المرتجعات ناقص تكلفة البضاعة المباعة ناقص المصروفات المسجلة. تُقدّر تكلفة الوحدة بمتوسط تكلفة الشراء المرجّح خلال الفترة، أو التكلفة الحالية عند عدم وجود مشتريات في الفترة. المبيعات التي لا تتوفر لها تكلفة مسجلة لا تدخل في تقدير الربح. تكلفة المخزون المتبقي تقديرية بالمتوسط المرجّح لمشترياته حتى نهاية الفترة أو التكلفة الحالية.</div>
    <p class="cs-footer">تم احتساب الكميات والفواتير من السجلات النشطة فقط، مع استبعاد السجلات المحذوفة.</p>
</div>
@endsection
