@extends('layouts.master3')
<title>  معرض أيه -  طباعة الفاتورة </title>

<style>
	span, td {
		font-weight: bolder;
		font-size: 20px;
	}

	@media print {
		.btn,
		.btn-danger,
		.btn-secondary {
			display: none !important; /* Force hide the buttons */
		}
	}
</style>

	
@section('content')
				<!-- row -->
				<div class="row row-sm">
					<div class="col-md-12 col-xl-12">
						<div class=" main-content-body-invoice">
							<div class="card card-invoice">
								<div class="card-body" id="printArea">
									<div class="invoice-header" style="text-align: center !important; display: block;">
										<img src="{{URL::asset('images/logo/aya_black.png')}}">
										</div><!-- billed-from -->
									</div><!-- invoice-header -->
									<div class="row mg-t-20">
										
										<div class="col-md">
											<label class="tx-gray-600">معلومات الفاتورة</label>
											<p class="invoice-info-row"><span>رقم الفاتورة</span> <span>{{ $invoice->id }}</span></p>
							<p class="invoice-info-row"><span>اسم العميل</span> <span>{{ $customer->name ?? 'بدون بيانات عميل' }}</span></p>
							<p class="invoice-info-row"><span>رقم الموبايل</span> <span>{{ $customer->phone ?? '—' }}</span></p>
											<p class="invoice-info-row"><span>تاريخ الفاتورة</span> <span>{{ \Carbon\Carbon::parse($invoice->created_at)->format('d/m/Y') }}</span></p>
											<p class="invoice-info-row"><span>الموظف المسئول</span> <span>{{ $user->name ?? 'N/A' }}</span></p>
										</div>
									</div>
									<div class="table-responsive mg-t-40">
										<table class="table table-invoice border text-md-nowrap mb-0">
											<thead>
												<tr>
													<th class="wd-20p">المنتج</th>
													<th class="tx-center">الكمية</th>
													<th class="tx-right">سعر الوحدة</th>
													<th class="tx-right">الإجمالي</th>
												</tr>
											</thead>
											<tbody>
											@foreach($invoiceItems as $item)

												<tr>
													<td>{{ $item->product_name }}</td>
													<td class="tx-center">{{ $item->quantity }}</td>
													<td class="tx-right">{{ number_format($item->unit_price, 2) }} ج.م</td>
													<td class="tx-right">{{ number_format($item->total_amount, 2) }} ج.م</td>
												</tr>
												@endforeach


												<tr>
													
													<td class="tx-right">إجمالي الفاتورة</td>
													<td class="tx-right" colspan=""><h4 class="tx-primary tx-bold">{{ number_format($total, 2) }} ج.م</h4></td>
												</tr>
												<tr>

													<td class="tx-right">خصم </td>
													<td class="tx-right" colspan=""><h4 class="tx-primary tx-bold">{{ number_format($deduction, 2) }} ج.م</h4></td>
												</tr>
												<tr>

													<td class="tx-right">طريقة الدفع</td>
													<td class="tx-right" colspan=""><h4 class="tx-primary tx-bold">{{ ucfirst($invoice->payment_method) }}</h4></td>
												</tr>
												<tr>

													<td class="tx-right">المبلغ المدفع</td>
													<td class="tx-right" colspan=""><h4 class="tx-primary tx-bold">{{ number_format($invoice->paid_amount, 2) }} ج.م</h4></td>
												</tr>
												<tr>

													<td class="tx-right">المبلغ المتبقي</td>
													<td class="tx-right" colspan=""><h4 class="tx-primary tx-bold">{{ number_format($invoice->remaining_amount, 2) }} ج.م</h4></td>
												</tr>

											</tbody>
										</table>
									</div>
									<hr class="mg-b-40">
									
									<a href="javascript:void(0);" class="btn btn-danger float-left mt-3 mr-2" onclick="window.print();">
										<i class="mdi mdi-printer ml-1"></i>Print
									</a>
									<a href="{{ route('sales_invoice') }}" class="btn btn-secondary float-left mt-3 mr-2">
										<i class="mdi mdi-arrow-left ml-1"></i>Back
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
@endsection
