@extends('Admin.layouts.app')
@section('content')
    <div id="dashboard" class="page active">
        <!-- Dashboard Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 style="font-weight: 800; color: #0f172a; font-size: 2rem; letter-spacing: -0.75px;">Analytics Overview</h1>
                <div style="font-size: 0.95rem; color: #64748b; margin-top: 4px;">Welcome back! Here's what's happening today.</div>
            </div>
            <div>
                <button type="button" class="btn btn-primary" onclick="resetFilters()" style="background-color: #4f46e5 !important; border-color: #4f46e5 !important; display: inline-flex !important; align-items: center !important; gap: 8px !important; border-radius: 8px !important; padding: 10px 20px !important;">
                    <i class="fas fa-redo"></i> Reset Filters
                </button>
            </div>
        </div>

        <!-- Bento Filter Bar -->
        <form method="GET" action="{{ route('admin.dashboard') }}" id="dashboardFilter">
            <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-md bg-surface-container-lowest p-md rounded-xl card-shadow border border-outline-variant mb-lg">
                <!-- Date Range -->
                <div class="flex flex-col gap-xs">
                    <label class="font-label-md text-[10px] uppercase text-on-surface-variant px-1">Date Range</label>
                    <select name="range" id="dateRangeSelect" onchange="handleRangeChange(this.value)" class="border-outline-variant rounded-lg font-body-sm text-body-sm bg-surface-container-low focus:ring-secondary py-2 px-3 w-full border">
                        <option value="today" {{ request('range') == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="week" {{ request('range') == 'week' || !request('range') ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ request('range') == 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="all" {{ request('range') == 'all' ? 'selected' : '' }}>All Time</option>
                        <option value="custom" {{ request('range') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>
                <!-- Custom Start Date -->
                <div class="flex flex-col gap-xs {{ request('range') == 'custom' ? '' : 'd-none' }}" id="customStartDateDiv">
                    <label class="font-label-md text-[10px] uppercase text-on-surface-variant px-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" onchange="applyFilters()" class="border-outline-variant rounded-lg font-body-sm text-body-sm bg-surface-container-low py-2 px-3 w-full border">
                </div>
                <!-- Custom End Date -->
                <div class="flex flex-col gap-xs {{ request('range') == 'custom' ? '' : 'd-none' }}" id="customEndDateDiv">
                    <label class="font-label-md text-[10px] uppercase text-on-surface-variant px-1">End Date</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" onchange="applyFilters()" class="border-outline-variant rounded-lg font-body-sm text-body-sm bg-surface-container-low py-2 px-3 w-full border">
                </div>
                <!-- Company -->
                <div class="flex flex-col gap-xs">
                    <label class="font-label-md text-[10px] uppercase text-on-surface-variant px-1">Company</label>
                    <select name="company" onchange="applyFilters()" class="border-outline-variant rounded-lg font-body-sm text-body-sm bg-surface-container-low focus:ring-secondary py-2 px-3 w-full border">
                        <option value="">All Companies</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}" {{ request('company') == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </section>
        </form>

        <!-- Summary Cards Row -->
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3 mb-4">
            <!-- Total Companies -->
            <div class="col">
                <div class="summary-card flex flex-col justify-between hover:border-primary transition-all bg-surface-container-lowest p-md rounded-xl card-shadow border border-outline-variant h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="summary-header">
                            <p class="font-label-md text-label-md text-on-surface-variant mb-0 uppercase">Total Companies</p>
                        </div>
                        <div class="p-sm bg-primary/10 rounded-lg flex items-center justify-center" style="width: 40px; height: 40px;">
                            <span class="material-symbols-outlined text-primary">domain</span>
                        </div>
                    </div>
                    <div class="summary-body">
                        <h4 class="font-headline-md text-headline-md text-primary mt-xs mb-2">{{ $stats['total_companies'] }}</h4>
                        <div class="mt-md d-flex justify-content-between align-items-center gap-sm">
                            <span class="font-label-sm text-label-sm text-success"><i class="fas fa-check-circle"></i> {{ $stats['active_companies'] }} active</span>
                            <a href="{{ route('admin.companies') }}" class="font-label-sm text-primary text-decoration-none">Manage <i class="fas fa-chevron-right text-[10px]"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Users -->
            <div class="col">
                <div class="summary-card flex flex-col justify-between hover:border-info transition-all bg-surface-container-lowest p-md rounded-xl card-shadow border border-outline-variant h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="summary-header">
                            <p class="font-label-md text-label-md text-on-surface-variant mb-0 uppercase">System Users</p>
                        </div>
                        <div class="p-sm bg-info/10 rounded-lg flex items-center justify-center" style="width: 40px; height: 40px;">
                            <span class="material-symbols-outlined text-info">group</span>
                        </div>
                    </div>
                    <div class="summary-body">
                        <h4 class="font-headline-md text-headline-md text-info mt-xs mb-2">{{ $stats['total_users'] }}</h4>
                        <div class="mt-md d-flex justify-content-between align-items-center gap-sm">
                            <span class="font-label-sm text-label-sm text-on-surface-variant">{{ $stats['admin_users'] }} Admins • {{ $stats['manager_users'] }} Managers</span>
                            <a href="{{ route('admin.users') }}" class="font-label-sm text-info text-decoration-none">Manage <i class="fas fa-chevron-right text-[10px]"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Expense Types -->
            <div class="col">
                <div class="summary-card flex flex-col justify-between hover:border-warning transition-all bg-surface-container-lowest p-md rounded-xl card-shadow border border-outline-variant h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="summary-header">
                            <p class="font-label-md text-label-md text-on-surface-variant mb-0 uppercase">Expense Types</p>
                        </div>
                        <div class="p-sm bg-warning/10 rounded-lg flex items-center justify-center" style="width: 40px; height: 40px;">
                            <span class="material-symbols-outlined text-warning">sell</span>
                        </div>
                    </div>
                    <div class="summary-body">
                        <h4 class="font-headline-md text-headline-md text-warning mt-xs mb-2">{{ $stats['expense_types'] }}</h4>
                        <div class="mt-md d-flex justify-content-between align-items-center gap-sm">
                            <span class="font-label-sm text-label-sm text-on-surface-variant">{{ $stats['active_expense_types'] }} Categorized</span>
                            <a href="{{ route('admin.expensetypes') }}" class="font-label-sm text-warning text-decoration-none">View All <i class="fas fa-chevron-right text-[10px]"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary Stats Grid -->
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3 mb-4">
            <!-- Total Transactions -->
            <div class="col">
                <div class="summary-card flex flex-col justify-between hover:border-success transition-all bg-surface-container-lowest p-md rounded-xl card-shadow border border-outline-variant h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="summary-header">
                            <p class="font-label-md text-label-md text-on-surface-variant mb-0 uppercase">Total Transactions</p>
                        </div>
                        <div class="p-sm bg-success/10 rounded-lg flex items-center justify-center" style="width: 40px; height: 40px;">
                            <span class="material-symbols-outlined text-success">sync_alt</span>
                        </div>
                    </div>
                    <div class="summary-body">
                        <h4 class="font-headline-md text-headline-md text-success mt-xs mb-2">₹{{ number_format($stats['total_amount']) }}</h4>
                        <div class="mt-md">
                            <span class="font-label-sm text-label-sm text-on-surface-variant">{{ $stats['total_transactions'] }} entries registered</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Review -->
            <div class="col">
                <div class="summary-card flex flex-col justify-between hover:border-warning transition-all bg-surface-container-lowest p-md rounded-xl card-shadow border border-outline-variant h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="summary-header">
                            <p class="font-label-md text-label-md text-on-surface-variant mb-0 uppercase">Pending Review</p>
                        </div>
                        <div class="p-sm bg-warning/10 rounded-lg flex items-center justify-center" style="width: 40px; height: 40px;">
                            <span class="material-symbols-outlined text-warning">schedule</span>
                        </div>
                    </div>
                    <div class="summary-body">
                        <h4 class="font-headline-md text-headline-md text-warning mt-xs mb-2">{{ $stats['pending_items'] }}</h4>
                        <div class="mt-md">
                            <span class="font-label-sm text-label-sm text-on-surface-variant">{{ $stats['pending_invoices'] }} Invoices, {{ $stats['pending_expenses'] }} Expenses</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overdue -->
            <div class="col">
                <div class="summary-card flex flex-col justify-between hover:border-danger transition-all bg-surface-container-lowest p-md rounded-xl card-shadow border border-outline-variant h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="summary-header">
                            <p class="font-label-md text-label-md text-on-surface-variant mb-0 uppercase">Overdue</p>
                        </div>
                        <div class="p-sm bg-danger/10 rounded-lg flex items-center justify-center" style="width: 40px; height: 40px;">
                            <span class="material-symbols-outlined text-danger">warning</span>
                        </div>
                    </div>
                    <div class="summary-body">
                        <h4 class="font-headline-md text-headline-md text-danger mt-xs mb-2">₹{{ number_format($stats['overdue_amount']) }}</h4>
                        <div class="mt-md">
                            <span class="font-label-sm text-label-sm text-on-surface-variant">{{ $stats['overdue_payments'] }} critical alerts</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manager Expenses Report Section -->
        <div class="row g-4 mb-4 mt-1">
            <div class="col-12">
                <div class="bg-surface-container-lowest p-md rounded-xl card-shadow border border-outline-variant">
                    <!-- Report Header -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-3 border-b border-outline-variant">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <div class="p-2 rounded-lg bg-primary/10 d-flex align-items-center justify-content-center text-primary" style="width: 36px; height: 36px;">
                                    <span class="material-symbols-outlined text-primary text-[22px]">manage_accounts</span>
                                </div>
                                <h3 class="font-title-lg text-title-lg text-on-surface mb-0 font-bold">Expenses Added by Manager Report</h3>
                            </div>
                            <p class="font-body-sm text-body-sm text-on-surface-variant mb-0">Audit of standard and manual expenses logged and submitted by each branch & company manager</p>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="badge rounded-pill px-3 py-2 font-medium" style="background-color: rgba(79, 70, 229, 0.08); color: #4f46e5; border: 1px solid rgba(79, 70, 229, 0.2);">
                                <i class="fas fa-calendar-alt me-1"></i> Period: {{ $managerExpensesReport['summary']['date_range_label'] }}
                            </span>
                            @if(request('company'))
                                <span class="badge rounded-pill px-3 py-2 font-medium" style="background-color: rgba(16, 185, 129, 0.08); color: #059669; border: 1px solid rgba(16, 185, 129, 0.2);">
                                    <i class="fas fa-building me-1"></i> {{ $companies->firstWhere('id', request('company'))->name ?? 'Filtered Company' }}
                                </span>
                            @endif
                            <div class="position-relative" style="min-width: 220px;">
                                <input type="text" id="managerSearchInput" onkeyup="filterManagerTable()" placeholder="Filter by manager or company..." class="form-control form-control-sm rounded-lg border-outline-variant font-body-sm ps-4" style="height: 36px;">
                                <i class="fas fa-search position-absolute text-muted" style="left: 10px; top: 11px; font-size: 12px;"></i>
                            </div>
                            <button type="button" onclick="exportManagerExpensesReport()" class="btn btn-outline-primary btn-sm rounded-lg d-flex align-items-center gap-2" style="height: 36px; border-color: #4f46e5; color: #4f46e5;">
                                <i class="fas fa-file-csv"></i> Export CSV
                            </button>
                        </div>
                    </div>

                    <!-- Mini KPI Summary Strip -->
                    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3 mb-4">
                        <div class="col">
                            <div class="p-3 rounded-xl border border-outline-variant bg-surface-container-low h-100 flex flex-col justify-between">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase font-medium">Total Managers</span>
                                    <span class="material-symbols-outlined text-info text-[20px]">badge</span>
                                </div>
                                <div>
                                    <h4 class="font-headline-sm text-headline-sm text-info mb-1 font-bold">{{ $managerExpensesReport['summary']['total_managers'] }}</h4>
                                    <span class="font-body-sm text-[11px] text-on-surface-variant">{{ $managerExpensesReport['summary']['active_managers_in_period'] }} active this period</span>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="p-3 rounded-xl border border-outline-variant bg-surface-container-low h-100 flex flex-col justify-between">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase font-medium">Expenses Added</span>
                                    <span class="material-symbols-outlined text-primary text-[20px]">receipt_long</span>
                                </div>
                                <div>
                                    <h4 class="font-headline-sm text-headline-sm text-primary mb-1 font-bold">{{ number_format($managerExpensesReport['summary']['total_expenses_in_period']) }}</h4>
                                    <span class="font-body-sm text-[11px] text-on-surface-variant">{{ number_format($managerExpensesReport['summary']['total_all_time_expenses']) }} all-time entries</span>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="p-3 rounded-xl border border-outline-variant bg-surface-container-low h-100 flex flex-col justify-between">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase font-medium">Total Amount Added</span>
                                    <span class="material-symbols-outlined text-success text-[20px]">payments</span>
                                </div>
                                <div>
                                    <h4 class="font-headline-sm text-headline-sm text-success mb-1 font-bold">₹{{ number_format($managerExpensesReport['summary']['total_amount_in_period'], 2) }}</h4>
                                    <span class="font-body-sm text-[11px] text-on-surface-variant">₹{{ number_format($managerExpensesReport['summary']['total_all_time_amount'], 2) }} all-time value</span>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="p-3 rounded-xl border border-outline-variant bg-surface-container-low h-100 flex flex-col justify-between">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase font-medium">Submission Rate</span>
                                    <span class="material-symbols-outlined text-warning text-[20px]">donut_large</span>
                                </div>
                                <div>
                                    <h4 class="font-headline-sm text-headline-sm text-warning mb-1 font-bold">
                                        {{ $managerExpensesReport['summary']['total_managers'] > 0 ? round(($managerExpensesReport['summary']['active_managers_in_period'] / $managerExpensesReport['summary']['total_managers']) * 100) : 0 }}%
                                    </h4>
                                    <span class="font-body-sm text-[11px] text-on-surface-variant">{{ $managerExpensesReport['summary']['active_managers_all_time'] }} managers active all-time</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Report Grid: Table (8 cols) & Chart / Spotlight (4 cols) -->
                    <div class="row g-4">
                        <!-- Left Table Column -->
                        <div class="col-lg-8">
                            <div class="table-responsive rounded-xl border border-outline-variant">
                                <table class="table table-hover align-middle mb-0" id="managerExpensesTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="font-label-sm text-label-sm text-on-surface-variant uppercase py-3 px-3">Manager</th>
                                            <th class="font-label-sm text-label-sm text-on-surface-variant uppercase py-3 px-3">Assigned Company</th>
                                            <th class="font-label-sm text-label-sm text-on-surface-variant uppercase py-3 px-3 text-center">Expenses Added</th>
                                            <th class="font-label-sm text-label-sm text-on-surface-variant uppercase py-3 px-3">Status Breakdown</th>
                                            <th class="font-label-sm text-label-sm text-on-surface-variant uppercase py-3 px-3">Total Value</th>
                                            <th class="font-label-sm text-label-sm text-on-surface-variant uppercase py-3 px-3">Share</th>
                                            <th class="font-label-sm text-label-sm text-on-surface-variant uppercase py-3 px-3 text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($managerExpensesReport['managers'] as $mgr)
                                            <tr class="manager-row" data-manager-id="{{ $mgr['id'] }}">
                                                <td class="px-3 py-3">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px; font-size: 14px; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
                                                            {{ strtoupper(substr($mgr['name'], 0, 1)) }}
                                                        </div>
                                                        <div>
                                                            <div class="font-label-md text-label-md text-on-surface font-semibold manager-name">{{ $mgr['name'] }}</div>
                                                            <div class="font-body-sm text-[11px] text-on-surface-variant manager-email">{{ $mgr['email'] }}</div>
                                                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill font-label-sm text-[9px] px-2 py-0 mt-1">
                                                                {{ $mgr['role'] }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="manager-companies">
                                                        @foreach($mgr['companies'] as $comp)
                                                            <span class="badge bg-light text-dark border rounded-lg font-body-sm text-[11px] px-2 py-1 mb-1 d-inline-block">
                                                                <i class="fas fa-building text-primary me-1"></i> {{ $comp }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </td>
                                                <td class="px-3 py-3 text-center">
                                                    <div class="d-inline-flex flex-column align-items-center">
                                                        <span class="badge font-bold px-2 py-1 text-sm rounded-lg" style="background-color: rgba(79, 70, 229, 0.12); color: #4f46e5;">
                                                            {{ $mgr['filtered_count'] }} added
                                                        </span>
                                                        <span class="text-[11px] text-muted mt-1">{{ $mgr['all_time_count'] }} all-time</span>
                                                        <span class="text-[10px] text-muted">{{ $mgr['standard_count'] }} Std · {{ $mgr['non_standard_count'] }} Non-Std</span>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="d-flex flex-column gap-1" style="min-width: 140px;">
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <span class="badge bg-success bg-opacity-10 text-success text-[10px] rounded-pill px-2 py-0">
                                                                <i class="fas fa-check-circle me-1"></i> {{ $mgr['paid_count'] }} Paid
                                                            </span>
                                                            <span class="text-[11px] text-success font-medium">₹{{ number_format($mgr['paid_amount']) }}</span>
                                                        </div>
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <span class="badge bg-warning bg-opacity-10 text-warning text-[10px] rounded-pill px-2 py-0">
                                                                <i class="fas fa-clock me-1"></i> {{ $mgr['pending_count'] }} Pending
                                                            </span>
                                                            <span class="text-[11px] text-warning font-medium">₹{{ number_format($mgr['pending_amount']) }}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div>
                                                        <span class="font-headline-sm text-[14px] text-on-surface font-bold d-block">
                                                            ₹{{ number_format($mgr['filtered_amount'], 2) }}
                                                        </span>
                                                        <span class="font-body-sm text-[11px] text-muted">₹{{ number_format($mgr['all_time_amount'], 2) }} all-time</span>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-3" style="min-width: 110px;">
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <span class="font-label-sm text-[11px] text-on-surface font-semibold">{{ $mgr['percentage'] }}%</span>
                                                    </div>
                                                    <div class="progress" style="height: 6px; border-radius: 999px; background-color: #e2e8f0;">
                                                        <div class="progress-bar" role="progressbar" style="width: {{ $mgr['percentage'] }}%; background-color: #4f46e5; border-radius: 999px;"></div>
                                                    </div>
                                                    <span class="text-[10px] text-muted d-block mt-1">Last: {{ $mgr['last_added_date'] }}</span>
                                                </td>
                                                <td class="px-3 py-3 text-end">
                                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-lg d-inline-flex align-items-center gap-1 px-3 py-1 font-medium" onclick="openManagerDetailsModal({{ $mgr['id'] }})" style="border-color: #4f46e5; color: #4f46e5;">
                                                        <i class="fas fa-list-ul"></i> Details
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-5">
                                                    <span class="material-symbols-outlined text-on-surface-variant/40 text-[42px] mb-2 d-block">person_off</span>
                                                    <p class="font-body-sm text-body-sm text-on-surface-variant mb-0 font-medium">No managers or expense records found for this filter</p>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2 rounded-lg" onclick="resetFilters()">Reset Filter</button>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Right Distribution & Spotlight Column -->
                        <div class="col-lg-4">
                            <div class="d-flex flex-column gap-3 h-100">
                                <!-- Chart Card -->
                                <div class="p-3 rounded-xl border border-outline-variant bg-surface-container-low flex-grow-1 flex flex-col justify-between">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <h4 class="font-title-md text-[14px] text-on-surface mb-0 font-bold">Contribution Share</h4>
                                            <span class="text-[11px] text-muted">Expense distribution by manager</span>
                                        </div>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary active btn-sm py-1 px-2 text-[11px]" id="chartMetricCountBtn" onclick="toggleManagerChartMetric('count')">Count</button>
                                            <button type="button" class="btn btn-outline-primary btn-sm py-1 px-2 text-[11px]" id="chartMetricAmountBtn" onclick="toggleManagerChartMetric('amount')">Amount</button>
                                        </div>
                                    </div>
                                    <div style="height: 200px; position: relative;">
                                        <canvas id="managerExpensesChart"></canvas>
                                    </div>
                                </div>

                                <!-- Top Submitter Spotlight Card -->
                                @php
                                    $topManager = $managerExpensesReport['managers']->sortByDesc('filtered_count')->first();
                                @endphp
                                @if($topManager && $topManager['filtered_count'] > 0)
                                    <div class="p-3 rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm" style="border-left: 4px solid #4f46e5 !important;">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge rounded-pill text-[10px] px-2 py-1" style="background-color: rgba(79, 70, 229, 0.1); color: #4f46e5;">
                                                <i class="fas fa-trophy text-warning me-1"></i> #1 Top Submitter
                                            </span>
                                            <span class="text-[11px] text-muted">{{ $topManager['percentage'] }}% of all expenses</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold" style="width: 42px; height: 42px; background: linear-gradient(135deg, #4f46e5 0%, #10b981 100%); font-size: 16px;">
                                                {{ strtoupper(substr($topManager['name'], 0, 1)) }}
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="font-label-md text-on-surface font-bold">{{ $topManager['name'] }}</div>
                                                <div class="text-[11px] text-muted">{{ $topManager['companies_string'] }}</div>
                                                <div class="mt-1 d-flex gap-2">
                                                    <span class="text-[11px] text-primary font-bold">{{ $topManager['filtered_count'] }} expenses added</span>
                                                    <span class="text-[11px] text-success font-bold">• ₹{{ number_format($topManager['filtered_amount']) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dynamic Visualization & Lists -->
        <div class="row row-cols-1 row-cols-lg-3 g-4 mb-4 mt-2">
            <!-- Left Column: Activity -->
            <div class="col-lg-8">
                <div class="bg-surface-container-lowest p-md rounded-xl card-shadow border border-outline-variant h-100 flex flex-col">
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-b border-outline-variant">
                        <div>
                            <h3 class="font-title-lg text-title-lg text-on-surface mb-1">Recent System Activity</h3>
                            <p class="font-body-sm text-body-sm text-on-surface-variant mb-0">Live stream of administrative actions</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2 rounded-lg" data-bs-toggle="modal" data-bs-target="#activityFilterModal">
                                <span class="material-symbols-outlined text-[18px]">filter_list</span> Filter
                            </button>
                            <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-primary btn-sm d-flex align-items-center rounded-lg" style="background-color: #4f46e5; border-color: #4f46e5;">
                                View All
                            </a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="font-label-sm text-label-sm text-on-surface-variant uppercase">User & Action</th>
                                    <th class="font-label-sm text-label-sm text-on-surface-variant uppercase">Resource</th>
                                    <th class="font-label-sm text-label-sm text-on-surface-variant uppercase">Details</th>
                                    <th class="font-label-sm text-label-sm text-on-surface-variant uppercase">Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentActivities as $activity)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-weight: 600;">
                                                    {{ substr($activity['user'], 0, 1) }}
                                                </div>
                                                <div>
                                                    <div class="font-label-md text-label-md text-on-surface">{{ $activity['user'] }}</div>
                                                    <span class="badge bg-{{ $activity['action_color'] == 'success' ? 'success' : ($activity['action_color'] == 'warning' ? 'warning' : 'primary') }} bg-opacity-10 text-{{ $activity['action_color'] == 'success' ? 'success' : ($activity['action_color'] == 'warning' ? 'warning' : 'primary') }} rounded-pill font-label-sm text-[10px] px-2 py-1">
                                                        {{ $activity['action'] }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill font-label-sm px-2 py-1">{{ $activity['resource'] }}</span>
                                        </td>
                                        <td class="font-body-sm text-body-sm text-on-surface-variant">{!! $activity['details'] !!}</td>
                                        <td>
                                            <div class="font-label-sm text-label-sm text-on-surface">{{ $activity['date'] }}</div>
                                            <div class="font-body-sm text-[11px] text-on-surface-variant">{{ $activity['time'] }}</div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <span class="material-symbols-outlined text-on-surface-variant/50 text-[32px] mb-2">history</span>
                                            <p class="font-body-sm text-body-sm text-on-surface-variant mb-0">No activity logs found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column: Top Users -->
            <div class="col-lg-4">
                <div class="bg-surface-container-lowest p-md rounded-xl card-shadow border border-outline-variant h-100 flex flex-col">
                    <div class="mb-4 pb-3 border-b border-outline-variant">
                        <h3 class="font-title-lg text-title-lg text-on-surface mb-1">Productivity</h3>
                        <p class="font-body-sm text-body-sm text-on-surface-variant mb-0">Top active contributors</p>
                    </div>
                    <div class="flex-grow-1">
                        @forelse($topUsers as $index => $user)
                            <div class="d-flex align-items-center gap-3 mb-3 p-2 rounded-lg hover:bg-surface-container-low transition-colors">
                                <div class="font-headline-sm text-headline-sm text-primary font-bold">#{{ $index + 1 }}</div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="font-label-md text-label-md text-on-surface">{{ $user['name'] }}</span>
                                        <span class="font-label-sm text-[11px] text-primary">{{ $user['count'] }} actions</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $user['percentage'] }}%"></div>
                                    </div>
                                    <div class="font-body-sm text-[11px] text-on-surface-variant mt-1">{{ $user['role'] }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-on-surface-variant font-body-sm">
                                <p>No data available</p>
                            </div>
                        @endforelse
                    </div>
                    <div class="mt-4 bg-primary bg-opacity-10 rounded-xl p-3 text-center">
                        <span class="font-headline-lg text-headline-lg text-primary d-block font-bold">{{ $stats['today_actions'] }}</span>
                        <span class="font-label-sm text-label-sm text-primary uppercase">Actions Today</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Charts Row -->
        <div class="row row-cols-1 row-cols-lg-2 g-4 mb-5">
            <div class="col">
                <div class="bg-surface-container-lowest p-md rounded-xl card-shadow border border-outline-variant h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-b border-outline-variant">
                        <h3 class="font-title-lg text-title-lg text-on-surface mb-0">Company Performance</h3>
                        <select class="form-select form-select-sm w-auto rounded-lg border-outline-variant font-body-sm text-body-sm" onchange="updateCompanyChart(this.value)">
                            <option value="income">Income</option>
                            <option value="expenses">Expenses</option>
                            <option value="balance">Net Balance</option>
                        </select>
                    </div>
                    <div style="height: 280px; position: relative;">
                        <canvas id="companyPerformanceChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="bg-surface-container-lowest p-md rounded-xl card-shadow border border-outline-variant h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-b border-outline-variant">
                        <h3 class="font-title-lg text-title-lg text-on-surface mb-0">Financial Trends</h3>
                        <select class="form-select form-select-sm w-auto rounded-lg border-outline-variant font-body-sm text-body-sm" onchange="updateFinancialChart(this.value)">
                            <option value="weekly" {{ request('range') == 'week' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ request('range') == 'month' ? 'selected' : '' }}>Monthly</option>
                        </select>
                    </div>
                    <div style="height: 280px; position: relative;">
                        <canvas id="financialOverviewChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Modal Refined -->
    <div class="modal fade" id="activityFilterModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-xl shadow-lg">
                <form method="GET" action="{{ route('admin.activity-logs.index') }}">
                    <div class="modal-header border-b border-outline-variant">
                        <h5 class="modal-title font-title-md text-on-surface">Advanced Filters</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="font-label-sm text-label-sm text-on-surface-variant mb-1">Action Type</label>
                                <select class="form-select form-select-sm rounded-lg border-outline-variant" name="action">
                                    <option value="">All Actions</option>
                                    <option value="created">Created</option>
                                    <option value="updated">Updated</option>
                                    <option value="deleted">Deleted</option>
                                    <option value="paid">Paid</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="font-label-sm text-label-sm text-on-surface-variant mb-1">User</label>
                                <select class="form-select form-select-sm rounded-lg border-outline-variant" name="user_id">
                                    <option value="">All Users</option>
                                    @foreach ($topUsers as $user)
                                        <option value="{{ $user['id'] ?? '' }}">{{ $user['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="font-label-sm text-label-sm text-on-surface-variant mb-1">Date From</label>
                                <input type="date" class="form-control form-control-sm rounded-lg border-outline-variant" name="date_from">
                            </div>
                            <div class="col-md-6">
                                <label class="font-label-sm text-label-sm text-on-surface-variant mb-1">Date To</label>
                                <input type="date" class="form-control form-control-sm rounded-lg border-outline-variant" name="date_to">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-t border-outline-variant bg-surface-container-lowest">
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-lg" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm rounded-lg" style="background-color: #4f46e5; border-color: #4f46e5;">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Manager Expense Details Modal -->
    <div class="modal fade" id="managerExpenseDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-xl shadow-lg">
                <div class="modal-header border-b border-outline-variant pb-3 bg-surface-container-low">
                    <div class="d-flex align-items-center gap-3">
                        <div id="modalManagerAvatar" class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 44px; height: 44px; font-size: 16px; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
                            M
                        </div>
                        <div>
                            <h5 class="modal-title font-title-md text-on-surface font-bold mb-0" id="modalManagerName">Manager Details</h5>
                            <div class="font-body-sm text-[12px] text-on-surface-variant" id="modalManagerEmail">manager@example.com</div>
                            <div class="font-body-sm text-[11px] text-primary mt-1" id="modalManagerCompanies"><i class="fas fa-building me-1"></i> Companies</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Quick KPI Badges inside modal -->
                    <div class="row g-2 mb-4">
                        <div class="col-sm-3 col-6">
                            <div class="p-2 rounded-lg bg-surface-container-low border border-outline-variant text-center">
                                <span class="font-label-sm text-[10px] text-muted uppercase d-block">Expenses Added</span>
                                <span class="font-headline-sm text-sm font-bold text-primary" id="modalTotalExpenses">0</span>
                            </div>
                        </div>
                        <div class="col-sm-3 col-6">
                            <div class="p-2 rounded-lg bg-surface-container-low border border-outline-variant text-center">
                                <span class="font-label-sm text-[10px] text-muted uppercase d-block">Total Value</span>
                                <span class="font-headline-sm text-sm font-bold text-success" id="modalTotalAmount">₹0</span>
                            </div>
                        </div>
                        <div class="col-sm-3 col-6">
                            <div class="p-2 rounded-lg bg-surface-container-low border border-outline-variant text-center">
                                <span class="font-label-sm text-[10px] text-muted uppercase d-block">Paid Expenses</span>
                                <span class="font-headline-sm text-sm font-bold text-success" id="modalPaidExpenses">0</span>
                            </div>
                        </div>
                        <div class="col-sm-3 col-6">
                            <div class="p-2 rounded-lg bg-surface-container-low border border-outline-variant text-center">
                                <span class="font-label-sm text-[10px] text-muted uppercase d-block">Pending</span>
                                <span class="font-headline-sm text-sm font-bold text-warning" id="modalPendingExpenses">0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Expenses List -->
                    <h6 class="font-label-md text-on-surface font-semibold mb-2 d-flex justify-content-between align-items-center">
                        <span>Recent Expenses Added</span>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary text-[11px]" id="modalExpensesCountBadge">0 listed</span>
                    </h6>
                    <div class="table-responsive rounded-lg border border-outline-variant" style="max-height: 320px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0" id="modalExpensesTable">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="font-label-sm text-[11px] text-on-surface-variant uppercase py-2 px-3">Expense #</th>
                                    <th class="font-label-sm text-[11px] text-on-surface-variant uppercase py-2 px-3">Purpose / Name</th>
                                    <th class="font-label-sm text-[11px] text-on-surface-variant uppercase py-2 px-3">Company</th>
                                    <th class="font-label-sm text-[11px] text-on-surface-variant uppercase py-2 px-3">Type</th>
                                    <th class="font-label-sm text-[11px] text-on-surface-variant uppercase py-2 px-3">Amount</th>
                                    <th class="font-label-sm text-[11px] text-on-surface-variant uppercase py-2 px-3">Status</th>
                                    <th class="font-label-sm text-[11px] text-on-surface-variant uppercase py-2 px-3">Date</th>
                                </tr>
                            </thead>
                            <tbody id="modalExpensesTbody">
                                <!-- Populated dynamically by openManagerDetailsModal -->
                            </tbody>
                        </table>
                    </div>
                    <div id="modalEmptyState" class="text-center py-4 d-none">
                        <span class="material-symbols-outlined text-muted text-[36px] mb-1">receipt_long</span>
                        <p class="font-body-sm text-muted mb-0">No expenses recorded for this manager yet.</p>
                    </div>
                </div>
                <div class="modal-footer border-t border-outline-variant bg-surface-container-lowest">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-lg" data-bs-dismiss="modal">Close</button>
                    <a href="{{ route('admin.standard-expenses') }}" class="btn btn-primary btn-sm rounded-lg" style="background-color: #4f46e5; border-color: #4f46e5;">
                        <i class="fas fa-external-link-alt me-1"></i> Go to All Expenses
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const managerReportData = @json($managerExpensesReport['managers']);
        let managerChartInstance = null;
        let activeChartMetric = 'count';

        document.addEventListener('DOMContentLoaded', function() {
            // Chart.js Default Overrides
            Chart.defaults.color = '#64748b';
            Chart.defaults.font.family = "'Inter', sans-serif";
            
            initializeFinancialChart();
            initializeCompanyChart();
            initializeManagerExpensesChart();

            window.applyFilters = () => document.getElementById('dashboardFilter').submit();
            window.resetFilters = () => window.location.href = "{{ route('admin.dashboard') }}";
        });

        function handleRangeChange(value) {
            const startDiv = document.getElementById('customStartDateDiv');
            const endDiv = document.getElementById('customEndDateDiv');
            if (value === 'custom') {
                if (startDiv) startDiv.classList.remove('d-none');
                if (endDiv) endDiv.classList.remove('d-none');
            } else {
                if (startDiv) startDiv.classList.add('d-none');
                if (endDiv) endDiv.classList.add('d-none');
                applyFilters();
            }
        }

        function filterManagerTable() {
            const query = (document.getElementById('managerSearchInput')?.value || '').toLowerCase();
            const rows = document.querySelectorAll('#managerExpensesTable tbody tr.manager-row');
            rows.forEach(row => {
                const name = row.querySelector('.manager-name')?.textContent.toLowerCase() || '';
                const email = row.querySelector('.manager-email')?.textContent.toLowerCase() || '';
                const companies = row.querySelector('.manager-companies')?.textContent.toLowerCase() || '';
                if (name.includes(query) || email.includes(query) || companies.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function exportManagerExpensesReport() {
            if (!managerReportData || managerReportData.length === 0) {
                alert('No data available to export.');
                return;
            }
            let csv = "Manager Name,Email,Role,Assigned Companies,Expenses Added (Filtered),Total Value Filtered (INR),Expenses (All-time),Total Value All-time (INR),Paid Count,Paid Amount (INR),Pending Count,Pending Amount (INR),Standard Count,Non-Standard Count,Last Added Date\n";
            managerReportData.forEach(m => {
                const cleanComp = (m.companies_string || '').replace(/"/g, '""');
                csv += `"${m.name}","${m.email}","${m.role}","${cleanComp}",${m.filtered_count},${m.filtered_amount},${m.all_time_count},${m.all_time_amount},${m.paid_count},${m.paid_amount},${m.pending_count},${m.pending_amount},${m.standard_count},${m.non_standard_count},"${m.last_added_date}"\n`;
            });
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.setAttribute("download", `manager_expenses_report_${new Date().toISOString().slice(0, 10)}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function openManagerDetailsModal(managerId) {
            const manager = managerReportData.find(m => m.id == managerId);
            if (!manager) return;

            document.getElementById('modalManagerName').textContent = manager.name;
            document.getElementById('modalManagerEmail').textContent = manager.email;
            document.getElementById('modalManagerCompanies').innerHTML = `<i class="fas fa-building me-1"></i> ${manager.companies_string || 'All Companies'}`;
            document.getElementById('modalManagerAvatar').textContent = (manager.name || 'M').charAt(0).toUpperCase();

            document.getElementById('modalTotalExpenses').textContent = manager.filtered_count + ' (' + manager.all_time_count + ' total)';
            document.getElementById('modalTotalAmount').textContent = '₹' + Number(manager.filtered_amount).toLocaleString();
            document.getElementById('modalPaidExpenses').textContent = manager.paid_count + ' (₹' + Number(manager.paid_amount).toLocaleString() + ')';
            document.getElementById('modalPendingExpenses').textContent = manager.pending_count + ' (₹' + Number(manager.pending_amount).toLocaleString() + ')';

            const tbody = document.getElementById('modalExpensesTbody');
            const emptyState = document.getElementById('modalEmptyState');
            const countBadge = document.getElementById('modalExpensesCountBadge');
            tbody.innerHTML = '';

            const expenses = manager.recent_expenses || [];
            countBadge.textContent = `${expenses.length} recent shown`;

            if (expenses.length === 0) {
                emptyState.classList.remove('d-none');
                document.getElementById('modalExpensesTable').classList.add('d-none');
            } else {
                emptyState.classList.add('d-none');
                document.getElementById('modalExpensesTable').classList.remove('d-none');

                expenses.forEach(exp => {
                    const tr = document.createElement('tr');
                    const statusClass = exp.status === 'paid' ? 'success' : (exp.status === 'overdue' ? 'danger' : 'warning');
                    tr.innerHTML = `
                        <td class="font-mono text-[11px] font-semibold py-2 px-3 text-primary">${exp.expense_number}</td>
                        <td class="font-body-sm text-[12px] py-2 px-3 text-on-surface font-medium">${exp.name}</td>
                        <td class="font-body-sm text-[11px] py-2 px-3 text-on-surface-variant">${exp.company}</td>
                        <td class="py-2 px-3"><span class="badge bg-light text-secondary border text-[10px]">${exp.source}</span></td>
                        <td class="font-body-sm text-[12px] py-2 px-3 font-bold text-on-surface">₹${Number(exp.amount).toLocaleString()}</td>
                        <td class="py-2 px-3"><span class="badge bg-${statusClass} bg-opacity-10 text-${statusClass} rounded-pill text-[10px] px-2 py-0">${exp.status.toUpperCase()}</span></td>
                        <td class="font-body-sm text-[11px] py-2 px-3 text-muted">${exp.date}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }

            const modal = new bootstrap.Modal(document.getElementById('managerExpenseDetailsModal'));
            modal.show();
        }

        function toggleManagerChartMetric(metric) {
            activeChartMetric = metric;
            const countBtn = document.getElementById('chartMetricCountBtn');
            const amountBtn = document.getElementById('chartMetricAmountBtn');
            if (metric === 'count') {
                countBtn.classList.add('active');
                amountBtn.classList.remove('active');
            } else {
                amountBtn.classList.add('active');
                countBtn.classList.remove('active');
            }
            renderManagerChart();
        }

        function renderManagerChart() {
            const ctx = document.getElementById('managerExpensesChart')?.getContext('2d');
            if (!ctx) return;

            if (managerChartInstance) {
                managerChartInstance.destroy();
            }

            const managersWithData = managerReportData.filter(m => (activeChartMetric === 'count' ? m.filtered_count : m.filtered_amount) > 0);
            const displayList = managersWithData.length > 0 ? managersWithData : managerReportData.slice(0, 6);

            const labels = displayList.map(m => m.name);
            const data = displayList.map(m => activeChartMetric === 'count' ? m.filtered_count : m.filtered_amount);
            const palette = ['#6366f1', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6', '#06b6d4', '#14b8a6', '#f97316'];

            managerChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels.length > 0 ? labels : ['No expenses recorded'],
                    datasets: [{
                        data: data.length > 0 && data.some(v => v > 0) ? data : [1],
                        backgroundColor: data.length > 0 && data.some(v => v > 0) ? palette.slice(0, data.length) : ['#e2e8f0'],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                boxWidth: 10,
                                font: { size: 11 },
                                padding: 8
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    if (activeChartMetric === 'amount') {
                                        return `${context.label}: ₹${Number(context.raw).toLocaleString()}`;
                                    }
                                    return `${context.label}: ${context.raw} expenses`;
                                }
                            }
                        }
                    }
                }
            });
        }

        function initializeManagerExpensesChart() {
            renderManagerChart();
        }

        function initializeFinancialChart() {
            const ctx = document.getElementById('financialOverviewChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($financialData['labels']),
                    datasets: [{
                        label: 'Income',
                        data: @json($financialData['income']),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3
                    }, {
                        label: 'Expenses',
                        data: @json($financialData['expenses']),
                        borderColor: '#f43f5e',
                        backgroundColor: 'rgba(244, 63, 94, 0.05)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { grid: { color: 'rgba(0,0,0,0.05)' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        function initializeCompanyChart() {
            const ctx = document.getElementById('companyPerformanceChart').getContext('2d');
            const companies = @json($companyPerformance);
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: companies.map(c => c.name),
                    datasets: [{
                        label: 'Income',
                        data: companies.map(c => c.monthly_income),
                        backgroundColor: '#6366f1',
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { grid: { color: 'rgba(0,0,0,0.05)' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }
    </script>
@endsection

