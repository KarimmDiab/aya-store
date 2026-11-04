@extends('layouts.master')
<title> معرض أيه - صفحة العميل </title>

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

		.table.dataTable thead th {
			color: #f9fbff !important;
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
	</style>
@endsection
@section('page-header')
	<!-- breadcrumb -->
	<div class="breadcrumb-header justify-content-between">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto">العملاء</h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/
					صفحة العميل</span>
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

	<!-- row -->
	<div class=" ">
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
									<h5 class="main-profile-name">{{ $customerInfo->name ?? 'No Name Available' }}</h5>
									@if ($invoicesInfo->invoices_number > 1)
										<p class="main-profile-name-text" style="margin-top : 10px ;">
											<span
												style="background-color: #8abb8a6b; color: #020202; padding: 5px 10px; border-radius: 15px; font-weight: bold;">عميل
												دائم</span>
										</p>
									@else
										<p class="main-profile-name-text" style="margin-top : 10px ;">
											<span
												style="background-color: #8abb8a6b; color: #020202; padding: 5px 10px; border-radius: 15px; font-weight: bold;">عميل
												جديد</span>
										</p>
									@endif
								</div>
							</div>
							<h6>رقم العميل</h6>
							<div class="main-profile-bio">
								{{ $customerInfo->contact_info }}
							</div><!-- main-profile-bio -->

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
									<h2 class="mb-0 tx-22 mb-1 mt-1">{{ $invoicesInfo->invoices_number }}</h2>
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
									<h2 class="mb-0 tx-22 mb-1 mt-1">{{ $invoicesInfo->total_all_invoices }} ج.م</h2>
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
									@if ($noOfCredit > 0)
										<h2 class="mb-0 tx-22 mb-1 mt-1">{{ $noOfCredit }}</h2>
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
						</ul>
					</div>
					<div class="tab-content border-left border-bottom border-right border-top-0 p-4">


						<div class="tab-pane active" id="home">
							<h4 class="tx-15 text-uppercase mb-3">الفواتير</h4>
							<p class="m-b-5">هذا الجدول يحتوي علي جميع الفواتير التي فام بها العميل</p>
							<!-- Invoice Table -->
							<div class="table-responsive">
								@if($customerInvoices->isNotEmpty())

									<table class="table text-md-nowrap" id="example">
										<thead>
											<tr>
												<th>كود الفاتورة</th>
												<th>تاريخ الفاتورة</th>
												<th>المبلغ</th>
												<th>طريقة الدفع</th>
												<th>الخصم</th>
												<th>المبلغ المدفوع</th>
												<th>Action</th>
												<th>تاريخ تسجيل الفاتورة</th>
											</tr>
										</thead>
										<tbody>
											@foreach($customerInvoices as $invoice)
												<tr>
													<td>{{ $invoice->sales_invoice_id }}</td>
													<!-- Changed from $customer to $invoice -->
													<td>{{ $invoice->invoice_date }}</td>
													<!-- Changed from $customer to $invoice -->
													<td>{{ $invoice->total_amount }}</td>
													<!-- Changed from $customer to $invoice -->
													<td>{{ $invoice->payment_method }}</td>
													<td>{{ $invoice->deduction }}</td>

													<!-- Changed from $customer to $invoice -->
													<td>{{ $invoice->paid_amount }}</td> <!-- Changed from $customer to $invoice -->
													<td>
														<!-- Button to show invoice details (using modal or another action) -->
														<button class="btn btn-info btn-sm" data-toggle="modal"
															data-target="#invoiceDetailsModal{{ $invoice->sales_invoice_id }}">
															عرض التفاصيل
														</button>
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

							<!-- Professional Modal Design for Invoice Details -->
							@foreach ($customerInvoices as $customer)
								<div class="modal fade" id="invoiceDetailsModal{{ $customer->sales_invoice_id }}" tabindex="-1"
									role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
									<div class="modal-dialog modal-lg" role="document">
										<div class="modal-content">
											<!-- Modal Header -->
											<div class="modal-header bg-primary text-white">
												<h5 class="modal-title" id="exampleModalLabel">
													<i class="fas fa-file-invoice-dollar"></i> تفاصيل الفاتورة -
													{{ $customer->sales_invoice_id }}
												</h5>
												<button type="button" class="close text-white" data-dismiss="modal"
													aria-label="Close">
													<span aria-hidden="true">&times;</span>
												</button>
											</div>

											<!-- Modal Body -->
											<div class="modal-body">
												<div class="container">
													<div class="row mb-3">
														<div class="col-md-6">
															<h6><strong>تاريخ الفاتورة:</strong></h6>
															<p>{{ $customer->invoice_date }}</p>
														</div>
														<div class="col-md-6">
															<h6><strong>اسم المستخدم:</strong></h6>
															<p>{{ $customer->username }}</p>
														</div>
													</div>
													<div class="row mb-3">
														<div class="col-md-6">
															<h6><strong>المبلغ الإجمالي:</strong></h6>
															<p class="text-success">
																{{ number_format($customer->total_amount, 2) }} جنيه
															</p>
														</div>
														<div class="col-md-6">
															<h6><strong>طريقة الدفع:</strong></h6>
															<p>
																@if($customer->payment_method === 'credit')
																	<span class="badge badge-warning">آجل</span>
																@elseif($customer->payment_method === 'cash')
																	<span class="badge badge-success">نقداً</span>
																@elseif($customer->payment_method === 'visa')
																	<span class="badge badge-primary">فيزا</span>
																@else
																	<span class="badge badge-secondary">غير محدد</span>
																@endif
															</p>
														</div>
													</div>
													<div class="row mb-3">
														<div class="col-md-4">
															<h6><strong>الخصم:</strong></h6>
															<p class="text-danger">{{ number_format($customer->deduction, 2) }}
																جنيه</p>
														</div>
														<div class="col-md-4">
															<h6><strong>الصافي:</strong></h6>
															<p class="text-success">{{ number_format($customer->net_total, 2) }}
																جنيه</p>
														</div>
														<div class="col-md-4">
															<h6><strong>المبلغ المدفوع:</strong></h6>
															<p class="text-success">
																{{ number_format($customer->paid_amount, 2) }} جنيه
															</p>
														</div>
													</div>
													<div class="row mb-3">
														<div class="col-md-6">
															<h6><strong>المبلغ المتبقي:</strong></h6>
															<p class="text-warning">
																{{ number_format($customer->remaining_amount, 2) }} جنيه
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
																</tr>
															</thead>
															<tbody>
																@foreach ($invoiceDetails as $item)
																	@if ($item->invoice_id == $customer->sales_invoice_id)
																		<tr>
																			<td>{{ $item->product_name }}</td>
																			<td>{{ $item->quantity }}</td>
																			<td>{{ number_format($item->unit_price, 2) }} جنيه</td>
																			<td>{{ number_format($item->total_product, 2) }} جنيه</td>
																		</tr>
																	@endif
																@endforeach
															</tbody>
														</table>
													</div>
												</div>
											</div>

											<!-- Modal Footer -->
											<div class="modal-footer">
												<!-- Print Sales Invoice Button -->
												<form
													action="{{ route('invoices.print', ['invoice_id' => $customer->sales_invoice_id, 'customer_id' => $customer->id]) }}"
													method="get" style="display: inline;"
													onsubmit="return openInvoicePopup(this);">
													<button type="submit" class="btn btn-info">طباعة</button>
												</form>
												<!-- end print Sales Invoice Button -->
												<button type="button" class="btn btn-secondary"
													data-dismiss="modal">إغلاق</button>
											</div>

										</div>
									</div>
								</div>
							@endforeach


							<div id="printableInvoice{{ $customer->sales_invoice_id ?? ''}}">
								<!-- Content of the invoice -->
							</div>
						</div>



						<div class="tab-pane" id="credit_invoice">
							<h4 class="tx-15 text-uppercase mb-3">الفواتير</h4>
							<p class="m-b-5">هذا الجدول يحتوي علي جميع الفواتير الأجلة التي فام بها العميل</p>
							<!-- Invoice Table -->
							<div class="table-responsive">
								@if($creditInvoices->isNotEmpty())

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
															@foreach($creditInvoices as $invoice)
																						<tr>
																							<td>{{ $invoice->sales_invoice_id }}</td>
																							<td>{{ $invoice->invoice_date }}</td>
																							<td>{{ $invoice->total_amount }}</td>
																							<td>{{ $invoice->payment_method }}</td>
																							<td>{{ $invoice->paid_amount }}</td>
																							<td>{{ $invoice->remaining_amount }}</td>

																							<td>
																								@if($invoice->total_paid >= $invoice->remaining_amount)
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
																									data-target="#creditDetailsModal{{ $invoice->sales_invoice_id }}">
																									التفاصيل
																								</button>
																								<!-- Button to open "Add Payment" modal -->
																								<button class="btn btn-success btn-sm" data-toggle="modal"
																									data-target="#addPaymentModal{{ $invoice->sales_invoice_id }}">
																									إضافة حركة سداد
																								</button>

																								<!-- Professional Modal Design for Invoice Details -->
																								@foreach ($creditInvoices as $customer)
																																	<div class="modal fade"
																																		id="creditDetailsModal{{ $customer->sales_invoice_id }}" tabindex="-1"
																																		role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
																																		<div class="modal-dialog modal-lg" role="document">
																																			<div class="modal-content">
																																				<!-- Modal Header -->
																																				<div class="modal-header bg-primary text-white">
																																					<h5 class="modal-title" id="exampleModalLabel">
																																						<i class="fas fa-file-invoice-dollar"></i> تفاصيل
																																						الفاتورة - {{ $customer->sales_invoice_id }}
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
																																								<p>{{ $customer->invoice_date }}</p>
																																							</div>
																																							<div class="col-md-6">
																																								<h6><strong>اسم المستخدم:</strong></h6>
																																								<p>{{ $customer->username }}</p>
																																							</div>
																																						</div>
																																						<div class="row mb-3">
																																							<div class="col-md-6">
																																								<h6><strong>المبلغ الإجمالي:</strong></h6>
																																								<p class="text-success">
																																									{{ number_format($customer->total_amount, 2) }}
																																									جنيه
																																								</p>
																																							</div>
																																							<div class="col-md-6">
																																								<h6><strong>طريقة الدفع:</strong></h6>
																																								<p>
																																									@if($customer->payment_method === 'credit')
																																										<span class="badge badge-warning">آجل</span>
																																									@elseif($customer->payment_method === 'cash')
																																										<span
																																											class="badge badge-success">نقداً</span>
																																									@elseif($customer->payment_method === 'visa')
																																										<span
																																											class="badge badge-primary">فيزا</span>
																																									@else
																																										<span class="badge badge-secondary">غير
																																											محدد</span>
																																									@endif
																																								</p>
																																							</div>
																																						</div>
																																						<div class="row mb-3">
																																							<div class="col-md-4">
																																								<h6><strong>الخصم:</strong></h6>
																																								<p class="text-danger">
																																									{{ number_format($customer->deduction, 2) }}
																																									جنيه
																																								</p>
																																							</div>
																																							<div class="col-md-4">
																																								<h6><strong>الصافي:</strong></h6>
																																								<p class="text-success">
																																									{{ number_format($customer->net_total, 2) }}
																																									جنيه
																																								</p>
																																							</div>
																																							<div class="col-md-4">
																																								<h6><strong>المبلغ المدفوع:</strong></h6>
																																								<p class="text-success">
																																									{{ number_format($customer->paid_amount, 2) }}
																																									جنيه
																																								</p>
																																							</div>
																																						</div>
																																						<div class="row mb-3">
																																							<div class="col-md-6">
																																								<h6><strong>المبلغ المتبقي:</strong></h6>
																																								<p class="text-warning">
																																									{{ number_format($customer->remaining_amount, 2) }}
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
																																									@foreach ($invoiceDetails as $item)
																																										@if ($item->invoice_id == $customer->sales_invoice_id)
																																											<tr>
																																												<td>{{ $item->product_name }}</td>
																																												<td>{{ $item->quantity }}</td>
																																												<td>{{ number_format($item->unit_price, 2) }}
																																													جنيه</td>
																																												<td>{{ number_format($item->total_product, 2) }}
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
																																																								@if ($transactions->sales_invoice_id == $customer->sales_invoice_id)
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
																																																																									{{ number_format($transactions->transaction_remaining_amount, 2) }}
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
																																					<!-- Print Sales Invoice Button -->
																																					<form
																																						action="{{ route('invoices.print', ['invoice_id' => $customer->sales_invoice_id, 'customer_id' => $customer->id]) }}"
																																						method="get" style="display: inline;"
																																						onsubmit="return openInvoicePopup(this);">
																																						<button type="submit"
																																							class="btn btn-info">طباعة</button>
																																					</form>
																																					<!-- End Print Sales Invoice Button -->
																																					<button type="button" class="btn btn-secondary"
																																						data-dismiss="modal">إغلاق</button>
																																				</div>

																																			</div>
																																		</div>
																																	</div>
																								@endforeach


																								<!-- Add Payment Modal -->
																								<div class="modal fade" id="addPaymentModal{{ $invoice->sales_invoice_id }}"
																									tabindex="-1" role="dialog" aria-labelledby="addPaymentLabel"
																									aria-hidden="true">
																									<div class="modal-dialog" role="document">
																										<div class="modal-content">
																											<!-- Modal Header -->
																											<div class="modal-header bg-success text-white">
																												<h5 class="modal-title" id="addPaymentLabel">
																													<i class="fas fa-money-bill-wave"></i> إضافة حركة سداد
																												</h5>
																												<button type="button" class="close text-white"
																													data-dismiss="modal" aria-label="Close">
																													<span aria-hidden="true">&times;</span>
																												</button>
																											</div>

																											<!-- Modal Body -->
																											<div class="modal-body">
																												<form
																													action="{{ route('customer.payments.store', ['sales_invoice_id' => $invoice->sales_invoice_id, 'customer_id' => $invoice->id]) }}"
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




							<div id="printableInvoice{{ $customer->sales_invoice_id ?? '' }}">
								<!-- Content of the invoice -->
							</div>
						</div>





						<div class="tab-pane" id="transactions">
							<h4 class="tx-15 text-uppercase mb-3">الفواتير</h4>
							<p class="m-b-5">هذا الجدول يحتوي علي جميع حركات سداد الفواتير الأجلة التي فام بها العميل</p>


							<!-- Invoice Table -->
							<div class="table-responsive">
								@if($cstTransaction->isNotEmpty())

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
											@foreach($cstTransaction as $invoice)
												<tr>
													<td>{{ $invoice->id }}</td> <!-- Changed from $customer to $invoice -->
													<td>{{ $invoice->sales_invoice_id }}</td>
													<!-- Changed from $customer to $invoice -->
													<td>{{ $invoice->payment_date }}</td>
													<!-- Changed from $customer to $invoice -->
													<td>{{ $invoice->amount_paid }}</td> <!-- Changed from $customer to $invoice -->
													<td>{{ $invoice->transaction_remaining_amount }}</td>
													<!-- Changed from $customer to $invoice -->
													<td>{{ $invoice->notes }}</td> <!-- Changed from $customer to $invoice -->
													<td>
														<!-- Button to show invoice details (using modal or another action) -->
														<button class="btn btn-info btn-sm" data-toggle="modal"
															data-target="#transactionCreditDetailsModal{{ $invoice->id }}">
															تعديل
														</button>

														<!-- Loop through customer payment transactions -->
														@foreach ($transaction as $customer)
															@if ($customer->id == $invoice->id)
																<!-- Filter results to only show the relevant modal -->
																<div class="modal fade" id="transactionCreditDetailsModal{{ $invoice->id }}"
																	tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
																	aria-hidden="true">
																	<div class="modal-dialog modal-lg" role="document">
																		<div class="modal-content">
																			<!-- Modal Header -->
																			<div class="modal-header bg-primary text-white">
																				<h5 class="modal-title" id="exampleModalLabel">
																					<i class="fas fa-file-invoice-dollar"></i> تعديل حركة
																					السداد رقم {{ $customer->id }} - الفاتورة رقم
																					{{ $customer->sales_invoice_id }}
																				</h5>
																				<button type="button" class="close text-white"
																					data-dismiss="modal" aria-label="Close">
																					<span aria-hidden="true">&times;</span>
																				</button>
																			</div>

																			<!-- Modal Body -->
																			<div class="modal-body">
																				<form id="customerPaymentForm_{{ $customer->id }}"
																					action="{{ route('customer-payment-transactions.customerTransaction', ['customerId' => $customer->customer_id, 'invoice_id' => $invoice->sales_invoice_id, 'transactionId' => $invoice->id]) }}"
																					method="POST">
																					@csrf
																					@method('PUT')


																					<div class="container">
																						<!-- Payment Date -->
																						<div class="row mb-3">
																							<div class="col-md-6">
																								<label
																									for="payment_date_{{ $customer->id }}"
																									class="form-label"><strong>تاريخ
																										السداد:</strong></label>
																								<input type="date"
																									id="payment_date_{{ $customer->id }}"
																									name="payment_date" class="form-control"
																									value="{{ old('payment_date', $customer->payment_date) }}">
																								<div class="text-danger"
																									id="payment_date_error_{{ $customer->id }}">
																								</div>
																							</div>
																							<div class="col-md-6">
																								<label for="user_name_{{ $customer->id }}"
																									class="form-label"><strong>اسم
																										المستخدم:</strong></label>
																								<input type="text"
																									id="user_name_{{ $customer->id }}"
																									class="form-control"
																									value="{{ $customer->user_name }}"
																									disabled>
																							</div>
																						</div>

																						<!-- Amount Paid and Remaining Amount -->
																						<div class="row mb-3">
																							<div class="col-md-6">
																								<label for="amount_paid_{{ $customer->id }}"
																									class="form-label"><strong>المبلغ
																										المدفوع:</strong></label>
																								<input type="number"
																									id="amount_paid_{{ $customer->id }}"
																									name="amount_paid" class="form-control"
																									step="0.01"
																									value="{{ old('amount_paid', $customer->amount_paid) }}">
																								<div class="text-danger"
																									id="amount_paid_error_{{ $customer->id }}">
																								</div>
																							</div>

																						</div>

																						<!-- Notes -->
																						<div class="row mb-3">
																							<div class="col-md-12">
																								<label for="notes_{{ $customer->id }}"
																									class="form-label"><strong>ملاحظات:</strong></label>
																								<textarea id="notes_{{ $customer->id }}"
																									name="notes" class="form-control"
																									rows="4">{{ old('notes', $customer->notes) }}</textarea>
																								<div class="text-danger"
																									id="notes_error_{{ $customer->id }}">
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
															@endif
														@endforeach

														@if ($cstTransaction->isEmpty())
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

	<script>
		function openInvoicePopup(form) {
			// Get the form's action URL
			const url = form.action;

			// Calculate 50% of the screen's width and 100% of the screen's height
			const screenWidth = window.innerWidth;
			const screenHeight = window.innerHeight;
			const popupWidth = screenWidth * 0.5; // 50% of screen width
			const popupHeight = screenHeight; // 100% of screen height

			// Position the popup at the center horizontally, but full height
			const left = (screenWidth - popupWidth) / 2;

			// Open the popup
			const popup = window.open(
				url,
				'_blank',
				`width=${popupWidth},height=${popupHeight},left=${left},top=0,scrollbars=yes`
			);

			// Return false to prevent the default form submission
			return false;
		}
	</script>


	<script>
		document.getElementById("customerPaymentForm_{{ $customerInfo->id }}").addEventListener("submit", function (e) {
			let isValid = true;
			const errors = {
				payment_date: "يرجى إدخال تاريخ صحيح.",
				amount_paid: "يرجى إدخال المبلغ المدفوع بشكل صحيح.",
				transaction_remaining_amount: "يرجى إدخال المبلغ المتبقي بشكل صحيح."
			};

			// Validate Payment Date
			const paymentDate = document.getElementById("payment_date_{{ $customerInfo->id }}").value;
			if (!paymentDate) {
				isValid = false;
				document.getElementById("payment_date_error_{{ $customerInfo->id }}").innerText = errors.payment_date;
			} else {
				document.getElementById("payment_date_error_{{ $customerInfo->id }}").innerText = "";
			}

			// Validate Amount Paid
			const amountPaid = document.getElementById("amount_paid_{{ $customerInfo->id }}").value;
			if (!amountPaid || parseFloat(amountPaid) < 0) {
				isValid = false;
				document.getElementById("amount_paid_error_{{ $customerInfo->id }}").innerText = errors.amount_paid;
			} else {
				document.getElementById("amount_paid_error_{{ $customerInfo->id }}").innerText = "";
			}

			// Validate Remaining Amount
			const remainingAmount = document.getElementById("transaction_remaining_amount_{{ $customerInfo->id }}").value;
			if (!remainingAmount || parseFloat(remainingAmount) < 0) {
				isValid = false;
				document.getElementById("transaction_remaining_amount_error_{{ $customerInfo->id }}").innerText = errors.transaction_remaining_amount;
			} else {
				document.getElementById("transaction_remaining_amount_error_{{ $customerInfo->id }}").innerText = "";
			}

			if (!isValid) {
				e.preventDefault(); // Prevent form submission if validation fails
			}
		});
	</script>

@endsection