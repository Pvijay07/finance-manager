@extends('Manager.layouts.app')
@section('content')
    <div id="upcoming-payments" class="">
        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="filter-group">
                <div class="filter-label">Date Range</div>
                <select id="dateRange" onchange="applyFilters()">
                    <option value="today">Today</option>
                    <option value="7days" selected>Next 7 Days</option>
                    <option value="30days">Next 30 Days</option>
                    <option value="month">This Month</option>
                </select>
            </div>
            <div class="filter-group">
                <div class="filter-label">Company</div>
                <select id="companyFilter" onchange="applyFilters()">
                    <option value="">All Companies</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}" {{ request('company') == $company->id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <div class="filter-label">Type</div>
                <select id="typeFilter" onchange="applyFilters()">
                    <option value="">All Types</option>
                    <option value="debit" {{ request('type') == 'debit' ? 'selected' : '' }}>Debit</option>
                    <option value="credit" {{ request('type') == 'credit' ? 'selected' : '' }}>Credit</option>
                </select>
            </div>
            <div class="filter-group">
                <div class="filter-label">Status</div>
                <select id="statusFilter" onchange="applyFilters()">
                    <option value="">All Status</option>
                    <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
            <div class="filter-group">
                <button class="btn btn-outline" onclick="resetFilters()">
                    <i class="fas fa-redo"></i> Reset
                </button>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <div class="tab {{ request('tab', 'all') == 'all' ? 'active' : '' }}" onclick="switchTab('all')">All Payments
            </div>
            <div class="tab {{ request('tab') == 'debits' ? 'active' : '' }}" onclick="switchTab('debits')">Only Debits
            </div>
            <div class="tab {{ request('tab') == 'credits' ? 'active' : '' }}" onclick="switchTab('credits')">Only Credits
            </div>
            <div class="tab {{ request('tab') == 'overdue' ? 'active' : '' }}" onclick="switchTab('overdue')">Overdue</div>
        </div>

        <!-- Statistics Cards -->
        <div class="dashboard-grid">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Total Upcoming</div>
                    <div class="card-icon upcoming">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
                <div class="card-value">₹{{ number_format($stats['total_upcoming'] ?? 0, 2) }}</div>
                <div>{{ $stats['upcoming_count'] ?? 0 }} items</div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Total Debits</div>
                    <div class="card-icon expense">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                </div>
                <div class="card-value">₹{{ number_format($stats['total_debits'] ?? 0, 2) }}</div>
                <div>{{ $stats['debits_count'] ?? 0 }} expenses</div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Total Credits</div>
                    <div class="card-icon income">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                </div>
                <div class="card-value">₹{{ number_format($stats['total_credits'] ?? 0, 2) }}</div>
                <div>{{ $stats['credits_count'] ?? 0 }} incomes</div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Overdue</div>
                    <div class="card-icon overdue">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="card-value">₹{{ number_format($stats['total_overdue'] ?? 0, 2) }}</div>
                <div>{{ $stats['overdue_count'] ?? 0 }} items</div>
            </div>
        </div>

        <!-- Payments Table -->
        <div class="table-container">
            <div class="table-header">
                <div class="table-title">Upcoming Payments</div>
                <div class="table-actions">
                    <button class="btn btn-outline" onclick="toggleAdvancedFilters()">
                        <i class="fas fa-filter"></i> Advanced Filter
                    </button>
                    <button class="btn btn-primary" onclick="exportToExcel()">
                        <i class="fas fa-download"></i> Export
                    </button>
                </div>
            </div>

            <!-- Advanced Filters -->
            <div id="advancedFilters"
                style="display: none; padding: 15px; background: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small">From Date</label>
                        <input type="date" class="form-control form-control-sm" id="fromDate"
                            value="{{ request('from_date', date('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">To Date</label>
                        <input type="date" class="form-control form-control-sm" id="toDate"
                            value="{{ request('to_date', date('Y-m-d', strtotime('+30 days'))) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Min Amount</label>
                        <input type="number" class="form-control form-control-sm" id="minAmount" placeholder="₹"
                            value="{{ request('min_amount') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Max Amount</label>
                        <input type="number" class="form-control form-control-sm" id="maxAmount" placeholder="₹"
                            value="{{ request('max_amount') }}">
                    </div>
                    <div class="col-12 text-end">
                        <button class="btn btn-sm btn-primary" onclick="applyAdvancedFilters()">Apply</button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="resetAdvancedFilters()">Reset</button>
                    </div>
                </div>
            </div>

            @if (count($payments) > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Due Date</th>
                            <th>Company</th>
                            <th>Type</th>
                            <th>Item Name</th>
                            <th>Party Name</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th>Days Left</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payments as $payment)
                            <tr
                                class="{{ $payment['type'] == 'expense' ? 'debit-row' : 'credit-row' }} 
                               {{ $payment['is_overdue'] ? 'overdue-row' : '' }}
                               {{ $payment['is_today'] ? 'today-row' : '' }}">
                                <td>
                                    @if ($payment['is_today'])
                                        <span class="badge bg-danger">Today</span>
                                    @elseif($payment['is_tomorrow'])
                                        <span class="badge bg-warning">Tomorrow</span>
                                    @else
                                        {{ date('d M Y', strtotime($payment['date'])) }}
                                    @endif
                                </td>
                                <td>{{ $payment['company'] }}</td>
                                <td>
                                    @if ($payment['payment_type'] == 'Debit')
                                        <span class="badge bg-danger text-info">Debit</span>
                                    @else
                                        <span class="badge bg-success text-info">Credit</span>
                                    @endif
                                </td>
                                <td>{{ $payment['description'] }}</td>
                                <td>{{ $payment['party_name'] ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $payment['category'] }}</span>
                                </td>
                                <td class="{{ $payment['type'] == 'expense' ? 'text-danger' : 'text-success' }}">
                                    {{ $payment['type'] == 'expense' ? '-' : '+' }}₹{{ number_format(abs($payment['amount']), 2) }}
                                </td>
                                <td>
                                    @if ($payment['source'] == 'standard')
                                        <span class="badge bg-primary">Standard</span>
                                    @elseif($payment['source'] == 'income')
                                        <span class="badge bg-info">Income</span>
                                    @else
                                        <span class="badge bg-info">Non-Standard</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusClass =
                                            [
                                                'pending' => 'pending',
                                                'upcoming' => 'upcoming',
                                                'overdue' => 'overdue',
                                                'paid' => 'paid',
                                            ][$payment['status']] ?? 'upcoming';
                                    @endphp
                                    <span class="status {{ $statusClass }}">{{ ucfirst($payment['status']) }}</span>
                                </td>
                                <td>
                                    @if ($payment['days_left'] === 0)
                                        <span class="badge bg-danger">Due Today</span>
                                    @elseif($payment['days_left'] < 0)
                                        <span class="badge bg-dark">Overdue {{ abs($payment['days_left']) }} days</span>
                                    @else
                                        {{ $payment['days_left'] }} days
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        @if ($payment['type'] == 'expense' && $payment['status'] != 'paid')
                                            <button class="btn btn-success btn-sm"
                                                onclick="markAsPaid({{ $payment['id'] }}, 'expense')">
                                                <i class="fas fa-check"></i> Mark Paid
                                            </button>
                                        @endif

                                        @if ($payment['type'] == 'income' && $payment['status'] != 'received')
                                            <button class="btn btn-success btn-sm"
                                                onclick="markAsReceived({{ $payment['id'] }})">
                                                <i class="fas fa-check"></i> Mark Received
                                            </button>
                                        @endif

                                        <button class="btn btn-outline btn-sm"
                                            onclick="viewDetails({{ $payment['id'] }}, '{{ $payment['type'] }}')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Upcoming Payments</h5>
                    <p class="text-muted">You're all caught up! No payments scheduled.</p>
                </div>
            @endif
        </div>

        <!-- Timeline View (Optional) -->
        @if (count($groupedPayments) > 0 && request('view') == 'timeline')
            <div class="card mt-4">
                <div class="card-header">
                    <div class="card-title">Payment Timeline</div>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach ($groupedPayments as $date => $items)
                            <div class="timeline-date">
                                <div class="timeline-date-header">
                                    <h6>{{ \Carbon\Carbon::parse($date)->format('d F Y') }}</h6>
                                    <span class="badge bg-primary">{{ count($items) }} items</span>
                                </div>

                                <div class="timeline-items">
                                    @foreach ($items as $item)
                                        <div
                                            class="timeline-item {{ $item['type'] == 'expense' ? 'expense-item' : 'income-item' }}">
                                            <div class="timeline-item-icon">
                                                @if ($item['type'] == 'expense')
                                                    <i class="fas fa-arrow-up text-danger"></i>
                                                @else
                                                    <i class="fas fa-arrow-down text-success"></i>
                                                @endif
                                            </div>
                                            <div class="timeline-item-content">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <h6 class="mb-1">{{ $item['description'] }}</h6>
                                                        <div class="text-muted small">
                                                            {{ $item['company'] }} • {{ $item['category'] }}
                                                        </div>
                                                    </div>
                                                    <div class="text-end">
                                                        <div
                                                            class="h6 mb-0 {{ $item['type'] == 'expense' ? 'text-danger' : 'text-success' }}">
                                                            {{ $item['type'] == 'expense' ? '-' : '+' }}₹{{ number_format(abs($item['amount']), 2) }}
                                                        </div>
                                                        <span
                                                            class="badge {{ $item['status'] == 'pending' ? 'bg-warning' : 'bg-info' }}">
                                                            {{ ucfirst($item['status']) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script>
        // Filter functions
        function applyFilters() {
            const dateRange = document.getElementById('dateRange').value;
            const companyId = document.getElementById('companyFilter').value;
            const type = document.getElementById('typeFilter').value;
            const status = document.getElementById('statusFilter').value;
            const tab = '{{ request('tab', 'all') }}';

            const params = new URLSearchParams();
            params.append('range', dateRange);
            if (companyId) params.append('company', companyId);
            if (type) params.append('type', type);
            if (status) params.append('status', status);
            params.append('tab', tab);

            window.location.href = '{{ route('income.upcoming') }}?' + params.toString();
        }

        function resetFilters() {
            document.getElementById('dateRange').value = '7days';
            document.getElementById('companyFilter').value = '';
            document.getElementById('typeFilter').value = '';
            document.getElementById('statusFilter').value = '';
            applyFilters();
        }

        function switchTab(tabName) {
            const params = new URLSearchParams(window.location.search);
            params.set('tab', tabName);
            window.location.href = '{{ route('income.upcoming') }}?' + params.toString();
        }

        function toggleAdvancedFilters() {
            const filters = document.getElementById('advancedFilters');
            filters.style.display = filters.style.display === 'none' ? 'block' : 'none';
        }

        function applyAdvancedFilters() {
            const fromDate = document.getElementById('fromDate').value;
            const toDate = document.getElementById('toDate').value;
            const minAmount = document.getElementById('minAmount').value;
            const maxAmount = document.getElementById('maxAmount').value;

            const params = new URLSearchParams(window.location.search);
            if (fromDate) params.set('from_date', fromDate);
            if (toDate) params.set('to_date', toDate);
            if (minAmount) params.set('min_amount', minAmount);
            if (maxAmount) params.set('max_amount', maxAmount);

            window.location.href = '{{ route('income.upcoming') }}?' + params.toString();
        }

        function resetAdvancedFilters() {
            const today = new Date().toISOString().split('T')[0];
            const next30Days = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];

            document.getElementById('fromDate').value = today;
            document.getElementById('toDate').value = next30Days;
            document.getElementById('minAmount').value = '';
            document.getElementById('maxAmount').value = '';
        }

        // Action functions
        function markAsPaid(expenseId, type) {
            if (confirm('Mark this payment as paid?')) {
                const url = type === 'expense' ?
                    `/expenses/${expenseId}/mark-paid` :
                    `/income/${expenseId}/mark-received`;

                fetch(url, {
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
                        alert('Error updating payment status');
                    });
            }
        }

        function markAsReceived(incomeId) {
            if (confirm('Mark this income as received?')) {
                fetch(`/income/${incomeId}/mark-received`, {
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
                        alert('Error updating income status');
                    });
            }
        }

        function viewDetails(id, type) {
            if (type === 'expense') {
                window.location.href = `/expenses/${id}/edit`;
            } else {
                window.location.href = `/income/${id}/edit`;
            }
        }

        function exportToExcel() {
            // This would typically make an API call to generate Excel
            alert('');
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Set default dates for advanced filters
            const today = new Date().toISOString().split('T')[0];
            const next30Days = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];

            if (!document.getElementById('fromDate').value) {
                document.getElementById('fromDate').value = today;
            }
            if (!document.getElementById('toDate').value) {
                document.getElementById('toDate').value = next30Days;
            }

            // Highlight today's and overdue rows
            document.querySelectorAll('.today-row').forEach(row => {
                row.style.backgroundColor = '#fff3cd';
            });

            document.querySelectorAll('.overdue-row').forEach(row => {
                row.style.backgroundColor = '#f8d7da';
            });
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

        .card-icon.upcoming {
            background: #ffc107;
        }

        .card-icon.expense {
            background: #dc3545;
        }

        .card-icon.income {
            background: #28a745;
        }

        .card-icon.overdue {
            background: #6c757d;
        }

        .card-value {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .tabs {
            display: flex;
            border-bottom: 2px solid #dee2e6;
            margin-bottom: 20px;
        }

        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border: 1px solid transparent;
            border-bottom: none;
            margin-bottom: -2px;
            background: #f8f9fa;
            color: #6c757d;
            font-weight: 500;
        }

        .tab:hover {
            background: #e9ecef;
        }

        .tab.active {
            background: white;
            color: #495057;
            border-color: #dee2e6 #dee2e6 white;
            border-top-left-radius: 4px;
            border-top-right-radius: 4px;
        }

        .table-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .table-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        table tr:hover {
            background: #f8f9fa;
        }

        .debit-row {
            border-left: 4px solid #dc3545;
        }

        .credit-row {
            border-left: 4px solid #28a745;
        }

        .overdue-row {
            background-color: #f8d7da !important;
        }

        .today-row {
            background-color: #fff3cd !important;
        }

        .status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .status.pending {
            background: #fff3cd;
            color: #856404;
        }

        .status.upcoming {
            background: #cff4fc;
            color: #055160;
        }

        .status.overdue {
            background: #f8d7da;
            color: #721c24;
        }

        .status.paid {
            background: #d4edda;
            color: #155724;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .bg-primary {
            background: #007bff;
        }

        .bg-secondary {
            background: #6c757d;
        }

        .bg-success {
            background: #28a745;
        }

        .bg-danger {
            background: #dc3545;
        }

        .bg-warning {
            background: #ffc107;
            color: #212529;
        }

        .bg-info {
            background: #17a2b8;
        }

        .bg-dark {
            background: #343a40;
        }

        .btn-group {
            display: flex;
            gap: 5px;
        }

        .btn-sm {
            padding: 4px 8px;
            font-size: 12px;
        }

        .text-danger {
            color: #dc3545 !important;
        }

        .text-success {
            color: #28a745 !important;
        }

        /* Timeline styles */
        .timeline {
            padding: 20px;
        }

        .timeline-date {
            margin-bottom: 30px;
        }

        .timeline-date-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 15px;
        }

        .timeline-items {
            padding-left: 20px;
        }

        .timeline-item {
            display: flex;
            margin-bottom: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }

        .timeline-item.expense-item {
            border-left-color: #dc3545;
        }

        .timeline-item.income-item {
            border-left-color: #28a745;
        }

        .timeline-item-icon {
            width: 40px;
            font-size: 1.2rem;
        }

        .timeline-item-content {
            flex: 1;
        }

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

        .filter-group select {
            padding: 6px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            min-width: 150px;
        }
    </style>
@endsection
