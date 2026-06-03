@extends('Admin.layouts.app')
@section('content')
    <div id="dashboard" class="page active dashboard-premium">
        <!-- Dashboard Header -->
        <div class="dashboard-header-modern mb-4">
            <div class="header-content">
                <h1 class="header-title">Analytics Overview</h1>
                <p class="header-subtitle">Welcome back! Here's what's happening today.</p>
            </div>
            <div class="header-actions">
                <form method="GET" action="{{ route('admin.dashboard') }}" id="dashboardFilter" class="modern-filter-form">
                    <div class="filter-pill-group">
                        <div class="filter-pill">
                            <i class="fas fa-calendar-alt"></i>
                            <select name="range" onchange="applyFilters()">
                                <option value="today" {{ request('range') == 'today' ? 'selected' : '' }}>Today</option>
                                <option value="week" {{ request('range') == 'week' || !request('range') ? 'selected' : '' }}>This Week</option>
                                <option value="month" {{ request('range') == 'month' ? 'selected' : '' }}>This Month</option>
                                <option value="custom" {{ request('range') == 'custom' ? 'selected' : '' }}>Custom</option>
                            </select>
                        </div>
                        <div class="filter-pill">
                            <i class="fas fa-building"></i>
                            <select name="company" onchange="applyFilters()">
                                <option value="">All Companies</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}" {{ request('company') == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>
                <button type="button" class="btn btn-glass" onclick="resetFilters()">
                    <i class="fas fa-redo"></i>
                </button>
            </div>
        </div>

        <!-- KPI Cards Grid -->
        <div class="dashboard-grid-modern">
            <div class="kpi-card glass-card purple">
                <div class="card-glow"></div>
                <div class="kpi-content">
                    <div class="kpi-header">
                        <span class="kpi-label">Total Companies</span>
                        <div class="kpi-icon">
                            <i class="fas fa-building"></i>
                        </div>
                    </div>
                    <div class="kpi-value">{{ $stats['total_companies'] }}</div>
                    <div class="kpi-footer">
                        <span class="trend-pos"><i class="fas fa-check-circle"></i> {{ $stats['active_companies'] }} active</span>
                        <a href="{{ route('admin.companies') }}" class="kpi-link">Manage <i class="fas fa-chevron-right"></i></a>
                    </div>
                </div>
            </div>

            <div class="kpi-card glass-card blue">
                <div class="card-glow"></div>
                <div class="kpi-content">
                    <div class="kpi-header">
                        <span class="kpi-label">System Users</span>
                        <div class="kpi-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="kpi-value">{{ $stats['total_users'] }}</div>
                    <div class="kpi-footer">
                        <span>{{ $stats['admin_users'] }} Admins • {{ $stats['manager_users'] }} Managers</span>
                        <a href="{{ route('admin.users') }}" class="kpi-link">Manage <i class="fas fa-chevron-right"></i></a>
                    </div>
                </div>
            </div>

            <div class="kpi-card glass-card orange">
                <div class="card-glow"></div>
                <div class="kpi-content">
                    <div class="kpi-header">
                        <span class="kpi-label">Expense Types</span>
                        <div class="kpi-icon">
                            <i class="fas fa-tags"></i>
                        </div>
                    </div>
                    <div class="kpi-value">{{ $stats['expense_types'] }}</div>
                    <div class="kpi-footer">
                        <span>{{ $stats['active_expense_types'] }} Categorized</span>
                        <a href="{{ route('admin.expensetypes') }}" class="kpi-link">View All <i class="fas fa-chevron-right"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary Stats Grid -->
        <div class="dashboard-grid-modern mt-4">
            <div class="stat-card glass-card compact">
                <div class="stat-icon-bg"><i class="fas fa-exchange-alt"></i></div>
                <div class="stat-data">
                    <span class="stat-label">Total Transactions</span>
                    <div class="stat-value">₹{{ number_format($stats['total_amount']) }}</div>
                    <div class="stat-meta">{{ $stats['total_transactions'] }} entries registered</div>
                </div>
            </div>
            <div class="stat-card glass-card compact warning">
                <div class="stat-icon-bg"><i class="fas fa-clock"></i></div>
                <div class="stat-data">
                    <span class="stat-label">Pending Review</span>
                    <div class="stat-value">{{ $stats['pending_items'] }}</div>
                    <div class="stat-meta">{{ $stats['pending_invoices'] }} Invoices, {{ $stats['pending_expenses'] }} Expenses</div>
                </div>
            </div>
            <div class="stat-card glass-card compact danger">
                <div class="stat-icon-bg"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="stat-data">
                    <span class="stat-label">Overdue</span>
                    <div class="stat-value">₹{{ number_format($stats['overdue_amount']) }}</div>
                    <div class="stat-meta">{{ $stats['overdue_payments'] }} critical alerts</div>
                </div>
            </div>
        </div>

        <!-- Dynamic Visualization & Lists -->
        <div class="content-row mt-4">
            <!-- Left Column: Activity & Top Users -->
            <div class="row-main">
                <div class="card-glass-outer">
                    <div class="card-glass-header">
                        <div class="header-info">
                            <h3>Recent System Activity</h3>
                            <p>Live stream of administrative actions</p>
                        </div>
                        <div class="header-actions">
                            <button class="btn btn-glass-sm" data-bs-toggle="modal" data-bs-target="#activityFilterModal">
                                <i class="fas fa-filter"></i>
                            </button>
                            <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-primary-modern">
                                View Full History
                            </a>
                        </div>
                    </div>
                    <div class="table-responsive-modern">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>User & Action</th>
                                    <th>Resource</th>
                                    <th>Details</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentActivities as $activity)
                                    <tr>
                                        <td class="td-user">
                                            <div class="user-block">
                                                <div class="avatar-modern">
                                                    <span>{{ substr($activity['user'], 0, 1) }}</span>
                                                </div>
                                                <div class="user-primary">
                                                    <span class="u-name">{{ $activity['user'] }}</span>
                                                    <span class="u-action action-{{ $activity['action_color'] }}">{{ $activity['action'] }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="resource-tag">{{ $activity['resource'] }}</span></td>
                                        <td class="td-details small">{!! $activity['details'] !!}</td>
                                        <td>
                                            <div class="time-block">
                                                <span class="d-date">{{ $activity['date'] }}</span>
                                                <span class="d-time">{{ $activity['time'] }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="empty-state">
                                            <div class="empty-icon"><i class="fas fa-history"></i></div>
                                            <p>No activity logs found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column: Top Users -->
            <div class="row-side">
                <div class="card-glass-outer h-100">
                    <div class="card-glass-header">
                        <div class="header-info">
                            <h3>Productivity</h3>
                            <p>Top active contributors</p>
                        </div>
                    </div>
                    <div class="top-performers">
                        @forelse($topUsers as $index => $user)
                            <div class="performer-item">
                                <div class="rank">#{{ $index + 1 }}</div>
                                <div class="performer-info">
                                    <div class="p-header">
                                        <span class="p-name">{{ $user['name'] }}</span>
                                        <span class="p-count">{{ $user['count'] }} actions</span>
                                    </div>
                                    <div class="p-bar-container">
                                        <div class="p-bar" style="width: {{ $user['percentage'] }}%"></div>
                                    </div>
                                    <span class="p-role">{{ $user['role'] }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state small">
                                <p>No data available</p>
                            </div>
                        @endforelse
                    </div>
                    <div class="daily-summary">
                        <div class="summary-box">
                            <span class="s-val">{{ $stats['today_actions'] }}</span>
                            <span class="s-label">Actions Today</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Charts Row -->
        <div class="content-row mt-4 mb-5">
            <div class="row-half">
                <div class="card-glass-outer">
                    <div class="card-glass-header">
                        <h3>Company Performance</h3>
                        <div class="header-actions">
                            <select class="form-select-modern" onchange="updateCompanyChart(this.value)">
                                <option value="income">Income</option>
                                <option value="expenses">Expenses</option>
                                <option value="balance">Net Balance</option>
                            </select>
                        </div>
                    </div>
                    <div class="chart-box">
                        <canvas id="companyPerformanceChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="row-half">
                <div class="card-glass-outer">
                    <div class="card-glass-header">
                        <h3>Financial Trends</h3>
                        <div class="header-actions">
                            <select class="form-select-modern" onchange="updateFinancialChart(this.value)">
                                <option value="weekly" {{ request('range') == 'week' ? 'selected' : '' }}>Weekly</option>
                                <option value="monthly" {{ request('range') == 'month' ? 'selected' : '' }}>Monthly</option>
                            </select>
                        </div>
                    </div>
                    <div class="chart-box">
                        <canvas id="financialOverviewChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Modal Refined -->
    <div class="modal fade" id="activityFilterModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-modal text-white" style="background: #1e293b; border-radius: 20px;">
                <form method="GET" action="{{ route('admin.activity-logs.index') }}">
                    <div class="modal-header border-0">
                        <h5 class="modal-title">Advanced Filters</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="label-modern">Action Type</label>
                                <select class="form-select-modern w-100" name="action">
                                    <option value="">All Actions</option>
                                    <option value="created">Created</option>
                                    <option value="updated">Updated</option>
                                    <option value="deleted">Deleted</option>
                                    <option value="paid">Paid</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="label-modern">User</label>
                                <select class="form-select-modern w-100" name="user_id">
                                    <option value="">All Users</option>
                                    @foreach ($topUsers as $user)
                                        <option value="{{ $user['id'] ?? '' }}">{{ $user['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="label-modern">Date From</label>
                                <input type="date" class="form-control-modern" name="date_from">
                            </div>
                            <div class="col-md-6">
                                <label class="label-modern">Date To</label>
                                <input type="date" class="form-control-modern" name="date_to">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn text-white" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary-modern">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Chart.js Default Overrides
            Chart.defaults.color = '#94a3b8';
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
                        y: { grid: { color: 'rgba(255,255,255,0.05)' } },
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
                        y: { grid: { color: 'rgba(255,255,255,0.05)' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }
    </script>
<style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: radial-gradient(circle at 10% 20%, #0f172a, #020617);
            font-family: 'Inter', sans-serif;
            color: #e2e8f0;
            padding: 2rem 1.5rem;
            min-height: 100vh;
        }

        /* Custom scroll */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #1e293b;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: #475569;
            border-radius: 10px;
        }

        /* glassmorphism core */
        .glass-card, .card-glass-outer, .stat-card {
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(12px);
            border-radius: 28px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.25s ease;
            box-shadow: 0 20px 35px -12px rgba(0, 0, 0, 0.4);
        }

        .glass-card:hover, .card-glass-outer:hover {
            border-color: rgba(99, 102, 241, 0.5);
            background: rgba(15, 23, 42, 0.75);
            transform: translateY(-2px);
        }

        /* dashboard header modern */
        .dashboard-header-modern {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            gap: 1rem;
        }

        .header-title {
            font-size: 1.9rem;
            font-weight: 700;
            background: linear-gradient(135deg, #fff, #a5b4fc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            letter-spacing: -0.3px;
        }

        .header-subtitle {
            color: #94a3b8;
            font-size: 0.9rem;
            margin-top: 6px;
        }

        .modern-filter-form .filter-pill-group {
            display: flex;
            gap: 12px;
            background: rgba(30, 41, 59, 0.6);
            padding: 6px 12px;
            border-radius: 60px;
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255,255,255,0.05);
        }

        .filter-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #0f172a;
            padding: 6px 16px;
            border-radius: 40px;
            color: #cbd5e1;
        }

        .filter-pill i {
            font-size: 0.85rem;
            color: #818cf8;
        }

        .filter-pill select {
            background: transparent;
            border: none;
            color: #f1f5f9;
            font-weight: 500;
            font-size: 0.85rem;
            outline: none;
            cursor: pointer;
        }

        .btn-glass {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 40px;
            padding: 8px 18px;
            color: #e2e8f0;
            transition: 0.2s;
        }

        .btn-glass:hover {
            background: #6366f1;
            border-color: #6366f1;
            color: white;
        }

        /* KPI cards grid */
        .dashboard-grid-modern {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.6rem;
        }

        .kpi-card {
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .card-glow {
            position: absolute;
            top: -20%;
            right: -10%;
            width: 150px;
            height: 150px;
            background: radial-gradient(circle, rgba(99,102,241,0.2) 0%, rgba(0,0,0,0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .kpi-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .kpi-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            color: #94a3b8;
        }

        .kpi-icon {
            width: 44px;
            height: 44px;
            background: rgba(99,102,241,0.2);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: #a5b4fc;
        }

        .kpi-value {
            font-size: 2.6rem;
            font-weight: 800;
            letter-spacing: -1px;
            line-height: 1.2;
            margin-bottom: 0.75rem;
        }

        .kpi-footer {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            color: #94a3b8;
        }

        .kpi-link {
            color: #a5b4fc;
            text-decoration: none;
            font-weight: 500;
            transition: 0.2s;
        }

        .kpi-link:hover { color: #0f172a; }

        .trend-pos i { color: #10b981; margin-right: 4px; }

        /* secondary stat cards compact */
        .stat-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.2rem 1.5rem;
        }
        .stat-icon-bg {
            width: 52px;
            height: 52px;
            background: rgba(99,102,241,0.15);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #818cf8;
        }
        .stat-data .stat-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 600;
            color: #94a3b8;
        }
        .stat-value {
            font-size: 1.7rem;
            font-weight: 800;
            line-height: 1.2;
        }
        .stat-meta {
            font-size: 0.7rem;
            color: #6c7a91;
        }
        .warning .stat-icon-bg { background: rgba(245,158,11,0.15); color: #fbbf24; }
        .danger .stat-icon-bg { background: rgba(239,68,68,0.15); color: #f87171; }

        /* content row layout */
        .content-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1.6rem;
        }
        .row-main { flex: 2.2; min-width: 260px; }
        .row-side { flex: 1.2; min-width: 260px; }
        .row-half { flex: 1; min-width: 280px; }

        .card-glass-outer {
            padding: 1.2rem;
            border-radius: 28px;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .card-glass-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 1.2rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .card-glass-header h3 {
            font-size: 1.2rem;
            font-weight: 600;
            margin: 0;
        }
        .card-glass-header p {
            font-size: 0.7rem;
            color: #7e8aa2;
            margin: 0;
        }

        .table-responsive-modern {
            overflow-x: auto;
        }
        .table-modern {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
        }
        .table-modern th {
            text-align: left;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #8c9ab5;
            padding: 0.5rem 0.2rem;
        }
        .table-modern td {
            padding: 0.8rem 0.2rem;
            border-bottom: 1px solid rgba(255,255,255,0.04);
        }
        .user-block {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .avatar-modern {
            width: 36px;
            height: 36px;
            background: linear-gradient(145deg, #2d3a5e, #1e293b);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
        }
        .u-name {
            font-weight: 600;
            font-size: 0.85rem;
        }
        .u-action {
            font-size: 0.7rem;
            background: rgba(16,185,129,0.2);
            padding: 2px 8px;
            border-radius: 30px;
            color: #6ee7b7;
        }
        .action-warning { background: rgba(245,158,11,0.2); color: #fcd34d; }
        .resource-tag {
            background: #1e293b;
            padding: 4px 10px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .time-block .d-date {
            font-size: 0.7rem;
            font-weight: 500;
        }
        .time-block .d-time {
            font-size: 0.65rem;
            color: #7e8aa2;
            display: block;
        }

        /* top performers */
        .top-performers {
            margin-top: 0.5rem;
        }
        .performer-item {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0.5rem 0;
            border-bottom: 1px dashed rgba(255,255,255,0.05);
        }
        .rank {
            font-weight: 800;
            font-size: 1.2rem;
            width: 32px;
            color: #a5b4fc;
        }
        .performer-info {
            flex: 1;
        }
        .p-header {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
        }
        .p-name { font-weight: 600; }
        .p-count { color: #a5b4fc; font-weight: 500; font-size: 0.75rem; }
        .p-bar-container {
            background: #1e293b;
            border-radius: 12px;
            height: 6px;
            margin: 6px 0;
            overflow: hidden;
        }
        .p-bar {
            background: linear-gradient(90deg, #818cf8, #6366f1);
            height: 6px;
            border-radius: 12px;
            width: 0%;
        }
        .p-role {
            font-size: 0.65rem;
            color: #7e8aa2;
        }
        .daily-summary {
            margin-top: 1rem;
            text-align: center;
            background: rgba(0,0,0,0.25);
            border-radius: 20px;
            padding: 0.8rem;
        }
        .s-val {
            font-size: 2rem;
            font-weight: 800;
            display: block;
            line-height: 1;
        }
        .chart-box {
            height: 280px;
            position: relative;
            margin-top: 0.5rem;
        }
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #6c7a91;
        }
        .btn-primary-modern {
            background: #6366f1;
            border: none;
            padding: 6px 14px;
            border-radius: 40px;
            font-size: 0.75rem;
            font-weight: 500;
            transition: 0.2s;
        }
        .btn-primary-modern:hover {
            background: #4f46e5;
            transform: scale(0.98);
        }
        .form-select-modern, .form-control-modern {
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 40px;
            padding: 8px 16px;
            color: #e2e8f0;
            font-size: 0.85rem;
        }
        .label-modern {
            font-size: 0.7rem;
            font-weight: 500;
            margin-bottom: 4px;
            display: block;
            color: #9ca3af;
        }
        .modal-content.glass-modal {
            background: #0f172aee;
            backdrop-filter: blur(16px);
        }
        @media (max-width: 768px) {
            body { padding: 1rem; }
            .dashboard-header-modern { flex-direction: column; align-items: stretch; }
        }
        </style>
 
@endsection
