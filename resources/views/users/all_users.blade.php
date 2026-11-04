@extends('layouts.master')



<title>  معرض أيه - المستخدمين </title>
@section('css')
<!--- Internal Select2 css-->
<link href="{{URL::asset('assets/plugins/select2/css/select2.min.css')}}" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
@endsection
@section('page-header')
				<!-- breadcrumb -->
				<div class="breadcrumb-header justify-content-between">
					<div class="my-auto">
						<div class="d-flex">
							<h4 class="content-title mb-0 my-auto">المستخدمين و الصلاحيات</h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/  المستخدمين</span>
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
										<h4 class="card-title mg-b-0">جدول المستخدمين</h4>
									</div>
									<p class="tx-12 tx-gray-500 mb-2">جدول عرض  لجميع المستخدمين .... في حالة التعديل في بيانات اي مستخدم برجاء الضغط علي <a href="">زر تعديل </a></p>
									<div class="col-4" style="margin-top: 10px;">
									<a class="btn ripple btn-primary" data-target="#addUser" data-toggle="modal" href="" style="margin: 15px; color: white;">إضافة اسم مستخدم جديد  </a>
									</div>
								</div>
								
							

							<div class="card-body">
								<div class="table-responsive">
									<table class="table text-md-nowrap" id="example2">
										<thead>
											<tr>
												<th class="wd-5p border-bottom-0">كود المستخدم</th>
												<th class="wd-15p border-bottom-0">اسم السمتخدم</th>
												<th class="wd-15p border-bottom-0">رقم الموبايل</th>
												<th class="wd-5p border-bottom-0">النوع</th>
												<th class="wd-10p border-bottom-0">المجموعة الإدارية</th>
												<th class="wd-10p border-bottom-0">الحالة</th>
												<th class="wd-25p border-bottom-0"> action</th>
												<th class="wd-25p border-bottom-0">تاريخ اخر تعديل علي بيانات السمتخدم</th>

											</tr>
										</thead>
										<tbody>
											@foreach($users as $user)
											<tr>
												<td>{{ $user->id }}</td>
												<td>{{ $user->name }}</td>
												<td>{{ $user->mobile_number }}</td>
												<td>{{ $user->gender }}</td>
												<td>
													<!-- Match the role_id from rolesWithUsers with the user's role -->
													@foreach($rolesWithUsers as $rolesWithUser)
														@if($rolesWithUser->role_id === $user->role)
															{{ $rolesWithUser->role_name }}
															@break <!-- Exit the loop once the matching role is found -->
														@endif
													@endforeach
												</td>
												<td>
													<span class="badge badge-pill" 
														style="width: 70px; height: 30px; font-size: 13px; font-weight: bold; background-color: {{ $user->status == 'active' ? 'green' : 'red' }};">
														{{ $user->status }}
													</span>
												</td>

												<!-- Edit Button -->
												<td>
													<a href="#" data-target="#edit_user_{{ $user->id }}" data-toggle="modal" class="btn ripple btn-primary edit-btn">تعديل</a>
													
													<!-- Edit User Modal -->
													<div class="modal fade" id="edit_user_{{ $user->id }}">
														<div class="modal-dialog" role="document">
															<div class="modal-content modal-content-demo">
																<div class="modal-header">
																	<h6 class="modal-title">تعديل المستخدم</h6>
																	<button aria-label="Close" class="close" data-dismiss="modal" type="button">
																		<span aria-hidden="true">&times;</span>
																	</button>
																</div>
																<form id="edituserForm" method="post" action="{{ route('updateUser', $user->id) }}">
																	@csrf
																	@method('PUT')
																	<div class="modal-body">
																		<div class="form-group">
																			<label for="user_id">كود المستخدم</label>
																			<input type="text" class="form-control" name="user_id" id="user_id" value="{{ $user->id }}" readonly>
																		</div>
																		<div class="form-group">
																			<label for="user_name">اسم المستخدم</label>
																			<input type="text" class="form-control" name="user_name" id="user_name" value="{{ $user->name }}" placeholder="username" required>
																			<div id="usernameError" class="alert alert-danger d-none"></div> <!-- Error message for username -->
																		</div>
																		<div class="form-group">
																			<label for="email">الايميل</label>
																			<input type="email" class="form-control" name="email" id="email" value="{{ $user->email }}" required>
																			<div id="emailError" class="alert alert-danger d-none"></div> <!-- Error message for email -->
																		</div>
																		
																		<hr>
																		<div class="form-group">
																			<label for="user_phone">رقم الموبايل</label>
																			<input type="tel" class="form-control" name="user_phone" id="user_phone" value="{{ $user->mobile_number }}" required>
																			<div id="phoneError" class="alert alert-danger d-none"></div> <!-- Error message for phone -->
																		</div>
																		<div class="form-group">
																			<label for="birth_date">تاريخ الميلاد</label>
																			<input type="date" class="form-control" name="birth_date" value="{{ $user->birth_date }}" id="birth_date">
																		</div>
																		<div class="form-group">
																			<label for="gender">النوع</label>
																			<select class="form-control" name="gender" id="gender">
																				<option value="male" {{ $user->gender == 'male' ? 'selected' : '' }}>ذكر</option>
																				<option value="female" {{ $user->gender == 'female' ? 'selected' : '' }}>أنثى</option>
																			</select>
																		</div>
																		<div class="form-group">
																			<label for="role">المجموعة الإدارية</label>
																			<select class="form-control" name="role" id="role" required>
																				@foreach($roles as $role)
																					<option value="{{ $role->role_id }}" {{ $user->role == $role->role_id ? 'selected' : '' }}>{{ $role->role_name }}</option>
																				@endforeach
																			</select>
																		</div>
																		<div class="form-group">
																			<label for="status">الحالة</label>
																			<select class="form-control" name="status" id="status" required>
																				<option value="active" {{ $user->status == 'active' ? 'selected' : '' }}>نشط</option>
																				<option value="deactive" {{ $user->status == 'deactive' ? 'selected' : '' }}>غير نشط</option>
																			</select>
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
													<!-- End Edit User Modal -->


													<!-- Delete Button -->
													<a href="#" data-target="#delete_user_{{ $user->id }}" data-toggle="modal" class="btn ripple btn-danger">مسح</a>

													<!-- Delete Confirmation Modal -->
													<div class="modal fade" id="delete_user_{{ $user->id }}">
														<div class="modal-dialog" role="document">
															<div class="modal-content">
																<div class="modal-header">
																	<h6 class="modal-title">حذف المستخدم</h6>
																	<button aria-label="Close" class="close" data-dismiss="modal" type="button">
																		<span aria-hidden="true">&times;</span>
																	</button>
																</div>
																<div class="modal-body">
																	<p>هل أنت متأكد أنك تريد حذف هذا المستخدم؟</p>
																</div>
																<div class="modal-footer">
																	<form action="{{ route('deleteUser', $user->id) }}" method="POST">
																		@csrf
																		@method('DELETE')
																		<button type="submit" class="btn btn-danger">نعم</button>
																	</form>
																	<button class="btn ripple btn-secondary" data-dismiss="modal" type="button">إغلاق</button>
																</div>
															</div>
														</div>
													</div>
													<!-- End Delete Confirmation Modal -->
												</td>
												<td>{{ $user->updated_at }}</td>
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






		
		<!-- Basic modal to add user-->
		<div class="modal" id="addUser">
			<div class="modal-dialog" role="document">
				<div class="modal-content modal-content-demo">
					<div class="modal-header">
						<h6 class="modal-title">إضافة مستخدم جديد</h6>
						<button aria-label="Close" class="close" data-dismiss="modal" type="button">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<form id="userForm" method="post" action="{{ route('add_users') }}">
						@csrf
						<div class="modal-body">
							<div class="">
								<div class="form-group">
									<label for="user_id">كود المستخدم</label>
									<input type="text" class="form-control" name="user_id" id="user_id" value="{{ $nextUserId }}" readonly>
								</div>
								<div class="form-group">
									<label for="user_name">اسم المستخدم</label>
									<input type="text" class="form-control" name="user_name" id="user_name" placeholder="username" required>
									<div class="alert alert-danger d-none" id="usernameError"></div>
								</div>
								<div class="form-group">
									<label for="email">الايميل</label>
									<input type="email" class="form-control" name="email" id="email" placeholder="email" required>
									<div class="alert alert-danger d-none" id="emailError"></div>
								</div>
								<div class="form-group">
									<label for="password">كلمة المرور</label>
									<input type="password" class="form-control" name="password" id="password" placeholder="password" required>
									<div class="alert alert-danger d-none" id="passwordError"></div>
								</div>
								<div class="form-group">
									<label for="password_confirmation">تأكيد كلمة المرور</label>
									<input type="password" class="form-control" name="password_confirmation" id="password_confirmation" placeholder="تأكيد كلمة المرور" required>
									<div class="alert alert-danger d-none" id="passwordConfirmationError"></div>
								</div>
								<hr>
								<div class="form-group">
									<label for="user_phone">رقم الموبايل</label>
									<input type="tel" class="form-control" name="user_phone" id="user_phone" placeholder="phone" required>
									<div class="alert alert-danger d-none" id="phoneError"></div>
								</div>
								<div class="form-group">
									<label for="birth_date">تاريخ الميلاد</label>
									<input type="date" class="form-control" name="birth_date" id="birth_date">
								</div>
								<div class="" style="margin-bottom: 22px;">
									<p class="mg-b-10">النوع</p>
									<select class="form-control select2" name="gender" style="width:100%;">
										<option label="Choose one"></option>
										<option value="male">ذكر</option>
										<option value="female">أنثي</option>
									</select>
								</div>
								<div class="" style="margin-bottom: 22px;">
									<p class="mg-b-10">المجموعة الإدارية</p>
									<select class="form-control select2" name="role" style="width:100%;" required>
										<option label="Choose one"></option>
										@foreach($roles as $role)
											<option value="{{ $role->role_id }}">{{ $role->role_name }}</option>
										@endforeach
									</select>
								</div>
								<div class="" style="margin-bottom: 22px;">
									<p class="mg-b-10">الحالة</p>
									<select class="form-control select2" name="status" style="width:100%;" required>
										<option label="Choose one"></option>
										<option value="active">نشط</option>
										<option value="deactive">غير نشط</option>
									</select>
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
		<!-- End Basic modal to add user -->





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
// Front-End Validation for both Add and Edit User Forms
document.addEventListener('DOMContentLoaded', function() {
    const addUserForm = document.getElementById('userForm');
    const editUserForm = document.getElementById('edituserForm');

	const usernamePattern = /^[\u0600-\u06FFa-zA-Z0-9\s]*$/; // يسمح بالأحرف العربية، الأحرف الإنجليزية، الأرقام، والمسافات فقط.
    const emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/; // Basic email regex
    const phonePattern = /^(01)[0-9]{9}$/; // Egyptian phone number pattern

    function validateForm(form) {
        let valid = true;
        
        const username = form.querySelector('[name="user_name"]');
        const email = form.querySelector('[name="email"]');
        const password = form.querySelector('[name="password"]');
        const passwordConfirmation = form.querySelector('[name="password_confirmation"]');
        const phone = form.querySelector('[name="user_phone"]');

        // Clear previous errors
        form.querySelectorAll('.alert').forEach(alert => alert.classList.add('d-none'));

        // Username validation
        if (username.value.trim() === '') {
            form.querySelector('#usernameError').textContent = 'اسم المستخدم مطلوب.';
            form.querySelector('#usernameError').classList.remove('d-none');
            valid = false;
        } else if (!usernamePattern.test(username.value.trim())) {
            form.querySelector('#usernameError').textContent = 'لا يسمح بكتابة رموز ';
            form.querySelector('#usernameError').classList.remove('d-none');
            valid = false;
        }

        // Email validation
        if (!emailPattern.test(email.value)) {
            form.querySelector('#emailError').textContent = 'يرجى إدخال بريد إلكتروني صالح.';
            form.querySelector('#emailError').classList.remove('d-none');
            valid = false;
        }

		
		}
        // Phone validation
        if (!phonePattern.test(phone.value)) {
            form.querySelector('#phoneError').textContent = '   رقم الموبايل يجب أن يكون مكون من 11 ارقام و يبدا 01.';
            form.querySelector('#phoneError').classList.remove('d-none');
            valid = false;
        }

        return valid;
    }

    // Attach validation to the Add User form
    if (addUserForm) {
        addUserForm.addEventListener('submit', function(event) {
            if (!validateForm(addUserForm)) {
                event.preventDefault(); // Prevent form submission if validation fails
            }
        });
    }

    // Attach validation to the Edit User form
    if (editUserForm) {
        editUserForm.addEventListener('submit', function(event) {
            if (!validateForm(editUserForm)) {
                event.preventDefault(); // Prevent form submission if validation fails
            }
        });
    }
});

</script>




@endsection