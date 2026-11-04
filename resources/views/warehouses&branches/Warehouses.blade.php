@extends('layouts.master')

<title> معرض أيه - المخازن </title>
@section('css')
	<!--- Internal Select2 css-->
	<link href="{{URL::asset('assets/plugins/select2/css/select2.min.css')}}" rel="stylesheet">
@endsection
@section('page-header')
	<!-- breadcrumb -->
	<div class="breadcrumb-header justify-content-between">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto">المخازن و الفروع</h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/
					المخازن</span>
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
					<h4 class="card-title mg-b-0">جدول المخازن</h4>
					<i class="mdi mdi-dots-horizontal text-gray"></i>
				</div>
				<p class="tx-12 tx-gray-500 mb-2">جدول عرض لجميع المخازن ..... في حالة التعديل علي بيانات اي مخزن برجاء
					الضغط علي <a href="">تعديل بيانات مخازن</a></p>
				<div class="col-4" style="margin-top: 10px;">
					<a class="btn ripple btn-primary" data-target="#addWarehouse" data-toggle="modal" href=""
						style="margin: 15px; color: white;">إضافة مخزن جديد </a>
				</div>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table text-md-nowrap" id="example2">
						<thead>
							<tr>
								<th class="wd-15p border-bottom-0">كود المخزن</th>
								<th class="wd-15p border-bottom-0">اسم المخزن</th>
								<th class="wd-20p border-bottom-0">عنوان المخزن</th>
								<th class="wd-15p border-bottom-0">تاريخ التسجيل </th>
								<th class="wd-15p border-bottom-0">action</th>
								<th class="wd-10p border-bottom-0">تاريخ اخر تعديل</th>
							</tr>
						</thead>
						<tbody>
							@foreach($warehouses as $warehouse)
								<tr>
									<td>{{ $warehouse->id }}</td>
									<td>{{ $warehouse->name }}</td>
									<td>{{ $warehouse->warehouse_address }}</td>
									<td>{{ $warehouse->created_at }}</td>
									<td>
										<a class="btn ripple btn-info" data-target="#editWarehouse{{ $warehouse->id }}"
											data-toggle="modal" href="">تعديل</a>
										<!-- Basic modal to edit warehouse-->
										<div class="modal" id="editWarehouse{{ $warehouse->id }}">
											<div class="modal-dialog" role="document">
												<div class="modal-content modal-content-demo">
													<div class="modal-header">
														<h6 class="modal-title">تعديل في بيانات المخزن</h6>
														<button aria-label="Close" class="close" data-dismiss="modal"
															type="button">
															<span aria-hidden="true">&times;</span>
														</button>
													</div>
													<form action="{{ route('updateWarehouses', $warehouse->id) }}"
														method="POST">
														@csrf
														@method('POST')
														<div class="modal-body">
															<div class="">
																<div class="form-group">
																	<label for="exampleInputEmail1">كود المخزن</label>
																	<input type="text" class="form-control"
																		id="exampleInputEmail1" value="{{ $warehouse->id }}"
																		placeholder="Enter email" readonly>
																</div>
																<div class="form-group">
																	<label for="editWarehouseName">اسم المخزن</label>
																	<input type="text" class="form-control"
																		id="editWarehouseName{{ $warehouse->id }}" name="name"
																		value="{{ $warehouse->name }}" placeholder="اسم المخزن">
																	<div id="editWarehouseNameError{{ $warehouse->id }}"
																		class="text-danger"></div> <!-- Error message div -->
																</div>
																<div class="form-group">
																	<label for="editWarehouseAddress">عنوان المخزن</label>
																	<input type="text" class="form-control"
																		id="editWarehouseAddress{{ $warehouse->id }}"
																		name="warehouse_address"
																		value="{{ $warehouse->warehouse_address }}"
																		placeholder="عنوان المخزن">
																	<div id="editWarehouseAddressError{{ $warehouse->id }}"
																		class="text-danger"></div> <!-- Error message div -->
																</div>
															</div>
														</div>
														<div class="modal-footer">
															<!-- Change button type to "submit" -->
															<button class="btn ripple btn-primary" type="submit">حفظ</button>
															<button class="btn ripple btn-secondary" data-dismiss="modal"
																type="button">إغلاق</button>
														</div>
													</form>
												</div>
											</div>
										</div>
										<!-- End Basic modal to edit warehouse -->



										<button class="btn btn-danger" data-toggle="modal"
											data-target="#deleteWarehouse{{ $warehouse->id }}">مسح</button>
										<!-- Modal for confirmation -->
										<div class="modal fade" id="deleteWarehouse{{ $warehouse->id }}" tabindex="-1"
											role="dialog" aria-labelledby="deleteWarehouseLabel" aria-hidden="true">
											<div class="modal-dialog" role="document">
												<div class="modal-content">
													<div class="modal-header">
														<h5 class="modal-title" id="deleteWarehouseLabel">تأكيد الحذف</h5>
														<button type="button" class="close" data-dismiss="modal"
															aria-label="Close">
															<span aria-hidden="true">&times;</span>
														</button>
													</div>
													<div class="modal-body">
														هل أنت متأكد من أنك تريد حذف هذا المخزن؟
													</div>
													<div class="modal-footer">
														<form action="{{ route('deleteWarehouses', $warehouse->id) }}"
															method="POST">
															@csrf
															@method('DELETE')
															<button type="submit" class="btn btn-danger">مسح</button>
														</form>
														<button type="button" class="btn btn-secondary"
															data-dismiss="modal">إلغاء</button>
													</div>
												</div>
											</div>
										</div>

									</td>
									<td>{{ $warehouse->updated_at }}</td>
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



	<!-- Add Warehouse Modal -->
	<div class="modal" id="addWarehouse">
		<div class="modal-dialog" role="document">
			<div class="modal-content modal-content-demo">
				<div class="modal-header">
					<h6 class="modal-title">إضافة مخزن جديد</h6>
					<button aria-label="Close" class="close" data-dismiss="modal" type="button">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form action="{{ route('add_Warehouses') }}" method="POST" class="addWarehouses">
					@csrf
					<div class="modal-body">
						<div class="form-group">
							<label for="addWarehouseName">اسم المخزن</label>
							<input type="text" class="form-control" id="addWarehouseName" name="name"
								placeholder="اسم المخزن">
							<div id="addWarehouseNameError" class="text-danger"></div> <!-- Error message div -->
						</div>
						<div class="form-group">
							<label for="addWarehouseAddress">عنوان المخزن</label>
							<input type="text" class="form-control" id="addWarehouseAddress" name="warehouse_address"
								placeholder="عنوان المخزن">
							<div id="addWarehouseAddressError" class="text-danger"></div> <!-- Error message div -->
						</div>
					</div>
					<div class="modal-footer">
						<button class="btn ripple btn-primary" type="submit">حفظ</button>
						<button class="btn ripple btn-secondary" data-dismiss="modal" type="button">إغلاق</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<!-- End Basic modal to add warehouse -->



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
		document.addEventListener("DOMContentLoaded", function () {
			// Function to validate Arabic/English letters, numbers, spaces, and hyphens
			function validateInput(input, errorDiv) {
				const validPattern = /^[\p{L}\d\s-]+$/u; // Allows letters (Arabic & English), numbers, spaces, and hyphens
				const errorElement = document.getElementById(errorDiv);

				if (!validPattern.test(input.value)) {
					errorElement.innerHTML = "برجاء إدخال حروف عربية أو إنجليزية، أرقام، مسافات، أو (-) فقط";
					return false;
				}

				errorElement.innerHTML = ""; // Clear any previous error message
				return true;
			}

			// Function to add validation on form submit
			function addValidationToForm(form, isEdit = false) {
				form.addEventListener("submit", function (e) {
					const warehouseName = form.querySelector("input[id^='addWarehouseName']") ||
						form.querySelector("input[id^='editWarehouseName']");
					const warehouseAddress = form.querySelector("input[id^='addWarehouseAddress']") ||
						form.querySelector("input[id^='editWarehouseAddress']");

					const nameErrorDiv = isEdit ? form.querySelector("div[id^='editWarehouseNameError']") :
						form.querySelector("div[id^='addWarehouseNameError']");
					const addressErrorDiv = isEdit ? form.querySelector("div[id^='editWarehouseAddressError']") :
						form.querySelector("div[id^='addWarehouseAddressError']");

					let isValid = true;

					// Validate warehouse name
					if (!validateInput(warehouseName, nameErrorDiv.id)) {
						isValid = false;
					}

					// Validate warehouse address
					if (!validateInput(warehouseAddress, addressErrorDiv.id)) {
						isValid = false;
					}

					if (!isValid) {
						e.preventDefault(); // Prevent form submission if validation fails
					}
				});
			}

			// Add validation to the "Add Warehouse" form
			const addWarehouseForm = document.querySelector(".addWarehouses");
			if (addWarehouseForm) {
				addValidationToForm(addWarehouseForm);
			}

			// Add validation to all "Edit Warehouse" forms
			document.querySelectorAll(".editWarehouses").forEach((form) => {
				addValidationToForm(form, true);
			});
		});
	</script>




@endsection