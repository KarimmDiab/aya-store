@extends('layouts.master3')
<title>معرض أيه - طباعة الفاتورة</title>

<style>
    @media print {
        .no-print {
            display: none !important;
        }

        body {
            margin: 0;
            padding: 0;
        }
    }
</style>

@section('content')
<div class="container mt-5">
    <!-- Invoice Header -->
    <div class="d-flex justify-content-between align-items-center border-bottom pb-3">
        <h2 class="text-primary">🧾 فاتورة المشتريات</h2>
        <button class="btn btn-outline-primary no-print" onclick="window.print()">
            <i class="fas fa-print"></i> طباعة الفاتورة
        </button>
    </div>

    <!-- Invoice Details Section -->
    <div class="row mt-4">
        <!-- Supplier Details -->
		<div class="col-md-6 col-sm-6 col-6">
			<div class="card shadow-sm p-3 rounded border-0">
                <h5 class="border-bottom pb-2 mb-3 text-primary"><i class="fas fa-user"></i> تفاصيل المورد</h5>
                <p><strong>الاسم:</strong> {{ $purchaseInvoice->name }}</p>
                <p><strong>العنوان:</strong> {{ $purchaseInvoice->address }}</p>
                <p><strong>الهاتف:</strong> {{ $purchaseInvoice->contact_info }}</p>
            </div>
        </div>

        <!-- Invoice Details -->
		<div class="col-md-6 col-sm-6 col-6">
			<div class="card shadow-sm p-3 rounded border-0">
                <h5 class="border-bottom pb-2 mb-3 text-success"><i class="fas fa-receipt"></i> تفاصيل الفاتورة</h5>
                <p><strong>رقم الفاتورة:</strong> {{ $purchaseInvoice->id }}</p>
                <p><strong>التاريخ:</strong> {{ $purchaseInvoice->date }}</p>
                <p><strong>طريقة الدفع:</strong> {{ $purchaseInvoice->payment_method }}</p>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card mt-4 shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-box-open"></i> تفاصيل المنتجات</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-hover table-striped">
                <thead class="table-dark">
                    <tr class="text-center">
                        <th>#</th>
                        <th>اسم المنتج</th>
                        <th>الكمية</th>
                        <th>سعر الوحدة</th>
                        <th>الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoiceDetails as $index => $detail)
                    <tr class="text-center">
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $detail->product_name ?? 'N/A' }}</td>
                        <td>{{ $detail->quantity_of_purchase ?? 'N/A' }}</td>
                        <td>{{ number_format($detail->price_per_pices, 2) ?? 'N/A' }} ج.م</td>
                        <td><strong>{{ number_format($detail->total_of_product, 2) ?? 'N/A' }} ج.م</strong></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Invoice Summary -->
    <div class="card mt-4 shadow-sm">
        <div class="card-body bg-light">
            <table class="table">
                <tbody>
                    <tr>
                        <td class="text-end"><strong>إجمالي الفاتورة:</strong></td>
                        <td class="text-end"><strong class="text-primary">{{ number_format($detail->total_amount, 2) ?? 'N/A' }} ج.م</strong></td>
                    </tr>
                    <tr>
                        <td class="text-end"><strong>المبلغ المدفوع:</strong></td>
                        <td class="text-end"><strong class="text-success">{{ number_format($detail->paid_amount, 2) ?? 'N/A' }} ج.م</strong></td>
                    </tr>
                    <tr>
                        <td class="text-end"><strong>المبلغ المتبقي:</strong></td>
                        <td class="text-end"><strong class="text-danger">{{ number_format($detail->remaining_amount, 2) ?? 'N/A' }} ج.م</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Signature Section -->
    <div class="row mt-4">
        <div class="col-md-6"></div>
        <div class="col-md-6 text-center">
            <p><strong>الموظف المسئول:</strong></p>
            <p class="border-bottom d-inline-block px-3"><strong>{{ $detail->user_name ?? 'N/A' }}</strong></p>
        </div>
    </div>
</div>
@endsection

@section('js')
<!-- Internal Chart.bundle js -->
@endsection
