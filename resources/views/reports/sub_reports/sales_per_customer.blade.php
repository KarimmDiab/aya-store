@extends('layouts.master3')
<title>معرض أيه - تقرير المبيعات لكل عميل</title>

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
        border-radius: 8px;
        overflow: hidden;
    }

    .table thead {
        background-color: #007bff;
        color: white;
        position: sticky;
        top: 0;
        z-index: 2;
    }

    .table tbody tr:nth-child(even) {
        background-color: #f9f9f9;
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

        /* Hide the last table header (th) */
        table thead th:last-child {
            display: none;
        }

        /* Hide the last column (td) in all rows */
        table tbody td:last-child {
            display: none;
        }
        
        .filter-container{
            display: none !important;
        }

    }
</style>

@section('content')

    <div class="container">
        <div class="report-container">
            <!-- Report Header -->
            <div class="report-header">
                <h2><i class="fas fa-users"></i> تقرير المبيعات لكل عميل خلال الفترة من {{ $startDate }} إلي {{ $endDate }}
                </h2>
                <div class="no-print">
                    <button class="btn export-btn btn-primary" onclick="exportToExcel()">
                        <i class="fas fa-file-excel"></i> تصدير Excel
                    </button>

                    <button class="btn print-btn btn-danger" onclick="printReport()">
                        <i class="fas fa-print"></i> طباعة التقرير
                    </button>
                </div>

            </div>

            <!-- Filters -->
            <div class="filter-container">
                <input type="text" id="searchCustomer" class="form-control" placeholder="🔍 ابحث عن عميل..."
                    style="max-width: 300px;">
                <select id="paymentStatus" class="form-control" style="max-width: 180px;">
                    <option value="">حالة الدفع</option>
                    <option value="paid">مدفوع</option>
                    <option value="pending">قيد الانتظار</option>
                </select>
            </div>

            <!-- Table -->
            <div class="table-container">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>اسم العميل</th>
                            <th>رقم الهاتف</th>
                            <th>عدد الطلبات</th>
                            <th>إجمالي المشتريات</th>
                            <th>آخر حالة دفع</th>
                            <th>الملف الشخصي</th>
                        </tr>
                    </thead>
                    <tbody id="reportTable">
                        @foreach ($customers as $index => $customer)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $customer->customer_name }}</td>
                                <td>{{ $customer->customer_phone }}</td>
                                <td>{{ $customer->invoices_number }}</td>
                                <td>{{ $customer->total_amount }}</td>
                                <td
                                    class="{{ $customer->last_transaction_remaining_amount == 0 ? 'status-paid' : 'status-pending' }}">
                                    {{ $customer->last_transaction_remaining_amount == 0 ? 'مدفوع' : 'قيد الانتظار' }}
                                </td>
                                <td>
                                    <a href="{{ route('customer_profile', ['id' => encrypt($customer->customer_id)]) }}"
                                        class="btn btn-info btn-sm">
                                        الملف الشخصي
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sales Chart -->
        <div class="chart-container">
            <canvas id="salesChart" width="400" height="200"></canvas>
        </div>
    </div>

@endsection

@section('js')
    <script src="{{ URL::asset('assets/js/chart.js') }}"></script>
    <script src="{{URL::asset('assets/js/xlsx.full.min.js')}}"></script>


    <script>
        document.getElementById("searchCustomer").addEventListener("input", filterTable);
        document.getElementById("paymentStatus").addEventListener("change", filterTable);

        function filterTable() {
            let searchValue = document.getElementById("searchCustomer").value.toLowerCase();
            let statusValue = document.getElementById("paymentStatus").value;
            let rows = document.querySelectorAll("#reportTable tr");

            rows.forEach(row => {
                let customerName = row.cells[1].textContent.toLowerCase(); // Adjust index if needed
                let phoneNumber = row.cells[2].textContent.toLowerCase();  // Adjust index if needed
                let paymentStatus = row.cells[5].textContent.trim();       // Adjust index if needed

                let matchesSearch = customerName.includes(searchValue) || phoneNumber.includes(searchValue);
                let matchesStatus = statusValue === "" ||
                    (statusValue === "paid" && paymentStatus === "مدفوع") ||
                    (statusValue === "pending" && paymentStatus === "قيد الانتظار");

                row.style.display = (matchesSearch && matchesStatus) ? "" : "none";
            });
        }



        function exportToExcel() {
            let table = document.getElementById("reportTable");

            if (!table) {
                console.error("Table not found!");
                return;
            }

            let wb = XLSX.utils.book_new();
            let ws = XLSX.utils.table_to_sheet(table);

            XLSX.utils.book_append_sheet(wb, ws, "معرض أيه - تقرير المبيعات لكل عميل");
            XLSX.writeFile(wb, "معرض أيه - تقرير المبيعات لكل عميل.xlsx");
        }




        function printReport() {
            let chartCanvas = document.getElementById("salesChart");
            let originalWidth = chartCanvas.style.width;
            let originalHeight = chartCanvas.style.height;

            // Resize for printing
            chartCanvas.style.width = "100%";
            chartCanvas.style.height = "auto";

            window.print();

            // Restore original size
            chartCanvas.style.width = originalWidth;
            chartCanvas.style.height = originalHeight;
        }



        window.onload = function () {
            var customerNames = @json($customers->pluck('customer_name'));
            var totalSales = @json($customers->pluck('total_amount'));

            if (!customerNames.length || !totalSales.length) {
                console.error("No data available for the chart");
                return;
            }

            function getRandomColor() {
                return `rgba(${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, 0.7)`;
            }

            var colors = customerNames.map(() => getRandomColor());

            var ctx = document.getElementById('salesChart').getContext('2d');
            var salesChart = new Chart(ctx, {
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
        };
    </script>
@endsection