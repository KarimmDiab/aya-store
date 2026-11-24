@extends('layouts.master')

@section('title', 'مرتجعات المشتريات')

@section('page-header')
    <div class="breadcrumb-header justify-content-between"><div class="my-auto"><h4 class="content-title mb-0">المشتريات / مرتجعات المشتريات</h4></div></div>
@endsection

@section('content')
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="card">
        <div class="card-header"><h4 class="card-title mb-0">تسجيل مرتجع من فاتورة مشتريات</h4></div>
        <div class="card-body">
            <form method="GET" action="{{ route('purchases.returns.index') }}" class="form-inline mb-4">
                <label for="invoice-id" class="mr-2">رقم فاتورة المشتريات</label>
                <input id="invoice-id" name="invoice_id" type="number" min="1" required class="form-control mr-2" value="{{ $invoiceId }}" placeholder="أدخل رقم الفاتورة">
                <button class="btn btn-primary" type="submit">عرض الفاتورة</button>
            </form>
            @if($invoiceId !== '' && !$sourceInvoice)<div class="alert alert-warning">لم يتم العثور على فاتورة مشتريات بهذا الرقم.</div>@endif
            @if($sourceInvoice)
                <div class="alert alert-info">الفاتورة رقم <strong>{{ $sourceInvoice->id }}</strong> — المورد: <strong>{{ $sourceInvoice->supplier_name }}</strong> — التاريخ: {{ $sourceInvoice->date }}</div>
                @if($sourceItems->isEmpty())<div class="alert alert-warning">لا توجد أصناف قابلة للإرجاع في هذه الفاتورة.</div>
                @else
                    <form method="POST" action="{{ route('purchases.returns.store') }}">
                        @csrf<input type="hidden" name="original_invoice_id" value="{{ $sourceInvoice->id }}">
                        <div class="table-responsive"><table class="table table-bordered text-center">
                            <thead><tr><th>الصنف</th><th>الكمية المشتراة</th><th>تم إرجاعه سابقًا</th><th>المتاح للإرجاع</th><th>تكلفة الوحدة</th><th>كمية المرتجع</th></tr></thead>
                            <tbody>@foreach($sourceItems as $item)<tr>
                                <td>{{ $item->product_name }}</td><td>{{ $item->purchased_quantity }}</td><td>{{ $item->returned_quantity }}</td><td>{{ $item->available_quantity }}</td><td>{{ number_format($item->unit_price, 2) }}</td>
                                <td><input type="number" class="form-control text-center" name="items[{{ $item->product_id }}]" min="0" max="{{ $item->available_quantity }}" step="1" value="0" @disabled($item->available_quantity < 1)></td>
                            </tr>@endforeach</tbody>
                        </table></div>
                        <div class="form-group mt-3"><label for="return-reason">سبب الإرجاع (اختياري)</label><input id="return-reason" name="reason" type="text" maxlength="500" class="form-control" value="{{ old('reason') }}"></div>
                        <button type="submit" class="btn btn-success">تسجيل المرتجع وخصم الكمية من المخزون</button>
                    </form>
                @endif
            @endif
        </div>
    </div>

    <div class="card mt-4"><div class="card-header"><h4 class="card-title mb-0">سجل مرتجعات المشتريات</h4></div><div class="card-body">
        <div class="table-responsive"><table class="table table-bordered text-center"><thead><tr><th>رقم المرتجع</th><th>فاتورة الشراء الأصلية</th><th>المورد</th><th>قيمة المرتجع</th><th>التاريخ</th><th>الإجراء</th></tr></thead><tbody>
            @forelse($returns as $return)<tr><td>{{ $return->id }}</td><td>{{ $return->original_invoice_id }}</td><td>{{ $return->supplier_name }}</td><td>{{ number_format($return->total_amount, 2) }}</td><td>{{ $return->created_at }}</td><td><a class="btn btn-info btn-sm" href="{{ route('purchases.returns.show', $return->id) }}">عرض وطباعة</a></td></tr>
            @empty<tr><td colspan="6">لا توجد مرتجعات مسجلة.</td></tr>@endforelse
        </tbody></table></div>{{ $returns->links() }}
    </div></div>
@endsection
