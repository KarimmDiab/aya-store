@extends('layouts.master2')

	
@section('content')
				<!-- row -->
				<div class="row row-sm">
					<div class="col-md-12 col-xl-12">
						<div class=" main-content-body-invoice">
							<div class="card card-invoice">
								<div class="card-body">
									<div class="invoice-header">
										<h1 class="invoice-title">Invoice</h1>
										<div class="billed-from">
											<h6>BootstrapDash, Inc.</h6>
											<p>201 Something St., Something Town, YT 242, Country 6546<br>
											Tel No: 324 445-4544<br>
											Email: youremail@companyname.com</p>
										</div><!-- billed-from -->
									</div><!-- invoice-header -->
									<div class="row mg-t-20">
										
										<div class="col-md">
											<label class="tx-gray-600">معلومات الفاتورة</label>
											<p class="invoice-info-row"><span>رقم الفاتورة</span> <span>GHT-673-00</span></p>
											<p class="invoice-info-row"><span>اسم العميل</span> <span>32334300</span></p>
											<p class="invoice-info-row"><span>رقم الموبايل</span> <span>32334300</span></p>
											<p class="invoice-info-row"><span>تاريخ الفاتورة</span> <span>November 21, 2017</span></p>
											<p class="invoice-info-row"><span>الموظف المسئول</span> <span>November 30, 2017</span></p>
										</div>
									</div>
									<div class="table-responsive mg-t-40">
										<table class="table table-invoice border text-md-nowrap mb-0">
											<thead>
												<tr>
													<th class="wd-20p">Type</th>
													<th class="tx-center">QNTY</th>
													<th class="tx-right">Unit Price</th>
													<th class="tx-right">Amount</th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td>Website Design</td>
													<td class="tx-center">2</td>
													<td class="tx-right">$150.00</td>
													<td class="tx-right">$300.00</td>
												</tr>
												<tr>
													<td>Firefox Plugin</td>
													<td class="tx-center">1</td>
													<td class="tx-right">$1,200.00</td>
													<td class="tx-right">$1,200.00</td>
												</tr>
												<tr>
													<td>iPhone App</td>
													<td class="tx-center">2</td>
													<td class="tx-right">$850.00</td>
													<td class="tx-right">$1,700.00</td>
												</tr>
												<tr>
													<td>Android App</td>
													<td class="tx-center">3</td>
													<td class="tx-right">$850.00</td>
													<td class="tx-right">$2,550.00</td>
												</tr>
												<tr>
													
													<td class="tx-right">Sub-Total</td>
													<td class="tx-right" colspan="">$5,750.00</td>
												</tr>
												<tr>

													<td class="tx-right">Tax (5%)</td>
													<td class="tx-right" colspan="">$287.50</td>
												</tr>
												<tr>

													<td class="tx-right">Discount</td>
													<td class="tx-right" colspan="">-$50.00</td>
												</tr>
												<tr>

													<td class="tx-right tx-uppercase tx-bold tx-inverse">Total Due</td>
													<td class="tx-right" colspan="">
														<h4 class="tx-primary tx-bold">$5,987.50</h4>
													</td>
												</tr>
											</tbody>
										</table>
									</div>
									<hr class="mg-b-40">
									<a class="btn btn-purple float-left mt-3 mr-2" href="">
										<i class="mdi mdi-currency-usd ml-1"></i>Pay Now
									</a>
									<a href="#" class="btn btn-danger float-left mt-3 mr-2">
										<i class="mdi mdi-printer ml-1"></i>Print
									</a>
									<a href="#" class="btn btn-success float-left mt-3">
										<i class="mdi mdi-telegram ml-1"></i>Send Invoice
									</a>
								</div>
							</div>
						</div>
					</div><!-- COL-END -->
				</div>
				<!-- row closed -->
			</div>
			<!-- Container closed -->
		</div>
		<!-- main-content closed -->
@endsection
@section('js')
<!--Internal  Chart.bundle js -->
<script src="{{URL::asset('assets/plugins/chart.js/Chart.bundle.min.js')}}"></script>
@endsection