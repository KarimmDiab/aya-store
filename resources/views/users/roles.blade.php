@extends('layouts.master')



<title>  معرض أيه - المجموعات الادارية </title>
@section('css')
<!--- Internal Select2 css-->
<link href="{{URL::asset('assets/plugins/select2/css/select2.min.css')}}" rel="stylesheet">
@endsection
@section('page-header')
				<!-- breadcrumb -->
				<div class="breadcrumb-header justify-content-between">
					<div class="my-auto">
						<div class="d-flex">
							<h4 class="content-title mb-0 my-auto">المستخدمين و الصلاحيات</h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/  المجموعات الادارية</span>
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

								<div class="card-header pb-0 ">
									<div class="d-flex justify-content-between">
										<h4 class="card-title mg-b-0">جدول المجموعات الادارية</h4>
									</div>
									<p class="tx-12 tx-gray-500 mb-2">جدول عرض  لجميع المجموعات الادارية .... في حالة التعديل في بيانات اي مجموعة إدارية برجاء الضغط علي <a href="">زر تعديل </a></p>
									<div class="col-4" style="margin-top: 10px;">
									<a class="btn ripple btn-primary" data-target="#addRole" data-toggle="modal" href="" style="margin: 15px; color:white">إضافة مجموعة إدارية جديد  </a>
									</div>
								</div>
								
							

							<div class="card-body">
								<div class="table-responsive">
									<table class="table text-md-nowrap" id="example2">
										<thead>
											<tr>
												<th class="wd-10p border-bottom-0">كود المجموعة</th>
												<th class="wd-15p border-bottom-0">اسم المجموعة الادارية</th>
												<th class="wd-40p border-bottom-0">وصف المجموعة الادارية</th>
												<th class="wd-15p border-bottom-0">عدد المستخدمين</th>
												<th class="wd-25p border-bottom-0"> action</th>
												<th class="wd-40p border-bottom-0">تاريخ اخر تعديل علي بيانات المجموعة الادارية</th>

											</tr>
										</thead>
										<tbody>
										@foreach($roles as $role)
											<tr>
												<td>{{ $role->role_id }}</td>
												<td>{{ $role->role_name }}</td>
												<td>{{ $role->description }}</td>
												<td>
													@php
														// Find the user count for the current role
														$count = $rolesWithUserCount->firstWhere('role_id', $role->role_id);
													@endphp
													{{ $count ? $count->user_count : 0 }} <!-- Display 0 if no users are assigned -->
												</td>
												<td>
												<a class="btn ripple btn-info" data-target="#editRole{{ $role->role_id }}" data-toggle="modal" href="">تعديل</a>
												<!-- Delete Role Form -->
												<form id="deleteRoleForm" action="{{ route('deleteroles', $role->role_id) }}" method="POST" style="display:inline;">
													@csrf
													@method('DELETE')
													<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteRoleModal" onclick="setDeleteFormAction('{{ route('deleteroles', $role->role_id) }}');">مسح</button>
												</form>

												</td>
														<!-- Basic modal to edit role -->
														<div class="modal" id="editRole{{ $role->role_id }}">
															<div class="modal-dialog" role="document">
																<div class="modal-content modal-content-demo">
																	<div class="modal-header">
																		<h6 class="modal-title">تعديل مجموعة إدارية</h6>
																		<button aria-label="Close" class="close" data-dismiss="modal" type="button">
																			<span aria-hidden="true">&times;</span>
																		</button>
																	</div>
																	<form id="editRoleForm{{ $role->role_id }}" action="{{ route('updateroles', $role->role_id) }}" method="post">
																		@csrf
																		<div class="modal-body">
																			<div class="">
																				<div class="form-group">
																					<label for="role_id">كود المجموعة</label>
																					<input type="text" class="form-control" id="role_id" value="{{ $role->role_id }}" placeholder="كود المجموعة" readonly>
																					<div class="text-danger" id="roleIdError"></div>
																				</div>
																				<div class="form-group">
																					<label for="roleName">اسم المجموعة الادارية</label>
																					<input type="text" class="form-control" name="add_role" id="roleName{{ $role->role_id }}" value="{{ $role->role_name }}" placeholder="اسم المجموعة" required>
																					<div class="text-danger" id="roleNameError{{ $role->role_id }}"></div>
																				</div>
																				<div class="form-group">
																					<label for="roleDesc">وصف المجموعة الادارية</label>
																					<input type="text" class="form-control" name="role_desc" id="roleDesc{{ $role->role_id }}" value="{{ $role->description }}" placeholder="وصف المجموعة" required>
																					<div class="text-danger" id="roleDescError{{ $role->role_id }}"></div>
																				</div>
																			</div>                
																		</div>
																		<div class="modal-footer">
																			<button class="btn ripple btn-primary" type="submit" form="editRoleForm{{ $role->role_id }}">حفظ</button>
																			<button class="btn ripple btn-secondary" data-dismiss="modal" type="button">إغلاق</button>
																		</div>
																	</form>
																</div>
															</div>
														</div>
														<!-- End Basic modal to edit role -->


														<!-- Delete Confirmation Modal -->
														<div class="modal fade" id="deleteRoleModal" tabindex="-1" role="dialog" aria-labelledby="deleteRoleModalLabel" aria-hidden="true">
															<div class="modal-dialog" role="document">
																<div class="modal-content">
																	<div class="modal-header">
																		<h5 class="modal-title" id="deleteRoleModalLabel">تأكيد الحذف</h5>
																		<button type="button" class="close" data-dismiss="modal" aria-label="Close">
																			<span aria-hidden="true">&times;</span>
																		</button>
																	</div>
																	<div class="modal-body">
																		<p>هل أنت متأكد أنك تريد حذف هذه المجموعة؟</p>
																	</div>
																	<div class="modal-footer">
																		<button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
																		<button type="button" class="btn btn-danger" id="confirmDeleteButton">مسح</button>
																	</div>
																</div>
															</div>
														</div>
														<!-- End Delete Confirmation Modal -->



												<td>{{ $role->updated_at }}</td>



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

<!-- Basic modal to add role-->
<div class="modal" id="addRole">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">إضافة مجموعة إدارية جديدة</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addRoleForm" action="{{ route('add_roles') }}" method="post">
                @csrf
                <div class="modal-body">
                    <div class="">
                        <div class="form-group">
                            <label for="roleName">اسم المجموعة الادارية</label>
                            <input type="text" class="form-control" name="add_role" id="roleName" placeholder="اسم المجموعة" required>
                            <div class="text-danger" id="roleNameError"></div> <!-- Error message div -->
                        </div>
                        <div class="form-group">
                            <label for="roleDesc">وصف المجموعة الادارية</label>
                            <input type="text" class="form-control" name="role_desc" id="roleDesc" placeholder="وصف المجموعة" required>
                            <div class="text-danger" id="roleDescError"></div> <!-- Error message div -->
                        </div>
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
<!-- End Basic modal to add role -->



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
    // Validation for the Add Role Form
    function validateAddRoleForm(event) {
        // Prevent the default form submission
        event.preventDefault(); 

        // Clear previous error messages
        document.getElementById('roleNameError').textContent = '';
        document.getElementById('roleDescError').textContent = '';

        const roleName = document.getElementById('roleName').value.trim();
        const roleDesc = document.getElementById('roleDesc').value.trim();

        // Regular expression to check for alphanumeric characters
        const alphanumericRegex = /^[a-zA-Z0-9أ-ي\s]+$/; // Allow spaces

        let isValid = true; // Flag to track overall form validity

        // Validate role name
        if (!alphanumericRegex.test(roleName)) {
            document.getElementById('roleNameError').textContent = 'اسم المجموعة يجب أن يحتوي على حروف وأرقام فقط.';
            isValid = false; // Set validity to false
        }

        // Validate role description
        if (!alphanumericRegex.test(roleDesc)) {
            document.getElementById('roleDescError').textContent = 'وصف المجموعة يجب أن يحتوي على حروف وأرقام فقط.';
            isValid = false; // Set validity to false
        }

        // If valid, submit the form
        if (isValid) {
            document.getElementById('addRoleForm').submit();
        }
    }

    // Validation for the Update Role Form
    function validateUpdateRoleForm(event, roleId) {
        // Prevent the default form submission
        event.preventDefault(); 

        // Clear previous error messages
        document.getElementById('roleNameError' + roleId).textContent = '';
        document.getElementById('roleDescError' + roleId).textContent = '';

        const roleName = document.getElementById('roleName' + roleId).value.trim();
        const roleDesc = document.getElementById('roleDesc' + roleId).value.trim();

        // Regular expression to check for alphanumeric characters
        const alphanumericRegex = /^[a-zA-Z0-9أ-ي\s]+$/; // Allow spaces

        let isValid = true; // Flag to track overall form validity

        // Validate role name
        if (!alphanumericRegex.test(roleName)) {
            document.getElementById('roleNameError' + roleId).textContent = 'اسم المجموعة يجب أن يحتوي على حروف وأرقام فقط.';
            isValid = false; // Set validity to false
        }

        // Validate role description
        if (!alphanumericRegex.test(roleDesc)) {
            document.getElementById('roleDescError' + roleId).textContent = 'وصف المجموعة يجب أن يحتوي على حروف وأرقام فقط.';
            isValid = false; // Set validity to false
        }

        // If valid, submit the form
        if (isValid) {
            document.getElementById('editRoleForm' + roleId).submit();
        }
    }

    // Add event listener for the Add Role Form
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('addRoleForm').addEventListener('submit', function(event) {
            validateAddRoleForm(event);
        });

        const forms = document.querySelectorAll('[id^="editRoleForm"]');
        forms.forEach(form => {
            const roleId = form.id.replace('editRoleForm', ''); // Extract role ID from form ID
            form.addEventListener('submit', function(event) {
                validateUpdateRoleForm(event, roleId);
            });
        });
    });
</script>


<script>
    function setDeleteFormAction(action) {
        const form = document.getElementById('deleteRoleForm');
        form.action = action;
    }

    document.getElementById('confirmDeleteButton').addEventListener('click', function() {
        const form = document.getElementById('deleteRoleForm');
        form.submit(); // Submit the form
    });
</script>




@endsection