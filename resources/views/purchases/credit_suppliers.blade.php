@extends('layouts.master')

@section('title')
    معرض أيه - موردين الأجل
@endsection

@section('css')
<!-- Internal Data table css -->
<link href="{{ URL::asset('assets/plugins/datatable/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet" />
<link href="{{ URL::asset('assets/plugins/datatable/css/responsive.bootstrap4.min.css') }}" rel="stylesheet" />

<style>
    /* ---------- Summary cards ---------- */
    .stat-card { border: 0; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.06); transition: transform .15s ease, box-shadow .15s ease; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,.09); }
    .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }
    .stat-icon.blue   { background: #e8f0ff; color: #0162e8; }
    .stat-icon.green  { background: #e3f8ee; color: #09ad95; }
    .stat-icon.purple { background: #f1eaff; color: #6f42c1; }
    .stat-icon.orange { background: #fff1e0; color: #f7931e; }
    .stat-value { font-size: 24px; font-weight: 700; line-height: 1.2; }
    .stat-label { font-size: 13px; color: #7b8190; }

    /* ---------- Table ---------- */
    .suppliers-card { border: 0; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.06); }
    #suppliersTable thead th { background: #f6f7fb; color: #4b5161; font-weight: 600; font-size: 13px; border-bottom: 1px solid #e6e8ef; white-space: nowrap; }
    #suppliersTable tbody td { vertical-align: middle; }
    #suppliersTable tbody tr { transition: background .12s ease; }
    #suppliersTable tbody tr:hover { background: #f9faff; }

    .supplier-cell { display: flex; align-items: center; gap: 10px; }
    .avatar-initial { width: 40px; height: 40px; border-radius: 50%; background: #f1eaff; color: #6f42c1; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0; }
    .supplier-name { font-weight: 600; color: #1f2430; margin: 0; }
    .supplier-meta { font-size: 12px; color: #9aa0ae; display: block; max-width: 260px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .phone-link { direction: ltr; unicode-bidi: embed; display: inline-block; color: #4b5161; }
    .phone-link:hover { color: #0162e8; text-decoration: none; }

    .badge-soft { padding: 5px 10px; border-radius: 20px; font-weight: 600; font-size: 12px; display: inline-block; white-space: nowrap; }
    .badge-soft.info      { background: #e8f0ff; color: #0162e8; }
    .badge-soft.muted     { background: #f0f1f5; color: #8a90a0; }
    .badge-soft.warning   { background: #fff1e0; color: #d97706; }
    .badge-soft.success   { background: #e3f8ee; color: #09ad95; }

    .amount { font-weight: 700; color: #1f2430; white-space: nowrap; }
    .amount.zero { color: #9aa0ae; font-weight: 500; }
    .updated-at { font-size: 12px; color: #8a90a0; white-space: nowrap; }
    .action-group .btn { padding: 5px 9px; border-radius: 8px; }

    /* ---------- Modals ---------- */
    .modal-content { border: 0; border-radius: 14px; overflow: hidden; }
    .modal-header { background: #f6f7fb; border-bottom: 1px solid #e6e8ef; }
    .modal-title { font-weight: 700; }
    .field-error { color: #ec2e2e; font-size: 12px; margin-top: 4px; display: none; }
    .form-control.is-invalid ~ .field-error { display: block; }
    .delete-icon { width: 64px; height: 64px; border-radius: 50%; background: #fde8e8; color: #ec2e2e; display: flex; align-items: center; justify-content: center; font-size: 30px; margin: 0 auto 12px; }

    /* ---------- DataTables tweaks ---------- */
    .dataTables_filter input { border-radius: 8px; min-width: 240px; }
    .dataTables_length select { border-radius: 8px; }
    .btn.is-loading { pointer-events: none; opacity: .75; }
</style>
@endsection

@section('page-header')
<!-- breadcrumb -->
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto">الموردون</h4>
            <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ موردين الأجل</span>
        </div>
    </div>
</div>
<!-- breadcrumb -->
@endsection

@section('content')

@php
    $pm = function ($m) {
        if ($m === 'credit') return ['آجل', 'warning'];
        if ($m === 'cash')   return ['نقداً', 'success'];
        return [$m ?: null, 'muted'];
    };
@endphp

{{-- ============ Flash messages ============ --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle ml-1"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="إغلاق"><span aria-hidden="true">&times;</span></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle ml-1"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="إغلاق"><span aria-hidden="true">&times;</span></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0 pr-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="إغلاق"><span aria-hidden="true">&times;</span></button>
    </div>
@endif

{{-- ============ Summary cards ============ --}}
<div class="row row-sm">
    <div class="col-12 col-md-4 mb-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon blue ml-3"><i class="fas fa-truck"></i></div>
                <div>
                    <div class="stat-value">{{ number_format($results->count()) }}</div>
                    <div class="stat-label">عدد موردين الأجل</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4 mb-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon orange ml-3"><i class="fas fa-file-invoice"></i></div>
                <div>
                    <div class="stat-value">{{ number_format($results->sum('count_invoices')) }}</div>
                    <div class="stat-label">إجمالي عدد الفواتير</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4 mb-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon purple ml-3"><i class="fas fa-coins"></i></div>
                <div>
                    <div class="stat-value">{{ number_format((float) $summary->total_invoices, 2) }} <small class="tx-13">ج.م</small></div>
                    <div class="stat-label">إجمالي الفواتير الآجلة</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4 mb-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon green ml-3"><i class="fas fa-money-bill-wave"></i></div>
                <div>
                    <div class="stat-value">{{ number_format((float) $summary->total_paid, 2) }} <small class="tx-13">ج.م</small></div>
                    <div class="stat-label">إجمالي المسدد من الفواتير الآجلة</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4 mb-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon orange ml-3"><i class="fas fa-hourglass-half"></i></div>
                <div>
                    <div class="stat-value">{{ number_format((float) $summary->total_remaining, 2) }} <small class="tx-13">ج.م</small></div>
                    <div class="stat-label">إجمالي المتبقي</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============ Suppliers table ============ --}}
<div class="row row-sm">
    <div class="col-xl-12">
        <div class="card suppliers-card">
            <div class="card-header pb-0 bg-transparent">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title mg-b-0">جدول موردين الأجل</h4>
                </div>
                <p class="tx-12 tx-gray-500 mb-2 mt-1">
                    ابحث بالاسم أو الكود أو رقم الهاتف أو العنوان. لتعديل بيانات مورد اضغط على زر
                    <span class="badge badge-primary"><i class="fas fa-edit"></i> تعديل</span>
                </p>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table text-md-nowrap" id="suppliersTable">
                        <thead>
                            <tr>
                                <th>المورد</th>
                                <th>رقم الهاتف</th>
                                <th class="text-center">عدد الفواتير</th>
                                <th>إجمالي مبلغ الفواتير</th>
                                <th>إجمالي المسدد</th>
                                <th>المتبقي</th>
                                <th>طريقة السداد</th>
                                <th>آخر تحديث لبيانات الفاتورة</th>
                                <th class="text-center no-sort">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($results as $supplier)
                                @php [$pmLabel, $pmClass] = $pm($supplier->payment_method); @endphp
                                <tr>
                                    {{-- Supplier --}}
                                    <td data-order="{{ $supplier->name }}" data-search="{{ $supplier->id }} {{ $supplier->name }} {{ $supplier->address }}">
                                        <div class="supplier-cell">
                                            <span class="avatar-initial">{{ mb_substr(trim($supplier->name), 0, 1) }}</span>
                                            <div>
                                                <p class="supplier-name">{{ $supplier->name }}</p>
                                                <span class="supplier-meta">
                                                    كود: #{{ $supplier->id }}
                                                    @if (!empty($supplier->address))
                                                        · <i class="fas fa-map-marker-alt"></i> {{ $supplier->address }}
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Phone --}}
                                    <td>
                                        <a href="tel:{{ $supplier->contact_info }}" class="phone-link" title="اتصال">
                                            <i class="fas fa-phone-alt tx-12 ml-1"></i>{{ $supplier->contact_info }}
                                        </a>
                                    </td>

                                    {{-- Invoice count --}}
                                    <td class="text-center" data-order="{{ (int) $supplier->count_invoices }}">
                                        <span class="badge-soft {{ $supplier->count_invoices > 0 ? 'info' : 'muted' }}">{{ $supplier->count_invoices }}</span>
                                    </td>

                                    {{-- Total --}}
                                    <td data-order="{{ (float) $supplier->total_all_invoices }}">
                                        <span class="amount {{ $supplier->total_all_invoices > 0 ? '' : 'zero' }}">
                                            {{ number_format((float) $supplier->total_all_invoices, 2) }} ج.م
                                        </span>
                                    </td>

                                    {{-- Paid and outstanding totals --}}
                                    <td data-order="{{ (float) $supplier->total_paid }}">
                                        <span class="amount {{ $supplier->total_paid > 0 ? '' : 'zero' }}">
                                            {{ number_format((float) $supplier->total_paid, 2) }} ج.م
                                        </span>
                                    </td>
                                    <td data-order="{{ (float) $supplier->total_remaining }}">
                                        <span class="amount {{ $supplier->total_remaining > 0 ? '' : 'zero' }}">
                                            {{ number_format((float) $supplier->total_remaining, 2) }} ج.م
                                        </span>
                                    </td>

                                    {{-- Payment method --}}
                                    <td>
                                        @if ($pmLabel)
                                            <span class="badge-soft {{ $pmClass }}">{{ $pmLabel }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    {{-- Updated at --}}
                                    <td data-order="{{ $supplier->last_invoice_activity_at }}">
                                        <span class="updated-at">
                                            <i class="far fa-clock ml-1"></i>{{ $supplier->last_invoice_activity_at > '1970-01-01 00:00:00' ? \Carbon\Carbon::parse($supplier->last_invoice_activity_at)->format('Y-m-d H:i') : '—' }}
                                        </span>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="text-center">
                                        <div class="action-group btn-group" role="group">
                                            <a href="{{ route('showForAllSuppliers', ['supplier_id' => encrypt($supplier->id)]) }}"
                                               class="btn btn-info" title="تفاصيل المورد" data-tooltip="true">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-primary" data-toggle="modal"
                                                    data-target="#editSuppliers{{ $supplier->id }}" title="تعديل" data-tooltip="true">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger" data-toggle="modal"
                                                    data-target="#deleteSuppliers{{ $supplier->id }}" title="مسح" data-tooltip="true">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============ Modals (outside the table for valid HTML) ============ --}}
@foreach ($results as $supplier)

    <!-- Edit supplier modal -->
    <div class="modal fade" id="editSuppliers{{ $supplier->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST" class="supplier-edit-form" novalidate>
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-edit ml-1 text-primary"></i> تعديل بيانات المورد</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="إغلاق"><span aria-hidden="true">&times;</span></button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label>كود المورد</label>
                            <input type="text" class="form-control" name="id" value="{{ $supplier->id }}" readonly>
                        </div>

                        <div class="form-group">
                            <label>اسم المورد <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" value="{{ $supplier->name }}"
                                   required maxlength="100" autocomplete="off" placeholder="اسم المورد">
                            <div class="field-error">الاسم يجب أن يحتوي على حروف عربية أو إنجليزية أو أرقام أو مسافات أو (-).</div>
                        </div>

                        <div class="form-group">
                            <label>عنوان المورد <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="address" value="{{ $supplier->address }}"
                                   required maxlength="255" autocomplete="off" placeholder="عنوان المورد">
                            <div class="field-error">من فضلك أدخل عنوان المورد.</div>
                        </div>

                        <div class="form-group mb-0">
                            <label>رقم الموبايل <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control text-left" dir="ltr" name="contact_info" value="{{ $supplier->contact_info }}"
                                   required inputmode="numeric" maxlength="11" autocomplete="off" placeholder="01XXXXXXXXX">
                            <div class="field-error">رقم الهاتف يجب أن يبدأ بـ 01 ويتكون من 11 رقمًا.</div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ</button>
                        <button type="button" class="btn btn-light" data-dismiss="modal">إغلاق</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete supplier modal -->
    <div class="modal fade" id="deleteSuppliers{{ $supplier->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" class="supplier-delete-form">
                @csrf
                @method('DELETE')
                <div class="modal-content">
                    <div class="modal-body text-center pt-4">
                        <div class="delete-icon"><i class="fas fa-exclamation-triangle"></i></div>
                        <h5 class="font-weight-bold">تأكيد حذف المورد</h5>
                        <p class="mb-1">هل أنت متأكد من حذف المورد <strong>{{ $supplier->name }}</strong>؟</p>
                        @if ($supplier->count_invoices > 0)
                            <div class="alert alert-warning py-2 mt-3 mb-0 tx-13">
                                هذا المورد لديه {{ $supplier->count_invoices }} فاتورة بإجمالي
                                <strong>{{ number_format((float) $supplier->total_all_invoices, 2) }} ج.م</strong>.
                                تأكد من تأثير الحذف قبل المتابعة.
                            </div>
                        @endif
                        <p class="text-muted tx-12 mt-2 mb-0">لا يمكن التراجع عن هذا الإجراء.</p>
                    </div>
                    <div class="modal-footer justify-content-center border-0 pt-0">
                        <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt ml-1"></i> نعم، احذف</button>
                        <button type="button" class="btn btn-light" data-dismiss="modal">إلغاء</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endforeach
@endsection

@section('js')
<!-- Internal Data tables -->
<script src="{{ URL::asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('assets/plugins/datatable/js/dataTables.bootstrap4.js') }}"></script>
<script src="{{ URL::asset('assets/plugins/datatable/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ URL::asset('assets/plugins/datatable/js/responsive.bootstrap4.min.js') }}"></script>

<script>
$(function () {

    /* ---------- DataTable (Arabic, searchable, sortable) ---------- */
    $('#suppliersTable').DataTable({
        responsive: true,
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        order: [[3, 'desc']],                       // biggest total first
        columnDefs: [{ targets: 'no-sort', orderable: false, searchable: false }],
        language: {
            search: '',
            searchPlaceholder: 'ابحث بالاسم أو الكود أو الهاتف...',
            lengthMenu: 'عرض _MENU_',
            info: 'عرض _START_ إلى _END_ من _TOTAL_ مورد',
            infoEmpty: 'لا يوجد موردين أجل',
            infoFiltered: '(تمت التصفية من _MAX_ مورد)',
            zeroRecords: 'لا توجد نتائج مطابقة للبحث',
            emptyTable: 'لا يوجد موردين أجل حتى الآن',
            paginate: { first: 'الأول', last: 'الأخير', next: 'التالي', previous: 'السابق' }
        }
    });

    $('[data-tooltip="true"]').tooltip({ trigger: 'hover' });

    /* ---------- Per-form validation (inline errors) ---------- */
    const nameRegex  = /^[\u0600-\u06FFa-zA-Z0-9\s\-]+$/;
    const phoneRegex = /^01[0-9]{9}$/;

    function check($input, valid) {
        $input.toggleClass('is-invalid', !valid);
        return valid;
    }

    function validateForm($form) {
        const $name    = $form.find('input[name="name"]');
        const $address = $form.find('input[name="address"]');
        const $phone   = $form.find('input[name="contact_info"]');
        const okName    = check($name,    nameRegex.test($name.val().trim()));
        const okAddress = check($address, $address.val().trim() !== '');
        const okPhone   = check($phone,   phoneRegex.test($phone.val().trim()));
        return okName && okAddress && okPhone;
    }

    // Digits only in phone (converts Arabic-Indic digits)
    $(document).on('input', '.supplier-edit-form input[name="contact_info"]', function () {
        const arabicDigits = '٠١٢٣٤٥٦٧٨٩';
        const v = this.value.replace(/[٠-٩]/g, d => arabicDigits.indexOf(d));
        this.value = v.replace(/\D/g, '').slice(0, 11);
    });

    $(document).on('input', '.supplier-edit-form input', function () {
        $(this).removeClass('is-invalid');
    });

    $('.supplier-edit-form').on('submit', function (e) {
        const $form = $(this);
        if (!validateForm($form)) {
            e.preventDefault();
            $form.find('.is-invalid').first().trigger('focus');
            return;
        }
        $form.find('button[type="submit"]').addClass('is-loading')
             .html('<span class="spinner-border spinner-border-sm ml-1"></span> جاري الحفظ...');
    });

    $('.supplier-delete-form').on('submit', function () {
        $(this).find('button[type="submit"]').addClass('is-loading')
               .html('<span class="spinner-border spinner-border-sm ml-1"></span> جاري الحذف...');
    });

    $('.modal').on('hidden.bs.modal', function () {
        $(this).find('.is-invalid').removeClass('is-invalid');
    });

    $('.modal').on('shown.bs.modal', function () {
        $(this).find('input[name="name"]').trigger('focus');
    });
});
</script>
@endsection
