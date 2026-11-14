@extends('layouts.master')

@section('title')
    معرض أيه - عملاء الأجل
@endsection

@section('css')
<!-- Internal Data table css -->
<link href="{{ URL::asset('assets/plugins/datatable/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet" />
<link href="{{ URL::asset('assets/plugins/datatable/css/buttons.bootstrap4.min.css') }}" rel="stylesheet">
<link href="{{ URL::asset('assets/plugins/datatable/css/responsive.bootstrap4.min.css') }}" rel="stylesheet" />

<style>
    /* ---------- Summary cards ---------- */
    .stat-card { border: 0; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.06); transition: transform .15s ease, box-shadow .15s ease; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,.09); }
    .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; }
    .stat-icon.blue   { background: #e8f0ff; color: #0162e8; }
    .stat-icon.red    { background: #fde8e8; color: #ec2e2e; }
    .stat-icon.orange { background: #fff1e0; color: #f7931e; }
    .stat-value { font-size: 24px; font-weight: 700; line-height: 1.2; }
    .stat-label { font-size: 13px; color: #7b8190; }

    /* ---------- Table ---------- */
    .customers-card { border: 0; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.06); }
    #creditTable thead th { background: #f6f7fb; color: #4b5161; font-weight: 600; font-size: 13px; border-bottom: 1px solid #e6e8ef; white-space: nowrap; }
    #creditTable tbody td { vertical-align: middle; }
    #creditTable tbody tr { transition: background .12s ease; }
    #creditTable tbody tr:hover { background: #f9faff; }

    .customer-cell { display: flex; align-items: center; gap: 10px; }
    .avatar-initial { width: 38px; height: 38px; border-radius: 50%; background: #fff1e0; color: #f7931e; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0; }
    .customer-name { font-weight: 600; color: #1f2430; margin: 0; }
    .customer-code { font-size: 12px; color: #9aa0ae; }

    .phone-link { direction: ltr; unicode-bidi: embed; display: inline-block; color: #4b5161; }
    .phone-link:hover { color: #0162e8; text-decoration: none; }

    .badge-soft { padding: 5px 10px; border-radius: 20px; font-weight: 600; font-size: 12px; display: inline-block; }
    .badge-soft.info   { background: #e8f0ff; color: #0162e8; }
    .badge-soft.muted  { background: #f0f1f5; color: #8a90a0; }
    .badge-soft.method { background: #f1eaff; color: #6f42c1; }

    .debt-amount { font-weight: 700; color: #ec2e2e; white-space: nowrap; }
    .debt-amount.zero { color: #09ad95; font-weight: 600; }

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
            <h4 class="content-title mb-0 my-auto">العملاء</h4>
            <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ عملاء الأجل</span>
        </div>
    </div>
</div>
<!-- breadcrumb -->
@endsection

@section('content')

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
                <div class="stat-icon blue ml-3"><i class="fas fa-user-clock"></i></div>
                <div>
                    <div class="stat-value">{{ number_format($results->count()) }}</div>
                    <div class="stat-label">عدد عملاء الأجل</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4 mb-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon orange ml-3"><i class="fas fa-file-invoice-dollar"></i></div>
                <div>
                    <div class="stat-value">{{ number_format($results->sum('count_invoices')) }}</div>
                    <div class="stat-label">فواتير الأجل المستحقة</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4 mb-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon red ml-3"><i class="fas fa-hand-holding-usd"></i></div>
                <div>
                    <div class="stat-value">{{ number_format($results->sum('total_invoices'), 2) }} <small class="tx-13">ج.م</small></div>
                    <div class="stat-label">إجمالي المديونية الحالية</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============ Credit customers table ============ --}}
<div class="row row-sm">
    <div class="col-xl-12">
        <div class="card customers-card">
            <div class="card-header pb-0 bg-transparent">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title mg-b-0">جدول عملاء الأجل</h4>
                </div>
                <p class="tx-12 tx-gray-500 mb-2 mt-1">
                    ابحث بالاسم أو الكود أو رقم الهاتف. لتعديل بيانات عميل اضغط على زر
                    <span class="badge badge-primary"><i class="fas fa-edit"></i> تعديل</span>
                </p>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table text-md-nowrap" id="creditTable">
                        <thead>
                            <tr>
                                <th>العميل</th>
                                <th>رقم الهاتف</th>
                                <th class="text-center">فواتير الأجل المستحقة</th>
                                <th>إجمالي المديونية الحالية</th>
                                <th>طريقة السداد</th>
                                <th>آخر تعديل</th>
                                <th class="text-center no-sort">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($results as $customer)
                                <tr>
                                    {{-- Customer --}}
                                    <td data-order="{{ $customer->name }}" data-search="{{ $customer->id }} {{ $customer->name }}">
                                        <div class="customer-cell">
                                            <span class="avatar-initial">{{ mb_substr(trim($customer->name), 0, 1) }}</span>
                                            <div>
                                                <p class="customer-name">{{ $customer->name }}</p>
                                                <span class="customer-code">كود: #{{ $customer->id }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Phone --}}
                                    <td>
                                        <a href="tel:{{ $customer->contact_info }}" class="phone-link" title="اتصال">
                                            <i class="fas fa-phone-alt tx-12 ml-1"></i>{{ $customer->contact_info }}
                                        </a>
                                    </td>

                                    {{-- Due invoices --}}
                                    <td class="text-center" data-order="{{ (int) $customer->count_invoices }}">
                                        <span class="badge-soft {{ $customer->count_invoices > 0 ? 'info' : 'muted' }}">
                                            {{ $customer->count_invoices }}
                                        </span>
                                    </td>

                                    {{-- Current debt --}}
                                    <td data-order="{{ (float) $customer->total_invoices }}">
                                        <span class="debt-amount {{ $customer->total_invoices > 0 ? '' : 'zero' }}">
                                            {{ number_format($customer->total_invoices, 2) }} ج.م
                                        </span>
                                    </td>

                                    {{-- Payment method --}}
                                    <td>
                                        @if ($customer->payment_method)
                                            <span class="badge-soft method">{{ $customer->payment_method }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    {{-- Updated at --}}
                                    <td data-order="{{ optional($customer->updated_at)->timestamp }}">
                                        <span class="updated-at">
                                            <i class="far fa-clock ml-1"></i>{{ optional($customer->updated_at)->format('Y-m-d H:i') }}
                                        </span>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="text-center">
                                        <div class="action-group btn-group" role="group">
                                            <a href="{{ route('customer_profile', ['id' => encrypt($customer->id)]) }}"
                                               class="btn btn-info" data-toggle="tooltip" title="تفاصيل العميل">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-primary" data-toggle="modal"
                                                    data-target="#editCustomers{{ $customer->id }}" title="تعديل" data-tooltip="true">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger" data-toggle="modal"
                                                    data-target="#deleteCustomers{{ $customer->id }}" title="مسح" data-tooltip="true">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-5">لا توجد مديونيات عملاء مستحقة حاليًا.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============ Modals (outside the table for valid HTML) ============ --}}
@foreach ($results as $customer)

    <!-- Edit customer modal -->
    <div class="modal fade" id="editCustomers{{ $customer->id }}" tabindex="-1" role="dialog" aria-labelledby="editTitle{{ $customer->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form action="{{ route('customers.update', $customer->id) }}" method="POST" class="customer-edit-form" novalidate>
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editTitle{{ $customer->id }}">
                            <i class="fas fa-user-edit ml-1 text-primary"></i> تعديل البيانات الأساسية للعميل
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="إغلاق"><span aria-hidden="true">&times;</span></button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label>كود العميل</label>
                            <input type="text" class="form-control" name="id" value="{{ $customer->id }}" readonly>
                        </div>

                        <div class="form-group">
                            <label>اسم العميل <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" value="{{ $customer->name }}"
                                   required maxlength="100" autocomplete="off" placeholder="مثال: أحمد محمد">
                            <div class="field-error">
                                الاسم يجب أن يحتوي على حروف عربية أو إنجليزية أو أرقام أو مسافات أو (-).
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label>رقم الهاتف <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control text-left" dir="ltr" name="phone" value="{{ $customer->contact_info }}"
                                   required inputmode="numeric" maxlength="11" autocomplete="off" placeholder="01XXXXXXXXX">
                            <div class="field-error">
                                رقم الهاتف يجب أن يبدأ بـ 01 ويتكون من 11 رقمًا.
                            </div>
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

    <!-- Delete customer modal -->
    <div class="modal fade" id="deleteCustomers{{ $customer->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="customer-delete-form">
                @csrf
                @method('DELETE')
                <div class="modal-content">
                    <div class="modal-body text-center pt-4">
                        <div class="delete-icon"><i class="fas fa-exclamation-triangle"></i></div>
                        <h5 class="font-weight-bold">تأكيد حذف العميل</h5>
                        <p class="mb-1">هل أنت متأكد من حذف العميل <strong>{{ $customer->name }}</strong>؟</p>
                        @if ($customer->total_invoices > 0)
                            <div class="alert alert-warning py-2 mt-3 mb-0 tx-13">
                                تنبيه: على هذا العميل مديونية حالية قدرها
                                <strong>{{ number_format($customer->total_invoices, 2) }} ج.م</strong>
                                ({{ $customer->count_invoices }} فاتورة مستحقة).
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
    $('#creditTable').DataTable({
        responsive: true,
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        order: [[3, 'desc']],                       // highest debt first
        columnDefs: [{ targets: 'no-sort', orderable: false, searchable: false }],
        language: {
            search: '',
            searchPlaceholder: 'ابحث بالاسم أو الكود أو الهاتف...',
            lengthMenu: 'عرض _MENU_',
            info: 'عرض _START_ إلى _END_ من _TOTAL_ عميل',
            infoEmpty: 'لا يوجد عملاء أجل',
            infoFiltered: '(تمت التصفية من _MAX_ عميل)',
            zeroRecords: 'لا توجد نتائج مطابقة للبحث',
            emptyTable: 'لا يوجد عملاء أجل حتى الآن',
            paginate: { first: 'الأول', last: 'الأخير', next: 'التالي', previous: 'السابق' }
        }
    });

    $('[data-toggle="tooltip"], [data-tooltip="true"]').tooltip({ trigger: 'hover' });

    /* ---------- Per-form validation (inline errors) ---------- */
    const nameRegex  = /^[\u0600-\u06FFa-zA-Z0-9\s\-]+$/;
    const phoneRegex = /^01[0-9]{9}$/;

    function check($input, valid) {
        $input.toggleClass('is-invalid', !valid);
        return valid;
    }

    function validateForm($form) {
        const $name  = $form.find('input[name="name"]');
        const $phone = $form.find('input[name="phone"]');
        const okName  = check($name,  nameRegex.test($name.val().trim()));
        const okPhone = check($phone, phoneRegex.test($phone.val().trim()));
        return okName && okPhone;
    }

    // Digits only in phone (converts Arabic-Indic digits)
    $(document).on('input', '.customer-edit-form input[name="phone"]', function () {
        const arabicDigits = '٠١٢٣٤٥٦٧٨٩';
        const v = this.value.replace(/[٠-٩]/g, d => arabicDigits.indexOf(d));
        this.value = v.replace(/\D/g, '').slice(0, 11);
    });

    $(document).on('input', '.customer-edit-form input', function () {
        $(this).removeClass('is-invalid');
    });

    $('.customer-edit-form').on('submit', function (e) {
        const $form = $(this);
        if (!validateForm($form)) {
            e.preventDefault();
            $form.find('.is-invalid').first().trigger('focus');
            return;
        }
        $form.find('button[type="submit"]')
             .addClass('is-loading')
             .html('<span class="spinner-border spinner-border-sm ml-1"></span> جاري الحفظ...');
    });

    $('.customer-delete-form').on('submit', function () {
        $(this).find('button[type="submit"]')
               .addClass('is-loading')
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
