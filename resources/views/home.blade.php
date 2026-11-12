@extends('layouts.master')
<title>معرض أيه - الصفحة الرئيسية</title>

@section('css')
<!-- Internal Gallery css -->
<link href="{{ URL::asset('assets/plugins/gallery/gallery.css') }}" rel="stylesheet">

<style>
:root {
    --hm-primary: #2f5bea;
    --hm-primary-dark: #1f3fb8;
    --hm-primary-soft: #eef2ff;
    --hm-text: #1f2937;
    --hm-muted: #6b7280;
    --hm-border: #e5e7eb;
    --hm-surface: #ffffff;
    --hm-radius: 14px;
    --hm-shadow: 0 1px 3px rgba(16, 24, 40, .06), 0 1px 2px rgba(16, 24, 40, .04);
    --hm-shadow-hover: 0 12px 28px rgba(16, 24, 40, .12);
}

/* ---------- Hero ---------- */
.hm-hero {
    background: linear-gradient(135deg, var(--hm-primary) 0%, var(--hm-primary-dark) 100%);
    border-radius: var(--hm-radius);
    color: #fff;
    padding: 28px 32px;
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    align-items: center;
    justify-content: space-between;
    box-shadow: var(--hm-shadow);
    margin-bottom: 24px;
}
.hm-hero h2 { margin: 0 0 6px; font-weight: 700; font-size: 1.6rem; color: #fff; }
.hm-hero p  { margin: 0; opacity: .85; font-size: .95rem; }

.hm-search {
    position: relative;
    min-width: 280px;
    flex: 0 1 360px;
}
.hm-search input {
    width: 100%;
    height: 46px;
    border: 0;
    border-radius: 999px;
    padding: 0 48px 0 18px;
    font-size: .95rem;
    color: var(--hm-text);
    box-shadow: 0 4px 14px rgba(0, 0, 0, .15);
}
.hm-search input:focus { outline: 3px solid rgba(255, 255, 255, .5); }
.hm-search i {
    position: absolute;
    inset-inline-end: 18px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--hm-muted);
    pointer-events: none;
}

/* ---------- Grid ---------- */
.hm-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 22px;
}

/* ---------- Card ---------- */
.hm-card {
    background: var(--hm-surface);
    border: 1px solid var(--hm-border);
    border-radius: var(--hm-radius);
    box-shadow: var(--hm-shadow);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
}
.hm-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--hm-shadow-hover);
    border-color: #c7d2fe;
}
.hm-card-head {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 18px 20px;
    border-bottom: 1px solid var(--hm-border);
}
.hm-card-icon {
    width: 48px;
    height: 48px;
    flex: 0 0 48px;
    border-radius: 12px;
    background: var(--hm-primary-soft);
    display: grid;
    place-items: center;
}
.hm-card-icon img { width: 28px; height: 28px; object-fit: contain; }
.hm-card-title { margin: 0; font-size: 1.05rem; font-weight: 700; color: var(--hm-text); }
.hm-card-sub   { margin: 2px 0 0; font-size: .8rem; color: var(--hm-muted); }

.hm-card-body {
    padding: 14px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex: 1;
}

/* ---------- Link rows ---------- */
.hm-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    border-radius: 10px;
    color: var(--hm-text);
    font-weight: 600;
    font-size: .95rem;
    text-decoration: none;
    transition: background .2s ease, color .2s ease, padding .2s ease;
}
.hm-link .hm-link-icon {
    width: 34px;
    height: 34px;
    border-radius: 9px;
    background: var(--hm-primary-soft);
    color: var(--hm-primary);
    display: grid;
    place-items: center;
    font-size: .9rem;
    transition: background .2s ease, color .2s ease;
}
.hm-link .hm-arrow {
    margin-inline-start: auto;
    font-size: .75rem;
    color: #9ca3af;
    transition: transform .2s ease, color .2s ease;
}
.hm-link:hover,
.hm-link:focus-visible {
    background: var(--hm-primary-soft);
    color: var(--hm-primary-dark);
    text-decoration: none;
    outline: none;
}
.hm-link:hover .hm-link-icon,
.hm-link:focus-visible .hm-link-icon { background: var(--hm-primary); color: #fff; }
.hm-link:hover .hm-arrow,
.hm-link:focus-visible .hm-arrow     { color: var(--hm-primary); transform: translateX(-4px); }
.hm-link:focus-visible { box-shadow: 0 0 0 3px rgba(47, 91, 234, .35); }

/* ---------- Empty state ---------- */
.hm-empty {
    display: none;
    text-align: center;
    padding: 60px 20px;
    color: var(--hm-muted);
}
.hm-empty i { font-size: 2.4rem; margin-bottom: 12px; color: #cbd5e1; }

.main-footer {
    position: fixed;
    right: 0;
    bottom: 0;
    left: 240px;
    z-index: 10;
    margin-top: 0;
    width: 100%;
}
.app.sidenav-toggled .main-footer { left: 80px; }
.main-content.app-content > .container-fluid { padding-bottom: 50px; }

@media (max-width: 576px) {
    .hm-hero { padding: 22px 20px; }
    .hm-grid { grid-template-columns: 1fr; }
}
@media (max-width: 767px) {
    .main-footer { left: 0; }
}
@media (prefers-reduced-motion: reduce) {
    .hm-card, .hm-link, .hm-link .hm-arrow { transition: none; }
    .hm-card:hover { transform: none; }
}
</style>
@endsection

@section('page-header')
<!-- breadcrumb -->
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto">الصفحة الرئيسية</h4>
        </div>
    </div>
</div>
<!-- breadcrumb -->
@endsection

@section('content')
@php
    /*
     | Each module = one card. Add / remove modules or links here only.
     | 'icon' is the image in public/images/icons, 'links' => [label, route name, Font Awesome icon]
     */
    $modules = [
        [
            'title' => 'المستخدمين و الصلاحيات',
            'sub'   => 'إدارة الحسابات وصلاحيات الوصول',
            'icon'  => 'user_icon.png',
            'links' => [
                ['جميع المستخدمين',   'all_users', 'fa-users'],
                ['المجموعات الإدارية', 'all_users', 'fa-user-shield'], // TODO: point to the roles route
            ],
        ],
        [
            'title' => 'المخازن و الفروع',
            'sub'   => 'متابعة المخازن وفروع النشاط',
            'icon'  => 'store_icon.png',
            'links' => [
                ['المخازن', 'Warehouses', 'fa-warehouse'],
                ['الفروع',  'branches',   'fa-store'],
            ],
        ],
        [
            'title' => 'المصروفات',
            'sub'   => 'تسجيل ومراجعة المصروفات',
            'icon'  => '3d-wallet.png',
            'links' => [
                ['بنود المصروفات', 'expenses',         'fa-list-ul'],
                ['إضافة مصروفات',  'expenses_details', 'fa-plus-circle'],
                ['جميع المصروفات', 'showDetails',      'fa-file-invoice-dollar'],
            ],
        ],
        [
            'title' => 'العملاء',
            'sub'   => 'بيانات العملاء والمديونيات',
            'icon'  => 'customer.png',
            'links' => [
                ['جميع العملاء', 'all_customers',    'fa-users'],
                ['عملاء بالآجل', 'credit_customers', 'fa-hand-holding-usd'],
            ],
        ],
        [
            'title' => 'الموردين و المشتريات',
            'sub'   => 'الموردون وفواتير الشراء',
            'icon'  => 'supplier.png',
            'links' => [
                ['الموردين',         'suppliers',         'fa-truck'],
                ['موردين بالآجل',    'credit_suplliers',  'fa-hand-holding-usd'],
                ['فواتير المشتريات', 'purchase_invoices', 'fa-file-invoice'],
            ],
        ],
        [
            'title' => 'المبيعات',
            'sub'   => 'عمليات البيع والفواتير',
            'icon'  => 'flash-sale.png',
            'links' => [
                ['عمليات البيع',    'sales_invoice', 'fa-cash-register'],
                ['فواتير المبيعات', 'all_sales',     'fa-receipt'],
            ],
        ],
        [
            'title' => 'المنتجات',
            'sub'   => 'الأصناف والمنتجات المتاحة',
            'icon'  => 'products.png',
            'links' => [
                ['أصناف المنتجات', 'categories', 'fa-tags'],
                ['المنتجات',       'products',   'fa-box-open'],
            ],
        ],
    ];
@endphp

<!-- row -->
<div class="row row-sm">
    <div class="col-12">

        <!-- Hero -->
        <section class="hm-hero" aria-label="الترحيب">
            <div>
                <h2><i class="fas fa-home ml-2"></i> مرحباً بك في لوحة التحكم</h2>
                <p>اختر القسم الذي تريد العمل عليه، أو ابحث عنه مباشرة.</p>
            </div>

            <div class="hm-search">
                <input type="search" id="moduleSearch" placeholder="ابحث عن قسم أو صفحة..."
                       autocomplete="off" aria-label="بحث في الأقسام">
                <i class="fas fa-search"></i>
            </div>
        </section>

        <!-- Modules -->
        <div class="hm-grid" id="moduleGrid">
            @foreach ($modules as $module)
                <article class="hm-card" data-search="{{ $module['title'] }} {{ collect($module['links'])->pluck(0)->implode(' ') }}">
                    <header class="hm-card-head">
                        <div class="hm-card-icon">
                            <img src="{{ asset('images/icons/' . $module['icon']) }}" alt="" loading="lazy">
                        </div>
                        <div>
                            <h5 class="hm-card-title">{{ $module['title'] }}</h5>
                            <p class="hm-card-sub">{{ $module['sub'] }}</p>
                        </div>
                    </header>

                    <nav class="hm-card-body" aria-label="{{ $module['title'] }}">
                        @foreach ($module['links'] as [$label, $routeName, $faIcon])
                            <a href="{{ route($routeName) }}" class="hm-link">
                                <span class="hm-link-icon"><i class="fas {{ $faIcon }}"></i></span>
                                <span>{{ $label }}</span>
                                <i class="fas fa-chevron-left hm-arrow"></i>
                            </a>
                        @endforeach
                    </nav>
                </article>
            @endforeach
        </div>

        <!-- Empty state for search -->
        <div class="hm-empty" id="moduleEmpty">
            <i class="fas fa-search d-block"></i>
            <h5>لا توجد نتائج مطابقة</h5>
            <p class="mb-0">جرّب كلمة بحث مختلفة.</p>
        </div>

    </div>
</div>
<!-- row closed -->
@endsection

@section('js')
<script>
(function () {
    var input = document.getElementById('moduleSearch');
    var cards = document.querySelectorAll('#moduleGrid .hm-card');
    var empty = document.getElementById('moduleEmpty');

    input.addEventListener('input', function () {
        var q = input.value.trim().toLowerCase();
        var visible = 0;

        cards.forEach(function (card) {
            var match = !q || card.dataset.search.toLowerCase().indexOf(q) !== -1;
            card.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        empty.style.display = visible ? 'none' : 'block';
    });
})();
</script>
@endsection
