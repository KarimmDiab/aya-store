@extends('layouts.master')
@section('css')
	<style>
		/* General Styles */
		body {
			font-family: 'Arial', sans-serif;
			background-color: #f8f9fa;
			margin: 0;
			padding: 0;
			color: #333;
		}

		.container {
			max-width: 1200px;
			margin: 50px auto;
			padding: 20px;
			background-color: #fff;
			box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
			border-radius: 12px;
		}

		h1 {
			text-align: center;
			color: #007bff;
			margin-bottom: 30px;
			font-size: 2.5rem;
		}

		/* Date Range Picker */
		.date-range-picker {
			margin-bottom: 40px;
			text-align: center;
		}

		.date-range-picker label {
			font-size: 1.2rem;
			font-weight: bold;
			margin-bottom: 10px;
			display: block;
		}

		.input-daterange {
			display: flex;
			justify-content: center;
			align-items: center;
			gap: 10px;
		}

		.input-daterange .input-group-addon {
			font-size: 1.2rem;
			color: #666;
		}

		.input-daterange .form-control {
			padding: 10px;
			border: 1px solid #ddd;
			border-radius: 8px;
			font-size: 1rem;
			width: 200px;
		}

		/* Reports Grid */
		.reports-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
			gap: 20px;
		}

		/* Report Card */
		.report-card {
			background-color: #fff;
			border: 1px solid #e0e0e0;
			border-radius: 12px;
			overflow: hidden;
			width: 30%;
			transition: transform 0.3s ease, box-shadow 0.3s ease;
		}

		.report-card:hover {
			transform: translateY(-5px);
			box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
		}

		.report-header {
			padding: 20px;
			background-color: #007bff;
			color: #fff;
			display: flex;
			align-items: center;
			gap: 10px;
		}

		.report-header i {
			font-size: 1.5rem;
		}

		.report-header h2 {
			margin: 0;
			font-size: 1.5rem;
		}

		.sub-reports {
			padding: 20px;
		}

		.sub-report {
			padding: 15px;
			margin: 10px 0;
			background-color: #f8f9fa;
			border: 1px solid #e0e0e0;
			border-radius: 8px;
			display: flex;
			align-items: center;
			gap: 10px;
			cursor: pointer;
			transition: background-color 0.3s ease, transform 0.3s ease;
		}

		.sub-report:hover {
			background-color: #e9ecef;
			transform: translateX(5px);
		}

		.sub-report i {
			font-size: 1.2rem;
			color: #007bff;
		}

		.sub-report span {
			font-size: 1.1rem;
			color: #333;
		}
	</style>
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

				<!-- Date Range Picker -->
				<div class="date-range-picker p-3 bg-white rounded shadow-sm">
					<label for="dateRange" class="fw-bold mb-2 d-block">اختار الفترة الزمنية:</label>
					<div class="input-daterange input-group">
						<input type="date" class="input-sm form-control border" name="startDate" id="startDate" placeholder="تاريخ البداية" />
						<span class="input-group-text bg-light">إلي</span>
						<input type="date" class="input-sm form-control border" name="endDate" id="endDate" placeholder="تاريخ النهاية" />
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
							<div class="sub-report" data-report="dailySales">
								<i class="fas fa-calendar-day"></i>
								<span>إجمالي المبيعات خلال فترة معينة</span>
							</div>
							<div class="sub-report" data-report="monthlySales">
								<i class="fas fa-calendar-alt"></i>
								<span>المبيعات لكل منتج خلال فترة معينة</span>
							</div>
							<div class="sub-report" data-report="customerSales">
								<i class="fas fa-users"></i>
								<span>المبيعات لكل فرع خلال فترة معينة</span>
							</div>
							<div class="sub-report" data-report="customerSales">
								<i class="fas fa-users"></i>
								<span>المبيعات لكل عميل خلال فترة معينة</span>
							</div>
							<div class="sub-report" data-report="customerSales">
								<i class="fas fa-users"></i>
								<span>المبيعات التي تمت من قبل كل موظف خلال فترة معينة</span>
							</div>
						</div>
					</div>

					<!-- Purchases Report Card -->
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
								<span>حركة المخزون خلال  فترة معينة</span>
							</div>
							<div class="sub-report" data-report="productPurchases">
								<i class="fas fa-box"></i>
								<span>المنتجات التي وصلت إلى حد إعادة الطلب خلال الفترة</span>
							</div>
						</div>
					</div>

					<!-- Expenses Report Card -->
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




			</div>
			<!-- row closed -->
		</div>
		<!-- Container closed -->
	</div>
	<!-- main-content closed -->
@endsection
@section('js')
@endsection