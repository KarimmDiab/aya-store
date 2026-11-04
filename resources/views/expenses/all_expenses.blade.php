@extends('layouts.master')
<title>  معرض أيه -  جميع المصاريف </title>

@section('css')
<!-- Internal Select2 css -->
<link href="{{URL::asset('assets/plugins/select2/css/select2.min.css')}}" rel="stylesheet">
<!--Internal  Datetimepicker-slider css -->
<link href="{{URL::asset('assets/plugins/amazeui-datetimepicker/css/amazeui.datetimepicker.css')}}" rel="stylesheet">
<link href="{{URL::asset('assets/plugins/jquery-simple-datetimepicker/jquery.simple-dtpicker.css')}}" rel="stylesheet">
<link href="{{URL::asset('assets/plugins/pickerjs/picker.min.css')}}" rel="stylesheet">
<!-- Internal Spectrum-colorpicker css -->
<link href="{{URL::asset('assets/plugins/spectrum-colorpicker/spectrum.css')}}" rel="stylesheet">
<style>
.is-invalid {
    border-color: red;
}

.is-invalid + .text-danger {
    color: red;
}
.modal-dialog-custom {
    max-width: 70% !important; /* Make modal 70% of the screen width */
}


</style>
@endsection
@section('page-header')
				<!-- breadcrumb -->
				<div class="breadcrumb-header justify-content-between">
					<div class="my-auto">
						<div class="d-flex">
							<h4 class="content-title mb-0 my-auto">المصاريف</h4>
						</div>
					</div>
					
				</div>
				<!-- breadcrumb -->
@endsection
@section('content')

<!-- Show success message if exists -->
@if (session('success'))
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



            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-0">جدول جميع المصاريف </h4>
                        </div>
                        <p class="tx-12 tx-gray-500 mb-2">جدول عرض  لجميع المصروفات .... في حالة التعديل في بيانات اي مصروف برجاء الضغط علي <a href="">زر تعديل </a></p>
       

                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="table key-buttons text-md-nowrap">
                                <thead>
                                    <tr>
                                        <th class="wd-5p">م</th>
                                        <th class="wd-15p">اسم بند المصروفات</th>
                                        <th class="wd-10p">تاريخ التسجيل</th>
                                        <th class="wd-8p">التكلفة</th>
                                        <th class="wd-25p">الملحوظات</th>
                                        <th class="wd-15p">تاريخ الإدخال</th>
                                        <th class="wd-15p">Action</th>
                                        <th class="wd-25p">تاريخ اخر تعديل</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($expensesDetails as $expense)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td> <!-- الرقم التسلسلي -->
                                        <td>{{ $expense->expenses_name }}</td>
                                        <td>{{ $expense->expenses_date }}</td>
                                        <td>{{ $expense->expenses_cost }}</td>
                                        <td>{{ $expense->expenses_note }}</td>
                                        <td>{{ $expense->created_at }}</td>
                                        <td>
                                            <!-- Edit Button -->
                                            <button class="btn ripple btn-info" data-toggle="modal" data-target="#editExpensesItem{{ $expense->expenses_details_id }}">تعديل</button>

                                            <!-- Delete Button -->
                                            <button class="btn btn-danger" data-toggle="modal" data-target="#deleteExpensesItem{{ $expense->expenses_details_id }}">مسح</button>
                                            <!-- View Image Button -->
                                            @if($expense->expenses_file) <!-- Assuming the image URL is stored in the database -->
                                                <button class="btn btn-warning" data-toggle="modal" data-target="#viewImageModal{{ $expense->expenses_details_id }}">عرض الصورة</button>
                                            @endif

                                        </td>
                                        <td>{{ $expense->updated_at }}</td>
                                    </tr>

                                    <!-- Edit Expenses Modal -->
                                    <div class="modal fade" id="editExpensesItem{{ $expense->expenses_details_id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <form action="{{ route('expenses.update', $expense->expenses_details_id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">اسم بند المصروفات</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <!-- Expense Name Select -->
                                                        <div class="form-group">
                                                            <label for="expense_id">اسم البند</label>
                                                            <select class="form-control" name="id" id="expense_id" required>
                                                                @foreach ($expenses as $expenseOption)
                                                                    <option value="{{ $expenseOption->expenses_item_id }}" 
                                                                        {{ $expense->expenses_item_id == $expenseOption->expenses_item_id ? 'selected' : '' }}>
                                                                        {{ $expenseOption->expenses_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('id')
                                                                <small class="text-danger">{{ $message }}</small>
                                                            @enderror
                                                        </div>

                                                        <!-- Expense Date -->
                                                        <div class="form-group">
                                                            <label>تاريخ</label>
                                                            <input type="date" class="form-control" name="date" value="{{ $expense->expenses_date }}" required>
                                                            @error('date')
                                                                <small class="text-danger">{{ $message }}</small>
                                                            @enderror
                                                        </div>

                                                        <!-- Expense Cost -->
                                                        <div class="form-group">
                                                            <label>التكلفة</label>
                                                            <input type="number" class="form-control" name="cost" value="{{ $expense->expenses_cost }}" required>
                                                            <small class="text-danger" id="cost-error"></small>
                                                        </div>

                                                        <!-- Expense Note -->
                                                        <div class="form-group">
                                                            <label>ملاحظات</label>
                                                            <input type="text" class="form-control" name="note" value="{{ $expense->expenses_note }}">
                                                            <small class="text-danger" id="note-error"></small>
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

                                    <!-- Delete Expenses Modal -->
                                    <div class="modal fade" id="deleteExpensesItem{{ $expense->expenses_details_id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <form action="{{ route('expenses.destroy', $expense->expenses_details_id) }}" method="POST">
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
                                                        <p>هل أنت متأكد من أنك تريد حذف هذا المصروف ؟</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-danger">حذف</button>
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- View Image Modal -->
                                    @if($expense->expenses_file) <!-- Only show if there is an image URL -->
                                    <div class="modal fade" id="viewImageModal{{ $expense->expenses_details_id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-custom" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">عرض الصورة</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <img src="{{ asset('images/expenses_pic/' . $expense->expenses_file) }}" class="img-fluid" alt="Expense Image">
                                                </div>
                                                <div class="modal-footer">
                                                    <!-- Download Button -->
                                                    <a href="{{ asset('images/expenses_pic/' . $expense->expenses_file) }}" class="btn btn-success" download>
                                                        تحميل الصورة
                                                    </a>
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif



                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div><!-- col-xl-12 closed --> 
		<!-- row closed -->
    </div>
	<!-- Container closed -->
</div>
<!-- main-content closed -->










        <!-- Basic modal to add expenses item -->
<div class="modal" id="expesesItem">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">إضافة بند مصروفات جديد</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addRoleForm" action="{{ route('storeExpensesItem') }}" method="post">
                @csrf
                <div class="modal-body">
                    <div class="">
                        <div class="form-group">
                            <label for="roleName">اسم بند المصروفات</label>
                            <input type="text" class="form-control" name="expenses_name" id="roleName" placeholder="اسم بند المصروفات" required>
                            <div class="text-danger" id="expensesNameError"></div> <!-- Error message div -->
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
<!-- End Basic modal to add expenses item -->
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
    document.addEventListener('DOMContentLoaded', function() {
        // Real-time validation for the 'cost' field
        const costInput = document.querySelector('input[name="cost"]');
        const costError = document.getElementById('cost-error');

        costInput.addEventListener('input', function() {
            const value = costInput.value;
            if (!value || isNaN(value) || parseFloat(value) < 0) {
                costError.textContent = 'التكلفة يجب أن تكون رقمًا موجبًا.';
                costInput.classList.add('is-invalid');
            } else {
                costError.textContent = '';
                costInput.classList.remove('is-invalid');
            }
        });

        // Real-time validation for the 'note' field
        const noteInput = document.querySelector('input[name="note"]');
        const noteError = document.getElementById('note-error');

        noteInput.addEventListener('input', function() {
            const value = noteInput.value;
            const regex = /^[A-Za-z0-9\s\u0600-\u06FF]+$/;

            if (!value || !regex.test(value)) {
                noteError.textContent = 'ملاحظات يجب أن تحتوي على حروف عربية، إنجليزية، أرقام ومسافات فقط.';
                noteInput.classList.add('is-invalid');
            } else {
                noteError.textContent = '';
                noteInput.classList.remove('is-invalid');
            }
        });
    });
</script>


@endsection