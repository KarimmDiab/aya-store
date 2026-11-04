@extends('layouts.master3')
<title>معرض أيه - تقرير إجمالي المبيعات</title>

<style>
    body {
        background-color: #f8f9fa;
        font-family: 'Tajawal', sans-serif;
    }

    .container {
        background: #ffffff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        margin-top: 20px;
    }

    h2 {
        font-weight: bold;
        color: #343a40;
        padding-bottom: 15px;
        border-bottom: 3px solid #007bff;
    }

    .table th,
    .table td {
        font-weight: bold;
    }

    .table th {
        background-color: #007bff;
        color: white;
        font-size: 16px;
    }

    .table tbody tr:hover {
        background-color: #f1f1f1;
    }

    .chart-container {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        margin-top: 20px;
    }

    .print-btn {
        margin-bottom: 10px;
    }

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
    <div class="container" style="text-align: center; margin-bottom: 15px;">
        <!-- Company Logo -->
        <img src="{{ asset('images/logo/aya_black.png') }}" alt="شعار الشركة" class="company-logo">

        <h2 class="text-center">📊 تقرير إجمالي المبيعات خلال الفترة من {{ $startDate }} إلي {{ $endDate }}</h2>

        <!-- Print Button -->
        <button onclick="window.print()" class="btn btn-primary no-print print-btn">🖨️ طباعة التقرير</button>

        <!-- small dashboard -->
        <div class="row text-center">
            <div class="col-md-4 col-sm-4">
                <div class="card p-3 bg-light">
                    <h5>إجمالي المبيعات</h5>
                    <h3 id="totalSales" class="text-primary">{{ round($reportStatics->total_amount_of_invoices) }} ج.م</h3>
                </div>
            </div>
            <div class="col-md-4 col-sm-4">
                <div class="card p-3 bg-light">
                    <h5>إجمالي عدد الفواتير</h5>
                    <h3 id="totalOrders" class="text-success">{{ round($reportStatics->number_of_invoices) }}</h3>
                </div>
            </div>
            <div class="col-md-4 col-sm-4">
                <div class="card p-3 bg-light">
                    <h5>متوسط الفاتورة</h5>
                    <h3 id="avgOrderValue" class="text-danger">{{ round($reportStatics->avg_amount_of_invoices) }} ج.م</h3>
                </div>
            </div>
        </div>

        <!-- Sales Table -->
        <table class="table table-striped table-bordered text-center mt-4">
            <thead>
                <tr>
                    <th>التارريخ</th>
                    <th>عدد الفواتير</th>
                    <th>المبيعات</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($total_sales as $report)
                    <tr>
                        <td>{{ $report->invoice_date }}</td>
                        <td class="text-success font-weight-bold">{{ round($report->number_of_invoices) }}</td>
                        <td class="text-danger font-weight-bold">{{ round($report->total_amount_of_invoices) }} ج.م</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Sales Chart -->
        <div class="chart-container">
            <canvas id="salesChart" class="mt-4"></canvas>
        </div>
    </div>
@endsection

@section('js')
    <!-- Chart.js -->
    <script src="{{ URL::asset('assets/js/chart.js') }}"></script>
    <script>
    var salesData = @json($total_sales);
</script>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        renderChart(salesData);
    });

    function renderChart(salesData) {
        const ctx = document.getElementById("salesChart").getContext("2d");

        // Extract data from Laravel JSON
        const labels = salesData.map(item => item.invoice_date);
        const sales = salesData.map(item => item.total_amount_of_invoices);

        // Destroy existing chart instance if exists
        if (window.salesChartInstance) {
            window.salesChartInstance.destroy();
        }

        // Create a new chart
        window.salesChartInstance = new Chart(ctx, {
            type: "line",
            data: {
                labels: labels,
                datasets: [{
                    label: "المبيعات اليومية (ج.م)",
                    data: sales,
                    borderColor: "#007bff",
                    backgroundColor: "rgba(0, 123, 255, 0.2)",
                    borderWidth: 2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
</script>

@endsection