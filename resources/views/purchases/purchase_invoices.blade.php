@extends('layouts.master')

@section('title')
    Aya Store - فواتير المشتريات
@endsection

@section('css')
<style>
:root {
    --pi-primary: #2f5bea;
    --pi-primary-soft: #eef2ff;
    --pi-text: #1f2937;
    --pi-muted: #6b7280;
    --pi-border: #e5e7eb;
    --pi-radius: 14px;
    --pi-shadow: 0 1px 3px rgba(16, 24, 40, .06), 0 1px 2px rgba(16, 24, 40, .04);
}

/* ---------- Card & toolbar ---------- */
.pi-card { border: 1px solid var(--pi-border); border-radius: var(--pi-radius); box-shadow: var(--pi-shadow); overflow: hidden; }
.pi-card .card-header { background: #fff; border-bottom: 1px solid var(--pi-border); padding: 22px 24px 18px; }
.pi-title { font-weight: 700; color: var(--pi-text); margin: 0; }
.pi-subtitle { color: var(--pi-muted); font-size: .85rem; margin: 4px 0 0; }

.pi-toolbar { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-top: 16px; }
.pi-search { position: relative; flex: 1 1 280px; max-width: 360px; }
.pi-search input { height: 42px; border-radius: 999px; padding-inline: 16px 42px; border: 1px solid var(--pi-border); }
.pi-search input:focus { border-color: var(--pi-primary); box-shadow: 0 0 0 3px rgba(47, 91, 234, .15); }
.pi-search i { position: absolute; inset-inline-end: 16px; top: 50%; transform: translateY(-50%); color: var(--pi-muted); pointer-events: none; }
.pi-toolbar .btn { height: 42px; border-radius: 999px; padding-inline: 20px; }
.pi-perpage { display: flex; align-items: center; gap: 8px; color: var(--pi-muted); font-size: .85rem; margin-inline-start: auto; }
.pi-perpage select { height: 42px; border-radius: 999px; border: 1px solid var(--pi-border); padding: 0 14px; background: #fff; color: var(--pi-text); }

/* ---------- Alerts ---------- */
.pi-alert { display: flex; gap: 12px; align-items: flex-start; border-radius: 12px; border: 0; padding: 14px 18px; box-shadow: var(--pi-shadow); }
.pi-alert ul { margin: 0; padding-inline-start: 18px; }
.pi-alert .close { margin-inline-start: auto; }

/* ---------- Results bar ---------- */
.pi-results-bar { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 8px; padding: 12px 20px; background: #f9fafb; border-bottom: 1px solid var(--pi-border); font-size: .85rem; color: var(--pi-muted); }
.pi-results-bar strong { color: var(--pi-text); }

/* ---------- Table ---------- */
.pi-table { margin-bottom: 0; }
.pi-table thead th { background: #f9fafb; color: var(--pi-muted); font-size: .78rem; font-weight: 700; letter-spacing: .02em; border-bottom: 1px solid var(--pi-border) !important; white-space: nowrap; padding: 14px 16px; }
.pi-table tbody td { vertical-align: middle; padding: 14px 16px; color: var(--pi-text); border-top: 1px solid var(--pi-border); }
.pi-table tbody tr { transition: background .15s ease; }
.pi-table tbody tr:hover { background: #f8faff; }
.pi-table tfoot td { background: #f9fafb; font-weight: 700; padding: 14px 16px; border-top: 2px solid var(--pi-border); white-space: nowrap; }
.pi-num { font-variant-numeric: tabular-nums; font-weight: 600; white-space: nowrap; }
.pi-id { display: inline-block; background: var(--pi-primary-soft); color: var(--pi-primary); font-weight: 700; border-radius: 8px; padding: 3px 10px; }
.pi-muted { color: var(--pi-muted); font-size: .85rem; }
.pi-debt { color: #b91c1c; }
.pi-ok { color: #15803d; }
.pi-mini-progress { height: 5px; border-radius: 5px; background: #eef0f6; margin-top: 6px; min-width: 80px; }
.pi-mini-progress > span { display: block; height: 100%; border-radius: 5px; background: #16a34a; }

/* ---------- Status badge ---------- */
.pi-badge { display: inline-flex; align-items: center; gap: 6px; border-radius: 999px; padding: 4px 12px; font-size: .78rem; font-weight: 700; white-space: nowrap; }
.pi-badge::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
.pi-badge.paid    { background: #dcfce7; color: #15803d; }
.pi-badge.partial { background: #fef3c7; color: #b45309; }
.pi-badge.unpaid  { background: #fee2e2; color: #b91c1c; }

/* ---------- Action buttons ---------- */
.pi-actions { display: flex; gap: 6px; align-items: center; flex-wrap: nowrap; }
.pi-icon-btn { width: 36px; height: 36px; border-radius: 10px; border: 0; display: inline-grid; place-items: center; cursor: pointer; transition: transform .15s ease, box-shadow .15s ease, background .15s ease; font-size: .9rem; text-decoration: none; }
.pi-icon-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 14px rgba(16, 24, 40, .15); text-decoration: none; }
.pi-icon-btn:focus-visible { outline: none; box-shadow: 0 0 0 3px rgba(47, 91, 234, .35); }
.pi-icon-btn.view   { background: #e0f2fe; color: #0369a1; }
.pi-icon-btn.edit   { background: #fef3c7; color: #b45309; }
.pi-icon-btn.delete { background: #fee2e2; color: #b91c1c; }
.pi-icon-btn.view:hover   { background: #0369a1; color: #fff; }
.pi-icon-btn.edit:hover   { background: #b45309; color: #fff; }
.pi-icon-btn.delete:hover { background: #b91c1c; color: #fff; }
.pi-icon-btn:disabled { opacity: .5; cursor: not-allowed; pointer-events: none; transform: none; box-shadow: none; }
.pi-action-disabled { display: inline-flex; cursor: not-allowed; }

/* ---------- Pagination ---------- */
.pi-pagination-wrap { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 12px; padding: 16px 20px; border-top: 1px solid var(--pi-border); }
.pi-card .pi-pagination { margin: 0; gap: 4px; flex-wrap: wrap; justify-content: center; }
.pi-card .pi-pagination .page-item .page-link { margin: 0; border-radius: 10px; border: 1px solid var(--pi-border); color: var(--pi-text); min-width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; padding: 0 12px; font-weight: 600; background: #fff; gap: 6px; }
.pi-card .pi-pagination .page-item .page-link:hover { background: var(--pi-primary-soft); color: var(--pi-primary); border-color: #c7d2fe; }
.pi-card .pi-pagination .page-item.active .page-link { background: var(--pi-primary); border-color: var(--pi-primary); color: #fff; box-shadow: 0 4px 10px rgba(47, 91, 234, .3); }
.pi-card .pi-pagination .page-item.disabled .page-link { color: #cbd5e1; background: #f9fafb; pointer-events: none; }
.pi-card .pi-pagination .page-item.gap .page-link { border: 0; background: transparent; min-width: 24px; padding: 0; }
.pi-jump { display: flex; align-items: center; gap: 6px; font-size: .85rem; color: var(--pi-muted); }
.pi-jump input { width: 70px; height: 38px; border-radius: 10px; border: 1px solid var(--pi-border); text-align: center; }

/* ---------- Empty state ---------- */
.pi-empty { text-align: center; padding: 56px 20px; color: var(--pi-muted); }
.pi-empty i { font-size: 2.6rem; color: #cbd5e1; margin-bottom: 12px; }

/* ---------- Modals ---------- */
.pi-modal .modal-content { border: 0; border-radius: var(--pi-radius); overflow: hidden; }
.pi-modal .modal-header { background: linear-gradient(135deg, var(--pi-primary), #1f3fb8); color: #fff; border: 0; padding: 18px 24px; }
.pi-modal .modal-title { color: #fff; font-weight: 700; }
.pi-modal .close { color: #fff; opacity: .9; text-shadow: none; }
.pi-modal .modal-body { padding: 24px; background: #f9fafb; }
.pi-modal .modal-footer { border-top: 1px solid var(--pi-border); }
.pi-edit-modal .modal-content { max-height: calc(100vh - 32px); }
.pi-edit-modal .modal-body { overflow-y: auto; }
.pi-edit-lines-heading { padding: 6px 0 12px; }
.pi-edit-lines-wrap { border: 1px solid var(--pi-border); border-radius: 12px; background: #fff; }
.pi-edit-lines { min-width: 1050px; text-align: center; }
.pi-edit-lines thead th { background: #eef2ff; color: var(--pi-primary); font-size: .78rem; white-space: nowrap; }
.pi-edit-lines td { vertical-align: middle; padding: 7px; }
.pi-edit-lines .form-control { min-width: 88px; }
.pi-edit-lines .edit-product-name { min-width: 150px; }
.pi-edit-lines .edit-category { min-width: 125px; }
.pi-readonly { background: #f3f4f6 !important; font-weight: 700; }
.pi-edit-footer { gap: 8px; padding-top: 16px; }

.pi-info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 20px; }
.pi-info { background: #fff; border: 1px solid var(--pi-border); border-radius: 12px; padding: 14px 16px; }
.pi-info small { display: block; color: var(--pi-muted); font-size: .75rem; margin-bottom: 4px; }
.pi-info strong { color: var(--pi-text); font-size: 1.05rem; }
.pi-info.total strong  { color: var(--pi-primary); }
.pi-info.paid strong   { color: #15803d; }
.pi-info.remain strong { color: #b91c1c; }

.pi-section-title { font-size: .95rem; font-weight: 700; color: var(--pi-text); margin: 22px 0 10px; }
.pi-items { background: #fff; border-radius: 12px; overflow: hidden; border: 1px solid var(--pi-border); }
.pi-items table { margin: 0; text-align: center; }
.pi-items thead th { background: #eef2ff; color: var(--pi-primary); font-size: .8rem; border: 0; white-space: nowrap; }
.pi-items tfoot td { background: #f9fafb; font-weight: 700; border-top: 2px solid var(--pi-border); }
.pi-items td { vertical-align: middle; }
.pi-payment-type { display: inline-flex; align-items: center; gap: 6px; padding: 5px 10px; border-radius: 999px; background: #eef2ff; color: var(--pi-primary); font-size: .78rem; font-weight: 700; white-space: nowrap; }
.pi-payment-type.later { background: #ecfdf5; color: #15803d; }
.pi-payment-notes { color: var(--pi-muted); font-size: .82rem; min-width: 150px; }
.pi-profit-pos { color: #15803d; font-weight: 700; }
.pi-profit-neg { color: #b91c1c; font-weight: 700; }

.pi-image-wrap { background: #fff; border: 1px solid var(--pi-border); border-radius: 12px; padding: 12px; text-align: center; }
.pi-image-wrap img { max-width: 100%; height: auto; border-radius: 8px; }
.pi-no-image { color: var(--pi-muted); padding: 20px; }
.pi-no-image i { color: #cbd5e1; font-size: 2rem; display: block; margin-bottom: 8px; }

.pi-delete-icon { width: 64px; height: 64px; border-radius: 50%; background: #fee2e2; color: #b91c1c; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 12px; }
.pi-btn-loading { pointer-events: none; opacity: .75; }

@media (max-width: 576px) {
    .pi-card .card-header { padding: 18px 16px 14px; }
    .pi-search { max-width: 100%; }
    .pi-perpage { margin-inline-start: 0; }
    .pi-pagination-wrap { justify-content: center; }
}
@media (prefers-reduced-motion: reduce) {
    .pi-icon-btn, .pi-table tbody tr { transition: none; }
    .pi-icon-btn:hover { transform: none; }
}
</style>
@endsection

@section('page-header')
<!-- breadcrumb -->
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto">المشتريات</h4>
            <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ فواتير المشتريات</span>
        </div>
    </div>
</div>
<!-- breadcrumb -->
@endsection

@section('content')

@php
    // ---------- Pagination state ----------
    $isPaginator = $invoices instanceof \Illuminate\Contracts\Pagination\Paginator;
    $isLength    = $invoices instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;

    if ($isPaginator) {
        // keep ?search= and ?per_page= on every page link, and jump back to the table after a page change
        $invoices->appends(request()->query())->fragment('invoices-table');
    }

    $items   = $isPaginator ? collect($invoices->items()) : collect($invoices);
    $perPage = $isPaginator ? $invoices->perPage() : null;
    $perPageOptions = collect([10, 15, 25, 50, 100])->push($perPage)->filter()->unique()->sort()->values();

    // ---------- Helpers ----------
    $fmt   = fn ($d, $f = 'Y-m-d') => $d ? \Carbon\Carbon::parse($d)->format($f) : '—';
    $money = fn ($n) => number_format((float) $n, 2);
    $calcRemaining = fn ($i) => (float) ($i->payment_remaning_amount ?? $i->remaining_amount ?? 0);
    $calcPaid      = fn ($i) => (float) ($i->total_paid_amount ?? $i->paid_amount);

    // Totals of the rows shown on this page
    $pageTotal     = $items->sum(fn ($i) => (float) $i->total_amount);
    $pagePaid      = $items->sum(fn ($i) => $calcPaid($i));
    $pageRemaining = $items->sum(fn ($i) => $calcRemaining($i));
@endphp

{{-- ===== Flash messages ===== --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show pi-alert" role="alert">
        <i class="fas fa-check-circle mt-1"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="close" data-dismiss="alert" aria-label="إغلاق"><span aria-hidden="true">&times;</span></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show pi-alert" role="alert">
        <i class="fas fa-exclamation-circle mt-1"></i>
        <div>{{ session('error') }}</div>
        <button type="button" class="close" data-dismiss="alert" aria-label="إغلاق"><span aria-hidden="true">&times;</span></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show pi-alert" role="alert">
        <i class="fas fa-exclamation-triangle mt-1"></i>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="إغلاق"><span aria-hidden="true">&times;</span></button>
    </div>
@endif

<div class="col-xl-12">
    <div class="card pi-card" id="invoices-table">

        <div class="card-header">
            <div class="d-flex justify-content-between align-items-start flex-wrap">
                <div class="mb-2">
                    <h4 class="card-title pi-title">جدول فواتير المشتريات</h4>
                    <p class="pi-subtitle">عرض جميع فواتير المشتريات. لتعديل بيانات أي فاتورة اضغط على زر التعديل.</p>
                </div>
                <button type="button" class="btn btn-success mb-2" data-toggle="collapse" data-target="#purchaseInvoiceFormPanel"
                        aria-expanded="{{ request()->boolean('create') || $errors->any() ? 'true' : 'false' }}">
                    <i class="fas fa-plus ml-1"></i> تسجيل فاتورة مشتريات
                </button>
            </div>

            {{-- Search + page size (both are plain GET params, so they survive pagination) --}}
            <form method="GET" action="{{ route('purchase_invoices') }}" class="pi-toolbar" role="search" id="filterForm">
                <div class="pi-search">
                    <label class="sr-only" for="invoice-search">بحث برقم الفاتورة</label>
                    <input id="invoice-search" type="search" name="search" value="{{ request('search') }}"
                           class="form-control" placeholder="ابحث برقم الفاتورة" autocomplete="off">
                    <i class="fas fa-search"></i>
                </div>
                <button type="submit" class="btn btn-primary" id="searchBtn"><i class="fas fa-search ml-1"></i> بحث</button>
                @if (request()->filled('search'))
                    <a href="{{ route('purchase_invoices', array_filter(['per_page' => request('per_page')])) }}" class="btn btn-light">
                        <i class="fas fa-times ml-1"></i> مسح البحث
                    </a>
                @endif

                @if ($isPaginator)
                    <div class="pi-perpage">
                        <label for="perPageSelect" class="mb-0">عدد الصفوف</label>
                        <select id="perPageSelect" name="per_page">
                            @foreach ($perPageOptions as $opt)
                                <option value="{{ $opt }}" {{ (int) $perPage === (int) $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </form>
        </div>

        <div class="card-body pb-0">
            @include('purchases.partials.invoice_form')
        </div>

        {{-- Results summary --}}
        <div class="pi-results-bar">
            <span>
                @if ($isLength && $invoices->total() > 0)
                    عرض <strong>{{ $invoices->firstItem() }}</strong> – <strong>{{ $invoices->lastItem() }}</strong>
                    من <strong>{{ number_format($invoices->total()) }}</strong> فاتورة
                @else
                    <strong>{{ $items->count() }}</strong> فاتورة
                @endif
                @if (request()->filled('search'))
                    · نتائج البحث عن «<strong>{{ request('search') }}</strong>»
                @endif
            </span>
            @if ($isLength && $invoices->lastPage() > 1)
                <span>صفحة <strong>{{ $invoices->currentPage() }}</strong> من <strong>{{ $invoices->lastPage() }}</strong></span>
            @endif
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table text-md-nowrap pi-table">
                    <thead>
                        <tr>
                            <th>رقم الفاتورة</th>
                            <th>تاريخ الفاتورة</th>
                            <th>اسم المورد</th>
                            <th>إجمالي الفاتورة</th>
                            <th>المبلغ المدفوع</th>
                            <th>المبلغ المتبقي</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                            <th>تاريخ التسجيل</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $invoice)
                            @php
                                $remaining = $calcRemaining($invoice);
                                $paidAmt   = $calcPaid($invoice);
                                $totalAmt  = (float) $invoice->total_amount;
                                $status    = $remaining <= 0 ? 'paid' : ($paidAmt > 0 ? 'partial' : 'unpaid');
                                $statusTxt = ['paid' => 'مدفوعة', 'partial' => 'مدفوعة جزئياً', 'unpaid' => 'غير مدفوعة'][$status];
                                $percent   = $totalAmt > 0 ? max(0, min(100, ($paidAmt / $totalAmt) * 100)) : 0;
                            @endphp
                            <tr>
                                <td><span class="pi-id">#{{ $invoice->id }}</span></td>
                                <td>{{ $fmt($invoice->date) }}</td>
                                <td>{{ $invoice->supplier_name ?? '—' }}</td>
                                <td class="pi-num">{{ $money($totalAmt) }} ج.م</td>
                                <td class="pi-num">
                                    {{ $money($paidAmt) }} ج.م
                                    <div class="pi-mini-progress" title="نسبة السداد {{ round($percent) }}%"><span style="width: {{ $percent }}%"></span></div>
                                </td>
                                <td class="pi-num {{ $remaining > 0 ? 'pi-debt' : 'pi-ok' }}">{{ $money($remaining) }} ج.م</td>
                                <td><span class="pi-badge {{ $status }}">{{ $statusTxt }}</span></td>
                                <td>
                                    <div class="pi-actions">
                                        <button type="button" class="pi-icon-btn view" title="تفاصيل الفاتورة" aria-label="تفاصيل الفاتورة {{ $invoice->id }}"
                                                data-toggle="modal" data-target="#detailsPurchaseInvoice{{ $invoice->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        @if ((float) ($invoice->total_amount_paid ?? 0) > 0.009)
                                            <span class="pi-action-disabled" title="لا يمكن تعديل الفاتورة لوجود حركات سداد لاحقة" aria-label="التعديل مقفول لوجود حركات سداد لاحقة">
                                                <button type="button" class="pi-icon-btn edit" disabled aria-disabled="true">
                                                    <i class="fas fa-lock"></i>
                                                </button>
                                            </span>
                                        @else
                                            <button type="button" class="pi-icon-btn edit js-edit-purchase-invoice"
                                                    data-invoice-id="{{ $invoice->id }}"
                                                    data-edit-url="{{ route('purchase_invoices.edit', ['invoice_id' => $invoice->id]) }}"
                                                    title="تعديل الفاتورة" aria-label="تعديل الفاتورة {{ $invoice->id }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        @endif

                                        <button type="button" class="pi-icon-btn delete" title="مسح" aria-label="مسح الفاتورة {{ $invoice->id }}"
                                                data-toggle="modal" data-target="#deleteInvoiceModal"
                                                data-action="{{ route('purchase_invoices_destroy', $invoice->id) }}"
                                                data-id="{{ $invoice->id }}"
                                                data-supplier="{{ $invoice->supplier_name ?? '—' }}"
                                                data-total="{{ $money($totalAmt) }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="pi-muted">{{ $fmt($invoice->created_at, 'Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="pi-empty">
                                        <i class="fas fa-file-invoice d-block"></i>
                                        <h5>لا توجد فواتير مشتريات</h5>
                                        <p class="mb-0">
                                            @if (request()->filled('search'))
                                                لا توجد نتائج مطابقة لبحثك.
                                            @else
                                                لم يتم تسجيل أي فاتورة حتى الآن.
                                            @endif
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if ($items->isNotEmpty())
                        <tfoot>
                            <tr>
                                <td colspan="3">إجمالي هذه الصفحة</td>
                                <td class="pi-num">{{ $money($pageTotal) }} ج.م</td>
                                <td class="pi-num">{{ $money($pagePaid) }} ج.م</td>
                                <td class="pi-num {{ $pageRemaining > 0 ? 'pi-debt' : 'pi-ok' }}">{{ $money($pageRemaining) }} ج.م</td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            {{-- ============ Pagination ============ --}}
            @if ($isLength && $invoices->lastPage() > 1)
                @php
                    $last    = $invoices->lastPage();
                    $current = min($invoices->currentPage(), $last);
                    $window  = collect([1, $last])->merge(range(max(1, $current - 2), min($last, $current + 2)))->unique()->sort()->values();
                    $prevP   = null;
                @endphp
                <div class="pi-pagination-wrap">
                    <nav aria-label="التنقل بين الصفحات">
                        <ul class="pagination pi-pagination">
                            {{-- Previous (arrow points right in RTL) --}}
                            <li class="page-item {{ $invoices->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $invoices->previousPageUrl() ?? '#' }}" rel="prev" aria-label="الصفحة السابقة">
                                    <i class="fas fa-chevron-right tx-11"></i> السابق
                                </a>
                            </li>

                            @foreach ($window as $p)
                                @if ($prevP !== null && $p - $prevP > 1)
                                    <li class="page-item gap disabled"><span class="page-link">…</span></li>
                                @endif
                                <li class="page-item {{ $p === $current ? 'active' : '' }}" @if ($p === $current) aria-current="page" @endif>
                                    <a class="page-link" href="{{ $invoices->url($p) }}">{{ $p }}</a>
                                </li>
                                @php $prevP = $p; @endphp
                            @endforeach

                            {{-- Next --}}
                            <li class="page-item {{ $invoices->hasMorePages() ? '' : 'disabled' }}">
                                <a class="page-link" href="{{ $invoices->nextPageUrl() ?? '#' }}" rel="next" aria-label="الصفحة التالية">
                                    التالي <i class="fas fa-chevron-left tx-11"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>

                    @if ($last > 7)
                        <form class="pi-jump" id="jumpForm" data-last="{{ $last }}">
                            <label for="jumpInput" class="mb-0">اذهب إلى صفحة</label>
                            <input type="number" id="jumpInput" min="1" max="{{ $last }}" placeholder="{{ $current }}">
                            <button type="submit" class="btn btn-sm btn-light border">انتقال</button>
                        </form>
                    @endif
                </div>
            @elseif ($isPaginator && !$isLength && ($invoices->previousPageUrl() || $invoices->hasMorePages()))
                {{-- simplePaginate(): previous / next only --}}
                <div class="pi-pagination-wrap justify-content-center">
                    <ul class="pagination pi-pagination">
                        <li class="page-item {{ $invoices->previousPageUrl() ? '' : 'disabled' }}">
                            <a class="page-link" href="{{ $invoices->previousPageUrl() ?? '#' }}"><i class="fas fa-chevron-right tx-11"></i> السابق</a>
                        </li>
                        <li class="page-item {{ $invoices->hasMorePages() ? '' : 'disabled' }}">
                            <a class="page-link" href="{{ $invoices->nextPageUrl() ?? '#' }}">التالي <i class="fas fa-chevron-left tx-11"></i></a>
                        </li>
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- =====================================================================
     MODALS — outside the table (valid HTML)
     ===================================================================== --}}

@foreach ($items as $invoice)
    @php
        $remaining = $calcRemaining($invoice);
        $paidAmt   = $calcPaid($invoice);
        $details   = $invoiceDetailsByInvoice->get($invoice->id, collect());
        $supplierPayments = $supplierPaymentsByInvoice->get($invoice->id, collect());
        $initialPaid = (float) ($invoice->paid_amount ?? 0);
        $sumLines  = $details->sum(fn ($d) => (float) $d->total_product);
        $sumProfit = $details->sum(fn ($d) => ($d->quantity_product * ($d->product_price ?? 0)) - ($d->quantity_product * $d->purchase_price));
    @endphp

    <div class="modal fade pi-modal" id="detailsPurchaseInvoice{{ $invoice->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="fas fa-file-invoice ml-2"></i> تفاصيل فاتورة مشتريات #{{ $invoice->id }}</h6>
                    <button aria-label="إغلاق" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">&times;</span></button>
                </div>

                <div class="modal-body">
                    <div class="pi-info-grid">
                        <div class="pi-info"><small>رقم الفاتورة</small><strong>#{{ $invoice->id }}</strong></div>
                        <div class="pi-info"><small>تاريخ الفاتورة</small><strong>{{ $fmt($invoice->date) }}</strong></div>
                        <div class="pi-info"><small>المورد</small><strong>{{ $invoice->supplier_name ?? '—' }}</strong></div>
                    </div>

                    <h6 class="pi-section-title"><i class="fas fa-boxes ml-1"></i> أصناف الفاتورة</h6>
                    <div class="pi-items table-responsive">
                        <table class="table text-md-nowrap">
                            <thead>
                                <tr>
                                    <th>م</th>
                                    <th>المنتج</th>
                                    <th>الكمية</th>
                                    <th>السعر للوحدة</th>
                                    <th>الإجمالي</th>
                                    <th>سعر البيع</th>
                                    <th>الربح</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($details as $detail)
                                    @php
                                        $profit = ($detail->quantity_product * ($detail->product_price ?? 0))
                                                - ($detail->quantity_product * $detail->purchase_price);
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $detail->product_name ?? '—' }}</td>
                                        <td>{{ $detail->quantity_product }}</td>
                                        <td class="pi-num">{{ $detail->purchase_price }}</td>
                                        <td class="pi-num">{{ $detail->total_product }}</td>
                                        <td class="pi-num">{{ $detail->product_price ?? '-' }}</td>
                                        <td class="pi-num {{ $profit >= 0 ? 'pi-profit-pos' : 'pi-profit-neg' }}">{{ $profit }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="pi-muted py-4">لا توجد أصناف لهذه الفاتورة</td></tr>
                                @endforelse
                            </tbody>
                            @if ($details->isNotEmpty())
                                <tfoot>
                                    <tr>
                                        <td colspan="4">الإجمالي</td>
                                        <td class="pi-num">{{ $money($sumLines) }}</td>
                                        <td></td>
                                        <td class="pi-num {{ $sumProfit >= 0 ? 'pi-profit-pos' : 'pi-profit-neg' }}">{{ $money($sumProfit) }}</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>

                    <h6 class="pi-section-title"><i class="fas fa-wallet ml-1"></i> الملخص المالي</h6>
                    <div class="pi-info-grid">
                        <div class="pi-info total"><small>إجمالي الفاتورة</small><strong>{{ $money($invoice->total_amount) }} ج.م</strong></div>
                        <div class="pi-info paid"><small>المبلغ المدفوع</small><strong>{{ $money($paidAmt) }} ج.م</strong></div>
                        <div class="pi-info remain"><small>المبلغ المتبقي (دين)</small><strong>{{ $money($remaining) }} ج.م</strong></div>
                    </div>

                    <h6 class="pi-section-title"><i class="fas fa-hand-holding-usd ml-1"></i> سجل دفعات المورد</h6>
                    <div class="pi-items table-responsive">
                        <table class="table text-md-nowrap">
                            <thead>
                                <tr>
                                    <th>م</th>
                                    <th>تاريخ السداد</th>
                                    <th>نوع الدفعة</th>
                                    <th>المبلغ المدفوع</th>
                                    <th>المتبقي بعد الدفعة</th>
                                    <th>ملاحظات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $paymentRow = 0; @endphp
                                @if ($initialPaid > 0)
                                    <tr>
                                        <td>{{ ++$paymentRow }}</td>
                                        <td>{{ $fmt($invoice->date) }}</td>
                                        <td><span class="pi-payment-type"><i class="fas fa-file-invoice-dollar"></i> دفعة عند تسجيل الفاتورة</span></td>
                                        <td class="pi-num pi-ok">{{ $money($initialPaid) }} ج.م</td>
                                        <td class="pi-num">{{ $money($invoice->remaining_amount) }} ج.م</td>
                                        <td class="pi-payment-notes">—</td>
                                    </tr>
                                @endif
                                @foreach ($supplierPayments as $payment)
                                    <tr>
                                        <td>{{ ++$paymentRow }}</td>
                                        <td>{{ $fmt($payment->payment_date) }}</td>
                                        <td><span class="pi-payment-type later"><i class="fas fa-money-check-alt"></i> سداد لاحق للمورد</span></td>
                                        <td class="pi-num pi-ok">{{ $money($payment->amount_paid) }} ج.م</td>
                                        <td class="pi-num">{{ $money($payment->payment_remaning_amount) }} ج.م</td>
                                        <td class="pi-payment-notes">{{ filled($payment->notes) ? $payment->notes : '—' }}</td>
                                    </tr>
                                @endforeach
                                @if ($paymentRow === 0 && $supplierPayments->isEmpty())
                                    <tr><td colspan="6" class="pi-muted py-4">لم تُسجل دفعات لهذه الفاتورة بعد.</td></tr>
                                @endif
                            </tbody>
                            @if ($paidAmt > 0)
                                <tfoot>
                                    <tr>
                                        <td colspan="3">إجمالي المدفوع</td>
                                        <td class="pi-num pi-ok">{{ $money($paidAmt) }} ج.م</td>
                                        <td class="pi-num">{{ $money($remaining) }} ج.م</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>

                    <h6 class="pi-section-title"><i class="fas fa-image ml-1"></i> صورة الفاتورة</h6>
                    <div class="pi-image-wrap">
                        @if ($invoice->purchase_invoices_pic)
                            <a href="{{ asset('images/purchases_invoices/' . $invoice->purchase_invoices_pic) }}" target="_blank" rel="noopener">
                                <img src="{{ asset('images/purchases_invoices/' . $invoice->purchase_invoices_pic) }}"
                                     alt="صورة الفاتورة {{ $invoice->id }}" loading="lazy">
                            </a>
                        @else
                            <div class="pi-no-image"><i class="far fa-image"></i> لا توجد صورة للفاتورة</div>
                        @endif
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal" type="button">إغلاق</button>
                </div>
            </div>
        </div>
    </div>
@endforeach

{{-- Shared invoice editor, loaded when the edit action is selected. --}}
<div class="modal fade pi-modal pi-edit-modal" id="editPurchaseInvoiceModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h6 class="modal-title mb-1"><i class="fas fa-edit ml-2"></i> تعديل فاتورة المشتريات</h6>
                    <small class="text-white-50">تعديل الأصناف والمبالغ مع تحديث المخزون وتكلفة المنتجات</small>
                </div>
                <button aria-label="إغلاق" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body" id="editPurchaseInvoiceModalBody">
                <div class="text-center py-5 text-muted"><span class="spinner-border spinner-border-sm ml-2"></span> جاري تحميل الفاتورة...</div>
            </div>
        </div>
    </div>
</div>

{{-- One shared delete confirmation (filled from the clicked row) --}}
<div class="modal fade" id="deleteInvoiceModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" action="#" id="deleteInvoiceForm">
            @csrf
            @method('DELETE')
            <div class="modal-content" style="border: 0; border-radius: 14px;">
                <div class="modal-body text-center pt-4">
                    <div class="pi-delete-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <h5 class="font-weight-bold">تأكيد حذف الفاتورة</h5>
                    <p class="mb-1">هل أنت متأكد من حذف فاتورة المشتريات <strong id="delInvId"></strong>؟</p>
                    <p class="pi-muted mb-1">المورد: <strong id="delInvSupplier"></strong> · الإجمالي: <strong id="delInvTotal"></strong> ج.م</p>
                    <p class="pi-muted mt-2 mb-0">لا يمكن التراجع عن هذا الإجراء.</p>
                </div>
                <div class="modal-footer justify-content-center border-0 pt-0">
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt ml-1"></i> نعم، احذف</button>
                    <button type="button" class="btn btn-light" data-dismiss="modal">إلغاء</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('js')
{{-- DataTables is intentionally NOT loaded here: paging/search are handled by the server --}}
<script>
function openInvoicePopup(form, btn) {
    const formData = new FormData(form);
    const icon = btn ? btn.querySelector('i') : null;

    // loading state
    if (btn) btn.setAttribute('disabled', 'disabled');
    if (icon) icon.className = 'fas fa-spinner fa-spin';

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': form.querySelector('input[name=_token]').value
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('HTTP ' + response.status);
        return response.text();
    })
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

        if (!newWindow) {
            alert('المتصفح منع فتح النافذة. برجاء السماح بالنوافذ المنبثقة لهذا الموقع.');
            return;
        }

        newWindow.document.write(`
            <html dir="rtl" lang="ar">
                <head>
                    <meta charset="utf-8">
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
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ أثناء تحميل الفاتورة. حاول مرة أخرى.');
    })
    .finally(() => {
        if (btn) btn.removeAttribute('disabled');
        if (icon) icon.className = 'fas fa-edit';
    });

    return false; // منع الإرسال الافتراضي للنموذج
}

$(function () {
    const $editModal = $('#editPurchaseInvoiceModal');
    const $editModalBody = $('#editPurchaseInvoiceModalBody');

    function showEditErrors(messages) {
        const $box = $('#editInvoiceErrors');
        const errors = Array.isArray(messages) ? messages : Object.values(messages || {}).flat();
        $box.empty().append($('<strong>').text('تعذر حفظ التعديلات:'));
        const $list = $('<ul class="mb-0 mt-2"></ul>');
        errors.forEach(message => $list.append($('<li>').text(message)));
        $box.append($list).removeClass('d-none');
    }

    function recalculateEditInvoice($form) {
        let total = 0;
        $form.find('#editPurchaseInvoiceLines tr').each(function () {
            const $row = $(this);
            const quantity = parseFloat($row.find('.edit-line-quantity').val()) || 0;
            const cost = parseFloat($row.find('.edit-line-cost').val()) || 0;
            const sale = parseFloat($row.find('.edit-line-sale').val()) || 0;
            const lineTotal = Math.round(quantity * cost * 100) / 100;
            const profit = Math.round((sale - cost) * quantity * 100) / 100;
            $row.find('.edit-line-total').val(lineTotal.toFixed(2));
            $row.find('.edit-line-profit').val(profit.toFixed(2));
            total += lineTotal;
        });

        total = Math.round(total * 100) / 100;
        const initialPaid = parseFloat($form.find('#editInvoicePaid').val()) || 0;
        const laterPaid = parseFloat($form.find('#editInvoiceLaterPaid').val()) || 0;
        $form.find('#editInvoiceTotal').val(total.toFixed(2));
        $form.find('#editInvoiceRemaining').val((total - initialPaid - laterPaid).toFixed(2));
        $form.find('#editInvoicePaymentWarning').toggleClass('d-none', initialPaid + laterPaid <= total + 0.009);
        return { total, initialPaid, laterPaid };
    }

    function bindEditInvoiceForm() {
        const $form = $('#editPurchaseInvoiceForm');
        $form.off('.purchaseInvoiceEdit');
        recalculateEditInvoice($form);

        $form.on('input.purchaseInvoiceEdit change.purchaseInvoiceEdit', '.edit-line-quantity, .edit-line-cost, .edit-line-sale, #editInvoicePaid', function () {
            recalculateEditInvoice($form);
        });

        $form.on('click.purchaseInvoiceEdit', '#editAddPurchaseLine', function () {
            const template = document.getElementById('editPurchaseInvoiceLineTemplate');
            if (template) {
                $form.find('#editPurchaseInvoiceLines').append(template.content.cloneNode(true));
                recalculateEditInvoice($form);
            }
        });

        $form.on('click.purchaseInvoiceEdit', '.edit-remove-line', function () {
            const $rows = $form.find('#editPurchaseInvoiceLines tr');
            if ($rows.length <= 1) {
                alert('يجب أن تحتوي الفاتورة على صنف واحد على الأقل.');
                return;
            }
            $(this).closest('tr').remove();
            recalculateEditInvoice($form);
        });

        $form.on('submit.purchaseInvoiceEdit', async function (event) {
            event.preventDefault();
            const form = this;
            const $submit = $(form).find('#savePurchaseInvoiceChanges');
            const amounts = recalculateEditInvoice($form);
            $('#editInvoiceErrors').addClass('d-none').empty();

            if (!form.reportValidity()) return;
            if (amounts.initialPaid + amounts.laterPaid > amounts.total + 0.009) {
                $('#editInvoicePaymentWarning').removeClass('d-none');
                $('#editInvoicePaid').trigger('focus');
                return;
            }

            $submit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm ml-1"></span> جاري الحفظ...');
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const payload = await response.json();
                if (!response.ok) {
                    if (response.status === 422 && payload.errors) {
                        showEditErrors(payload.errors);
                    } else {
                        showEditErrors([payload.message || 'حدث خطأ أثناء حفظ الفاتورة.']);
                    }
                    return;
                }

                $editModal.modal('hide');
                window.location.reload();
            } catch (error) {
                showEditErrors(['تعذر الاتصال بالخادم. تحقق من الاتصال ثم حاول مرة أخرى.']);
            } finally {
                $submit.prop('disabled', false).html('<i class="fas fa-save ml-1"></i> حفظ التعديلات');
            }
        });
    }

    $(document).on('click', '.js-edit-purchase-invoice', async function () {
        const editUrl = this.dataset.editUrl;
        $editModalBody.html('<div class="text-center py-5 text-muted"><span class="spinner-border spinner-border-sm ml-2"></span> جاري تحميل الفاتورة...</div>');
        $editModal.modal('show');

        try {
            const response = await fetch(editUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
            });
            if (!response.ok) {
                let message = 'تعذر تحميل الفاتورة.';
                try {
                    const payload = await response.json();
                    message = payload.message || message;
                } catch (error) {}
                throw new Error(message);
            }
            $editModalBody.html(await response.text());
            bindEditInvoiceForm();
        } catch (error) {
            $editModalBody.empty().append($('<div class="alert alert-danger mb-0">').text(error.message || 'تعذر تحميل بيانات الفاتورة. أعد تحميل الصفحة وحاول مرة أخرى.'));
        }
    });

    const requestedEditId = new URLSearchParams(window.location.search).get('edit');
    if (requestedEditId) {
        const $requestedEdit = $('.js-edit-purchase-invoice').filter(function () {
            return String(this.dataset.invoiceId) === String(requestedEditId);
        }).first();
        if ($requestedEdit.length) {
            $requestedEdit.trigger('click');
            const url = new URL(window.location.href);
            url.searchParams.delete('edit');
            window.history.replaceState({}, '', url.toString());
        }
    }

    /* ---------- Invoice form (partial) calculations — unchanged ---------- */
    const $lines = $('#purchaseInvoiceLinesBody');
    const number = value => {
        const result = parseFloat(value);
        return Number.isFinite(result) ? result : 0;
    };

    function recalculateInvoice() {
        let total = 0;
        $lines.find('tr').each(function () {
            const $row = $(this);
            const lineTotal = Math.round(number($row.find('.line-quantity').val()) * number($row.find('.line-unit-price').val()) * 100) / 100;
            $row.find('.line-total').val(lineTotal.toFixed(2));
            total += lineTotal;
        });

        total = Math.round(total * 100) / 100;
        const paid = number($('#purchaseInvoicePaid').val());
        $('#purchaseInvoiceTotal').val(total.toFixed(2));
        $('#purchaseInvoiceRemaining').val(Math.max(0, total - paid).toFixed(2));
        $('#purchaseInvoiceAmountError').toggleClass('d-none', paid <= total + 0.009);
    }

    $('#addPurchaseLine').on('click', function () {
        const template = document.getElementById('purchaseInvoiceLineTemplate');
        $lines.append(template.content.cloneNode(true));
    });

    $lines.on('click', '.remove-purchase-line', function () {
        if ($lines.find('tr').length > 1) {
            $(this).closest('tr').remove();
            recalculateInvoice();
        }
    });

    $lines.on('input change', '.line-quantity, .line-unit-price', recalculateInvoice);
    $('#purchaseInvoicePaid').on('input', recalculateInvoice);
    $('#purchaseInvoiceForm').on('submit', function (event) {
        recalculateInvoice();
        if (number($('#purchaseInvoicePaid').val()) > number($('#purchaseInvoiceTotal').val()) + 0.009) {
            event.preventDefault();
            $('#purchaseInvoicePaid').trigger('focus');
        }
    });

    recalculateInvoice();

    /* ---------- Pagination / filters ---------- */
    // Changing the page size reloads from page 1 (the form has no "page" field), keeping the search term
    $('#perPageSelect').on('change', function () { this.form.submit(); });

    $('#filterForm').on('submit', function () {
        $('#searchBtn').addClass('pi-btn-loading').html('<span class="spinner-border spinner-border-sm ml-1"></span> جاري البحث...');
    });

    // "Go to page" box: keeps every other query parameter
    $('#jumpForm').on('submit', function (e) {
        e.preventDefault();
        const last = parseInt($(this).data('last'), 10);
        let page = parseInt($('#jumpInput').val(), 10);
        if (isNaN(page)) return;
        page = Math.min(Math.max(page, 1), last);
        const url = new URL(window.location.href);
        url.searchParams.set('page', page);
        url.hash = 'invoices-table';
        window.location.href = url.toString();
    });

    /* ---------- Shared delete modal ---------- */
    $('#deleteInvoiceModal').on('show.bs.modal', function (e) {
        const $btn = $(e.relatedTarget);
        $('#deleteInvoiceForm').attr('action', $btn.data('action'));
        $('#delInvId').text('#' + $btn.data('id'));
        $('#delInvSupplier').text($btn.data('supplier'));
        $('#delInvTotal').text($btn.data('total'));
    });
    $('#deleteInvoiceForm').on('submit', function () {
        $(this).find('button[type="submit"]').addClass('pi-btn-loading')
               .html('<span class="spinner-border spinner-border-sm ml-1"></span> جاري الحذف...');
    });
});
</script>
@endsection
