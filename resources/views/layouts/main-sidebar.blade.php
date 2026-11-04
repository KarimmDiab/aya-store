<!-- main-sidebar -->
<div class="app-sidebar__overlay" data-toggle="sidebar"></div>
<aside class="app-sidebar sidebar-scroll">
    <div class="main-sidebar-header active">
        <a class="desktop-logo logo-light active" href="{{ route('home') }}"><img
                src="{{ URL::asset('images/logo/aya_black.png') }}" class="main-logo" alt="logo"
                style="height: 2.5rem !important;"></a>
        <a class="desktop-logo logo-dark active" href="{{ route('home') }}"><img
                src="{{ URL::asset('assets/img/brand/logo-white.png') }}" class="main-logo dark-theme"
                alt="logo"></a>
        <a class="logo-icon mobile-logo icon-light active" href="{{ route('home') }}"><img
                src="{{ URL::asset('images/logo/small_aya_black.png') }}" class="logo-icon" alt="logo"></a>
        <a class="logo-icon mobile-logo icon-dark active" href="{{ route('home') }}"><img
                src="{{ URL::asset('assets/img/brand/favicon-white.png') }}" class="logo-icon dark-theme"
                alt="logo"></a>
    </div>
    <div class="main-sidemenu">
        <div class="app-sidebar__user clearfix">
            <div class="dropdown user-pro-body">
                <div class="">
                    <img alt="user-img" class="avatar avatar-xl brround"
                        src="{{ Auth::user()->profile_picture ? asset('images/profile_pic/' . Auth::user()->profile_picture) : (Auth::user()->gender === 'male' ? asset('images/profile_pic/male.png') : asset('images/profile_pic/female.png')) }}">

                    <span class="avatar-status profile-status bg-green"></span>
                </div>
                <div class="user-info">
                    <h4 class="font-weight-semibold mt-3 mb-0">{{ Auth::user()->name }}</h4>
                    <span class="mb-0 text-muted">{{ Auth::user()->email }}</span>
                </div>
            </div>
        </div>
        <ul class="side-menu">
            <li class="side-item side-item-category">الرئيسية</li>
            <li class="slide">
                <a class="side-menu__item" href="{{ route('home') }}"> <i class="fas fa-home"
                        style="color:gray; margin-left: 15px;"></i> <span class="side-menu__label">الصفحة
                        الرئيسية</span></a>
                <a class="side-menu__item" href="{{ url('/' . ($page = 'index')) }}"><i class="fas fa-chart-line"
                        style="color:gray; margin-left: 15px;"></i> <i class="fa-solid fa-dash"
                        style="color:gray; margin-left: 15px;"></i><span class="side-menu__label">DashBoard</span></a>
            </li>
            <li class="side-item side-item-category">الاعدادات</li>

            <li class="slide">
                <a class="side-menu__item" data-toggle="slide" href="{{ url('/' . ($page = '#')) }}"> <i
                        class="fas fa-users-cog" style="color:gray; margin-left: 15px;"></i> <span
                        class="side-menu__label"> المستخدمين و الصلاحيات</span><i
                        class="angle fe fe-chevron-down"></i></a>
                <ul class="slide-menu">
                    <li><a class="slide-item" href="{{ route('all_users') }}">المستخدمين</a></li>
                    <li><a class="slide-item" href="{{ route('all_roles') }}">المجموعات الإدارية</a></li>
                </ul>
            </li>
            <li class="slide">
                <a class="side-menu__item" data-toggle="slide" href="{{ url('/' . ($page = '#')) }}"> <i
                        class="fas fa-warehouse" style="color:gray; margin-left: 15px;"></i> <span
                        class="side-menu__label">المخازن و الفروع</span><i class="angle fe fe-chevron-down"></i></a>
                <ul class="slide-menu">
                    <li><a class="slide-item" href="{{ route('Warehouses') }}">المخازن</a></li>
                    <li><a class="slide-item" href="{{ route('branches') }}">الفروع</a></li>
                </ul>
            </li>

            <li class="slide">
                <a class="side-menu__item" data-toggle="slide" href="{{ url('/' . ($page = '#')) }}"> <i
                        class="fas fa-money-bill-wave" style="color:gray; margin-left: 15px;"></i> <span
                        class="side-menu__label">المصروفات</span><i class="angle fe fe-chevron-down"></i></a>
                <ul class="slide-menu">
                    <li><a class="slide-item" href="{{ route('expenses') }}">بنود المصروفات</a></li>
                    <li><a class="slide-item" href="{{ route('expenses_details') }}">إضافة مصاريف</a></li>
                    <li><a class="slide-item" href="{{ route('showDetails') }}"> جميع المصاريف</a></li>
                </ul>
            </li>

            <li class="slide">
                <a class="side-menu__item" data-toggle="slide" href="{{ url('/' . ($page = '#')) }}"><i class="fas fa-user"
                        style="color:gray; margin-left: 15px;"></i><span class="side-menu__label">العملاء</span><i
                        class="angle fe fe-chevron-down"></i></a>
                <ul class="slide-menu">
                    <li><a class="slide-item" href="{{ route('all_customers') }}">جميع العملاء</a></li>
                    <li><a class="slide-item" href="{{ route('credit_customers') }}"> عملاء بالاجل</a></li>
                </ul>
            </li>

            <li class="slide">
                <a class="side-menu__item" data-toggle="slide" href="{{ url('/' . ($page = '#')) }}"><i
                        class="fas fa-truck" style="color:gray; margin-left: 15px;"></i><span
                        class="side-menu__label">الموردين و المشتريات</span><i
                        class="angle fe fe-chevron-down"></i></a>
                <ul class="slide-menu">
                    <li><a class="slide-item" href="{{ route('suppliers') }}">الموردين</a></li>
                    <li><a class="slide-item" href="{{ route('credit_suplliers') }}">موردين الأجل</a></li>
                    <li><a class="slide-item" href="{{ route('purchase_invoices') }}">فواتير المشتريات</a></li>
                    <li><a class="slide-item" href="{{ route('editPricesInPurchaseInvoice') }}">تعديل أسعار فواتير المشتريات</a></li>

                </ul>
            </li>

            <li class="slide">
                <a class="side-menu__item" data-toggle="slide" href="{{ url('/' . ($page = '#')) }}"><i
                        class="fas fa-shopping-cart" style="color:gray; margin-left: 15px;"></i><span
                        class="side-menu__label">المبيعات</span><i class="angle fe fe-chevron-down"></i></a>
                <ul class="slide-menu">
                    <li><a class="slide-item" href="{{ route('sales_invoice') }}">عملبات البيع</a></li>
                    <li><a class="slide-item" href="{{ route('all_sales') }}">فواتير المبيعات</a></li>


                </ul>
            </li>
            <li class="slide">
                <a class="side-menu__item" data-toggle="slide" href="{{ url('/' . ($page = '#')) }}"><i
                        class="fas fa-box" style="color:gray; margin-left: 15px;"></i><span
                        class="side-menu__label">المنتجات</span><i class="angle fe fe-chevron-down"></i></a>
                <ul class="slide-menu">
                    <li><a class="slide-item" href="{{ route('categories') }}">اصناف المنتجات</a></li>
                    <li><a class="slide-item" href="{{ route('products') }}"> المنتجات</a></li>
                </ul>
            </li>
            <li class="slide">
                <a class="side-menu__item" href="{{ route('home_reports') }}"><i class="fas fa-box"
                        style="color:gray; margin-left: 15px;"></i><span class="side-menu__label">التقارير</span><i
                        class="angle fe fe-chevron-down"></i></a>
            </li>


        </ul>
    </div>
</aside>
<!-- main-sidebar -->
