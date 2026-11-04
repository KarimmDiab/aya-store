@extends('layouts.master')
<title> معرض أيه - صفحة التقارير </title>

@section('css')
	<link rel="stylesheet" href="{{ asset('assets/css/reports.css') }}">
	<link href="{{URL::asset('assets/plugins/select2/css/select2.min.css')}}" rel="stylesheet">

@endsection
@section('page-header')
	<!-- breadcrumb -->
	<div class="breadcrumb-header justify-content-between">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto">Pages</h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/
					Empty</span>
			</div>
		</div>

	</div>
	<!-- breadcrumb -->
@endsection
@section('content')
	<!-- row -->
	<div class="row">

		<h1>التقــــــــاريـــــــر</h1>
		<form id="salesReportForm" method="post">
			@csrf
			<!-- Date Range Picker -->
			<div class="date-range-picker p-3 bg-white rounded shadow-sm">
				<label for="dateRange" class="fw-bold mb-2 d-block">اختار الفترة الزمنية:</label>
				<div class="input-daterange input-group">
					<input type="date" class="input-sm form-control border" name="startDate" id="startDate"
						placeholder="تاريخ البداية" required />
					<span class="input-group-text bg-light">إلي</span>
					<input type="date" class="input-sm form-control border" name="endDate" id="endDate"
						placeholder="تاريخ النهاية" required />
				</div>
			</div>


			<!-- Reports Section -->
			<div class="row justify-content-between">
				<!-- Sales Report Card -->
				<div class="report-card col-4">
					<div class="report-header">
						<i class="fas fa-chart-line"></i>
						<h2>تقارير المبيعات</h2>
					</div>
					<div class="sub-reports">
						<div class="sub-report" data-report="dailySales"
							onclick="submitReport('{{ route('total_sales') }}')" target="_blank">
							<i class="fas fa-calendar-day"></i>
							<span>إجمالي المبيعات خلال فترة معينة</span>
						</div>
						<!-- Sub-report Button -->
						<div class="sub-report" data-report="monthlySales">
							<button type="button" class="btn" onclick="submitReport('{{ route('sales_per_product') }}')">
								<i class="fas fa-calendar-alt"></i>
								المبيعات لكل منتج خلال فترة معينة
							</button>
						</div>
						
						<div class="sub-report" data-report="customerSales" onclick="submitReport('{{ route('salesPerCustomer') }}')">
							<i class="fas fa-users"></i>
							<span>المبيعات لكل عميل خلال فترة معينة</span>
						</div>
						<div class="sub-report" data-report="employeeSales" onclick="submitReport('{{ route('salesPerUser') }}')">
							<i class="fas fa-user-tie"></i>
							<span>المبيعات التي تمت من قبل كل موظف خلال فترة معينة</span>
						</div>
					</div>
				</div>

				<!-- stock Report Card -->
				<div class="report-card col-4">
					<div class="report-header">
						<i class="fas fa-shopping-cart"></i>
						<h2>تقارير المخزون</h2>
					</div>
					<div class="sub-reports">
						<div class="sub-report" data-report="supplierPurchases">
							<i class="fas fa-truck"></i>
							<span>كمية المخزون لكل منتج في بداية ونهاية الفترة المحددة</span>
						</div>
						<div class="sub-report" data-report="productPurchases">
							<i class="fas fa-box"></i>
							<span>حركة المخزون خلال فترة معينة</span>
						</div>
						<div class="sub-report" data-report="productPurchases">
							<i class="fas fa-box"></i>
							<span>المنتجات التي وصلت إلى حد إعادة الطلب خلال الفترة</span>
						</div>
					</div>
				</div>

				<!-- purchases Report Card -->
				<div class="report-card col-4">
					<div class="report-header">
						<i class="fas fa-money-bill-wave"></i>
						<h2>تقارير المشتريات</h2>
					</div>
					<div class="sub-reports">
						<div class="sub-report" data-report="dailyExpenses">
							<i class="fas fa-calendar-day"></i>
							<span>إجمالي المشتريات خلال فترة معينة</span>
						</div>
						<div class="sub-report" data-report="monthlyExpenses">
							<i class="fas fa-calendar-alt"></i>
							<span>المشتريات لكل مورد خلال فترة معينة</span>
						</div>
						<div class="sub-report" data-report="monthlyExpenses">
							<i class="fas fa-calendar-alt"></i>
							<span>المشتريات لكل منتج خلال فترة معينة</span>
						</div>
						<div class="sub-report" data-report="monthlyExpenses">
							<i class="fas fa-calendar-alt"></i>
							<span>المدفوعات المستحقة أو المدفوعة للموردين خلال الفترة</span>
						</div>
					</div>
				</div>


				<!-- profit Report Card -->
				<div class="report-card col-4">
					<div class="report-header">
						<i class="fas fa-money-bill-wave"></i>
						<h2>تقارير الارباح</h2>
					</div>
					<div class="sub-reports">
						<div class="sub-report" data-report="dailyExpenses">
							<i class="fas fa-calendar-day"></i>
							<span>إجمالي الأرباح خلال فترة محددة بناءً على المبيعات والمشتريات</span>
						</div>
						<div class="sub-report" data-report="monthlyExpenses">
							<i class="fas fa-calendar-alt"></i>
							<span>مقارنة الأرباح لكل فرع بين فترتين مختلفتين</span>
						</div>
					</div>
				</div>


				<!-- customers Report Card -->
				<div class="report-card col-4">
					<div class="report-header">
						<i class="fas fa-money-bill-wave"></i>
						<h2>تقارير العملاء</h2>
					</div>
					<div class="sub-reports">
						<div class="sub-report" data-report="dailyExpenses">
							<i class="fas fa-calendar-day"></i>
							<span>أكثر العملاء شراءً خلال فترة معينة</span>
						</div>
						<div class="sub-report" data-report="monthlyExpenses">
							<i class="fas fa-calendar-alt"></i>
							<span>العملاء الذين لديهم ديون أو مستحقات غير مسددة خلال الفترة</span>
						</div>
						<div class="sub-report" data-report="monthlyExpenses">
							<i class="fas fa-calendar-alt"></i>
							<span>المبالغ التي تم تحصيلها من العملاء خلال فترة معينة</span>
						</div>
					</div>
				</div>


				<!-- suppliers Report Card -->
				<div class="report-card col-4">
					<div class="report-header">
						<i class="fas fa-money-bill-wave"></i>
						<h2>تقارير الموردين</h2>
					</div>
					<div class="sub-reports">
						<div class="sub-report" data-report="dailyExpenses">
							<i class="fas fa-calendar-day"></i>
							<span>إجمالي المبالغ المدفوعة لكل مورد خلال الفترة</span>
						</div>
						<div class="sub-report" data-report="monthlyExpenses">
							<i class="fas fa-calendar-alt"></i>
							<span>الديون المستحقة لكل مورد في بداية ونهاية الفترة</span>
						</div>
					</div>
				</div>







			</div>


		</form>

	</div>
	<!-- row closed -->
	</div>
	<!-- Container closed -->
	</div>
	<!-- main-content closed -->
@endsection
@section('js')
	<!-- Include jQuery -->
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<!-- Internal Form-validation js -->
	<script src="{{URL::asset('assets/js/form-validation.js')}}"></script>
	<!--Internal  Datepicker js -->
	<script src="{{URL::asset('assets/plugins/jquery-ui/ui/widgets/datepicker.js')}}"></script>
	<!-- Internal Select2 js-->
	<script src="{{URL::asset('assets/plugins/select2/js/select2.min.js')}}"></script>
	<!-- Internal Modal js-->
	<script src="{{URL::asset('assets/js/modal.js')}}"></script>

	<script>
function submitReport(route) {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;

    if (!startDate || !endDate) {
        alert("الرجاء اختيار الفترة الزمنية أولاً.");
        return;
    }

    // إنشاء نموذج جديد
    const form = document.createElement("form");
    form.method = "POST";
    form.action = route;
    form.target = "_blank"; // فتح في نافذة جديدة

    // إضافة CSRF Token (مطلوب في Laravel)
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const csrfInput = document.createElement("input");
    csrfInput.type = "hidden";
    csrfInput.name = "_token";
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);

    // إدخال تاريخ البداية
    const startInput = document.createElement("input");
    startInput.type = "hidden";
    startInput.name = "startDate";
    startInput.value = startDate;
    form.appendChild(startInput);

    // إدخال تاريخ النهاية
    const endInput = document.createElement("input");
    endInput.type = "hidden";
    endInput.name = "endDate";
    endInput.value = endDate;
    form.appendChild(endInput);

    // إضافة النموذج إلى `body` وإرساله
    document.body.appendChild(form);
    form.submit();

    // حذف النموذج بعد الإرسال
    document.body.removeChild(form);
}

	</script>


@endsection