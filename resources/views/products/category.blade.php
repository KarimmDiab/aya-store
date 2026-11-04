@extends('layouts.master')



<title> معرض أيه - اصناف المنتجات </title>
@section('css')
    <!--- Internal Select2 css-->
    <link href="{{URL::asset('assets/plugins/select2/css/select2.min.css')}}" rel="stylesheet">
@endsection
@section('page-header')
    <!-- breadcrumb -->
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto"> المنتجات</h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/ اصناف
                    المنتجات</span>
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
                    <h4 class="card-title mg-b-0">جدول الاصناف</h4>
                </div>
                <p class="tx-12 tx-gray-500 mb-2">جدول عرض لجميع الاصناف .... في حالة التعديل في بيانات اي صنف برجاء الضغط
                    علي <a href="">زر تعديل </a></p>
                <div class="col-4" style="margin-top: 10px;">
                    <a class="btn ripple btn-primary" data-toggle="modal" data-target="#addCategory"
                        style="margin: 15px ; color: white ;">إضافة تصنيف جديد</a>
                </div>
            </div>



            <div class="card-body">
                <div class="table-responsive">
                    <table class="table text-md-nowrap" id="example2">
                        <thead>
                            <tr>
                                <th class="wd-15p">كود التصنيف</th>
                                <th class="wd-15p">اسم التصنيف</th>
                                <th class="wd-15p">وصف التصنيف</th>
                                <th class="wd-25p">تاريخ تسجيل التصنيف</th>
                                <th class="wd-25p">Action</th>
                                <th class="wd-25p">تاريخ اخر تعديل علي التصنيف</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                                <tr>
                                    <td>{{ $category->category_id }}</td>
                                    <td>{{ $category->category_name }}</td>
                                    <td>{{ $category->category_desc }}</td>
                                    <td>{{ $category->created_at }}</td>
                                    <td>
                                        <!-- Edit Button -->
                                        <button class="btn ripple btn-info" data-toggle="modal"
                                            data-target="#editCategoryModal{{ $category->category_id }}">تعديل</button>

                                        <!-- Delete Button -->
                                        <button class="btn btn-danger" data-toggle="modal"
                                            data-target="#deleteCategoryModal{{ $category->category_id }}">مسح</button>
                                    </td>
                                    <td>{{ $category->updated_at }}</td>
                                </tr>

                                <!-- Edit Supplier Modal -->
                                <div class="modal fade" id="editCategoryModal{{ $category->category_id }}" tabindex="-1"
                                    role="dialog" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <!-- Edit Supplier Form -->
                                        <form action="{{ route('category.update', $category->category_id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">تعديل بيانات التصنيف</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label>كود التصنيف</label>
                                                        <input type="text" class="form-control" name="category_id"
                                                            value="{{ $category->category_id }}" readonly>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>اسم التصنيف</label>
                                                        <input type="text" class="form-control" name="category_name"
                                                            value="{{ $category->category_name }}" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>وصف التصنيف</label>
                                                        <input type="text" class="form-control" name="category_desc"
                                                            value="{{ $category->category_desc }}" required>
                                                    </div>

                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-primary">حفظ</button>
                                                    <button type="button" class="btn btn-secondary"
                                                        data-dismiss="modal">إغلاق</button>
                                                </div>
                                            </div>
                                        </form>

                                    </div>
                                </div>

                                <!-- Delete Supplier Modal -->
                                <div class="modal fade" id="deleteCategoryModal{{ $category->category_id }}" tabindex="-1"
                                    role="dialog" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <form action="{{ route('category.destroy', $category->category_id) }}" method="POST">
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
                                                    <p>هل أنت متأكد من أنك تريد حذف هذا التصنيف ؟</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-danger">حذف</button>
                                                    <button type="button" class="btn btn-secondary"
                                                        data-dismiss="modal">إغلاق</button>
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


    <!-- Add category Modal -->
    <div class="modal fade" id="addCategory" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <!-- Add Supplier Form -->
            <form action="{{ route('category.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">إضافة تصنيف جديد</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="category_name">اسم التصنيف</label>
                            <input type="text" class="form-control" name="category_name" id="category_name"
                                placeholder="أدخل اسم التصنيف" required>
                        </div>
                        <div class="form-group">
                            <label for="category_desc">شرح التصنيف</label>
                            <input type="text" class="form-control" name="category_desc" id="category_desc"
                                placeholder="أدخل وصف التصنيف" required>
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
            form.addEventListener('submit', function (event) {
                // Clear previous error messages
                clearErrors();

                // Flag to check if form is valid
                let isValid = true;

                // Get input values for both forms
                const category_name = form.querySelector('[name="category_name"]').value;
                const category_desc = form.querySelector('[name="category_desc"]').value;

                // Validate name (Arabic letters, English letters, numbers, and spaces only)
                const category_nameRegex = /^[\u0600-\u06FFa-zA-Z0-9\s]+$/;
                if (!category_nameRegex.test(category_name)) {
                    isValid = false;
                    showError(form, 'category_name', 'اسم التصنيف يجب أن يحتوي على الحروف العربية أو الإنجليزية أو الأرقام أو المسافات فقط');
                }

                // Validate description (Arabic letters, English letters, numbers, and spaces only)
                const category_descRegex = /^[\u0600-\u06FFa-zA-Z0-9\s]+$/;
                if (!category_descRegex.test(category_desc)) {
                    isValid = false;
                    showError(form, 'category_desc', 'وصف التصنيف يجب أن يحتوي على الحروف العربية أو الإنجليزية أو الأرقام أو المسافات فقط');
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
            errorMessages.forEach(function (error) {
                error.remove();
            });

            const invalidFields = document.querySelectorAll('.is-invalid');
            invalidFields.forEach(function (field) {
                field.classList.remove('is-invalid');
            });
        }
    </script>

@endsection