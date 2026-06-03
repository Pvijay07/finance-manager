@extends('Manager.layouts.app')
@section('content')
    <!-- Reports & Analytics Page -->
    <div id="reports" class="">
        <form id="reportFilterForm" method="GET" action="{{ route('manager.reports') }}">
            <div class="filter-bar">
                <div class="filter-group">
                    <div class="filter-label">Date Range</div>
                    <select name="date_range" id="dateRange" onchange="toggleCustomDate()">
                        <option value="today" {{ $dateRange == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="this_week" {{ $dateRange == 'this_week' ? 'selected' : '' }}>This Week</option>
                        <option value="this_month" {{ $dateRange == 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="this_quarter" {{ $dateRange == 'this_quarter' ? 'selected' : '' }}>This Quarter
                        </option>
                        <option value="this_year" {{ $dateRange == 'this_year' ? 'selected' : '' }}>This Year</option>
                        <option value="custom" {{ $dateRange == 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>

                <div id="customDateRange"
                    style="{{ $dateRange == 'custom' ? 'display: flex;' : 'display: none;' }} gap: 10px;">
                    <div class="filter-group">
                        <div class="filter-label">From</div>
                        <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}"
                            class="form-control">
                    </div>
                    <div class="filter-group">
                        <div class="filter-label">To</div>
                        <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="form-control">
                    </div>
                </div>

                <div class="filter-group">
                    <div class="filter-label">Company</div>
                    <select name="company">
                        <option value="all" {{ $companyId == 'all' ? 'selected' : '' }}>All Companies</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}" {{ $companyId == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <div class="filter-label">Output Format</div>
                    <select name="output">
                        <option value="table" {{ $outputFormat == 'table' ? 'selected' : '' }}>Table</option>
                        <option value="graph" {{ $outputFormat == 'graph' ? 'selected' : '' }}>Graph</option>
                        <option value="both" {{ $outputFormat == 'both' ? 'selected' : '' }}>Both</option>
                    </select>
                </div>
                <div class="filter-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                    <button type="button" class="btn btn-outline" onclick="resetFilters()">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </div>
        </form>

        <div class="dashboard-grid">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Profit & Loss Report</div>
                    <div class="card-icon profit">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="card-value">
                    @if ($reports['summary']['net_profit'] >= 0)
                        ₹{{ number_format($reports['summary']['net_profit'], 0) }}
                    @else
                        -₹{{ number_format(abs($reports['summary']['net_profit']), 0) }}
                    @endif
                </div>
                <div>Company-wise P&L for {{ $reports['date_range_label'] }}</div>
                <div class="card-footer">
                    <small>
                        Income: ₹{{ number_format($reports['summary']['total_income'], 0) }}<br>
                        Expense: ₹{{ number_format($reports['summary']['total_expense'], 0) }}
                    </small>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title">Expense Breakdown</div>
                    <div class="card-icon expense">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                </div>
                <div class="card-value">{{ $reports['summary']['expense_count'] }} Expenses</div>
                <div>Expense by category for {{ $reports['date_range_label'] }}</div>
                <div class="card-footer">
                    <small>
                        Top Categories:<br>
                        @foreach (array_slice($reports['category_wise'], 0, 2) as $category => $data)
                            {{ $category }}: ₹{{ number_format($data['amount'], 0) }}<br>
                        @endforeach
                    </small>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title">Upcoming Payments</div>
                    <div class="card-icon upcoming">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
                <div class="card-value">{{ count($reports['upcoming_payments']) }} Payments</div>
                <div>Upcoming expense payments</div>
                <div class="card-footer">
                    <small>
                        @if (count($reports['upcoming_payments']) > 0)
                            @php
                                $totalUpcoming = 0;
                                foreach ($reports['upcoming_payments'] as $payment) {
                                    $totalUpcoming += $payment['amount'];
                                }
                            @endphp
                            Total: ₹{{ number_format($totalUpcoming, 0) }}
                        @else
                            No upcoming payments
                        @endif
                    </small>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title">Non-standard Expenses</div>
                    <div class="card-icon warning">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="card-value">{{ count($reports['non_standard_expenses']) }} Expenses</div>
                <div>One-time costs summary</div>
                <div class="card-footer">
                    <small>
                        @if (count($reports['non_standard_expenses']) > 0)
                            Total:
                            ₹{{ number_format(array_sum(array_column($reports['non_standard_expenses'], 'amount')), 0) }}
                        @else
                            No non-standard expenses
                        @endif
                    </small>
                </div>
            </div>
        </div>

        @if ($outputFormat == 'graph' || $outputFormat == 'both')
            <div class="chart-container">
                <div class="chart-header">
                    <div class="chart-title">Profit & Loss Report - {{ $reports['date_range_label'] }}</div>
                    <div>
                        <button class="btn btn-primary" onclick="exportChart()">
                            <i class="fas fa-download"></i> Export as PDF
                        </button>
                    </div>
                </div>
                <div class="chart">
                    <canvas id="profitLossChart"></canvas>
                </div>
            </div>
        @endif

        @if ($outputFormat == 'table' || $outputFormat == 'both')
            <div class="table-container">
                <div class="table-header">
                    <div class="table-title">Profit & Loss Details - {{ $reports['date_range_label'] }}</div>
                    <div class="table-actions">
                        <form action="{{ route('manager.reports.export.excel') }}" method="GET" style="display: inline;">
                            <input type="hidden" name="date_range" value="{{ $dateRange }}">
                            <input type="hidden" name="company" value="{{ $companyId }}">
                            <input type="hidden" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
                            <input type="hidden" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-download"></i> Export as Excel
                            </button>
                        </form>
                    </div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Total Income</th>
                            <th>Total Expense</th>
                            <th>Net Profit/Loss</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $grandTotalIncome = 0;
                            $grandTotalExpense = 0;
                            $grandNetProfit = 0;
                        @endphp

                        @foreach ($reports['company_wise'] as $companyName => $data)
                            @php
                                $netProfit = $data['income'] - $data['expense'];
                                $grandTotalIncome += $data['income'];
                                $grandTotalExpense += $data['expense'];
                                $grandNetProfit += $netProfit;
                            @endphp
                            <tr>
                                <td>{{ $companyName }}</td>
                                <td>₹{{ number_format($data['income'], 0) }}</td>
                                <td>₹{{ number_format($data['expense'], 0) }}</td>
                                <td class="{{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                                    ₹{{ number_format($netProfit, 0) }}
                                </td>
                            </tr>
                        @endforeach

                        <tr style="font-weight: bold; background-color: #f8f9fa;">
                            <td>Total</td>
                            <td>₹{{ number_format($grandTotalIncome, 0) }}</td>
                            <td>₹{{ number_format($grandTotalExpense, 0) }}</td>
                            <td class="{{ $grandNetProfit >= 0 ? 'text-success' : 'text-danger' }}">
                                ₹{{ number_format($grandNetProfit, 0) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Additional Tables -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="table-container">
                        <div class="table-header">
                            <div class="table-title">Top Expense Categories</div>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Amount</th>
                                    <th>Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reports['category_wise'] as $categoryName => $data)
                                    <tr>
                                        <td>{{ $categoryName }}</td>
                                        <td>₹{{ number_format($data['amount'], 0) }}</td>
                                        <td>{{ $data['count'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="table-container">
                        <div class="table-header">
                            <div class="table-title">Upcoming Payments</div>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Expense</th>
                                    <th>Company</th>
                                    <th>Amount</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reports['upcoming_payments'] as $payment)
                                    @php
                                        // Handle missing due_day or due_date
                                        $dueInfo = '';
                                        if (!empty($payment['expense']->due_date)) {
                                            $dueInfo = date('d M Y', strtotime($payment['expense']->due_date));
                                        } elseif (!empty($payment['due_day']) && !empty($payment['frequency'])) {
                                            $dueInfo =
                                                'Day ' .
                                                $payment['due_day'] .
                                                ' (' .
                                                ucfirst($payment['frequency']) .
                                                ')';
                                        } else {
                                            $dueInfo = 'Not set';
                                        }

                                        $companyName = $payment['expense']->company
                                            ? $payment['expense']->company->name
                                            : 'N/A';
                                    @endphp
                                    <tr>
                                        <td>{{ $payment['expense']->expense_name }}</td>
                                        <td>{{ $companyName }}</td>
                                        <td>₹{{ number_format($payment['amount'], 0) }}</td>
                                        <td>{{ $dueInfo }}</td>
                                        <td>
                                            <span
                                                class="status {{ $payment['expense']->status == 'pending' ? 'pending' : 'active' }}">
                                                {{ ucfirst($payment['expense']->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No upcoming payments</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function toggleCustomDate() {
            const dateRange = document.getElementById('dateRange').value;
            const customDateRange = document.getElementById('customDateRange');

            if (dateRange === 'custom') {
                customDateRange.style.display = 'flex';
            } else {
                customDateRange.style.display = 'none';
            }
        }

        function resetFilters() {
            document.getElementById('reportFilterForm').reset();
            document.getElementById('dateRange').value = 'this_month';
            document.getElementById('customDateRange').style.display = 'none';
            document.getElementById('reportFilterForm').submit();
        }

        // Initialize Chart
        @if ($outputFormat == 'graph' || $outputFormat == 'both')
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('profitLossChart').getContext('2d');

                // Prepare data for chart
                const companies = @json(array_keys($reports['company_wise']));
                const incomes = @json(array_column($reports['company_wise'], 'income'));
                const expenses = @json(array_column($reports['company_wise'], 'expense'));

                const chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: companies,
                        datasets: [{
                                label: 'Income',
                                data: incomes,
                                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Expense',
                                data: expenses,
                                backgroundColor: 'rgba(255, 99, 132, 0.6)',
                                borderColor: 'rgba(255, 99, 132, 1)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: true,
                                text: 'Company-wise Income vs Expense'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '₹' + value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });

                // Pie chart for categories
                @if (count($reports['category_wise']) > 0)
                    const categoryCtx = document.createElement('canvas');
                    categoryCtx.id = 'categoryChart';
                    document.querySelector('.chart-container').appendChild(categoryCtx);

                    const categoryLabels = @json(array_keys($reports['category_wise']));
                    const categoryData = @json(array_column($reports['category_wise'], 'amount'));

                    const categoryChart = new Chart(categoryCtx, {
                        type: 'pie',
                        data: {
                            labels: categoryLabels,
                            datasets: [{
                                data: categoryData,
                                backgroundColor: [
                                    'rgba(255, 99, 132, 0.6)',
                                    'rgba(54, 162, 235, 0.6)',
                                    'rgba(255, 206, 86, 0.6)',
                                    'rgba(75, 192, 192, 0.6)',
                                    'rgba(153, 102, 255, 0.6)'
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'right',
                                },
                                title: {
                                    display: true,
                                    text: 'Expense by Category'
                                }
                            }
                        }
                    });
                @endif
            });

            function exportChart() {
                // Implement chart export to PDF
                alert('PDF export functionality would be implemented here');
            }
        @endif
    </script>

    <style>
        .filter-bar {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 20px;
            padding: 15px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filter-label {
            font-size: 12px;
            font-weight: 600;
            color: #6c757d;
        }

        .filter-group select,
        .filter-group input {
            padding: 6px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            min-width: 150px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .card-title {
            font-weight: 600;
            font-size: 16px;
            color: #333;
        }

        .card-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        .card-icon.profit {
            background: #10b981;
        }

        .card-icon.expense {
            background: #f59e0b;
        }

        .card-icon.upcoming {
            background: #3b82f6;
        }

        .card-icon.warning {
            background: #ef4444;
        }

        .card-value {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #1f2937;
        }

        .card-footer {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
        }

        .chart-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .chart-title {
            font-weight: 600;
            font-size: 18px;
            color: #333;
        }

        .table-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .table-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-title {
            font-weight: 600;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            padding: 12px 15px;
            text-align: left;
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
        }

        table td {
            padding: 12px 15px;
            border-bottom: 1px solid #dee2e6;
        }

        .text-success {
            color: #10b981;
        }

        .text-danger {
            color: #ef4444;
        }
    </style>
@endsection
