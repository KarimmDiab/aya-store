@extends('layouts.master')

<title>  معرض أيه - الفروع </title>
@section('css')
<!--- Internal Select2 css-->
<link href="{{URL::asset('assets/plugins/select2/css/select2.min.css')}}" rel="stylesheet">
@endsection
@section('page-header')
				<!-- breadcrumb -->
				<div class="breadcrumb-header justify-content-between">
					<div class="my-auto">
						<div class="d-flex">
							<h4 class="content-title mb-0 my-auto">المخازن و الفروع</h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/ الفروع</span>
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

@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
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
									<h4 class="card-title mg-b-0">جدول الفروع</h4>
									<i class="mdi mdi-dots-horizontal text-gray"></i>
								</div>
								<p class="tx-12 tx-gray-500 mb-2">جدول عرض  لجميع الفروع ..... في حالة التعديل علي بيانات اي مخزن برجاء الضغط علي <a href="">تعديل بيانات مخازن</a></p>
								<div class="col-4" style="margin-top: 10px;">
									<a class="btn ripple btn-primary" data-target="#addbranche" data-toggle="modal" href="" style="margin: 15px;">إضافة فرع جديد  </a>
								</div>
							</div>
							<div class="card-body">
								<div class="table-responsive">
								<table class="table text-md-nowrap" id="example2">
    <thead>
        <tr>
            <th class="wd-15p border-bottom-0">كود الفرع</th>
            <th class="wd-15p border-bottom-0">اسم الفرع</th>
            <th class="wd-20p border-bottom-0">عنوان الفرع</th>
            <th class="wd-15p border-bottom-0">تاريخ التسجيل</th>
            <th class="wd-15p border-bottom-0">Action</th>
            <th class="wd-10p border-bottom-0">تاريخ اخر تعديل</th>
        </tr>
    </thead>
    <tbody>
        @foreach($branches as $branch)
        <tr>
            <td>{{ $branch->id }}</td>
            <td>{{ $branch->name }}</td>
            <td>{{ $branch->location }}</td>
            <td>{{ $branch->created_at }}</td>
            <td>
                <!-- Edit Button -->
                <a class="btn ripple btn-info" data-toggle="modal" href="#editBranchModal{{ $branch->id }}">تعديل</a>

                <!-- Edit Modal -->
                <div class="modal fade" id="editBranchModal{{ $branch->id }}" tabindex="-1" role="dialog" aria-labelledby="editBranchModalLabel{{ $branch->id }}" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h6 class="modal-title">تعديل في بيانات الفرع</h6>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
							<form action="{{ route('branches_update', $branch->id) }}" method="POST">
								@csrf
								@method('PUT')
								<div class="modal-body">
									<div class="form-group">
										<label for="edit_branche_name{{ $branch->id }}">اسم الفرع</label>
										<input type="text" class="form-control" name="branche_name" id="edit_branche_name{{ $branch->id }}" value="{{ old('branche_name', $branch->name) }}" required>
										@error('branche_name')
											<div class="text-danger">{{ $message }}</div>
										@enderror
									</div>
									<div class="form-group">
										<label for="edit_branche_location{{ $branch->id }}">عنوان الفرع</label>
										<input type="text" class="form-control" name="branche_location" id="edit_branche_location{{ $branch->id }}" value="{{ old('branche_location', $branch->location) }}" required>
										@error('branche_location')
											<div class="text-danger">{{ $message }}</div>
										@enderror
									</div>
								</div>
								<div class="modal-footer">
									<button type="submit" class="btn ripple btn-primary">حفظ</button>
									<button type="button" class="btn ripple btn-secondary" data-dismiss="modal">إغلاق</button>
								</div>
							</form>

                        </div>
                    </div>
                </div>
                
                <!-- Delete Button -->
                <form action="{{ route('branches_destroy', $branch->id) }}" method="POST" style="display:inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا الفرع؟')">مسح</button>
                </form>
            </td>
            <td>{{ $branch->updated_at }}</td>
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


		
<!-- Basic modal to add branch -->
<div class="modal" id="addbranche">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">إضافة فرع جديد</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addBranchForm" class="addBranch" action="{{ route('branches_store') }}" method="POST">
                @csrf

                <!-- Alert div for error messages -->
                <div id="errorAlert" class="alert alert-danger" style="display: none;">
                    <ul id="errorList"></ul>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label for="branche_name">اسم الفرع</label>
                        <input type="text" class="form-control" name="branche_name" id="branche_name"
                               placeholder="ادخل اسم الفرع" required>
                        <div id="addBranchNameError" class="text-danger"></div>
                    </div>
                    <div class="form-group">
                        <label for="branche_location">عنوان الفرع</label>
                        <input type="text" class="form-control" name="branche_location" id="branche_location"
                               placeholder="ادخل عنوان الفرع" required>
                        <div id="addBranchLocationError" class="text-danger"></div>
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
<!-- End Basic modal to add branch -->



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

// Function to validate inputs for letters, numbers, and spaces only, and display errors
function validateInput(input, errorDiv) {
    const regex = /^[A-Za-z0-9\s\u0600-\u06FF]+$/; // Allows Arabic, English letters, numbers, and spaces
    if (!input.value.match(regex)) {
        document.getElementById(errorDiv).innerText = "برجاء ادخال حروف، أرقام، أو مسافات فقط";
        return false;
    }
    document.getElementById(errorDiv).innerText = ''; // Clear any previous error
    return true;
}


    // Function to add validation to form inputs
    function addValidationToBranchForm(form, isEdit = false) {
        form.addEventListener('submit', function (e) {
            const branchName = form.querySelector(isEdit ? '#edit_branche_name' : '#branche_name');
            const branchLocation = form.querySelector(isEdit ? '#edit_branche_location' : '#branche_location');

            const nameErrorDiv = form.querySelector(isEdit ? '#editBranchNameError' : '#addBranchNameError');
            const locationErrorDiv = form.querySelector(isEdit ? '#editBranchLocationError' : '#addBranchLocationError');

            let isValid = true;

            // Validate branch name
            if (!validateInput(branchName, nameErrorDiv.id)) {
                isValid = false;
            }

            // Validate branch location
            if (!validateInput(branchLocation, locationErrorDiv.id)) {
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault(); // Prevent form submission if validation fails
            }
        });
    }

    // Add validation to the addBranch form
    const addBranchForm = document.querySelector('#addBranchForm');
    if (addBranchForm) {
        addValidationToBranchForm(addBranchForm);
    }

    // Add validation to the editBranch form
    const editBranchForm = document.querySelector('#editBranchForm');
    if (editBranchForm) {
        addValidationToBranchForm(editBranchForm, true);
    }
</script>


@endsection