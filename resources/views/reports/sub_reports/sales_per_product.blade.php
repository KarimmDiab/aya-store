@extends('layouts.master3')
<title>معرض أيه - تقرير المبيعات لكل منتج</title>

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
    .table th, .table td {
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

    <h2 class="text-center">📊 تقرير المبيعات لكل منتج خلال الفترة من {{ $startDate }} إلي {{ $endDate }}</h2>

    <!-- Print Button -->
    <button onclick="window.print()" class="btn btn-primary no-print print-btn">🖨️ طباعة التقرير</button>

    <!-- Sales Table -->
    <table class="table table-striped table-bordered text-center mt-4">
        <thead>
            <tr>
                <th>كود المنتج</th>
                <th>اسم المنتج</th>
                <th>الكمية المباعة</th>
                <th>متوسط سعر البيع للقطعة</th>
                <th>إجمالي المبيعات (جنيه)</th>
                <th>متوسط سعر الشراء للقطعة</th>
                <th>صافي الربح</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($total_sales as $report)
            <tr>
                <td>{{ $report->product_id }}</td>
                <td>{{ $report->product_name }}</td>
                <td>{{ round($report->total_sold_quantity) }}</td>
                <td>{{ round($report->avg_selling_price) }} ج.م</td>
                <td class="text-success font-weight-bold">{{ round($report->total_sales) }} ج.م</td>
                <td>{{ round($report->avg_buying_price) }} ج.م</td>
                <td class="text-danger font-weight-bold">{{ round($report->net_profit) }} ج.م</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <!-- Sales Chart -->
    <div class="chart-container">
        <canvas id="salesChart"></canvas>
    </div>
</div>
@endsection

@section('js')
    <!-- Chart.js -->
    <script src="{{ URL::asset('assets/js/chart.js') }}"></script>

    <script>
        // Prepare Data from Blade Variables
        var productNames = @json($total_sales->pluck('product_name'));
        var totalSales = @json($total_sales->pluck('total_sales'));

        // Generate random colors for bars
        function getRandomColor() {
            return `rgba(${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, 0.7)`;
        }

        var colors = productNames.map(() => getRandomColor());

        // Create Chart
        var ctx = document.getElementById('salesChart').getContext('2d');
        var salesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: productNames,
                datasets: [{
                    label: 'إجمالي المبيعات (بالجنيه)',
                    data: totalSales,
                    backgroundColor: colors,
                    borderColor: colors.map(color => color.replace('0.7', '1')),
                    borderWidth: 2,
                    borderRadius: 10
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        labels: {
                            font: {
                                size: 14
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: {
                                size: 13
                            }
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 13
                            }
                        }
                    }
                }
            }
        });
    </script>
@endsection
