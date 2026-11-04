<!DOCTYPE html>
<html lang="en">
<head>
		<meta charset="UTF-8">
		<meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="Description" content="Bootstrap Responsive Admin Web Dashboard HTML5 Template">
		<meta name="Author" content="Spruko Technologies Private Limited">
		<meta name="Keywords" content="admin,admin dashboard,admin dashboard template,admin panel template,admin template,admin theme,bootstrap 4 admin template,bootstrap 4 dashboard,bootstrap admin,bootstrap admin dashboard,bootstrap admin panel,bootstrap admin template,bootstrap admin theme,bootstrap dashboard,bootstrap form template,bootstrap panel,bootstrap ui kit,dashboard bootstrap 4,dashboard design,dashboard html,dashboard template,dashboard ui kit,envato templates,flat ui,html,html and css templates,html dashboard template,html5,jquery html,premium,premium quality,sidebar bootstrap 4,template admin bootstrap 4"/>
        <style>
            nav{
                position: relative !important;
            }
            .navbar-nav .nav-item .nav-link {
                transition: color 0.3s ease-in-out;
            }
            .navbar-nav .nav-item .nav-link:hover {
                color: #ffc107 !important;
            }
            .dropdown-menu .dropdown-item {
                transition: background 0.3s ease-in-out;
            }
            .dropdown-menu .dropdown-item:hover {
                background: #ffc107 !important;
                color: #000 !important;
            }
        </style>
		@include('layouts.head')
</head>

<body class="main-body " style="background-color: aliceblue;">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow fixed-top" dir="rtl">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold text-uppercase" href="{{ route('home') }}">معرض أيه</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="usersDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-users-cog"></i> المستخدمين و الصلاحيات
                        </a>
                        <ul class="dropdown-menu bg-dark border-0 shadow" aria-labelledby="usersDropdown">
                            <li><a class="dropdown-item text-light" href="{{ route('all_users') }}">المستخدمين</a></li>
                            <li><a class="dropdown-item text-light" href="{{ route('all_roles') }}">المجموعات الإدارية</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="warehousesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-warehouse"></i> المخازن و الفروع
                        </a>
                        <ul class="dropdown-menu bg-dark border-0 shadow" aria-labelledby="warehousesDropdown">
                            <li><a class="dropdown-item text-light" href="{{ route('Warehouses') }}">المخازن</a></li>
                            <li><a class="dropdown-item text-light" href="{{ route('branches') }}">الفروع</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="expensesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-money-bill-wave"></i> المصروفات
                        </a>
                        <ul class="dropdown-menu bg-dark border-0 shadow" aria-labelledby="expensesDropdown">
                            <li><a class="dropdown-item text-light" href="{{ route('expenses') }}">بنود المصروفات</a></li>
                            <li><a class="dropdown-item text-light" href="{{ route('expenses_details') }}">إضافة مصاريف</a></li>
                            <li><a class="dropdown-item text-light" href="{{ route('showDetails') }}">جميع المصاريف</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="customersDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-truck"></i> العملاء
                        </a>
                        <ul class="dropdown-menu bg-dark border-0 shadow" aria-labelledby="customersDropdown">
                            <li><a class="dropdown-item text-light" href="{{ route('all_customers') }}">جميع العملاء</a></li>
                            <li><a class="dropdown-item text-light" href="{{ route('credit_customers') }}">عملاء بالأجل</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="suppliersDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-truck"></i> الموردين و المشتريات
                        </a>
                        <ul class="dropdown-menu bg-dark border-0 shadow" aria-labelledby="suppliersDropdown">
                            <li><a class="dropdown-item text-light" href="{{ route('suppliers') }}">الموردين</a></li>
                            <li><a class="dropdown-item text-light" href="{{ route('credit_suplliers') }}">موردين الأجل</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="salesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-shopping-cart"></i> المبيعات
                        </a>
                        <ul class="dropdown-menu bg-dark border-0 shadow" aria-labelledby="salesDropdown">
                            <li><a class="dropdown-item text-light" href="{{ route('sales_invoice') }}">فاتورة المبيعات</a></li>
                            <li><a class="dropdown-item text-light" href="{{ route('all_sales') }}">فاتورة المبيعات</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="productsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-box"></i> المنتجات
                        </a>
                        <ul class="dropdown-menu bg-dark border-0 shadow" aria-labelledby="productsDropdown">
                            <li><a class="dropdown-item text-light" href="{{ route('categories') }}">اصناف المنتجات</a></li>
                            <li><a class="dropdown-item text-light" href="{{ route('products') }}">المنتجات</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

		@yield('content')
		@include('layouts.footer-scripts')
	</body>

    <!-- FontAwesome Icons (Include this if not already added in your project) -->

<!-- Custom CSS for Styling -->


</html>
