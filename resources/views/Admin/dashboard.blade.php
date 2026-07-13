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
                    <select name="range" onchange="applyFilters()" class="border-outline-variant rounded-lg font-body-sm text-body-sm bg-surface-container-low focus:ring-secondary py-2 px-3 w-full border">
                        <option value="today" {{ request('range') == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="week" {{ request('range') == 'week' || !request('range') ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ request('range') == 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="custom" {{ request('range') == 'custom' ? 'selected' : '' }}>Custom</option>
                    </select>
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Chart.js Default Overrides
            Chart.defaults.color = '#64748b';
            Chart.defaults.font.family = "'Inter', sans-serif";
            
            initializeFinancialChart();
            initializeCompanyChart();

            window.applyFilters = () => document.getElementById('dashboardFilter').submit();
            window.resetFilters = () => window.location.href = "{{ route('admin.dashboard') }}";
        });

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

