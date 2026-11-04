@extends('layouts.master3')
<title>معرض أيه - تقرير المبيعات لكل موظف</title>

<style>
    body {
        background-color: #f8f9fa;
        font-family: 'Tajawal', sans-serif;
        direction: rtl;
        text-align: right;
    }

    .report-container {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        margin-top: 30px;
    }

    .report-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 3px solid #007bff;
        padding-bottom: 10px;
    }

    .report-header h2 {
        font-size: 24px;
        font-weight: bold;
        color: #007bff;
        display: flex;
        align-items: center;
    }

    .filter-container {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 15px;
    }

    .filter-container input,
    .filter-container select,
    .filter-container button {
        height: 40px;
        border-radius: 8px;
    }

    .table-container {
        margin-top: 20px;
        overflow-x: auto;
        max-height: 450px;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    }

    .table thead {
        background: linear-gradient(45deg, #007bff, #0056b3);
        color: white;
    }

    .table thead th {
        padding: 15px;
        text-align: center;
        font-size: 16px;
    }

    .table tbody tr {
        transition: all 0.3s ease-in-out;
    }

    .table tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .table tbody tr:hover {
        background-color: #dfe8ff;
    }

    .table tbody td {
        padding: 12px;
        text-align: center;
        font-size: 15px;
        border-bottom: 1px solid #ddd;
    }

    .status-paid {
        color: green;
        font-weight: bold;
    }

    .status-pending {
        color: orange;
        font-weight: bold;
    }

    @media print {
        .no-print {
            display: none !important;
        }

        table thead th:last-child {
            display: none;
        }

        table tbody td:last-child {
            display: none;
        }

        .filter-container {
            display: none !important;
        }   
    }
</style>

@section('content')
    <div class="container">
        <div class="report-container">
            <div class="report-header">
                <h2><i class="fas fa-users"></i> تقرير المبيعات لكل عميل خلال الفترة من {{ $startDate }} إلي {{ $endDate }}</h2>
                <div class="no-print">
                    <button class="btn export-btn btn-primary" onclick="exportToExcel()">
                        <i class="fas fa-file-excel"></i> تصدير Excel
                    </button>
                    <button class="btn print-btn btn-danger" onclick="printReport()">
                        <i class="fas fa-print"></i> طباعة التقرير
                    </button>
                </div>
            </div>

            <div class="filter-container">
                <input type="text" id="searchCustomer" class="form-control" placeholder="🔍 ابحث عن موظف..." style="max-width: 300px;">
            </div>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>اسم الموظف</th>
                            <th>رقم الهاتف</th>
                            <th>الإيميل</th>
                            <th>عدد فواتير المبيعات</th>
                            <th>إجمالي المبلغ</th>
                        </tr>
                    </thead>
                    <tbody id="reportTable">
                        @foreach ($salesReportUser as $index => $user)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $user->user_name }}</td>
                                <td>{{ $user->user_phone }}</td>
                                <td>{{ $user->user_email }}</td>
                                <td>{{ $user->count_sales_invoice }}</td>
                                <td>{{ $user->sum_invoices_amount }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="chart-container">
            <canvas id="salesChart" width="400" height="200"></canvas>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ URL::asset('assets/js/chart.js') }}"></script>
    <script src="{{URL::asset('assets/js/xlsx.full.min.js')}}"></script>


    <script>
       document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchCustomer");
    const statusSelect = document.getElementById("paymentStatus");
    const reportTable = document.getElementById("reportTable");

    if (searchInput) searchInput.addEventListener("input", filterTable);
    if (statusSelect) statusSelect.addEventListener("change", filterTable);

    function filterTable() {
        let searchValue = searchInput.value.toLowerCase();
        let statusValue = statusSelect.value;
        let rows = reportTable.querySelectorAll("tr");

        rows.forEach(row => {
            let customerName = row.cells[1]?.textContent.toLowerCase() || ""; // اسم العميل
            let phoneNumber = row.cells[2]?.textContent.toLowerCase() || "";  // رقم الهاتف
            let paymentStatus = row.cells[5]?.textContent.trim() || "";       // حالة الدفع

            let matchesSearch = customerName.includes(searchValue) || phoneNumber.includes(searchValue);
            let matchesStatus = statusValue === "" ||
                (statusValue === "paid" && paymentStatus === "مدفوع") ||
                (statusValue === "pending" && paymentStatus === "قيد الانتظار");

            row.style.display = (matchesSearch && matchesStatus) ? "" : "none";
        });
    }

    // تصدير البيانات إلى Excel
    function exportToExcel() {
        let table = document.querySelector(".table");

        if (!table) {
            console.error("لم يتم العثور على الجدول!");
            return;
        }

        let wb = XLSX.utils.book_new();
        let ws = XLSX.utils.table_to_sheet(table);

        XLSX.utils.book_append_sheet(wb, ws, "تقرير المبيعات لكل عميل");
        XLSX.writeFile(wb, "تقرير_المبيعات.xlsx");
    }

    document.querySelector(".export-btn")?.addEventListener("click", exportToExcel);

    // طباعة التقرير
    function printReport() {
        window.print();
    }

    document.querySelector(".print-btn")?.addEventListener("click", printReport);

    // رسم المخطط البياني
    function drawChart() {
        let customerNames = @json($salesReportUser->pluck('user_name'));
        let totalSales = @json($salesReportUser->pluck('sum_invoices_amount'));

        if (!customerNames.length || !totalSales.length) {
            console.warn("لا توجد بيانات متاحة للرسم البياني");
            return;
        }

        let colors = customerNames.map(() => 
            `rgba(${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, 0.7)`
        );

        let ctx = document.getElementById('salesChart')?.getContext('2d');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: customerNames,
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
                            font: { size: 14 }
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { font: { size: 13 } } },
                    x: { ticks: { font: { size: 13 } } }
                }
            }
        });
    }

    drawChart();
});

    </script>
@endsection