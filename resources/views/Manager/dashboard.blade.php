@extends('Manager.layouts.app')
@section('content')
    <!-- Dashboard Page -->
    <div id="dashboard" class="page active">
        <!-- Page Title -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 style="font-weight: 800; color: #0f172a; font-size: 2rem; letter-spacing: -0.75px;">Manager Dashboard</h1>
                <div style="font-size: 0.95rem; color: #64748b; margin-top: 4px;">Overview of financials and analytics</div>
            </div>
        </div>

        <!-- Bento Filter Bar -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-md bg-surface-container-lowest p-md rounded-xl card-shadow border border-outline-variant mb-lg">
            <!-- Date Range -->
            <div class="flex flex-col gap-xs">
                <label class="font-label-md text-[10px] uppercase text-on-surface-variant px-1">Date Range</label>
                <select id="dateRange" onchange="updateFilters()" class="border-outline-variant rounded-lg font-body-sm text-body-sm bg-surface-container-low focus:ring-secondary py-2 px-3 w-full border">
                    <option value="today" {{ $dateRange == 'today' ? 'selected' : '' }}>Today</option>
                    <option value="week" {{ $dateRange == 'week' ? 'selected' : '' }}>This Week</option>
                    <option value="month" {{ $dateRange == 'month' ? 'selected' : '' }}>This Month</option>
                </select>
            </div>
            <!-- Company -->
            <div class="flex flex-col gap-xs">
                <label class="font-label-md text-[10px] uppercase text-on-surface-variant px-1">Company</label>
                <select id="companyFilter" onchange="updateFilters()" class="border-outline-variant rounded-lg font-body-sm text-body-sm bg-surface-container-low focus:ring-secondary py-2 px-3 w-full border">
                    <option value="">All Companies</option>
                    @foreach($companies as $company)
                    <option value="{{ $company->id }}" {{ $companyId == $company->id ? 'selected' : '' }}>
                        {{ $company->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <!-- View -->
            <div class="flex flex-col gap-xs">
                <label class="font-label-md text-[10px] uppercase text-on-surface-variant px-1">View</label>
                <select id="viewType" onchange="updateFilters()" class="border-outline-variant rounded-lg font-body-sm text-body-sm bg-surface-container-low focus:ring-secondary py-2 px-3 w-full border">
                    <option value="summary" {{ $viewType == 'summary' ? 'selected' : '' }}>Summary</option>
                    <option value="detailed" {{ $viewType == 'detailed' ? 'selected' : '' }}>Detailed</option>
                </select>
            </div>
            <!-- Refresh -->
            <div class="flex items-end">
                <button type="button" onclick="window.location.reload()" class="w-full h-[40px] border border-secondary text-secondary bg-transparent font-label-md text-label-md rounded-lg flex items-center justify-center gap-sm hover:bg-secondary-fixed transition-colors">
                    <span class="material-symbols-outlined text-[20px]">sync</span> Refresh
                </button>
            </div>
        </section>

        <!-- Summary Cards Row -->
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3 mb-4">
            <!-- Total Income -->
            <div class="col">
                <div class="summary-card flex flex-col justify-between hover:border-success transition-all bg-surface-container-lowest p-md rounded-xl card-shadow border border-outline-variant h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="summary-header">
                            <p class="font-label-md text-label-md text-on-surface-variant mb-0 uppercase">Total Income</p>
                        </div>
                        <div class="p-sm rounded-lg flex items-center justify-center" style="width: 40px; height: 40px; background-color: rgba(34, 197, 94, 0.1);">
                            <span class="material-symbols-outlined text-success">arrow_upward</span>
                        </div>
                    </div>
                    <div class="summary-body">
                        <h4 class="font-headline-md text-headline-md text-success mt-xs mb-2">₹{{ number_format($currentStats['totalIncome'], 2) }}</h4>
                        <div class="mt-md d-flex justify-content-between align-items-center gap-sm">
                            <span class="font-data-mono text-data-mono text-on-surface-variant">{{ $currentStats['periodLabel'] }}</span>
                            @php
                                $incomeChange = $previousStats['totalIncome'] > 0 
                                    ? (($currentStats['totalIncome'] - $previousStats['totalIncome']) / $previousStats['totalIncome']) * 100 
                                    : 0;
                            @endphp
                            <span class="font-label-sm text-label-sm {{ $incomeChange >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $incomeChange >= 0 ? '+' : '' }}{{ number_format($incomeChange, 1) }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
 <div class="col">
                <div class="summary-card flex flex-col justify-between hover:border-success transition-all bg-surface-container-lowest p-md rounded-xl card-shadow border border-outline-variant h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="summary-header">
                            <p class="font-label-md text-label-md text-on-surface-variant mb-0 uppercase">Total Receivable Income</p>
                        </div>
                        <div class="p-sm rounded-lg flex items-center justify-center" style="width: 40px; height: 40px; background-color: rgba(34, 197, 94, 0.1);">
                            <span class="material-symbols-outlined text-success">arrow_upward</span>
                        </div>
                    </div>
                    <div class="summary-body">
                        <h4 class="font-headline-md text-headline-md text-success mt-xs mb-2">₹{{ number_format($currentStats['totalReceivableIncome'], 2) }}</h4>
                        <div class="mt-md d-flex justify-content-between align-items-center gap-sm">
                            <span class="font-data-mono text-data-mono text-on-surface-variant">{{ $currentStats['periodLabel'] }}</span>
                            @php
                                $incomeChange = $previousStats['totalReceivableIncome'] > 0 
                                    ? (($currentStats['totalReceivableIncome'] - $previousStats['totalReceivableIncome']) / $previousStats['totalReceivableIncome']) * 100 
                                    : 0;
                            @endphp
                            <span class="font-label-sm text-label-sm {{ $incomeChange >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $incomeChange >= 0 ? '+' : '' }}{{ number_format($incomeChange, 1) }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Total Expenses -->
            <div class="col">
                <div class="summary-card flex flex-col justify-between hover:border-danger transition-all bg-surface-container-lowest p-md rounded-xl card-shadow border border-outline-variant h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="summary-header">
                            <p class="font-label-md text-label-md text-on-surface-variant mb-0 uppercase">Total Expenses</p>
                        </div>
                        <div class="p-sm rounded-lg flex items-center justify-center" style="width: 40px; height: 40px; background-color: rgba(239, 68, 68, 0.1);">
                            <span class="material-symbols-outlined text-danger">arrow_downward</span>
                        </div>
                    </div>
                    <div class="summary-body">
                        <h4 class="font-headline-md text-headline-md text-danger mt-xs mb-2">₹{{ number_format($currentStats['totalExpenses'], 2) }}</h4>
                        <div class="mt-md d-flex justify-content-between align-items-center gap-sm">
                            <span class="font-data-mono text-data-mono text-on-surface-variant">{{ $currentStats['periodLabel'] }}</span>
                            @php
                                $expenseChange = $previousStats['totalExpenses'] > 0 
                                    ? (($currentStats['totalExpenses'] - $previousStats['totalExpenses']) / $previousStats['totalExpenses']) * 100 
                                    : 0;
                            @endphp
                            <span class="font-label-sm text-label-sm {{ $expenseChange <= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $expenseChange >= 0 ? '+' : '' }}{{ number_format($expenseChange, 1) }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
 <div class="col">
                <div class="summary-card flex flex-col justify-between hover:border-danger transition-all bg-surface-container-lowest p-md rounded-xl card-shadow border border-outline-variant h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="summary-header">
                            <p class="font-label-md text-label-md text-on-surface-variant mb-0 uppercase">Total Payable Expenses</p>
                        </div>
                        <div class="p-sm rounded-lg flex items-center justify-center" style="width: 40px; height: 40px; background-color: rgba(239, 68, 68, 0.1);">
                            <span class="material-symbols-outlined text-danger">arrow_downward</span>
                        </div>
                    </div>
                    <div class="summary-body">
                        <h4 class="font-headline-md text-headline-md text-danger mt-xs mb-2">₹{{ number_format($currentStats['totalPayableExpenses'], 2) }}</h4>
                        <div class="mt-md d-flex justify-content-between align-items-center gap-sm">
                            <span class="font-data-mono text-data-mono text-on-surface-variant">{{ $currentStats['periodLabel'] }}</span>
                            @php
                                $expenseChange = $previousStats['totalPayableExpenses'] > 0 
                                    ? (($currentStats['totalPayableExpenses'] - $previousStats['totalPayableExpenses']) / $previousStats['totalPayableExpenses']) * 100 
                                    : 0;
                            @endphp
                            <span class="font-label-sm text-label-sm {{ $expenseChange <= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $expenseChange >= 0 ? '+' : '' }}{{ number_format($expenseChange, 1) }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Net Profit -->
            <div class="col">
                <div class="summary-card flex flex-col justify-between hover:border-info transition-all bg-surface-container-lowest p-md rounded-xl card-shadow border border-outline-variant h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="summary-header">
                            <p class="font-label-md text-label-md text-on-surface-variant mb-0 uppercase">Net Profit</p>
                        </div>
                        <div class="p-sm rounded-lg flex items-center justify-center" style="width: 40px; height: 40px; background-color: rgba(14, 165, 233, 0.1);">
                            <span class="material-symbols-outlined text-info">monitoring</span>
                        </div>
                    </div>
                    <div class="summary-body">
                        <h4 class="font-headline-md text-headline-md {{ $currentStats['netProfit'] >= 0 ? 'text-success' : 'text-danger' }} mt-xs mb-2">
                            ₹{{ number_format(abs($currentStats['netProfit']), 2) }}
                        </h4>
                        <div class="mt-md d-flex justify-content-between align-items-center gap-sm">
                            <span class="font-data-mono text-data-mono text-on-surface-variant">{{ $currentStats['periodLabel'] }}</span>
                            @php
                                $profitChange = $previousStats['netProfit'] != 0 
                                    ? (($currentStats['netProfit'] - $previousStats['netProfit']) / abs($previousStats['netProfit'])) * 100 
                                    : ($currentStats['netProfit'] > 0 ? 100 : -100);
                            @endphp
                            <span class="font-label-sm text-label-sm {{ $profitChange >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $profitChange >= 0 ? '+' : '' }}{{ number_format($profitChange, 1) }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Payments -->
            <div class="col">
                <div class="summary-card flex flex-col justify-between hover:border-warning transition-all bg-surface-container-lowest p-md rounded-xl card-shadow border border-outline-variant h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="summary-header">
                            <p class="font-label-md text-label-md text-on-surface-variant mb-0 uppercase">Upcoming Payments</p>
                        </div>
                        <div class="p-sm rounded-lg flex items-center justify-center" style="width: 40px; height: 40px; background-color: rgba(245, 158, 11, 0.1);">
                            <span class="material-symbols-outlined text-warning">event</span>
                        </div>
                    </div>
                    <div class="summary-body">
                        <h4 class="font-headline-md text-headline-md text-warning mt-xs mb-2">₹{{ number_format($currentStats['upcomingPayments'], 2) }}</h4>
                        <div class="mt-md d-flex justify-content-between align-items-center gap-sm">
                            <span class="font-data-mono text-data-mono text-on-surface-variant">This Month</span>
                            @php
                                $upcomingCount = $immediatePayments->count();
                            @endphp
                            <span class="font-label-sm text-label-sm text-warning">{{ $upcomingCount }} Due</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Immediate Payments Table -->
        <!-- <div class="table-container">
            <div class="table-header">
                <div class="table-title">Immediate Payments (Today + Next 3 Days)</div>
                <div class="table-actions">
                    <a href="{{ route('standard-expenses.index') }}" class="btn btn-outline">
                        <i class="fas fa-list"></i> View All Expenses
                    </a>
                    <a href="{{ route('non-standard-expenses.index') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Expense
                    </a>
                </div>
            </div>
            @if($immediatePayments->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Company</th>
                        <th>Expense Name</th>
                        <th>Type</th>
                        <th>Party</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($immediatePayments as $payment)
                    <tr>
                        <td>{{ $payment['date'] }}</td>
                        <td>{{ $payment['company'] }}</td>
                        <td>{{ $payment['name'] }}</td>
                        <td>{{ $payment['type'] }}</td>
                        <td>{{ $payment['party'] }}</td>
                        <td>₹{{ number_format($payment['amount'], 2) }}</td>
                        <td>
                            @php
                                $statusColors = [
                                    'pending' => 'pending',
                                    'upcoming' => 'active',
                                    'overdue' => 'overdue'
                                ];
                            @endphp
                            <span class="status {{ $statusColors[$payment['status']] ?? 'active' }}">
                                {{ ucfirst($payment['status']) }}
                            </span>
                        </td>
                        <td>
                            @if($payment['status'] !== 'paid')
                            <button class="btn btn-success btn-sm mark-paid-btn" 
                                    onclick="markAsPaid({{ $payment['id'] }})">
                                <i class="fas fa-check"></i> Mark Paid
                            </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="text-center py-4">
                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                <p class="text-muted">No immediate payments due</p>
            </div>
            @endif
        </div> -->

        <!-- Upcoming Debits & Credits -->
        <!-- <div class="dashboard-grid">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Upcoming Debits</div>
                    <div class="card-icon expense">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                </div>
                <div class="card-value">₹{{ number_format($upcomingStats['debits']['amount'], 2) }}</div>
                <div>Next 30 days</div>
                <div class="card-footer">
                    <span>{{ $upcomingStats['debits']['count'] }} payments</span>
                    <a href="{{ route('income.upcoming') }}" class="btn btn-outline btn-sm">
                        View All
                    </a>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Upcoming Credits</div>
                    <div class="card-icon income">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                </div>
                <div class="card-value">₹{{ number_format($upcomingStats['credits']['amount'], 2) }}</div>
                <div>Next 30 days</div>
                <div class="card-footer">
                    <span>{{ $upcomingStats['credits']['count'] }} receipts</span>
                    <a href="{{ route('income.upcoming') }}" class="btn btn-outline btn-sm">
                        View All
                    </a>
                </div>
            </div>
        </div> -->

        <!-- Company-wise Profit/Loss Chart -->
        <!-- <div class="chart-container">
            <div class="chart-header">
                <div class="chart-title">Company-wise Profit & Loss</div>
                <div>
                    <select id="chartPeriod" onchange="updateChart()">
                        <option value="month">This Month</option>
                        <option value="last_month">Last Month</option>
                        <option value="quarter">This Quarter</option>
                        <option value="year">This Year</option>
                    </select>
                </div>
            </div>
            <div class="chart">
                <canvas id="profitLossChart"></canvas>
            </div>
        </div> -->

        <!-- Notifications Panel -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Notifications & Alerts</div>
                <div>
                    <span class="badge bg-danger">{{ count($notifications) }} New</span>
                </div>
            </div>
            <div style="padding: 10px 0;">
                @foreach($notifications as $notification)
                <div style="display: flex; align-items: center; gap: 10px; padding: 10px; border-bottom: 1px solid #eee;">
                    <div style="color: var(--{{ $notification['type'] }});">
                        <i class="fas fa-{{ $notification['icon'] }}"></i>
                    </div>
                    <div style="flex: 1;">{{ $notification['message'] }}</div>
                    @if($notification['link'] != '#')
                    <a href="{{ $notification['link'] }}" class="btn btn-sm btn-outline">
                        View
                    </a>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    function updateFilters() {
        const dateRange = document.getElementById('dateRange').value;
        const companyId = document.getElementById('companyFilter').value;
        const viewType = document.getElementById('viewType').value;
        
        const params = new URLSearchParams();
        params.append('range', dateRange);
        if (companyId) params.append('company', companyId);
        params.append('view', viewType);
        
        window.location.href = '{{ route("manager.dashboard") }}?' + params.toString();
    }
    
    function markAsPaid(expenseId) {
        if (confirm('Mark this expense as paid?')) {
            fetch(`{{ url('/') }}/manager/standard-expenses/${expenseId}/mark-paid`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error marking as paid');
            });
        }
    }
    
    // Initialize Chart
    document.addEventListener('DOMContentLoaded', function() {
        const companyData = @json($companyProfitLoss);
        
        const ctx = document.getElementById('profitLossChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: companyData.map(c => c.name),
                datasets: [
                    {
                        label: 'Income',
                        data: companyData.map(c => c.income),
                        backgroundColor: 'rgba(40, 167, 69, 0.7)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Expenses',
                        data: companyData.map(c => c.expenses),
                        backgroundColor: 'rgba(220, 53, 69, 0.7)',
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Profit/Loss',
                        data: companyData.map(c => c.profit),
                        type: 'line',
                        fill: false,
                        borderColor: 'rgba(23, 162, 184, 1)',
                        backgroundColor: 'rgba(23, 162, 184, 0.1)',
                        borderWidth: 2,
                        tension: 0.1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += '₹' + context.parsed.y.toLocaleString('en-IN', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString('en-IN');
                            }
                        }
                    }
                }
            }
        });
        
        function updateChart() {
            // You can implement AJAX to update chart data based on period
            const period = document.getElementById('chartPeriod').value;
            alert('Chart period changed to: ' + period + '. Implement AJAX to update chart data.');
        }
    });
    </script>
    
    <style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .card-title {
        font-weight: 600;
        color: #333;
    }
    
    .card-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }
    
    .card-icon.income { background: #28a745; }
    .card-icon.expense { background: #dc3545; }
    .card-icon.profit { background: #17a2b8; }
    .card-icon.upcoming { background: #ffc107; }
    
    .card-value {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 5px;
    }
    
    .card-footer {
        margin-top: 15px;
        padding-top: 10px;
        border-top: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 14px;
    }
    
    .table-container {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
    
    .table-actions {
        display: flex;
        gap: 10px;
    }
    
    .chart-container {
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    
    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .chart-title {
        font-weight: 600;
        color: #333;
    }
    
    .status {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .status.active { background: #d4edda; color: #155724; }
    .status.pending { background: #fff3cd; color: #856404; }
    .status.overdue { background: #f8d7da; color: #721c24; }
    
    .text-success { color: #28a745 !important; }
    .text-danger { color: #dc3545 !important; }
    .text-warning { color: #ffc107 !important; }
    
    .btn-sm {
        padding: 5px 10px;
        font-size: 12px;
    }
    </style>
@endsection