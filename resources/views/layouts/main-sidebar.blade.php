@php
    /* =====================================================================
     * MENU DEFINITION — edit here to add / rename / reorder items.
     *   - 'route' : route name        - 'path'  : url path (for request()->is)
     *   - 'also'  : extra route patterns that should keep this item highlighted
     * ===================================================================== */
    $menu = [
        [
            'category' => 'الرئيسية',
            'items' => [
                ['label' => 'الصفحة الرئيسية', 'icon' => 'fa-home',       'route' => 'home'],
                ['label' => 'DashBoard',       'icon' => 'fa-chart-line', 'url' => url('/index'), 'path' => 'index'],
            ],
        ],
        [
            'category' => 'المبيعات والعملاء',
            'items' => [
                ['label' => 'العملاء', 'icon' => 'fa-user', 'children' => [
                    ['label' => 'جميع العملاء', 'route' => 'all_customers', 'also' => ['customer_profile']],
                    ['label' => 'عملاء الأجل',  'route' => 'credit_customers'],
                ]],
                ['label' => 'المبيعات', 'icon' => 'fa-shopping-cart', 'children' => [
                    ['label' => 'عمليات البيع',    'route' => 'sales_invoice'],
                    ['label' => 'فواتير المبيعات', 'route' => 'all_sales'],
                    ['label' => 'مرتجعات المبيعات', 'route' => 'sales.returns.index', 'also' => ['sales.returns.*']],
                ]],
            ],
        ],
        [
            'category' => 'المشتريات والمخزون',
            'items' => [
                ['label' => 'الموردين و المشتريات', 'icon' => 'fa-truck', 'children' => [
                    ['label' => 'الموردين',                     'route' => 'suppliers', 'also' => ['supplier_profile']],
                    ['label' => 'موردين الأجل',                 'route' => 'credit_suplliers'],
                    ['label' => 'فواتير المشتريات',             'route' => 'purchase_invoices'],
                ]],
                ['label' => 'المنتجات', 'icon' => 'fa-box', 'children' => [
                    ['label' => 'تصنيفات المنتجات', 'route' => 'categories'],
                    ['label' => 'المنتجات',         'route' => 'products'],
                ]],
                ['label' => 'المخازن و الفروع', 'icon' => 'fa-warehouse', 'children' => [
                    ['label' => 'المخازن', 'route' => 'Warehouses'],
                    ['label' => 'الفروع',  'route' => 'branches'],
                ]],
            ],
        ],
        [
            'category' => 'المالية والتقارير',
            'items' => [
                ['label' => 'المصروفات', 'icon' => 'fa-money-bill-wave', 'children' => [
                    ['label' => 'بنود المصروفات', 'route' => 'expenses'],
                    ['label' => 'إضافة مصاريف',   'route' => 'expenses_details'],
                    ['label' => 'جميع المصاريف',  'route' => 'showDetails'],
                ]],
                ['label' => 'التقارير', 'icon' => 'fa-chart-pie', 'route' => 'home_reports'],
            ],
        ],
        [
            'category' => 'الإدارة',
            'items' => [
                ['label' => 'المستخدمين و الصلاحيات', 'icon' => 'fa-users-cog', 'children' => [
                    ['label' => 'المستخدمين',         'route' => 'all_users'],
                    ['label' => 'المجموعات الإدارية', 'route' => 'all_roles'],
                ]],
            ],
        ],
    ];

    // Is a single menu entry (link or child) the current page?
    $isActive = function (array $entry) {
        if (isset($entry['path']) && request()->is($entry['path'])) {
            return true;
        }
        $patterns = array_merge(isset($entry['route']) ? [$entry['route']] : [], $entry['also'] ?? []);
        return $patterns ? request()->routeIs(...$patterns) : false;
    };

    // Current user + avatar
    $authUser = Auth::user();
    $defaultAvatar = asset('images/profile_pic/' . ($authUser->gender === 'male' ? 'male.png' : 'female.png'));
    $avatar = $authUser->profile_picture ? asset('images/profile_pic/' . $authUser->profile_picture) : $defaultAvatar;
@endphp

<style>
    /* ---------- User block ---------- */
    .app-sidebar .user-info h4,
    .app-sidebar .user-info span { display: block; max-width: 210px; margin-left: auto; margin-right: auto; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    /* ---------- Menu search ---------- */
    .app-sidebar .menu-search { position: relative; margin: 10px 16px 8px; }
    .app-sidebar .menu-search input {
        width: 100%; font-size: 13px; padding: 8px 12px; padding-inline-start: 34px;
        border: 1px solid rgba(128,128,128,.25); border-radius: 10px;
        background: rgba(128,128,128,.10); color: inherit; outline: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .app-sidebar .menu-search input:focus { border-color: #0162e8; box-shadow: 0 0 0 3px rgba(1,98,232,.12); }
    .app-sidebar .menu-search i { position: absolute; inset-inline-start: 13px; top: 50%; transform: translateY(-50%); font-size: 12px; opacity: .55; pointer-events: none; }
    .sidenav-toggled .app-sidebar .menu-search,
    .main-sidebar-hide .app-sidebar .menu-search { display: none; }

    /* ---------- Menu icons ---------- */
    .app-sidebar .menu-icon { width: 22px; margin-inline-end: 12px; text-align: center; font-size: 15px; opacity: .65; transition: opacity .15s ease, color .15s ease; }
    .app-sidebar .side-menu__item:hover .menu-icon,
    .app-sidebar .side-menu__item.active .menu-icon { opacity: 1; color: #0162e8; }

    /* ---------- Active states ---------- */
    .app-sidebar .side-menu__item.active { background: rgba(1,98,232,.08); border-inline-start: 3px solid #0162e8; }
    .app-sidebar .side-menu__item.active .side-menu__label { color: #0162e8; font-weight: 700; }
    .app-sidebar .slide-item.active { color: #0162e8; font-weight: 700; }

    /* ---------- Search "no results" ---------- */
    .app-sidebar .menu-empty { padding: 18px 20px; text-align: center; font-size: 13px; opacity: .6; list-style: none; }
</style>

<!-- main-sidebar -->
<div class="app-sidebar__overlay" data-toggle="sidebar"></div>
<aside class="app-sidebar sidebar-scroll" aria-label="القائمة الرئيسية">
    <div class="main-sidebar-header active">
        <a class="desktop-logo logo-light active" href="{{ route('home') }}">
            <img src="{{ URL::asset('images/logo/aya_black.png') }}" class="main-logo" alt="معرض أيه" style="height: 2.5rem !important;">
        </a>
        <a class="desktop-logo logo-dark active" href="{{ route('home') }}">
            <img src="{{ URL::asset('assets/img/brand/logo-white.png') }}" class="main-logo dark-theme" alt="معرض أيه">
        </a>
        <a class="logo-icon mobile-logo icon-light active" href="{{ route('home') }}">
            <img src="{{ URL::asset('images/logo/small_aya_black.png') }}" class="logo-icon" alt="معرض أيه">
        </a>
        <a class="logo-icon mobile-logo icon-dark active" href="{{ route('home') }}">
            <img src="{{ URL::asset('assets/img/brand/favicon-white.png') }}" class="logo-icon dark-theme" alt="معرض أيه">
        </a>
    </div>

    <div class="main-sidemenu">
        {{-- ---------- User ---------- --}}
        <div class="app-sidebar__user clearfix">
            <div class="dropdown user-pro-body">
                <div>
                    <img alt="{{ $authUser->name }}" class="avatar avatar-xl brround" src="{{ $avatar }}"
                         onerror="this.onerror=null;this.src='{{ $defaultAvatar }}'">
                    <span class="avatar-status profile-status bg-green"></span>
                </div>
                <div class="user-info">
                    <h4 class="font-weight-semibold mt-3 mb-0" title="{{ $authUser->name }}">{{ $authUser->name }}</h4>
                    <span class="mb-0 text-muted" title="{{ $authUser->email }}">{{ $authUser->email }}</span>
                </div>
            </div>
        </div>

        {{-- ---------- Search ---------- --}}
        <div class="menu-search">
            <i class="fas fa-search"></i>
            <input type="search" id="menuSearch" placeholder="ابحث في القائمة..." autocomplete="off" aria-label="ابحث في القائمة">
        </div>

        {{-- ---------- Menu ---------- --}}
        <ul class="side-menu">
            @foreach ($menu as $section)
                <li class="side-item side-item-category">{{ $section['category'] }}</li>

                @foreach ($section['items'] as $item)
                    @if (isset($item['children']))
                        @php $open = collect($item['children'])->contains(fn ($child) => $isActive($child)); @endphp
                        <li class="slide {{ $open ? 'is-expanded' : '' }}" data-open="{{ $open ? 1 : 0 }}">
                            <a class="side-menu__item {{ $open ? 'active' : '' }}" data-toggle="slide" href="javascript:void(0);"
                               aria-expanded="{{ $open ? 'true' : 'false' }}">
                                <i class="fas {{ $item['icon'] }} menu-icon"></i>
                                <span class="side-menu__label">{{ $item['label'] }}</span>
                                <i class="angle fe fe-chevron-down"></i>
                            </a>
                            <ul class="slide-menu" @if ($open) style="display: block;" @endif>
                                @foreach ($item['children'] as $child)
                                    @php $childActive = $isActive($child); @endphp
                                    <li>
                                        <a class="slide-item {{ $childActive ? 'active' : '' }}"
                                           href="{{ route($child['route']) }}"
                                           @if ($childActive) aria-current="page" @endif>{{ $child['label'] }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @else
                        @php $active = $isActive($item); @endphp
                        <li class="slide">
                            <a class="side-menu__item {{ $active ? 'active' : '' }}"
                               href="{{ $item['url'] ?? route($item['route']) }}"
                               @if ($active) aria-current="page" @endif>
                                <i class="fas {{ $item['icon'] }} menu-icon"></i>
                                <span class="side-menu__label">{{ $item['label'] }}</span>
                            </a>
                        </li>
                    @endif
                @endforeach
            @endforeach

            <li class="menu-empty" id="menuNoResults" style="display: none;">لا توجد نتائج مطابقة</li>
        </ul>
    </div>
</aside>
<!-- main-sidebar -->

<script>
    // Sidebar search: filters items, expands matching groups, restores state when cleared
    document.addEventListener('DOMContentLoaded', function () {
        var input = document.getElementById('menuSearch');
        var menu  = document.querySelector('.app-sidebar .side-menu');
        var empty = document.getElementById('menuNoResults');
        if (!input || !menu) return;

        var cats   = Array.prototype.slice.call(menu.querySelectorAll('.side-item-category'));
        var slides = Array.prototype.slice.call(menu.querySelectorAll('li.slide'));

        // Arabic-friendly normalisation (ignores tashkeel and أ/إ/آ, ة/ه, ى/ي differences)
        function norm(s) {
            return (s || '').toLowerCase()
                .replace(/[\u064B-\u0652\u0640]/g, '')
                .replace(/[أإآ]/g, 'ا').replace(/ة/g, 'ه').replace(/ى/g, 'ي')
                .trim();
        }

        function reset() {
            cats.forEach(function (c) { c.style.display = ''; });
            slides.forEach(function (li) {
                li.style.display = '';
                var sub = li.querySelector('.slide-menu');
                if (sub) {
                    var wasOpen = li.getAttribute('data-open') === '1';
                    Array.prototype.forEach.call(sub.querySelectorAll('li'), function (x) { x.style.display = ''; });
                    sub.style.display = wasOpen ? 'block' : 'none';
                    li.classList.toggle('is-expanded', wasOpen);
                }
            });
            empty.style.display = 'none';
        }

        function run() {
            var q = norm(input.value);
            if (!q) { reset(); return; }

            var any = false;
            cats.forEach(function (c) { c.style.display = 'none'; });

            slides.forEach(function (li) {
                var labelEl = li.querySelector('.side-menu__label');
                var show = norm(labelEl ? labelEl.textContent : '').indexOf(q) !== -1;
                var sub = li.querySelector('.slide-menu');

                if (sub) {
                    var childMatch = false;
                    Array.prototype.forEach.call(sub.querySelectorAll('li'), function (x) {
                        var m = show || norm(x.textContent).indexOf(q) !== -1;
                        x.style.display = m ? '' : 'none';
                        childMatch = childMatch || m;
                    });
                    show = childMatch;
                    sub.style.display = show ? 'block' : 'none';
                    li.classList.toggle('is-expanded', show);
                }
                li.style.display = show ? '' : 'none';
                any = any || show;
            });
            empty.style.display = any ? 'none' : '';
        }

        input.addEventListener('input', run);
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { input.value = ''; reset(); }
        });

        // Bring the active page into view inside the scrollable sidebar
        var current = menu.querySelector('.slide-item.active, .side-menu__item.active');
        if (current && current.scrollIntoView) {
            setTimeout(function () { current.scrollIntoView({ block: 'nearest' }); }, 150);
        }
    });
</script>
