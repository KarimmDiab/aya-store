@extends('layouts.master')
<title>  معرض أيه - الصفحة الرئيسية </title>

@section('css')
<!-- Internal Gallery css -->
<link href="{{URL::asset('assets/plugins/gallery/gallery.css')}}" rel="stylesheet">

<style>
.custom-card {
    transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
	min-height: 350px; /* Adjust as needed */
    display: flex;
    flex-direction: column;
}

.custom-card:hover {
    transform: translateY(-5px);
    box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.2);
}

.custom-btn {
    transition: all 0.3s ease-in-out;
    background: none;
    color: blue;
    border: 2px solid blue; /* إضافة حدود لتوضيح الزر */
    padding: 10px 20px;
    font-size: 16px;
    border-radius: 5px;
}

.img-fluid {
	max-width: 50% !important;
}

/* تأثير عند تمرير الماوس */
.custom-btn:hover {
    background: blue;
    color: white;
	transform: scale(1.05);
}

.custom-icon {
    transition: transform 0.3s ease-in-out;
}

.custom-icon:hover {
    transform: rotate(10deg);
}


.custom-card .card-body {
    flex-grow: 1; /* Expands to fill available space */
    display: flex;
    flex-direction: column;
    justify-content: space-between; /* Ensures buttons align properly */
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
				<!-- row -->
				<div class="row row-sm">
						<div class="row row-sm">
							<!-- col -->
<div class="col-lg-12">
    <div class="card shadow-lg border-0 rounded-3">
        <div class="card-body d-flex justify-content-between align-items-center p-4 bg-light">
            <h4 class="fw-bold text-dark mb-0">
                <i class="fas fa-home text-primary me-2"></i> الصفحة الرئيسية
            </h4>
           
        </div>
    </div>
</div>

							<!-- /col -->
							<div class="row">
								<div class="col-xl-4 col-md-6">
									<div class="card mg-b-20 shadow-lg border-0 custom-card">
										<div class="card-header bg-primary text-white text-center">
											<div class="d-flex align-items-center mb-2">
												<img src="{{ asset('images/icons/user_icon.png') }}" alt="المنتجات" width="30" height="30">
												<h5><span class="ms-2">المستخدمين و الصلاحيات</span></h5>
											</div>
										</div>

										<div class="card-body text-center">
											<div class="d-flex justify-content-center gap-3 mb-3">
												<img class="col-4 img-fluid custom-icon" src="{{ asset('images/icons/users.png') }}" alt="Users">
												<img class="col-4 img-fluid custom-icon" src="{{ asset('images/icons/role.png') }}" alt="Roles">
											</div>
											<hr>
											<div class="d-grid gap-3">
												<a href="{{ route('all_users') }}" class="btn btn-outline-primary  btn-lg d-flex align-items-center justify-content-center gap-2 shadow-sm custom-btn">
													<i class="fas fa-users"></i> جميع المستخدمين
												</a>

												<a href="{{ route('all_users') }}" class="btn btn-outline-primary  btn-lg d-flex align-items-center justify-content-center gap-2 shadow-sm custom-btn">
													<i class="fas fa-users-cog"></i> المجموعات الإدارية
												</a>
											</div>
										</div>
									</div>
								</div>





								<div class="col-xl-4 col-md-6">
									<div class="card mg-b-20 shadow-lg border-0 custom-card">
										<div class="card-header bg-primary text-white text-center">
											<div class="d-flex align-items-center mb-2">
												<img src="{{ asset('images/icons/store_icon.png') }}" alt="المخازن و الفروع" width="30" height="30">
												<h5><span class="ms-2">المخازن و الفروع</span></h5>
											</div>
										</div>

										<div class="card-body text-center">
											<div class="d-flex justify-content-center gap-3 mb-3">
												<img class="col-4 img-fluid custom-icon" src="{{ asset('images/icons/shops.png') }}" alt="Users">
												<img class="col-4 img-fluid custom-icon" src="{{ asset('images/icons/warehouse.png') }}" alt="Roles">
											</div>
											<hr>
											<div class="d-grid gap-3">
												<a href="{{ route('Warehouses') }}" class="btnbtn btn-outline-primary btn-lg d-flex align-items-center justify-content-center gap-2 shadow-sm custom-btn">
													<i class="fas fa-users"></i>  المخازن
												</a>

												<a href="{{ route('branches') }}" class="btn btn btn-outline-primary btn-lg d-flex align-items-center justify-content-center gap-2 shadow-  custom-btn">
													<i class="fas fa-users-cog"></i> الفروع
												</a>
											</div>
										</div>
									</div>
								</div>


								<div class="col-xl-4 col-md-6">
									<div class="card mg-b-20 shadow-lg border-0 custom-card">
										<div class="card-header bg-primary text-white text-center">
											<div class="d-flex align-items-center mb-2">
												<img src="{{ asset('images/icons/3d-wallet.png') }}" alt="المصروفات" width="30" height="30">
												<h5><span class="ms-2">المصروفات</span></h5>
											</div>
										</div>

										<div class="card-body text-center">
											<div class="d-flex justify-content-center gap-3 mb-3">
												<img class="col-4 img-fluid custom-icon" src="{{ asset('images/icons/expense.png') }}" alt="Users">
											</div>
											<hr>
											<div class="d-grid gap-3">
												<a href="{{ route('expenses') }}" class="btn btn btn-outline-primary btn-lg d-flex align-items-center justify-content-center gap-2 shadow-sm custom-btn">
													<i class="fas fa-users"></i>  بنود المصروفات
												</a>

												<a href="{{ route('expenses_details') }}" class="btn btn btn-outline-primary btn-lg d-flex align-items-center justify-content-center gap-2 shadow-sm custom-btn">
													<i class="fas fa-users-cog"></i> إضافة مصروفات
												</a>

												<a href="{{ route('showDetails') }}" class="btn btn btn-outline-primary btn-lg d-flex align-items-center justify-content-center gap-2 shadow-sm custom-btn">
													<i class="fas fa-users-cog"></i> جميع مصروفات
												</a>
											</div>
										</div>
									</div>
								</div>


								<div class="col-xl-4 col-md-6">
									<div class="card mg-b-20 shadow-lg border-0 custom-card">
										<div class="card-header bg-primary text-white text-center">
											<div class="d-flex align-items-center mb-2">
												<img src="{{ asset('images/icons/customer.png') }}" alt="العملاء" width="30" height="30">
												<h5><span class="ms-2">العملاء</span></h5>
											</div>
										</div>

										<div class="card-body text-center">
											<div class="d-flex justify-content-center gap-3 mb-3">
												<img class="col-4 img-fluid custom-icon" src="{{ asset('images/icons/customers.png') }}" alt="Users">
											</div>
											<hr>
											<div class="d-grid gap-3">
												<a href="{{ route('all_customers') }}" class="btn btn btn-outline-primary btn-lg d-flex align-items-center justify-content-center gap-2 shadow-sm custom-btn">
													<i class="fas fa-users"></i>  جميع العملاء
												</a>

												<a href="{{ route('credit_customers') }}" class="btn btn btn-outline-primary btn-lg d-flex align-items-center justify-content-center gap-2 shadow-sm custom-btn">
													<i class="fas fa-users-cog"></i>  عملاء بالاجل
												</a>

												
											</div>
										</div>
									</div>
								</div>



								<div class="col-xl-4 col-md-6">
									<div class="card mg-b-20 shadow-lg border-0 custom-card">
										<div class="card-header bg-primary text-white text-center">
											<div class="d-flex align-items-center mb-2">
												<img src="{{ asset('images/icons/supplier.png') }}" alt="الموردين و المشتريات" width="30" height="30">
												<h5><span class="ms-2">الموردين و المشتريات</span></h5>
											</div>
										</div>

										<div class="card-body text-center">
											<div class="d-flex justify-content-center gap-3 mb-3">
												<img class="col-4 img-fluid custom-icon" src="{{ asset('images/icons/suppliers.png') }}" alt="Users">
												<img class="col-4 img-fluid custom-icon" src="{{ asset('images/icons/purchase_invoice.png') }}" alt="Users">

											</div>
											<hr>
											<div class="d-grid gap-3">
												<a href="{{ route('suppliers') }}" class="btn btn btn-outline-primary btn-lg d-flex align-items-center justify-content-center gap-2 shadow-sm custom-btn">
													<i class="fas fa-users"></i>  الموردين
												</a>

												<a href="{{ route('credit_suplliers') }}" class="btn btn btn-outline-primary btn-lg d-flex align-items-center justify-content-center gap-2 shadow-sm custom-btn">
													<i class="fas fa-users-cog"></i>  موردين بالاجل
												</a>

												<a href="{{ route('purchase_invoices') }}" class="btn btn btn-outline-primary btn-lg d-flex align-items-center justify-content-center gap-2 shadow-sm custom-btn">
													<i class="fas fa-users-cog"></i>  فواتير المشتريات
												</a>
												
											</div>
										</div>
									</div>
								</div>


								<div class="col-xl-4 col-md-6">
									<div class="card mg-b-20 shadow-lg border-0 custom-card">
										<div class="card-header bg-primary text-white text-center">
											<div class="d-flex align-items-center mb-2">
												<img src="{{ asset('images/icons/flash-sale.png') }}" alt="المبيعات" width="30" height="30">
												<h5><span class="ms-2">المبيعات</span></h5>
											</div>
										</div>

										<div class="card-body text-center">
											<div class="d-flex justify-content-center gap-3 mb-3">
												<img class="col-4 img-fluid custom-icon" src="{{ asset('images/icons/sale.png') }}" alt="Users">
											</div>
											<hr>
											<div class="d-grid gap-3">
												<a href="{{ route('sales_invoice') }}" class="btn btn btn-outline-primary btn-lg d-flex align-items-center justify-content-center gap-2 shadow-sm custom-btn">
													<i class="fas fa-users"></i>  عمليات البيع
												</a>

												<a href="{{ route('all_sales') }}" class="btn btn btn-outline-primary btn-lg d-flex align-items-center justify-content-center gap-2 shadow-sm custom-btn">
													<i class="fas fa-users-cog"></i>  فواتير المبيعات
												</a>
												
											</div>
										</div>
									</div>
								</div>

								<div class="col-xl-4 col-md-6">
									<div class="card mg-b-20 shadow-lg border-0 custom-card">
										<div class="card-header bg-primary text-white text-center">
											<div class="d-flex align-items-center mb-2">
												<img src="{{ asset('images/icons/products.png') }}" alt="المبيعات" width="30" height="30">
												<h5><span class="ms-2">المنتجات</span></h5>
											</div>
										</div>

										<div class="card-body text-center">
											<div class="d-flex justify-content-center gap-3 mb-3">
												<img class="col-4 img-fluid custom-icon" src="{{ asset('images/icons/box.png') }}" alt="Users">
											</div>
											<hr>
											<div class="d-grid gap-3">
												<a href="{{ route('categories') }}" class="btn btn btn-outline-primary btn-lg d-flex align-items-center justify-content-center gap-2 shadow-sm custom-btn">
													<i class="fas fa-users"></i> اصناف المنتجات
												</a>

												<a href="{{ route('products') }}" class="btn btn btn-outline-primary btn-lg d-flex align-items-center justify-content-center gap-2 shadow-sm custom-btn">
													<i class="fas fa-users-cog"></i> المنتجات
												</a>
												
											</div>
										</div>
									</div>
								</div>


							</div>



						</div>
					<!-- /col -->

					
				</div>
				<!-- row closed -->
			</div>
			<!-- Container closed -->
		</div>
		<!-- main-content closed -->
@endsection
@section('js')
@endsection