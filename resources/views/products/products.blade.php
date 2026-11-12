@extends('layouts.master')

@section('title')
    معرض أيه - المنتجات
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
    .stat-value { font-size: 22px; font-weight: 700; line-height: 1.2; }
    .stat-label { font-size: 13px; color: #7b8190; }

    /* ---------- Card + table ---------- */
    .products-card { border: 0; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.06); }
    #productsTable thead th { background: #f6f7fb; color: #4b5161; font-weight: 600; font-size: 13px; border-bottom: 1px solid #e6e8ef; white-space: nowrap; }
    #productsTable tbody td { vertical-align: middle; }
    #productsTable tbody tr { transition: background .12s ease; }
    #productsTable tbody tr:hover { background: #f9faff; }

    .product-cell { display: flex; align-items: center; gap: 12px; }
    .product-thumb { width: 56px; height: 56px; border-radius: 12px; object-fit: cover; background: #f0f1f5; border: 1px solid #e6e8ef; flex-shrink: 0; }
    .product-name { font-weight: 600; color: #1f2430; margin: 0; }
    .product-code { font-size: 12px; color: #9aa0ae; }

    .badge-soft { padding: 5px 10px; border-radius: 20px; font-weight: 600; font-size: 12px; display: inline-block; white-space: nowrap; }
    .badge-soft.category { background: #f1eaff; color: #6f42c1; }
    .badge-soft.ok  { background: #e3f8ee; color: #09ad95; }
    .badge-soft.low { background: #fff1e0; color: #d97706; }
    .badge-soft.out { background: #fde8e8; color: #ec2e2e; }

    .price { font-weight: 700; color: #1f2430; white-space: nowrap; }
    .date-text { font-size: 12px; color: #8a90a0; white-space: nowrap; }
    .action-group .btn { padding: 5px 9px; border-radius: 8px; }

    .filters-bar { background: #f9faff; border: 1px solid #eef0f6; border-radius: 10px; padding: 12px 12px 0; margin-bottom: 16px; }

    /* ---------- Modals / forms ---------- */
    .modal-content { border: 0; border-radius: 14px; overflow: hidden; }
    .modal-header { background: #f6f7fb; border-bottom: 1px solid #e6e8ef; }
    .modal-title { font-weight: 700; }
    .field-error { color: #ec2e2e; font-size: 12px; margin-top: 4px; display: none; }
    .form-control.is-invalid ~ .field-error { display: block; }
    .image-preview { display: flex; align-items: center; gap: 12px; margin-bottom: 8px; }
    .image-preview img { width: 84px; height: 84px; object-fit: cover; border-radius: 12px; border: 1px solid #e6e8ef; background: #f0f1f5; }
    .delete-icon { width: 64px; height: 64px; border-radius: 50%; background: #fde8e8; color: #ec2e2e; display: flex; align-items: center; justify-content: center; font-size: 30px; margin: 0 auto 12px; }
    .delete-product-thumb { width: 72px; height: 72px; border-radius: 12px; object-fit: cover; border: 1px solid #e6e8ef; }

    /* ---------- Barcode preview ---------- */
    .label-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
    .barcode-label { border: 1px dashed #c9ccd6; border-radius: 10px; padding: 8px 6px; text-align: center; background: #fff; overflow: hidden; }
    .barcode-label p { margin: 0 0 2px; font-size: 13px; line-height: 1.3; }
    .barcode-label svg { max-width: 100%; height: auto; }
    #barcodePreview { max-height: 55vh; overflow-y: auto; padding: 4px; }

    /* ---------- DataTables tweaks ---------- */
    .dataTables_filter input { border-radius: 8px; min-width: 240px; }
    .dataTables_length select { border-radius: 8px; }
    .btn.is-loading { pointer-events: none; opacity: .75; }

    @media (max-width: 576px) { .label-grid { grid-template-columns: repeat(2, 1fr); } }
</style>
@endsection

@section('page-header')
<!-- breadcrumb -->
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto">المنتجات</h4>
            <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ جميع المنتجات</span>
        </div>
    </div>
</div>
<!-- breadcrumb -->
@endsection

@section('content')

@php
    // ---- Data prepared once, so the markup stays clean ----
    $LOW_STOCK_THRESHOLD = 5; // quantity at or below this is flagged as "low"
    $categoryMap = collect($categories)->pluck('category_name', 'category_id');
    $placeholderImg = asset('images/products/item.png');

    $totalProducts  = $products->count();
    $totalUnits     = $products->sum(fn ($p) => (float) $p->quantity);
    $inventoryValue = $products->sum(fn ($p) => (float) $p->quantity * (float) $p->cost_price);
    $needAttention  = $products->filter(fn ($p) => (float) $p->quantity <= $LOW_STOCK_THRESHOLD)->count();

    // Clean rows for the Excel export (no images / buttons)
    $exportRows = $products->map(fn ($p) => [
        'كود المنتج'        => $p->id,
        'اسم المنتج'        => $p->name,
        'التصنيف'           => $categoryMap[$p->category] ?? '',
        'الكمية'            => (float) $p->quantity,
        'سعر التكلفة'       => (float) $p->cost_price,
        'سعر البيع'         => (float) $p->price,
        'تاريخ التسجيل'     => $p->created_at ? \Illuminate\Support\Carbon::parse($p->created_at)->format('Y-m-d H:i') : '',
        'تاريخ آخر تعديل'   => $p->updated_at ? \Illuminate\Support\Carbon::parse($p->updated_at)->format('Y-m-d H:i') : '',
    ])->values();
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
    <div class="col-6 col-lg-3 mb-3">
        <div class="card stat-card"><div class="card-body d-flex align-items-center">
            <div class="stat-icon blue ml-3"><i class="fas fa-box-open"></i></div>
            <div><div class="stat-value">{{ number_format($totalProducts) }}</div><div class="stat-label">عدد المنتجات</div></div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3 mb-3">
        <div class="card stat-card"><div class="card-body d-flex align-items-center">
            <div class="stat-icon green ml-3"><i class="fas fa-cubes"></i></div>
            <div><div class="stat-value">{{ number_format($totalUnits) }}</div><div class="stat-label">إجمالي القطع بالمخزون</div></div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3 mb-3">
        <div class="card stat-card"><div class="card-body d-flex align-items-center">
            <div class="stat-icon purple ml-3"><i class="fas fa-coins"></i></div>
            <div><div class="stat-value">{{ number_format($inventoryValue) }} <small class="tx-12">ج.م</small></div><div class="stat-label">قيمة المخزون (بسعر التكلفة)</div></div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3 mb-3">
        <div class="card stat-card"><div class="card-body d-flex align-items-center">
            <div class="stat-icon orange ml-3"><i class="fas fa-exclamation-triangle"></i></div>
            <div><div class="stat-value">{{ number_format($needAttention) }}</div><div class="stat-label">منتجات منخفضة / نافدة</div></div>
        </div></div>
    </div>
</div>

{{-- ============ Products table ============ --}}
<div class="row row-sm">
    <div class="col-xl-12">
        <div class="card products-card">
            <div class="card-header pb-0 bg-transparent">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div class="mb-2">
                        <h4 class="card-title mg-b-0">جدول المنتجات</h4>
                        <p class="tx-12 tx-gray-500 mb-0 mt-1">
                            ابحث بالاسم أو الكود، أو استخدم الفلاتر. الكمية وسعر التكلفة يتحدثان من فواتير المشتريات، وتعديل المنتج مخصص للبيانات الأساسية وسعر البيع. لتعديل منتج اضغط على
                            <span class="badge badge-info"><i class="fas fa-edit"></i> تعديل</span>
                        </p>
                    </div>
                    <div class="mb-2">
                        <button type="button" id="exportExcel" class="btn btn-outline-success ml-1">
                            <i class="fas fa-file-excel ml-1"></i> تصدير Excel
                        </button>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addProducts">
                            <i class="fas fa-plus ml-1"></i> إضافة منتج جديد
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body">
                {{-- Filters --}}
                <div class="filters-bar">
                    <div class="row">
                        <div class="col-12 col-md-4 form-group">
                            <label class="tx-12 mb-1">التصنيف</label>
                            <select id="filterCategory" class="form-control">
                                <option value="">كل التصنيفات</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->category_id }}">{{ $category->category_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4 form-group">
                            <label class="tx-12 mb-1">حالة المخزون</label>
                            <select id="filterStock" class="form-control">
                                <option value="">الكل</option>
                                <option value="ok">متوفر</option>
                                <option value="low">منخفض (≤ {{ $LOW_STOCK_THRESHOLD }})</option>
                                <option value="out">نافد</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table text-md-nowrap" id="productsTable">
                        <thead>
                            <tr>
                                <th>المنتج</th>
                                <th>التصنيف</th>
                                <th class="text-center">الكمية</th>
                                <th>سعر التكلفة</th>
                                <th>سعر البيع</th>
                                <th>تاريخ التسجيل</th>
                                <th>آخر تعديل</th>
                                <th class="text-center no-sort">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                @php
                                    $qty = (float) $product->quantity;
                                    $stock = $qty <= 0 ? 'out' : ($qty <= $LOW_STOCK_THRESHOLD ? 'low' : 'ok');
                                    $imgSrc = asset('images/products/' . ($product->image ?: 'item.png'));
                                    $catName = $categoryMap[$product->category] ?? null;
                                    $createdAt = $product->created_at ? \Illuminate\Support\Carbon::parse($product->created_at) : null;
                                    $updatedAt = $product->updated_at ? \Illuminate\Support\Carbon::parse($product->updated_at) : null;
                                @endphp
                                <tr data-category="{{ $product->category }}" data-stock="{{ $stock }}">
                                    {{-- Product (image + name + code) --}}
                                    <td data-order="{{ $product->name }}" data-search="{{ $product->id }} {{ $product->name }}">
                                        <div class="product-cell">
                                            <img src="{{ $imgSrc }}" alt="{{ $product->name }}" class="product-thumb" loading="lazy"
                                                 onerror="this.onerror=null;this.src='{{ $placeholderImg }}'">
                                            <div>
                                                <p class="product-name">{{ $product->name }}</p>
                                                <span class="product-code">كود: #{{ $product->id }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Category --}}
                                    <td>
                                        @if ($catName)
                                            <span class="badge-soft category">{{ $catName }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    {{-- Quantity --}}
                                    <td class="text-center" data-order="{{ $qty }}">
                                        <span class="badge-soft {{ $stock }}">
                                            {{ $product->quantity }}
                                            @if ($stock === 'out') · نفذت @elseif ($stock === 'low') · منخفض @endif
                                        </span>
                                    </td>

                                    {{-- Cost price --}}
                                    <td data-order="{{ (float) $product->cost_price }}">
                                        <span class="price">{{ number_format((float) $product->cost_price, 2) }} ج.م</span>
                                    </td>

                                    {{-- Sale price --}}
                                    <td data-order="{{ (float) $product->price }}">
                                        <span class="price">{{ number_format((float) $product->price, 2) }} ج.م</span>
                                    </td>

                                    {{-- Dates --}}
                                    <td data-order="{{ $createdAt ? $createdAt->timestamp : '' }}">
                                        <span class="date-text"><i class="far fa-calendar-plus ml-1"></i>{{ $createdAt ? $createdAt->format('Y-m-d H:i') : '—' }}</span>
                                    </td>
                                    <td data-order="{{ $updatedAt ? $updatedAt->timestamp : '' }}">
                                        <span class="date-text"><i class="far fa-clock ml-1"></i>{{ $updatedAt ? $updatedAt->format('Y-m-d H:i') : '—' }}</span>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="text-center">
                                        <div class="action-group btn-group" role="group">
                                            <button type="button" class="btn btn-info edit-product-btn"
                                                    data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                                                    data-category="{{ $product->category }}"
                                                    data-price="{{ $product->price }}" data-image="{{ $imgSrc }}"
                                                    data-update-url="{{ route('products.update', $product->id) }}"
                                                    title="تعديل" data-tooltip="true">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-secondary print-barcode-btn"
                                                    data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                                                    data-quantity="{{ $product->quantity }}" data-price="{{ $product->price }}"
                                                    title="طباعة باركود" data-tooltip="true">
                                                <i class="fas fa-barcode"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger delete-product-btn"
                                                    data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                                                    data-quantity="{{ $product->quantity }}" data-image="{{ $imgSrc }}"
                                                    data-delete-url="{{ route('products.destroy', $product->id) }}"
                                                    title="مسح" data-tooltip="true">
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

{{-- ============ Modals (outside the table, rendered once) ============ --}}

<!-- Print barcode modal (single instance) -->
<div class="modal fade" id="printBarcodeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-barcode ml-1 text-primary"></i> طباعة الباركود</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="إغلاق"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-wrap align-items-end justify-content-between mb-3">
                    <div class="form-group mb-0">
                        <label for="barcodeQuantity" class="mb-1">عدد البطاقات</label>
                        <input type="number" id="barcodeQuantity" class="form-control text-center" min="1" max="120" value="15" style="width: 130px;">
                    </div>
                    <button type="button" id="printBarcode" class="btn btn-primary mt-2">
                        <i class="fas fa-print ml-1"></i> طباعة
                    </button>
                </div>
                <div id="barcodePreview"></div>
            </div>
        </div>
    </div>
</div>

<!-- Add product modal -->
<div class="modal fade" id="addProducts" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="product-form" novalidate>
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle ml-1 text-primary"></i> إضافة منتج جديد</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="إغلاق"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>كود المنتج <span class="text-danger">*</span></label>
                        <input type="text" class="form-control text-left" dir="ltr" name="product_id" inputmode="numeric" placeholder="أدخل كود المنتج (أرقام فقط)" autocomplete="off" required>
                        <div class="field-error">كود المنتج يجب أن يحتوي على أرقام فقط.</div>
                    </div>
                    <div class="form-group">
                        <label>اسم المنتج <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="product_name" placeholder="أدخل اسم المنتج" maxlength="150" autocomplete="off" required>
                        <div class="field-error">اسم المنتج يجب أن يحتوي على حروف عربية أو إنجليزية أو أرقام أو مسافات فقط.</div>
                    </div>
                    <div class="form-group">
                        <label>التصنيف <span class="text-danger">*</span></label>
                        <select class="form-control" name="product_category" required>
                            <option value="">اختر التصنيف</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->category_id }}">{{ $category->category_name }}</option>
                            @endforeach
                        </select>
                        <div class="field-error">من فضلك اختر التصنيف.</div>
                    </div>
                    <div class="form-group">
                        <label>سعر البيع (ج.م) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control text-left" dir="ltr" name="product_price" inputmode="decimal" placeholder="0.00" autocomplete="off" required>
                        <div class="field-error">السعر يجب أن يكون عددًا صحيحًا أو عشريًا (حتى رقمين بعد الفاصلة).</div>
                    </div>
                    <div class="form-group mb-0">
                        <label>صورة المنتج</label>
                        <div class="image-preview">
                            <img src="{{ $placeholderImg }}" alt="معاينة الصورة" class="js-image-preview">
                            <small class="text-muted">اختياري — صورة بصيغة JPG أو PNG بحجم أقل من 2MB.</small>
                        </div>
                        <input type="file" class="form-control js-image-input" name="product_image" accept="image/*">
                        <div class="field-error">الملف يجب أن يكون صورة بحجم أقل من 2MB.</div>
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

<!-- Shared edit modal: populate only when a product is selected. -->
<div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form action="#" method="POST" enctype="multipart/form-data" class="product-form" id="editProductForm" novalidate>
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title"><i class="fas fa-edit ml-1 text-info"></i> تعديل بيانات المنتج</h5><button type="button" class="close" data-dismiss="modal" aria-label="إغلاق"><span aria-hidden="true">&times;</span></button></div>
                <div class="modal-body">
                    <div class="form-group"><label>كود المنتج</label><input type="text" class="form-control" name="product_id" readonly></div>
                    <div class="form-group"><label>اسم المنتج <span class="text-danger">*</span></label><input type="text" class="form-control" name="product_name" maxlength="150" autocomplete="off" required><div class="field-error">اسم المنتج يجب أن يحتوي على حروف عربية أو إنجليزية أو أرقام أو مسافات فقط.</div></div>
                    <div class="form-group"><label>التصنيف <span class="text-danger">*</span></label><select class="form-control" name="product_category" required><option value="">اختر التصنيف</option>@foreach ($categories as $category)<option value="{{ $category->category_id }}">{{ $category->category_name }}</option>@endforeach</select><div class="field-error">من فضلك اختر التصنيف.</div></div>
                    <div class="form-group"><label>سعر البيع (ج.م) <span class="text-danger">*</span></label><input type="text" class="form-control text-left" dir="ltr" name="product_price" inputmode="decimal" autocomplete="off" required><div class="field-error">سعر البيع يجب أن يكون عددًا صحيحًا أو عشريًا.</div></div>
                    <div class="form-group mb-0"><label>صورة المنتج</label><div class="image-preview"><img src="{{ $placeholderImg }}" alt="معاينة الصورة" class="js-image-preview" onerror="this.onerror=null;this.src='{{ $placeholderImg }}'"><small class="text-muted">لتغيير الصورة اختر صورة جديدة، وإلا ستبقى الصورة الحالية.</small></div><input type="file" class="form-control js-image-input" name="product_image" accept="image/*"><div class="field-error">الملف يجب أن يكون صورة بحجم أقل من 2MB.</div></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ</button><button type="button" class="btn btn-light" data-dismiss="modal">إغلاق</button></div>
            </div>
        </form>
    </div>
</div>

<!-- Shared delete modal. -->
<div class="modal fade" id="deleteProductModal" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog modal-dialog-centered" role="document">
    <form action="#" method="POST" class="product-delete-form" id="deleteProductForm">@csrf @method('DELETE')
        <div class="modal-content"><div class="modal-body text-center pt-4"><div class="delete-icon"><i class="fas fa-exclamation-triangle"></i></div><h5 class="font-weight-bold">تأكيد حذف المنتج</h5>
                    <img src="{{ $placeholderImg }}" alt="" class="delete-product-thumb my-2" id="deleteProductImage" onerror="this.onerror=null;this.src='{{ $placeholderImg }}'">
            <p class="mb-1">هل أنت متأكد من حذف <strong id="deleteProductName"></strong> (كود #<span id="deleteProductId"></span>)؟</p><div class="alert alert-warning py-2 mt-3 mb-0 tx-13 d-none" id="deleteProductStock"></div><p class="text-muted tx-12 mt-2 mb-0">لا يمكن التراجع عن هذا الإجراء.</p></div>
            <div class="modal-footer justify-content-center border-0 pt-0"><button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt ml-1"></i> نعم، احذف</button><button type="button" class="btn btn-light" data-dismiss="modal">إلغاء</button></div>
        </div>
    </form>
</div></div>
@endsection

@section('js')
<!-- Internal Data tables -->
<script src="{{ URL::asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('assets/plugins/datatable/js/dataTables.bootstrap4.js') }}"></script>
<script src="{{ URL::asset('assets/plugins/datatable/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ URL::asset('assets/plugins/datatable/js/responsive.bootstrap4.min.js') }}"></script>
<script>
$(function () {

    const deferredLibraries = {};
    function loadLibrary(name, src) {
        if (!deferredLibraries[name]) {
            deferredLibraries[name] = new Promise(function (resolve, reject) {
                const script = document.createElement('script');
                script.src = src;
                script.onload = resolve;
                script.onerror = function () { delete deferredLibraries[name]; reject(new Error('تعذر تحميل المكتبة')); };
                document.head.appendChild(script);
            });
        }
        return deferredLibraries[name];
    }
    const barcodeLibraryUrl = @json(URL::asset('assets/js/JsBarcode.all.min.js'));
    const excelLibraryUrl = @json(URL::asset('assets/js/xlsx.full.min.js'));

    /* =====================================================
     * DataTable + filters
     * ===================================================== */
    const table = $('#productsTable').DataTable({
        responsive: true,
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        order: [[6, 'desc']],                       // latest edits first
        columnDefs: [{ targets: 'no-sort', orderable: false, searchable: false }],
        language: {
            search: '',
            searchPlaceholder: 'ابحث بالاسم أو الكود...',
            lengthMenu: 'عرض _MENU_',
            info: 'عرض _START_ إلى _END_ من _TOTAL_ منتج',
            infoEmpty: 'لا توجد منتجات',
            infoFiltered: '(تمت التصفية من _MAX_ منتج)',
            zeroRecords: 'لا توجد نتائج مطابقة',
            emptyTable: 'لا توجد منتجات حتى الآن',
            paginate: { first: 'الأول', last: 'الأخير', next: 'التالي', previous: 'السابق' }
        }
    });

    // Category + stock filters (matched against data attributes on each row)
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'productsTable') return true;
        const node = table.row(dataIndex).node();
        const cat = $('#filterCategory').val();
        const stock = $('#filterStock').val();
        if (cat && String(node.dataset.category) !== String(cat)) return false;
        if (stock && node.dataset.stock !== stock) return false;
        return true;
    });
    $('#filterCategory, #filterStock').on('change', function () { table.draw(); });

    $('[data-tooltip="true"]').tooltip({ trigger: 'hover' });

    // Reuse one edit/delete modal instead of putting two full modal trees in the page per product.
    $(document).on('click', '.edit-product-btn', function () {
        const button = this;
        const $form = $('#editProductForm');
        $form[0].reset();
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.attr('action', button.dataset.updateUrl);
        $form.find('[name="product_id"]').val(button.dataset.id);
        $form.find('[name="product_name"]').val(button.dataset.name);
        $form.find('[name="product_category"]').val(button.dataset.category);
        $form.find('[name="product_price"]').val(button.dataset.price);
        $form.find('.js-image-preview').attr('src', button.dataset.image || '{{ $placeholderImg }}');
        $('#editProductModal').modal('show');
    });

    $(document).on('click', '.delete-product-btn', function () {
        const button = this;
        $('#deleteProductForm').attr('action', button.dataset.deleteUrl);
        $('#deleteProductId').text(button.dataset.id);
        $('#deleteProductName').text(button.dataset.name);
        $('#deleteProductImage').attr('src', button.dataset.image || '{{ $placeholderImg }}');
        const quantity = parseFloat(button.dataset.quantity) || 0;
        $('#deleteProductStock').toggleClass('d-none', quantity <= 0)
            .text('يوجد ' + button.dataset.quantity + ' قطعة من هذا المنتج في المخزون.');
        $('#deleteProductModal').modal('show');
    });

    /* =====================================================
     * Form validation (only product add/edit forms)
     * ===================================================== */
    const nameRegex  = /^[\u0600-\u06FFa-zA-Z0-9\s]+$/;
    const idRegex    = /^[0-9]+$/;
    const priceRegex = /^\d+(\.\d{1,2})?$/;
    const MAX_IMAGE_BYTES = 2 * 1024 * 1024; // 2MB — adjust to match your server limit

    function setValid($el, ok) { $el.toggleClass('is-invalid', !ok); return ok; }

    function validateForm($form) {
        let ok = true;
        const $f = n => $form.find('[name="' + n + '"]');

        const $id = $f('product_id');
        if ($id.length && !$id.prop('readonly')) ok = setValid($id, idRegex.test($id.val().trim())) && ok;

        ok = setValid($f('product_name'), nameRegex.test($f('product_name').val().trim())) && ok;
        ok = setValid($f('product_category'), $f('product_category').val() !== '') && ok;
        ok = setValid($f('product_price'), priceRegex.test($f('product_price').val().trim())) && ok;

        const file = $f('product_image')[0] && $f('product_image')[0].files[0];
        if (file) {
            const fileOk = file.type.startsWith('image/') && file.size <= MAX_IMAGE_BYTES;
            ok = setValid($f('product_image'), fileOk) && ok;
        }
        return ok;
    }

    // Normalise Arabic-Indic digits + strip junk while typing
    const arabicDigits = '٠١٢٣٤٥٦٧٨٩';
    const toLatin = s => s.replace(/[٠-٩]/g, d => arabicDigits.indexOf(d));
    $(document).on('input', '.product-form [name="product_id"]:not([readonly])', function () {
        this.value = toLatin(this.value).replace(/\D/g, '');
    });
    $(document).on('input', '.product-form [name="product_price"]', function () {
        this.value = toLatin(this.value).replace(/[^\d.]/g, '');
    });
    $(document).on('input change', '.product-form .form-control', function () { $(this).removeClass('is-invalid'); });

    $('.product-form').on('submit', function (e) {
        const $form = $(this);
        if (!validateForm($form)) {
            e.preventDefault();
            $form.find('.is-invalid').first().trigger('focus');
            return;
        }
        $form.find('button[type="submit"]').addClass('is-loading')
             .html('<span class="spinner-border spinner-border-sm ml-1"></span> جاري الحفظ...');
    });

    $('.product-delete-form').on('submit', function () {
        $(this).find('button[type="submit"]').addClass('is-loading')
               .html('<span class="spinner-border spinner-border-sm ml-1"></span> جاري الحذف...');
    });

    // Live image preview
    $(document).on('change', '.js-image-input', function () {
        const $img = $(this).closest('.form-group').find('.js-image-preview');
        const file = this.files[0];
        if (file && file.type.startsWith('image/')) {
            $img.attr('src', URL.createObjectURL(file));
        }
    });

    $('.modal').on('hidden.bs.modal', function () {
        $(this).find('.is-invalid').removeClass('is-invalid');
    });
    $('#addProducts').on('hidden.bs.modal', function () {
        const form = $(this).find('form')[0];
        form.reset();
        $(form).find('.js-image-preview').attr('src', '{{ $placeholderImg }}');
    }).on('shown.bs.modal', function () {
        $(this).find('[name="product_id"]').trigger('focus');
    });

    /* =====================================================
     * Barcode labels (single modal, count is adjustable)
     * ===================================================== */
    let currentLabel = null;
    const $preview = $('#barcodePreview');

    function renderBarcodes() {
        if (!currentLabel) return;
        let qty = parseInt($('#barcodeQuantity').val(), 10);
        if (isNaN(qty) || qty < 1) qty = 1;
        if (qty > 120) qty = 120;

        const grid = document.createElement('div');
        grid.className = 'label-grid';

        for (let i = 0; i < qty; i++) {
            const label = document.createElement('div');
            label.className = 'barcode-label';

            const name = document.createElement('p');
            name.innerHTML = '<strong></strong>';
            name.firstChild.textContent = currentLabel.name;       // textContent → no HTML injection

            const price = document.createElement('p');
            price.textContent = 'السعر: ' + currentLabel.price;

            const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');

            label.append(name, price, svg);
            grid.appendChild(label);

            JsBarcode(svg, String(currentLabel.id), { format: 'CODE128', displayValue: true, width: 2, height: 50 });
        }
        $preview.empty().append(grid);
    }

    $(document).on('click', '.print-barcode-btn', async function () {
        try {
            await loadLibrary('barcode', barcodeLibraryUrl);
        } catch (error) {
            alert('تعذر تحميل أداة إنشاء الباركود.');
            return;
        }
        currentLabel = { id: this.dataset.id, name: this.dataset.name, price: this.dataset.price };
        $('#barcodeQuantity').val(15);
        renderBarcodes();
        $('#printBarcodeModal').modal('show');
    });
    $('#barcodeQuantity').on('input change', renderBarcodes);

    $('#printBarcode').on('click', function () {
        const html = `<!DOCTYPE html>
<html dir="rtl" lang="ar"><head><meta charset="utf-8"><title>طباعة الباركود</title>
<style>
    @page { size: A4; margin: 10mm; }
    body { margin: 0; font-family: Tahoma, Arial, sans-serif; }
    .label-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 4mm; }
    .barcode-label { border: 1px dashed #bbb; border-radius: 6px; padding: 3mm; text-align: center; page-break-inside: avoid; break-inside: avoid; }
    .barcode-label p { margin: 0 0 2px; font-size: 12px; }
    .barcode-label svg { max-width: 100%; height: auto; }
</style></head>
<body>${$preview.html()}</body></html>`;

        const w = window.open('', '_blank', 'height=700,width=900');
        if (!w) { alert('المتصفح منع نافذة الطباعة. من فضلك اسمح بالنوافذ المنبثقة لهذا الموقع.'); return; }
        w.document.open();
        w.document.write(html);
        w.document.close();
        setTimeout(function () { w.focus(); w.print(); }, 300);
    });

    /* =====================================================
     * Excel export (clean data, all rows — not just the visible page)
     * ===================================================== */
    const exportRows = @json($exportRows);

    $('#exportExcel').on('click', async function () {
        if (!exportRows.length) { alert('لا توجد بيانات للتصدير.'); return; }
        try {
            await loadLibrary('excel', excelLibraryUrl);
        } catch (error) {
            alert('تعذر تحميل أداة تصدير Excel.');
            return;
        }
        const ws = XLSX.utils.json_to_sheet(exportRows);
        ws['!cols'] = [{ wch: 12 }, { wch: 30 }, { wch: 18 }, { wch: 10 }, { wch: 12 }, { wch: 18 }, { wch: 18 }];
        const wb = XLSX.utils.book_new();
        wb.Workbook = { Views: [{ RTL: true }] };
        XLSX.utils.book_append_sheet(wb, ws, 'Products');
        const d = new Date().toISOString().slice(0, 10);
        XLSX.writeFile(wb, 'products_' + d + '.xlsx');
    });
});
</script>
@endsection
