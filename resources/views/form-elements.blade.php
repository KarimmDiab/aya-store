@extends('layouts.master')
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
				<!-- row -->
				<div class="row row-sm">
					

				<div class="col-md-12 col-xl-12 col-xs-12 col-sm-12">
            <form id="expenses-form" method="post" action="/submit-expenses">
                <div class="card">
                    <div class="card-body">
                        <div class="main-content-label mg-b-5">
                            إضافة مصاريف 
                        </div>
                        <p class="mg-b-20">هذة الصفحة مخصصة لإضافة المصاريف و بنود المصاريف </p>
                        
                        <!-- Expenses container -->
                        <div id="expenses-container" > 
                            <div class="row expense-item border p-3 mb-3" style="background: #8080805e;">
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <p class="mg-b-10">بند المصاريف</p>
                                    <select class="form-control select2" name="expense[0][category]">
                                        <option value="">اختر احد بنود المصاريف</option>
                                        @foreach ( $expenses as $expense )
                                        <option value="{{ $expense->expenses_item_id  }}">{{ $expense->expenses_name  }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <label class="form-label">تاريخ المصاريف: <span class="tx-danger">*</span></label>
                                    <input class="form-control" name="expense[0][date]" placeholder="قم بإدخال تاريخ المصروف" required type="date">
                                </div>
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <label class="form-label">مبلغ المصروفات :<span class="tx-danger">*</span></label>
                                    <input class="form-control" name="expense[0][cost]" placeholder="قم بإدخال مبلغ المصروفات" required type="number">
                                </div>
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <label class="form-label">ملحوظات :<span class="tx-danger">*</span></label>
                                    <input class="form-control" name="expense[0][note]" placeholder="قم بكتابة ملحوظات" type="text">
                                </div>
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <label class="form-label">تحميل مرفق إن وجد :<span class="tx-danger">*</span></label>
                                    <input class="form-control" name="expense[0][file]" type="file">
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-success" id="add-item" style="margin-botton : 15px;">إضافة مصروف جديد</button>
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
    let expenseCount = 1; // Counter for unique expense inputs

    $('#add-item').on('click', function () {
        let expenseItem = `
            <div class="row expense-item border p-3 mb-3" style="background: #8080805e;">
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <p class="mg-b-10">بند المصاريف</p>
                    <select class="form-control select2" name="expense[${expenseCount}][category]">
                        <option value="">Choose one</option>
                        <option value="Firefox">Firefox</option>
                        <option value="Chrome">Chrome</option>
                        <option value="Safari">Safari</option>
                        <option value="Opera">Opera</option>
                        <option value="Internet Explorer">Internet Explorer</option>
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