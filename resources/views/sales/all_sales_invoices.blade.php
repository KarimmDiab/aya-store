@extends('layouts.master')

<title>  معرض أيه - فواتير المبيعات </title>
@section('css')
<!--- Internal Select2 css-->
<link href="{{URL::asset('assets/plugins/select2/css/select2.min.css')}}" rel="stylesheet">
@endsection
@section('page-header')
				<!-- breadcrumb -->
				<div class="breadcrumb-header justify-content-between">
					<div class="my-auto">
						<div class="d-flex">
							<h4 class="content-title mb-0 my-auto">المبيعات</h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/ فواتير المبيعات</span>
						</div>
					</div>
					
				</div>
				<!-- breadcrumb -->
@endsection
@section('content')

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


                	<!--div-->
					<div class="col-xl-12">
						<div class="card">
							<div class="card-header pb-0">
								<div class="d-flex justify-content-between">
									<h4 class="card-title mg-b-0">جدول فواتير المبيعات</h4>
									<i class="mdi mdi-dots-horizontal text-gray"></i>
								</div>
								<p class="tx-12 tx-gray-500 mb-2">جدول عرض  لجميع فواتير المبيعات ..... في حالة التعديل علي بيانات اي فاتورة برجاء الضغط علي <a href="">تعديل بيانات مخازن</a></p>
								
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table class="table text-md-nowrap" id="example2">
										<thead>
											<tr>
												<th class="wd-10p border-bottom-0">رقم الفاتورة</th>
												<th class="wd-10p border-bottom-0">اسم العميل</th>
												<th class="wd-10p border-bottom-0">رقم الموبايل</th>
												<th class="wd-10p border-bottom-0"> طريقة الدفع</th>
												<th class="wd-10p border-bottom-0">إجمالي الفاتورة</th>
												<th class="wd-10p border-bottom-0">المبلغ المدفوع</th>
												<th class="wd-9p border-bottom-0">المتبقي</th>
												<th class="wd-10p border-bottom-0">تاريخ الفاتورة</th>
												<th class="wd-20p border-bottom-0">action</th>
												<th class="wd-10p border-bottom-0">تاريخ اخر تعديل</th>
											</tr>
										</thead>
										<tbody>
											@foreach($salesInvoices as $invoice)
											<tr>
												<td>{{ $invoice->sales_invoice_id }}</td>
												<td>{{ $invoice->customer_name }}</td>
												<td>{{ $invoice->customer_phone }}</td>
												<td>{{ $invoice->payment_method }}</td>
												<td>{{ $invoice->total_invoice }}</td>
												<td>{{ $invoice->invoice_paid_amount }}</td>
												<td>{{ $invoice->adjusted_remaining_amount }}</td>
												<td>{{ $invoice->invoice_date }}</td>
												
												<td>
												    <!-- details Sales Invoice Button -->
													<a class="btn ripple btn-info" data-target="#detailsSalesInvoice{{ $invoice->sales_invoice_id }}" data-toggle="modal" href="">تفاصيل </a>
														<!-- Large Modal for details Sales invoice-->
														<div class="modal" id="detailsSalesInvoice{{ $invoice->sales_invoice_id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                                            <div class="modal-dialog modal-lg" role="document" style="width: 88%; max-width: 88%;">
                                                                <div class="modal-content modal-content-demo">
                                                                    <div class="modal-header">
                                                                        <h6 class="modal-title">تفاصيل فاتورة المبيعات </h6>
                                                                        <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                            <div class="row row-sm">
                                                                                <div class="col-6">
                                                                                    <div class="form-group mg-b-0">
                                                                                        <label class="form-label"> رقم الفاتورة: <span class="tx-danger">*</span></label>
                                                                                        <input class="form-control" name="details_sales_invoices_no" placeholder="قم بإدخال رقم الفاتورة" value="{{ $invoice->sales_invoice_id }}" readonly type="text">
                                                                                     </div>
                                                                                </div>
                                                                                <div class="col-6">
                                                                                    <div class="form-group">
                                                                                        <label class="form-label">تاريخ الفاتورة: <span class="tx-danger">*</span></label>
                                                                                         <input class="form-control" name="details_sales_invoices_date" placeholder="قم بإدخال تاريخ الفاتورة" value="{{ $invoice->invoice_date }}" readonly type="text">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="row row-sm" style="margin-bottom: 45px;">
                                                                                <div class="form-group col-6">
                                                                                    <label for="purchase_invoices_supplier">اسم العميل</label>
																					<input class="form-control" name="details_sales_customer_name" placeholder="برجاء إدخال اسم العميل" value="{{ $invoice->customer_name }}" readonly type="text">
																				</div>
                                                                            </div>
																					<div class="row row-sm" style="margin-bottom: 45px;">
                                                                                <div class="form-group col-6">
                                                                                    <label for="purchase_invoices_supplier">رقم الموبايل</label>
																					<input class="form-control" name="details_sales_customer_phone" placeholder="برجاء إدخال موبايل العميل" value="{{ $invoice->customer_phone }}" readonly type="text">
																				</div>
                                                                            </div>
                                                                            <hr>
                                                                            <div class="table-responsive">
                                                                                <table class="table text-md-nowrap table-bordered" id="invoice-items-table" style="text-align: center;">
                                                                                    <thead>
                                                                                        <tr style="background-color: rgba(25, 145, 117, 0.5);">
                                                                                            <th>المنتج</th>
                                                                                            <th>الكمية</th>
                                                                                            <th>السعر بيع للوحدة</th>
                                                                                            <th>الإجمالي</th>   
                                                                                        </tr>
                                                                                    </thead>
                                                                                     <tbody id="invoice-items-body">
                                                                                        @foreach ($salesInvoicesDetails as $invoiceDetail)
                                                                                            @if ($invoice->sales_invoice_id == $invoiceDetail->sales_invoice_id)
                                                                                                    
                                                                                                 <tr>
                                                                                                    <td>
																										{{ $invoiceDetail->product_name }}
                                                                                                     </td>
                                                                                                    <td>
                                                                                                        {{ $invoiceDetail->product_quantity }}
                                                                                                    </td>
                                                                                                    <td>
                                                                                                        {{ $invoiceDetail->sales_price }}
                                                                                                    </td>
                                                                                                    <td>
                                                                                                         {{ $invoiceDetail->product_total_amount }}
                                                                                                    </td>
                                                                                                            
                                                                                                </tr>
                                                                                             @endif
                                                                                        @endforeach
                                                                                    </tbody>

                                                                                </table>
                                                                            </div>
                                                                            <hr>
                                                                            <div class="">
                                                                                 <div class="col-6">
                                                                                    <div class="form-group">
                                                                                        <label for="purchase_invoices_amount">إجمالي الفاتورة :</label>
                                                                                        <input type="number" step="0.01" id="details_sales_invoices_amount" name="details_sales_invoices_amount" class="form-control" value="{{ $invoice->total_invoice }}" placeholder="مبلغ الفاتورة" readonly>
                                                                                    </div>
                                                                                </div>
																				<div class="col-6">
                                                                                    <div class="form-group">
                                                                                        <label for="purchase_invoices_amount"> طريقة الدفع :</label>
                                                                                        <input type="text" step="0.01" id="details_sales_payment_method" name="details_sales_payment_method" class="form-control" value="{{ $invoice->payment_method }}" placeholder="" readonly>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-6">
                                                                                    <div class="form-group">
                                                                                        <label class="form-label">المبلغ المدفوع:</label>
                                                                                        <input class="form-control" id="details_sales_amount_paid" name="details_sales_amount_paid" type="number" value="{{ $invoice->invoice_paid_amount }}" placeholder="أدخل المبلغ المدفوع" readonly>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-6">
                                                                                    <div class="form-group">
                                                                                        <label class="form-label">المبلغ المتبقي (دين):</label>
                                                                                        <input class="form-control" id="details_sales_remaining_amount" name="details_sales_remaining_amount" type="number" value="{{ $invoice->invoice_remaning_amount }}" placeholder="المبلغ المتبقي" readonly>
                                                                                    </div>
                                                                                </div>         
                                                                            </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">إغلاق</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>									
                                                     <!-- end Large Modal for details Sales invoice-->
     
                                                   	
													<!-- Edit Sales Invoice Button -->
													<a class="btn ripple btn-primary" data-target="#editSalesInvoice{{ $invoice->sales_invoice_id }}" data-toggle="modal" href="">تعديل</a>
													<!-- Large Modal for Details Sales Invoice -->
													<div class="modal" id="editSalesInvoice{{ $invoice->sales_invoice_id }}" tabindex="-1" role="dialog" aria-hidden="true">
														<div class="modal-dialog modal-lg" role="document" style="width: 88%; max-width: 88%;">
															<div class="modal-content modal-content-demo">
																<div class="modal-header">
																	<h6 class="modal-title">تعديل فاتورة المبيعات</h6>
																	<button aria-label="Close" class="close" data-dismiss="modal" type="button">
																		<span aria-hidden="true">&times;</span>
																	</button>
																</div>
																<div class="modal-body">
																	<div class="row row-sm">
																		<!-- Invoice Number -->
																		<div class="col-6">
																			<div class="form-group mg-b-0">
																				<label class="form-label">رقم الفاتورة: <span class="tx-danger">*</span></label>
																				<input class="form-control" name="edit_sales_invoices_no" value="{{ $invoice->sales_invoice_id }}" readonly type="text">
																			</div>
																		</div>
																		<!-- Invoice Date -->
																		<div class="col-6">
																			<div class="form-group">
																				<label class="form-label">تاريخ الفاتورة: <span class="tx-danger">*</span></label>
																				<input class="form-control" name="edit_sales_invoices_date" value="{{ $invoice->invoice_date }}" readonly type="text">
																			</div>
																		</div>
																	</div>

																	<!-- Customer Information -->
																	<div class="row row-sm">
																		<div class="form-group col-6">
																			<label for="edit_sales_customer_name">اسم العميل</label>
																			<input class="form-control" name="edit_sales_customer_name" value="{{ $invoice->customer_name }}" type="text">
																		</div>
																		<div class="form-group col-6">
																			<label for="edit_sales_customer_phone">رقم الموبايل</label>
																			<input class="form-control" name="edit_sales_customer_phone" value="{{ $invoice->customer_phone }}" type="text">
																		</div>
																	</div>
																	<hr>

																	<!-- Invoice Items Table -->
																	<div class="table-responsive">
																		<table class="table text-md-nowrap table-bordered" id="invoice-items-table" style="text-align: center;">
																			<thead>
																				<tr style="background-color: rgba(25, 145, 117, 0.5);">
																					<th>المنتج</th>
																					<th>الكمية</th>
																					<th>السعر بيع للوحدة</th>
																					<th>الإجمالي</th>
																				</tr>
																			</thead>
																			<tbody>
																				@foreach ($salesInvoicesDetails as $invoiceDetail)
																					@if ($invoice->sales_invoice_id == $invoiceDetail->sales_invoice_id)
																						<tr>
																							<td>
																								<select name="product_name[]" class="form-control">
																									@foreach ($products as $product)
																										<option value="{{ $product->id }}" {{ $product->id == $invoiceDetail->product_id ? 'selected' : '' }}>
																											{{ $product->name }}
																										</option>
																									@endforeach
																								</select>
																							</td>
																							<td>
																								<input type="number" name="quantity[]" class="form-control product-quantity" value="{{ $invoiceDetail->product_quantity }}">
																							</td>
																							<td>
																								<input type="number" step="0.01" name="unit_price[]" class="form-control product-price" value="{{ $invoiceDetail->sales_price }}" readonly>
																							</td>
																							<td>
																								<input type="number" step="0.01" name="total[]" class="form-control product-total" value="{{ $invoiceDetail->product_total_amount }}" readonly>
																							</td>
																						</tr>
																					@endif
																				@endforeach
																			</tbody>
																		</table>
																	</div>
																	<hr>

																	<!-- Totals -->
																	<div class="row">
																		<div class="col-6">
																			<div class="form-group">
																				<label for="edit_sales_invoices_amount">إجمالي الفاتورة:</label>
																				<input type="number" step="0.01" id="edit_sales_invoices_amount" name="edit_sales_invoices_amount" class="form-control" value="{{ $invoice->total_invoice }}" readonly>
																			</div>
																		</div>
																		<div class="col-6">
																			<div class="form-group">
																				<label for="edit_sales_payment_method">طريقة الدفع:</label>
																				<select id="edit_sales_payment_method" name="edit_sales_payment_method" class="form-control">
																					<option value="cash" {{ $invoice->payment_method == 'cash' ? 'selected' : '' }}>نقداً</option>
																					<option value="credit" {{ $invoice->payment_method == 'credit' ? 'selected' : '' }}>بالدين</option>
																				</select>
																			</div>
																		</div>
																		<div class="col-6">
																			<div class="form-group">
																				<label class="form-label">المبلغ المدفوع:</label>
																				<input class="form-control" id="edit_sales_amount_paid" name="edit_sales_amount_paid" type="number" value="{{ $invoice->invoice_paid_amount }}">
																			</div>
																		</div>
																		<div class="col-6">
																			<div class="form-group">
																				<label class="form-label">المبلغ المتبقي (دين):</label>
																				<input class="form-control" id="edit_sales_remaining_amount" name="edit_sales_remaining_amount" type="number" value="{{ $invoice->invoice_remaning_amount }}" readonly>
																			</div>
																		</div>
																	</div>
																</div>

																<div class="modal-footer">
																	<button class="btn ripple btn-secondary" data-dismiss="modal" type="button">إغلاق</button>
																	<button class="btn ripple btn-primary" type="submit">حفظ التعديلات</button>
																</div>
															</div>
														</div>
													</div>								
                                                     <!-- end Large Modal for details Sales invoice-->

													 
													<!-- Print Sales Invoice Button -->
													<form action="{{ route('invoice.show', $invoice->sales_invoice_id) }}" method="get" style="display: inline;" onsubmit="return openInvoicePopup(this);">
														<button type="submit" class="btn btn-warning">طباعة</button>
													</form>
													 <!-- end print Sales Invoice Button -->

													<!-- delete Sales Invoice Button -->
													<button class="btn btn-danger" data-toggle="modal" data-target="#deleteSalesInvoce{{ $invoice->sales_invoice_id }}">مسح</button>
													<div class="modal fade" id="deleteSalesInvoce{{ $invoice->sales_invoice_id }}" tabindex="-1" role="dialog" aria-labelledby="deleteWarehouseLabel" aria-hidden="true">
														<div class="modal-dialog" role="document">
															<div class="modal-content">
																<div class="modal-header">
																	<h5 class="modal-title" id="deleteWarehouseLabel">تأكيد الحذف</h5>
																	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
																		<span aria-hidden="true">&times;</span>
																	</button>
																</div>
																<div class="modal-body">
																	هل أنت متأكد من أنك تريد حذف هذة الفاتورة؟
																</div>
																<div class="modal-footer">
																	<form action="{{ route('invoice.destroy', $invoice->sales_invoice_id) }}" method="POST">
																		@csrf
																		@method('DELETE')
																		<button type="submit" class="btn btn-danger">مسح</button>
																	</form>
																	<button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
																</div>
															</div>
														</div>
													</div>
													<!-- end delete Sales Invoice Button -->

												</td>
												<td>{{ $invoice->invoice_update }}</td>
											</tr>
											@endforeach

											
										</tbody>
									</table>
								</div>
							</div><!-- bd -->
						</div><!-- bd -->
					</div>
					<!--/div-->

	
				<!-- row closed -->
			</div>
			<!-- Container closed -->
		</div>
		<!-- main-content closed -->


		


		
@endsection

@section('js')
<!--Internal  Select2 js -->
<script src="{{URL::asset('assets/plugins/select2/js/select2.min.js')}}"></script>
<!--Internal  Parsley.min js -->
<script src="{{URL::asset('assets/plugins/parsleyjs/parsley.min.js')}}"></script>
<!-- Internal Form-validation js -->
<script src="{{URL::asset('assets/js/form-validation.js')}}"></script>
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

<script>
    document.addEventListener("input", function (e) {
        // Function to calculate the product total for a specific row
        const calculateProductTotal = (row) => {
            const quantity = parseFloat(row.querySelector(".product-quantity").value) || 0;
            const price = parseFloat(row.querySelector(".product-price").value) || 0;
            const totalField = row.querySelector(".product-total");
            const total = quantity * price;
            totalField.value = total.toFixed(2);
        };

        // Function to calculate invoice total dynamically
        const updateInvoiceTotal = () => {
            let invoiceTotal = 0;
            const allTotals = document.querySelectorAll(".product-total");
            allTotals.forEach((field) => {
                invoiceTotal += parseFloat(field.value) || 0;
            });
            document.getElementById("edit_sales_invoices_amount").value = invoiceTotal.toFixed(2);
            return invoiceTotal;
        };

        // Function to calculate the remaining amount dynamically
        const updateRemainingAmount = () => {
            const invoiceTotal = parseFloat(document.getElementById("edit_sales_invoices_amount").value) || 0;
            const paidAmount = parseFloat(document.getElementById("edit_sales_amount_paid").value) || 0;
            const remainingAmount = invoiceTotal - paidAmount;
            document.getElementById("edit_sales_remaining_amount").value = remainingAmount.toFixed(2);
        };

        // Handle changes in product quantity or price
        if (e.target.classList.contains("product-quantity") || e.target.classList.contains("product-price")) {
            const row = e.target.closest("tr");
            calculateProductTotal(row);

            // Update the invoice total and remaining amount
            updateInvoiceTotal();
            updateRemainingAmount();
        }

        // Handle changes in paid amount
        if (e.target.id === "edit_sales_amount_paid") {
            updateRemainingAmount();
        }
    });

    // Automatically calculate totals for all rows on page load
    window.addEventListener("DOMContentLoaded", () => {
        const rows = document.querySelectorAll("#invoice-items-body tr");
        rows.forEach((row) => {
            const quantityField = row.querySelector(".product-quantity");
            const priceField = row.querySelector(".product-price");
            if (quantityField && priceField) {
                calculateProductTotal(row);
            }
        });

        // Update the totals after initial calculation
        const invoiceTotal = updateInvoiceTotal();
        updateRemainingAmount();
    });
</script>
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


@endsection