@extends('layouts.master')

<title>  معرض أيه - فواتير المبيعات </title>
@section('css')
<!--- Internal Select2 css-->
<link href="{{URL::asset('assets/plugins/select2/css/select2.min.css')}}" rel="stylesheet">
<style>
    .sales-invoices-card { border: 0; border-radius: 14px; box-shadow: 0 8px 30px rgba(24, 39, 75, .08); overflow: hidden; }
    .sales-invoices-card .card-header { padding: 22px 24px; background: linear-gradient(120deg, #fff, #f5f7ff); border-bottom: 1px solid #edf0f5; }
    .sales-invoices-card .card-title { font-weight: 700; color: #182230; }
    .sales-invoices-card .table-responsive { border-radius: 10px; }
    .sales-invoices-table { min-width: 1450px; margin-bottom: 0; }
    .sales-invoices-table thead th { color: #536176; background: #f5f7fb; border-top: 0; font-size: 12px; white-space: nowrap; }
    .sales-invoices-table tbody tr:hover { background: #f8faff; }
    .sales-invoices-table td { vertical-align: middle; white-space: nowrap; }
    .invoice-number { display: inline-block; padding: 5px 10px; border-radius: 8px; background: #eef2ff; color: #4338ca; font-weight: 700; }
    .invoice-amount { font-weight: 700; color: #1e293b; font-variant-numeric: tabular-nums; }
    .invoice-amount-due { color: #b45309; }
    .invoice-actions { display: flex; align-items: center; gap: 6px; min-width: 280px; }
    .invoice-actions .btn { white-space: nowrap; }
    .invoice-modal .modal-content { border: 0; border-radius: 15px; overflow: hidden; box-shadow: 0 20px 60px rgba(15, 23, 42, .2); }
    .invoice-modal .modal-dialog { max-width: 980px; }
    .invoice-modal-header { color: #fff; background: linear-gradient(120deg, #3347a9, #5369d8); border-bottom: 0; }
    .invoice-modal-header small { display: block; margin-top: 4px; color: rgba(255,255,255,.78); }
    .invoice-modal .modal-body { padding: 24px; }
    .invoice-customer-strip { display: flex; flex-wrap: wrap; gap: 12px; padding: 14px; border: 1px solid #e8ecf4; border-radius: 12px; background: #f8faff; }
    .invoice-customer-strip > div { flex: 1 1 180px; min-width: 0; }
    .invoice-customer-strip span { display: block; margin-bottom: 4px; color: #758198; font-size: 12px; }
    .invoice-customer-strip strong { display: block; color: #1e293b; }
    .invoice-customer-strip .form-control { margin-top: 3px; }
    .invoice-summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(155px, 1fr)); gap: 10px; margin: 18px 0 24px; }
    .invoice-summary-item { padding: 13px 15px; border: 1px solid #e9edf5; border-radius: 11px; background: #fff; }
    .invoice-summary-item span { display: block; margin-bottom: 7px; color: #738097; font-size: 12px; }
    .invoice-summary-item strong { color: #182230; font-size: 15px; font-variant-numeric: tabular-nums; }
    .invoice-summary-due { border-color: #f1d6a8; background: #fffaf1; }
    .invoice-summary-due strong { color: #a65a00; }
    .invoice-section-heading { margin: 0 0 12px; color: #26344b; font-size: 15px; font-weight: 700; }
    .invoice-items-table { margin-bottom: 0; border: 1px solid #e8ecf4; }
    .invoice-items-table thead th { color: #526078; background: #f5f7fb; border-bottom: 1px solid #e8ecf4; white-space: nowrap; }
    .invoice-items-table td, .invoice-items-table th { vertical-align: middle; }
    .invoice-edit-items { min-width: 650px; }
    .invoice-edit-items input, .invoice-edit-items select { min-width: 90px; }
    .invoice-modal .modal-footer { padding: 14px 24px; background: #fafbfe; }
    .invoice-delete-modal .modal-dialog { max-width: 520px; }
    @media (max-width: 767px) {
        .sales-invoices-card .card-header, .sales-invoices-card .card-body { padding: 15px; }
        .invoice-modal .modal-body { padding: 16px; }
        .invoice-actions { min-width: 0; flex-wrap: wrap; }
    }
</style>
@endsection
@section('page-header')
				<!-- breadcrumb -->
				<div class="breadcrumb-header justify-content-between">
					<div class="my-auto">
						<div class="d-flex">
							<h4 class="content-title mb-0 my-auto">المبيعات</h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/ فواتير المبيعات</span>
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

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


                	<!--div-->
					<div class="col-xl-12">
						<div class="card sales-invoices-card">
							<div class="card-header pb-0">
								<div class="d-flex justify-content-between">
									<div>
										<h4 class="card-title mg-b-0">فواتير المبيعات</h4>
										<p class="text-muted mb-0 mt-1">إجماليات الفواتير بعد الخصم والمرتجعات والسداد</p>
									</div>
								</div>

							</div>
							<div class="card-body">
								<form method="GET" action="{{ route('all_sales') }}" class="invoice-date-filter mb-4">
									<div class="form-row align-items-end">
										<div class="form-group col-md-4 mb-2">
											<label for="date-from">من تاريخ</label>
											<input id="date-from" type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
										</div>
										<div class="form-group col-md-4 mb-2">
											<label for="date-to">إلى تاريخ</label>
											<input id="date-to" type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
										</div>
										<div class="form-group col-md-4 mb-2 d-flex">
											<button type="submit" class="btn btn-primary mr-2">تطبيق الفلتر</button>
											<a href="{{ route('all_sales') }}" class="btn btn-light">مسح التواريخ</a>
										</div>
									</div>
								</form>
								<div class="table-responsive">
									<table class="table text-md-nowrap sales-invoices-table" id="example2">
										<thead>
											<tr>
												<th class="wd-10p border-bottom-0">رقم الفاتورة</th>
												<th class="wd-10p border-bottom-0">اسم العميل</th>
												<th class="wd-10p border-bottom-0">رقم الموبايل</th>
												<th class="wd-10p border-bottom-0"> طريقة الدفع</th>
												<th class="wd-10p border-bottom-0">الخصم</th>
												<th class="wd-10p border-bottom-0">الصافي بعد الخصم</th>
												<th class="wd-10p border-bottom-0">قيمة المرتجعات</th>
												<th class="wd-10p border-bottom-0">الصافي بعد المرتجع</th>
												<th class="wd-10p border-bottom-0">المدفوع بعد خصم المرتجعات</th>
												<th class="wd-9p border-bottom-0">المتبقي الحالي</th>
												<th class="wd-10p border-bottom-0">تاريخ الفاتورة</th>
												<th class="wd-20p border-bottom-0">الإجراءات</th>
												<th class="wd-10p border-bottom-0">تاريخ اخر تعديل</th>
											</tr>
										</thead>
										<tbody>
											@foreach($salesInvoices as $invoice)
											<tr>
												<td><span class="invoice-number">#{{ $invoice->sales_invoice_id }}</span></td>
												<td>{{ $invoice->customer_name ?: 'بدون بيانات عميل' }}</td>
												<td>{{ $invoice->customer_phone ?: '—' }}</td>
												<td><span class="badge {{ $invoice->payment_method === 'credit' ? 'badge-warning' : ($invoice->payment_method === 'visa' ? 'badge-info' : 'badge-success') }}">{{ ['cash' => 'نقداً', 'visa' => 'بطاقة', 'credit' => 'آجل'][$invoice->payment_method] ?? $invoice->payment_method }}</span></td>
												<td class="invoice-amount">{{ number_format($invoice->invoice_discount, 2) }} ج.م</td>
												<td class="invoice-amount">{{ number_format($invoice->net_after_discount, 2) }} ج.م</td>
												<td class="invoice-amount">{{ number_format($invoice->total_returned, 2) }} ج.م</td>
												<td class="invoice-amount">{{ number_format($invoice->net_after_returns, 2) }} ج.م</td>
												<td class="invoice-amount">{{ number_format($invoice->total_paid_amount, 2) }} ج.م</td>
												<td class="invoice-amount invoice-amount-due">{{ number_format($invoice->adjusted_remaining_amount, 2) }} ج.م</td>
												<td>{{ $invoice->invoice_date }}</td>

												<td>
    <div class="invoice-actions">
        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#detailsSalesInvoice{{ $invoice->sales_invoice_id }}">تفاصيل</button>
        @can('sales.update')
            @if($invoice->return_count > 0)
                <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="لا يمكن تعديل فاتورة لها مرتجعات">تعديل</button>
            @else
                <button type="button" class="btn btn-sm btn-outline-warning" data-toggle="modal" data-target="#editSalesInvoice{{ $invoice->sales_invoice_id }}">تعديل</button>
            @endif
        @endcan
        <a class="btn btn-sm btn-outline-info" href="{{ route('invoice.show', $invoice->sales_invoice_id) }}" target="_blank" rel="noopener">طباعة</a>
        @can('sales.delete')<button type="button" class="btn btn-sm btn-outline-danger" data-toggle="modal" data-target="#deleteSalesInvoce{{ $invoice->sales_invoice_id }}">حذف</button>@endcan
    </div>
    @include('sales.partials.invoice_details_modal')
    @can('sales.update')
    @if($invoice->return_count <= 0)
        @include('sales.partials.invoice_edit_modal')
    @endif
    @endcan
    @can('sales.delete')
    <div class="modal fade invoice-modal invoice-delete-modal" id="deleteSalesInvoce{{ $invoice->sales_invoice_id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header invoice-modal-header">
                    <h5 class="modal-title">حذف فاتورة #{{ $invoice->sales_invoice_id }}</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="إغلاق"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">سيتم إعادة الكميات المتبقية بعد المرتجعات للمخزون واستبعاد الفاتورة ودفعاتها من الحسابات النشطة.</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">إلغاء</button>
                    <form action="{{ route('invoice.destroy', $invoice->sales_invoice_id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">تأكيد الحذف</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endcan
</td>
												<td>{{ $invoice->invoice_update }}</td>
											</tr>
											@endforeach


										</tbody>
									</table>
								</div>
								<div class="mt-3">
									{{ $salesInvoices->links() }}
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
<script src="{{URL::asset('assets/plugins/select2/js/select2.min.js')}}"></script>
<script src="{{URL::asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
<script src="{{URL::asset('assets/plugins/datatable/js/dataTables.bootstrap4.js')}}"></script>
<script src="{{URL::asset('assets/plugins/datatable/js/dataTables.responsive.min.js')}}"></script>
<script src="{{URL::asset('assets/plugins/datatable/js/responsive.bootstrap4.min.js')}}"></script>
<script>
    $(function () {
        $('.invoice-edit-form').closest('.invoice-modal').on('shown.bs.modal', function () {
            $(this).find('.sales-product-select').each(function () {
                const $select = $(this);
                if ($select.hasClass('select2-hidden-accessible')) return;

                $select.select2({
                    width: '100%',
                    minimumInputLength: 1,
                    dropdownParent: $select.closest('.modal'),
                    ajax: {
                        url: "{{ route('sales.products.search') }}",
                        dataType: 'json',
                        delay: 250,
                        data: params => ({ q: params.term }),
                        processResults: data => data,
                        cache: true
                    }
                });
                $select.on('select2:select', function (event) {
                    const row = this.closest('tr');
                    const price = parseFloat(event.params.data.price) || 0;
                    row.querySelector('.product-price').value = price.toFixed(2);
                    updateInvoiceEditTotals(this.closest('.invoice-edit-form'));
                });
            });
        });

        $('#example2').DataTable({
            responsive: true,
            language: { searchPlaceholder: 'Search...', sSearch: '' }
        });
    });

    function updateInvoiceEditTotals(form) {
        if (!form) return;
        const gross = Array.from(form.querySelectorAll('.product-total'))
            .reduce((sum, field) => sum + (parseFloat(field.value) || 0), 0);
        const discount = parseFloat(form.dataset.discount) || 0;
        const returns = parseFloat(form.dataset.returns) || 0;
        const additionalPaid = parseFloat(form.dataset.additionalPaid) || 0;
        const initialPaid = parseFloat(form.querySelector('.edit-invoice-initial-paid').value) || 0;
        const net = Math.max(0, gross - discount - returns);
        const due = Math.max(0, net - initialPaid - additionalPaid);

        form.querySelector('.edit-gross-input').value = gross.toFixed(2);
        form.querySelector('.edit-gross-total').textContent = `${gross.toFixed(2)} ج.م`;
        form.querySelector('.edit-net-total').textContent = `${net.toFixed(2)} ج.م`;
        form.querySelector('.edit-due-total').textContent = `${due.toFixed(2)} ج.م`;
    }

    document.addEventListener('input', function (event) {
        const form = event.target.closest('.invoice-edit-form');
        if (!form) return;

        if (event.target.matches('.product-quantity, .product-price')) {
            const row = event.target.closest('tr');
            const quantity = parseFloat(row.querySelector('.product-quantity').value) || 0;
            const price = parseFloat(row.querySelector('.product-price').value) || 0;
            row.querySelector('.product-total').value = (quantity * price).toFixed(2);
        }
        updateInvoiceEditTotals(form);
    });

    $('.invoice-edit-form').each(function () { updateInvoiceEditTotals(this); });
</script>

<script>
    function openInvoicePopup(form) {
        // Get the form's action URL
        const url = form.action;

        // Calculate 50% of the screen's width and 100% of the screen's height
        const screenWidth = window.innerWidth;
        const screenHeight = window.innerHeight;
        const popupWidth = screenWidth * 0.5; // 50% of screen width
        const popupHeight = screenHeight; // 100% of screen height

        // Position the popup at the center horizontally, but full height
        const left = (screenWidth - popupWidth) / 2;

        // Open the popup
        const popup = window.open(
            url,
            '_blank',
            `width=${popupWidth},height=${popupHeight},left=${left},top=0,scrollbars=yes`
        );

        // Return false to prevent the default form submission
        return false;
    }
</script>


@endsection
