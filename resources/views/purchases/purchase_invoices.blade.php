@extends('layouts.master')

<title>VOLTIX - فواتير المشتريات</title>
@section('css')
<!--- Internal Select2 css-->
<link href="{{URL::asset('assets/plugins/select2/css/select2.min.css')}}" rel="stylesheet">
<style>
    .dropdown {
        position: relative;
        display: inline-block;
    }
    .dropdown-content {
        display: none;
        position: absolute;
        background-color: #f1f1f1;
        min-width: 160px;
        max-height: 200px; /* Set a max-height to limit the height of the dropdown */
        overflow-y: auto; /* Enables vertical scrolling */
        box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
        z-index: 1;
    }
    .dropdown-content .dropdown-item {
        color: black;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
    }
    .dropdown-content .dropdown-item:hover {
        background-color: #ddd;
    }
    .dropdown:hover .dropdown-content {
        display: block;
    }
    .search-input {
        width: 100%;
        padding: 8px;
        box-sizing: border-box;
    }
</style>


@endsection
@section('page-header')
    <!-- breadcrumb -->
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">المشتريات</h4>
                <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ تسجيل فاتورة مشتريات جديدة</span>
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

<!-- Display validation errors -->
@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
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
                                                <h4 class="card-title mg-b-0">جدول فواتير المشتريات</h4>
                                            </div>
                                            <p class="tx-12 tx-gray-500 mb-2">جدول عرض  لجميع فواتير المشتريات .... في حالة التعديل في بيانات اي فاتورة برجاء الضغط علي <a href="">زر تعديل </a></p>

                                        </div>



                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table text-md-nowrap" id="example2">
                                                    <thead>
                                                        <tr>
                                                            <th class="wd-10 border-bottom-0">رقم الفاتورة</th>
                                                            <th class="wd-20p border-bottom-0">تاريخ الفاتورة</th>
                                                            <th class="wd-20p border-bottom-0">اسم المورد</th>
                                                            <th class="wd-10p border-bottom-0">إجمالي الفاتورة</th>
                                                            <th class="wd-10p border-bottom-0">المبلغ المدفوع</th>
                                                            <th class="wd-10p border-bottom-0">المبلغ المتبقي</th>
                                                            <th class="wd-25p border-bottom-0"> action</th>
                                                            <th class="wd-25p border-bottom-0">تاريخ اخر تعديل علي بيانات السمتخدم</th>

                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($invoices as $invoice)
                                                        @php
                                                            // Find the supplier where the supplier ID matches the invoice's supplier ID
                                                            $supplier = $suppliers->firstWhere('id', $invoice->supplier_id);
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $invoice->id }}</td>
                                                            <td>{{ $invoice->date }}</td>
                                                            <td>{{ $supplier ? $supplier->name : 'N/A' }}</td>
                                                            <td>{{ $invoice->total_amount }}</td>
                                                            <td>{{ $invoice->paid_amount }}</td>
                                                            <td>{{ $invoice->payment_remaning_amount }}</td>
                                                            <td>
                                                                <!-- details purchase Invoice Button -->
                                                                <a class="btn ripple btn-info" data-target="#detailsPurchaseInvoice{{ $invoice->id }}" data-toggle="modal" href="">تفاصيل الفاتورة</a>
                                                                <!-- Large Modal for details purchase invoice-->
                                                                <div class="modal" id="detailsPurchaseInvoice{{ $invoice->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                                                    <div class="modal-dialog modal-lg" role="document" style="width: 88%; max-width: 88%;">
                                                                        <div class="modal-content modal-content-demo">
                                                                            <div class="modal-header">
                                                                                <h6 class="modal-title">تفاصيل فاتورة مشتريات </h6>
                                                                                <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                                                                                    <span aria-hidden="true">&times;</span>
                                                                                </button>
                                                                            </div>
                                                                            <div class="modal-body">
                                                                                    <div class="row row-sm">
                                                                                        <div class="col-6">
                                                                                            <div class="form-group mg-b-0">
                                                                                                <label class="form-label"> رقم الفاتورة: <span class="tx-danger">*</span></label>
                                                                                                <input class="form-control" name="edit_purchase_invoices_no" placeholder="قم بإدخال رقم الفاتورة" value="{{ $invoice->id }}" readonly type="text">
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-6">
                                                                                            <div class="form-group">
                                                                                                <label class="form-label">تاريخ الفاتورة: <span class="tx-danger">*</span></label>
                                                                                                <input class="form-control" name="edit_purchase_invoices_date" placeholder="قم بإدخال تاريخ الفاتورة" value="{{ $invoice->date }}" readonly type="date">
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="row row-sm" style="margin-bottom: 45px;">
                                                                                        <div class="form-group col-6">
                                                                                            <label for="purchase_invoices_supplier">المورد</label>
                                                                                            <input type="text" id="supplier_input" name="supplier_name" class="form-control" value="{{ $invoiceDetails->firstWhere('purchase_invoice_id', $invoice->id)->supplier_name ?? 'N/A' }}" readonly>
                                                                                        </div>
                                                                                    </div>
                                                                                    <hr>
                                                                                    <div class="table-responsive">
                                                                                        <table class="table text-md-nowrap table-bordered" id="invoice-items-table" style="text-align: center;">
                                                                                            <thead>
                                                                                                <tr style="background-color: rgba(25, 145, 117, 0.5);">
                                                                                                    <th>م</th>
                                                                                                    <th>المنتج</th>
                                                                                                    <th>الكمية</th>
                                                                                                    <th>السعر للوحدة</th>
                                                                                                    <th>الإجمالي</th>
                                                                                                    <th>سعر البيع</th>
                                                                                                    <th>الربح</th>
                                                                                                </tr>
                                                                                            </thead>
                                                                                            <tbody id="invoice-items-body">
                                                                                                @foreach ($invoiceDetails as $detail)
                                                                                                    @if ($invoice->id == $detail->purchase_invoice_id)

                                                                                                        <tr>
                                                                                                            <td>{{ $loop->iteration }}</td>

                                                                                                            <td>
                                                                                                                {{ $products->firstWhere('id', $detail->product_id)?->name ?? 'N/A' }}

                                                                                                            </td>

                                                                                                            <td>
                                                                                                                {{ $detail->quantity_product }}
                                                                                                            </td>
                                                                                                            <td>
                                                                                                                {{ $detail->purchase_price }}
                                                                                                            </td>
                                                                                                            <td>
                                                                                                                {{ $detail->total_product }}
                                                                                                            </td>
                                                                                                            <td>
                                                                                                                {{ $detail->product_price ?? '' }}
                                                                                                            </td>
                                                                                                            <td>
                                                                                                            {{ ($detail->quantity_product * ($detail->product_price ?? 0)) - ($detail->quantity_product * $detail->purchase_price) }}
                                                                                                            </td>

                                                                                                        </tr>
                                                                                                    @endif
                                                                                                @endforeach
                                                                                            </tbody>

                                                                                        </table>
                                                                                    </div>
                                                                                    <hr>
                                                                                    <div class="">
                                                                                        <div class="col-6">
                                                                                            <div class="form-group">
                                                                                                <label for="purchase_invoices_amount">إجمالي الفاتورة :</label>
                                                                                                <input type="number" step="0.01" id="edit_purchase_invoices_amount" name="edit_purchase_invoices_amount" class="form-control" value="{{ $invoice->total_amount }}" placeholder="مبلغ الفاتورة" readonly>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-6">
                                                                                            <div class="form-group">
                                                                                                <label class="form-label">المبلغ المدفوع:</label>
                                                                                                <input class="form-control" id="edit_amount_paid" name="edit_amount_paid" type="number" value="{{ $invoice->paid_amount }}" placeholder="أدخل المبلغ المدفوع" readonly>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-6">
                                                                                            <div class="form-group">
                                                                                                <label class="form-label">المبلغ المتبقي (دين):</label>
                                                                                                <input class="form-control" id="edit_remaining_amount" name="edit_remaining_amount" type="number" value="{{ $invoice->remaining_amount }}" placeholder="المبلغ المتبقي" readonly>
                                                                                            </div>
                                                                                        </div>
                                                                                        <!-- Displaying the Invoice Image -->
                                                                                        <div class="form-group">
                                                                                            <label class="form-label">صورة الفاتورة:</label>
                                                                                            <img src="{{ $invoice->purchase_invoices_pic ? asset('images/purchases_invoices/' . $invoice->purchase_invoices_pic) : '' }}"  style="width: 100%; height: auto;">
                                                                                                @if (!$invoice->purchase_invoices_pic)
                                                                                                    <p style="color : red;">لا توجد صورة للفاتورة</p>
                                                                                                @endif
                                                                                        </div>
                                                                                    </div>
                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">إغلاق</button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- End Large Modal for details purchase invoice -->

                                                                <!-- Edit Purchase Invoice Button -->
                                                                <form id="editInvoiceForm" action="{{ route('editInvoice', ['invoice_id' => $invoice->id]) }}"
                                                                    method="post" style="display: inline;">
                                                                    @csrf
                                                                    <button type="button" class="btn btn-warning" onclick="openInvoicePopup(this.form)">تعديل الفاتورة</button>
                                                                </form>
                                                                <!-- End Edit Purchase Invoice Button -->





                                                                <!-- Delete Button for purchase invoice -->
                                                                <form action="{{ route('purchase_invoices_destroy', $invoice->id) }}" method="POST" style="display: inline;">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من حذف الفاتورة؟')">مسح</button>
                                                                </form>
                                                                <!-- end Delete Button for purchase invoice -->

                                                            </td>

                                                            <td>{{ $invoice->created_at }}</td>
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
    // Add new row for purchase invoice items
    document.getElementById('add-item').addEventListener('click', function () {
        let tableBody = document.getElementById('invoice-items-body');
        let newRow = document.createElement('tr');

        newRow.innerHTML = `
            <td>
                <select class="form-control select2" id="product_name" name="product_id[]" required>
                    <option label="اختر المنتج">اختر المنتج</option>
                    @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </td>

            <td><input type="number" name="quantity[]" class="form-control quantity" placeholder="الكمية" required></td>
            <td><input type="number" name="unit_price[]" class="form-control unit-price" placeholder="السعر للوحدة" required></td>
            <td><input type="number" name="total[]" class="form-control total" placeholder="الإجمالي" readonly></td>
            <td><input type="number" name="sales_price[]" class="form-control sales_price" placeholder="سعر البيع" required></td>
            <td><input type="number" name="profit[]" class="form-control profit" placeholder="الربح" readonly></td>
            <td><button type="button" class="btn btn-danger remove-item">حذف</button></td>
        `;

        tableBody.appendChild(newRow);
        $('.select2').select2();
    });

    // Remove row from purchase invoice items
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-item')) {
            e.target.closest('tr').remove();
            calculateTotalInvoiceAmount(); // Update total when item is removed
            calculateTotalSalesPriceAndProfit(); // Recalculate sales price and profit when item is removed
        }
    });

    // Calculate total price based on quantity and unit price
    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('quantity') || e.target.classList.contains('unit-price') || e.target.classList.contains('sales_price')) {
            let row = e.target.closest('tr');
            let quantity = parseFloat(row.querySelector('.quantity').value) || 0;
            let unitPrice = parseFloat(row.querySelector('.unit-price').value) || 0;
            let salesPrice = parseFloat(row.querySelector('.sales_price').value) || 0;
            let total = row.querySelector('.total');
            let profit = row.querySelector('.profit');

            // Calculate total for the row
            total.value = (quantity * unitPrice).toFixed(2);

            // Calculate profit for the row
            profit.value = ((salesPrice - unitPrice) * quantity).toFixed(2);

            // Recalculate the total invoice amount and sales price
            calculateTotalInvoiceAmount();
            calculateTotalSalesPriceAndProfit();
        }
    });

    // Calculate the total invoice amount
    function calculateTotalInvoiceAmount() {
        let totalAmount = 0;
        document.querySelectorAll('.total').forEach(function (totalField) {
            totalAmount += parseFloat(totalField.value) || 0;
        });
        let totalAmountField = document.getElementById('purchase_invoices_amount');
        if (totalAmountField) {
            totalAmountField.value = totalAmount.toFixed(2);
            calculateRemainingAmount(); // Update remaining amount based on the total
        }
    }

    // Calculate remaining amount based on total and paid amount
    document.getElementById('amount_paid')?.addEventListener('input', calculateRemainingAmount);

    function calculateRemainingAmount() {
        let totalAmountField = document.getElementById('purchase_invoices_amount');
        let paidAmountField = document.getElementById('amount_paid');
        let remainingAmountField = document.getElementById('remaining_amount');
        if (totalAmountField && paidAmountField && remainingAmountField) {
            let totalAmount = parseFloat(totalAmountField.value) || 0;
            let amountPaid = parseFloat(paidAmountField.value) || 0;
            remainingAmountField.value = (totalAmount - amountPaid).toFixed(2);
        }
    }

    // Calculate total sales price and profit based on sales price
    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('sales_price')) {
            calculateTotalSalesPriceAndProfit();
        }
    });

    // Calculate the total sales price and profit
    function calculateTotalSalesPriceAndProfit() {
        let totalSalesPrice = 0;
        let totalProfit = 0;

        document.querySelectorAll('.sales_price').forEach(function (salesPriceField) {
            let row = salesPriceField.closest('tr');
            let quantity = parseFloat(row.querySelector('.quantity').value) || 0;
            let salesPrice = parseFloat(salesPriceField.value) || 0;
            let unitPrice = parseFloat(row.querySelector('.unit-price').value) || 0;

            // Calculate totals and profits
            totalSalesPrice += salesPrice * quantity;
            let profit = (salesPrice - unitPrice) * quantity;
            totalProfit += profit;

            // Update profit field
            row.querySelector('.profit').value = profit.toFixed(2);
        });

        let totalSalesPriceField = document.getElementById('total_sales_price');
        let totalProfitField = document.getElementById('total_profit');
        let overallProfitField = document.getElementById('profit');
        let totalInvoiceAmountField = document.getElementById('purchase_invoices_amount');

        if (totalSalesPriceField) totalSalesPriceField.value = totalSalesPrice.toFixed(2);
        if (totalProfitField) totalProfitField.value = totalProfit.toFixed(2);

        if (overallProfitField && totalInvoiceAmountField) {
            let totalInvoiceAmount = parseFloat(totalInvoiceAmountField.value) || 0;
            overallProfitField.value = (totalSalesPrice - totalInvoiceAmount).toFixed(2);
        }
    }

    // Ensure the script runs after the DOM content is fully loaded
    document.addEventListener('DOMContentLoaded', function () {
        calculateRemainingAmount(); // Initialize remaining amount calculation
    });
</script>

<script>
    // Add new row for purchase invoice items
    document.getElementById('edit_add-item').addEventListener('click', function () {
        let tableBody = document.getElementById('invoice-items-body');
        let newRow = document.createElement('tr');

        newRow.innerHTML = `
            <td>
                <select class="form-control select2" name="product_id[]" required>
                    <option label="اختر المنتج">اختر المنتج</option>
                    @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </td>
            <td><input type="number" name="edit_quantity[]" class="form-control quantity" placeholder="الكمية" required></td>
            <td><input type="number" name="edit_unit_price[]" class="form-control unit-price" placeholder="السعر للوحدة" required></td>
            <td><input type="number" name="edit_total[]" class="form-control total" placeholder="الإجمالي" readonly></td>
            <td><input type="number" name="edit_sales_price[]" class="form-control sales_price" placeholder="سعر البيع" required></td>
            <td><input type="number" name="edit_profit[]" class="form-control profit" placeholder="الربح" readonly></td>
            <td><button type="button" class="btn btn-danger remove-item">حذف</button></td>
        `;

        tableBody.appendChild(newRow);
        $('.select2').select2(); // Reinitialize Select2 for the newly added row
    });

    // Remove row from purchase invoice items
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-item')) {
            e.target.closest('tr').remove();
            calculateTotalInvoiceAmount(); // Update total when item is removed
            calculateTotalSalesPriceAndProfit(); // Recalculate sales price and profit when item is removed
        }
    });

    // Calculate total price based on quantity and unit price
    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('quantity') || e.target.classList.contains('unit-price') || e.target.classList.contains('sales_price')) {
            let row = e.target.closest('tr');
            let quantity = parseFloat(row.querySelector('.quantity').value) || 0;
            let unitPrice = parseFloat(row.querySelector('.unit-price').value) || 0;
            let salesPrice = parseFloat(row.querySelector('.sales_price').value) || 0;
            let total = row.querySelector('.total');
            let profit = row.querySelector('.profit');

            // Calculate total for the row
            total.value = (quantity * unitPrice).toFixed(2);

            // Calculate profit for the row
            profit.value = ((salesPrice - unitPrice) * quantity).toFixed(2);

            // Recalculate the total invoice amount and sales price
            calculateTotalInvoiceAmount();
            calculateTotalSalesPriceAndProfit();
        }
    });

    // Calculate the total invoice amount
    function calculateTotalInvoiceAmount() {
        let totalAmount = 0;
        document.querySelectorAll('.total').forEach(function (totalField) {
            totalAmount += parseFloat(totalField.value) || 0;
        });
        let totalAmountField = document.getElementById('purchase_invoices_amount');
        if (totalAmountField) {
            totalAmountField.value = totalAmount.toFixed(2);
            calculateRemainingAmount(); // Update remaining amount based on the total
        }
    }

    // Calculate remaining amount based on total and paid amount
    document.getElementById('edit_amount_paid')?.addEventListener('input', calculateRemainingAmount);

    function calculateRemainingAmount() {
        let totalAmountField = document.getElementById('purchase_invoices_amount');
        let paidAmountField = document.getElementById('edit_amount_paid');
        let remainingAmountField = document.getElementById('edit_remaining_amount');
        if (totalAmountField && paidAmountField && remainingAmountField) {
            let totalAmount = parseFloat(totalAmountField.value) || 0;
            let amountPaid = parseFloat(paidAmountField.value) || 0;
            remainingAmountField.value = (totalAmount - amountPaid).toFixed(2);
        }
    }

    // Calculate total sales price and profit based on sales price
    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('sales_price')) {
            calculateTotalSalesPriceAndProfit();
        }
    });

    // Calculate the total sales price and profit
    function calculateTotalSalesPriceAndProfit() {
        let totalSalesPrice = 0;
        let totalProfit = 0;

        document.querySelectorAll('.sales_price').forEach(function (salesPriceField) {
            let row = salesPriceField.closest('tr');
            let quantity = parseFloat(row.querySelector('.quantity').value) || 0;
            let salesPrice = parseFloat(salesPriceField.value) || 0;
            let unitPrice = parseFloat(row.querySelector('.unit-price').value) || 0;

            // Calculate totals and profits
            totalSalesPrice += salesPrice * quantity;
            let profit = (salesPrice - unitPrice) * quantity;
            totalProfit += profit;

            // Update profit field
            row.querySelector('.profit').value = profit.toFixed(2);
        });

        let totalSalesPriceField = document.getElementById('total_sales_price');
        let totalProfitField = document.getElementById('total_profit');
        let overallProfitField = document.getElementById('profit');
        let totalInvoiceAmountField = document.getElementById('purchase_invoices_amount');

        if (totalSalesPriceField) totalSalesPriceField.value = totalSalesPrice.toFixed(2);
        if (totalProfitField) totalProfitField.value = totalProfit.toFixed(2);

        if (overallProfitField && totalInvoiceAmountField) {
            let totalInvoiceAmount = parseFloat(totalInvoiceAmountField.value) || 0;
            overallProfitField.value = (totalSalesPrice - totalInvoiceAmount).toFixed(2);
        }
    }

    // Ensure the script runs after the DOM content is fully loaded
    document.addEventListener('DOMContentLoaded', function () {
        calculateTotalInvoiceAmount();
        calculateTotalSalesPriceAndProfit();
    });
</script>

<script>
function openInvoicePopup(form) {
    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
        }
    })
    .then(response => response.text())
    .then(html => {
        const width = Math.round(window.screen.width * 0.8);
        const height = Math.round(window.screen.height * 0.8);
        const left = Math.round((window.screen.width - width) / 2);
        const top = Math.round((window.screen.height - height) / 2);

        const newWindow = window.open(
            '',
            '_blank',
            `width=${width},height=${height},left=${left},top=${top}`
        );

        newWindow.document.write(`
            <html>
                <head>
                    <style>
                        body {
                            zoom: 100%;
                            transform: scale(1);
                            transform-origin: 0 0;
                        }
                    </style>
                </head>
                <body>
                    ${html}
                </body>
            </html>
        `);
        newWindow.document.close();
    })
    .catch(error => console.error('Error:', error));

    return false; // منع الإرسال الافتراضي للنموذج
}

</script>




@endsection
