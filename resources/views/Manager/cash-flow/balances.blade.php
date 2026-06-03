@extends('Manager.layouts.app')
@section('content')
    <div id="balances" class="pge">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">Total Income</h6>
                        <h4 class="card-title text-success">₹{{ number_format($overallStats['total_income']) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">Total Expenses</h6>
                        <h4 class="card-title text-danger">₹{{ number_format($overallStats['total_expenses']) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">Net Balance</h6>
                        <h4 class="card-title {{ $overallStats['net_balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                            ₹{{ number_format($overallStats['net_balance']) }}
                        </h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">Total Net Dues</h6>
                        <h4 class="card-title {{ $overallStats['total_net_dues'] >= 0 ? 'text-warning' : 'text-danger' }}">
                            ₹{{ number_format($overallStats['total_net_dues']) }}
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('income.balance') }}" id="filterForm">
            <div class="filter-bar mb-3">
                <div class="filter-group">
                    <div class="filter-label">Company</div>
                    <select name="company" onchange="applyFilters()">
                        <option value="">All Companies</option>
                        @foreach(\App\Models\Company::where('status', 'active')->get() as $company)
                            <option value="{{ $company->id }}" {{ request('company') == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <div class="filter-label">Sort By</div>
                    <select name="sort" onchange="applyFilters()">
                        <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Company Name</option>
                        <option value="balance_desc" {{ request('sort') == 'balance_desc' ? 'selected' : '' }}>Highest Balance</option>
                        <option value="balance_asc" {{ request('sort') == 'balance_asc' ? 'selected' : '' }}>Lowest Balance</option>
                        <option value="dues_desc" {{ request('sort') == 'dues_desc' ? 'selected' : '' }}>Highest Dues</option>
                    </select>
                </div>
                <div class="filter-group">
                    <button type="button" class="btn btn-outline" onclick="resetFilters()">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </div>
        </form>

        <!-- Company Balances Table -->
        <div class="table-container mb-5">
            <div class="table-header">
                <div class="table-title">Company Balances</div>
                <div class="table-actions">
                    <button class="btn btn-outline" data-bs-toggle="modal" data-bs-target="#settlementModal">
                        <i class="fas fa-hand-holding-usd"></i> Record Settlement
                    </button>
                    <button class="btn btn-primary" onclick="exportBalances()">
                        <i class="fas fa-download"></i> Export
                    </button>
                </div>
            </div>
            
            @if($companies->count() > 0)
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Total Income</th>
                            <th>Total Expenses</th>
                            <th>Net Balance</th>
                            <th>Pending Income</th>
                            <th>Pending Expenses</th>
                            <th>Net Dues</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($companies as $company)
                            <tr>
                                <td>
                                    <strong>{{ $company['name'] }}</strong><br>
                                    <small class="text-muted">Code: {{ $company['code'] }}</small>
                                </td>
                                <td class="text-success">
                                    <strong>₹{{ number_format($company['total_income']) }}</strong>
                                </td>
                                <td class="text-danger">
                                    <strong>₹{{ number_format($company['total_expenses']) }}</strong>
                                </td>
                                <td>
                                    <strong class="{{ $company['net_balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        ₹{{ number_format($company['net_balance']) }}
                                    </strong>
                                </td>
                                <td>
                                    <span class="badge bg-warning">
                                        ₹{{ number_format($company['dues']['income']) }}
                                        @if($company['pending_income_count'] > 0)
                                            <span class="badge bg-dark ms-1">{{ $company['pending_income_count'] }}</span>
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-danger">
                                        ₹{{ number_format($company['dues']['expenses']) }}
                                        @if($company['pending_expense_count'] > 0)
                                            <span class="badge bg-dark ms-1">{{ $company['pending_expense_count'] }}</span>
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $company['dues']['net'] >= 0 ? 'bg-warning' : 'bg-danger' }}">
                                        ₹{{ number_format($company['dues']['net']) }}
                                    </span>
                                </td>
                                <td>
                                    @if($company['dues']['net'] == 0)
                                        <span class="status settled">Settled</span>
                                    @else
                                        <span class="status pending">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-info" onclick="viewCompanyDetails({{ $company['id'] }})"
                                                title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <!-- <button class="btn btn-sm btn-success" 
                                                onclick="openSettleModal('company', {{ $company['id'] }})"
                                                title="Settle Dues">
                                            <i class="fas fa-check"></i>
                                        </button> -->
                                        <button class="btn btn-sm btn-primary" 
                                                onclick="viewTransactions({{ $company['id'] }})"
                                                title="View Transactions">
                                            <i class="fas fa-exchange-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No company balances found.
                </div>
            @endif
        </div>

        <!-- Dues Details Table -->
        <div class="table-container">
            <div class="table-header">
                <div class="table-title">Detailed Dues & Pending Items</div>
            </div>
            
            <div class="accordion" id="duesAccordion">
                @foreach($companies as $company)
                    @if($company['dues']['net'] != 0)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading{{ $company['id'] }}">
                                <button class="accordion-button collapsed" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#collapse{{ $company['id'] }}">
                                    {{ $company['name'] }} - Pending Dues: ₹{{ number_format($company['dues']['net']) }}
                                </button>
                            </h2>
                            <div id="collapse{{ $company['id'] }}" class="accordion-collapse collapse" 
                                 data-bs-parent="#duesAccordion">
                                <div class="accordion-body">
                                    <!-- This would be populated via AJAX when expanded -->
                                    <div class="text-center">
                                        <div class="spinner-border" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p>Loading details...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <!-- Settlement Modal -->
    <div class="modal fade" id="settlementModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Record Settlement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="settlementForm" method="POST" action="{{ route('manager.settlements.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="settlementCompany" class="form-label">Company *</label>
                                <select class="form-select" id="settlementCompany" name="company_id" required>
                                    <option value="">Select Company</option>
                                    @foreach(\App\Models\Company::where('status', 'active')->get() as $company)
                                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="settlementType" class="form-label">Settlement Type *</label>
                                <select class="form-select" id="settlementType" name="type" required>
                                    <option value="income">Receive Income</option>
                                    <option value="expense">Pay Expense</option>
                                    <option value="advance">Advance Payment</option>
                                    <option value="adjustment">Balance Adjustment</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="settlementAmount" class="form-label">Amount *</label>
                                <input type="number" step="0.01" class="form-control" id="settlementAmount" name="amount" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="settlementDate" class="form-label">Date *</label>
                                <input type="date" class="form-control" id="settlementDate" name="date" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="paymentMethod" class="form-label">Payment Method *</label>
                                <select class="form-select" id="paymentMethod" name="payment_method" required>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cash">Cash</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="online">Online Payment</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="referenceNumber" class="form-label">Reference Number</label>
                                <input type="text" class="form-control" id="referenceNumber" name="reference_number">
                            </div>
                            <div class="col-12 mb-3">
                                <label for="settlementNotes" class="form-label">Notes</label>
                                <textarea class="form-control" id="settlementNotes" name="notes" rows="3"></textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="markAllSettled" name="mark_all_settled">
                                    <label class="form-check-label" for="markAllSettled">
                                        Mark all pending items as settled
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Record Settlement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Quick Settle Modal -->
    <div class="modal fade" id="quickSettleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="quickSettleTitle">Quick Settlement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="quickSettleForm" method="POST">
                    @csrf
                    <input type="hidden" id="settleType" name="type">
                    <input type="hidden" id="settleId" name="id">
                    
                    <div class="modal-body">
                        <div id="quickSettleContent">
                            <!-- Content will be loaded dynamically -->
                        </div>
                        <div class="mb-3">
                            <label for="quickAmount" class="form-label">Amount *</label>
                            <input type="number" step="0.01" class="form-control" id="quickAmount" name="amount" required>
                        </div>
                        <div class="mb-3">
                            <label for="quickDate" class="form-label">Date *</label>
                            <input type="date" class="form-control" id="quickDate" name="date" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i> Settle
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function applyFilters() {
            document.getElementById('filterForm').submit();
        }

        function resetFilters() {
            window.location.href = "{{ route('income.balance') }}";
        }

        function viewCompanyDetails(companyId) {
            window.location.href = `{{ url('/manager/companies') }}/${companyId}`;
        }

        function viewTransactions(companyId) {
            window.location.href = `{{ url('/manager/transactions') }}?company=${companyId}`;
        }

        function openSettleModal(type, id) {
            document.getElementById('settleType').value = type;
            document.getElementById('settleId').value = id;
            
            const modal = new bootstrap.Modal(document.getElementById('quickSettleModal'));
            
            if (type === 'company') {
                document.getElementById('quickSettleTitle').textContent = 'Settle Company Dues';
                fetch(`{{ url('/manager/companies') }}/${id}/dues-details`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            let html = `
                                <div class="alert alert-info">
                                    <strong>${data.company.name}</strong><br>
                                    <small>Net Dues: ₹${data.netDues.toLocaleString()}</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Settle For:</label>
                                    <select class="form-select" id="settleFor">
                                        <option value="all">All Dues</option>
                                        <option value="income">Pending Income Only</option>
                                        <option value="expense">Pending Expenses Only</option>
                                    </select>
                                </div>
                            `;
                            document.getElementById('quickSettleContent').innerHTML = html;
                            document.getElementById('quickAmount').value = data.netDues;
                        }
                    });
            }
            
            modal.show();
        }

        // Handle accordion click to load details
        document.querySelectorAll('.accordion-button').forEach(button => {
            button.addEventListener('click', function() {
                const companyId = this.getAttribute('data-bs-target').replace('#collapse', '');
                const collapseDiv = document.getElementById('collapse' + companyId);
                
                // Only load if not already loaded
                if (!collapseDiv.hasAttribute('data-loaded')) {
                    loadCompanyDuesDetails(companyId, collapseDiv);
                }
            });
        });

        function loadCompanyDuesDetails(companyId, container) {
            fetch(`{{ url('/manager/companies') }}/${companyId}/dues-details`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let html = `
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Pending Income</h6>
                                    ${data.pendingIncomes.length > 0 ? 
                                        data.pendingIncomes.map(income => `
                                            <div class="card mb-2">
                                                <div class="card-body p-2">
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            <strong>${income.description}</strong><br>
                                                            <small>Date: ${new Date(income.income_date).toLocaleDateString()}</small>
                                                        </div>
                                                        <div class="text-end">
                                                            <strong>₹${income.amount.toLocaleString()}</strong><br>
                                                            <button class="btn btn-sm btn-success mt-1" onclick="settleIncome(${income.id})">
                                                                <i class="fas fa-check"></i> Settle
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        `).join('') : '<p class="text-muted">No pending income</p>'}
                                </div>
                                <div class="col-md-6">
                                    <h6>Pending Expenses</h6>
                                    ${data.pendingExpenses.length > 0 ? 
                                        data.pendingExpenses.map(expense => `
                                            <div class="card mb-2">
                                                <div class="card-body p-2">
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            <strong>${expense.description}</strong><br>
                                                            <small>Due: ${new Date(expense.due_date).toLocaleDateString()}</small>
                                                        </div>
                                                        <div class="text-end">
                                                            <strong>₹${expense.planned_amount.toLocaleString()}</strong><br>
                                                            <button class="btn btn-sm btn-danger mt-1" onclick="settleExpense(${expense.id})">
                                                                <i class="fas fa-check"></i> Pay
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        `).join('') : '<p class="text-muted">No pending expenses</p>'}
                                </div>
                            </div>
                        `;
                        container.querySelector('.accordion-body').innerHTML = html;
                        container.setAttribute('data-loaded', 'true');
                    }
                });
        }

        function settleIncome(incomeId) {
            if (confirm('Mark this income as received?')) {
                fetch(`{{ url('/manager/incomes') }}/${incomeId}/settle`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }).then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Income settled successfully!');
                        window.location.reload();
                    }
                });
            }
        }

        function settleExpense(expenseId) {
            if (confirm('Mark this expense as paid?')) {
                fetch(`/manager/expenses/${expenseId}/settle`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }).then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Expense settled successfully!');
                        window.location.reload();
                    }
                });
            }
        }

        function exportBalances() {
            const params = new URLSearchParams(new FormData(document.getElementById('filterForm')));
            window.open(`/manager/balances/export?${params.toString()}`, '_blank');
        }

        // Update balance display on type change
        document.getElementById('settlementType').addEventListener('change', function() {
            const companyId = document.getElementById('settlementCompany').value;
            if (companyId) {
                fetch(`/manager/companies/${companyId}/balance-summary`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const type = this.value;
                            let amount = 0;
                            if (type === 'income') amount = data.pendingIncome;
                            else if (type === 'expense') amount = data.pendingExpenses;
                            else if (type === 'advance') amount = 0;
                            else if (type === 'adjustment') amount = data.netDues;
                            
                            document.getElementById('settlementAmount').value = amount;
                        }
                    });
            }
        });
    </script>

    <style>
        .stat-card {
            border-left: 4px solid;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card:nth-child(1) { border-color: #28a745; }
        .stat-card:nth-child(2) { border-color: #dc3545; }
        .stat-card:nth-child(3) { border-color: #007bff; }
        .stat-card:nth-child(4) { border-color: #ffc107; }
        
        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .status.pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .status.settled {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .filter-bar {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .filter-group {
            display: inline-block;
            margin-right: 20px;
            margin-bottom: 10px;
        }
        .filter-label {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 5px;
        }
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .table-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
        }
        .accordion-button:not(.collapsed) {
            background-color: #f8f9fa;
            color: #2c3e50;
        }
    </style>
@endsection