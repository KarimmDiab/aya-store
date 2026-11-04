@extends('layouts.master')


<title>  معرض أيه -  اضافة بنود المصاريف </title>

@section('css')
<!-- Internal Select2 css -->
<link href="{{URL::asset('assets/plugins/select2/css/select2.min.css')}}" rel="stylesheet">
<!--Internal  Datetimepicker-slider css -->
<link href="{{URL::asset('assets/plugins/amazeui-datetimepicker/css/amazeui.datetimepicker.css')}}" rel="stylesheet">
<link href="{{URL::asset('assets/plugins/jquery-simple-datetimepicker/jquery.simple-dtpicker.css')}}" rel="stylesheet">
<link href="{{URL::asset('assets/plugins/pickerjs/picker.min.css')}}" rel="stylesheet">
<!-- Internal Spectrum-colorpicker css -->
<link href="{{URL::asset('assets/plugins/spectrum-colorpicker/spectrum.css')}}" rel="stylesheet">
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

            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0" ">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-0">جدول بنود المصروفات</h4>
                        </div>
                        <p class="tx-12 tx-gray-500 mb-2">جدول عرض  لجميع بنود المصروفات .... في حالة التعديل في بيانات اي بند برجاء الضغط علي <a href="">زر تعديل </a></p>
                        <div class="col-4" >
                            <a class="btn btn-primary" data-toggle="modal" data-target="#expesesItem" style="color : white ; margin-bottom: 15px;">إضافة بند مصروفات جديد</a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-md-nowrap" id="example">
                                <thead>
                                    <tr>
                                        <th class="wd-15p">كود بند المصروفات</th>
                                        <th class="wd-15p">اسم بند المصروفات</th>
                                        <th class="wd-25p">تاريخ التسجيل</th>
                                        <th class="wd-25p">Action</th>
                                        <th class="wd-25p">تاريخ اخر تعديل</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($expenses as $expense)
                                    <tr>
                                        <td>{{ $expense->expenses_item_id }}</td>
                                        <td>{{ $expense->expenses_name }}</td>
                                        <td>{{ $expense->created_at }}</td>
                                        <td>
                                            <!-- Edit Button -->
                                            <button class="btn ripple btn-info" data-toggle="modal" data-target="#editExpensesItem{{ $expense->expenses_item_id }}">تعديل</button>

                                            <!-- Delete Button -->
                                            <button class="btn btn-danger" data-toggle="modal" data-target="#deleteExpensesItem{{ $expense->expenses_item_id }}">مسح</button>
                                        </td>
                                        <td>{{ $expense->updated_at }}</td>
                                    </tr>

                                    <!-- Edit Expenses Modal -->
                                    <div class="modal fade" id="editExpensesItem{{ $expense->expenses_item_id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <form action="{{ route('expensesItemUpdate', $expense->expenses_item_id) }}" method="POST">
                                                @csrf
                                                @method('PUT') <!-- Ensure PUT method is used -->
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">تعديل اسم بند المصروفات</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label>كود البند</label>
                                                            <input type="text" class="form-control" name="id" value="{{ $expense->expenses_item_id }}" readonly>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>اسم بند المصروفات</label>
                                                            <input type="text" class="form-control" name="name" value="{{ $expense->expenses_name }}" required>
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
                                    <div class="modal fade" id="deleteExpensesItem{{ $expense->expenses_item_id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <form action="{{ route('expensesItemDestroy', $expense->expenses_item_id) }}" method="POST">
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
                                                        <p>هل أنت متأكد من أنك تريد حذف هذا البند؟</p>
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
    // Function to validate expenses_name field
    function validateExpensesName() {
        var expensesName = document.getElementById('roleName').value;
        var regex = /^[A-Za-z0-9\s\u0600-\u06FF]+$/; // Allows Arabic, English, numbers, and spaces
        
        if (!regex.test(expensesName)) {
            document.getElementById('expensesNameError').innerText = 'الرجاء إدخال حروف عربية أو إنجليزية أو أرقام أو مسافات فقط';
            return false;
        } else {
            document.getElementById('expensesNameError').innerText = '';
            return true;
        }
    }

    // Attach the validation to the form submit
    document.getElementById('addRoleForm').addEventListener('submit', function(event) {
        if (!validateExpensesName()) {
            event.preventDefault(); // Prevent form submission if validation fails
        }
    });
</script>

<script>
    let expenseCount = 1; // Counter for unique expense inputs

    $('#add-item').on('click', function () {
        let expenseItem = `
            <div class="row expense-item border p-3 mb-3" style="background: #8080805e;">
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <p class="mg-b-10">بند المصاريف</p>
                    <select class="form-control select2" name="expense[${expenseCount}][category]">
                        <option value="" disabled selected>اختر احد بنود المصاريف</option> <!-- Placeholder option -->
                        @foreach ( $expenses as $expense )
                        <option value="{{ $expense->expenses_item_id  }}">{{ $expense->expenses_name  }}</option>
                        @endforeach

                    </select>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <label class="form-label">تاريخ المصاريف: <span class="tx-danger">*</span></label>
                    <input class="form-control" name="expense[${expenseCount}][date]" placeholder="قم بإدخال تاريخ المصروف" required type="date">
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <label class="form-label">مبلغ المصروفات :<span class="tx-danger">*</span></label>
                    <input class="form-control" name="expense[${expenseCount}][cost]" placeholder="قم بإدخال مبلغ المصروفات" required type="number">
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <label class="form-label">ملحوظات :<span class="tx-danger">*</span></label>
                    <input class="form-control" name="expense[${expenseCount}][note]" placeholder="قم بكتابة ملحوظات" type="text">
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <label class="form-label">تحميل مرفق إن وجد :<span class="tx-danger">*</span></label>
                    <input class="form-control" name="expense[${expenseCount}][file]" type="file">
                </div>
            </div>`;
        
        $('#expenses-container').append(expenseItem);

        // Initialize Select2 for the newly added select element
        $('.select2').select2();

        expenseCount++; // Increment counter
    });

    // Initialize Select2 for any existing select elements on page load
    $(document).ready(function() {
        $('.select2').select2();
    });
</script>

@endsection