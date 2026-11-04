@extends('layouts.master')


<title>  معرض أيه -  عملاء الأجل </title>

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
							<h4 class="content-title mb-0 my-auto">جميع عملاء الأجل</h4>
						</div>
					</div>
					
				</div>
				<!-- breadcrumb -->
@endsection
@section('content')

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif


            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0" >
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-0">جدول عملاء الأجل</h4>
                        </div>
                        <p class="tx-12 tx-gray-500 mb-2">جدول عرض  لجميع عملاء الأجل .... في حالة التعديل في بيانات اي عميل برجاء الضغط علي <a href="">زر تعديل </a></p>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-md-nowrap" id="example">
                                <thead>
                                    <tr>
                                        <th class="wd-10p">كود العميل</th>
                                        <th class="wd-15p">اسم العميل</th>
                                        <th class="wd-15p">رقم العميل</th>
                                        <th class="wd-10p">عدد الفواتير</th>
                                        <th class="wd-10p">اجمالي المبلغ الفواتير</th>
                                        <th class="wd-10p   ">طريقة السداد</th>
                                        <th class="wd-25p">Action</th>
                                        <th class="wd-15p">تاريخ اخر تعديل علي بيانات العميل</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($results as $customer)
                                    <tr>
                                        <td>{{ $customer->id }}</td>
                                        <td>{{ $customer->name }}</td>
                                        <td>{{ $customer->contact_info }}</td>
                                        <td>{{ $customer->count_invoices }}</td>
                                        <td>{{ $customer->total_invoices }}</td>
                                        <td>{{ $customer->payment_method }}</td>




                                        <td>
                                            <a href="{{ route('customer_profile', ['id' => encrypt($customer->id)]) }}" class="btn ripple btn-info">
                                                تفاصيل
                                            </a>


                                            <!-- Edit Button -->
                                            <button class="btn ripple btn-primary" data-toggle="modal" data-target="#editCustomers{{ $customer->id }}">تعديل</button>

                                            <!-- Delete Button -->
                                            <button class="btn btn-danger" data-toggle="modal" data-target="#deleteCustomers{{ $customer->id }}">مسح</button>
                                        </td>
                                        <td>{{ $customer->updated_at }}</td>

                                    </tr>

                                    <!-- Edit customers Modal -->
                                    <div class="modal fade" id="editCustomers{{ $customer->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <form action="{{ route('customers.update', $customer->id) }}" method="POST">
                                                @csrf
                                                @method('PUT') <!-- Ensure PUT method is used -->
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">تعديل بيانات الاساسية للعميل</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label>كود العميل</label>
                                                            <input type="text" class="form-control" name="id" value="{{ $customer->id }}" readonly>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>اسم العميل</label>
                                                            <input type="text" class="form-control" name="name" value="{{ $customer->name }}" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>رقم العميل</label>
                                                            <input type="tel" class="form-control" name="phone" value="{{ $customer->contact_info }}" required>
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

                                    <!-- Delete customers Modal -->
                                    <div class="modal fade" id="deleteCustomers{{ $customer->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <form action="{{ route('customers.destroy', $customer->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">تأكيد حذف العميل</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>هل أنت متأكد من أنك تريد حذف هذا العميل ؟</p>
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
    document.addEventListener("DOMContentLoaded", function () {
        const form = document.querySelector("form");
        const nameInput = form.querySelector('input[name="name"]');
        const phoneInput = form.querySelector('input[name="phone"]');

        form.addEventListener("submit", function (e) {
            let isValid = true;

            // Name validation: Arabic, English, numbers, hyphens, spaces
            const nameRegex = /^[\u0600-\u06FFa-zA-Z0-9\s\-]+$/;
            if (!nameRegex.test(nameInput.value.trim())) {
                isValid = false;
                alert("اسم العميل يجب أن يحتوي على حروف عربية، حروف إنجليزية، أرقام، مسافات، أو علامات (-).");
            }

            // Phone validation: Starts with 01 and has 11 digits
            const phoneRegex = /^01[0-9]{9}$/;
            if (!phoneRegex.test(phoneInput.value.trim())) {
                isValid = false;
                alert("رقم الهاتف يجب أن يبدأ بـ 01 ويحتوي على 11 رقم.");
            }

            if (!isValid) {
                e.preventDefault(); // Prevent form submission if invalid
            }
        });
    });
</script>




@endsection