@extends('layouts.master')

@section('title')
    معرض أيه - صفحة التقارير
@endsection

@section('css')
<style>
    .rp-wrap { width:100%; }

    /* ---------- Period bar ---------- */
    .rp-period {
        position:sticky; top:70px; z-index:20;
        background:#fff; border:1px solid #e6eaf2; border-radius:12px;
        box-shadow:0 6px 20px rgba(20,30,60,.06); padding:16px 18px; margin-bottom:22px;
    }
    .rp-period-title { font-weight:700; color:#1f2a44; margin-bottom:10px; }
    .rp-dates { display:flex; flex-wrap:wrap; gap:10px; align-items:center; }
    .rp-dates .rp-field { flex:1 1 170px; min-width:150px; }
    .rp-dates label { display:block; font-size:12px; color:#8a93a6; margin-bottom:3px; }
    .rp-dates .form-control { height:40px; }
    .rp-dates .form-control.is-invalid { border-color:#dc3545; }
    .rp-presets { display:flex; flex-wrap:wrap; gap:6px; margin-top:12px; }
    .rp-chip {
        border:1px solid #d9dfec; background:#f7f9fc; color:#4a5568; border-radius:50px;
        padding:4px 14px; font-size:13px; cursor:pointer; transition:.15s;
    }
    .rp-chip:hover { background:#eaf2ff; border-color:#0162e8; color:#0162e8; }
    .rp-chip.active { background:#0162e8; border-color:#0162e8; color:#fff; }
    .rp-error { display:none; color:#dc3545; font-size:13px; margin-top:8px; }
    .rp-error.show { display:block; }

    /* ---------- Search ---------- */
    .rp-search { max-width:340px; margin-bottom:18px; }

    /* ---------- Grid + cards ---------- */
    .rp-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(310px,1fr)); gap:18px; }
    .rp-card {
        background:#fff; border:1px solid #e6eaf2; border-radius:14px; overflow:hidden;
        display:flex; flex-direction:column; transition:box-shadow .2s, transform .2s;
    }
    .rp-card:hover { box-shadow:0 10px 28px rgba(20,30,60,.09); transform:translateY(-2px); }
    .rp-card-head { display:flex; align-items:center; gap:12px; padding:16px 18px; border-bottom:1px solid #f0f2f7; }
    .rp-card-icon {
        width:42px; height:42px; border-radius:12px; display:flex; align-items:center; justify-content:center;
        font-size:18px; color:#fff; flex:0 0 42px;
    }
    .rp-card-head h2 { font-size:17px; font-weight:700; margin:0; color:#1f2a44; }
    .rp-card-head small { color:#9aa3b5; display:block; font-size:12px; }

    .c-sales     { background:linear-gradient(135deg,#0162e8,#4a90ff); }
    .c-stock     { background:linear-gradient(135deg,#f59e0b,#fbbf24); }
    .c-purchases { background:linear-gradient(135deg,#8b5cf6,#a78bfa); }
    .c-profit    { background:linear-gradient(135deg,#10b981,#34d399); }
    .c-customers { background:linear-gradient(135deg,#ec4899,#f472b6); }
    .c-suppliers { background:linear-gradient(135deg,#0ea5e9,#38bdf8); }

    .rp-list { list-style:none; margin:0; padding:8px; }
    .rp-item {
        width:100%; display:flex; align-items:center; gap:12px; text-align:right;
        background:transparent; border:0; border-radius:10px; padding:11px 12px; color:#2d3752;
        font-size:14px; line-height:1.5; cursor:pointer; transition:background .15s;
    }
    .rp-item i.lead-icon { width:20px; text-align:center; color:#0162e8; opacity:.85; }
    .rp-item span.txt { flex:1; }
    .rp-item i.go { color:#c0c7d6; transition:transform .15s, color .15s; }
    .rp-item:hover:not(:disabled) { background:#f1f6ff; }
    .rp-item:hover:not(:disabled) i.go { color:#0162e8; transform:translateX(-3px); }
    .rp-item:focus-visible { outline:2px solid #0162e8; outline-offset:-2px; }
    .rp-item:disabled { cursor:not-allowed; opacity:.6; }
    .rp-item:disabled i.lead-icon { color:#9aa3b5; }
    .rp-soon { font-size:11px; background:#f1f2f6; color:#8a93a6; border-radius:50px; padding:2px 9px; white-space:nowrap; }
    .rp-item.is-loading { pointer-events:none; opacity:.7; }

    .rp-empty { display:none; text-align:center; color:#8a93a6; padding:40px 0; }

    @media (max-width:576px) { .rp-period { top:0; position:static; } }
</style>
@endsection

@section('page-header')
<!-- breadcrumb -->
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto">التقارير</h4>
            <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ لوحة التقارير</span>
        </div>
    </div>
</div>
<!-- breadcrumb -->
@endsection

@section('content')
@php
    // Reports without a route yet are shown disabled with a "قريبًا" badge.
    $groups = [
        ['key'=>'sales', 'title'=>'تقارير المبيعات', 'icon'=>'fa-chart-line', 'color'=>'c-sales', 'items'=>[
            ['t'=>'إجمالي المبيعات خلال فترة معينة', 'i'=>'fa-calendar-day', 'r'=>route('total_sales'), 'm'=>'get'],
            ['t'=>'المبيعات لكل منتج خلال فترة معينة', 'i'=>'fa-box-open', 'r'=>route('sales_per_product'), 'm'=>'get'],
            ['t'=>'المبيعات لكل عميل خلال فترة معينة', 'i'=>'fa-users', 'r'=>route('salesPerCustomer'), 'm'=>'get'],
            ['t'=>'المبيعات التي تمت من قبل كل موظف خلال فترة معينة', 'i'=>'fa-user-tie', 'r'=>route('salesPerUser'), 'm'=>'get'],
        ]],
        ['key'=>'stock', 'title'=>'تقارير المخزون', 'icon'=>'fa-warehouse', 'color'=>'c-stock', 'items'=>[
            ['t'=>'رصيد كل منتج في بداية ونهاية الفترة', 'i'=>'fa-boxes', 'r'=>route('inventory_stock_summary'), 'm'=>'get'],
            ['t'=>'حركة المخزون خلال الفترة', 'i'=>'fa-exchange-alt', 'r'=>route('inventory_movements'), 'm'=>'get'],
            ['t'=>'المنتجات عند حد إعادة الطلب بنهاية الفترة', 'i'=>'fa-exclamation-circle', 'r'=>route('inventory_low_stock'), 'm'=>'get'],
        ]],
        ['key'=>'purchases', 'title'=>'تقارير المشتريات', 'icon'=>'fa-shopping-cart', 'color'=>'c-purchases', 'items'=>[
            ['t'=>'إجمالي المشتريات والمدفوعات خلال الفترة', 'i'=>'fa-calendar-day', 'r'=>route('purchase_report_total'), 'm'=>'get'],
            ['t'=>'المشتريات والمدفوعات لكل مورد', 'i'=>'fa-truck', 'r'=>route('purchase_report_suppliers'), 'm'=>'get'],
            ['t'=>'الكميات والتكلفة لكل منتج', 'i'=>'fa-box', 'r'=>route('purchase_report_products'), 'm'=>'get'],
            ['t'=>'المدفوعات وأرصدة الموردين', 'i'=>'fa-file-invoice-dollar', 'r'=>route('purchase_report_payments'), 'm'=>'get'],
        ]],
        ['key'=>'profit', 'title'=>'تقارير الأرباح', 'icon'=>'fa-money-bill-wave', 'color'=>'c-profit', 'items'=>[
            ['t'=>'التقرير الموحد للمشتريات والمبيعات والمخزون والربح', 'i'=>'fa-file-invoice-dollar', 'r'=>route('reports.consolidated'), 'm'=>'get'],
            ['t'=>'تقدير الربح الإجمالي خلال فترة', 'i'=>'fa-coins', 'r'=>route('profit_report_summary'), 'm'=>'get'],
            ['t'=>'مقارنة الربح بين فترتين متساويتين', 'i'=>'fa-balance-scale', 'r'=>route('profit_report_comparison'), 'm'=>'get'],
        ]],
        ['key'=>'customers', 'title'=>'تقارير العملاء', 'icon'=>'fa-user-friends', 'color'=>'c-customers', 'items'=>[
            ['t'=>'مبيعات العملاء والمرتجعات وصافي المبيعات', 'i'=>'fa-trophy', 'r'=>route('customer_report_sales'), 'm'=>'get'],
            ['t'=>'أرصدة العملاء أول وآخر الفترة', 'i'=>'fa-hand-holding-usd', 'r'=>route('customer_report_balances'), 'm'=>'get'],
            ['t'=>'المبالغ المحصلة من العملاء', 'i'=>'fa-receipt', 'r'=>route('customer_report_collections'), 'm'=>'get'],
        ]],
        ['key'=>'suppliers', 'title'=>'تقارير الموردين', 'icon'=>'fa-truck', 'color'=>'c-suppliers', 'items'=>[
            ['t'=>'المبالغ المدفوعة لكل مورد خلال الفترة', 'i'=>'fa-wallet', 'r'=>route('supplier_report_paid'), 'm'=>'get'],
            ['t'=>'أرصدة الموردين أول وآخر الفترة', 'i'=>'fa-file-invoice', 'r'=>route('supplier_report_balances'), 'm'=>'get'],
        ]],
    ];
@endphp

<div class="rp-wrap">
    @csrf {{-- CSRF token source for submitReport() --}}

    <!-- Period selector -->
    <div class="rp-period">
        <div class="rp-period-title"><i class="far fa-calendar-alt ml-1"></i> اختر الفترة الزمنية</div>
        <div class="rp-dates">
            <div class="rp-field">
                <label for="startDate">من تاريخ</label>
                <input type="date" class="form-control" id="startDate" name="startDate" required>
            </div>
            <div class="rp-field">
                <label for="endDate">إلى تاريخ</label>
                <input type="date" class="form-control" id="endDate" name="endDate" required>
            </div>
        </div>
        <div class="rp-presets" id="rpPresets">
            <button type="button" class="rp-chip" data-preset="today">اليوم</button>
            <button type="button" class="rp-chip" data-preset="week">آخر 7 أيام</button>
            <button type="button" class="rp-chip" data-preset="month">هذا الشهر</button>
            <button type="button" class="rp-chip" data-preset="lastMonth">الشهر الماضي</button>
            <button type="button" class="rp-chip" data-preset="quarter">هذا الربع</button>
            <button type="button" class="rp-chip" data-preset="year">هذه السنة</button>
        </div>
        <div class="rp-error" id="rpError" role="alert"></div>
    </div>

    <!-- Search -->
    <input type="search" id="rpSearch" class="form-control rp-search" placeholder="ابحث عن تقرير...">

    <!-- Report cards -->
    <div class="rp-grid" id="rpGrid">
        @foreach ($groups as $g)
            <section class="rp-card" data-group>
                <div class="rp-card-head">
                    <div class="rp-card-icon {{ $g['color'] }}"><i class="fas {{ $g['icon'] }}"></i></div>
                    <div>
                        <h2>{{ $g['title'] }}</h2>
                        <small>{{ count($g['items']) }} تقارير</small>
                    </div>
                </div>
                <ul class="rp-list">
                    @foreach ($g['items'] as $item)
                        <li data-item>
                            <button type="button" class="rp-item"
                                    @if ($item['r']) data-route="{{ $item['r'] }}" data-method="{{ $item['m'] ?? 'post' }}" @else disabled title="هذا التقرير غير متاح حاليًا" @endif>
                                <i class="fas {{ $item['i'] }} lead-icon"></i>
                                <span class="txt">{{ $item['t'] }}</span>
                                @if ($item['r'])
                                    <i class="fas fa-chevron-left go"></i>
                                @else
                                    <span class="rp-soon">قريبًا</span>
                                @endif
                            </button>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endforeach
    </div>

    <div class="rp-empty" id="rpEmpty"><i class="fas fa-search fa-2x d-block mb-2"></i>لا توجد تقارير مطابقة لبحثك</div>
</div>
@endsection

@section('js')
<script>
(function () {
    var $start = document.getElementById('startDate');
    var $end   = document.getElementById('endDate');
    var $err   = document.getElementById('rpError');

    /* ---------- date helpers (local time, avoids toISOString UTC shift) ---------- */
    function fmt(d) {
        var m = String(d.getMonth() + 1).padStart(2, '0');
        var day = String(d.getDate()).padStart(2, '0');
        return d.getFullYear() + '-' + m + '-' + day;
    }

    function showError(msg) {
        $err.textContent = msg;
        $err.classList.add('show');
    }
    function clearError() {
        $err.classList.remove('show');
        $start.classList.remove('is-invalid');
        $end.classList.remove('is-invalid');
    }

    function setRange(a, b, presetBtn) {
        $start.value = fmt(a);
        $end.value   = fmt(b);
        document.querySelectorAll('.rp-chip').forEach(function (c) { c.classList.remove('active'); });
        if (presetBtn) presetBtn.classList.add('active');
        clearError();
    }

    function applyPreset(name, btn) {
        var now = new Date(), y = now.getFullYear(), m = now.getMonth();
        switch (name) {
            case 'today':     setRange(now, now, btn); break;
            case 'week':      setRange(new Date(y, m, now.getDate() - 6), now, btn); break;
            case 'month':     setRange(new Date(y, m, 1), now, btn); break;
            case 'lastMonth': setRange(new Date(y, m - 1, 1), new Date(y, m, 0), btn); break;
            case 'quarter':   setRange(new Date(y, Math.floor(m / 3) * 3, 1), now, btn); break;
            case 'year':      setRange(new Date(y, 0, 1), now, btn); break;
        }
    }

    document.getElementById('rpPresets').addEventListener('click', function (e) {
        var btn = e.target.closest('.rp-chip');
        if (btn) applyPreset(btn.dataset.preset, btn);
    });

    [$start, $end].forEach(function (el) {
        el.addEventListener('change', function () {
            document.querySelectorAll('.rp-chip').forEach(function (c) { c.classList.remove('active'); });
            clearError();
        });
    });

    // Default: current month
    applyPreset('month', document.querySelector('[data-preset="month"]'));

    /* ---------- Submit a report in a new tab ---------- */
    function getCsrf() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) return meta.getAttribute('content');
        var input = document.querySelector('input[name="_token"]');
        return input ? input.value : '';
    }

    window.submitReport = function (route, btn) {
        clearError();
        var s = $start.value, e = $end.value;

        if (!s || !e) {
            if (!s) $start.classList.add('is-invalid');
            if (!e) $end.classList.add('is-invalid');
            showError('الرجاء اختيار تاريخ البداية وتاريخ النهاية أولاً.');
            $start.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
        if (s > e) {
            $start.classList.add('is-invalid');
            $end.classList.add('is-invalid');
            showError('تاريخ البداية يجب أن يكون قبل أو يساوي تاريخ النهاية.');
            return;
        }

        if (btn && btn.dataset.method === 'get') {
            var reportUrl = new URL(route, window.location.origin);
            reportUrl.searchParams.set('startDate', s);
            reportUrl.searchParams.set('endDate', e);
            window.open(reportUrl.href, '_blank');
            btn.classList.add('is-loading');
            setTimeout(function () { btn.classList.remove('is-loading'); }, 1200);
            return;
        }

        var form = document.createElement('form');
        form.method = 'POST';
        form.action = route;
        form.target = '_blank';

        [['_token', getCsrf()], ['startDate', s], ['endDate', e]].forEach(function (p) {
            var i = document.createElement('input');
            i.type = 'hidden'; i.name = p[0]; i.value = p[1];
            form.appendChild(i);
        });

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);

        // brief visual feedback
        if (btn) {
            btn.classList.add('is-loading');
            setTimeout(function () { btn.classList.remove('is-loading'); }, 1200);
        }
    };

    document.getElementById('rpGrid').addEventListener('click', function (e) {
        var btn = e.target.closest('.rp-item[data-route]');
        if (btn) window.submitReport(btn.dataset.route, btn);
    });

    /* ---------- Report search ---------- */
    document.getElementById('rpSearch').addEventListener('input', function () {
        var q = this.value.trim().toLowerCase(), visibleGroups = 0;
        document.querySelectorAll('[data-group]').forEach(function (g) {
            var visible = 0;
            g.querySelectorAll('[data-item]').forEach(function (li) {
                var match = !q || li.textContent.toLowerCase().indexOf(q) !== -1;
                li.style.display = match ? '' : 'none';
                if (match) visible++;
            });
            g.style.display = visible ? '' : 'none';
            if (visible) visibleGroups++;
        });
        document.getElementById('rpEmpty').style.display = visibleGroups ? 'none' : 'block';
    });
})();
</script>
@endsection
