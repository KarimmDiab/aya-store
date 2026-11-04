@extends('layouts.master')



<title>  معرض أيه -  المنتجات </title>
@section('css')
<!--- Internal Select2 css-->
<link href="{{URL::asset('assets/plugins/select2/css/select2.min.css')}}" rel="stylesheet">
@endsection
@section('page-header')
				<!-- breadcrumb -->
				<div class="breadcrumb-header justify-content-between">
					<div class="my-auto">
						<div class="d-flex">
							<h4 class="content-title mb-0 my-auto"> المنتجات</h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/  المنتجات</span>
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
									<h4 class="card-title mg-b-0">جدول المنتجات</h4>
								</div>
								<p class="tx-12 tx-gray-500 mb-2">جدول عرض  لجميع المنتجات .... في حالة التعديل في بيانات اي منتج برجاء الضغط علي <a href="">زر تعديل </a></p>
								<div class="col-4" style="margin-top: 10px;">
								    <a class="btn ripple btn-primary" data-toggle="modal" data-target="#addProducts" style="margin: 15px ; color: white ;">إضافة منتج جديد</a>
                                </div>
							</div>

							<div class="card-body">
                            <div class="mb-3 text-right">
                                 <button id="exportExcel" class="btn btn-success">تصدير إلى Excel</button>
                            </div>

								<div class="table-responsive">
								<table class="table text-md-nowrap" id="example2">
									<thead>
										<tr style="text-align: center;">
											<th class="wd-5p">كود المنتج</th>
                                            <th class="wd-5p">صورة المنتج</th>
											<th class="wd-15p">اسم المنتج</th>
											<th class="wd-10p"> التصنيف</th>
                                            <th class="wd-10p"> الكمية</th>
											<th class="wd-10p"> سعر القطعة</th>
											<th class="wd-25p">تاريخ تسجيل التصنيف</th>
											<th class="wd-25p">Action</th>
											<th class="wd-25p">تاريخ اخر تعديل علي المنتج</th>
										</tr>
									</thead>
									<tbody>
										@foreach($products as $product)
										<tr style="text-align: center;">
											<td>{{ $product->id }}</td>
                                            <td>
                                                <img src="{{ asset('images/products/' . ($product->image ?: 'item.png')) }}" alt="Product Image" style="max-width: 130%;   border-radius: 20px;">
                                            </td>
											<td >{{ $product->name }}</td>
                                            <td>
                                            @foreach ($categories as $category)
                                                {{-- Check if the product category matches the category_id --}}
                                                @if ($product->category == $category->category_id)
                                                    {{ $category->category_name }}
                                                @endif
                                            @endforeach
                                        </td>
											<td>{{ $product->quantity }}</td>
                                            <td>{{ $product->price }}</td>
											<td>{{ $product->created_at }}</td>
											<td>
												<!-- Edit Button -->
												<button class="btn ripple btn-info" data-toggle="modal" data-target="#editProductModal{{ $product->id }}">تعديل</button>
												<!-- Delete Button -->
												<button class="btn btn-danger" data-toggle="modal" data-target="#deleteProductModal{{ $product->id }}">مسح</button>
                                                <!-- Print Barcode Button -->
                                                <button class="btn btn-secondary print-barcode-btn" data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-quantity="{{ $product->quantity }}" data-price="{{ $product->price }}">طباعة باركود</button>
											</td>
											<td>{{ $product->updated_at }}</td>
										</tr>


										<!-- print barcode Modal -->
                                        <div class="modal fade" id="printBarcodeModal" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">طباعة الباركود</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body text-center">
                                                        <button id="printBarcode" class="btn btn-primary mt-3">طباعة</button>
                                                        <div id="barcodePreview"></div>
                                                        <div class="form-group mt-3" style="display : none;">
                                                            <label for="barcodeQuantity">عدد البطاقات:</label>
                                                            <input type="number" id="barcodeQuantity" class="form-control text-center" min="1" value="1" style="max-width: 150px; margin: 0 auto;">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

										<!-- Edit product Modal -->
										<div class="modal fade" id="editProductModal{{ $product->id }}" tabindex="-1" role="dialog" aria-hidden="true">
											<div class="modal-dialog" role="document">
                                                <!-- Edit Supplier Form -->
                                                <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">تعديل بيانات المنتج</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label>كود المنتج</label>
                                                                <input type="text" class="form-control" name="product_id" value="{{ $product->id }}" readonly>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>اسم المنتج</label>
                                                                <input type="text" class="form-control" name="product_name" value="{{ $product->name }}" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>التصنيف</label>
                                                                <select class="form-control" name="product_category" required>
                                                                    <option value="">اختر التصنيف</option>
                                                                    @foreach ($categories as $category)
                                                                        <option value="{{ $category->category_id }}" 
                                                                            {{ isset($product) && $product->category == $category->category_id ? 'selected' : '' }}>
                                                                            {{ $category->category_name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>الكمية</label>
                                                                <input type="text" class="form-control" name="product_quantity" value="{{ $product->quantity }}" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>سعر القطعة</label>
                                                                <input type="text" class="form-control" name="product_price" value="{{ $product->price }}" required>
                                                            </div>

                                                            <!-- Image Display and Update -->
                                                            <div class="form-group">
                                                                <label>صورة المنتج</label>
                                                                <div>
                                                                    <img src="{{ asset('images/products/' . ($product->image ?: 'item.png')) }}" alt="Product Image" style="width: 100px; height: 100px; object-fit: cover;">
                                                                </div>
                                                                <input type="file" class="form-control mt-2"  value="{{ $product->image }}"  name="product_image">
                                                                <small class="form-text text-muted">إذا كنت ترغب في تغيير الصورة، اختر صورة جديدة.</small>
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

										<!-- Delete product Modal -->
										<div class="modal fade" id="deleteProductModal{{ $product->id }}" tabindex="-1" role="dialog" aria-hidden="true">
											<div class="modal-dialog" role="document">
												<form action="{{ route('products.destroy', $product->id) }}" method="POST">
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
															<p>هل أنت متأكد من أنك تريد حذف هذا المنتج ؟</p>
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

						</div><!-- bd -->
					</div><!-- bd -->
				</div>
				<!--/div-->
			<!-- row closed -->
		</div>
		<!-- Container closed -->
	</div>
	<!-- main-content closed -->




<!-- Add product Modal -->
<div class="modal fade" id="addProducts" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <!-- Add product Form -->
        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إضافة منتج جديد</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="supplierName">كود المنتج</label>
                        <input type="text" class="form-control" name="product_id" id="product_id" placeholder="أدخل كود المنتج" required>
                    </div>
                    <div class="form-group">
                        <label for="supplierName">اسم المنتج</label>
                        <input type="text" class="form-control" name="product_name" id="product_name" placeholder="أدخل اسم المنتج" required>
                    </div>
                    <!-- Product Category -->
                    <div class="form-group">
                        <label>التصنيف</label>
                        <select class="form-control" name="product_category" required>
                            <option value="">اختر التصنيف</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->category_id }}">
                                    {{ $category->category_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="supplierAddress">سعر القطعة</label>
                        <input type="text" class="form-control" name="product_price" id="product_price" placeholder="أدخل  سعر القطعة" required>
                    </div>
                    <div class="form-group">
                        <label for="product_image">صورة المنتج</label>
                        <input type="file" class="form-control" name="product_image" id="product_image" accept="image/*">
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

<!--Internal  Datatable js -->
<script src="{{URL::asset('assets/js/table-data.js')}}"></script>

<!--Internal  Datepicker js -->
<script src="{{URL::asset('assets/plugins/jquery-ui/ui/widgets/datepicker.js')}}"></script>

<!-- Internal Modal js-->
<script src="{{URL::asset('assets/js/modal.js')}}"></script>
<script src="{{URL::asset('assets/js/JsBarcode.all.min.js')}}"></script>
<script src="{{URL::asset('assets/js/xlsx.full.min.js')}}"></script>



<script>
    // Add event listener for form submission
    document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function (event) {
        // Clear previous error messages
        clearErrors();

        // Flag to check if form is valid
        let isValid = true;

        // Get input values for both forms
        const product_id = form.querySelector('[name="product_id"]').value;
        const product_name = form.querySelector('[name="product_name"]').value;
        const product_quantity = form.querySelector('[name="product_quantity"]').value;
        const product_price = form.querySelector('[name="product_price"]').value;

        // Validate Product Name (Arabic letters, English letters, numbers, and spaces only)
    const productNameRegex = /^[\u0600-\u06FFa-zA-Z0-9\s]+$/;
    if (!productNameRegex.test(product_name)) {
        isValid = false;
        showError(form, 'product_name', 'اسم المنتج يجب أن يحتوي على الحروف العربية أو الإنجليزية أو الأرقام أو المسافات فقط');
    }

    // Validate Product ID (numbers only)
    const productIdRegex = /^[0-9]+$/;
    if (!productIdRegex.test(product_id)) {
        isValid = false;
        showError(form, 'product_id', 'كود المنتج يجب أن يحتوي على أرقام فقط');
    }



    // Validate Product Price (integer or decimal numbers)
    const productPriceRegex = /^\d+(\.\d{1,2})?$/;
    if (!productPriceRegex.test(product_price)) {
        isValid = false;
        showError(form, 'product_price', 'السعر يجب أن يكون عددًا صحيحًا أو عشريًا');
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
            errorMessages.forEach(function(error) {
                error.remove();
            });

            const invalidFields = document.querySelectorAll('.is-invalid');
            invalidFields.forEach(function(field) {
                field.classList.remove('is-invalid');
            });
        }
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const printBarcodeBtns = document.querySelectorAll('.print-barcode-btn');
    const barcodeModal = document.getElementById('printBarcodeModal');
    const barcodePreview = document.getElementById('barcodePreview');
    const printBarcodeButton = document.getElementById('printBarcode');
    const barcodeQuantityInput = 15; // Fixed quantity for labels

    printBarcodeBtns.forEach(button => {
        button.addEventListener('click', () => {
            const productId = button.dataset.id;
            const productName = button.dataset.name;
            const productPrice = button.dataset.price;

            barcodePreview.innerHTML = ''; // Clear previous content

            // Create barcodes grid with 15 labels (fixed quantity)
            let barcodeGrid = '';
            for (let i = 0; i < barcodeQuantityInput; i++) {
                barcodeGrid += `
                    <div style="width: 30%; display: inline-block; text-align: center; margin: 10px 0 ; border: 2px solid #ddd; /* Add border to each label */
                            border-radius: 10px;">
                        <p><strong>${productName}</strong></p>
                        <p><strong>السعر:</strong> ${productPrice}</p>
                        <svg id="barcode${productId}-${i}"></svg>
                    </div>
                `;
            }
            barcodePreview.innerHTML = `<div style="display: flex; flex-wrap: wrap; justify-content: space-between; ">${barcodeGrid}</div>`;

            // Generate barcodes for each label
            for (let i = 0; i < barcodeQuantityInput; i++) {
                JsBarcode(`#barcode${productId}-${i}`, productId, {
                    format: "CODE128",
                    displayValue: true,
                    width: 2,
                    height: 50
                });
            }

            // Show the modal
            $(barcodeModal).modal('show');
        });
    });

  // Print barcode
printBarcodeButton.addEventListener('click', () => {
    const printContent = `
        <html>
            <head>
                <title>طباعة الباركود</title>
                <style>
                    @media print {
                        body {
                            margin: 0;
                            padding: 0;
                            width: 21cm;
                            height: 29.7cm;
                        }
                        .container {
                            display: flex;
                            flex-wrap: wrap;
                            justify-content: space-between;
                            padding: 10px;
                            box-sizing: border-box;
                        }
                        .label {
                            width: 7cm; /* Adjust width of each label */
                            height: 3cm; /* Adjust height of each label */
                            margin: 5px;
                            display: inline-block;
                            text-align: center;
                            overflow: hidden;
                            
                            padding: 10px;
                            box-sizing: border-box;
                            page-break-inside: avoid;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    ${barcodePreview.innerHTML}

                </div>
            </body>
        </html>
    `;

    // Open print preview window
    const printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write(printContent);
    printWindow.document.close();

    // Wait until the print window is loaded, then print
    printWindow.onload = () => {
        printWindow.print();
    };
    });

    });

</script>

<script>
    document.getElementById('exportExcel').addEventListener('click', function () {
        // Select the table
        var table = document.getElementById('example2');
        // Convert the table to a worksheet
        var worksheet = XLSX.utils.table_to_sheet(table);
        // Create a new workbook and append the worksheet
        var workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, "Products");
        // Export the workbook as an Excel file
        XLSX.writeFile(workbook, 'products_table.xlsx');
    });
</script>



@endsection