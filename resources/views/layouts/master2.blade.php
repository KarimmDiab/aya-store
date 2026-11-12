<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="لوحة إدارة معرض آية">
    <title>معرض آية | لوحة الإدارة</title>
    @include('layouts.head')
    <style>
        :root{--navy:#14243b;--navy-deep:#0c182a;--accent:#3b82f6;--surface:#fff;--canvas:#f4f7fb;--ink:#18283e;--muted:#75839a;--line:#e8edf4}
        body.aya-layout{background:var(--canvas)!important;color:var(--ink);font-family:'Cairo','Tajawal',system-ui,-apple-system,'Segoe UI',sans-serif;min-height:100vh}
        .aya-header{position:relative;z-index:1050;background:var(--navy-deep);box-shadow:0 8px 30px rgba(10,25,45,.12)}
        .aya-header-inner{margin:auto;min-height:74px;padding:0 26px;display:flex;align-items:center;gap:22px}
        .aya-brand{display:flex;align-items:center;gap:12px;color:#fff!important;text-decoration:none!important;flex-shrink:0}
        .aya-brand-mark{height:42px;width:42px;border-radius:13px;background:linear-gradient(145deg,#60a5fa,#2563eb);display:grid;place-items:center;font-size:21px;box-shadow:0 6px 18px rgba(37,99,235,.27)}
        .aya-brand-name{font-size:18px;font-weight:800;line-height:1.3}.aya-brand-sub{display:block;font-size:10px;color:#a6b5cc;font-weight:500;letter-spacing:.4px;margin-top:3px}
        .aya-nav-toggle{display:none;border:1px solid #3a4b61;color:#fff;background:transparent;border-radius:10px;padding:9px 12px;margin-right:auto;cursor:pointer}
        .aya-nav{display:flex;align-items:center;gap:3px;flex:1;min-width:0}
        .aya-nav .nav-item{position:relative;list-style:none}.aya-nav .nav-link{display:flex;align-items:center;gap:8px;color:#cbd5e1!important;font-size:12.5px;font-weight:650;padding:12px 10px!important;border-radius:9px;white-space:nowrap;transition:background .18s,color .18s}
        .aya-nav .nav-link:hover,.aya-nav .nav-item.show>.nav-link,.aya-nav .nav-link:focus{color:#fff!important;background:rgba(255,255,255,.09)}
        .aya-nav .nav-link i{font-size:15px;color:#93b7ec}.aya-nav .dropdown-toggle::after{margin-right:3px;margin-left:0;opacity:.65}
        .aya-nav .dropdown-menu{right:0;left:auto;top:calc(100% + 9px);min-width:225px;border:1px solid var(--line);border-radius:13px;background:#fff;padding:8px;box-shadow:0 18px 46px rgba(14,33,56,.17);text-align:right}
        .aya-nav .dropdown-menu:before{content:'';position:absolute;top:-10px;right:0;left:0;height:10px}
        .aya-nav .dropdown-item{display:flex;align-items:center;gap:11px;border-radius:8px;color:#31435d;padding:11px 12px;font-size:13px;font-weight:600;transition:background .15s,color .15s}
        .aya-nav .dropdown-item:hover,.aya-nav .dropdown-item:focus{background:#eef5ff;color:#2563eb}.aya-nav .dropdown-item i{width:18px;text-align:center;color:#64748b}.aya-nav .dropdown-item:hover i{color:#2563eb}
        .aya-nav .dropdown-divider{border-color:var(--line)}
        .aya-header-right{margin-right:auto;display:flex;align-items:center;gap:11px;flex-shrink:0}.aya-status{display:flex;align-items:center;gap:7px;color:#a8bbd4;font-size:11px}.aya-status-dot{height:7px;width:7px;border-radius:50%;background:#34d399;box-shadow:0 0 0 4px rgba(52,211,153,.12)}
        .aya-workspace-strip{background:#fff;border-bottom:1px solid var(--line)}.aya-workspace-inner{max-width:1600px;margin:auto;padding:13px 27px;display:flex;align-items:center;justify-content:space-between;gap:12px}
        .aya-workspace-title{font-size:13px;font-weight:700;color:#334155}.aya-workspace-caption{font-size:11px;color:var(--muted)}
        .aya-content{padding:22px 24px 36px;min-height:calc(100vh - 132px)}
        .aya-content .main-content,.aya-content .container-fluid{max-width:100%}
        .aya-content .card{border:1px solid var(--line);border-radius:13px;box-shadow:0 4px 18px rgba(23,42,69,.035)}
        .aya-content .btn{border-radius:8px}.aya-content .form-control{border-radius:8px}
        @media(max-width:1450px){.aya-header-inner{gap:12px;padding:0 17px}.aya-nav .nav-link{font-size:11.5px;padding:11px 7px!important;gap:5px}}
        @media(max-width:1199px){.aya-header-inner{flex-wrap:wrap;min-height:68px;padding:12px 18px}.aya-nav-toggle{display:block}.aya-header-right{display:none}.aya-nav{display:none;flex-basis:100%;width:100%;flex-direction:column;align-items:stretch;padding:12px 0 4px;border-top:1px solid rgba(255,255,255,.1)}.aya-nav.is-open{display:flex}.aya-nav .nav-link{font-size:14px;padding:13px!important;justify-content:space-between}.aya-nav .dropdown-menu{position:static!important;transform:none!important;float:none;width:100%;box-shadow:none;border:0;background:#172a43;margin:2px 0 8px;padding:5px}.aya-nav .dropdown-item{color:#e1e9f4}.aya-nav .dropdown-item i{color:#a6bad3}.aya-nav .dropdown-item:hover,.aya-nav .dropdown-item:focus{background:#254064;color:#fff}.aya-nav .dropdown-divider{border-color:#30465f}}
        @media(max-width:575px){.aya-workspace-inner{padding:12px 16px}.aya-content{padding:15px 10px 28px}.aya-brand-name{font-size:16px}}
        @media(prefers-reduced-motion:reduce){.aya-layout *{transition:none!important}}
    </style>
    @yield('css')
</head>
<body class="main-body aya-layout">
<header class="aya-header">
    <div class="aya-header-inner">
        <a class="aya-brand" href="{{ route('home') }}" aria-label="الرئيسية - معرض آية">
            <span class="aya-brand-mark"><i class="fas fa-store"></i></span>
            <span class="aya-brand-name">معرض آية<span class="aya-brand-sub">نظام إدارة الأعمال</span></span>
        </a>
        <button class="aya-nav-toggle" id="ayaNavToggle" type="button" aria-controls="ayaMainNav" aria-expanded="false" aria-label="فتح القائمة"><i class="fas fa-bars"></i></button>
        <nav class="aya-nav" id="ayaMainNav" aria-label="القائمة الرئيسية">
            <div class="nav-item dropdown"><a class="nav-link dropdown-toggle" href="#" id="salesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-shopping-bag"></i> المبيعات</a><div class="dropdown-menu" aria-labelledby="salesDropdown"><a class="dropdown-item" href="{{ route('sales_invoice') }}"><i class="fas fa-plus-circle"></i> إنشاء فاتورة مبيعات</a><a class="dropdown-item" href="{{ route('all_sales') }}"><i class="fas fa-file-invoice"></i> جميع فواتير المبيعات</a><div class="dropdown-divider"></div><a class="dropdown-item" href="{{ route('sales.returns.index') }}"><i class="fas fa-undo-alt"></i> مرتجعات المبيعات</a></div></div>
            <div class="nav-item dropdown"><a class="nav-link dropdown-toggle" href="#" id="productsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-box-open"></i> المنتجات</a><div class="dropdown-menu" aria-labelledby="productsDropdown"><a class="dropdown-item" href="{{ route('products') }}"><i class="fas fa-box"></i> جميع المنتجات</a><a class="dropdown-item" href="{{ route('categories') }}"><i class="fas fa-tags"></i> أصناف المنتجات</a></div></div>
            <div class="nav-item dropdown"><a class="nav-link dropdown-toggle" href="#" id="customersDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-users"></i> العملاء</a><div class="dropdown-menu" aria-labelledby="customersDropdown"><a class="dropdown-item" href="{{ route('all_customers') }}"><i class="fas fa-address-book"></i> جميع العملاء</a><a class="dropdown-item" href="{{ route('credit_customers') }}"><i class="fas fa-hand-holding-usd"></i> عملاء بالأجل</a></div></div>
            <div class="nav-item dropdown"><a class="nav-link dropdown-toggle" href="#" id="suppliersDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-truck-loading"></i> الموردون والمشتريات</a><div class="dropdown-menu" aria-labelledby="suppliersDropdown"><a class="dropdown-item" href="{{ route('suppliers') }}"><i class="fas fa-truck"></i> الموردون</a><a class="dropdown-item" href="{{ route('credit_suplliers') }}"><i class="fas fa-file-invoice-dollar"></i> موردو الأجل</a></div></div>
            <div class="nav-item dropdown"><a class="nav-link dropdown-toggle" href="#" id="warehousesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-warehouse"></i> المخازن والفروع</a><div class="dropdown-menu" aria-labelledby="warehousesDropdown"><a class="dropdown-item" href="{{ route('Warehouses') }}"><i class="fas fa-warehouse"></i> المخازن</a><a class="dropdown-item" href="{{ route('branches') }}"><i class="fas fa-code-branch"></i> الفروع</a></div></div>
            <div class="nav-item dropdown"><a class="nav-link dropdown-toggle" href="#" id="expensesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-wallet"></i> المصروفات</a><div class="dropdown-menu" aria-labelledby="expensesDropdown"><a class="dropdown-item" href="{{ route('expenses') }}"><i class="fas fa-list-alt"></i> بنود المصروفات</a><a class="dropdown-item" href="{{ route('expenses_details') }}"><i class="fas fa-plus"></i> إضافة مصروف</a><a class="dropdown-item" href="{{ route('showDetails') }}"><i class="fas fa-receipt"></i> جميع المصروفات</a></div></div>
            <div class="nav-item dropdown"><a class="nav-link dropdown-toggle" href="#" id="usersDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-user-shield"></i> الإدارة</a><div class="dropdown-menu" aria-labelledby="usersDropdown"><a class="dropdown-item" href="{{ route('all_users') }}"><i class="fas fa-user-friends"></i> المستخدمون</a><a class="dropdown-item" href="{{ route('all_roles') }}"><i class="fas fa-key"></i> المجموعات الإدارية والصلاحيات</a></div></div>
        </nav>
        <div class="aya-header-right"><span class="aya-status"><span class="aya-status-dot"></span> لوحة الإدارة</span></div>
    </div>
</header>
<main class="aya-content" id="main-content">@yield('page-header') @yield('content')</main>
@include('layouts.footer-scripts')
@yield('js')
<script>
(function(){
    var button=document.getElementById('ayaNavToggle');
    var nav=document.getElementById('ayaMainNav');
    if(button&&nav){button.addEventListener('click',function(){var expanded=button.getAttribute('aria-expanded')==='true';button.setAttribute('aria-expanded',String(!expanded));nav.classList.toggle('is-open',!expanded);});}
    // Bootstrap 4 dropdowns are initialized by bootstrap.bundle.js / bootstrap.js + Popper.
})();
</script>
</body>
</html>
