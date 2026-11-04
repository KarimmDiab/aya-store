@extends('layouts.master')



<title>  معرض أيه - الموردين </title>
@section('css')
<!--- Internal Select2 css-->
<link href="{{URL::asset('assets/plugins/select2/css/select2.min.css')}}" rel="stylesheet">
@endsection
@section('page-header')
				<!-- breadcrumb -->
				<div class="breadcrumb-header justify-content-between">
					<div class="my-auto">
						<div class="d-flex">
							<h4 class="content-title mb-0 my-auto"> المشتريات</h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/  الموردين</span>
						</div>
					</div>
					
				</div>
				<!-- breadcrumb -->
@endsection
@section('content')
			
<!-- Display Success Message -->
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<!-- Display Validation Errors -->
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
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



                					<!--div-->
					<div class="col-xl-12">
						<div class="card">

								<div class="card-header pb-0 ">
									<div class="d-flex justify-content-between">
										<h4 class="card-title mg-b-0">جدول الموردين</h4>
									</div>
									<p class="tx-12 tx-gray-500 mb-2">جدول عرض  لجميع الموردين .... في حالة التعديل في بيانات اي مورد برجاء الضغط علي <a href="">زر تعديل </a></p>
									<div class="col-4" style="margin-top: 10px;">
									<a class="btn ripple btn-primary" data-toggle="modal" data-target="#addSupplier" style="margin: 15px; color:white">إضافة مورد جديد</a>									</div>
								</div>
								
							

							<div class="card-body">
								<div class="table-responsive">
								<table class="table text-md-nowrap" id="example2">
									<thead>
										<tr>
											<th class="wd-15p">كود المورد</th>
											<th class="wd-15p">اسم المورد</th>
											<th class="wd-15p">رقم الموبايل</th>
											<th class="wd-15p">عنوان المورد</th>
											<th class="wd-25p">تاريخ التسجيل</th>
											<th class="wd-25p">Action</th>
											<th class="wd-25p">تاريخ اخر تعديل</th>
										</tr>
									</thead>
									<tbody>
										@foreach($suppliers as $supplier)
										<tr>
											<td>{{ $supplier->id }}</td>
											<td>{{ $supplier->name }}</td>
											<td>{{ $supplier->contact_info }}</td>
											<td>{{ $supplier->address }}</td>
											<td>{{ $supplier->created_at }}</td>
											<td>
                                                <a href="{{ route('showForAllSuppliers', ['supplier_id' => encrypt($supplier->id)]) }}" class="btn ripple btn-info">
                                                    تفاصيل   
                                                </a>    
												<!-- Edit Button -->
												<button class="btn ripple btn-primary" data-toggle="modal" data-target="#editSupplierModal{{ $supplier->id }}">تعديل</button>

												<!-- Delete Button -->
												<button class="btn btn-danger" data-toggle="modal" data-target="#deleteSupplierModal{{ $supplier->id }}">مسح</button>
											</td>
											<td>{{ $supplier->updated_at }}</td>
										</tr>

										<!-- Edit Supplier Modal -->
										<div class="modal fade" id="editSupplierModal{{ $supplier->id }}" tabindex="-1" role="dialog" aria-hidden="true">
											<div class="modal-dialog" role="document">
                                                <!-- Edit Supplier Form -->
                                                <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">تعديل بيانات مورد</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label>كود المورد</label>
                                                                <input type="text" class="form-control" name="id" value="{{ $supplier->id }}" readonly>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>اسم المورد</label>
                                                                <input type="text" class="form-control" name="name" value="{{ $supplier->name }}" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>عنوان المورد</label>
                                                                <input type="text" class="form-control" name="address" value="{{ $supplier->address }}" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>رقم الموبايل</label>
                                                                <input type="text" class="form-control" name="contact_info" value="{{ $supplier->contact_info }}" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="supplier_category">التصنيف الخاص بالمورد</label>
                                                                <select class="form-control" name="supplier_category[]" id="supplier_category" multiple required>
                                                                    @foreach ($categories as $category)
                                                                    <option value="{{ $category->category_id }}" 
                                                                        @if (isset($selectedCategories[$supplier->id]) && in_array($category->category_id, $selectedCategories[$supplier->id])) 
                                                                            selected 
                                                                        @endif>
                                                                        {{ $category->category_name }}
                                                                    </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-primary">حفظ</button>
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                                                        </div>
                                                    </div>
                                                </form>

											</div>
										</div>

										<!-- Delete Supplier Modal -->
										<div class="modal fade" id="deleteSupplierModal{{ $supplier->id }}" tabindex="-1" role="dialog" aria-hidden="true">
											<div class="modal-dialog" role="document">
												<form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST">
													@csrf
													@method('DELETE')
													<div class="modal-content">
														<div class="modal-header">
															<h5 class="modal-title">تأكيد الحذف</h5>
															<button type="button" class="close" data-dismiss="modal" aria-label="Close">
																<span aria-hidden="true">&times;</span>
															</button>
														</div>
														<div class="modal-body">
															<p>هل أنت متأكد من أنك تريد حذف هذا المورد؟</p>
														</div>
														<div class="modal-footer">
															<button type="submit" class="btn btn-danger">حذف</button>
															<button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
														</div>
													</div>
												</form>
											</div>
										</div>
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


<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplier" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
<!-- Add Supplier Form -->
<form action="{{ route('suppliers.store') }}" method="POST">
    @csrf
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">إضافة مورد جديد</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="supplierName">اسم المورد</label>
                <input type="text" class="form-control" name="name" id="supplierName" placeholder="أدخل اسم المورد" required>
            </div>
            <div class="form-group">
                <label for="supplierAddress">عنوان المورد</label>
                <input type="text" class="form-control" name="address" id="supplierAddress" placeholder="أدخل عنوان المورد" required>
            </div>
            <div class="form-group">
                <label for="supplierPhone">رقم الموبايل</label>
                <input type="text" class="form-control" name="contact_info" id="supplierPhone" placeholder="أدخل رقم الموبايل" required>
            </div>
            <div class="form-group">
                <label for="supplier_category">التصنيف الخاص بالمورد</label>
                <select class="form-control" name="supplier_category[]" id="supplier_category" multiple required>
                    @foreach ($categories as $category)
                        <option value="{{ $category->category_id }}">{{ $category->category_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn ripple btn-primary">حفظ</button>
            <button type="button" class="btn ripple btn-secondary" data-dismiss="modal">إغلاق</button>
        </div>
    </div>
</form>

    </div>
</div>
<!-- End Modal -->



		
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

<!--Internal  Datepicker js -->
<script src="{{URL::asset('assets/plugins/jquery-ui/ui/widgets/datepicker.js')}}"></script>

<!-- Internal Modal js-->
<script src="{{URL::asset('assets/js/modal.js')}}"></script>


<script>
    // Add event listener for form submission
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(event) {
        // Clear previous error messages
            clearErrors();

            // Flag to check if form is valid
            let isValid = true;

            // Get input values for both forms
            const name = form.querySelector('[name="name"]').value;
            const address = form.querySelector('[name="address"]').value;
            const contactInfo = form.querySelector('[name="contact_info"]').value;

            // Validate name (Arabic letters, English letters, numbers, and spaces only)
const nameRegex = /^[\u0600-\u06FFa-zA-Z0-9\s]+$/;
if (!nameRegex.test(name)) {
    isValid = false;
    showError(form, 'name', 'اسم المورد يجب أن يحتوي على الحروف العربية، الحروف الإنجليزية، الأرقام والمسافات فقط');
}

// Validate address (Arabic letters, English letters, numbers, and spaces only)
const addressRegex = /^[\u0600-\u06FFa-zA-Z0-9\s]+$/;
if (!addressRegex.test(address)) {
    isValid = false;
    showError(form, 'address', 'عنوان المورد يجب أن يحتوي على الحروف العربية، الحروف الإنجليزية، الأرقام والمسافات فقط');
}

// Validate phone number (starts with 01 and 11 digits)
const phoneRegex = /^01[0-9]{9}$/;
if (!phoneRegex.test(contactInfo)) {
    isValid = false;
    showError(form, 'contact_info', 'رقم الهاتف يجب أن يبدأ بـ 01 ويحتوي على 11 رقمًا');
}

// If validation failed, prevent form submission
if (!isValid) {
    event.preventDefault(); // Prevent form submission
}

        });
    });

    // Function to display error message under the field
    function showError(form, fieldName, message) {
        const inputField = form.querySelector('[name="' + fieldName + '"]');
        const errorDiv = document.createElement('div');
        errorDiv.classList.add('text-danger');
        errorDiv.innerText = message;
        inputField.classList.add('is-invalid'); // Optional: Highlight the input field
        inputField.parentNode.appendChild(errorDiv);
    }

    // Function to clear previous error messages
    function clearErrors() {
        const errorMessages = document.querySelectorAll('.text-danger');
        errorMessages.forEach(function(error) {
            error.remove();
        });

        const invalidFields = document.querySelectorAll('.is-invalid');
        invalidFields.forEach(function(field) {
            field.classList.remove('is-invalid');
        });
    }
</script>


@endsection