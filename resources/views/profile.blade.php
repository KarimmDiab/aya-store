@extends('layouts.master')
@section('css')
@endsection
@section('page-header')
				<!-- breadcrumb -->
				<div class="breadcrumb-header justify-content-between">
					<div class="my-auto">
						<div class="d-flex">
							<h4 class="content-title mb-0 my-auto">Pages</h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/ Profile</span>
						</div>
					</div>

				</div>
				<!-- breadcrumb -->
@endsection
@section('content')
				<!-- row -->
				<div class="row row-sm">
					<div class="col-lg-4">
						<div class="card mg-b-20">
							<div class="card-body">
								<div class="pl-0">
									<div class="main-profile-overview">
										<div class="main-img-user profile-user">
											<img alt="" src="{{URL::asset('assets/img/faces/6.jpg')}}"><a class="fas fa-camera profile-edit" href="JavaScript:void(0);"></a>
										</div>
										<div class="d-flex justify-content-between mg-b-20">
											<div>
												<h5 class="main-profile-name">{{ $customers->name }}</h5>
												<p class="main-profile-name-text">Web Designer</p>
											</div>
										</div>
										<h6>رقم العميل</h6>
										<div class="main-profile-bio">
											{{ $customers->contact_info }}
										</div><!-- main-profile-bio -->
										
										<hr class="mg-y-30">
										
									</div><!-- main-profile-overview -->
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-8">
						<div class="row row-sm">
							<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12">
								<div class="card ">
									<div class="card-body">
										<div class="counter-status d-flex md-mb-0">
											<div class="counter-icon bg-primary-transparent">
												<i class="icon-layers text-primary"></i>
											</div>
											<div class="mr-auto">
												<h5 class="tx-13">عدد الفواتير</h5>
												<h2 class="mb-0 tx-22 mb-1 mt-1">1,587</h2>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12">
								<div class="card ">
									<div class="card-body">
										<div class="counter-status d-flex md-mb-0">
											<div class="counter-icon bg-danger-transparent">
												<i class="icon-paypal text-danger"></i>
											</div>
											<div class="mr-auto">
												<h5 class="tx-13">إجمالي مبلغ الفواتير</h5>
												<h2 class="mb-0 tx-22 mb-1 mt-1">46,782 ج.م</h2>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12">
								<div class="card ">
									<div class="card-body">
										<div class="counter-status d-flex md-mb-0">
											<div class="counter-icon bg-success-transparent">
												<i class="icon-rocket text-success"></i>
											</div>
											<div class="mr-auto">
												<h5 class="tx-13">عدد الفواتير الاجل </h5>
												<h2 class="mb-0 tx-22 mb-1 mt-1">1000</h2>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="card">
							<div class="card-body">
								<div class="tabs-menu ">
									<!-- Tabs -->
									<ul class="nav nav-tabs profile navtab-custom panel-tabs">
										<li class="active">
											<a href="#home" data-toggle="tab" aria-expanded="true"> <span class="visible-xs"><i class="las la-user-circle tx-16 mr-1"></i></span> <span class="hidden-xs">الفواتير</span> </a>
										</li>
										<li class="">
											<a href="#profile" data-toggle="tab" aria-expanded="false"> <span class="visible-xs"><i class="las la-images tx-15 mr-1"></i></span> <span class="hidden-xs">GALLERY</span> </a>
										</li>
										<li class="">
											<a href="#settings" data-toggle="tab" aria-expanded="false"> <span class="visible-xs"><i class="las la-cog tx-16 mr-1"></i></span> <span class="hidden-xs">SETTINGS</span> </a>
										</li>
									</ul>
								</div>
								<div class="tab-content border-left border-bottom border-right border-top-0 p-4">
									<div class="tab-pane active" id="home">
										<h4 class="tx-15 text-uppercase mb-3">الفواتير</h4>
										<p class="m-b-5">هذا الجدول يحتوي علي جميع الفواتير التي فام بها العميل</p>
										    <!-- Invoice Table -->
		<div class="table-responsive">
		<table class="table text-md-nowrap" id="example">
    <thead>
        <tr>
            <th>كود الفاتورة</th>
            <th>تاريخ الفاتورة</th>
            <th>المبلغ</th>
            <th>طريقة الدفع</th>
            <th>المبلغ المدفوع</th>
            <th>Action</th>
            <th>تاريخ تسجيل الفاتورة</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($customers as $customer)
        <tr>
            <td>{{ $customer->sales_invoice_id }}</td>
            <td>{{ $customer->invoice_date }}</td>
            <td>{{ $customer->total_invoices }}</td>
            <td>{{ $customer->payment_method }}</td>
            <td>{{ $customer->paid_amount }}</td>
            <td>
                <!-- Button to show invoice details (using modal or another action) -->
                <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#invoiceDetailsModal{{ $customer->sales_invoice_id }}">
                    عرض التفاصيل
                </button>
            </td>
            <td>{{ $customer->created_at }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
        </div>

		<!-- Modal to show invoice details -->
@foreach ($customers as $customer)
<div class="modal fade" id="invoiceDetailsModal{{ $customer->sales_invoice_id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">تفاصيل الفاتورة - {{ $customer->sales_invoice_id }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Display more invoice details here -->
                <p><strong>تاريخ الفاتورة:</strong> {{ $customer->invoice_date }}</p>
                <p><strong>المبلغ:</strong> {{ $customer->total_invoices }}</p>
                <p><strong>طريقة الدفع:</strong> {{ $customer->payment_method }}</p>
                <p><strong>المبلغ المدفوع:</strong> {{ $customer->paid_amount }}</p>
                <p><strong>تاريخ تسجيل الفاتورة:</strong> {{ $customer->created_at }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>
@endforeach
										
									</div>
									<div class="tab-pane" id="profile">
										<div class="row">
											<div class="col-sm-4">
												<div class="border p-1 card thumb">
													<a href="#" class="image-popup" title="Screenshot-2"> <img src="{{URL::asset('assets/img/photos/7.jpg')}}" class="thumb-img" alt="work-thumbnail"> </a>
													<h4 class="text-center tx-14 mt-3 mb-0">Gallary Image</h4>
													<div class="ga-border"></div>
													<p class="text-muted text-center"><small>Photography</small></p>
												</div>
											</div>
											<div class="col-sm-4">
												<div class=" border p-1 card thumb">
													<a href="#" class="image-popup" title="Screenshot-2"> <img src="{{URL::asset('assets/img/photos/8.jpg')}}" class="thumb-img" alt="work-thumbnail"> </a>
													<h4 class="text-center tx-14 mt-3 mb-0">Gallary Image</h4>
													<div class="ga-border"></div>
													<p class="text-muted text-center"><small>Photography</small></p>
												</div>
											</div>
											<div class="col-sm-4">
												<div class=" border p-1 card thumb">
													<a href="#" class="image-popup" title="Screenshot-2"> <img src="{{URL::asset('assets/img/photos/9.jpg')}}" class="thumb-img" alt="work-thumbnail"> </a>
													<h4 class="text-center tx-14 mt-3 mb-0">Gallary Image</h4>
													<div class="ga-border"></div>
													<p class="text-muted text-center"><small>Photography</small></p>
												</div>
											</div>
											<div class="col-sm-4">
												<div class=" border p-1 card thumb  mb-xl-0">
													<a href="#" class="image-popup" title="Screenshot-2"> <img src="{{URL::asset('assets/img/photos/10.jpg')}}" class="thumb-img" alt="work-thumbnail"> </a>
													<h4 class="text-center tx-14 mt-3 mb-0">Gallary Image</h4>
													<div class="ga-border"></div>
													<p class="text-muted text-center"><small>Photography</small></p>
												</div>
											</div>
											<div class="col-sm-4">
												<div class=" border p-1 card thumb  mb-xl-0">
													<a href="#" class="image-popup" title="Screenshot-2"> <img src="{{URL::asset('assets/img/photos/6.jpg')}}" class="thumb-img" alt="work-thumbnail"> </a>
													<h4 class="text-center tx-14 mt-3 mb-0">Gallary Image</h4>
													<div class="ga-border"></div>
													<p class="text-muted text-center"><small>Photography</small></p>
												</div>
											</div>
											<div class="col-sm-4">
												<div class=" border p-1 card thumb  mb-xl-0">
													<a href="#" class="image-popup" title="Screenshot-2"> <img src="{{URL::asset('assets/img/photos/5.jpg')}}" class="thumb-img" alt="work-thumbnail"> </a>
													<h4 class="text-center tx-14 mt-3 mb-0">Gallary Image</h4>
													<div class="ga-border"></div>
													<p class="text-muted text-center"><small>Photography</small></p>
												</div>
											</div>
										</div>
									</div>
									<div class="tab-pane" id="settings">
										<form role="form">
											<div class="form-group">
												<label for="FullName">Full Name</label>
												<input type="text" value="John Doe" id="FullName" class="form-control">
											</div>
											<div class="form-group">
												<label for="Email">Email</label>
												<input type="email" value="first.last@example.com" id="Email" class="form-control">
											</div>
											<div class="form-group">
												<label for="Username">Username</label>
												<input type="text" value="john" id="Username" class="form-control">
											</div>
											<div class="form-group">
												<label for="Password">Password</label>
												<input type="password" placeholder="6 - 15 Characters" id="Password" class="form-control">
											</div>
											<div class="form-group">
												<label for="RePassword">Re-Password</label>
												<input type="password" placeholder="6 - 15 Characters" id="RePassword" class="form-control">
											</div>
											<div class="form-group">
												<label for="AboutMe">About Me</label>
												<textarea id="AboutMe" class="form-control">Loren gypsum dolor sit mate, consecrate disciplining lit, tied diam nonunion nib modernism tincidunt it Loretta dolor manga Amalia erst volute. Ur wise denim ad minim venial, quid nostrum exercise ration perambulator suspicious cortisol nil it applique ex ea commodore consequent.</textarea>
											</div>
											<button class="btn btn-primary waves-effect waves-light w-md" type="submit">Save</button>
										</form>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- row closed -->
			</div>
			<!-- Container closed -->
		</div>
		<!-- main-content closed -->
@endsection
@section('js')


<!-- Internal Select2.min js -->
<script src="{{URL::asset('assets/plugins/select2/js/select2.min.js')}}"></script>

<!-- Internal Data tables -->
<script src="{{URL::asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
<script src="{{URL::asset('assets/plugins/datatable/js/dataTables.dataTables.min.js')}}"></script>
<script src="{{URL::asset('assets/plugins/datatable/js/dataTables.responsive.min.js')}}"></script>
<script src="{{URL::asset('assets/plugins/datatable/js/responsive.dataTables.min.js')}}"></script>
<script src="{{URL::asset('assets/plugins/datatable/js/jquery.dataTables.js')}}"></script>
<script src="{{URL::asset('assets/plugins/datatable/js/dataTables.bootstrap4.js')}}"></script>
<script src="{{URL::asset('assets/plugins/datatable/js/dataTables.buttons.min.js')}}"></script>
<script src="{{URL::asset('assets/plugins/datatable/js/buttons.bootstrap4.min.js')}}"></script>
<script src="{{URL::asset('assets/plugins/datatable/js/jszip.min.js')}}"></script>
<script src="{{URL::asset('assets/plugins/datatable/js/pdfmake.min.js')}}"></script>
<script src="{{URL::asset('assets/plugins/datatable/js/vfs_fonts.js')}}"></script>
<script src="{{URL::asset('assets/plugins/datatable/js/buttons.html5.min.js')}}"></script>
<script src="{{URL::asset('assets/plugins/datatable/js/buttons.print.min.js')}}"></script>
<script src="{{URL::asset('assets/plugins/datatable/js/buttons.colVis.min.js')}}"></script>
<script src="{{URL::asset('assets/plugins/datatable/js/dataTables.responsive.min.js')}}"></script>
<script src="{{URL::asset('assets/plugins/datatable/js/responsive.bootstrap4.min.js')}}"></script>
<!--Internal  Datatable js -->
<script src="{{URL::asset('assets/js/table-data.js')}}"></script>
<!-- Internal form-elements js -->
<script src="{{URL::asset('assets/js/form-elements.js')}}"></script>
@endsection