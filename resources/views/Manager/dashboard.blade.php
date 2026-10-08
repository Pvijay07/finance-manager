@extends('Manager.layouts.app')
@section('content')
<div id="dashboard" class="page active px-2 sm:px-4 py-3">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h1 style="font-weight: 800; color: #0f172a; font-size: 1.85rem; letter-spacing: -0.75px;" class="mb-0">
                    Manager Financial Hub
                </h1>
                @if($selectedCompany)
                <span class="badge bg-primary/10 text-primary px-2.5 py-1 text-xs font-semibold rounded-pill border border-primary/20">
                    <i class="fas fa-building me-1"></i> {{ $selectedCompany->name }}
                </span>
                @endif
            </div>
            <div style="font-size: 0.925rem; color: #64748b; margin-top: 4px;">
                Real-time operational financial metrics, company profit/loss analysis, and cashflow tracking.
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" onclick="resetFilters()" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 rounded-lg py-2 px-3 text-sm font-medium shadow-sm bg-white hover:bg-slate-50 transition-all">
                <i class="fas fa-redo-alt text-xs"></i> Reset
            </button>
            <a href="{{ route('standard-expenses.index') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 rounded-lg py-2 px-3 text-sm font-medium shadow-sm" style="background-color: #4f46e5 !important; border-color: #4f46e5 !important;">
                <i class="fas fa-plus text-xs"></i> Manage Expenses
            </a>
        </div>
    </div>

    <!-- Bento Filter Bar -->
    <form method="GET" action="{{ route('manager.dashboard') }}" id="dashboardFilterForm">
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 bg-surface-container-lowest p-3 rounded-xl card-shadow border border-outline-variant mb-4">
            <!-- Date Range Selector -->
            <div class="flex flex-col gap-1">
                <label class="font-label-md text-[11px] font-bold uppercase tracking-wider text-slate-500 px-1">Period</label>
                <select name="range" id="dateRangeSelect" onchange="handleRangeChange(this.value)" class="border-outline-variant rounded-lg font-body-sm text-sm bg-surface-container-low focus:ring-indigo-500 py-2 px-3 w-full border text-slate-700 font-medium">
                    <option value="today" {{ $dateRange == 'today' ? 'selected' : '' }}>Today</option>
                    <option value="week" {{ $dateRange == 'week' ? 'selected' : '' }}>This Week</option>
                    <option value="month" {{ $dateRange == 'month' || !$dateRange ? 'selected' : '' }}>This Month</option>
                    <option value="quarter" {{ $dateRange == 'quarter' ? 'selected' : '' }}>This Quarter</option>
                    <option value="year" {{ $dateRange == 'year' ? 'selected' : '' }}>This Year</option>
                    <option value="all" {{ $dateRange == 'all' ? 'selected' : '' }}>All Time</option>
                    <option value="custom" {{ $dateRange == 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                </select>
            </div>

            <!-- Custom Start Date -->
            <div class="flex flex-col gap-1 {{ $dateRange == 'custom' ? '' : 'd-none' }}" id="customStartDateDiv">
                <label class="font-label-md text-[11px] font-bold uppercase tracking-wider text-slate-500 px-1">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDateParam }}" onchange="applyFilters()" class="border-outline-variant rounded-lg font-body-sm text-sm bg-surface-container-low py-2 px-3 w-full border text-slate-700">
            </div>

            <!-- Custom End Date -->
            <div class="flex flex-col gap-1 {{ $dateRange == 'custom' ? '' : 'd-none' }}" id="customEndDateDiv">
                <label class="font-label-md text-[11px] font-bold uppercase tracking-wider text-slate-500 px-1">End Date</label>
                <input type="date" name="end_date" value="{{ $endDateParam }}" onchange="applyFilters()" class="border-outline-variant rounded-lg font-body-sm text-sm bg-surface-container-low py-2 px-3 w-full border text-slate-700">
            </div>

            <!-- Company Filter -->
            <div class="flex flex-col gap-1">
                <label class="font-label-md text-[11px] font-bold uppercase tracking-wider text-slate-500 px-1">Filter by Company</label>
                <select name="company" id="companySelect" onchange="applyFilters()" class="border-outline-variant rounded-lg font-body-sm text-sm bg-surface-container-low focus:ring-indigo-500 py-2 px-3 w-full border text-slate-700 font-medium">
                    <option value="">All Assigned Companies</option>
                    @foreach($companies as $company)
                    <option value="{{ $company->id }}" {{ $companyId == $company->id ? 'selected' : '' }}>
                        {{ $company->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Quick Stats View -->
            <div class="flex flex-col gap-1">
                <label class="font-label-md text-[11px] font-bold uppercase tracking-wider text-slate-500 px-1">View Mode</label>
                <select name="view" onchange="applyFilters()" class="border-outline-variant rounded-lg font-body-sm text-sm bg-surface-container-low focus:ring-indigo-500 py-2 px-3 w-full border text-slate-700 font-medium">
                    <option value="summary" {{ $viewType == 'summary' ? 'selected' : '' }}>Executive Summary</option>
                    <option value="detailed" {{ $viewType == 'detailed' ? 'selected' : '' }}>Detailed Breakdown</option>
                </select>
            </div>
        </section>
    </form>

    <!-- Top Notifications Alert (If any critical issues) -->
    @if(count($notifications) > 0)
    <div class="mb-4">
        @foreach($notifications as $notif)
            @if($notif['type'] === 'danger' || $notif['type'] === 'warning')
            <div class="alert alert-{{ $notif['type'] == 'danger' ? 'danger' : 'warning' }} d-flex align-items-center justify-content-between p-3 rounded-xl border-0 shadow-sm mb-2" role="alert">
                <div class="d-flex align-items-center gap-2.5">
                    <span class="material-symbols-outlined text-[20px]">{{ $notif['icon'] }}</span>
                    <span class="text-sm font-semibold">{{ $notif['message'] }}</span>
                </div>
                @if(!empty($notif['link']) && $notif['link'] !== '#')
                <a href="{{ $notif['link'] }}" class="btn btn-sm btn-{{ $notif['type'] == 'danger' ? 'danger' : 'warning' }} rounded-lg text-xs font-bold px-3 py-1 text-decoration-none">
                    Review Now <i class="fas fa-arrow-right ms-1"></i>
                </a>
                @endif
            </div>
            @endif
        @endforeach
    </div>
    @endif

    <!-- Primary KPI Metric Cards Grid -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-6 g-3 mb-4">
        <!-- 1. Total Received Revenue -->
        <div class="col">
            <div class="card-kpi bg-surface-container-lowest p-3 rounded-xl card-shadow border border-outline-variant h-100 flex flex-col justify-between hover:border-emerald-500 transition-all">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="font-label-md text-[11px] font-bold uppercase tracking-wider text-slate-500">Revenue Received</span>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-emerald-50 text-emerald-600">
                        <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>
                    </div>
                </div>
                <div>
                    <h3 class="text-xl font-extrabold text-slate-800 mb-1">
                        ₹{{ number_format($currentStats['totalIncome'], 2) }}
                    </h3>
                    @php
                        $incChange = ($previousStats['totalIncome'] ?? 0) > 0 
                            ? ((($currentStats['totalIncome'] ?? 0) - ($previousStats['totalIncome'] ?? 0)) / ($previousStats['totalIncome'] ?? 1)) * 100 
                            : 0;
                    @endphp
                    <div class="d-flex align-items-center justify-content-between text-[11px] text-slate-500">
                        <span>{{ $currentStats['receivedIncomeCount'] }} receipts</span>
                        <span class="font-semibold {{ $incChange >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $incChange >= 0 ? '+' : '' }}{{ number_format($incChange, 1) }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Total Pending / Receivable Income -->
        <div class="col">
            <div class="card-kpi bg-surface-container-lowest p-3 rounded-xl card-shadow border border-outline-variant h-100 flex flex-col justify-between hover:border-teal-500 transition-all">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="font-label-md text-[11px] font-bold uppercase tracking-wider text-slate-500">Receivable Dues</span>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-teal-50 text-teal-600">
                        <span class="material-symbols-outlined text-[18px]">pending_actions</span>
                    </div>
                </div>
                <div>
                    <h3 class="text-xl font-extrabold text-slate-800 mb-1">
                        ₹{{ number_format($currentStats['totalReceivableIncome'], 2) }}
                    </h3>
                    <div class="d-flex align-items-center justify-content-between text-[11px] text-slate-500">
                        <span>Pending collection</span>
                        <a href="{{ route('manager.invoices') }}" class="text-teal-600 font-semibold text-decoration-none hover:underline">View <i class="fas fa-chevron-right text-[9px]"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Total Paid Expenses -->
        <div class="col">
            <div class="card-kpi bg-surface-container-lowest p-3 rounded-xl card-shadow border border-outline-variant h-100 flex flex-col justify-between hover:border-rose-500 transition-all">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="font-label-md text-[11px] font-bold uppercase tracking-wider text-slate-500">Paid Expenses</span>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-rose-50 text-rose-600">
                        <span class="material-symbols-outlined text-[18px]">payments</span>
                    </div>
                </div>
                <div>
                    <h3 class="text-xl font-extrabold text-slate-800 mb-1">
                        ₹{{ number_format($currentStats['totalExpenses'], 2) }}
                    </h3>
                    @php
                        $expChange = ($previousStats['totalExpenses'] ?? 0) > 0 
                            ? ((($currentStats['totalExpenses'] ?? 0) - ($previousStats['totalExpenses'] ?? 0)) / ($previousStats['totalExpenses'] ?? 1)) * 100 
                            : 0;
                    @endphp
                    <div class="d-flex align-items-center justify-content-between text-[11px] text-slate-500">
                        <span>{{ $currentStats['paidExpensesCount'] }} paid</span>
                        <span class="font-semibold {{ $expChange <= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $expChange >= 0 ? '+' : '' }}{{ number_format($expChange, 1) }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Total Payable Expenses -->
        <div class="col">
            <div class="card-kpi bg-surface-container-lowest p-3 rounded-xl card-shadow border border-outline-variant h-100 flex flex-col justify-between hover:border-amber-500 transition-all">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="font-label-md text-[11px] font-bold uppercase tracking-wider text-slate-500">Payable Liabilities</span>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-amber-50 text-amber-600">
                        <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                    </div>
                </div>
                <div>
                    <h3 class="text-xl font-extrabold text-slate-800 mb-1">
                        ₹{{ number_format($currentStats['totalPayableExpenses'], 2) }}
                    </h3>
                    <div class="d-flex align-items-center justify-content-between text-[11px] text-slate-500">
                        <span>Pending bills</span>
                        <a href="{{ route('manager.expenses', ['status' => 'pending']) }}" class="text-amber-600 font-semibold text-decoration-none hover:underline">Settle <i class="fas fa-chevron-right text-[9px]"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Net Operating Profit -->
        <div class="col">
            <div class="card-kpi bg-surface-container-lowest p-3 rounded-xl card-shadow border border-outline-variant h-100 flex flex-col justify-between hover:border-indigo-500 transition-all">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="font-label-md text-[11px] font-bold uppercase tracking-wider text-slate-500">Net Profit</span>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ $currentStats['netProfit'] >= 0 ? 'bg-indigo-50 text-indigo-600' : 'bg-rose-50 text-rose-600' }}">
                        <span class="material-symbols-outlined text-[18px]">monitoring</span>
                    </div>
                </div>
                <div>
                    <h3 class="text-xl font-extrabold {{ $currentStats['netProfit'] >= 0 ? 'text-indigo-600' : 'text-rose-600' }} mb-1">
                        ₹{{ number_format($currentStats['netProfit'], 2) }}
                    </h3>
                    <div class="d-flex align-items-center justify-content-between text-[11px] text-slate-500">
                        <span>Margin: {{ $currentStats['totalIncome'] > 0 ? number_format(($currentStats['netProfit'] / $currentStats['totalIncome']) * 100, 1) : 0 }}%</span>
                        <span class="font-bold {{ $currentStats['netProfit'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $currentStats['netProfit'] >= 0 ? 'Surplus' : 'Deficit' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 6. Scheduled Next 7 Days -->
        <div class="col">
            <div class="card-kpi bg-surface-container-lowest p-3 rounded-xl card-shadow border border-outline-variant h-100 flex flex-col justify-between hover:border-orange-500 transition-all">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="font-label-md text-[11px] font-bold uppercase tracking-wider text-slate-500">Due In 7 Days</span>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-orange-50 text-orange-600">
                        <span class="material-symbols-outlined text-[18px]">alarm</span>
                    </div>
                </div>
                <div>
                    <h3 class="text-xl font-extrabold text-orange-600 mb-1">
                        ₹{{ number_format($currentStats['upcomingPayments'], 2) }}
                    </h3>
                    <div class="d-flex align-items-center justify-content-between text-[11px] text-slate-500">
                        <span>{{ $immediatePayments->count() }} obligations</span>
                        <a href="#immediate-dues-section" class="text-orange-600 font-semibold text-decoration-none hover:underline">View List <i class="fas fa-arrow-down text-[9px]"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive Charts Section -->
    <div class="row g-4 mb-4">
        <!-- Chart 1: Company-Wise Revenue vs Expense Comparison -->
        <div class="col-12 col-xl-7">
            <div class="bg-surface-container-lowest p-4 rounded-xl card-shadow border border-outline-variant h-100">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4 pb-2 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-bold text-slate-800 mb-0.5 d-flex align-items-center gap-2">
                            <i class="fas fa-chart-bar text-indigo-600"></i> Company-Wise Revenue & Expense Comparison
                        </h2>
                        <span class="text-xs text-slate-500">Income received vs paid expenses per company for {{ $currentStats['periodLabel'] }}</span>
                    </div>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary active text-xs font-semibold px-2.5 py-1" onclick="toggleCompanyChartMode('all')">All Metrics</button>
                        <button type="button" class="btn btn-outline-secondary text-xs font-semibold px-2.5 py-1" onclick="toggleCompanyChartMode('profit')">Profit Only</button>
                    </div>
                </div>
                <div style="position: relative; height: 320px; width: 100%;">
                    <canvas id="companyComparisonChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Chart 2: Cashflow Trend Line / Area Chart -->
        <div class="col-12 col-xl-5">
            <div class="bg-surface-container-lowest p-4 rounded-xl card-shadow border border-outline-variant h-100">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-bold text-slate-800 mb-0.5 d-flex align-items-center gap-2">
                            <i class="fas fa-chart-line text-emerald-600"></i> Cashflow Timeline Trend
                        </h2>
                        <span class="text-xs text-slate-500">Revenue inflows vs expense outflows timeline</span>
                    </div>
                    <span class="badge bg-indigo-50 text-indigo-700 font-semibold px-2 py-1 text-[11px] rounded">
                        {{ $currentStats['periodLabel'] }}
                    </span>
                </div>
                <div style="position: relative; height: 320px; width: 100%;">
                    <canvas id="cashflowTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Charts & Analytics Grid -->
    <div class="row g-4 mb-4">
        <!-- Chart 3: Expense Category Breakdown -->
        <div class="col-12 col-lg-5">
            <div class="bg-surface-container-lowest p-4 rounded-xl card-shadow border border-outline-variant h-100">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-bold text-slate-800 mb-0.5 d-flex align-items-center gap-2">
                            <i class="fas fa-chart-pie text-amber-500"></i> Expense Category Breakdown
                        </h2>
                        <span class="text-xs text-slate-500">Distribution of paid operational expenses</span>
                    </div>
                </div>
                @if(!empty($categoryBreakdown['data']) && count($categoryBreakdown['data']) > 0)
                <div class="row align-items-center">
                    <div class="col-12 col-sm-6" style="position: relative; height: 230px;">
                        <canvas id="categoryDoughnutChart"></canvas>
                    </div>
                    <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                        <div class="flex flex-col gap-2 max-h-[220px] overflow-y-auto pr-1">
                            @foreach($categoryBreakdown['labels'] as $idx => $catLabel)
                            <div class="d-flex align-items-center justify-content-between text-xs py-1 px-2 rounded hover:bg-slate-50">
                                <div class="d-flex align-items-center gap-2 truncate">
                                    <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $categoryBreakdown['colors'][$idx] ?? '#4f46e5' }}"></span>
                                    <span class="text-slate-700 font-medium truncate" title="{{ $catLabel }}">{{ $catLabel }}</span>
                                </div>
                                <span class="font-bold text-slate-800">₹{{ number_format($categoryBreakdown['data'][$idx] ?? 0, 0) }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @else
                <div class="d-flex flex-column align-items-center justify-content-center py-5 text-slate-400">
                    <span class="material-symbols-outlined text-[48px] text-slate-300 mb-2">pie_chart</span>
                    <p class="text-sm font-medium mb-0">No paid expense categorized for this period</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Company Financial Performance Matrix Table -->
        <div class="col-12 col-lg-7">
            <div class="bg-surface-container-lowest p-4 rounded-xl card-shadow border border-outline-variant h-100">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-bold text-slate-800 mb-0.5 d-flex align-items-center gap-2">
                            <i class="fas fa-building text-teal-600"></i> Company Financial Performance
                        </h2>
                        <span class="text-xs text-slate-500">Summary of income, expenses, and net profit per entity</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-sm">
                        <thead class="bg-slate-50 text-slate-600 text-[11px] uppercase tracking-wider font-bold">
                            <tr>
                                <th class="py-2.5 px-3">Company Name</th>
                                <th class="py-2.5 px-3 text-end">Revenue</th>
                                <th class="py-2.5 px-3 text-end">Expenses</th>
                                <th class="py-2.5 px-3 text-end">Net Profit</th>
                                <th class="py-2.5 px-3 text-center">Margin</th>
                                <th class="py-2.5 px-3 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($companyProfitLoss as $cItem)
                            <tr>
                                <td class="py-2.5 px-3 font-semibold text-slate-800">
                                    {{ $cItem['name'] }}
                                </td>
                                <td class="py-2.5 px-3 text-end font-medium text-emerald-600">
                                    ₹{{ number_format($cItem['income'], 2) }}
                                </td>
                                <td class="py-2.5 px-3 text-end font-medium text-rose-600">
                                    ₹{{ number_format($cItem['expenses'], 2) }}
                                </td>
                                <td class="py-2.5 px-3 text-end font-bold {{ $cItem['profit'] >= 0 ? 'text-indigo-600' : 'text-rose-600' }}">
                                    ₹{{ number_format($cItem['profit'], 2) }}
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <span class="badge {{ $cItem['margin'] >= 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }} text-[11px] font-bold px-2 py-0.5 rounded">
                                        {{ $cItem['margin'] }}%
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <a href="{{ route('manager.dashboard', ['company' => $cItem['id'], 'range' => $dateRange]) }}" class="btn btn-xs btn-outline-primary text-[11px] px-2 py-1 rounded hover:bg-indigo-50 font-semibold" title="Filter this company">
                                        Focus
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-slate-400">
                                    No company records found for this user
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Center: Immediate Due Obligations & Recent Activity -->
    <div class="row g-4" id="immediate-dues-section">
        <!-- Immediate Payments Table -->
        <div class="col-12 col-xl-7">
            <div class="bg-surface-container-lowest p-4 rounded-xl card-shadow border border-outline-variant h-100">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3 pb-2 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-bold text-slate-800 mb-0.5 d-flex align-items-center gap-2">
                            <i class="fas fa-clock text-orange-500"></i> Immediate Due Obligations (Next 7 Days)
                        </h2>
                        <span class="text-xs text-slate-500">Expenses requiring payment or settlement</span>
                    </div>
                    <a href="{{ route('standard-expenses.index') }}" class="btn btn-sm btn-outline-secondary text-xs font-semibold px-2.5 py-1">
                        View All Expenses <i class="fas fa-chevron-right text-[9px] ms-1"></i>
                    </a>
                </div>

                @if($immediatePayments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-sm">
                        <thead class="bg-slate-50 text-slate-600 text-[11px] uppercase tracking-wider font-bold">
                            <tr>
                                <th class="py-2 px-3">Due Date</th>
                                <th class="py-2 px-3">Company</th>
                                <th class="py-2 px-3">Expense Name</th>
                                <th class="py-2 px-3">Party</th>
                                <th class="py-2 px-3 text-end">Amount</th>
                                <th class="py-2 px-3 text-center">Status</th>
                                <th class="py-2 px-3 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($immediatePayments as $pay)
                            <tr>
                                <td class="py-2.5 px-3 text-xs font-semibold text-slate-700">
                                    {{ $pay['date'] }}
                                </td>
                                <td class="py-2.5 px-3 text-xs text-slate-600">
                                    {{ $pay['company'] }}
                                </td>
                                <td class="py-2.5 px-3 font-medium text-slate-800">
                                    {{ $pay['name'] }}
                                    <span class="text-[10px] text-slate-400 block">{{ $pay['type'] }}</span>
                                </td>
                                <td class="py-2.5 px-3 text-xs text-slate-600">
                                    {{ $pay['party'] }}
                                </td>
                                <td class="py-2.5 px-3 text-end font-bold text-slate-900">
                                    ₹{{ number_format($pay['amount'], 2) }}
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    @if($pay['status'] === 'overdue')
                                        <span class="badge bg-rose-50 text-rose-700 border border-rose-200 text-[10px] font-bold px-2 py-0.5 rounded">Overdue</span>
                                    @elseif($pay['status'] === 'upcoming')
                                        <span class="badge bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold px-2 py-0.5 rounded">Upcoming</span>
                                    @else
                                        <span class="badge bg-slate-100 text-slate-700 text-[10px] font-bold px-2 py-0.5 rounded">Pending</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <a href="{{ $pay['is_standard'] ? route('manager.standard-expenses') : route('non-standard-expenses.index') }}" class="btn btn-xs btn-primary text-[11px] px-2.5 py-1 rounded font-semibold shadow-none" style="background-color: #4f46e5 !important; border-color: #4f46e5 !important;">
                                        Settle
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="d-flex flex-column align-items-center justify-content-center py-5 text-slate-400">
                    <span class="material-symbols-outlined text-[48px] text-emerald-500 mb-2">check_circle</span>
                    <p class="text-sm font-semibold text-slate-700 mb-0.5">All Dues Cleared</p>
                    <span class="text-xs text-slate-500">No scheduled expense obligations due in the next 7 days</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Recent Transactions Activity -->
        <div class="col-12 col-xl-5">
            <div class="bg-surface-container-lowest p-4 rounded-xl card-shadow border border-outline-variant h-100">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-bold text-slate-800 mb-0.5 d-flex align-items-center gap-2">
                            <i class="fas fa-history text-indigo-600"></i> Recent Activity Log
                        </h2>
                        <span class="text-xs text-slate-500">Latest recorded income & expense events</span>
                    </div>
                </div>

                <div class="flex flex-col gap-2.5">
                    @forelse($recentTransactions as $tx)
                    <div class="p-2.5 rounded-lg border border-slate-100 hover:bg-slate-50 transition-all d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $tx['type'] === 'income' ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }}">
                                <i class="fas {{ $tx['type'] === 'income' ? 'fa-arrow-down' : 'fa-arrow-up' }} text-xs"></i>
                            </div>
                            <div>
                                <h6 class="text-xs font-bold text-slate-800 mb-0">{{ $tx['title'] }}</h6>
                                <span class="text-[11px] text-slate-500">{{ $tx['company'] }} • {{ $tx['party'] }} • {{ $tx['date'] }}</span>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="text-xs font-extrabold {{ $tx['type'] === 'income' ? 'text-emerald-600' : 'text-slate-800' }}">
                                {{ $tx['type'] === 'income' ? '+' : '-' }}₹{{ number_format($tx['amount'], 2) }}
                            </span>
                            <span class="badge {{ $tx['status'] === 'received' || $tx['status'] === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }} text-[9px] font-bold block mt-0.5">
                                {{ ucfirst($tx['status']) }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="d-flex flex-column align-items-center justify-content-center py-5 text-slate-400">
                        <span class="material-symbols-outlined text-[40px] text-slate-300 mb-1">receipt</span>
                        <p class="text-xs font-medium mb-0">No recent transactions recorded</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Filter handlers
function handleRangeChange(val) {
    const startDiv = document.getElementById('customStartDateDiv');
    const endDiv = document.getElementById('customEndDateDiv');
    if (val === 'custom') {
        startDiv.classList.remove('d-none');
        endDiv.classList.remove('d-none');
    } else {
        startDiv.classList.add('d-none');
        endDiv.classList.add('d-none');
        applyFilters();
    }
}

function applyFilters() {
    document.getElementById('dashboardFilterForm').submit();
}

function resetFilters() {
    window.location.href = "{{ route('manager.dashboard') }}";
}

// Chart Objects and State
let companyChartInstance = null;
let trendChartInstance = null;
let doughnutChartInstance = null;

const companyDataRaw = @json($companyProfitLoss);
const trendDataRaw = @json($trendData);
const categoryDataRaw = @json($categoryBreakdown);

let currentCompanyChartMode = 'all';

document.addEventListener('DOMContentLoaded', function() {
    renderCompanyComparisonChart();
    renderCashflowTrendChart();
    renderCategoryDoughnutChart();
});

// Chart 1: Company Wise Bar Chart
function renderCompanyComparisonChart() {
    const ctx = document.getElementById('companyComparisonChart');
    if (!ctx) return;

    const names = companyDataRaw.map(c => c.name);
    const incomes = companyDataRaw.map(c => c.income);
    const expenses = companyDataRaw.map(c => c.expenses);
    const profits = companyDataRaw.map(c => c.profit);

    let datasets = [];

    if (currentCompanyChartMode === 'profit') {
        datasets = [
            {
                label: 'Net Profit (₹)',
                data: profits,
                backgroundColor: profits.map(p => p >= 0 ? 'rgba(79, 70, 229, 0.85)' : 'rgba(239, 68, 68, 0.85)'),
                borderColor: profits.map(p => p >= 0 ? '#4f46e5' : '#ef4444'),
                borderWidth: 1.5,
                borderRadius: 6,
            }
        ];
    } else {
        datasets = [
            {
                label: 'Revenue Received (₹)',
                data: incomes,
                backgroundColor: 'rgba(16, 185, 129, 0.85)',
                borderColor: '#10b981',
                borderWidth: 1,
                borderRadius: 6,
            },
            {
                label: 'Paid Expenses (₹)',
                data: expenses,
                backgroundColor: 'rgba(244, 63, 94, 0.85)',
                borderColor: '#f43f5e',
                borderWidth: 1,
                borderRadius: 6,
            },
            {
                label: 'Net Profit (₹)',
                data: profits,
                type: 'line',
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                borderWidth: 2.5,
                pointBackgroundColor: '#4f46e5',
                pointRadius: 4,
                tension: 0.25,
                fill: false
            }
        ];
    }

    if (companyChartInstance) {
        companyChartInstance.destroy();
    }

    companyChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: names,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        usePointStyle: true,
                        font: { size: 11, weight: '600', family: 'Inter' }
                    }
                },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleFont: { size: 12, weight: 'bold', family: 'Inter' },
                    bodyFont: { size: 11, family: 'Inter' },
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return `${context.dataset.label}: ₹${context.parsed.y.toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { size: 11, family: 'Inter' },
                        color: '#64748b'
                    }
                },
                y: {
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        font: { size: 11, family: 'Inter' },
                        color: '#64748b',
                        callback: function(value) {
                            if (Math.abs(value) >= 100000) return '₹' + (value / 100000).toFixed(1) + 'L';
                            if (Math.abs(value) >= 1000) return '₹' + (value / 1000).toFixed(0) + 'k';
                            return '₹' + value;
                        }
                    }
                }
            }
        }
    });
}

function toggleCompanyChartMode(mode) {
    currentCompanyChartMode = mode;
    const btns = event.target.parentElement.querySelectorAll('button');
    btns.forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');
    renderCompanyComparisonChart();
}

// Chart 2: Cashflow Trend Timeline
function renderCashflowTrendChart() {
    const ctx = document.getElementById('cashflowTrendChart');
    if (!ctx) return;

    if (trendChartInstance) {
        trendChartInstance.destroy();
    }

    trendChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: trendDataRaw.labels || [],
            datasets: [
                {
                    label: 'Revenue Inflow',
                    data: trendDataRaw.income || [],
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.08)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                    pointHoverRadius: 6
                },
                {
                    label: 'Expense Outflow',
                    data: trendDataRaw.expenses || [],
                    borderColor: '#f43f5e',
                    backgroundColor: 'rgba(244, 63, 94, 0.08)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                    pointHoverRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: 10,
                        usePointStyle: true,
                        font: { size: 11, weight: '600', family: 'Inter' }
                    }
                },
                tooltip: {
                    backgroundColor: '#1e293b',
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return `${context.dataset.label}: ₹${context.parsed.y.toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { size: 10, family: 'Inter' },
                        color: '#64748b'
                    }
                },
                y: {
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        font: { size: 10, family: 'Inter' },
                        color: '#64748b',
                        callback: function(value) {
                            if (value >= 100000) return '₹' + (value / 100000).toFixed(1) + 'L';
                            if (value >= 1000) return '₹' + (value / 1000).toFixed(0) + 'k';
                            return '₹' + value;
                        }
                    }
                }
            }
        }
    });
}

// Chart 3: Category Doughnut Chart
function renderCategoryDoughnutChart() {
    const ctx = document.getElementById('categoryDoughnutChart');
    if (!ctx || !categoryDataRaw.data || categoryDataRaw.data.length === 0) return;

    if (doughnutChartInstance) {
        doughnutChartInstance.destroy();
    }

    doughnutChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: categoryDataRaw.labels,
            datasets: [{
                data: categoryDataRaw.data,
                backgroundColor: categoryDataRaw.colors,
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#1e293b',
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            const val = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                            return ` ${context.label}: ₹${val.toLocaleString('en-IN')} (${pct}%)`;
                        }
                    }
                }
            }
        }
    });
}
</script>

<style>
.card-shadow {
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.03);
}
.card-kpi {
    transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
}
.card-kpi:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08), 0 2px 4px -1px rgba(0, 0, 0, 0.04);
}
</style>
@endsection