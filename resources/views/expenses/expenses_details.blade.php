@extends('layouts.master')
<title>  معرض أيه -  اضافة مصاريف </title>

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

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif




			    <!-- row -->
			    <div class="row row-sm">
                    <div class="col-md-12 col-xl-12 col-xs-12 col-sm-12">
                        <form id="expenses-form" action="{{ route('storeExpensesDetails') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div id="hidden-div" style="display: none;">
                                <!-- Hidden content will be populated here -->
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="main-content-label mg-b-5 col-4">
                                            <div class="main-content-label mg-b-5">إضافة مصاريف</div>
                                            <p class="mg-b-20">هذة الصفحة مخصصة لإضافة المصاريف و بنود المصاريف</p>
                                        </div>
                                        <div class="col-4"></div>
                                        <div class="col-4" style="margin-top: 10px; text-align: left;">
                                            <a class="btn ripple btn-primary" data-target="#expesesItem" data-toggle="modal" href="">إضافة بند مصروفات جديد</a>
                                        </div>
                                    </div>

                                    <!-- Expenses container -->
                                    <div id="expenses-container">
                                        <div class="row expense-item border p-3 mb-3" style="background: #8080805e;">
                                            <div class="col-lg-4 col-md-6 col-sm-12">
                                                <p class="mg-b-10">بند المصاريف <span class="tx-danger">*</span></p>
                                                <select class="form-control select2" name="expenses_details[0][category]" required>
                                                    <option value="" disabled selected>اختر احد بنود المصاريف</option>
                                                    @foreach ($expenses as $expense)
                                                        <option value="{{ $expense->expenses_item_id }}">{{ $expense->expenses_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-lg-4 col-md-6 col-sm-12">
                                                <label class="form-label">تاريخ المصاريف: <span class="tx-danger">*</span></label>
                                                <input class="form-control" name="expenses_details[0][date]" placeholder="قم بإدخال تاريخ المصروف" required type="date">
                                            </div>
                                            <div class="col-lg-4 col-md-6 col-sm-12">
                                                <label class="form-label">مبلغ المصروفات: <span class="tx-danger">*</span></label>
                                                <input class="form-control" name="expenses_details[0][cost]" placeholder="قم بإدخال مبلغ المصروفات" required type="number">
                                            </div>
                                            <div class="col-lg-4 col-md-6 col-sm-12">
                                                <label class="form-label">ملحوظات:</label>
                                                <input class="form-control" name="expenses_details[0][note]" placeholder="قم بكتابة ملحوظات" type="text">
                                            </div>
                                            <div class="col-lg-4 col-md-6 col-sm-12">
                                                <label class="form-label">تحميل مرفق إن وجد:</label>
                                                <input class="form-control" name="expenses_details[0][file]" type="file" accept=".jpg, .jpeg, .png, .pdf">
                                            </div>
                                            <div class="col-lg-4 col-md-6 col-sm-12 d-flex align-items-end">
                                                <button type="button" class="btn btn-danger remove-expense">حذف</button>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" class="btn btn-success" id="add-item" style="margin-bottom: 15px;">إضافة مصروف جديد</button>
                                    <button type="submit" class="btn btn-primary col-12">تسجيل المصروفات</button>
                                </div>
                            </div>
                        </form>
                    </div>
				</div>
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
<!--Internal  Datepicker js -->
<script src="{{URL::asset('assets/plugins/jquery-ui/ui/widgets/datepicker.js')}}"></script>
<!--Internal  jquery.maskedinput js -->
<script src="{{URL::asset('assets/plugins/jquery.maskedinput/jquery.maskedinput.js')}}"></script>
<!--Internal  spectrum-colorpicker js -->
<script src="{{URL::asset('assets/plugins/spectrum-colorpicker/spectrum.js')}}"></script>
<!-- Internal Select2.min js -->
<script src="{{URL::asset('assets/plugins/select2/js/select2.min.js')}}"></script>
<!--Internal Ion.rangeSlider.min js -->
<script src="{{URL::asset('assets/plugins/ion-rangeslider/js/ion.rangeSlider.min.js')}}"></script>
<!--Internal  jquery-simple-datetimepicker js -->
<script src="{{URL::asset('assets/plugins/amazeui-datetimepicker/js/amazeui.datetimepicker.min.js')}}"></script>
<!-- Ionicons js -->
<script src="{{URL::asset('assets/plugins/jquery-simple-datetimepicker/jquery.simple-dtpicker.js')}}"></script>
<!--Internal  pickerjs js -->
<script src="{{URL::asset('assets/plugins/pickerjs/picker.min.js')}}"></script>
<!-- Internal form-elements js -->
<script src="{{URL::asset('assets/js/form-elements.js')}}"></script>

<script>
   document.getElementById("add-item").addEventListener("click", function () {
    const container = document.getElementById("expenses-container");
    const count = container.children.length;

    // Create a new Expenses container div
    const newContainer = document.createElement("div");
    newContainer.className = "row expense-item border p-3 mb-3";
    newContainer.style.background = "#8080805e";

    // Add new expense item inputs and labels
    newContainer.innerHTML = `
        <div class="col-lg-4 col-md-6 col-sm-12">
            <p class="mg-b-10">بند المصاريف</p>
            <select class="form-control select2" name="expenses_details[${count}][category]" required>
                <option value="" disabled selected>اختر احد بنود المصاريف</option>
                @foreach ($expenses as $expense)
                    <option value="{{ $expense->expenses_item_id }}">{{ $expense->expenses_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-12">
            <label class="form-label">تاريخ المصاريف: <span class="tx-danger">*</span></label>
            <input class="form-control" name="expenses_details[${count}][date]" placeholder="قم بإدخال تاريخ المصروف" required type="date">
        </div>
        <div class="col-lg-4 col-md-6 col-sm-12">
            <label class="form-label">مبلغ المصروفات: <span class="tx-danger">*</span></label>
            <input class="form-control" name="expenses_details[${count}][cost]" placeholder="قم بإدخال مبلغ المصروفات" required type="number">
        </div>
        <div class="col-lg-4 col-md-6 col-sm-12">
            <label class="form-label">ملحوظات:</label>
            <input class="form-control" name="expenses_details[${count}][note]" placeholder="قم بكتابة ملحوظات" type="text">
        </div>
        <div class="col-lg-4 col-md-6 col-sm-12">
            <label class="form-label">تحميل مرفق إن وجد:</label>
            <input class="form-control" name="expenses_details[${count}][file]" type="file" accept=".jpg, .jpeg, .png, .pdf">
        </div>
        <div class="col-lg-4 col-md-6 col-sm-12 d-flex align-items-end">
            <button type="button" class="btn btn-danger remove-expense">حذف</button>
        </div>
    `;

    container.appendChild(newContainer);

    // Initialize Select2 for the new select element
    $(newContainer).find(".select2").select2();
});

// Remove expense item
document.addEventListener("click", function (event) {
    if (event.target.classList.contains("remove-expense")) {
        const expenseItem = event.target.closest(".expense-item");
        const container = document.getElementById("expenses-container");

        // Ensure the first item can't be deleted
        if (expenseItem && container.children[0] !== expenseItem) {
            expenseItem.remove();
        } else {
            alert("Cannot delete the first expense item!");
        }
    }
});

// Collect data into the hidden div and submit it
document.getElementById("expenses-form").addEventListener("submit", function (event) {
    event.preventDefault(); // Prevent the default form submission

    // Clear the hidden div before appending new data
    const hiddenDiv = document.getElementById("hidden-div");
    hiddenDiv.innerHTML = '';

    // Collect all expense items and add them as hidden inputs
    document.querySelectorAll("#expenses-container .expense-item").forEach((expense, index) => {
        const category = expense.querySelector('select[name^="expenses_details"]');
        const date = expense.querySelector('input[name^="expenses_details"][type="date"]');
        const cost = expense.querySelector('input[name^="expenses_details"][type="number"]');
        const note = expense.querySelector('input[name^="expenses_details"][type="text"]');
        const file = expense.querySelector('input[name^="expenses_details"][type="file"]');

        // Create hidden inputs for each expense detail
        hiddenDiv.innerHTML += `
            <input type="hidden" name="expenses_details[${index}][category]" value="${category.value}">
            <input type="hidden" name="expenses_details[${index}][date]" value="${date.value}">
            <input type="hidden" name="expenses_details[${index}][cost]" value="${cost.value}">
            <input type="hidden" name="expenses_details[${index}][note]" value="${note.value}">
            <input type="hidden" name="expenses_details[${index}][file]" value="${file.value}">
        `;
    });

    // Now submit the form
    this.submit();
});

</script>



@endsection