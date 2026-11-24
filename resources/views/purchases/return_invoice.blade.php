@extends('layouts.master')

@section('title', 'فاتورة مرتجع مشتريات')

@section('css')
<style>@media print { .app-sidebar, .main-header, .main-footer, #back-to-top, .no-print { display:none !important; } .main-content.app-content { margin:0 !important; padding:0 !important; } .container-fluid { width:100% !important; padding:0 !important; } .card { border:0 !important; box-shadow:none !important; } }</style>
@endsection

@section('page-header')
    <div class="breadcrumb-header justify-content-between"><div class="my-auto"><h4 class="content-title mb-0">فاتورة مرتجع مشتريات</h4></div><button type="button" class="btn btn-primary no-print" onclick="window.print()">طباعة</button></div>
@endsection

@section('content')
    <div class="card"><div class="card-header"><h4 class="card-title mb-0">مرتجع رقم {{ $return->id }}</h4></div><div class="card-body">
        @if(session('success'))<div class="alert alert-success no-print">{{ session('success') }}</div>@endif
        <div class="row mb-4"><div class="col-md-6"><strong>فاتورة الشراء الأصلية:</strong> {{ $return->original_invoice_id }}</div><div class="col-md-6"><strong>تاريخ المرتجع:</strong> {{ $return->created_at }}</div><div class="col-md-6"><strong>المورد:</strong> {{ $return->supplier_name }}</div><div class="col-md-6"><strong>الموظف:</strong> {{ $return->user_name ?: '—' }}</div>@if($return->reason)<div class="col-md-12"><strong>سبب الإرجاع:</strong> {{ $return->reason }}</div>@endif</div>
        <div class="table-responsive"><table class="table table-bordered text-center"><thead><tr><th>م</th><th>الصنف</th><th>الكمية المرتجعة</th><th>تكلفة الوحدة</th><th>الإجمالي</th></tr></thead><tbody>
            @foreach($items as $item)<tr><td>{{ $loop->iteration }}</td><td>{{ $item->product_name }}</td><td>{{ $item->quantity }}</td><td>{{ number_format($item->unit_price, 2) }}</td><td>{{ number_format($item->total_amount, 2) }}</td></tr>@endforeach
        </tbody><tfoot><tr><th colspan="4">إجمالي قيمة المرتجع</th><th>{{ number_format($return->total_amount, 2) }} ج.م</th></tr></tfoot></table></div>
        <a class="btn btn-secondary no-print" href="{{ route('purchases.returns.index') }}">العودة إلى المرتجعات</a>
    </div></div>
@endsection
