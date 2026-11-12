@extends('layouts.master')

@section('title')
    معرض أيه - الموردين
@endsection

@section('css')
<!--- Internal Select2 css-->
<link href="{{ URL::asset('assets/plugins/select2/css/select2.min.css') }}" rel="stylesheet">
<link href="{{ URL::asset('assets/plugins/datatable/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet">

<style>
    /* ---------- Page header / toolbar ---------- */
    .sup-toolbar { display:flex; flex-wrap:wrap; gap:12px; align-items:center; justify-content:space-between; }
    .sup-toolbar .sup-title { margin:0; font-weight:700; }
    .sup-toolbar .sup-sub { margin:2px 0 0; color:#8a93a6; font-size:13px; }
    .sup-stat {
        display:inline-flex; align-items:center; gap:8px; padding:6px 14px;
        background:#f1f4fb; border-radius:50px; font-size:13px; color:#4a5568;
    }
    .sup-stat b { color:#0162e8; font-size:15px; }

    /* ---------- Table ---------- */
    #suppliersTable { border-collapse:separate; border-spacing:0; width:100% !important; }
    #suppliersTable thead th {
        background:#f7f9fc; color:#5b6477; font-weight:600; font-size:13px;
        border-top:0; border-bottom:1px solid #e6eaf2; white-space:nowrap;
    }
    #suppliersTable tbody td { vertical-align:middle; border-top:1px solid #f0f2f7; font-size:14px; }
    #suppliersTable tbody tr:hover { background:#fafbff; }

    .sup-avatar {
        width:40px; height:40px; border-radius:50%; flex:0 0 40px;
        display:flex; align-items:center; justify-content:center;
        background:linear-gradient(135deg,#0162e8,#4a90ff); color:#fff; font-weight:700; font-size:16px;
    }
    .sup-name { font-weight:600; color:#1f2a44; }
    .sup-id   { font-size:12px; color:#9aa3b5; }
    .sup-phone { direction:ltr; unicode-bidi:embed; display:inline-block; color:#1f2a44; }
    .sup-phone:hover { color:#0162e8; text-decoration:none; }
    .sup-date { font-size:13px; color:#6b7385; white-space:nowrap; }
    .sup-badge {
        display:inline-block; background:#eaf2ff; color:#0162e8; border-radius:50px;
        padding:2px 10px; font-size:12px; margin:1px 0 1px 3px;
    }
    .sup-badge.muted { background:#f1f2f6; color:#8a93a6; }

    .sup-actions { white-space:nowrap; }
    .sup-actions .btn { padding:5px 10px; font-size:13px; }

    /* ---------- Empty state ---------- */
    .sup-empty { text-align:center; padding:50px 10px; color:#8a93a6; }
    .sup-empty i { font-size:48px; display:block; margin-bottom:10px; opacity:.5; }

    /* ---------- Modals ---------- */
    .modal-content { border:0; border-radius:12px; overflow:hidden; box-shadow:0 20px 50px rgba(20,30,60,.2); }
    .modal-header { background:#f7f9fc; border-bottom:1px solid #e6eaf2; }
    .modal-header .modal-title { font-weight:700; }
    .modal-footer { background:#fafbfd; border-top:1px solid #eef0f6; }
    .modal-body .form-group label { font-weight:600; font-size:13px; color:#3b4560; }
    .modal-body .form-control:focus { box-shadow:0 0 0 3px rgba(1,98,232,.12); border-color:#0162e8; }
    .modal-body .form-control.is-invalid { border-color:#dc3545; }
    .field-error { color:#dc3545; font-size:12px; margin-top:4px; }
    .field-hint  { color:#9aa3b5; font-size:12px; margin-top:4px; }
    .select2-container { width:100% !important; }

    .delete-icon {
        width:64px; height:64px; margin:0 auto 12px; border-radius:50%;
        background:#fdecee; color:#dc3545; display:flex; align-items:center; justify-content:center; font-size:30px;
    }
    .btn.is-loading { pointer-events:none; opacity:.75; }

    @media (max-width:576px) {
        .sup-toolbar .btn { width:100%; }
    }
</style>
@endsection

@section('page-header')
<!-- breadcrumb -->
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto">المشتريات</h4>
            <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ الموردين</span>
        </div>
    </div>
</div>
<!-- breadcrumb -->
@endsection

@section('content')

@php
    // id => name lookup used for the category badges
    $categoryMap = $categories->pluck('category_name', 'category_id');
    $reopenModal = $errors->any() ? old('_form') : null;
@endphp

<!-- Flash messages -->
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle ml-1"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="إغلاق"><span aria-hidden="true">&times;</span></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>يرجى تصحيح الأخطاء التالية:</strong>
        <ul class="mb-0 pr-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="إغلاق"><span aria-hidden="true">&times;</span></button>
    </div>
@endif

<div class="col-xl-12">
    <div class="card">

        <div class="card-header pb-3">
            <div class="sup-toolbar">
                <div>
                    <h4 class="card-title sup-title">جدول الموردين</h4>
                    <p class="sup-sub">عرض جميع الموردين — استخدم أزرار الإجراءات لعرض التفاصيل أو التعديل أو الحذف.</p>
                </div>
                <div class="d-flex align-items-center flex-wrap" style="gap:10px;">
                    <span class="sup-stat">إجمالي الموردين <b>{{ $suppliers->count() }}</b></span>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addSupplier">
                        <i class="fas fa-plus ml-1"></i> إضافة مورد جديد
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table text-md-nowrap" id="suppliersTable">
                    <thead>
                        <tr>
                            <th>المورد</th>
                            <th>رقم الموبايل</th>
                            <th>العنوان</th>
                            <th>التصنيفات</th>
                            <th>تاريخ التسجيل</th>
                            <th>آخر تعديل</th>
                            <th class="text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($suppliers as $supplier)
                            @php
                                $cats = $selectedCategories[$supplier->id] ?? [];
                                $createdAt = $supplier->created_at ? \Illuminate\Support\Carbon::parse($supplier->created_at) : null;
                                $updatedAt = $supplier->updated_at ? \Illuminate\Support\Carbon::parse($supplier->updated_at) : null;
                            @endphp
                            <tr>
                                <td data-order="{{ $supplier->id }}" data-search="{{ $supplier->id }} {{ $supplier->name }}">
                                    <div class="d-flex align-items-center" style="gap:10px;">
                                        <div class="sup-avatar">{{ mb_substr($supplier->name, 0, 1) }}</div>
                                        <div>
                                            <div class="sup-name">{{ $supplier->name }}</div>
                                            <div class="sup-id">كود: #{{ $supplier->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a class="sup-phone" href="tel:{{ $supplier->contact_info }}">{{ $supplier->contact_info }}</a>
                                </td>
                                <td>{{ $supplier->address }}</td>
                                <td>
                                    @forelse ($cats as $catId)
                                        @if (isset($categoryMap[$catId]))
                                            <span class="sup-badge">{{ $categoryMap[$catId] }}</span>
                                        @endif
                                    @empty
                                        <span class="sup-badge muted">بدون تصنيف</span>
                                    @endforelse
                                </td>
                                <td class="sup-date" data-order="{{ $createdAt ? $createdAt->timestamp : '' }}">
                                    {{ $createdAt ? $createdAt->format('Y-m-d H:i') : '—' }}
                                </td>
                                <td class="sup-date" data-order="{{ $updatedAt ? $updatedAt->timestamp : '' }}">
                                    {{ $updatedAt ? $updatedAt->format('Y-m-d H:i') : '—' }}
                                </td>
                                <td class="sup-actions text-center">
                                    <a href="{{ route('showForAllSuppliers', ['supplier_id' => encrypt($supplier->id)]) }}"
                                       class="btn btn-outline-info" title="تفاصيل المورد">
                                        <i class="fas fa-eye"></i> تفاصيل
                                    </a>
                                    <button type="button" class="btn btn-outline-primary" data-toggle="modal"
                                            data-target="#editSupplierModal{{ $supplier->id }}" title="تعديل">
                                        <i class="fas fa-edit"></i> تعديل
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" data-toggle="modal"
                                            data-target="#deleteSupplierModal{{ $supplier->id }}" title="حذف">
                                        <i class="fas fa-trash"></i> حذف
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ===================== Modals (outside the table for valid HTML) ===================== --}}

@foreach ($suppliers as $supplier)
    <!-- Edit Supplier Modal -->
    <div class="modal fade" id="editSupplierModal{{ $supplier->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST" data-validate novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="_form" value="edit{{ $supplier->id }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">تعديل بيانات المورد</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="إغلاق"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>كود المورد</label>
                            <input type="text" class="form-control" value="{{ $supplier->id }}" readonly disabled>
                        </div>
                        <div class="form-group">
                            <label for="editName{{ $supplier->id }}">اسم المورد</label>
                            <input type="text" class="form-control" name="name" id="editName{{ $supplier->id }}"
                                   value="{{ old('_form') === 'edit'.$supplier->id ? old('name') : $supplier->name }}" required>
                        </div>
                        <div class="form-group">
                            <label for="editAddress{{ $supplier->id }}">عنوان المورد</label>
                            <input type="text" class="form-control" name="address" id="editAddress{{ $supplier->id }}"
                                   value="{{ old('_form') === 'edit'.$supplier->id ? old('address') : $supplier->address }}" required>
                        </div>
                        <div class="form-group">
                            <label for="editPhone{{ $supplier->id }}">رقم الموبايل</label>
                            <input type="tel" inputmode="numeric" maxlength="11" dir="ltr" class="form-control text-right"
                                   name="contact_info" id="editPhone{{ $supplier->id }}"
                                   value="{{ old('_form') === 'edit'.$supplier->id ? old('contact_info') : $supplier->contact_info }}" required>
                            <div class="field-hint">11 رقمًا ويبدأ بـ 01</div>
                        </div>
                        <div class="form-group">
                            <label for="editCategory{{ $supplier->id }}">التصنيف الخاص بالمورد</label>
                            <select class="form-control js-category-select" name="supplier_category[]"
                                    id="editCategory{{ $supplier->id }}" multiple required
                                    data-placeholder="اختر تصنيفًا أو أكثر">
                                @foreach ($categories as $category)
                                    <option value="{{ $category->category_id }}"
                                        @if (in_array($category->category_id, $selectedCategories[$supplier->id] ?? [])) selected @endif>
                                        {{ $category->category_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary js-submit">حفظ التعديلات</button>
                        <button type="button" class="btn btn-light" data-dismiss="modal">إلغاء</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Supplier Modal -->
    <div class="modal fade" id="deleteSupplierModal{{ $supplier->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-content">
                    <div class="modal-body text-center pt-4">
                        <div class="delete-icon"><i class="fas fa-exclamation-triangle"></i></div>
                        <h5 class="mb-2">تأكيد الحذف</h5>
                        <p class="text-muted mb-0">
                            هل أنت متأكد من حذف المورد <b>{{ $supplier->name }}</b>؟<br>
                            لا يمكن التراجع عن هذا الإجراء.
                        </p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="submit" class="btn btn-danger js-submit">نعم، احذف</button>
                        <button type="button" class="btn btn-light" data-dismiss="modal">إلغاء</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endforeach

<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplier" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form action="{{ route('suppliers.store') }}" method="POST" data-validate novalidate>
            @csrf
            <input type="hidden" name="_form" value="add">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إضافة مورد جديد</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="إغلاق"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="supplierName">اسم المورد</label>
                        <input type="text" class="form-control" name="name" id="supplierName"
                               placeholder="أدخل اسم المورد" value="{{ old('_form') === 'add' ? old('name') : '' }}" required>
                    </div>
                    <div class="form-group">
                        <label for="supplierAddress">عنوان المورد</label>
                        <input type="text" class="form-control" name="address" id="supplierAddress"
                               placeholder="أدخل عنوان المورد" value="{{ old('_form') === 'add' ? old('address') : '' }}" required>
                    </div>
                    <div class="form-group">
                        <label for="supplierPhone">رقم الموبايل</label>
                        <input type="tel" inputmode="numeric" maxlength="11" dir="ltr" class="form-control text-right"
                               name="contact_info" id="supplierPhone" placeholder="01XXXXXXXXX"
                               value="{{ old('_form') === 'add' ? old('contact_info') : '' }}" required>
                        <div class="field-hint">11 رقمًا ويبدأ بـ 01</div>
                    </div>
                    <div class="form-group">
                        <label for="addCategory">التصنيف الخاص بالمورد</label>
                        <select class="form-control js-category-select" name="supplier_category[]" id="addCategory"
                                multiple required data-placeholder="اختر تصنيفًا أو أكثر">
                            @foreach ($categories as $category)
                                <option value="{{ $category->category_id }}"
                                    @if (old('_form') === 'add' && in_array($category->category_id, old('supplier_category', []))) selected @endif>
                                    {{ $category->category_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary js-submit">حفظ</button>
                    <button type="button" class="btn btn-light" data-dismiss="modal">إلغاء</button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- End Modals -->

@endsection

@section('js')
<script src="{{ URL::asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ URL::asset('assets/plugins/datatable/js/jquery.dataTables.js') }}"></script>
<script src="{{ URL::asset('assets/plugins/datatable/js/dataTables.bootstrap4.js') }}"></script>
<script src="{{ URL::asset('assets/plugins/datatable/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ URL::asset('assets/plugins/datatable/js/responsive.bootstrap4.min.js') }}"></script>

<script>
$(function () {

    /* ---------- DataTable (Arabic, search + paging) ---------- */
    $('#suppliersTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        columnDefs: [{ orderable: false, targets: 6 }],
        language: {
            search: '',
            searchPlaceholder: 'ابحث بالاسم أو الموبايل أو العنوان...',
            lengthMenu: 'عرض _MENU_',
            info: 'عرض _START_ إلى _END_ من أصل _TOTAL_ مورد',
            infoEmpty: 'لا توجد بيانات',
            infoFiltered: '(تمت التصفية من _MAX_ مورد)',
            zeroRecords: 'لا يوجد موردون مطابقون لبحثك',
            emptyTable: 'لا يوجد موردون حتى الآن — ابدأ بإضافة مورد جديد',
            paginate: { first: 'الأول', last: 'الأخير', next: 'التالي', previous: 'السابق' }
        }
    });

    /* ---------- Select2 inside modals ---------- */
    $('.js-category-select').each(function () {
        var $el = $(this);
        $el.select2({
            dir: 'rtl',
            width: '100%',
            placeholder: $el.data('placeholder'),
            dropdownParent: $el.closest('.modal')
        });
    });

    /* ---------- Reopen the modal that failed server-side validation ---------- */
    @if ($reopenModal === 'add')
        $('#addSupplier').modal('show');
    @elseif ($reopenModal && \Illuminate\Support\Str::startsWith($reopenModal, 'edit'))
        $('#editSupplierModal{{ \Illuminate\Support\Str::after($reopenModal, 'edit') }}').modal('show');
    @endif

    /* ---------- Digits only in phone fields ---------- */
    $(document).on('input', 'input[name="contact_info"]', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 11);
    });

    /* ---------- Client-side validation (only forms marked data-validate) ---------- */
    var textRegex  = /^[\u0600-\u06FFa-zA-Z0-9\s.,\-_\/\\()،؛:#]+$/; // letters, digits, spaces + common address punctuation
    var phoneRegex = /^01[0-9]{9}$/;

    function showError(form, field, message) {
        var $input = $(form).find('[name="' + field + '"]');
        $input.addClass('is-invalid');
        $input.closest('.form-group').append('<div class="field-error">' + message + '</div>');
    }

    function clearErrors(form) {
        $(form).find('.field-error').remove();
        $(form).find('.is-invalid').removeClass('is-invalid');
    }

    $('form[data-validate]').on('submit', function (e) {
        var form = this, valid = true;
        clearErrors(form);

        var name    = $.trim($(form).find('[name="name"]').val());
        var address = $.trim($(form).find('[name="address"]').val());
        var phone   = $.trim($(form).find('[name="contact_info"]').val());
        var cats    = $(form).find('[name="supplier_category[]"]').val() || [];

        if (!name || !textRegex.test(name)) {
            valid = false;
            showError(form, 'name', 'اسم المورد يجب أن يحتوي على حروف عربية أو إنجليزية أو أرقام فقط');
        }
        if (!address || !textRegex.test(address)) {
            valid = false;
            showError(form, 'address', 'العنوان يحتوي على رموز غير مسموحة');
        }
        if (!phoneRegex.test(phone)) {
            valid = false;
            showError(form, 'contact_info', 'رقم الهاتف يجب أن يبدأ بـ 01 ويتكون من 11 رقمًا');
        }
        if (cats.length === 0) {
            valid = false;
            $(form).find('[name="supplier_category[]"]').closest('.form-group')
                   .append('<div class="field-error">اختر تصنيفًا واحدًا على الأقل</div>');
        }

        if (!valid) {
            e.preventDefault();
            e.stopImmediatePropagation();
            $(form).find('.is-invalid').first().focus();
        }
    });

    /* ---------- Prevent double submit + loading state ---------- */
    $('form').on('submit', function (e) {
        if (e.isDefaultPrevented()) return;
        var $btn = $(this).find('.js-submit');
        $btn.addClass('is-loading').prop('disabled', true)
            .prepend('<span class="spinner-border spinner-border-sm ml-1" role="status"></span> ');
    });

    /* ---------- Reset form errors when a modal closes ---------- */
    $('.modal').on('hidden.bs.modal', function () {
        clearErrors($(this).find('form')[0]);
    });

    /* ---------- Auto-dismiss success alert ---------- */
    setTimeout(function () { $('.alert-success').fadeOut(400, function () { $(this).remove(); }); }, 5000);
});
</script>
@endsection
