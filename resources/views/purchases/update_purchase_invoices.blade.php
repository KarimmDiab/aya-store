@extends('layouts.master3')

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
            max-height: 200px;
            /* Set a max-height to limit the height of the dropdown */
            overflow-y: auto;
            /* Enables vertical scrolling */
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






    <div class="card">
        <div class="card-body">
            <div class="main-content-label mg-b-5">
                تسجيل فاتورة مشتريات جديدة
            </div>
            <p class="mg-b-20">قم بتسجيل بيانات الفاتورة</p>

            <form action="{{ route('purchase_invoices_update', ['invoice_id' => $invoices->id]) }}" method="POST">
                @csrf
                @method('PUT') <!-- Add this for PUT request -->

                <div class="row row-sm">
                    <div class="col-6">
                        <div class="form-group mg-b-0">
                            <label class="form-label"> رقم الفاتورة: <span class="tx-danger">*</span></label>
                            <input class="form-control" name="purchase_invoices_no" placeholder="قم بإدخال رقم الفاتورة"
                                required type="text" value="{{ $invoices->id }}" readonly>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">تاريخ الفاتورة: <span class="tx-danger">*</span></label>
                            <input class="form-control" name="purchase_invoices_date" placeholder="قم بإدخال تاريخ الفاتورة"
                                required type="date" value="{{ $invoices->date }}">
                        </div>
                    </div>
                </div>
                <div class="row row-sm">
                    <div class="form-group col-6">
                        <label for="purchase_invoices_supplier">المورد <span class="tx-danger">*</span></label>
                        <select id="purchase_invoices_supplier" name="purchase_invoices_supplier"
                            class="form-control select2" required>
                            <option value="">اختر المورد</option>
                            @foreach ($suppliers as $supplier)
                                @if ($invoices->supplier_id == $supplier->id)
                                    <option value="{{ $supplier->id }}" selected>
                                        {{ $supplier->name }}
                                    </option>
                                @endif
                            @endforeach
                        </select>


                    </div>
                </div>

                <hr>
                <!-- Table for Purchase Invoice Items -->
                <div class="table-responsive">
                    <table class="table text-md-nowrap table-bordered" id="invoice-items-table" style="text-align: center;">
                        <thead>
                            <tr class="table-info">
                                <th>المنتج</th>
                                <th>الكمية</th>
                                <th>السعر للوحدة</th>
                                <th>الإجمالي</th>
                                <th>سعر البيع</th>
                                <th>الربح</th>
                                <th>حذف</th>
                            </tr>
                        </thead>
                        <tbody id="invoice-items-body">
                            @foreach ($invoiceDetails as $detail)
                                @if ($invoices->id == $detail->purchase_invoice_id)
                                    <tr>
                                        <td>
                                            <select class="form-control select2" id="product_name" name="product_id[]" required>
                                                <option label="اختر المنتج">اختر المنتج</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}" {{ $detail->product_id == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="number" name="quantity[]" class="form-control quantity"
                                                placeholder="الكمية" value="{{ $detail->quantity_product }}" required></td>
                                        <td><input type="number" name="unit_price[]" class="form-control unit-price"
                                                placeholder="السعر للوحدة" value="{{ $detail->purchase_price }}" required></td>
                                        <td><input type="number" name="total[]" class="form-control total" placeholder="الإجمالي"
                                                value="{{ $detail->total_product }}" readonly></td>
                                        <td><input type="number" name="sales_price[]" class="form-control sales_price"
                                                placeholder="سعر البيغ" value="{{ $detail->product_price }}" required></td>
                                        <td><input type="number" name="profit[]" class="form-control profit" placeholder="الربح"
                                                Value="{{ ($detail->quantity_product * ($detail->product_price ?? 0)) - ($detail->quantity_product * $detail->purchase_price) }}"
                                                readonly></td>
                                        <td><button type="button" class="btn btn-danger remove-item">حذف</button></td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-success" id="add-item">إضافة منتج</button>
                </div>
                <hr>
                <!-- Total Invoice Amount (Disabled and Auto-calculated) -->
                <div class="">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="purchase_invoices_amount">إجمالي الفاتورة :</label>
                            <input type="number" step="0.01" id="purchase_invoices_amount" name="purchase_invoices_amount"
                                class="form-control" placeholder="مبلغ الفاتورة" value="{{ $invoices->total_amount }}"
                                readonly>
                        </div>
                    </div>




                    <!-- Paid Amount Field -->
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">المبلغ المدفوع:</label>
                            <input class="form-control" id="amount_paid" name="amount_paid" type="number"
                                placeholder="أدخل المبلغ المدفوع" value="{{ $invoices->paid_amount }}" required>
                        </div>
                    </div>

                    <!-- Remaining Amount (Debt) -->
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">المبلغ المتبقي (دين):</label>
                            <input class="form-control" id="remaining_amount" name="remaining_amount" type="number"
                                placeholder="المبلغ المتبقي" value="{{ abs($invoices->remaining_amount) }}" readonly>
                        </div>
                    </div>

                </div>
                <hr>
                <input type="hidden" name="deleted_items" id="deleted_items">
                <div class="col-12">
                    <button class="btn btn-primary pd-x-20 mg-t-10" type="submit" style="width:100%;">تعديل بيانات
                        الفاتورة</button>
                </div>
            </form>
        </div>
    </div>






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
                           <input type="hidden" name="detail_id[]" value=""> 
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
        let deletedItems = [];

        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-item')) {
                let row = e.target.closest('tr');

                // لو الصف موجود في الداتا بيز
                let idInput = row.querySelector('input[name="detail_id[]"]');

                if (idInput) {
                    deletedItems.push(idInput.value);
                }

                // خزّن القيم
                document.getElementById('deleted_items').value = deletedItems.join(',');

                row.remove();

                calculateTotalInvoiceAmount();
                calculateTotalSalesPriceAndProfit();
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







@endsection