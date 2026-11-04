@extends('layouts.master2')

@section('content')
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
											<option value="{{ $role->id }}">{{ $role->name }}</option>
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
