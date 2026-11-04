@extends('layouts.master')
<title> معرض أيه - المورد </title>

@section('css')
	<style>
		/* Table Styles */
		.table {
			font-size: 1rem;
			background-color: #ffffff;
			border-radius: 8px;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
		}

		/* Table Header Styles */
		.table th {
			background-color: #343a40;
			color: #ffffff;
			text-align: center;
			font-weight: bold;
			padding: 12px 15px;
		}

		/* Table Row Styles */
		.table td {
			text-align: center;
			padding: 10px 15px;
			vertical-align: middle;
		}

		/* Hover Effect for Rows */
		.table tbody tr:hover {
			background-color: #f8f9fa;
		}

		/* Action Button Styles */
		.table .btn {
			padding: 8px 15px;
			border-radius: 5px;
			font-size: 14px;
		}

		/* Button Hover Effect */
		.table .btn:hover {
			opacity: 0.85;
		}

		/* Modal Button Styles */
		.btn-primary {
			background-color: #007bff;
			border-color: #007bff;
		}

		.btn-primary:hover {
			background-color: #0056b3;
			border-color: #0056b3;
		}

		/* Tooltip and Info Box */
		.table .alert {
			font-size: 16px;
			border-radius: 8px;
			background-color: #ffeeba;
			color: #856404;
		}

		/* Responsive Design for Small Screens */
		@media (max-width: 768px) {

			.table th,
			.table td {
				font-size: 0.9rem;
				padding: 8px;
			}

			.table .btn {
				font-size: 12px;
				padding: 6px 12px;
			}
		}

		body {
        font-family: Arial, sans-serif;
        text-align: center;
        margin: 0;
        padding: 0;
    }
    .barcode-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px; /* Space between barcodes */
    }
    .barcode-item {
        width: 150px;
        height: 110px;
        border: 2px solid #000;
        border-radius: 10px;
        padding: 10px;
        text-align: center;
        background-color: #fff;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    .barcode-item p {
        font-size: 14px;
        margin: 5px 0;
        font-weight: bold;
    }
    .barcode-item svg {
        margin-top: 5px;
    }

	</style>
@endsection
@section('page-header')
	<!-- breadcrumb -->
	<div class="breadcrumb-header justify-content-between">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto">Pages</h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/
					Profile</span>
			</div>
		</div>

	</div>
	<!-- breadcrumb -->
@endsection
@section('content')

	@if (session('success'))
		<div class="alert alert-success alert-dismissible fade show" role="alert">
			<strong>نجاح!</strong> {{ session('success') }}
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
	@endif

	@if (session('error'))
		<div class="alert alert-danger alert-dismissible fade show" role="alert">
			<strong>خطأ!</strong> {{ session('error') }}
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
	@endif

	{{-- عرض أي أخطاء أخرى --}}
	@if ($errors->any())
		<div class="alert alert-danger alert-dismissible fade show" role="alert">
			<strong>هناك أخطاء في الإدخال:</strong>
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
	@endif






	<!-- row -->
	<div class="">
		<div class="col-lg-12">
			<div class="card mg-b-20">
				<div class="card-body">
					<div class="pl-0">
						<div class="main-profile-overview">
							<div class="main-img-user profile-user">
								<img alt="" src="{{URL::asset('images/general_pic/customer_pic.png')}}"><a
									class="fas fa-camera profile-edit" href="JavaScript:void(0);"></a>
							</div>
							<div class="d-flex justify-content-between mg-b-20">
								<div>
									<h5 class="main-profile-name">
										{{ $suppliersInfo->supplier_name ?? ($suppliers->name ?? 'No Name Available') }}
									</h5>
									@if ($suppliersInfo)
										@if ($suppliersInfo->number_of_invoice > 1)
											<p class="main-profile-name-text" style="margin-top : 10px;">
												<span
													style="background-color: #8abb8a6b; color: #020202; padding: 5px 10px; border-radius: 15px; font-weight: bold;">مورد
													دائم</span>
											</p>
										@else
											<p class="main-profile-name-text" style="margin-top : 10px;">
												<span
													style="background-color: #8abb8a6b; color: #020202; padding: 5px 10px; border-radius: 15px; font-weight: bold;">مورد
													جديد</span>
											</p>
										@endif
									@elseif ($suppliers)
										<p class="main-profile-name-text" style="margin-top : 10px;">
											<span
												style="background-color: #8abb8a6b; color: #020202; padding: 5px 10px; border-radius: 15px; font-weight: bold;">مورد
												جديد</span>
										</p>
									@endif

								</div>
							</div>
							<h6>رقم المورد</h6>
							<div class="main-profile-bio">
								{{ $suppliers->contact_info }}
							</div><!-- main-profile-bio -->

							<h6>عنوان المورد</h6>
							<div class="main-profile-bio">
								{{ $suppliers->address }}
							</div>

							<hr class="mg-y-30">

						</div><!-- main-profile-overview -->
					</div>
				</div>
			</div>
		</div>
		<div class="col-lg-12">
			<div class="row row-sm">
				<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12">
					<div class="card ">
						<div class="card-body">
							<div class="counter-status d-flex md-mb-0">
								<div class="" style="text-align: right;">
									<h5 class="tx-13">عدد الفواتير</h5>
									@if($suppliersInfo)
										@if ($suppliersInfo->number_of_invoice > 0)
											<h2 class="mb-0 tx-22 mb-1 mt-1">{{ $suppliersInfo->number_of_invoice }}</h2>
										@else
											<h2 class="mb-0 tx-22 mb-1 mt-1">لا توجد فواتير</h2>
										@endif
									@else
										<h6 class="mb-0 tx-22 mb-1 mt-1">لا توجد فواتير </h6>
									@endif
						</div>
								<div class="counter-icon bg-primary-transparent" style="margin-right: auto;">
									<i class="icon-layers text-primary"></i>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12">
					<div class="card ">
						<div class="card-body">
							<div class="counter-status d-flex md-mb-0">

								<div class="" style="text-align: right;">
									<h5 class="tx-13">إجمالي مبلغ الفواتير</h5>
									@if($suppliersInfo)
										@if ($suppliersInfo->total_of_all_invoices > 0)
											<h2 class="mb-0 tx-22 mb-1 mt-1">{{ $suppliersInfo->total_of_all_invoices }} ج.م</h2>
										@else
											<h2 class="mb-0 tx-22 mb-1 mt-1">{{ $suppliersInfo->total_of_all_invoices }} ج.م</h2>
										@endif
									@else
										<h6 class="mb-0 tx-22 mb-1 mt-1">لا توجد فواتير </h6>
									@endif
								</div>
								<div class="counter-icon bg-danger-transparent" style="margin-right: auto;">
									<i class="fas fa-money-bill-wave text-success"></i>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm-12 col-xl-4 col-lg-12 col-md-12">
					<div class="card ">
						<div class="card-body">
							<div class="counter-status d-flex md-mb-0">

								<div class="" style="text-align: right;">
									<h5 class="tx-13">عدد الفواتير الاجل </h5>
									@if($suppliersInfo)
										@if ($suppliersInfo->number_of_credit_invoices > 0)
											<h2 class="mb-0 tx-22 mb-1 mt-1">{{ $suppliersInfo->number_of_credit_invoices }}</h2>
										@else
											<h6 class="mb-0 tx-22 mb-1 mt-1">لا توجد فواتير أجلة</h6>
										@endif
									@else
										<h6 class="mb-0 tx-22 mb-1 mt-1">لا توجد فواتير أجلة</h6>
									@endif
								</div>
								<div class="counter-icon bg-success-transparent" style="margin-right: auto;">
									<i class="fas fa-file-invoice" style="color: #7071b3;"></i>
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
								<a href="#home" data-toggle="tab" aria-expanded="true"> <span class="visible-xs"><i
											class="las la-user-circle tx-16 mr-1"></i></span> <span
										class="hidden-xs">الفواتير</span> </a>
							</li>
							<li class="">
								<a href="#credit_invoice" data-toggle="tab" aria-expanded="false"> <span
										class="visible-xs"><i class="las la-images tx-15 mr-1"></i></span> <span
										class="hidden-xs">الفواتير الأجلة</span> </a>
							</li>
							<li class="">
								<a href="#transactions" data-toggle="tab" aria-expanded="false"> <span class="visible-xs"><i
											class="las la-cog tx-16 mr-1"></i></span> <span class="hidden-xs">حركات سداد
										الفواتير الأجلة</span> </a>
							</li>
							<li class="">
								<a href="#add_purchase_invoice" data-toggle="tab" aria-expanded="false"> <span
										class="visible-xs"><i class="las la-cog tx-16 mr-1"></i></span> <span
										class="hidden-xs">إضافة فاتورة مشتريات</span> </a>
							</li>
						</ul>
					</div>
					<div class="tab-content border-left border-bottom border-right border-top-0 p-4">


						<div class="tab-pane active" id="home">
							<h4 class="tx-15 text-uppercase mb-3">الفواتير</h4>
							<p class="m-b-5">هذا الجدول يحتوي علي جميع الفواتير المشتريات التي فام بها المورد</p>
							<!-- Invoice Table -->
							<div class="table-responsive">
								@if($supplierPurchaseInvoice->isNotEmpty())

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
														@foreach($supplierPurchaseInvoice as $invoice)
																				<tr>
																					<td>{{ $invoice->id }}</td> <!-- Changed from $customer to $invoice -->
																					<td>{{ $invoice->date }}</td> <!-- Changed from $customer to $invoice -->
																					<td>{{ $invoice->total_amount }}</td>
																					<!-- Changed from $customer to $invoice -->
																					<td>{{ $invoice->payment_method }}</td>
																					<!-- Changed from $customer to $invoice -->
																					<td>{{ $invoice->paid_amount }}</td> <!-- Changed from $customer to $invoice -->
																					<td>
																						<!-- Button to show invoice details (using modal or another action) -->
																						<button class="btn btn-info btn-sm" data-toggle="modal"
																							data-target="#invoiceDetailsModal{{ $invoice->id }}">
																							عرض التفاصيل
																						</button>

																						@foreach ($purchaseDetails as $detail)
																														@if($invoice->id == $detail->purchase_invoices_id)
<!-- Professional Modal Design for Invoice Details -->
<div class="modal fade" id="invoiceDetailsModal{{ $invoice->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-file-invoice-dollar"></i> تفاصيل الفاتورة - {{ $invoice->id }}
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body" id="printableInvoice{{ $invoice->id }}">
                <div class="container">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6><strong>تاريخ الفاتورة:</strong></h6>
                            <p>{{ $invoice->date }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6><strong>اسم المستخدم:</strong></h6>
                            <p>{{ $invoice->user_name }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6><strong>المبلغ الإجمالي:</strong></h6>
                            <p class="text-success">{{ number_format($detail->total_amount, 2) }} جنيه</p>
                        </div>
                        <div class="col-md-6">
                            <h6><strong>طريقة الدفع:</strong></h6>
                            <p>
                                @if($detail->payment_method === 'credit')
                                    <span class="badge badge-warning">آجل</span>
                                @elseif($detail->payment_method === 'cash')
                                    <span class="badge badge-success">نقداً</span>
                                @else
                                    <span class="badge badge-secondary">غير محدد</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Table for Invoice Items -->
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-primary">
                                <tr>
                                    <th>اسم المنتج</th>
                                    <th>الكمية</th>
                                    <th>سعر الوحدة</th>
                                    <th>الإجمالي</th>
                                    <th>سعر البيع</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($purchaseDetails as $detail)
                                    @if($invoice->id == $detail->purchase_invoices_id)
                                        <tr>
                                            <td>{{ $detail->product_name }}</td>
                                            <td>{{ $detail->quantity }}</td>
                                            <td>{{ number_format($detail->price_per_pices, 2) }} جنيه</td>
                                            <td>{{ number_format($detail->total_price, 2) }} جنيه</td>
                                            <td>{{ $detail->product_price }}</td>

                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>

					<div id="barcodePrintArea{{ $invoice->id }}" style="display: none;">
						<div class="barcode-container">
							@foreach ($purchaseDetails as $detail)
								@if($invoice->id == $detail->purchase_invoices_id)
									@for ($i = 0; $i < $detail->quantity; $i++)
										<div class="barcode-item">
											<p><strong>{{ $detail->product_name }}</strong></p>
											<p><strong>السعر:</strong> {{ $detail->product_price }}</p>
											<svg class="barcode" data-barcode="{{ $detail->product_id }}"></svg>
										</div>
									@endfor
								@endif
							@endforeach
						</div>
					</div>

                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
				<!-- Print purchase Invoice Button -->
				<form action="{{ route('suppliers.printInvoice', ['invoice_no' => $invoice->id, 'supplier_id' => $suppliersInfo->id]) }}"
					method="get" style="display: inline;"
					onsubmit="return openInvoicePopup(this);">
					<button type="submit" class="btn btn-info">طباعة</button>
				</form>
				<!-- End Print purchase Invoice Button -->
				<button type="button" class="btn btn-primary" onclick="printAllBarcodes({{ $invoice->id }})">طباعة جميع الباركود</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

																														@endif
																						@endforeach

																					</td>
																					<td>{{ $invoice->created_at }}</td> <!-- Changed from $customer to $invoice -->
																				</tr>
														@endforeach
													</tbody>

												</table>
								@else
									<div class="alert alert-warning text-center p-4" role="alert"
										style="font-size: 18px; border-radius: 10px;">
										<i class="fas fa-exclamation-circle" style="font-size: 24px; color: #ffc107;"></i>
										<p class="mt-3" style="margin-bottom: 0; color: #856404;">
											لا يوجد فواتير مسجلة لهذا العميل
										</p>
									</div>
								@endif

							</div>
						</div>



						<div class="tab-pane" id="credit_invoice">
							<h4 class="tx-15 text-uppercase mb-3">الفواتير الأجلة</h4>
							<p class="m-b-5">هذا الجدول يحتوي علي جميع الفواتير الأجلة التي فام بها العميل</p>
							<!-- Invoice Table -->
							<div class="table-responsive">
								@if($creditInvoiceNumbers->isNotEmpty())

												<table class="table text-md-nowrap" id="example1">
													<thead>
														<tr>
															<th>كود الفاتورة</th>
															<th>تاريخ الفاتورة</th>
															<th>المبلغ</th>
															<th>طريقة الدفع</th>
															<th>المبلغ المدفوع</th>
															<th>المبلغ المتبقي</th>
															<th>حالة السداد</th>
															<th>Action</th>
														</tr>
													</thead>
													<tbody>
														@foreach($creditInvoiceNumbers as $invoice)
																				<tr>
																					<td>{{ $invoice->purchase_invoices_id }}</td>
																					<td>{{ $invoice->date }}</td>
																					<td>{{ $invoice->total_amount }}</td>
																					<td>{{ $invoice->payment_method }}</td>
																					<td>{{ $invoice->paid_amount }}</td>
																					<td>{{ $invoice->payment_remaning_amount }}</td>

																					<td>
																						@if($invoice->total_amount_paid == $invoice->remaining_amount || $invoice->total_amount_paid >= $invoice->remaining_amount)
																							<span class="badge badge-success"
																								style="background-color: green; color: white; padding: 5px;">
																								تم السداد بالكامل
																							</span>
																						@else
																							<span class="badge badge-warning"
																								style="background-color: yellow; color: black; padding: 5px;">
																								لم يتم استكمال السداد
																							</span>
																						@endif
																					</td>

																					<td>
																						<button class="btn btn-info btn-sm" data-toggle="modal"
																							data-target="#creditDetailsModal{{ $invoice->purchase_invoices_id }}">
																							التفاصيل
																						</button>
																						<!-- Button to open "Add Payment" modal -->
																						<button class="btn btn-success btn-sm" data-toggle="modal"
																							data-target="#addPaymentModal{{ $invoice->purchase_invoices_id }}">
																							إضافة حركة سداد
																						</button>

																						<!-- Professional Modal Design for Invoice Details -->
																						@foreach ($creditDetails as $purchaseInvoice)
																														@if($purchaseInvoice->purchase_invoices_id == $invoice->purchase_invoices_id)
																																						<div class="modal fade"
																																							id="creditDetailsModal{{ $invoice->purchase_invoices_id }}"
																																							tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
																																							aria-hidden="true">
																																							<div class="modal-dialog modal-lg" role="document">
																																								<div class="modal-content">
																																									<!-- Modal Header -->
																																									<div class="modal-header bg-primary text-white">
																																										<h5 class="modal-title" id="exampleModalLabel">
																																											<i class="fas fa-file-invoice-dollar"></i> تفاصيل
																																											الفاتورة - {{ $invoice->purchase_invoices_id }}
																																										</h5>
																																										<button type="button" class="close text-white"
																																											data-dismiss="modal" aria-label="Close">
																																											<span aria-hidden="true">&times;</span>
																																										</button>
																																									</div>

																																									<!-- Modal Body -->
																																									<div class="modal-body">
																																										<div class="container">
																																											<div class="row mb-3">
																																												<div class="col-md-6">
																																													<h6><strong>تاريخ الفاتورة:</strong></h6>
																																													<p>{{ $purchaseInvoice->date }}</p>
																																												</div>
																																												<div class="col-md-6">
																																													<h6><strong>اسم المستخدم:</strong></h6>
																																													<p>{{ $purchaseInvoice->username }}</p>
																																												</div>
																																											</div>
																																											<div class="row mb-3">
																																												<div class="col-md-6">
																																													<h6><strong>المبلغ الإجمالي:</strong></h6>
																																													<p class="text-success">
																																														{{ number_format($purchaseInvoice->total_amount, 2) }}
																																														جنيه
																																													</p>
																																												</div>
																																												<div class="col-md-6">
																																													<h6><strong>طريقة الدفع:</strong></h6>
																																													<p>
																																														@if($purchaseInvoice->payment_method === 'credit')
																																															<span class="badge badge-warning">آجل</span>
																																														@elseif($purchaseInvoice->payment_method === 'cash')
																																															<span
																																																class="badge badge-success">نقداً</span>
																																														@else
																																															<span class="badge badge-secondary">غير
																																																محدد</span>
																																														@endif
																																													</p>
																																												</div>
																																											</div>
																																											<div class="row mb-3">

																																												<div class="col-md-4">
																																													<h6><strong>المبلغ المدفوع:</strong></h6>
																																													<p class="text-success">
																																														{{ number_format($purchaseInvoice->paid_amount, 2) }}
																																														جنيه
																																													</p>
																																												</div>
																																											</div>
																																											<div class="row mb-3">
																																												<div class="col-md-6">
																																													<h6><strong>المبلغ المتبقي:</strong></h6>
																																													<p class="text-warning">
																																														{{ number_format($purchaseInvoice->remaining_amount, 2) }}
																																														جنيه
																																													</p>
																																												</div>
																																											</div>

																																											<!-- Table for Invoice Items -->
																																											<div class="table-responsive">
																																												<h5 class="text-primary">تفاصيل المنتجات</h5>
																																												<table class="table table-bordered">
																																													<thead class="table-primary">
																																														<tr>
																																															<th>اسم المنتج</th>
																																															<th>الكمية</th>
																																															<th>سعر الوحدة</th>
																																															<th>الإجمالي</th>
																																														</tr>
																																													</thead>
																																													<tbody>
																																														@foreach ($creditDetails as $item)
																																															@if ($item->purchase_invoices_id == $purchaseInvoice->purchase_invoices_id)
																																																<tr>
																																																	<td>{{ $item->product_name }}</td>
																																																	<td>{{ $item->quantity }}</td>
																																																	<td>{{ number_format($item->price_per_pices, 2) }}
																																																		جنيه</td>
																																																	<td>{{ number_format($item->total_price, 2) }}
																																																		جنيه</td>
																																																</tr>
																																															@endif
																																														@endforeach
																																													</tbody>
																																												</table>
																																											</div>

																																											<!-- Transaction History Table -->
																																											<div class="table-responsive">
																																												<h5 class="text-primary">سجل الدفعات</h5>
																																												<table class="table table-bordered">
																																													<thead class="table-success">
																																														<tr>
																																															<th>تاريخ السداد</th>
																																															<th>المبلغ المدفوع</th>
																																															<th>المبلغ المتبقي</th>
																																															<th>ملاحظات</th>
																																														</tr>
																																													</thead>
																																													<tbody>
																																														@php
																																															$transactionsFound = false;
																																														@endphp
																																														@foreach ($transaction as $transactions)
																																																												@if ($transactions->purchases_invoice_id == $invoice->purchase_invoices_id)
																																																																										@php
																																																																											$transactionsFound = true;
																																																																										@endphp
																																																																										<tr>
																																																																											<td>{{ $transactions->payment_date }}
																																																																											</td>
																																																																											<td class="text-success">
																																																																												{{ number_format($transactions->amount_paid, 2) }}
																																																																												جنيه
																																																																											</td>
																																																																											<td class="text-warning">
																																																																												{{ number_format($transactions->payment_remaning_amount, 2) }}
																																																																												جنيه
																																																																											</td>
																																																																											<td>{{ $transactions->notes ?? 'لا يوجد' }}
																																																																											</td>
																																																																										</tr>
																																																												@endif
																																														@endforeach
																																														@if (!$transactionsFound)
																																															<tr>
																																																<td colspan="4" class="text-center">لا
																																																	يوجد دفعات سداد</td>
																																															</tr>
																																														@endif

																																													</tbody>
																																												</table>
																																											</div>

																																										</div>
																																									</div>

																																									<!-- Modal Footer -->
																																									<div class="modal-footer">
																																										<!-- Print purchase Invoice Button -->
																																										<form action="{{ route('suppliers.printInvoice', ['invoice_no' => $invoice->purchase_invoices_id, 'supplier_id' => $suppliersInfo->id]) }}"
																																											method="get" style="display: inline;"
																																											onsubmit="return openInvoicePopup(this);">
																																											<button type="submit" class="btn btn-info">طباعة</button>
																																										</form>
																																										<!-- End Print purchase Invoice Button -->

																																										<!-- End Print Sales Invoice Button -->
																																										<button type="button" class="btn btn-secondary"
																																											data-dismiss="modal">إغلاق</button>
																																									</div>

																																								</div>
																																							</div>
																																						</div>
																							@endif
																						@endforeach


																						<!-- Add Payment Modal -->
																						<div class="modal fade"
																							id="addPaymentModal{{ $invoice->purchase_invoices_id }}" tabindex="-1"
																							role="dialog" aria-labelledby="addPaymentLabel" aria-hidden="true">
																							<div class="modal-dialog" role="document">
																								<div class="modal-content">
																									<!-- Modal Header -->
																									<div class="modal-header bg-success text-white">
																										<h5 class="modal-title" id="addPaymentLabel">
																											<i class="fas fa-money-bill-wave"></i> إضافة حركة سداد
																											للفاتورة - {{ $invoice->purchase_invoices_id }}
																										</h5>
																										<button type="button" class="close text-white"
																											data-dismiss="modal" aria-label="Close">
																											<span aria-hidden="true">&times;</span>
																										</button>
																									</div>

																									<!-- Modal Body -->
																									<div class="modal-body">
																										<form
																											action="{{ route('supplierPaymentStore', ['purchase_id' => $invoice->purchase_invoices_id, 'supplier_id' => $invoice->supplier_id]) }}"
																											method="POST">
																											@csrf

																											<div class="form-group">
																												<label for="payment_date">تاريخ السداد</label>
																												<input type="date" class="form-control"
																													name="payment_date" required>
																											</div>

																											<div class="form-group">
																												<label for="amount_paid">المبلغ المدفوع</label>
																												<input type="number" class="form-control"
																													name="amount_paid" step="0.01" required>
																											</div>

																											<div class="form-group">
																												<label for="notes">ملاحظات</label>
																												<textarea class="form-control" name="notes"
																													rows="3"></textarea>
																											</div>

																											<!-- Modal Footer -->
																											<div class="modal-footer">
																												<button type="submit"
																													class="btn btn-success">حفظ</button>
																												<button type="button" class="btn btn-secondary"
																													data-dismiss="modal">إغلاق</button>
																											</div>
																										</form>
																									</div>
																								</div>
																							</div>
																						</div>

																					</td>
																				</tr>
														@endforeach
													</tbody>


												</table>
								@else
									<div class="alert alert-warning text-center p-4" role="alert"
										style="font-size: 18px; border-radius: 10px;">
										<i class="fas fa-exclamation-circle" style="font-size: 24px; color: #ffc107;"></i>
										<p class="mt-3" style="margin-bottom: 0; color: #856404;">
											لا يوجد فواتير مسجلة لهذا العميل
										</p>
									</div>
								@endif

							</div>




							<div id="printableInvoice">
								<!-- Content of the invoice -->
							</div>
						</div>

						<div class="tab-pane" id="transactions">
							<h4 class="tx-15 text-uppercase mb-3">الفواتير</h4>
							<p class="m-b-5">هذا الجدول يحتوي علي جميع حركات سداد الفواتير الأجلة التي فام بها العميل</p>


							<!-- Invoice Table -->
							<div class="table-responsive">
								@if($transaction->isNotEmpty())

									<table class="table table-striped table-bordered table-hover" id="example1">
										<thead class="thead-dark">
											<tr>
												<th>كود الحركة</th>
												<th>كود الفاتورة</th>
												<th>تاريخ السداد</th>
												<th>المبلغ المدفوع</th>
												<th>المبلغ المتبقي</th>
												<th>ملاحظات</th>
												<th>Action</th>
												<th>تاريخ تسجيل الفاتورة</th>
											</tr>
										</thead>
										<tbody>
											@foreach($transaction as $invoice)
												<tr>
													<td>{{ $invoice->supplier_payments_id }}</td>
													<!-- Changed from $customer to $invoice -->
													<td>{{ $invoice->purchases_invoice_id }}</td>
													<!-- Changed from $customer to $invoice -->
													<td>{{ $invoice->payment_date }}</td>
													<!-- Changed from $customer to $invoice -->
													<td>{{ $invoice->amount_paid }}</td> <!-- Changed from $customer to $invoice -->
													<td>{{ $invoice->payment_remaning_amount }}</td>
													<!-- Changed from $customer to $invoice -->
													<td>{{ $invoice->notes }}</td> <!-- Changed from $customer to $invoice -->
													<td>
														<!-- Button to show invoice details (using modal or another action) -->
														<button class="btn btn-info btn-sm" data-toggle="modal"
															data-target="#transactionCreditDetailsModal{{ $invoice->supplier_payments_id }}">
															تعديل
														</button>

														<!-- Loop through customer payment transactions -->
														<div class="modal fade"
															id="transactionCreditDetailsModal{{ $invoice->supplier_payments_id }}"
															tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
															aria-hidden="true">
															<div class="modal-dialog modal-lg" role="document">
																<div class="modal-content">
																	<!-- Modal Header -->
																	<div class="modal-header bg-primary text-white">
																		<h5 class="modal-title" id="exampleModalLabel">
																			<i class="fas fa-file-invoice-dollar"></i> تعديل حركة
																			السداد رقم {{ $invoice->supplier_payments_id }} -
																			الفاتورة رقم {{ $invoice->purchases_invoice_id }}
																		</h5>
																		<button type="button" class="close text-white"
																			data-dismiss="modal" aria-label="Close">
																			<span aria-hidden="true">&times;</span>
																		</button>
																	</div>

																	<!-- Modal Body -->
																	<div class="modal-body">
																		<form
																			id="supplierPaymentForm_{{ $invoice->supplier_payments_id }}"
																			action="{{ route('supplier-payment-transactions.supplierTransaction', ['supplierID' => $invoice->supplier_id, 'invoice_id' => $invoice->purchases_invoice_id, 'transactionId' => $invoice->supplier_payments_id]) }}"
																			method="POST">
																			@csrf
																			@method('PUT')


																			<div class="container">
																				<!-- Payment Date -->
																				<div class="row mb-3">
																					<div class="col-md-6">
																						<label
																							for="payment_date_{{ $invoice->supplier_payments_id }}"
																							class="form-label"><strong>تاريخ
																								السداد:</strong></label>
																						<input type="date"
																							id="payment_date_{{ $invoice->supplier_payments_id }}"
																							name="payment_date" class="form-control"
																							value="{{ old('payment_date', $invoice->payment_date) }}">
																						<div class="text-danger"
																							id="payment_date_error_{{ $invoice->supplier_payments_id }}">
																						</div>
																					</div>
																					<div class="col-md-6">
																						<label
																							for="user_name_{{ $invoice->supplier_payments_id }}"
																							class="form-label"><strong>اسم
																								المستخدم:</strong></label>
																						<input type="text"
																							id="user_name_{{ $invoice->supplier_payments_id }}"
																							class="form-control"
																							value="{{ $invoice->name }}" disabled>
																					</div>
																				</div>

																				<!-- Amount Paid and Remaining Amount -->
																				<div class="row mb-3">
																					<div class="col-md-6">
																						<label
																							for="amount_paid_{{ $invoice->supplier_payments_id }}"
																							class="form-label"><strong>المبلغ
																								المدفوع:</strong></label>
																						<input type="number"
																							id="amount_paid_{{ $invoice->supplier_payments_id }}"
																							name="amount_paid" class="form-control"
																							step="0.01"
																							value="{{ old('amount_paid', $invoice->amount_paid) }}">
																						<div class="text-danger"
																							id="amount_paid_error_{{ $invoice->supplier_payments_id }}">
																						</div>
																					</div>

																				</div>

																				<!-- Notes -->
																				<div class="row mb-3">
																					<div class="col-md-12">
																						<label
																							for="notes_{{ $invoice->supplier_payments_id }}"
																							class="form-label"><strong>ملاحظات:</strong></label>
																						<textarea
																							id="notes_{{ $invoice->supplier_payments_id }}"
																							name="notes" class="form-control"
																							rows="4">{{ old('notes', $invoice->notes) }}</textarea>
																						<div class="text-danger"
																							id="notes_error_{{ $invoice->supplier_payments_id }}">
																						</div>
																					</div>
																				</div>
																			</div>

																			<!-- Submit Button -->
																			<div class="text-right">
																				<button type="submit"
																					class="btn btn-success">تحديث</button>
																				<button type="button" class="btn btn-secondary"
																					data-dismiss="modal">إغلاق</button>
																			</div>
																		</form>
																	</div>
																</div>
															</div>
														</div>

														@if ($transaction->isEmpty())
															<div class="alert alert-warning text-center p-4" role="alert"
																style="font-size: 18px; border-radius: 10px;">
																<i class="fas fa-exclamation-circle"
																	style="font-size: 24px; color: #ffc107;"></i>
																<p class="mt-3" style="margin-bottom: 0; color: #856404;">
																	لا يوجد عمليات سداد مسجلة لهذا العميل
																</p>
															</div>
														@endif

													</td>
													<td>{{ $invoice->created_at }}</td> <!-- Changed from $customer to $invoice -->
												</tr>
											@endforeach
										</tbody>
									</table>





								@else
									<div class="alert alert-warning text-center p-4" role="alert"
										style="font-size: 18px; border-radius: 10px;">
										<i class="fas fa-exclamation-circle" style="font-size: 24px; color: #ffc107;"></i>
										<p class="mt-3" style="margin-bottom: 0; color: #856404;">
											لا يوجد عمليات سداد مسجلة لهذا العميل
										</p>
									</div>
								@endif

							</div>


						</div>



						<div class="tab-pane" id="add_purchase_invoice">
							<h4 class="tx-15 text-uppercase mb-3">تسجيل فاتورة مشتريات</h4>

							<div class="main-content-label mg-b-5">
								تسجيل فاتورة مشتريات جديدة
							</div>
							<p class="mg-b-20">قم بتسجيل بيانات الفاتورة</p>

							<form action="{{ route('add_purchase_invoices_new', ['supplier' => encrypt($suppliers->id)]) }}"
								method="post" data-parsley-validate="" enctype="multipart/form-data">
								@csrf
								<div class="row row-sm">
									<div class="col-6">
										<div class="form-group mg-b-0">
											<label class="form-label"> رقم الفاتورة: <span
													class="tx-danger">*</span></label>
											<input class="form-control" name="purchase_invoices_no"
												placeholder="قم بإدخال رقم الفاتورة" required type="text">
										</div>
									</div>
									<div class="col-6">
										<div class="form-group">
											<label class="form-label">تاريخ الفاتورة: <span
													class="tx-danger">*</span></label>
											<input class="form-control" name="purchase_invoices_date"
												placeholder="قم بإدخال تاريخ الفاتورة" required type="date">
										</div>
									</div>
								</div>

								<!-- File Upload Field -->
								<div class="col-6" style="margin-bottom: 45px;">
									<div class="form-group">
										<label for="invoice_file">تحميل الملف (صورة أو PDF):</label>
										<input type="file" name="invoice_file" id="invoice_file" class="form-control"
											accept="image/jpeg, image/png, image/jpg, application/pdf">
									</div>
								</div>
								<hr>
								<!-- Styled Table for Purchase Invoice Items -->
								<div class="table-responsive">
									<table class="table table-hover table-bordered text-center" id="invoice-items-table">
										<thead class="table-dark">
											<tr>
												<th>التصنيف</th>
												<th>كود المنتج</th>
												<th>المنتج</th>
												<th>الكمية</th>
												<th>السعر للوحدة</th>
												<th>الإجمالي</th>
												<th>سعر البيع</th>
												<th>الربح</th>
												<th>حذف</th>
											</tr>
										</thead>
										<tbody id="invoice-items-body">
											<tr>

												<td style="width: 15%;">
													<select class="form-control" name="category_name[]" required>
														<option value="">اختر التصنيف</option>
														@foreach($supplierCategories as $category)
															@if($category->supplier_id == $suppliers->id)
																<option value="{{ $category->category_id }}">
																	{{ $category->category_name }}
																</option>
															@endif
														@endforeach
													</select>
												</td>
												<!-- عند تفعيل الـ Checkbox سيتم إخفاء هذين الـ td وعرض القائمة المنسدلة -->
												<td class="product-inputs" style="width: 10%;"><input type="number"
														name="product_id[]" class="form-control text-center product_id"
														placeholder="كود المنتج" required></td>
												<td class="product-inputs" style="width: 20%;"><input type="text"
														name="product[]" class="form-control text-center product"
														placeholder="اسم المنتج" required></td>
												<td style="width: 10%;"><input type="number" name="quantity[]"
														class="form-control text-center quantity" placeholder="الكمية"
														required></td>
												<td style="width: 10%;"><input type="float" name="unit_price[]"
														class="form-control text-center unit-price"
														placeholder="السعر للوحدة" required></td>
												<td style="width: 10%;"><input type="number" name="total[]"
														class="form-control text-center total" placeholder="الاجمالي"
														readonly></td>
												<td style="width: 10%;"><input type="number" name="sales_price[]"
														class="form-control text-center sales_price" placeholder="سعر البيع"
														required></td>
												<td style="width: 10%;"><input type="number" name="profit[]"
														class="form-control text-center profit" placeholder="الربح"
														readonly></td>
												<td><button type="button"
														class="btn btn-sm btn-danger remove-item">×</button></td>
											</tr>
										</tbody>
									</table>
									<button type="button" class="btn btn-info" id="add-item">+ إضافة منتج</button>
								</div>
								<hr>
								<!-- Total Invoice Amount (Disabled and Auto-calculated) -->
								<div class="">
									<div class="col-6">
										<div class="form-group">
											<label for="purchase_invoices_amount">إجمالي الفاتورة :</label>
											<input type="number" step="0.01" id="purchase_invoices_amount"
												name="purchase_invoices_amount" class="form-control"
												placeholder="مبلغ الفاتورة" readonly>
										</div>
									</div>

									<!-- Total Sales Price Field -->
									<div class="col-6">
										<div class="form-group">
											<label for="total_sales_price">إجمالي سعر البيع :</label>
											<input type="number" step="0.01" id="total_sales_price" name="total_sales_price"
												class="form-control" placeholder="إجمالي سعر البيع" readonly>
										</div>
									</div>

									<!-- Profit Field -->
									<div class="col-6">
										<div class="form-group">
											<label for="profit">الربح :</label>
											<input type="number" step="0.01" id="total_profit" name="profit"
												class="form-control" placeholder="الربح" readonly>
										</div>
									</div>


									<!-- Paid Amount Field -->
									<div class="col-6">
										<div class="form-group">
											<label class="form-label">المبلغ المدفوع:</label>
											<input class="form-control" id="amount_paid" name="amount_paid" type="float"
												placeholder="أدخل المبلغ المدفوع" required>
										</div>
									</div>

									<!-- Remaining Amount (Debt) -->
									<div class="col-6">
										<div class="form-group">
											<label class="form-label">المبلغ المتبقي (دين):</label>
											<input class="form-control" id="remaining_amount" name="remaining_amount"
												type="number" placeholder="المبلغ المتبقي" readonly>
										</div>
									</div>

								</div>
								<hr>
								<div class="col-12">
									<button class="btn btn-primary pd-x-20 mg-t-10" type="submit" style="width:100%;">تسجيل
										الفاتورة</button>
								</div>
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
	<script src="{{URL::asset('assets/js/JsBarcode.all.min.js')}}"></script>








	<script>
		document.addEventListener("DOMContentLoaded", function () {
			document.querySelector("form").addEventListener("submit", function (e) {
				let isValid = true;
				const errors = {
					payment_date: "يرجى إدخال تاريخ صحيح.",
					amount_paid: "يرجى إدخال المبلغ المدفوع بشكل صحيح (يجب أن يكون 0.01 على الأقل).",
					notes: "يجب ألا تتجاوز الملاحظات 255 حرفًا."
				};

				// Validate Payment Date
				const paymentDateInput = document.querySelector("[name='payment_date']");
				const paymentDateError = document.getElementById("payment_date_error");
				if (!paymentDateInput.value) {
					isValid = false;
					paymentDateError.innerText = errors.payment_date;
				} else {
					paymentDateError.innerText = "";
				}

				// Validate Amount Paid
				const amountPaidInput = document.querySelector("[name='amount_paid']");
				const amountPaidError = document.getElementById("amount_paid_error");
				if (!amountPaidInput.value || parseFloat(amountPaidInput.value) < 0.01) {
					isValid = false;
					amountPaidError.innerText = errors.amount_paid;
				} else {
					amountPaidError.innerText = "";
				}

				// Validate Notes
				const notesInput = document.querySelector("[name='notes']");
				const notesError = document.getElementById("notes_error");
				if (notesInput.value.length > 255) {
					isValid = false;
					notesError.innerText = errors.notes;
				} else {
					notesError.innerText = "";
				}

				if (!isValid) {
					e.preventDefault(); // Prevent form submission if validation fails
				}
			});
		});
	</script>



	<script>
		// Add new row for purchase invoice items
		document.getElementById('add-item').addEventListener('click', function () {
			let tableBody = document.getElementById('invoice-items-body');
			let newRow = document.createElement('tr');

			newRow.innerHTML = `

							<td style="width: 15%;">
								 <select class="form-control" name="category_name[]" required>
									<option value="">اختر التصنيف</option>
									@foreach($supplierCategories as $category)
										@if($category->supplier_id == $suppliers->id)
											<option value="{{ $category->category_id }}">{{ $category->category_name }}</option>
										@endif
									@endforeach
								 </select>
							</td>
							<td style="width: 10%;"><input type="number" name="product_id[]" class="form-control text-center product_id" placeholder="كود المنتج" required></td>
							<td style="width: 20%;"><input type="text" name="product[]" class="form-control text-center product" placeholder="اسم المنتج" required></td>
							<td style="width: 10%;"><input type="number" name="quantity[]" class="form-control quantity" placeholder="الكمية" required></td>
							<td style="width: 10%;"><input type="float" name="unit_price[]" class="form-control unit-price" placeholder="السعر للوحدة" required></td>
							<td style="width: 10%;"><input type="number" name="total[]" class="form-control total" placeholder="الإجمالي" readonly></td>
							<td style="width: 10%;"><input type="number" name="sales_price[]" class="form-control sales_price" placeholder="سعر البيع" required></td>
							<td style="width: 10%;"><input type="number" name="profit[]" class="form-control profit" placeholder="الربح" readonly></td>
							<td><button type="button" class="btn btn-sm btn-danger remove-item">×</button></td>

						`;

			tableBody.appendChild(newRow);
			$('.select2').select2();
		});

		// Remove row from purchase invoice items
		document.addEventListener('click', function (e) {
			if (e.target.classList.contains('remove-item')) {
				e.target.closest('tr').remove();
				calculateTotalInvoiceAmount(); // Update total when item is removed
				calculateTotalSalesPriceAndProfit(); // Recalculate sales price and profit when item is removed
			}
		});

		// Calculate total price based on quantity and unit price
		document.addEventListener('input', function (e) {
			if (e.target.classList.contains('quantity') || e.target.classList.contains('unit-price') || e.target.classList.contains('sales_price')) {
				let row = e.target.closest('tr');
				let quantity = parseFloat(row.querySelector('.quantity').value) || 0;
				let unitPrice = parseFloat(row.querySelector('.unit-price').value) || 0;
				let salesPrice = parseFloat(row.querySelector('.sales_price').value) || 0;
				//let salesPrice = unitPrice * 0.35;
				//let salesPrice = row.querySelector('.unit-price').value * 0.35;
				let total = row.querySelector('.total');
				let profit = row.querySelector('.profit');

				// Calculate total for the row
				total.value = (quantity * unitPrice).toFixed(2);

				// Calculate profit for the row
				profit.value = ((salesPrice - unitPrice) * quantity).toFixed(2);

				// Recalculate the total invoice amount and sales price
				calculateTotalInvoiceAmount();
				calculateTotalSalesPriceAndProfit();
			}
		});

		// Calculate the total invoice amount
		function calculateTotalInvoiceAmount() {
			let totalAmount = 0;
			document.querySelectorAll('.total').forEach(function (totalField) {
				totalAmount += parseFloat(totalField.value) || 0;
			});
			let totalAmountField = document.getElementById('purchase_invoices_amount');
			if (totalAmountField) {
				totalAmountField.value = totalAmount.toFixed(2);
				calculateRemainingAmount(); // Update remaining amount based on the total
			}
		}

		// Calculate remaining amount based on total and paid amount
		document.getElementById('amount_paid')?.addEventListener('input', calculateRemainingAmount);

		function calculateRemainingAmount() {
			let totalAmountField = document.getElementById('purchase_invoices_amount');
			let paidAmountField = document.getElementById('amount_paid');
			let remainingAmountField = document.getElementById('remaining_amount');
			if (totalAmountField && paidAmountField && remainingAmountField) {
				let totalAmount = parseFloat(totalAmountField.value) || 0;
				let amountPaid = parseFloat(paidAmountField.value) || 0;
				remainingAmountField.value = (totalAmount - amountPaid).toFixed(2);
			}
		}

		// Calculate total sales price and profit based on sales price
		document.addEventListener('input', function (e) {
			if (e.target.classList.contains('sales_price')) {
				calculateTotalSalesPriceAndProfit();
			}
		});

		// Calculate the total sales price and profit
		function calculateTotalSalesPriceAndProfit() {
			let totalSalesPrice = 0;
			let totalProfit = 0;

			document.querySelectorAll('.sales_price').forEach(function (salesPriceField) {
				let row = salesPriceField.closest('tr');
				let quantity = parseFloat(row.querySelector('.quantity').value) || 0;
				let salesPrice = parseFloat(salesPriceField.value) || 0;
				let unitPrice = parseFloat(row.querySelector('.unit-price').value) || 0;

				// Calculate totals and profits
				totalSalesPrice += salesPrice * quantity;
				let profit = (salesPrice - unitPrice) * quantity;
				totalProfit += profit;

				// Update profit field
				row.querySelector('.profit').value = profit.toFixed(2);
			});

			let totalSalesPriceField = document.getElementById('total_sales_price');
			let totalProfitField = document.getElementById('total_profit');
			let overallProfitField = document.getElementById('profit');
			let totalInvoiceAmountField = document.getElementById('purchase_invoices_amount');

			if (totalSalesPriceField) totalSalesPriceField.value = totalSalesPrice.toFixed(2);
			if (totalProfitField) totalProfitField.value = totalProfit.toFixed(2);

			if (overallProfitField && totalInvoiceAmountField) {
				let totalInvoiceAmount = parseFloat(totalInvoiceAmountField.value) || 0;
				overallProfitField.value = (totalSalesPrice - totalInvoiceAmount).toFixed(2);
			}
		}

		// Ensure the script runs after the DOM content is fully loaded
		document.addEventListener('DOMContentLoaded', function () {
			calculateRemainingAmount(); // Initialize remaining amount calculation
		});

		document.addEventListener("DOMContentLoaded", function () {
			document.querySelectorAll(".select-item").forEach(function (checkbox) {
				checkbox.addEventListener("change", function () {
					let row = this.closest("tr");
					let productInputs = row.querySelectorAll(".product-inputs");
					let productSelectContainer = row.querySelector(".product-select-container");
					let productSelect = row.querySelector(".product-select");

					if (this.checked) {
						productInputs.forEach(el => el.classList.add("d-none"));
						productSelectContainer.classList.remove("d-none");
					} else {
						productInputs.forEach(el => el.classList.remove("d-none"));
						productSelectContainer.classList.add("d-none");
					}
				});
			});

			document.querySelectorAll(".product-select").forEach(function (select) {
				select.addEventListener("change", function () {
					let row = this.closest("tr");
					let selectedOption = this.options[this.selectedIndex];
					let productIdInput = row.querySelector(".product_id");
					let productNameInput = row.querySelector(".product");

					if (selectedOption.value) {
						productIdInput.value = selectedOption.value;
						productNameInput.value = selectedOption.text;
					} else {
						productIdInput.value = "";
						productNameInput.value = "";
					}
				});
			});
		});

	</script>

<!-- JavaScript for Print Functionality -->
<script>
    function printAllBarcodes(invoiceId) {
    var barcodeContainer = document.getElementById("barcodePrintArea" + invoiceId);
    barcodeContainer.style.display = "block";

    // Generate barcodes dynamically
    barcodeContainer.querySelectorAll(".barcode").forEach(function(svg) {
        JsBarcode(svg, svg.getAttribute("data-barcode"), {
            format: "CODE128",
            displayValue: true,
            fontSize: 25,
			width: 4,
            height: 60
        });
    });

    // Ensure barcodes render before printing
    setTimeout(function () {
        var printWindow = window.open("", "_blank");
			printWindow.document.write(`
		<html>
		<head>
			<title>طباعة الباركود</title>
			<style>
			 	@page {
                    size: A4 portrait; /* Set A4 paper size */
                    margin: 10mm; /* Adjust margins */
                }
				body {
					font-family: Arial, sans-serif;
					text-align: center;
					margin: 0;
					padding: 0;
				}
				.barcode-container {
					display: flex;
					flex-wrap: wrap;
					justify-content: center;
					gap: 10px;
				}
				.barcode-item {
					width: 30%;
					height: 110px;
					border: 2px solid #000;
					border-radius: 10px;
					text-align: center;
					background-color: #fff;
					display: flex;
					flex-direction: column;
					justify-content: center;
					align-items: center;
				}
				.barcode-item p {
					font-size: 16px;
					margin: 5px 0;
					font-weight: bold;
				}
				.barcode-item svg {
					margin-top: 5px;
					font-size : 20px monospace;
				}
			</style>
		</head>
		<body>
			<div class="barcode-container">
				${barcodeContainer.innerHTML}
			</div>
		</body>
		</html>
	`);

			printWindow.document.close();

			printWindow.onload = function () {
            printWindow.print();
        };
    }, 500);
}


</script>


<script>
    function openInvoicePopup(form) {
        // Calculate 50% of the screen width and height
        var width = window.innerWidth * 0.5;
        var height = window.innerHeight * 0.5;

        // Calculate center position
        var left = (window.innerWidth - width) / 2;
        var top = (window.innerHeight - height) / 2;

        // Open the popup window
        var popup = window.open(
            form.action,
            '_blank',
            'width=' + width + ',height=' + height + ',left=' + left + ',top=' + top + ',resizable=yes,scrollbars=yes'
        );

        return false; // Prevent default form submission
    }
</script>

@endsection
