@extends('Manager.layouts.app')
@section('content')
    <div id="expenses" class="manager-panel">
        <!-- Date Range & Filter Section -->
        <div class="filter-section card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-filter me-2 text-primary"></i>Filters
                    </h6>
                    <button class="btn btn-sm btn-outline-secondary" onclick="resetFilters()">
                        <i class="fas fa-redo me-1"></i>Reset
                    </button>
                </div>

                <div class="row g-3">
                    <!-- Date Range Filter -->
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label class="form-label small fw-bold mb-1">
                                <i class="fas fa-calendar-alt me-1 text-muted"></i>Date Range
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-calendar text-muted"></i>
                                </span>
                                <select class="form-select border-start-0" id="dateRangeFilter"
                                    onchange="updateDateRange()">
                                    <option value="today" {{ $dateRange == 'today' ? 'selected' : '' }}>Today</option>
                                    <option value="week" {{ $dateRange == 'week' ? 'selected' : '' }}>This Week</option>
                                    <option value="month" {{ $dateRange == 'month' ? 'selected' : '' }}>This Month
                                    </option>
                                    <option value="quarter" {{ $dateRange == 'quarter' ? 'selected' : '' }}>This Quarter
                                    </option>
                                    <option value="year" {{ $dateRange == 'year' ? 'selected' : '' }}>This Year</option>
                                    <option value="custom" {{ $dateRange == 'custom' ? 'selected' : '' }}>Custom Range
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Company Filter -->
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label class="form-label small fw-bold mb-1">
                                <i class="fas fa-building me-1 text-muted"></i>Company
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-building text-muted"></i>
                                </span>
                                <select class="form-select border-start-0" id="companyFilter" onchange="applyFilters()">
                                    <option value="">All Companies</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}" {{ $companyId == $company->id ? 'selected' : '' }}>
                                            {{ $company->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Type Filter -->
                    <div class="col-md-2 col-sm-6">
                        <div class="form-group">
                            <label class="form-label small fw-bold mb-1">
                                <i class="fas fa-tag me-1 text-muted"></i>Type
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-tag text-muted"></i>
                                </span>
                                <select class="form-select border-start-0" id="typeFilter" onchange="applyFilters()">
                                    <option value="all" {{ $type == 'all' ? 'selected' : '' }}>All Types</option>
                                    <option value="standard" {{ $type == 'standard' ? 'selected' : '' }}>Standard</option>
                                    <option value="non-standard" {{ $type == 'non-standard' ? 'selected' : '' }}>
                                        Non-standard</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Category Filter -->
                    <div class="col-md-2 col-sm-6">
                        <div class="form-group">
                            <label class="form-label small fw-bold mb-1">
                                <i class="fas fa-layer-group me-1 text-muted"></i>Category
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-layer-group text-muted"></i>
                                </span>
                                <select class="form-select border-start-0" id="categoryFilter" onchange="applyFilters()">
                                    <option value="all">All Categories</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div class="col-md-2 col-sm-6">
                        <div class="form-group">
                            <label class="form-label small fw-bold mb-1">
                                <i class="fas fa-circle me-1 text-muted"></i>Status
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-circle text-muted"></i>
                                </span>
                                <select class="form-select border-start-0" id="statusFilter" onchange="applyFilters()">
                                    <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All Status</option>
                                    <option value="paid" {{ $status == 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="upcoming" {{ $status == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                                    <option value="overdue" {{ $status == 'overdue' ? 'selected' : '' }}>Overdue</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Custom Date Range (Hidden by Default) -->
                <div id="customDateRange" class="row g-3 mt-3"
                    style="display: {{ $dateRange == 'custom' ? 'flex' : 'none' }};">
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small fw-bold mb-1">Start Date</label>
                        <input type="date" class="form-control form-control-sm" id="startDate"
                            value="{{ $startDate ? $startDate->format('Y-m-d') : '' }}">
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small fw-bold mb-1">End Date</label>
                        <input type="date" class="form-control form-control-sm" id="endDate"
                            value="{{ $endDate ? $endDate->format('Y-m-d') : '' }}">
                    </div>
                    <div class="col-md-2 col-sm-6 align-self-end">
                        <button class="btn btn-sm btn-primary w-100" onclick="applyCustomDate()">
                            <i class="fas fa-check me-1"></i>Apply
                        </button>
                    </div>
                </div>

                <!-- Active Filters Badges -->
                @if ($companyId || $categoryId != 'all' || $status != 'all' || $type != 'all')
                    <div class="mt-3 pt-2 border-top">
                        <small class="text-muted me-2">Active filters:</small>
                        @if ($companyId)
                            @php $companyName = $companies->where('id', $companyId)->first()->name ?? ''; @endphp
                            <span class="badge bg-light text-dark border me-1 mb-1">
                                <i class="fas fa-building me-1"></i>{{ $companyName }}
                                <button type="button" class="btn-close btn-close-sm ms-1"
                                    style="font-size: 0.6rem; padding: 0.2rem;" onclick="removeFilter('company')"></button>
                            </span>
                        @endif
                        @if ($type != 'all')
                            @php
                                $typeColor = match ($type) {
                                    'standard' => 'primary',
                                    'non-standard' => 'warning',
                                    default => 'secondary',
                                };
                                $typeIcon = match ($type) {
                                    'standard' => 'fas fa-check-circle',
                                    'non-standard' => 'fas fa-edit',
                                    default => 'fas fa-tag',
                                };
                            @endphp
                            <span class="badge bg-{{ $typeColor }} text-white me-1 mb-1">
                                <i class="{{ $typeIcon }} me-1"></i>{{ ucfirst($type) }}
                                <button type="button" class="btn-close btn-close-sm ms-1"
                                    style="font-size: 0.6rem; padding: 0.2rem; opacity: 0.7;"
                                    onclick="removeFilter('type')"></button>
                            </span>
                        @endif
                        @if ($categoryId != 'all')
                            @php $categoryName = $categories->where('id', $categoryId)->first()->name ?? ''; @endphp
                            <span class="badge bg-info text-white me-1 mb-1">
                                <i class="fas fa-layer-group me-1"></i>{{ $categoryName }}
                                <button type="button" class="btn-close btn-close-sm ms-1"
                                    style="font-size: 0.6rem; padding: 0.2rem; opacity: 0.7;"
                                    onclick="removeFilter('category')"></button>
                            </span>
                        @endif
                        @if ($status != 'all')
                            @php
                                $statusConfig = [
                                    'paid' => ['color' => 'success', 'icon' => 'fas fa-check-circle'],
                                    'pending' => ['color' => 'warning', 'icon' => 'fas fa-clock'],
                                    'overdue' => ['color' => 'danger', 'icon' => 'fas fa-exclamation-circle'],
                                    'upcoming' => ['color' => 'info', 'icon' => 'fas fa-calendar-alt'],
                                ];
                                $statusInfo = $statusConfig[$status] ?? [
                                    'color' => 'secondary',
                                    'icon' => 'fas fa-circle',
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusInfo['color'] }} text-white me-1 mb-1">
                                <i class="{{ $statusInfo['icon'] }} me-1"></i>{{ ucfirst($status) }}
                                <button type="button" class="btn-close btn-close-sm ms-1"
                                    style="font-size: 0.6rem; padding: 0.2rem; opacity: 0.7;"
                                    onclick="removeFilter('status')"></button>
                            </span>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Summary Cards Row -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="summary-card">
                    <div class="summary-header">
                        <h6 class="mb-1" id="dateRangeTitle">{{ $dateRangeTitle }} Payments</h6>
                    </div>
                    <div class="summary-body">
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <h3 class="mb-0" id="totalPayments">₹{{ number_format($totalPayments, 2) }}</h3>
                                <small class="text-muted" id="totalItems">{{ $totalItems }} Items</small>
                            </div>
                            <div class="summary-icon">
                                <i class="fas fa-money-bill-wave text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="summary-card">
                    <div class="summary-header">
                        <h6 class="mb-1" id="paidTitle">{{ $dateRangeTitle }} Paid</h6>
                    </div>
                    <div class="summary-body">
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <h3 class="mb-0" id="paidAmount">₹{{ number_format($paidAmount, 2) }}</h3>
                                <small class="text-muted" id="paidCount">{{ $paidCount }} Items</small>
                            </div>
                            <div class="summary-icon">
                                <i class="fas fa-check-circle text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="summary-card">
                    <div class="summary-header">
                        <h6 class="mb-1" id="pendingTitle">{{ $dateRangeTitle }} Pending</h6>
                    </div>
                    <div class="summary-body">
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <h3 class="mb-0" id="pendingAmount">₹{{ number_format($pendingAmount, 2) }}</h3>
                                <small class="text-muted" id="pendingCount">{{ $pendingCount }} Items</small>
                            </div>
                            <div class="summary-icon">
                                <i class="fas fa-clock text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="summary-card">
                    <div class="summary-header">
                        <h6 class="mb-1" id="overdueTitle">{{ $dateRangeTitle }} Over Due</h6>
                    </div>
                    <div class="summary-body">
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <h3 class="mb-0" id="overdueAmount">₹{{ number_format($overdueAmount, 2) }}</h3>
                                <small class="text-muted" id="overdueCount">{{ $overdueCount }} Items</small>
                            </div>
                            <div class="summary-icon">
                                <i class="fas fa-exclamation-triangle text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="summary-card">
                    <div class="summary-header">
                        <h6 class="mb-1">Total Over Due</h6>
                    </div>
                    <div class="summary-body">
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <h3 class="mb-0" id="totalOverdueAmount">
                                    ₹{{ number_format($totalOverdueAmount, 2) }}
                                </h3>
                                <small class="text-muted" id="totalOverdueCount">{{ $totalOverdueCount }} Items</small>
                            </div>
                            <div class="summary-icon">
                                <i class="fas fa-exclamation-circle text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Buttons Row -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="summary-card h-100">
                    <div class="summary-header">
                        <h6 class="mb-1">Filter Payments</h6>
                    </div>
                    <div class="summary-body">
                        <div class="btn-group w-100">
                            <button class="btn btn-outline-primary {{ $status == 'all' ? 'active' : '' }}"
                                onclick="filterPayments('all')" id="btnAll">
                                All Payments
                            </button>
                            <button class="btn btn-outline-warning {{ $status == 'pending' ? 'active' : '' }}"
                                onclick="filterPayments('pending')" id="btnPending">
                                Only Pending
                            </button>
                            <button class="btn btn-outline-info {{ $status == 'upcoming' ? 'active' : '' }}"
                                onclick="filterPayments('upcoming')" id="btnUpcoming">
                                Only Upcoming
                            </button>
                            <button class="btn btn-outline-success {{ $status == 'paid' ? 'active' : '' }}"
                                onclick="filterPayments('paid')" id="btnPaid">
                                Only Paid
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Table Section -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">All Payments</h5>
                <div class="card-tools d-flex align-items-center gap-2">
                    <select class="form-select form-select-sm border-0 bg-light" style="width: auto;" id="perPageSelector"
                        onchange="updatePerPage()">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 per page</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 per page</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 per page</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 per page</option>
                    </select>
                    <button class="btn btn-sm btn-primary" onclick="openAddNonStandardModal()">
                        <i class="fas fa-plus"></i> Add Non-standard Expense
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Bill No</th>
                                <th>Expense Name</th>
                                <th>Company</th>
                                <th>Category</th>
                                <th>Expense Type</th>
                                <th>Total Amount</th>
                                <th>Due Date</th>

                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="expensesTableBody">
                            @foreach ($allExpenses as $expense)
                                <tr data-status="{{ $expense->status }}" data-type="{{ $expense->source }}">
                                    <td>
                                        <a href="{{ route('manager.expense.view', $expense->id) }}" class="fw-bold text-primary text-decoration-none">
                                            #EXP-{{ $expense->id }}
                                        </a>
                                    </td>
                                    <td>{{ $expense->expense_name }}</td>
                                    <td>{{ $expense->company->name ?? 'All Companies' }}</td>
                                    <td>{{ $expense->categoryRelation->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge @if ($expense->categoryRelation->category_type == 'standard_fixed') bg-success @elseif($expense->categoryRelation->category_type == 'standard_editable') bg-info @else bg-warning @endif">
                                            @if ($expense->categoryRelation->category_type == 'standard_fixed')
                                                Standard Fixed
                                            @elseif($expense->categoryRelation->category_type == 'standard_editable')
                                                Standard Editable
                                            @else
                                                Non Standard
                                            @endif
                                        </span>
                                    </td>
                                    <td>₹{{ number_format($expense->planned_amount, 2) }}</td>
                                    <td>{{ $expense->due_date ? $expense->due_date->format('d M Y') : 'N/A' }}</td>

                                    {{-- <td>{{ ucfirst($expense->frequency ?? 'Once') }}</td>
                                    <td>{{ $expense->due_day }}</td> --}}

                                    <td>
                                        @php
                                            $statusClass =
                                                [
                                                    'paid' => 'success',
                                                    'pending' => 'warning',
                                                    'upcoming' => 'info',
                                                    'overdue' => 'danger',
                                                ][$expense->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $statusClass }}">
                                            {{ ucfirst($expense->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            @php
                                                $expenseType =
                                                    $expense->categoryRelation->category_type === 'standard_fixed'
                                                    ? 'standard_fixed'
                                                    : ($expense->categoryRelation->category_type ===
                                                        'standard_editable'
                                                        ? 'standard_editable'
                                                        : 'non-standard');
                                            @endphp
                                            @if ($expense->status != 'paid')
                                                <button class="btn btn-outline-primary"
                                                    onclick="editExpense({{ $expense->id }}, '{{ $expenseType }}')">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            @endif

                                            @if (count($expense->receipts ?? []) > 0)
                                                <button class="btn btn-outline-info" onclick="viewReceipts({{ $expense->id }})">
                                                    <i class="fas fa-receipt"></i>
                                                </button>
                                            @endif

                                            @if ($expense->source == 'manual')
                                                <button class="btn btn-outline-danger" onclick="deleteExpense({{ $expense->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                        <!-- In your actions column, add this button: -->
                                        @if ($expense->is_split || $expense->parent_id)
                                            <button class="btn btn-outline-info btn-sm ms-1"
                                                onclick="viewSplitHistory({{ $expense->id }})" title="View Split History">
                                                <i class="fas fa-code-branch"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <!-- Pagination -->
                    @if ($allExpenses->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                            <div class="text-muted small">
                                Showing {{ $allExpenses->firstItem() }} to {{ $allExpenses->lastItem() }} of
                                {{ $allExpenses->total() }} entries
                            </div>
                            <div class="laravel-pagination">
                                {{ $allExpenses->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Expense Modal -->
    <div class="modal fade" id="editExpenseModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="max-width: 100% !important; margin: 0 auto;">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Non-Standard Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editExpenseForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <input type="hidden" id="editExpenseId" name="id">
                    <input type="hidden" id="editIsSplit" value="0">
                    <input type="hidden" id="editParentId" name="parent_id" value="0">
                    <input type="hidden" id="editIsStandard" name="is_standard" value="0">
                    <input type="hidden" id="editTaxPercentage" name="tax_percentage" value="0">
                    <input type="hidden" id="editTdsTaxId" name="tds_tax_id" value="0">
                    <input type="hidden" id="editGstTaxId" name="gst_tax_id" value="0">
                    <input type="hidden" id="editCurrentBaseAmount" value="0">

                    <div class="modal-body">
                        <!-- Header Section -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-uppercase small text-muted">Expenses Name</label>
                                <input type="text" readonly id="editExpenseNameDisplay" name="expense_name"
                                    class="form-control">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-bold text-uppercase small text-muted">Planned Amount
                                    (₹) <span id="nonStandardPlannedBreakdown" class="text-primary ms-2"
                                        style="text-transform: none;"></span></label>
                                <input type="number" readonly id="editPlannedAmountDisplay" name="planned_amount"
                                    class="form-control">
                            </div>
                            <div class="col-md-4">
                                <!-- <label class="form-label fw-bold text-uppercase small text-muted">Original Bill Total (Base)
                                            (₹)</label> -->
                                <input type="hidden" readonly id="editOriginalAmountDisplay" name="original_amount"
                                    class="form-control bg-light">
                            </div>
                        </div>

                        <!-- Payment Section -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-uppercase small text-muted">Paid Amount (₹)</label>
                                <input type="number" class="form-control" id="editPaidAmount" name="actual_amount"
                                    step="0.01" placeholder="Enter paid amount">
                                <small class="text-muted" id="paidBreakdown"></small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-uppercase small text-muted">Paid On</label>
                                <input type="date" class="form-control" id="editPaidDate" name="paid_date" max="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-uppercase small text-muted">Payment Mode</label>
                                <select class="form-select" id="editPaymentMode" name="payment_mode"
                                    onchange="togglePaymentModeDetails(this)">
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="upi">UPI</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-uppercase small text-muted">Upload Receipts</label>
                                <input type="file" class="form-control" id="editReceiptFile" name="receipts[]"
                                    accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" required>
                            </div>
                        </div>

                        <!-- Payment Mode Details (Hidden by default) -->
                        <div class="row mb-4 payment-mode-details" style="display: none;">
                            <div class="col-md-4 bank-details" style="display: none;">
                                <label class="form-label fw-bold text-uppercase small text-muted">Select Bank</label>
                                <select class="form-select" id="editBankName" name="bank_name">
                                    <option value="">Select Bank</option>
                                    <option value="SBI">SBI</option>
                                    <option value="HDFC">HDFC</option>
                                    <option value="ICICI">ICICI</option>
                                    <option value="Axis">Axis</option>
                                </select>
                            </div>
                            <div class="col-md-4 upi-details" style="display: none;">
                                <label class="form-label fw-bold text-uppercase small text-muted">UPI Type</label>
                                <select class="form-select" id="editUpiType" name="upi_type">
                                    <option value="GPay">Google Pay</option>
                                    <option value="PhonePe">PhonePe</option>
                                    <option value="Paytm">Paytm</option>
                                </select>
                            </div>
                            <div class="col-md-4 upi-details" style="display: none;">
                                <label class="form-label fw-bold text-uppercase small text-muted">UPI Phone</label>
                                <input type="text" class="form-control" id="editUpiNumber" name="upi_number"
                                    placeholder="Enter Number" maxlength="10" pattern="[0-9]{10}" title="UPI phone number must be exactly 10 digits">
                            </div>
                        </div>
                        <!-- Balance Section -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-uppercase small text-muted">Balance Amount
                                    (₹)</label>
                                <input type="number" class="form-control bg-light" id="editBalanceAmount"
                                    name="editBalanceAmount" step="0.01" readonly value="0.00">
                                <small class="text-muted" id="pendingBreakdown"></small>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">

                                    <label class="form-label fw-bold text-uppercase small text-muted">Status</label>
                                    <select class="form-control form-select-lg" id="editStatus" name="status" required>
                                        <option value="" selected disabled>Select Status</option>
                                        <option value="due" class="text-warning" selected>Due</option>
                                        <option value="settle" class="text-info">Settle</option>
                                        <!-- <option value="convert_to_tds">Convert to TDS</option> -->
                                    </select>
                                </div>

                            </div>
                            <div class="col-md-4" id="editSettleNotesContainer" style="display:none;">
                                <div class="form-group">
                                    <label class="form-label fw-bold text-uppercase small text-muted" for="editSettleNotes">Settle Notes <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="editSettleNotes" name="settle_notes" rows="1" placeholder="Enter notes..."></textarea>
                                </div>
                            </div>
                            <div class="col-md-4" style="display: none;">
                                <label class="form-label fw-bold text-uppercase small text-muted">Due Date</label>
                                <input type="date" class="form-control" id="editNonStandardDueDate" name="due_date"
                                    min="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <!-- GST Section -->
                        <div class="tax-section mb-3" id="nonstGstSection">
                            <div class="row g-3 align-items-end">
                                <div class="col-auto">
                                    <div class="form-check" style="margin-top: 32px;">
                                        <input class="form-check-input" type="checkbox" id="editApplyGst" name="apply_gst"
                                            value="1" checked>
                                        <label class="form-check-label" for="editApplyGst">GST</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <label class="form-label">GST %</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="editGstPercentage"
                                            name="gst_percentage" value="18" min="0" max="100" step="0.01">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col">
                                    <label class="form-label">GST Amount</label>
                                    <input type="number" class="form-control" id="editGstAmount" name="gst_amount"
                                        value="0.00" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- TDS Section -->
                        <div class="tax-section mb-3" id="nonstTdsSection">
                            <div class="row g-3 align-items-end">
                                <div class="col-auto">
                                    <div class="form-check" style="margin-top: 32px;">
                                        <input class="form-check-input" type="checkbox" id="editApplyTds" name="apply_tds"
                                            value="1" checked>
                                        <label class="form-check-label" for="editApplyTds">TDS</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <label class="form-label">TDS %</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="editTdsPercentage"
                                            name="tds_percentage" value="10" min="0" max="100" step="0.01">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col">
                                    <label class="form-label">TDS Amount</label>
                                    <input type="number" class="form-control" id="editTdsAmount" name="tds_amount"
                                        value="0.00" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- TDS Status Section -->
                        <div class="row mb-3" id="nonstTdsStatusSection">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-uppercase small text-muted">TDS Status</label>
                                <select class="form-select form-select-sm" id="editTdsStatus" name="tds_status">
                                    <option value="" selected disabled>Select Status</option>
                                    <option value="received">Paid</option>
                                    <option value="not_received">Not Paid</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-uppercase small text-muted">TDS
                                    Certificate/Receipt</label>
                                <div class="input-group input-group-sm">
                                    <input type="file" class="form-control" id="editTdsFile" name="tds_file"
                                        accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                    <button type="button" class="btn btn-outline-secondary" onclick="viewTdsFile()"
                                        id="viewTdsBtn" style="display: none;">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </div>
                                <small class="text-muted" id="tdsFileInfo"></small>
                            </div>
                        </div>

                        

                        <!-- Vendor Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-uppercase small text-muted">Vendor/ Party
                                    Name</label>
                                <input type="text" class="form-control" id="editPartyName" name="party_name" placeholder="">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-uppercase small text-muted">Mobile Number</label>
                                <input type="number" class="form-control" id="editMobileNumber" name="mobile_number"
                                    placeholder="">
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="row mb-4">
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold text-uppercase small text-muted" for="editNotes">Note</label>
                                <textarea class="form-control" id="editNotes" name="notes" rows="2"
                                    placeholder=""></textarea>
                            </div>
                        </div>

                        <!-- Existing Receipts -->
                        <div class="row mb-4" id="existingReceiptsSection" style="display: none;">
                            <div class="col-12">
                                <label class="form-label fw-bold text-uppercase small text-muted">Existing
                                    Receipts:</label>
                                <div id="receiptsList" class="list-group">
                                    <!-- Existing receipts will be loaded here -->
                                </div>
                            </div>
                        </div>

                        <!-- Footer Note -->
                        <div class="alert alert-info mt-3">
                            <small><i class="fas fa-info-circle"></i> This will be applicable only on the non standard
                                expenses</small>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Receipts Modal -->
    <div class="modal fade" id="viewReceiptsModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="width: auto !important">
                <div class="modal-header">
                    <h5 class="modal-title">Receipts for Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="receiptsGallery" class="row">
                        <!-- Receipts will be displayed here -->
                    </div>
                    <div id="noReceipts" class="text-center py-4" style="display: none;">
                        <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No receipts uploaded for this expense</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Non-standard Expense Modal -->
    <div class="modal fade" id="addNonStandardModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Non-standard Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addNonStandardForm" action="{{ route('manager.expenses.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <!-- Add this hidden input for source -->
                    <input type="hidden" name="source" value="manual">

                    <div class="modal-body">
                        <!-- Company & Expense Name -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Company</label>
                                <select class="form-select" name="company_id" required>
                                    <option value="" selected disabled>Select Company</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Expense Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="expense_name" required
                                    placeholder="e.g., Server Maintenance">
                            </div>
                        </div>

                        <!-- Category & Amount -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select" name="category_id" required>
                                    <option value="" selected disabled>Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Actual Amount (₹) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="actual_amount" name="actual_amount"
                                    step="0.01" required value="0.00">
                            </div>
                        </div>
                        <div class="section-divider"></div>

                        <!-- GST Section -->
                        <div class="tax-section mb-3">
                            <div class="row g-3 align-items-end">
                                <div class="col-auto">
                                    <div class="form-check" style="margin-top: 32px;">
                                        <input class="form-check-input" type="checkbox" id="apply_gst" name="apply_gst"
                                            value="1" checked>
                                        <label class="form-check-label" for="apply_gst">GST</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <label class="form-label">GST %</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="gst_percentage" name="gst_percentage"
                                            value="18" min="0" max="100" step="0.01">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col">
                                    <label class="form-label">GST Amount</label>
                                    <input type="number" class="form-control" id="gst_amount" name="gst_amount" value="0.00"
                                        readonly>
                                </div>
                            </div>
                        </div>

                        <!-- TDS Section -->
                        <div class="tax-section mb-3">
                            <div class="row g-3 align-items-end">
                                <div class="col-auto">
                                    <div class="form-check" style="margin-top: 32px;">
                                        <input class="form-check-input" type="checkbox" id="apply_tds" name="apply_tds"
                                            value="1" checked>
                                        <label class="form-check-label" for="apply_tds">TDS</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <label class="form-label">TDS %</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="tds_percentage" name="tds_percentage"
                                            value="10" min="0" max="100" step="0.01">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col">
                                    <label class="form-label">TDS Amount</label>
                                    <input type="number" class="form-control" id="tds_amount" name="tds_amount" value="0.00"
                                        readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Amount After TDS & TDS Details -->
                        <div class="row g-3 mb-4">
                            <!-- <div class="col-md-4 tds-after-amount"> -->
                            <!-- <label class="form-label">Amount After TDS</label> -->
                            <input type="hidden" class="form-control" id="amount_after_tds" name="amount_after_tds"
                                value="0.00" readonly>
                            <!-- </div> -->
                            <div class="col-md-4 tds-status-field">
                                <label class="form-label">TDS Status</label>
                                <select class="form-select" id="addTdsStatus" name="tds_status">
                                    <option value="" selected disabled>Select Status</option>
                                    <option value="received">Paid</option>
                                    <option value="not_received">Not Paid</option>
                                </select>
                            </div>
                            <div class="col-md-4 tds-receipt-field">
                                <label class="form-label">Receipt</label>
                                <input type="file" id="addTdsReceipt" name="tds_receipt" class="form-control"
                                    accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                            </div>
                        </div>

                        <!-- Grand Total -->
                        <!-- <div class="grand-total-box mb-4"> -->
                        <!-- <label class="form-label text-center d-block mb-2">Grand Total</label> -->
                        <input type="hidden" class="form-control" id="grand_total" name="grand_total" value="0.00" readonly>
                        <!-- </div> -->

                        <div class="section-divider"></div>

                        <!-- Schedule, Paid, Balance -->
                        <div class="row g-3 mb-3">
                            <!-- <div class="col-md-3"> -->
                            <!-- <label class="form-label">Schedule Amount</label> -->
                            <input type="hidden" class="form-control" id="schedule_amount" name="planned_amount" step="0.01"
                                value="0.00">
                            <!-- </div> -->
                            <div class="col-md-3">
                                <label class="form-label">Paid Amount (₹)</label>
                                <input type="number" class="form-control" id="paid_amount" name="paid_amount" step="0.01"
                                    value="0.00">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Payment Mode</label>
                                <select class="form-select" name="payment_mode" id="payment_mode"
                                    onchange="togglePaymentModeDetails(this)">
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="upi">UPI</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Payment Date</label>
                                <input type="date" class="form-control" name="payment_date" id="payment_date" max="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-3">

                                <label class="form-label" id="receipts_label">Receipt</label>
                                <div id="receiptsContainer">
                                    <div class="receipt-item mb-2">
                                        <div class="input-group">
                                            <input type="file" name="receipts[]" class="form-control" id="main_receipt"
                                                accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">

                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Payment Mode Details Row (Hidden by default) -->
                        <div class="row g-3 mb-3 payment-mode-details" style="display: none;">
                            <div class="col-md-6 bank-details" style="display: none;">
                                <label class="form-label">Select Bank</label>
                                <select class="form-select" name="bank_name">
                                    <option value="">Select Bank</option>
                                    <option value="SBI">State Bank of India</option>
                                    <option value="HDFC">HDFC Bank</option>
                                    <option value="ICICI">ICICI Bank</option>
                                    <option value="Axis">Axis Bank</option>
                                </select>
                            </div>
                            <div class="col-md-6 upi-details" style="display: none;">
                                <label class="form-label">UPI Type</label>
                                <select class="form-select" name="upi_type">
                                    <option value="GPay">Google Pay</option>
                                    <option value="PhonePe">PhonePe</option>
                                    <option value="Paytm">Paytm</option>
                                </select>
                            </div>
                            <div class="col-md-6 upi-details" style="display: none;">
                                <label class="form-label">UPI Phone Number</label>
                                <input type="text" class="form-control" id="addUpiNumber" name="upi_number" placeholder="Enter phone number" maxlength="10" pattern="[0-9]{10}" title="UPI phone number must be exactly 10 digits">
                            </div>
                        </div>
                        <!-- Add these hidden inputs for split payment in your form -->
                        <input type="hidden" name="split_payment" id="split_payment" value="0">
                        <input type="hidden" name="create_new_for_balance" id="create_new_for_balance" value="0">

                        <!-- Add this section after the Status & Payment Date section -->
                        <div class="row g-3 mb-3" id="splitPaymentSection" style="display: none;">
                            <div class="col-md-6">
                                <label class="form-label">New Due Date for Balance</label>
                                <input type="date" class="form-control" name="new_due_date"
                                    value="{{ date('Y-m-d', strtotime('+30 days')) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Balance Expense Notes</label>
                                <textarea class="form-control" name="balance_notes" rows="2"
                                    placeholder="Notes for balance expense..."></textarea>
                            </div>
                        </div>
                        <!-- Status & Payment Date -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Balance</label>
                                <input type="number" class="form-control bg-light" id="balance_amount" name="balance_amount"
                                    step="0.01" readonly value="0.00">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" name="status" required id="payment_status">
                                    <option value="" selected disabled>Select Status</option>
                                    <option value="due" class="text-warning">Due</option>
                                    <option value="settle" class="text-info">Settle</option>
                                </select>
                            </div>
                            <div class="col-md-3" id="addSettleNotesContainer" style="display:none;">
                                <label class="form-label" for="addSettleNotes">Settle Notes <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="addSettleNotes" name="settle_notes" rows="1" placeholder="Enter notes..."></textarea>
                            </div>
                            <div class="col-md-4" id="due_date_container" style="display: none;">
                                <label class="form-label">Due Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="due_date" id="add_due_date"
                                    min="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="section-divider"></div>

                        <!-- Party/Vendor & Mobile -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Party/Vendor Name</label>
                                <input type="text" class="form-control" name="party_name" placeholder="Optional">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mobile Number</label>
                                <input type="number" class="form-control" name="mobile_number" placeholder="Optional">
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label class="form-label" for="addNotes">Notes</label>
                            <textarea class="form-control" id="addNotes" name="notes" rows="3" placeholder="Optional notes..."></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Expense
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Standard Fixed Expense Modal -->
    <div class="modal fade" id="editStandardFixedModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="max-width: 100% !important; margin: 0 auto;">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Standard Fixed Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editStandardFixedForm">
                    @csrf
                    @method('PUT')

                    <input type="hidden" id="editFixedExpenseId" name="id">
                    <input type="hidden" id="editFixedIsSplit" value="0">
                    <input type="hidden" id="editFixedParentId" name="parent_id" value="0">
                    <input type="hidden" name="source" value="standard">
                    <input type="hidden" id="editFixedTdsTaxId" name="tds_tax_id" value="0">
                    <input type="hidden" id="editFixedGstTaxId" name="gst_tax_id" value="0">
                    <input type="hidden" id="editFixedBaseAmount" value="0">
                    <input type="hidden" id="editFixedApplyGstHidden" name="apply_gst" value="0">
                    <input type="hidden" id="editFixedApplyTdsHidden" name="apply_tds" value="0">

                    <div class="modal-body">
                        <!-- Expense Name and Amounts Row -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label fw-bold text-uppercase small text-muted">Expense Name</label>
                                    <p class="form-control-plaintext fw-semibold fs-6 mb-0"
                                        id="editFixedExpenseNameDisplay"></p>
                                    <input type="hidden" id="editFixedExpenseName" name="expense_name">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="form-label fw-bold text-uppercase small text-muted">Planned Amount
                                        (₹)<i class="fas fa-lock me-1"></i> <span id="fixedPlannedBreakdown"
                                            class="text-primary ms-2" style="text-transform: none;"></span></label>

                                    <div class="d-flex align-items-start">
                                        <div class="flex-grow-1">
                                            <input type="number" class="form-control" id="editFixedPlannedAmountDisplay"
                                                step="0.01" min="0" required readonly>
                                        </div>
                                        <div id="fixedTaxSummary" class="ms-3">
                                            <!-- GST and Total will be displayed here -->
                                        </div>
                                    </div>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Divider -->
                        <hr class="my-4">

                        <!-- Payment Details Row -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label fw-bold text-uppercase small text-muted">Paid Amount(₹)</label>
                                    <input type="number" class="form-control" id="editFixedActualAmount"
                                        name="actual_amount" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label fw-bold text-uppercase small text-muted">Paid On</label>
                                    <input type="date" class="form-control" id="editFixedPaidDate" name="paid_date" max="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label fw-bold text-uppercase small text-muted">Payment Mode</label>
                                    <select class="form-select" id="editFixedPaymentMode" name="payment_mode"
                                        onchange="togglePaymentModeDetails(this)">
                                        <option value="cash">Cash</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="cheque">Cheque</option>
                                        <option value="upi">UPI</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-uppercase small text-muted">Balance (₹)</label>
                                <input type="number" class="form-control bg-light" id="fixedBalanceAmount"
                                    name="balance_amount" step="0.01" readonly>
                            </div>
                        </div>

                        <!-- Payment Mode Details (Hidden) -->
                        <div class="row g-3 mb-4 payment-mode-details" style="display: none;">
                            <div class="col-md-4 bank-details" style="display: none;">
                                <label class="form-label fw-bold text-uppercase small text-muted">Bank</label>
                                <select class="form-select" id="editFixedBankName" name="bank_name">
                                    <option value="">Select Bank</option>
                                    <option value="SBI">SBI</option>
                                    <option value="HDFC">HDFC</option>
                                    <option value="ICICI">ICICI</option>
                                    <option value="Axis">Axis</option>
                                </select>
                            </div>
                            <div class="col-md-4 upi-details" style="display: none;">
                                <label class="form-label fw-bold text-uppercase small text-muted">UPI Type</label>
                                <select class="form-select" id="editFixedUpiType" name="upi_type">
                                    <option value="GPay">GPay</option>
                                    <option value="PhonePe">PhonePe</option>
                                    <option value="Paytm">Paytm</option>
                                </select>
                            </div>
                            <div class="col-md-4 upi-details" style="display: none;">
                                <label class="form-label fw-bold text-uppercase small text-muted">UPI Phone</label>
                                <input type="text" class="form-control" id="editFixedUpiNumber" name="upi_number"
                                    placeholder="Number" maxlength="10" pattern="[0-9]{10}" title="UPI phone number must be exactly 10 digits">
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-uppercase small text-muted">Upload Receipts <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="editFixedReceiptFile" name="receipts[]" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-uppercase small text-muted">Status</label>
                                <select class="form-select" id="editFixedStatus" name="status" required>
                                    <option value="" selected disabled>Select Status</option>
                                    <option value="due" class="text-warning">Due</option>
                                    <option value="settle" class="text-info">Settle</option>
                                    <!-- <option value="convert_to_tds">Convert to TDS</option> -->
                                </select>
                            </div>
                            <div class="col-md-4" id="editFixedSettleNotesContainer" style="display:none;">
                                <label class="form-label fw-bold text-uppercase small text-muted" for="editFixedSettleNotes">Settle Notes <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="editFixedSettleNotes" name="settle_notes" rows="1" placeholder="Enter notes..."></textarea>
                            </div>
                            <div class="col-md-4" style="display: none;">
                                <label class="form-label fw-bold text-uppercase small text-muted">Due Date</label>
                                <input type="date" class="form-control" id="editFixedDueDate" name="due_date"
                                    min="{{ date('Y-m-d') }}">
                            </div>
                        </div>

                        <!-- Divider -->
                        <hr class="my-4">
                        <!-- Vendor and Date Row -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label fw-bold text-uppercase small text-muted">Vendor/ Party
                                        Name</label>
                                    <input type="text" class="form-control" id="editFixedPartyName" name="party_name"
                                        placeholder="Enter vendor name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-uppercase small text-muted">Mobile Number</label>
                                <input type="number" class="form-control" id="editFixedMobileNumber" name="mobile_number"
                                    placeholder="">
                            </div>

                        </div>
                        <!-- GST Section -->
                        <div class="tax-section mb-3" id="fixedGstSection">
                            <div class="row g-3 align-items-end">
                                <div class="col-auto">
                                    <div class="form-check" style="margin-top: 32px;">
                                        <input class="form-check-input" type="checkbox" id="fixedApplyGst" name="apply_gst"
                                            value="1" checked>
                                        <label class="form-check-label" for="fixedApplyGst">GST <i
                                                class="fas fa-lock ms-1 small text-muted"></i></label>
                                    </div>
                                </div>
                                <div class="col">
                                    <label class="form-label">GST %</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="fixedGstPercentage"
                                            name="gst_percentage" value="18" min="0" max="100" step="0.01" readonly>
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col">
                                    <label class="form-label">GST Amount</label>
                                    <input type="number" class="form-control" id="fixedGstAmount" name="gst_amount"
                                        value="0.00" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- TDS Section -->
                        <div class="tax-section mb-3" id="fixedTdsSection">
                            <div class="row g-3 align-items-end">
                                <div class="col-auto">
                                    <div class="form-check" style="margin-top: 32px;">
                                        <input class="form-check-input" type="checkbox" id="fixedApplyTds" name="apply_tds"
                                            value="1" checked>
                                        <label class="form-check-label" for="fixedApplyTds">TDS <i
                                                class="fas fa-lock ms-1 small text-muted"></i></label>
                                    </div>
                                </div>
                                <div class="col">
                                    <label class="form-label">TDS %</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="fixedTdsPercentage"
                                            name="tds_percentage" value="10" min="0" max="100" step="0.01" readonly>
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col">
                                    <label class="form-label">TDS Amount</label>
                                    <input type="number" class="form-control" id="fixedTdsAmount" name="tds_amount"
                                        value="0.00" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- TDS Section -->
                        <div class="row mb-3" id="fixedTdsExtraSection">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-uppercase small text-muted">TDS Status</label>
                                <select class="form-select form-select-sm" id="editFixedTdsStatus" name="tds_status">
                                    <option value="received">Paid</option>
                                    <option value="not_received" selected>Not Paid</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-uppercase small text-muted">TDS
                                    Certificate/Receipt</label>
                                <div class="input-group input-group-sm">
                                    <input type="file" class="form-control" id="editFixedTdsFile" name="tds_file"
                                        accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                    <button type="button" class="btn btn-outline-secondary" onclick="viewTdsFile()"
                                        id="viewTdsBtn" style="display: none;">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </div>
                                <small class="text-muted" id="tdsFileInfo"></small>
                            </div>
                        </div>
                        <!-- Divider -->
                        <hr class="my-4">
                        <!-- Notes Section -->
                        <div class="form-group mb-4">
                            <label class="form-label fw-bold text-uppercase small text-muted" for="editFixedNotes">Notes</label>
                            <textarea class="form-control" id="editFixedNotes" name="notes" rows="3"
                                placeholder="Add any additional notes..."></textarea>
                        </div>

                        <!-- Information Alert -->
                        <div class="alert alert-info border-start border-info border-3">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-info-circle fs-5 me-2 mt-1 text-info"></i>
                                <div class="small">
                                    This is a standard fixed expense. Planned amount cannot be modified.
                                    Only payment details can be updated.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check me-1"></i> Update Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Standard Editable Expense Modal -->
    <div class="modal fade" id="editStandardEditableModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Standard Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editStandardEditableForm">
                    @csrf
                    <input type="hidden" id="editEditableExpenseId" name="id">
                    <input type="hidden" id="editEditableIsSplit" value="0">
                    <input type="hidden" id="editEditableParentId" name="parent_id" value="0">
                    <input type="hidden" id="editEditableTdsTaxId" name="tds_tax_id" value="0">
                    <input type="hidden" id="editEditableGstTaxId" name="gst_tax_id" value="0">
                    <input type="hidden" id="editEditableBaseAmount" value="0">

                    <input type="hidden" name="source" value="standard">
                    <div class="modal-body">

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-uppercase small text-muted ">Expense Name</label>
                                <p class="form-control-plaintext" id="editEditableExpenseNameDisplay"></p>
                                <input type="hidden" id="editEditableExpenseName" name="expense_name">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-bold text-uppercase small text-muted">Planned Amount (₹)
                                    * <span id="editablePlannedBreakdown" class="text-primary ms-2"
                                        style="text-transform: none;"></span></label>
                                <div class="d-flex align-items-start">
                                    <div class="flex-grow-1"></div>
                                    <input type="number" class="form-control" id="editEditablePlannedAmount"
                                        name="planned_amount" step="0.01" required>
                                </div>
                                <div id="editTaxSummary" class="ms-3">
                                    <!-- GST and Total will be displayed here -->
                                </div>
                            </div>


                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold text-uppercase small text-muted">Paid Amount(₹)</label>
                                    <input type="number" class="form-control" id="editEditableActualAmount"
                                        name="actual_amount" step="0.01">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold text-uppercase small text-muted">Paid On</label>
                                    <input type="date" class="form-control" id="editEditablePaidDate" name="paid_date" max="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold text-uppercase small text-muted">Payment Mode</label>
                                    <select class="form-select" id="editEditablePaymentMode" name="payment_mode"
                                        onchange="togglePaymentModeDetails(this)">
                                        <option value="cash">Cash</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="cheque">Cheque</option>
                                        <option value="upi">UPI</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-bold text-uppercase small text-muted">Upload
                                        Receipts <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="editEditableReceiptFile" name="receipts[]" required>
                                </div>
                            </div>

                            <!-- Payment Mode Details (Hidden) -->
                            <div class="row g-3 mb-4 payment-mode-details" style="display: none;">
                                <div class="col-md-4 bank-details" style="display: none;">
                                    <label class="form-label fw-bold text-uppercase small text-muted">Bank</label>
                                    <select class="form-select" id="editEditableBankName" name="bank_name">
                                        <option value="">Select Bank</option>
                                        <option value="SBI">SBI</option>
                                        <option value="HDFC">HDFC</option>
                                        <option value="ICICI">ICICI</option>
                                        <option value="Axis">Axis</option>
                                    </select>
                                </div>
                                <div class="col-md-4 upi-details" style="display: none;">
                                    <label class="form-label fw-bold text-uppercase small text-muted">UPI Type</label>
                                    <select class="form-select" id="editEditableUpiType" name="upi_type">
                                        <option value="GPay">GPay</option>
                                        <option value="PhonePe">PhonePe</option>
                                        <option value="Paytm">Paytm</option>
                                    </select>
                                </div>
                                <div class="col-md-4 upi-details" style="display: none;">
                                    <label class="form-label fw-bold text-uppercase small text-muted">UPI Phone</label>
                                    <input type="text" class="form-control" id="editEditableUpiNumber" name="upi_number"
                                        placeholder="Number" maxlength="10" pattern="[0-9]{10}" title="UPI phone number must be exactly 10 digits">
                                </div>
                            </div>

                            <div class="row g-3 mb-4">

                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-uppercase small text-muted">Balance (₹)</label>
                                    <input type="number" class="form-control bg-light" id="edittableBalanceAmount"
                                        name="balance_amount" step="0.01" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-uppercase small text-muted">Status</label>
                                    <select class="form-select" id="editEditableStatus" name="status" required>
                                        <option value="" selected disabled>Select Status</option>
                                        <option value="due" class="text-warning">Due</option>
                                        <option value="settle" class="text-info">Settle</option>
                                        <!-- <option value="convert_to_tds">Convert to TDS</option> -->
                                    </select>
                                </div>
                                <div class="col-md-4" id="editEditableSettleNotesContainer" style="display:none;">
                                    <label class="form-label fw-bold text-uppercase small text-muted" for="editEditableSettleNotes">Settle Notes <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="editEditableSettleNotes" name="settle_notes" rows="1" placeholder="Enter notes..."></textarea>
                                </div>
                                <div class="col-md-4" style="display: none;">
                                    <label class="form-label fw-bold text-uppercase small text-muted">Due Date</label>
                                    <input type="date" class="form-control" id="editEditableDueDate" name="due_date"
                                        min="{{ date('Y-m-d') }}">
                                </div>

                            </div>
                        </div>

                        <!-- GST Section -->
                        <div class="tax-section mb-3" id="editGstSection">
                            <div class="row g-3 align-items-end">
                                <div class="col-auto">
                                    <div class="form-check" style="margin-top: 32px;">
                                        <input class="form-check-input" type="checkbox" id="editableApplyGst"
                                            name="apply_gst" value="1" checked>
                                        <label class="form-check-label" for="editableApplyGst">GST</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <label class="form-label">GST %</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="editableGstPercentage"
                                            name="gst_percentage" value="18" min="0" max="100" step="0.01">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col">
                                    <label class="form-label">GST Amount</label>
                                    <input type="number" class="form-control" id="editableGstAmount" name="gst_amount"
                                        value="0.00" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- TDS Section -->
                        <div class="tax-section mb-3" id="editTdsSection">
                            <div class="row g-3 align-items-end">
                                <div class="col-auto">
                                    <div class="form-check" style="margin-top: 32px;">
                                        <input class="form-check-input" type="checkbox" id="editableApplyTds"
                                            name="apply_tds" value="1" checked>
                                        <label class="form-check-label" for="editableApplyTds">TDS</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <label class="form-label">TDS %</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="editableTdsPercentage"
                                            name="tds_percentage" value="10" min="0" max="100" step="0.01">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col">
                                    <label class="form-label">TDS Amount</label>
                                    <input type="number" class="form-control" id="editableTdsAmount" name="tds_amount"
                                        value="0.00" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- TDS Status Section -->
                        <div class="row mb-3" id="editableTdsStatusSection">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-uppercase small text-muted">TDS Status</label>
                                <select class="form-select form-select-sm" id="editEditableTdsStatus" name="tds_status">
                                    <option value="received">Paid</option>
                                    <option value="not_received" selected>Not Paid</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-uppercase small text-muted">TDS
                                    Certificate/Receipt</label>
                                <div class="input-group input-group-sm">
                                    <input type="file" class="form-control" id="editEditableTdsFile" name="tds_file"
                                        accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                    <button type="button" class="btn btn-outline-secondary" onclick="viewTdsFile()"
                                        id="viewTdsBtn" style="display: none;">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </div>
                                <small class="text-muted" id="tdsFileInfo"></small>
                            </div>
                        </div>

                        <div class="row mb-4">

                            <div class="col-md-4">
                                <label class="form-label fw-bold text-uppercase small text-muted">Party/Vendor</label>
                                <input type="text" class="form-control" id="editEditablePartyName" name="party_name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-uppercase small text-muted">Mobile Number</label>
                                <input type="number" class="form-control" id="editEditableMobileNumber" name="mobile_number"
                                    placeholder="">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold text-uppercase small text-muted" for="editEditableNotes">Notes</label>
                                <textarea class="form-control" id="editEditableNotes" name="notes" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Add this modal at the bottom of your view -->
    <div class="modal fade" id="splitHistoryModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Split Payment History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="splitHistoryContent">
                        <!-- Split history will be loaded here -->
                    </div>
                    <div id="noSplitHistory" class="text-center py-4" style="display: none;">
                        <i class="fas fa-code-branch fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No split payment history found</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize all modals
            const addModal = new bootstrap.Modal(document.getElementById('addNonStandardModal'));
            const editModal = new bootstrap.Modal(document.getElementById('editExpenseModal'));
            const viewReceiptsModal = new bootstrap.Modal(document.getElementById('viewReceiptsModal'));

            // Store modal instances globally
            window.expenseModals = {
                addNonStandard: addModal,
                editExpenseModal: editModal,
                viewReceipts: viewReceiptsModal
            };

            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Set today's date in all paid date fields if they are empty
            const today = new Date().toISOString().split('T')[0];
            ['editPaidDate', 'editFixedPaidDate', 'editEditablePaidDate'].forEach(id => {
                const el = document.getElementById(id);
                if (el && !el.value) {
                    el.value = today;
                }
            });

            // Restrict UPI Phone Number inputs to 10 digits only
            const upiInputs = document.querySelectorAll('input[name="upi_number"]');
            upiInputs.forEach(input => {
                input.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
            });
        });
        // State variables
        window.isNonStandardLoading = false;
        window.isStandardEditableLoading = false;
        window.isStandardFixedLoading = false;

        // Defined at top-level for hoisting and visibility
        async function editExpense(expenseId, expenseType = 'non-standard') {
            window.isNonStandardLoading = true;
            try {
                const response = await fetch(
                    `https://xhtmlreviews.in/beta-finance/manager/expenses/${expenseId}/edit`);
                const data = await response.json();

                if (!data.success) {
                    alert(data.message || 'Error loading expense data');
                    return;
                }

                // Reset forms before loading new data
                const forms = ['editStandardFixedForm', 'editStandardEditableForm', 'editExpenseForm'];
                forms.forEach(formId => {
                    const form = document.getElementById(formId);
                    if (form) form.reset();
                });

                // Determine which modal to show based on expense type
                if (expenseType === 'standard_fixed') {
                    window.isStandardFixedLoading = true;
                    loadStandardFixedData(data);
                    const modal = new bootstrap.Modal(document.getElementById('editStandardFixedModal'));
                    modal.show();
                } else if (expenseType === 'standard_editable') {
                    window.isStandardEditableLoading = true;
                    loadStandardEditableData(data);
                    const modal = new bootstrap.Modal(document.getElementById('editStandardEditableModal'));
                    modal.show();
                } else {
                    loadNonStandardData(data);
                    const modal = new bootstrap.Modal(document.getElementById('editExpenseModal'));
                    modal.show();
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error loading expense data');
            }
        }
        // Ensure it's on window for onclick handlers
        window.editExpense = editExpense;

        let currentStatusFilter = '{{ $status }}';
        let currentDateRange = '{{ $dateRange }}';

        // Update date range and reload
        function updateDateRange() {
            const dateRange = document.getElementById('dateRangeFilter').value;
            const customRangeDiv = document.getElementById('customDateRange');

            if (dateRange === 'custom') {
                customRangeDiv.style.display = 'flex';
            } else {
                customRangeDiv.style.display = 'none';
                applyFilters();
            }
        }

        // Apply custom date range
        function applyCustomDate() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            if (!startDate || !endDate) {
                alert('Please select both start and end dates');
                return;
            }

            let url = window.location.pathname + '?';
            url += `date_range=custom&start_date=${startDate}&end_date=${endDate}&`;

            // Preserve other filters
            const companyId = document.getElementById('companyFilter').value;
            const type = document.getElementById('typeFilter').value;
            const categoryId = document.getElementById('categoryFilter').value;
            const status = document.getElementById('statusFilter').value;

            if (companyId) url += `company=${companyId}&`;
            if (type !== 'all') url += `type=${type}&`;
            if (categoryId !== 'all') url += `category=${categoryId}&`;
            if (status !== 'all') url += `status=${status}&`;

            // Remove trailing &
            url = url.replace(/&$/, '');

            window.location.href = url;
        }

        // Update per page items
        function updatePerPage() {
            const perPage = document.getElementById('perPageSelector').value;
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('per_page', perPage);
            urlParams.set('page', 1); // Reset to page 1 when changing items per page
            window.location.search = urlParams.toString();
        }
        // Apply all filters
        function applyFilters() {
            const companyId = document.getElementById('companyFilter').value;
            const type = document.getElementById('typeFilter').value;
            const categoryId = document.getElementById('categoryFilter').value;
            const status = document.getElementById('statusFilter').value;
            const dateRange = document.getElementById('dateRangeFilter').value;

            let url = window.location.pathname + '?';

            if (companyId) url += `company=${companyId}&`;
            if (type !== 'all') url += `type=${type}&`;
            if (categoryId !== 'all') url += `category=${categoryId}&`;
            if (status !== 'all') url += `status=${status}&`;
            if (dateRange !== 'month') url += `date_range=${dateRange}&`;

            // Add custom dates if selected
            if (dateRange === 'custom') {
                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;
                if (startDate && endDate) {
                    url += `start_date=${startDate}&end_date=${endDate}&`;
                }
            }

            // Preserve per_page if exists
            const perPage = document.getElementById('perPageSelector')?.value;
            if (perPage && perPage !== '10') url += `per_page=${perPage}&`;

            // Remove trailing &
            url = url.replace(/[&?]$/, '');

            window.location.href = url;
        }

        // Filter payments by status
        function filterPayments(status) {
            document.querySelectorAll('.btn-group .btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Add active class to clicked button
            const activeBtn = document.getElementById(`btn${status.charAt(0).toUpperCase() + status.slice(1)}`);
            if (activeBtn) {
                activeBtn.classList.add('active');
            }

            // Update the status filter dropdown
            const statusFilter = document.getElementById('statusFilter');
            if (statusFilter) {
                statusFilter.value = status;
            }
            applyFilters();
        }

        // Reset all filters
        function resetFilters() {
            const url = new URL(window.location.href);
            url.searchParams.delete('company');
            url.searchParams.delete('type');
            url.searchParams.delete('category');
            url.searchParams.delete('status');
            url.searchParams.set('date_range', 'month');

            window.location.href = url.toString();
        }
        // Remove specific filter
        function removeFilter(filterType) {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.delete(filterType);

            let newUrl = window.location.pathname;
            if (urlParams.toString()) {
                newUrl += '?' + urlParams.toString();
            }

            window.location.href = newUrl;
        }

        // ==============================================
        // TDS TOGGLE FUNCTIONS FOR EACH MODAL
        // ==============================================

        // 1. Toggle TDS fields for Add Non-Standard Modal
        function toggleTdsFieldsAdd() {
            const applyTds = document.getElementById('apply_tds');
            const tdsStatusField = document.querySelector('#addNonStandardModal .tds-status-field');
            const tdsReceiptField = document.querySelector('#addNonStandardModal .tds-receipt-field');
            const tdsAfterAmountField = document.querySelector('#addNonStandardModal .tds-after-amount');


            if (applyTds && applyTds.checked) {
                // Show TDS related fields by overriding the !important rule
                if (tdsStatusField) tdsStatusField.style.cssText = 'display: block !important';
                if (tdsReceiptField) tdsReceiptField.style.cssText = 'display: block !important';
                if (tdsAfterAmountField) tdsAfterAmountField.style.cssText = 'display: block !important';
            } else {
                // Keep visible but reset if needed, or just let them stay
                if (tdsStatusField) tdsStatusField.style.cssText = 'display: block !important';
                if (tdsReceiptField) tdsReceiptField.style.cssText = 'display: block !important';
                if (tdsAfterAmountField) tdsAfterAmountField.style.cssText = 'display: block !important';
            }
        }

        function toggleTdsFieldsEdit() {
            const tdsSection = document.getElementById('nonstTdsSection');
            const tdsStatusSection = document.getElementById('nonstTdsStatusSection');

            const checkbox = document.getElementById('editApplyTds');
            // Do not hide the entire tdsSection so the checkbox remains visible!
            if (tdsStatusSection) tdsStatusSection.style.display = (checkbox && checkbox.checked) ? 'flex' : 'none';
        }

        function toggleTdsFieldsFixed() {
            const tdsSection = document.getElementById('fixedTdsSection');
            const tdsStatusSection = document.getElementById('fixedTdsExtraSection');

            const applyTds = document.getElementById('fixedApplyTds');
            // Do not hide the entire tdsSection so the checkbox remains visible!
            if (tdsStatusSection) tdsStatusSection.style.display = (applyTds && applyTds.checked) ? 'flex' : 'none';
        }

        function toggleTdsFieldsEditable() {
            const tdsSection = document.getElementById('editTdsSection') || document.getElementById('editableTdsSection');
            const tdsStatusSection = document.getElementById('editableTdsStatusSection');

            const applyTds = document.getElementById('editableApplyTds');
            // Do not hide the entire tdsSection so the checkbox remains visible!
            if (tdsStatusSection) tdsStatusSection.style.display = (applyTds && applyTds.checked) ? 'flex' : 'none';
        }

        // ==============================================
        // TAX CALCULATION FUNCTIONS FOR EACH MODAL
        // ==============================================

        // 1. For Add Non-Standard Expense Modal
        function calculateTaxNonStandardAdd() {
            // Get base amount
            const baseAmount = parseFloat(document.getElementById('actual_amount')?.value) || 0;

            // Initialize tax amounts
            let gstAmount = 0;
            let tdsAmount = 0;
            let grandTotal = baseAmount;
            let amountAfterTDS = baseAmount;

            // Calculate GST if applied
            const applyGST = document.getElementById('apply_gst');
            if (applyGST && applyGST.checked) {
                const gstPercentage = parseFloat(document.getElementById('gst_percentage')?.value) || 0;
                gstAmount = (baseAmount * gstPercentage) / 100;
                grandTotal += gstAmount;

                const gstAmountField = document.getElementById('gst_amount');
                if (gstAmountField) gstAmountField.value = gstAmount.toFixed(2);
            } else {
                const gstAmountField = document.getElementById('gst_amount');
                if (gstAmountField) gstAmountField.value = '0.00';
            }

            // Calculate TDS if applied (on base amount only)
            const applyTDS = document.getElementById('apply_tds');
            if (applyTDS && applyTDS.checked) {
                const tdsPercentage = parseFloat(document.getElementById('tds_percentage')?.value) || 0;
                const amountForTds = baseAmount; // Changed from baseAmount + gstAmount
                tdsAmount = (amountForTds * tdsPercentage) / 100;
                // grandTotal -= tdsAmount; // TDS is deducted
                amountAfterTDS = baseAmount;

                const tdsAmountField = document.getElementById('tds_amount');
                const amountAfterTDSField = document.getElementById('amount_after_tds');
                if (tdsAmountField) tdsAmountField.value = tdsAmount.toFixed(2);
                if (amountAfterTDSField) amountAfterTDSField.value = (baseAmount - tdsAmount).toFixed(2);
            } else {
                const tdsAmountField = document.getElementById('tds_amount');
                const amountAfterTDSField = document.getElementById('amount_after_tds');
                if (tdsAmountField) tdsAmountField.value = '0.00';
                if (amountAfterTDSField) amountAfterTDSField.value = baseAmount.toFixed(2);
            }

            // Update grand total and schedule amount
            const grandTotalField = document.getElementById('grand_total');
            const netPayable = baseAmount + gstAmount - tdsAmount;

            if (grandTotalField) grandTotalField.value = (baseAmount + gstAmount).toFixed(2);

            const scheduleAmountInput = document.getElementById('schedule_amount');
            const paidAmountInput = document.getElementById('paid_amount');

            const oldScheduleAmount = parseFloat(scheduleAmountInput?.value) || 0;
            const currentPaidAmount = parseFloat(paidAmountInput?.value) || 0;

            // schedule_amount will be used for balance calculation (Net Payable)
            if (scheduleAmountInput) scheduleAmountInput.value = netPayable.toFixed(2);

            // Auto-populate Paid Amount only if it hasn't been manually changed by the user
            if (paidAmountInput && (Math.abs(currentPaidAmount - oldScheduleAmount) < 0.01 || paidAmountInput.value === "" || paidAmountInput.value === "0.00")) {
                paidAmountInput.value = netPayable.toFixed(2);
            }

            // Calculate balance
            calculateBalance();
            handleStatusBehavior('non-standard-add');
        }

        // 2. For Edit Non-Standard Expense Modal
        function calculateTaxNonStandardEdit(event) {
            // Hoist all elements to the top for safety
            const baseAmountInput = document.getElementById('editCurrentBaseAmount');
            const gstAmountField = document.getElementById('editGstAmount');
            const tdsAmountField = document.getElementById('editTdsAmount');
            const statusField = document.getElementById('editStatus');
            const plannedAmountDisplay = document.getElementById('editPlannedAmountDisplay');
            const paidAmountInput = document.getElementById('editPaidAmount');
            const balanceDisplay = document.getElementById('editBalanceAmount');
            const dueDateField = document.getElementById('editNonStandardDueDate');

            if (!baseAmountInput || !plannedAmountDisplay || !paidAmountInput) return;

            const baseAmount = parseFloat(baseAmountInput.value) || 0;

            // Get references to tax elements
            const applyGst = document.getElementById('editApplyGst');
            const gstPercentage = document.getElementById('editGstPercentage');

            const applyTds = document.getElementById('editApplyTds');
            const tdsPercentage = document.getElementById('editTdsPercentage');

            // Calculate GST and TDS ONLY if not loading or if explicitly triggered by change
            let gstAmount = parseFloat(gstAmountField?.value) || 0;
            let tdsAmount = parseFloat(tdsAmountField?.value) || 0;

            if (!window.isNonStandardLoading) {
                // Calculate GST if applied
                const isGstTrigger = event && event.target && ['editGstPercentage', 'editApplyGst', 'editCurrentBaseAmount'].includes(event.target.id);
                if (applyGst && applyGst.checked && gstPercentage) {
                    if (isGstTrigger) {
                        const gstPercent = parseFloat(gstPercentage.value) || 0;
                        gstAmount = (baseAmount * gstPercent) / 100;
                        if (gstAmountField) gstAmountField.value = gstAmount.toFixed(2);
                    }
                } else if (gstAmountField) {
                    gstAmount = 0;
                    gstAmountField.value = '0.00';
                }

                // Calculate TDS if applied (on base amount only)
                const isTdsTrigger = event && event.target && ['editTdsPercentage', 'editApplyTds', 'editCurrentBaseAmount'].includes(event.target.id);
                if (applyTds && applyTds.checked && tdsPercentage) {
                    if (isTdsTrigger) {
                        const tdsPercent = parseFloat(tdsPercentage.value) || 0;
                        tdsAmount = (baseAmount * tdsPercent) / 100;
                        if (tdsAmountField) tdsAmountField.value = tdsAmount.toFixed(2);
                    }
                } else if (tdsAmountField) {
                    tdsAmount = 0;
                    tdsAmountField.value = '0.00';
                }
            }

            updateBreakdownText('nonStandardPlannedBreakdown', baseAmount, gstAmount, tdsAmount);
            // Recalculate Planned Amount = Base + GST
            const isSplitNonStandard = document.getElementById('editIsSplit')?.value == '1' || document.getElementById('editParentId')?.value != '0';
            const plannedAmount = isSplitNonStandard ? (baseAmount + gstAmount - tdsAmount) : (baseAmount + gstAmount);
            if (plannedAmountDisplay) {
                plannedAmountDisplay.value = plannedAmount.toFixed(2);
            }

            // Net Payable = Base + GST - TDS
            const netPayable = baseAmount + gstAmount - tdsAmount;

            const isSplit = document.getElementById('editIsSplit')?.value == '1' || document.getElementById('editParentId')?.value != '0';

            // Removed auto-update of Paid Amount based on user request (Paid Amount should not auto-change on edits)

            // Final balance calculation
            let finalPaid = parseFloat(paidAmountInput.value) || 0;
            if (statusField && document.activeElement === statusField) {
                if (statusField.value === 'settle') {
                    finalPaid = 0;
                    if (paidAmountInput) paidAmountInput.value = '0.00';
                } else if (statusField.value === 'paid') {
                    finalPaid = isSplit ? plannedAmount : netPayable;
                    if (paidAmountInput) paidAmountInput.value = finalPaid.toFixed(2);
                }
            }

            const isTaxChange = event && event.target && ['editGstPercentage', 'editApplyGst', 'editTdsPercentage', 'editApplyTds'].includes(event.target.id);
            if (isTaxChange && !window.isNonStandardLoading) {
                const balanceField = document.getElementById('editBalanceAmount');
                const currentBalance = parseFloat(balanceField?.value) || 0;
                finalPaid = Math.max(0, netPayable - currentBalance);
                if (paidAmountInput) paidAmountInput.value = finalPaid.toFixed(2);
            }

            if (finalPaid > netPayable + 0.01) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Amount',
                    text: 'Paid Amount cannot be more than Planned Amount - TDS Amount (₹' + netPayable.toFixed(2) + ')',
                    confirmButtonColor: '#6c5ce7'
                });
                finalPaid = netPayable;
                paidAmountInput.value = netPayable.toFixed(2);
            } else if (finalPaid < 0) {
                finalPaid = 0;
                paidAmountInput.value = '0.00';
            }

            const balance = Math.max(0, (isSplit ? plannedAmount : netPayable) - finalPaid);

            if (balanceDisplay) {
                balanceDisplay.value = balance.toFixed(2);
            }

            // Validations: If balance > 0, status is mandatory. If status is due, due date is mandatory.
            if (statusField) {
                const statusContainer = statusField.closest('div[class^="col-"]');
                if (balance > 0.01) {
                    if (statusContainer) statusContainer.style.display = 'block';
                    if (finalPaid <= 0 || !statusField.value || statusField.value === 'settle') {
                        statusField.value = 'due';
                    }
                    statusField.required = true;
                    statusField.setAttribute('required', 'required');
                    const label = statusField.previousElementSibling;
                    if (label && !label.innerHTML.includes('*')) label.innerHTML += ' <span class="text-danger">*</span>';
                } else {
                    if (statusContainer) statusContainer.style.display = 'none';
                    statusField.required = false;
                    statusField.removeAttribute('required');
                    const label = statusField.previousElementSibling;
                    if (label && label.innerHTML.includes('*')) label.innerHTML = label.innerHTML.replace(' <span class="text-danger">*</span>', '');
                }
            }

            // Finalize UI
            toggleTdsFieldsEdit();
            handleStatusBehavior('non-standard');
        }

        // 3. For Edit Standard Fixed Modal
        function calculateTaxStandardFixed(event) {
            const fixedForm = document.getElementById('editStandardFixedForm');
            if (!fixedForm) return;

            // Get base amount from hidden field (True Base)
            let baseAmount = parseFloat(document.getElementById('editFixedBaseAmount')?.value) || 0;

            // Get planned amount from display (Base + GST)
            const plannedAmountDisplay = document.getElementById('editFixedPlannedAmountDisplay');
            let plannedAmount = 0;
            if (plannedAmountDisplay) {
                plannedAmount = parseFloat(plannedAmountDisplay.value) || 0;
            }

            // Get tax elements
            const applyGst = fixedForm.querySelector('#fixedApplyGst');
            const gstPercentage = fixedForm.querySelector('#fixedGstPercentage');
            const gstAmountField = fixedForm.querySelector('#fixedGstAmount');

            const applyTds = fixedForm.querySelector('#fixedApplyTds');
            const tdsPercentage = fixedForm.querySelector('#fixedTdsPercentage');
            const tdsAmountField = fixedForm.querySelector('#fixedTdsAmount');
            const balanceDisplay = document.getElementById('fixedBalanceAmount');
            const isSplit = fixedForm.querySelector('#editFixedIsSplit');
            const isSplitFixed = document.getElementById('editFixedIsSplit')?.value == '1' || document.getElementById('editFixedParentId')?.value != '0';

            // Use existing GST Amount or reset if unchecked
            let gstAmount = parseFloat(gstAmountField?.value) || 0;
            const isGstTrigger = event && event.target && ['fixedGstPercentage', 'fixedApplyGst', 'editFixedBaseAmount'].includes(event.target.id);
            if (applyGst && !applyGst.checked) {
                gstAmount = 0;
                if (gstAmountField) gstAmountField.value = '0.00';
            } else if (applyGst && applyGst.checked && !isStandardFixedLoading && isGstTrigger) {
                // Recalculate if NOT loading
                const gstPercent = parseFloat(gstPercentage?.value) || 0;
                gstAmount = (baseAmount * gstPercent) / 100;
                if (gstAmountField) gstAmountField.value = gstAmount.toFixed(2);
            }

            // Recalculate TDS if NOT loading
            let tdsAmount = parseFloat(tdsAmountField?.value) || 0;
            const isTdsTrigger = event && event.target && ['fixedTdsPercentage', 'fixedApplyTds', 'editFixedBaseAmount'].includes(event.target.id);
            if (applyTds && !applyTds.checked) {
                tdsAmount = 0;
                if (tdsAmountField) tdsAmountField.value = '0.00';
            } else if (applyTds && applyTds.checked && !isStandardFixedLoading && isTdsTrigger) {
                const tdsPercent = parseFloat(tdsPercentage?.value) || 0;
                tdsAmount = (baseAmount * tdsPercent) / 100;
                if (tdsAmountField) tdsAmountField.value = tdsAmount.toFixed(2);
            }

            updateBreakdownText('fixedPlannedBreakdown', baseAmount, gstAmount, tdsAmount, isSplitFixed);
            // Recalculate Planned Amount (Display only for fixed)
            plannedAmount = isSplitFixed ? (baseAmount + gstAmount - tdsAmount) : (baseAmount + gstAmount);
            if (plannedAmountDisplay) {
                plannedAmountDisplay.value = plannedAmount.toFixed(2);
            }

            // Net Payable = Planned Amount - TDS
            const totalWithTax = baseAmount + gstAmount - tdsAmount;

            const paidAmountInput = document.getElementById('editFixedActualAmount');
            
            let paidAmount = parseFloat(paidAmountInput?.value) || 0;

            const statusField = document.getElementById('editFixedStatus');
            if (statusField && document.activeElement === statusField) {
                if (statusField.value === 'settle') {
                    paidAmount = 0;
                    if (paidAmountInput) paidAmountInput.value = '0.00';
                } else if (statusField.value === 'paid') {
                    paidAmount = isSplitFixed ? plannedAmount : totalWithTax;
                    if (paidAmountInput) paidAmountInput.value = paidAmount.toFixed(2);
                }
            }

            // Validation
            if (paidAmount > totalWithTax + 0.01) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Amount',
                    text: 'Paid Amount cannot be more than Planned Amount - TDS Amount (₹' + totalWithTax.toFixed(2) + ')',
                    confirmButtonColor: '#6c5ce7'
                });
                paidAmount = totalWithTax;
                paidAmountInput.value = totalWithTax.toFixed(2);
            } else if (paidAmount < 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Amount',
                    text: 'Paid Amount cannot be negative',
                    confirmButtonColor: '#6c5ce7'
                });
                paidAmount = 0;
                paidAmountInput.value = '0.00';
            }
            
            const balance = Math.max(0, (isSplitFixed ? plannedAmount : totalWithTax) - paidAmount);

            if (balanceDisplay) {
                balanceDisplay.value = balance.toFixed(2);
            }

            // Enable/Disable status if balance exists
            if (statusField) {
                const statusContainer = statusField.closest('div[class^="col-"]');
                if (balance > 0.01) {
                    if (statusContainer) statusContainer.style.display = 'block';
                    if (paidAmount <= 0 || !statusField.value || statusField.value === 'settle') {
                        statusField.value = 'due';
                    }
                    statusField.required = true;
                    statusField.setAttribute('required', 'required');
                } else {
                    if (statusContainer) statusContainer.style.display = 'none';
                    statusField.required = false;
                    statusField.removeAttribute('required');
                }
            }

            toggleTdsFieldsFixed();
            handleStatusBehavior('fixed');
        }

        function calculateTaxStandardEditable(event) {
            const editableForm = document.getElementById('editStandardEditableForm');
            if (!editableForm) return;

            // Get tax elements
            const applyGst = editableForm.querySelector('#editableApplyGst');
            const gstPercentage = editableForm.querySelector('#editableGstPercentage');
            const gstAmountField = editableForm.querySelector('#editableGstAmount');

            const applyTds = editableForm.querySelector('#editableApplyTds');
            const tdsPercentage = editableForm.querySelector('#editableTdsPercentage');
            const tdsAmountField = editableForm.querySelector('#editableTdsAmount');

            // Get base amount from hidden field
            let baseAmount = parseFloat(document.getElementById('editEditableBaseAmount')?.value) || 0;

            // Planned Amount Display (Actual + GST)
            const plannedAmountField = document.getElementById('editEditablePlannedAmount');
            let plannedAmount = parseFloat(plannedAmountField?.value) || 0;

            // If the driver of this change is the Planned Amount field itself, update the base amount
            if (event && event.target && event.target.id === 'editEditablePlannedAmount') {
                const gstPercent = (applyGst && applyGst.checked) ? (parseFloat(gstPercentage?.value) || 0) : 0;
                baseAmount = plannedAmount / (1 + (gstPercent / 100));
                const baseField = document.getElementById('editEditableBaseAmount');
                if (baseField) baseField.value = baseAmount.toFixed(2);
            }

            // Calculate GST
            let gstAmount = parseFloat(gstAmountField?.value) || 0;
            const isGstTrigger = event && event.target && ['editableGstPercentage', 'editableApplyGst', 'editEditablePlannedAmount'].includes(event.target.id);
            if (applyGst && applyGst.checked && gstPercentage) {
                if (!window.isStandardEditableLoading && isGstTrigger) {
                    const gstPercent = parseFloat(gstPercentage.value) || 0;
                    gstAmount = (baseAmount * gstPercent) / 100;
                    if (gstAmountField) gstAmountField.value = gstAmount.toFixed(2);
                }
            } else if (gstAmountField) {
                gstAmount = 0;
                gstAmountField.value = '0.00';
            }

            // Calculate TDS (calculated on base amount only)
            let tdsAmount = parseFloat(tdsAmountField?.value) || 0;
            const isTdsTrigger = event && event.target && ['editableTdsPercentage', 'editableApplyTds', 'editEditablePlannedAmount'].includes(event.target.id);
            if (applyTds && applyTds.checked && tdsPercentage) {
                if (!window.isStandardEditableLoading && isTdsTrigger) {
                    const tdsPercent = parseFloat(tdsPercentage.value) || 0;
                    const amountForTds = baseAmount;
                    tdsAmount = (amountForTds * tdsPercent) / 100;
                    if (tdsAmountField) tdsAmountField.value = tdsAmount.toFixed(2);
                }
            } else if (tdsAmountField) {
                tdsAmount = 0;
                tdsAmountField.value = '0.00';
            }

            const isSplitEditable = document.getElementById('editEditableIsSplit')?.value == '1' || document.getElementById('editEditableParentId')?.value != '0';
            updateBreakdownText('editablePlannedBreakdown', baseAmount, gstAmount, tdsAmount, isSplitEditable);
            // Recalculate Planned Amount = Base + GST
            plannedAmount = isSplitEditable ? (baseAmount + gstAmount - tdsAmount) : (baseAmount + gstAmount);
            if (plannedAmountField) {
                plannedAmountField.value = plannedAmount.toFixed(2);
            }

            // Net Payable Calculation
            const netPayable = baseAmount + gstAmount - tdsAmount;

            // Get actual paid amount
            const actualAmountField = document.getElementById('editEditableActualAmount');

            let actualAmount = parseFloat(actualAmountField?.value) || 0;

            const statusField = document.getElementById('editEditableStatus');
            if (statusField && document.activeElement === statusField) {
                if (statusField.value === 'settle') {
                    actualAmount = 0;
                    if (actualAmountField) actualAmountField.value = '0.00';
                } else if (statusField.value === 'paid') {
                    actualAmount = isSplitEditable ? plannedAmount : netPayable;
                    if (actualAmountField) actualAmountField.value = actualAmount.toFixed(2);
                }
            }

            const isTaxChange = event && event.target && ['editableGstPercentage', 'editableApplyGst', 'editableTdsPercentage', 'editableApplyTds'].includes(event.target.id);
            if (isTaxChange && !window.isStandardEditableLoading) {
                const balanceField = document.getElementById('edittableBalanceAmount');
                const currentBalance = parseFloat(balanceField?.value) || 0;
                const targetPayable = isSplitEditable ? plannedAmount : netPayable;
                actualAmount = Math.max(0, targetPayable - currentBalance);
                if (actualAmountField) actualAmountField.value = actualAmount.toFixed(2);
            }

            // Validation: Paid Amount cannot be greater than Net Payable or negative
            if (actualAmount > netPayable + 0.01) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Amount',
                    text: 'Paid Amount cannot be more than Planned Amount - TDS Amount (₹' + netPayable.toFixed(2) + ')',
                    confirmButtonColor: '#6c5ce7'
                });
                actualAmount = netPayable;
                if (actualAmountField) actualAmountField.value = netPayable.toFixed(2);
            } else if (actualAmount < 0) {
                actualAmount = 0;
                if (actualAmountField) actualAmountField.value = '0.00';
            }

            // Update balance amount
            const balanceAmount = Math.max(0, (isSplitEditable ? plannedAmount : netPayable) - actualAmount);
            const balanceField = document.getElementById('edittableBalanceAmount');
            if (balanceField) {
                balanceField.value = balanceAmount.toFixed(2);
            }

            // Enable/Disable status if balance exists
            if (statusField) {
                const statusContainer = statusField.closest('div[class^="col-"]');
                if (balanceAmount > 0.01) {
                    if (statusContainer) statusContainer.style.display = 'block';
                    if (actualAmount <= 0 || !statusField.value || statusField.value === 'settle') {
                        statusField.value = 'due';
                    }
                    statusField.required = true;
                    statusField.setAttribute('required', 'required');
                } else {
                    if (statusContainer) statusContainer.style.display = 'none';
                    statusField.required = false;
                    statusField.removeAttribute('required');
                }
            }

            toggleTdsFieldsEditable();
            handleStatusBehavior('editable');
        }
        // Balance calculation for Add Non-Standard Modal
        function calculateBalance() {
            const scheduleAmountInput = document.getElementById('schedule_amount');
            const paid_amountInput = document.getElementById('paid_amount');
            const balanceAmountInput = document.getElementById('balance_amount');

            if (scheduleAmountInput && paid_amountInput && balanceAmountInput) {
                const scheduleAmount = parseFloat(scheduleAmountInput.value) || 0;
                let paidAmount = parseFloat(paid_amountInput.value) || 0;

                const statusDropdown = document.getElementById('payment_status');
                if (statusDropdown && document.activeElement === statusDropdown && (statusDropdown.value === 'settle' || statusDropdown.value === 'paid')) {
                    paidAmount = scheduleAmount;
                    if (paid_amountInput) paid_amountInput.value = paidAmount.toFixed(2);
                }

                if (paidAmount > scheduleAmount + 0.01) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Amount',
                        text: 'Paid Amount (₹' + paidAmount.toFixed(2) + ') cannot be more than Planned Amount - TDS Amount (₹' + scheduleAmount.toFixed(2) + ')',
                        confirmButtonColor: '#6c5ce7'
                    });
                    paidAmount = scheduleAmount;
                    paid_amountInput.value = scheduleAmount.toFixed(2);
                } else if (paidAmount < 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Amount',
                        text: 'Paid Amount cannot be negative',
                        confirmButtonColor: '#6c5ce7'
                    });
                    paidAmount = 0;
                    paid_amountInput.value = '0.00';
                }

                const balance = Math.max(0, scheduleAmount - paidAmount);
                balanceAmountInput.value = balance.toFixed(2);

                // Enable/Disable status based on balance
                if (statusDropdown) {
                    const statusContainer = statusDropdown.closest('div[class^="col-"]');
                    statusDropdown.disabled = false;
                    if (balance > 0.01) {
                        if (statusContainer) statusContainer.style.display = 'block';
                        if (paidAmount <= 0 || !statusDropdown.value) {
                            statusDropdown.value = 'due';
                        }
                        statusDropdown.required = true;
                        statusDropdown.setAttribute('required', 'required');
                    } else {
                        if (statusContainer) statusContainer.style.display = 'none';
                        statusDropdown.required = false;
                        statusDropdown.removeAttribute('required');
                    }
                }

                handleStatusBehavior('non-standard-add');
                handleReceiptsRequirement();
            }
        }

        // ==============================================
        // MODAL EVENT LISTENERS
        // ==============================================

        function initializeAddNonStandardTaxEvents() {
            const actualAmountInput = document.getElementById('actual_amount');
            const gstCheckbox = document.getElementById('apply_gst');
            const tdsCheckbox = document.getElementById('apply_tds');
            const gstPercentageInput = document.getElementById('gst_percentage');
            const tdsPercentageInput = document.getElementById('tds_percentage');
            const paidAmountInput = document.getElementById('paid_amount');

            if (actualAmountInput) actualAmountInput.oninput = calculateTaxNonStandardAdd;
            if (gstCheckbox) gstCheckbox.onchange = calculateTaxNonStandardAdd;
            if (tdsCheckbox) {
                tdsCheckbox.onchange = function () {
                    calculateTaxNonStandardAdd();
                    toggleTdsFieldsAdd();
                };
            }
            if (gstPercentageInput) gstPercentageInput.oninput = calculateTaxNonStandardAdd;
            if (tdsPercentageInput) tdsPercentageInput.oninput = calculateTaxNonStandardAdd;
            if (paidAmountInput) paidAmountInput.oninput = calculateBalance;

            // Initialize calculation
            calculateTaxNonStandardAdd();
            toggleTdsFieldsAdd();
        }

        // Initialize Edit Non-Standard Modal Tax Events
        function initializeEditNonStandardTaxEvents() {
            const editForm = document.getElementById('editExpenseForm');
            if (!editForm) return;

            const applyGst = editForm.querySelector('#editApplyGst');
            const applyTds = editForm.querySelector('#editApplyTds');
            const gstPercentage = editForm.querySelector('#editGstPercentage');
            const tdsPercentage = editForm.querySelector('#editTdsPercentage');
            const paidAmountInput = document.getElementById('editPaidAmount');
            const statusField = document.getElementById('editStatus');

            if (applyGst) applyGst.onchange = calculateTaxNonStandardEdit;
            if (applyTds) {
                applyTds.onchange = function (event) {
                    calculateTaxNonStandardEdit(event);
                    toggleTdsFieldsEdit();
                };
            }
            if (gstPercentage) gstPercentage.oninput = calculateTaxNonStandardEdit;
            if (tdsPercentage) tdsPercentage.oninput = calculateTaxNonStandardEdit;
            if (paidAmountInput) paidAmountInput.oninput = calculateTaxNonStandardEdit;
            if (statusField) statusField.onchange = () => handleStatusBehavior('non-standard');

            // Initial calculation
            calculateTaxNonStandardEdit();
            toggleTdsFieldsEdit();
        }

        // Initialize Edit Standard Fixed Modal Tax Events
        function initializeEditStandardFixedTaxEvents() {
            const fixedForm = document.getElementById('editStandardFixedForm');
            if (!fixedForm) return;

            const applyGst = fixedForm.querySelector('#fixedApplyGst');
            const applyTds = fixedForm.querySelector('#fixedApplyTds');
            const gstPercentage = fixedForm.querySelector('#fixedGstPercentage');
            const tdsPercentage = fixedForm.querySelector('#fixedTdsPercentage');
            const paidAmountInput = document.getElementById('editFixedActualAmount');


            // Remove existing listeners
            if (applyGst) applyGst.onchange = null;
            if (applyTds) applyTds.onchange = null;
            if (gstPercentage) gstPercentage.oninput = null;
            if (tdsPercentage) tdsPercentage.oninput = null;
            if (paidAmountInput) paidAmountInput.oninput = null;

            // Add event listeners for tax calculation triggers
            const taxCalculationEvents = (event) => {
                calculateTaxStandardFixed(event);
                toggleTdsFieldsFixed(); // Also toggle TDS fields if needed
            };

            // GST related events
            if (applyGst) {
                applyGst.addEventListener('change', taxCalculationEvents);
            }
            if (gstPercentage) {
                gstPercentage.addEventListener('input', taxCalculationEvents);
            }

            // TDS related events
            if (applyTds) {
                applyTds.addEventListener('change', taxCalculationEvents);
            }
            if (tdsPercentage) {
                tdsPercentage.addEventListener('input', taxCalculationEvents);
            }

            // Paid amount input - this is CRITICAL for balance calculation
            if (paidAmountInput) {
                paidAmountInput.addEventListener('input', calculateTaxStandardFixed);
            }

            // Initialize calculations
            // calculateTaxStandardFixed();
            toggleTdsFieldsFixed();
        }
        // Initialize Edit Standard Editable Modal Tax Events
        function initializeEditStandardEditableTaxEvents() {
            const editableForm = document.getElementById('editStandardEditableForm');
            if (!editableForm) return;

            // Get relevant elements
            const plannedAmount = document.getElementById('editEditablePlannedAmount');
            const actualAmount = document.getElementById('editEditableActualAmount');
            const applyGst = editableForm.querySelector('#editableApplyGst');
            const applyTds = editableForm.querySelector('#editableApplyTds');
            const gstPercentage = editableForm.querySelector('#editableGstPercentage');
            const tdsPercentage = editableForm.querySelector('#editableTdsPercentage');

            // Remove existing listeners to avoid duplicates
            const removeAndAdd = (el, type, handler) => {
                if (!el) return;
                el.removeEventListener(type, handler);
                el.addEventListener(type, handler);
            };

            removeAndAdd(plannedAmount, 'input', calculateTaxStandardEditable);
            removeAndAdd(actualAmount, 'input', calculateTaxStandardEditable);
            removeAndAdd(applyGst, 'change', calculateTaxStandardEditable);
            removeAndAdd(applyTds, 'change', calculateTaxStandardEditable);
            removeAndAdd(gstPercentage, 'input', calculateTaxStandardEditable);
            removeAndAdd(tdsPercentage, 'input', calculateTaxStandardEditable);

            // Initialize toggle on load
            toggleTdsFieldsEditable();
        }


        // ==============================================
        // FORM SUBMISSION HANDLERS
        // ==============================================

        // Standard Fixed Form Submission
        const standardFixedForm = document.getElementById('editStandardFixedForm');
        if (standardFixedForm) {
            standardFixedForm.addEventListener('submit', async function (e) {
                e.preventDefault();

                // Validate before submission
                let paidAmount = parseFloat(document.getElementById('editFixedActualAmount').value) || 0;
                let plannedAmount = parseFloat(document.getElementById('editFixedPlannedAmountDisplay').value) || 0;
                let tdsAmount = parseFloat(document.getElementById('fixedTdsAmount').value) || 0;
                let isSplitFixed = document.getElementById('editFixedIsSplit').value == '1' || document.getElementById('editFixedParentId').value != '0';
                
                let totalWithTax = isSplitFixed ? plannedAmount : (plannedAmount - tdsAmount);

                if (paidAmount > totalWithTax + 0.01) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Amount',
                        text: 'Paid Amount cannot be more than Planned Amount - TDS Amount (₹' + totalWithTax.toFixed(2) + ')',
                        confirmButtonColor: '#6c5ce7'
                    });
                    document.getElementById('editFixedActualAmount').value = totalWithTax.toFixed(2);
                    return;
                }

                const expenseId = document.getElementById('editFixedExpenseId').value;
                const formData = new FormData(this);
                formData.append('_method', 'PUT');

                try {
                    const response = await fetch(
                        `https://xhtmlreviews.in/beta-finance/manager/expenses/${expenseId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await response.json();
                    if (data.success) {
                        alert(data.message);
                        $('#editStandardFixedModal').modal('hide');
                        location.reload();
                    } else {
                        alert(data.message || 'Error updating expense');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error updating expense');
                }
            });
        }

        // Standard Editable Form Submission
        const standardEditableForm = document.getElementById('editStandardEditableForm');
        if (standardEditableForm) {
            standardEditableForm.addEventListener('submit', async function (e) {
                e.preventDefault();

                // Validate before submission
                let paidAmount = parseFloat(document.getElementById('editEditableActualAmount').value) || 0;
                let plannedAmount = parseFloat(document.getElementById('editEditablePlannedAmount').value) || 0;
                let tdsAmount = parseFloat(document.getElementById('editableTdsAmount').value) || 0;
                let gstAmount = parseFloat(document.getElementById('editableGstAmount').value) || 0;
                let baseAmount = parseFloat(document.getElementById('editEditableBaseAmount').value) || 0;
                let isSplitEditable = document.getElementById('editEditableIsSplit').value == '1' || document.getElementById('editEditableParentId').value != '0';
                
                let netPayable = isSplitEditable ? 
                    plannedAmount : 
                    (baseAmount + gstAmount - tdsAmount);

                if (paidAmount > netPayable + 0.01) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Amount',
                        text: 'Paid Amount cannot be more than Planned Amount - TDS Amount (₹' + netPayable.toFixed(2) + ')',
                        confirmButtonColor: '#6c5ce7'
                    });
                    document.getElementById('editEditableActualAmount').value = netPayable.toFixed(2);
                    return;
                }

                const expenseId = document.getElementById('editEditableExpenseId').value;
                const formData = new FormData(this);
                formData.append('_method', 'PUT');

                try {
                    const response = await fetch(
                        `https://xhtmlreviews.in/beta-finance/manager/expenses/${expenseId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await response.json();
                    if (data.success) {
                        alert(data.message);
                        $('#editStandardEditableModal').modal('hide');
                        location.reload();
                    } else {
                        alert(data.message || 'Error updating expense');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error updating expense');
                }
            });
        }

        // Edit Expense Form Submission (for non-standard)
        const editExpenseForm = document.getElementById('editExpenseForm');
        if (editExpenseForm) {
            editExpenseForm.addEventListener('submit', async function (e) {
                e.preventDefault();

                // Validate before submission
                let paidAmount = parseFloat(document.getElementById('editPaidAmount').value) || 0;
                let baseAmount = parseFloat(document.getElementById('editCurrentBaseAmount').value) || 0;
                let gstAmount = parseFloat(document.getElementById('editGstAmount').value) || 0;
                let tdsAmount = parseFloat(document.getElementById('editTdsAmount').value) || 0;
                let isSplit = document.getElementById('editIsSplit').value == '1' || document.getElementById('editParentId').value != '0';
                
                let netPayable = baseAmount + gstAmount - tdsAmount;

                if (paidAmount > netPayable + 0.01) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Amount',
                        text: 'Paid Amount cannot be more than Planned Amount - TDS Amount (₹' + netPayable.toFixed(2) + ')',
                        confirmButtonColor: '#6c5ce7'
                    });
                    document.getElementById('editPaidAmount').value = netPayable.toFixed(2);
                    return;
                }

                const expenseId = document.getElementById('editExpenseId').value;
                const formData = new FormData(this);

                try {
                    const response = await fetch(
                        `https://xhtmlreviews.in/beta-finance/manager/expenses/${expenseId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await response.json();
                    if (data.success) {
                        alert(data.message);
                        $('#editExpenseModal').modal('hide');
                        location.reload();
                    } else {
                        alert(data.message || 'Error updating expense');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error updating expense');
                }
            });
        }

        // ==============================================
        // EXPENSE DATA LOADING FUNCTIONS
        // ==============================================

        // editExpense definition moved to top of script block

        // Load data for Standard Fixed Expense
        function loadStandardFixedData(expense) {
            console.log('Loading fixed expense data:', expense);

            document.getElementById('editFixedExpenseId').value = expense.id;
            document.getElementById('editFixedIsSplit').value = expense.is_split ? 1 : 0;
            document.getElementById('editFixedParentId').value = expense.parent_id || 0;
            document.getElementById('editFixedExpenseName').value = expense.expense_name;
            document.getElementById('editFixedExpenseNameDisplay').textContent = expense.expense_name;

            // Store original planned amount for calculations
            const plannedAmount = parseFloat(expense.planned_amount) || 0;
            const gstAmount = parseFloat(expense.gst_amount) || 0;
            const tdsAmount = parseFloat(expense.tds_amount) || 0;

            // True Base = Planned Amount - GST (For Split: Planned Amount + TDS - GST)
            const trueBase = expense.is_split ? (plannedAmount + tdsAmount - gstAmount) : (plannedAmount - gstAmount);
            document.getElementById('editFixedBaseAmount').value = trueBase.toFixed(2);

            // Display Planned Amount as Gross (Base + GST)
            document.getElementById('editFixedPlannedAmountDisplay').value = plannedAmount.toFixed(2);
            updateBreakdownText('fixedPlannedBreakdown', trueBase, gstAmount, tdsAmount, expense.is_split || expense.parent_id);

            let actualAmount = plannedAmount - tdsAmount;
            document.getElementById('editFixedActualAmount').value = expense.is_split ? plannedAmount.toFixed(2) : actualAmount.toFixed(2);
            document.getElementById('editFixedMobileNumber').value = expense.mobile_number || '';

            // Get tax-related elements
            const taxSection = document.getElementById('fixedTaxSection');
            const fixedApplyGst = document.getElementById('fixedApplyGst');
            const fixedGstPercentage = document.getElementById('fixedGstPercentage');
            const fixedGstAmount = document.getElementById('fixedGstAmount');
            const fixedApplyTds = document.getElementById('fixedApplyTds');
            const fixedTdsPercentage = document.getElementById('fixedTdsPercentage');
            const fixedTdsAmount = document.getElementById('fixedTdsAmount');

            // Get the actual section DIVs
            const fixedGstSection = document.getElementById('fixedGstSection');
            const fixedTdsSection = document.getElementById('fixedTdsSection');

            // Create a summary display element if it doesn't exist
            let summaryDisplay = document.getElementById('fixedTaxSummary');
            if (!summaryDisplay) {
                summaryDisplay = document.createElement('div');
                summaryDisplay.id = 'fixedTaxSummary';
                summaryDisplay.className = 'alert alert-info mt-2';
                const plannedAmountInput = document.getElementById('editFixedPlannedAmountDisplay');
                if (plannedAmountInput && plannedAmountInput.parentNode) {
                    plannedAmountInput.parentNode.appendChild(summaryDisplay);
                }
            }

            // Calculate tax values
            const gstPercentage = parseFloat(expense.gst_percentage) || 0;
            const tdsPercentage = parseFloat(expense.tds_percentage) || 0;

            // Calculate totals
            const subtotal = plannedAmount;
            const totalAmount = subtotal + gstAmount - tdsAmount;
            console.log(plannedAmount)
            // Update summary display
            updateFixedTaxSummary(plannedAmount, gstAmount, tdsAmount, totalAmount);

            console.log('Tax calculations:', {
                plannedAmount: plannedAmount,
                gstAmount: gstAmount,
                gstPercentage: gstPercentage,
                tdsAmount: tdsAmount,
                tdsPercentage: tdsPercentage,
                subtotal: subtotal,
                totalAmount: totalAmount
            });

            // Show/Hide entire tax section if it exists
            if (taxSection) {
                if ((!expense.has_gst && !expense.has_tds)) {
                    taxSection.style.display = 'none';
                } else {
                    taxSection.style.display = 'block';
                }
            }

            // Handle GST - SHOW/HIDE BASED ON DATA
            if ((expense.has_gst || expense.gst_amount > 0 || expense.gst_tax_id)) {
                // Show GST section/row
                if (fixedGstSection) {
                    fixedGstSection.style.display = 'block';
                }

                // Set values - LOCK BOTH CHECKBOX AND PERCENTAGE TO PREVENT CHANGES BUT ALLOW SUBMISSION
                if (fixedApplyGst) {
                    fixedApplyGst.checked = true;
                    fixedApplyGst.disabled = true;
                    document.getElementById('editFixedApplyGstHidden').value = "1";
                }
                if (fixedGstPercentage) {
                    fixedGstPercentage.value = gstPercentage;
                    fixedGstPercentage.readOnly = true;
                }
                if (fixedGstAmount) {
                    fixedGstAmount.value = gstAmount.toFixed(2);
                }
            } else {
                // Hide GST section/row
                if (fixedGstSection) {
                    fixedGstSection.style.display = 'none';
                }

                // Reset values - ENABLE BOTH FOR EDITING
                if (fixedApplyGst) {
                    fixedApplyGst.checked = false;
                    fixedApplyGst.disabled = true;
                    document.getElementById('editFixedApplyGstHidden').value = "0";
                }
                if (fixedGstPercentage) {
                    fixedGstPercentage.value = 0;
                    fixedGstPercentage.disabled = false;
                }
                if (fixedGstAmount) {
                    fixedGstAmount.value = '0.00';
                }
            }

            // Handle TDS - SHOW/HIDE BASED ON DATA
            const fixedTdsExtraSection = document.getElementById('fixedTdsExtraSection');
            console.log((expense.has_tds || expense.tds_amount > 0 || expense.tds_tax_id))
            if ((expense.has_tds || expense.tds_amount > 0 || expense.tds_tax_id)) {
                // Show TDS section
                if (fixedTdsSection) {
                    fixedTdsSection.style.display = 'block';
                }
                if (fixedTdsExtraSection) {
                    fixedTdsExtraSection.style.display = 'flex';
                }

                // Set values - LOCK BOTH CHECKBOX AND PERCENTAGE
                if (fixedApplyTds) {
                    fixedApplyTds.checked = true;
                    fixedApplyTds.disabled = true;
                    document.getElementById('editFixedApplyTdsHidden').value = "1";
                }
                if (fixedTdsPercentage) {
                    fixedTdsPercentage.value = tdsPercentage;
                    fixedTdsPercentage.readOnly = true;
                }
                if (fixedTdsAmount) {
                    fixedTdsAmount.value = tdsAmount.toFixed(2);
                }
            } else {
                // Hide TDS section/row
                if (fixedTdsSection) {
                    fixedTdsSection.style.display = 'none';
                }
                if (fixedTdsExtraSection) {
                    fixedTdsExtraSection.style.display = 'none';
                }

                // Reset values - ENABLE BOTH FOR EDITING
                if (fixedApplyTds) {
                    fixedApplyTds.checked = false;
                    fixedApplyTds.disabled = true;
                    document.getElementById('editFixedApplyTdsHidden').value = "0";
                }
                if (fixedTdsPercentage) {
                    fixedTdsPercentage.value = 0;
                    fixedTdsPercentage.disabled = false;
                }
                if (fixedTdsAmount) {
                    fixedTdsAmount.value = '0.00';
                }
            }

            // Set other fields
            document.getElementById('editFixedPartyName').value = expense.party_name || '';
            document.getElementById('editFixedDueDate').value = expense.due_date || '';
            document.getElementById('editFixedNotes').value = expense.notes || '';
            if (document.getElementById('editFixedSettleNotes')) {
                document.getElementById('editFixedSettleNotes').value = expense.settle_notes || '';
            }

            // Sanitize date for input field
            const formatDate = (dateStr) => {
                if (!dateStr) return '';
                return dateStr.split('T')[0];
            };

            document.getElementById('editFixedPaidDate').value = formatDate(expense.paid_date);
            document.getElementById('editFixedTdsTaxId').value = expense.tds_tax_id || '';
            document.getElementById('editFixedGstTaxId').value = expense.gst_tax_id || '';

            if (expense.payment_mode) {
                const modeEl = document.getElementById('editFixedPaymentMode');
                modeEl.value = expense.payment_mode;
                document.getElementById('editFixedBankName').value = expense.bank_name || '';
                document.getElementById('editFixedUpiType').value = expense.upi_type || '';
                document.getElementById('editFixedUpiNumber').value = expense.upi_number || '';
                togglePaymentModeDetails(modeEl);
            }

            if (expense.status) {
                let statusVal = (expense.status === 'settle' || expense.status === 'paid') ? 'settle' : (expense.status === 'convert_to_tds' ? 'convert_to_tds' : 'due');
                document.getElementById('editFixedStatus').value = statusVal;
            }

            // Handle status-based behavior
            calculateTaxStandardFixed();

            // Reset loading flag
            setTimeout(() => {
                isStandardFixedLoading = false;
            }, 100);
            handleTdsStatusBehavior('editFixedTdsStatus', 'editFixedTdsFile');

            // Initialize tax calculation
            initializeEditStandardFixedTaxEvents();
        }

        // Function to update the tax summary display
        function updateBreakdownText(displayId, baseAmount, gstAmount, tdsAmount, isSplit = false) {
            const el = document.getElementById(displayId);
            if (!el) return;

            el.className = "ms-1 text-primary fw-normal";
            el.style.fontSize = "0.9em";
            el.style.textTransform = "none";

            let html = `(Base: ₹${baseAmount.toFixed(2)}`;
            if (gstAmount > 0) html += ` + GST: ₹${gstAmount.toFixed(2)}`;
            if (isSplit && tdsAmount > 0) {
                html += ` - TDS: ₹${tdsAmount.toFixed(2)}`;
            }
            let finalAmt = isSplit ? (baseAmount + gstAmount - tdsAmount) : (baseAmount + gstAmount);
            html += ` = ₹${finalAmt.toFixed(2)})`;

            if (!isSplit && tdsAmount > 0) {
                html += ` <span class="text-danger ms-1" style="font-size:0.95em;"><i class="bi bi-info-circle"></i> TDS ₹${tdsAmount.toFixed(2)} deducted from payable</span>`;
            }
            el.innerHTML = html;
        }

        function updateFixedTaxSummary(plannedAmount, gstAmount, tdsAmount, totalAmount) {
            const summaryDisplay = document.getElementById('fixedTaxSummary');
            if (!summaryDisplay) return;

            // User requested: "Total Amount Should Show Only In Standard Editable"
            summaryDisplay.innerHTML = '';
            summaryDisplay.style.display = 'none';
        }

        function updateEditTaxSummary(plannedAmount, gstAmount, tdsAmount, totalAmount) {
            const summaryDisplay = document.getElementById('editTaxSummary');
            if (!summaryDisplay) return;
            console.log(totalAmount)
            let summaryHTML = `
                                                                                                                                                                        <div class="d-flex flex-column" style="min-width: 150px;">
                                                                                                                                                                            ${gstAmount > 0 ? `<div class="text-success small">+ GST: ₹${gstAmount.toFixed(2)}</div>` : ''}
                                                                                                                                                                            <div class="fw-bold mt-1 border-top pt-1">
                                                                                                                                                                                Total: ₹${(parseFloat(plannedAmount) + parseFloat(gstAmount)).toFixed(2) || totalAmount}
                                                                                                                                                                            </div>
                                                                                                                                                                        </div>
                                                                                                                                                                    `;

            summaryDisplay.innerHTML = summaryHTML;
        }

        // Load data for Standard Editable Expense
        function loadStandardEditableData(expense) {
            console.log('Loading editable expense data:', expense);

            document.getElementById('editEditableExpenseId').value = expense.id;
            document.getElementById('editEditableIsSplit').value = expense.is_split ? 1 : 0;
            document.getElementById('editEditableParentId').value = expense.parent_id || 0;
            document.getElementById('editEditableExpenseName').value = expense.expense_name;
            document.getElementById('editEditableExpenseNameDisplay').textContent = expense.expense_name;
            // Calculate correct base amount for editable standard expense
            const gstAmountVal = parseFloat(expense.gst_amount) || 0;
            const tdsAmountVal = parseFloat(expense.tds_amount) || 0;
            const plannedAmount = parseFloat(expense.planned_amount) || 0;

            // True Base = Planned Amount - GST (For Split: Planned Amount + TDS - GST)
            const trueBase = expense.is_split ? (plannedAmount + tdsAmountVal - gstAmountVal) : (plannedAmount - gstAmountVal);
            document.getElementById('editEditableBaseAmount').value = trueBase.toFixed(2);

            // Display Planned Amount as Gross (Base + GST)
            document.getElementById('editEditablePlannedAmount').value = plannedAmount.toFixed(2);
            updateBreakdownText('editablePlannedBreakdown', trueBase, gstAmountVal, tdsAmountVal, expense.is_split || expense.parent_id);
            // Paid amount display = Actual + GST - TDS (Wait, user says this is the entry field)
            // If it's an existing record, show actual_amount. If new/reset, show Net Payable.
            const netPayableVal = plannedAmount - tdsAmountVal;
            let paidAmountVal;
            if (['due', 'pending', 'upcoming'].includes(expense.status)) {
                paidAmountVal = netPayableVal;
            } else {
                paidAmountVal = parseFloat(expense.actual_amount) || netPayableVal;
            }
            console.log('paidAmountVal', paidAmountVal)
            console.log('expense.actual_amount', expense.actual_amount)
            console.log('netPayableVal', netPayableVal)
            document.getElementById('editEditableActualAmount').value = expense.is_split ? plannedAmount.toFixed(2) : paidAmountVal.toFixed(2);
            document.getElementById('editEditablePartyName').value = expense.party_name || '';
            document.getElementById('editEditableMobileNumber').value = expense.mobile_number || '';
            document.getElementById('editEditableDueDate').value = expense.due_date || '';
            document.getElementById('editEditableNotes').value = expense.notes || '';
            if (document.getElementById('editEditableSettleNotes')) {
                document.getElementById('editEditableSettleNotes').value = expense.settle_notes || '';
            }

            // Sanitize date for input field
            const formatDate = (dateStr) => {
                if (!dateStr) return '';
                return dateStr.split('T')[0];
            };

            document.getElementById('editEditablePaidDate').value = formatDate(expense.paid_date);
            document.getElementById('editEditableTdsTaxId').value = expense.tds_tax_id || '';
            document.getElementById('editEditableGstTaxId').value = expense.gst_tax_id || '';

            // Calculate initial balance amount
            const initialActual = parseFloat(expense.actual_amount) || 0;
            const initialBalance = plannedAmount - initialActual;

            // Set initial balance amount
            const balanceFieldVal = document.getElementById('edittableBalanceAmount');
            if (balanceFieldVal) {
                balanceFieldVal.value = initialBalance.toFixed(2);
            }

            // Get the actual section DIVs for EDITABLE expense
            const editableGstSection = document.getElementById('editableGstSection') ||
                document.getElementById('editGstSection'); // The GST div
            const editableTdsSection = document.getElementById('editableTdsSection') ||
                document.getElementById('editTdsSection'); // The TDS div

            console.log('Editable GST Section:', editableGstSection);
            console.log('Editable TDS Section:', editableTdsSection);

            // Set tax data
            const editableApplyGst = document.getElementById('editableApplyGst');
            const editableGstPercentage = document.getElementById('editableGstPercentage');
            const editableGstAmount = document.getElementById('editableGstAmount');
            const editableApplyTds = document.getElementById('editableApplyTds');
            const editableTdsPercentage = document.getElementById('editableTdsPercentage');
            const editableTdsAmount = document.getElementById('editableTdsAmount');

            const gstAmount = parseFloat(expense.gst_amount) || 0;
            const tdsAmount = parseFloat(expense.tds_amount) || 0;
            const netPayable = plannedAmount + gstAmount - tdsAmount;

            // updateEditTaxSummary(initialActual, gstAmount, tdsAmount, netPayable);


            // Handle GST - SHOW ONLY IF TAX EXISTS
            if ((expense.has_gst || expense.gst_tax_id)) {
                // Show GST section/row if element exists
                if (editableGstSection) {
                    editableGstSection.style.display = 'block'; // Show the entire section
                }

                // LOCK BOTH CHECKBOX AND PERCENTAGE WHEN TAX EXISTS
                if (editableApplyGst) {
                    editableApplyGst.checked = true;
                    editableApplyGst.disabled = false; // MUST BE ENABLED TO SUBMIT
                    editableApplyGst.style.pointerEvents = 'none'; // Prevent interaction
                }
                if (editableGstPercentage) {
                    editableGstPercentage.value = expense.gst_percentage || 18;
                    editableGstPercentage.readOnly = true; // Use readOnly instead of disabled
                }
                if (editableGstAmount) {
                    editableGstAmount.value = expense.gst_amount || '0.00';
                }
            } else {
                if (editableGstSection) {
                    editableGstSection.style.display = 'block';
                }

                // Reset and unlock them
                if (editableApplyGst) {
                    editableApplyGst.checked = false;
                    editableApplyGst.disabled = false;
                    editableApplyGst.style.pointerEvents = 'auto'; // Re-enable interaction
                }
                if (editableGstPercentage) {
                    editableGstPercentage.value = 18; // Set default so checking box calculates
                    editableGstPercentage.readOnly = false; // Allow interaction
                }
                if (editableGstAmount) {
                    editableGstAmount.value = '0.00';
                }
            }

            // Handle TDS - SHOW IF TAX EXISTS (regardless of split status)
            if (expense.has_tds || expense.tds_tax_id) {
                // Show TDS section/row if element exists
                if (editableTdsSection) {
                    editableTdsSection.style.display = 'block'; // Show the entire section
                }

                // LOCK BOTH CHECKBOX AND PERCENTAGE WHEN TAX EXISTS
                if (editableApplyTds) {
                    editableApplyTds.checked = true;
                    editableApplyTds.disabled = false; // MUST BE ENABLED TO SUBMIT
                    editableApplyTds.style.pointerEvents = 'none'; // Prevent interaction
                }
                if (editableTdsPercentage) {
                    editableTdsPercentage.value = expense.tds_percentage || 10;
                    editableTdsPercentage.readOnly = true; // Use readOnly instead of disabled
                }
                if (editableTdsAmount) {
                    editableTdsAmount.value = expense.tds_amount || '0.00';
                }
            } else {
                const tdsStatusSection = document.getElementById('editableTdsStatusSection');
                if (editableTdsSection) {
                    editableTdsSection.style.display = 'block';
                }
                if (tdsStatusSection) {
                    tdsStatusSection.style.display = 'flex';
                }

                // Reset and unlock values
                if (editableApplyTds) {
                    editableApplyTds.checked = false;
                    editableApplyTds.disabled = false;
                    editableApplyTds.style.pointerEvents = 'auto'; // Re-enable interaction
                }
                if (editableTdsPercentage) {
                    editableTdsPercentage.value = 10; // Set default so checking box calculates
                    editableTdsPercentage.readOnly = false; // Allow interaction
                }
                if (editableTdsAmount) {
                    editableTdsAmount.value = '0.00';
                }
            }

            if (expense.payment_mode) {
                const modeEl = document.getElementById('editEditablePaymentMode');
                modeEl.value = expense.payment_mode;
                document.getElementById('editEditableBankName').value = expense.bank_name || '';
                document.getElementById('editEditableUpiType').value = expense.upi_type || '';
                document.getElementById('editEditableUpiNumber').value = expense.upi_number || '';
                togglePaymentModeDetails(modeEl);
            }

            // Set status and due date
            if (expense.status) {
                let statusVal = (expense.status === 'settle' || expense.status === 'paid') ? 'settle' : (expense.status === 'convert_to_tds' ? 'convert_to_tds' : 'due');
                document.getElementById('editEditableStatus').value = statusVal;
            }

            // Handle status-based behavior
            calculateTaxStandardEditable();

            // Reset loading flag
            setTimeout(() => {
                window.isStandardEditableLoading = false;
            }, 100);
            handleTdsStatusBehavior('editEditableTdsStatus', 'editEditableTdsFile');

            // Initialize tax calculation with event listeners
            initializeEditStandardEditableTaxEvents();
        }

        // Load data for non-standard expenses

        function loadNonStandardData(expense) {
            console.log('Loading non-standard expense data:', expense);
            window.isNonStandardLoading = true;

            document.getElementById('editExpenseId').value = expense.id;
            document.getElementById('editIsSplit').value = expense.is_split ? 1 : 0;
            document.getElementById('editParentId').value = expense.parent_id || 0;
            document.getElementById('editExpenseNameDisplay').value = expense.expense_name;

            // Load taxes first
            const gstAmountVal = parseFloat(expense.gst_amount) || 0;
            const tdsAmountVal = parseFloat(expense.tds_amount) || 0;

            document.getElementById('editGstTaxId').value = expense.gst_tax_id || '';
            document.getElementById('editGstAmount').value = gstAmountVal.toFixed(2);
            document.getElementById('editGstPercentage').value = expense.gst_percentage || 18;
            document.getElementById('editApplyGst').checked = !!expense.has_gst;

            document.getElementById('editTdsTaxId').value = expense.tds_tax_id || '';
            document.getElementById('editTdsAmount').value = tdsAmountVal.toFixed(2);
            document.getElementById('editTdsPercentage').value = expense.tds_percentage || 10;
            document.getElementById('editApplyTds').checked = !!expense.has_tds;
            document.getElementById('editTdsStatus').value = expense.tds_status || 'not_received';

            // Calculate the base amount: Base = Planned - GST (For Split: Planned + TDS - GST)
            const plannedAmount = parseFloat(expense.planned_amount) || 0;
            const currentBase = (expense.is_split || expense.parent_id) ? (plannedAmount - gstAmountVal + tdsAmountVal) : (plannedAmount - gstAmountVal);
            document.getElementById('editCurrentBaseAmount').value = currentBase.toFixed(2);
            document.getElementById('editPlannedAmountDisplay').value = plannedAmount.toFixed(2);
            updateBreakdownText('nonStandardPlannedBreakdown', currentBase, gstAmountVal, tdsAmountVal, expense.is_split || expense.parent_id);

            document.getElementById('editOriginalAmountDisplay').value = parseFloat(expense.original_total_base || currentBase).toFixed(2);

            let paidAmount;
            if (expense.is_split || expense.parent_id) {
                paidAmount = plannedAmount;
            } else {
                paidAmount = parseFloat(expense.balance_amount) || plannedAmount - tdsAmountVal;
            }
            document.getElementById('editPaidAmount').value = paidAmount.toFixed(2);

            let statusVal = (expense.status === 'settle' || expense.status === 'paid') ? 'settle' : (expense.status === 'convert_to_tds' ? 'convert_to_tds' : 'due');
            document.getElementById('editStatus').value = statusVal;
            document.getElementById('editPartyName').value = expense.party_name || '';
            document.getElementById('editMobileNumber').value = expense.mobile_number || '';
            document.getElementById('editNonStandardDueDate').value = expense.due_date || '';
            document.getElementById('editNotes').value = expense.notes || '';
            if (document.getElementById('editSettleNotes')) {
                document.getElementById('editSettleNotes').value = expense.settle_notes || '';
            }
            document.getElementById('editPaidDate').value = expense.paid_date || '';
            const gstSection = document.getElementById('nonstGstSection');
            const tdsSection = document.getElementById('nonstTdsSection');
            const tdsStatusSection = document.getElementById('nonstTdsStatusSection');
            if (gstSection) gstSection.style.display = 'block';
            if (tdsSection) tdsSection.style.display = 'block';
            if (tdsStatusSection) tdsStatusSection.style.display = 'flex';

            if (expense.payment_mode) {
                const modeEl = document.getElementById('editPaymentMode');
                if (modeEl) {
                    modeEl.value = expense.payment_mode;
                    document.getElementById('editBankName').value = expense.bank_name || '';
                    document.getElementById('editUpiType').value = expense.upi_type || '';
                    document.getElementById('editUpiNumber').value = expense.upi_number || '';
                    togglePaymentModeDetails(modeEl);
                }
            }

            handleTdsStatusBehavior('editTdsStatus', 'editTdsFile');

            // Load existing receipts
            const receiptsList = document.getElementById('receiptsList');
            if (receiptsList) {
                if (expense.receipts && expense.receipts.length > 0) {
                    receiptsList.innerHTML = '';
                    expense.receipts.forEach(receipt => {
                        const receiptItem = document.createElement('div');
                        receiptItem.className = 'list-group-item d-flex justify-content-between align-items-center';
                        receiptItem.innerHTML = `
                                                                                                                                                                                        <div>
                                                                                                                                                                                            <i class="fas fa-file-pdf text-danger me-2"></i>
                                                                                                                                                                                            ${receipt.file_name}
                                                                                                                                                                                            <small class="text-muted ms-2">(${receipt.file_size})</small>
                                                                                                                                                                                        </div>
                                                                                                                                                                                        <div>
                                                                                                                                                                                            <a href="/${receipt.file_path}" target="_blank" class="btn btn-sm btn-outline-primary me-1">
                                                                                                                                                                                                <i class="fas fa-eye"></i>
                                                                                                                                                                                            </a>
                                                                                                                                                                                        </div>
                                                                                                                                                                                    `;
                        receiptsList.appendChild(receiptItem);
                    });
                    document.getElementById('existingReceiptsSection').style.display = 'block';
                } else {
                    document.getElementById('existingReceiptsSection').style.display = 'none';
                }
            }

            // Final tax calculation
            calculateTaxNonStandardEdit();

            // Reset loading flag
            setTimeout(() => {
                window.isNonStandardLoading = false;
            }, 100);

            // Initialize tax calculation with event listeners
            initializeEditNonStandardTaxEvents();
        }


        // ==============================================
        // OTHER FUNCTIONS
        // ==============================================

        // View TDS file
        function viewTdsFile() {
            alert('TDS file viewing feature');
        }

        // Mark as paid
        function markAsPaid(expenseId) {
            if (confirm('Mark this expense as paid?')) {
                fetch(`https://xhtmlreviews.in/beta-finance/manager/expenses/${expenseId}/mark-paid`, {
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
                        } else {
                            alert(data.message || 'Error marking as paid');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error marking as paid');
                    });
            }
        }

        // Delete expense
        function deleteExpense(expenseId) {
            if (confirm('Are you sure you want to delete this expense?')) {
                fetch(`https://xhtmlreviews.in/beta-finance/manager/expenses/${expenseId}`, {
                    method: 'DELETE',
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
                        } else {
                            alert(data.message || 'Error deleting expense');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error deleting expense');
                    });
            }
        }
        window.deleteExpense = deleteExpense;

        // View receipts
        async function viewReceipts(expenseId) {
            try {
                const response = await fetch(
                    `https://xhtmlreviews.in/beta-finance/manager/expenses/${expenseId}/receipts`);
                const data = await response.json();

                const receiptsGallery = document.getElementById('receiptsGallery');
                receiptsGallery.innerHTML = '';

                if (data.receipts && data.receipts.length > 0) {
                    document.getElementById('noReceipts').style.display = 'none';

                    data.receipts.forEach(receipt => {
                        const col = document.createElement('div');
                        col.className = 'col-md-6 mb-3';

                        let previewContent = '';
                        if (receipt.file_type === 'pdf') {
                            previewContent = `
                                                                                                                                                                                                        <div class="card">
                                                                                                                                                                                                            <div class="card-body text-center">
                                                                                                                                                                                                                <i class="fas fa-file-pdf fa-3x text-danger mb-2"></i>
                                                                                                                                                                                                                <h6 class="card-title">${receipt.file_name}</h6>
                                                                                                                                                                                                                <p class="text-muted small">${receipt.file_size}</p>
                                                                                                                                                                                                            </div>
                                                                                                                                                                                                            <div class="card-footer d-flex justify-content-between">
                                                                                                                                                                                                                <a href="${receipt.file_url}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                                                                                                                                                                    <i class="fas fa-eye"></i> View
                                                                                                                                                                                                                </a>
                                                                                                                                                                                                                <a href="${receipt.file_url}" download class="btn btn-sm btn-outline-success">
                                                                                                                                                                                                                    <i class="fas fa-download"></i> Download
                                                                                                                                                                                                                </a>
                                                                                                                                                                                                            </div>
                                                                                                                                                                                                        </div>
                                                                                                                                                                                                    `;
                        } else {
                            previewContent = `
                                                                                                                                                                                                        <div class="card">
                                                                                                                                                                                                            <img src="${receipt.file_url}" class="card-img-top" alt="${receipt.file_name}" style="height: 200px; object-fit: cover;">
                                                                                                                                                                                                            <div class="card-body">
                                                                                                                                                                                                                <h6 class="card-title">${receipt.file_name}</h6>
                                                                                                                                                                                                                <p class="text-muted small">${receipt.file_size}</p>
                                                                                                                                                                                                            </div>
                                                                                                                                                                                                            <div class="card-footer d-flex justify-content-between">
                                                                                                                                                                                                                <a href="${receipt.file_url}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                                                                                                                                                                    <i class="fas fa-expand"></i> Full View
                                                                                                                                                                                                                </a>
                                                                                                                                                                                                                <a href="${receipt.file_url}" download class="btn btn-sm btn-outline-success">
                                                                                                                                                                                                                    <i class="fas fa-download"></i> Download
                                                                                                                                                                                                                </a>
                                                                                                                                                                                                            </div>
                                                                                                                                                                                                        </div>
                                                                                                                                                                                                    `;
                        }

                        col.innerHTML = previewContent;
                        receiptsGallery.appendChild(col);
                    });
                } else {
                    document.getElementById('noReceipts').style.display = 'block';
                }

                const modal = new bootstrap.Modal(document.getElementById('viewReceiptsModal'));
                modal.show();
            } catch (error) {
                console.error('Error viewing receipts:', error);
                alert('Error loading receipts');
            }
        }
        window.viewReceipts = viewReceipts;

        // Open add non-standard expense modal
        function openAddNonStandardModal() {
            // Initialize tax events for add modal
            initializeAddNonStandardTaxEvents();

            // Initialize balance calculation events
            const scheduleAmountInput = document.getElementById('schedule_amount');
            const paidAmountInput = document.getElementById('paid_amount');

            if (scheduleAmountInput) {
                scheduleAmountInput.addEventListener('input', calculateBalance);
            }
            if (paidAmountInput) {
                paidAmountInput.addEventListener('input', calculateBalance);
            }

            const modal = new bootstrap.Modal(document.getElementById('addNonStandardModal'));
            handleTdsStatusBehavior('addTdsStatus', 'addTdsReceipt');
            modal.show();
        }


        // ==============================================
        // CSS TO HIDE TDS FIELDS BY DEFAULT
        // ==============================================

        // Add this style to hide TDS fields initially (only for Add Non-Standard modal)
        const style = document.createElement('style');
        style.textContent = `
                                                                                                                                                                        /* Hide TDS fields in Add Non-Standard Modal by default */
                                                                                                                                                                        #addNonStandardModal .tds-status-field,
                                                                                                                                                                        #addNonStandardModal .tds-receipt-field {
                                                                                                                                                                            display: none !important;
                                                                                                                                                                        }
                                                                                                                                                                    `;
        document.head.appendChild(style);

        // Add more receipt fields in edit modal
        function addMoreReceipt() {
            const container = document.getElementById('receiptsContainer');
            const receiptItem = document.createElement('div');
            receiptItem.className = 'receipt-item mb-3';
            receiptItem.innerHTML = `
                                                                                                                                                                                    <div class="d-flex gap-2">
                                                                                                                                                                                        <input type="file" name="receipts[]" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                                                                                                                                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeReceipt(this)">
                                                                                                                                                                                            <i class="fas fa-times"></i>
                                                                                                                                                                                        </button>
                                                                                                                                                                                    </div>
                                                                                                                                                                                    <small class="text-muted">Supported: JPG, PNG, PDF, DOC (Max: 5MB each)</small>
                                                                                                                                                                                `;
            container.appendChild(receiptItem);
        }

        // Remove receipt field from edit modal
        function removeReceipt(button) {
            button.closest('.receipt-item').remove();
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function () {
            // Set today's date in paid date field
            const today = new Date().toISOString().split('T')[0];
            const paidDateField = document.getElementById('editPaidDate');
            if (paidDateField) {
                paidDateField.value = today;
            }

            // Add Non-Standard Form submission
            const addNonStandardForm = document.getElementById('addNonStandardForm');
            if (addNonStandardForm) {
                addNonStandardForm.addEventListener('submit', function (e) {
                    // Ensure tax calculations are done before submission
                    calculateTaxNonStandardAdd();
                });
            }

            // Add status behavior listeners
            const statusConfigs = [{
                id: 'editStatus',
                calc: calculateTaxNonStandardEdit
            },
            {
                id: 'editFixedStatus',
                calc: calculateTaxStandardFixed
            },
            {
                id: 'editEditableStatus',
                calc: calculateTaxStandardEditable
            },
            {
                id: 'payment_status',
                calc: calculateTaxNonStandardAdd
            }
            ];

            statusConfigs.forEach(config => {
                const el = document.getElementById(config.id);
                if (el) {
                    el.addEventListener('change', function () {
                        // First, update UI visibility based on the new status
                        const isFixed = config.id === 'editFixedStatus';
                        const isEditable = config.id === 'editEditableStatus';
                        const isNonStandard = config.id === 'editStatus';
                        const isNonStandardAdd = config.id === 'payment_status';
                        
                        let modalType = '';
                        if (isFixed) modalType = 'fixed';
                        else if (isEditable) modalType = 'editable';
                        else if (isNonStandard) modalType = 'non-standard';
                        else if (isNonStandardAdd) modalType = 'non-standard-add';
                        
                        if (modalType) {
                            handleStatusBehavior(modalType);
                        }

                        // Only recalculate if NOT switching to 'settle'/'paid', to allow manual overrides without forced reversion
                        if (this.value !== 'settle' && this.value !== 'paid') {
                            config.calc();
                        }
                    });
                }
            });

            // Add TDS status behavior listeners
            const tdsStatusConfigs = [{
                statusId: 'editTdsStatus',
                fileId: 'editTdsFile'
            },
            {
                statusId: 'addTdsStatus',
                fileId: 'addTdsReceipt'
            },
            {
                statusId: 'editFixedTdsStatus',
                fileId: 'editFixedTdsFile'
            },
            {
                statusId: 'editEditableTdsStatus',
                fileId: 'editEditableTdsFile'
            }
            ];

            tdsStatusConfigs.forEach(config => {
                const el = document.getElementById(config.statusId);
                if (el) {
                    el.addEventListener('change', () => handleTdsStatusBehavior(config.statusId, config
                        .fileId));
                }
            });

            setTimeout(() => {
                if (document.getElementById('apply_tds')) toggleTdsFieldsAdd();
                if (document.getElementById('editApplyTds')) toggleTdsFieldsEdit();
                if (document.getElementById('fixedApplyTds')) toggleTdsFieldsFixed();
                if (document.getElementById('editableApplyTds')) toggleTdsFieldsEditable();
            }, 500);
        });

        function handleStatusBehavior(modalType) {
            let statusId, balanceId, dueDateId, notesId;
            let receiptFileId, paymentModeId, paidDateId;

            if (modalType === 'non-standard') {
                statusId = 'editStatus';
                balanceId = 'editBalanceAmount';
                dueDateId = 'editNonStandardDueDate';
                notesId = 'editSettleNotes';
                receiptFileId = 'editReceiptFile';
                paymentModeId = 'editPaymentMode';
                paidDateId = 'editPaidDate';
            } else if (modalType === 'fixed') {
                statusId = 'editFixedStatus';
                balanceId = 'fixedBalanceAmount';
                dueDateId = 'editFixedDueDate';
                notesId = 'editFixedSettleNotes';
                receiptFileId = 'editFixedReceiptFile';
                paymentModeId = 'editFixedPaymentMode';
                paidDateId = 'editFixedPaidDate';
            } else if (modalType === 'editable') {
                statusId = 'editEditableStatus';
                balanceId = 'edittableBalanceAmount';
                dueDateId = 'editEditableDueDate';
                notesId = 'editEditableSettleNotes';
                receiptFileId = 'editEditableReceiptFile';
                paymentModeId = 'editEditablePaymentMode';
                paidDateId = 'editEditablePaidDate';
            } else if (modalType === 'non-standard-add') {
                statusId = 'payment_status';
                balanceId = 'balance_amount';
                dueDateId = 'add_due_date';
                notesId = 'addSettleNotes';
                receiptFileId = 'main_receipt';
                paymentModeId = 'payment_mode';
                paidDateId = 'payment_date';
            }

            const statusEl = document.getElementById(statusId);
            const balanceEl = document.getElementById(balanceId);
            const dueDateEl = document.getElementById(dueDateId);
            const notesEl = typeof notesId !== 'undefined' ? document.getElementById(notesId) : null;
            const notesContainer = typeof notesId !== 'undefined' ? document.getElementById(notesId + 'Container') : null;

            const receiptFileEl = receiptFileId ? document.getElementById(receiptFileId) : null;
            const paymentModeEl = paymentModeId ? document.getElementById(paymentModeId) : null;
            const paidDateEl = paidDateId ? document.getElementById(paidDateId) : null;

            if (!statusEl) return;

            const statusContainer = statusEl.closest('div[class^="col-"]');
            let status = statusEl.value;
            
            // If the status dropdown is hidden (e.g. because balance is 0), treat status as not selected
            if (statusContainer && statusContainer.style.display === 'none') {
                status = '';
            }

            const modal = statusEl.closest('.modal');
            const isDue = (status === 'due' || status === 'pending' || status === 'upcoming' || status === 'overdue');

            // Keep all payment/tax sections visible by default as requested
            modal.querySelectorAll('hr').forEach(hr => hr.style.display = '');
            // (Other sections will naturally stay visible or shown once by initial load)

            let balance = 0;
            if (balanceEl) {
                balance = parseFloat(balanceEl.value || balanceEl.textContent) || 0;
            }

            if (dueDateEl) {
                const dueDateCol = dueDateEl.closest('[class*="col-"]');
                if (dueDateCol) {
                    dueDateCol.style.display = isDue ? '' : 'none';
                }
            }

            if (status === 'settle' || status === 'paid') {
                if (balanceEl && modalType !== 'non-standard-add' && modalType !== 'non-standard') {
                    if (balanceEl.tagName === 'INPUT') {
                        balanceEl.value = '0.00';
                    } else {
                        balanceEl.textContent = '0.00';
                    }
                }
                if (dueDateEl) {
                    dueDateEl.disabled = true;
                    dueDateEl.required = false;
                    dueDateEl.value = '';
                    dueDateEl.removeAttribute('required');
                }
                
                if (notesEl && notesContainer) {
                    notesContainer.style.display = 'block';
                    notesEl.required = true;
                    notesEl.setAttribute('required', 'required');
                }
            } else if (status === 'due') {
                if (dueDateEl) {
                    dueDateEl.disabled = false;
                    dueDateEl.required = true;
                    dueDateEl.setAttribute('required', 'required');
                }
            } else {
                if (dueDateEl) {
                    dueDateEl.disabled = false;
                    dueDateEl.required = false;
                    dueDateEl.removeAttribute('required');
                }
            }

            if (status !== 'settle' && status !== 'paid') {
                if (notesEl && notesContainer) {
                    notesContainer.style.display = 'none';
                    notesEl.required = false;
                    notesEl.removeAttribute('required');
                }
            }

            // Toggling dynamic required attributes and asterisks on payment fields based on status
            if (status === 'settle' || status === 'due' || !status) {
                [receiptFileEl, paymentModeEl, paidDateEl].forEach(el => {
                    if (el) {
                        el.required = false;
                        el.removeAttribute('required');
                        const label = el.closest('.form-group') ? el.closest('.form-group').querySelector('.form-label') : (el.closest('[class*="col-"]') ? el.closest('[class*="col-"]').querySelector('.form-label') : null);
                        if (label) {
                            label.innerHTML = label.innerHTML.replace(' <span class="text-danger">*</span>', '');
                        }
                    }
                });
            } else if (status === 'paid' || status === 'upcoming' || status === 'pending' || status === 'overdue') {
                [receiptFileEl, paymentModeEl, paidDateEl].forEach(el => {
                    if (el) {
                        el.required = true;
                        el.setAttribute('required', 'required');
                        const label = el.closest('.form-group') ? el.closest('.form-group').querySelector('.form-label') : (el.closest('[class*="col-"]') ? el.closest('[class*="col-"]').querySelector('.form-label') : null);
                        if (label && !label.innerHTML.includes('*')) {
                            label.innerHTML += ' <span class="text-danger">*</span>';
                        }
                    }
                });
            }

            // Add receipt requirement check
            if (modalType === 'non-standard-add') {
                handleReceiptsRequirement();
            }
        }

        function handleReceiptsRequirement() {
            const paidAmountInput = document.getElementById('paid_amount');
            const mainReceiptInput = document.getElementById('main_receipt');
            const receiptsLabel = document.getElementById('receipts_label');
            const paymentModeSelect = document.getElementById('payment_mode');
            const paymentDateInput = document.getElementById('payment_date');

            if (!paidAmountInput) return;

            const paidAmount = parseFloat(paidAmountInput.value) || 0;

            if (paidAmount > 0) {
                // Mandatory fields
                if (mainReceiptInput) {
                    mainReceiptInput.required = true;
                    mainReceiptInput.setAttribute('required', 'required');
                }
                if (receiptsLabel && !receiptsLabel.innerHTML.includes('*')) {
                    receiptsLabel.innerHTML += ' <span class="text-danger">*</span>';
                }

                if (paymentModeSelect) {
                    paymentModeSelect.required = true;
                    paymentModeSelect.setAttribute('required', 'required');
                    const label = paymentModeSelect.previousElementSibling;
                    if (label && !label.innerHTML.includes('*')) label.innerHTML += ' <span class="text-danger">*</span>';
                }

                if (paymentDateInput) {
                    paymentDateInput.required = true;
                    paymentDateInput.setAttribute('required', 'required');
                    const label = paymentDateInput.previousElementSibling;
                    if (label && !label.innerHTML.includes('*')) label.innerHTML += ' <span class="text-danger">*</span>';
                }
            } else {
                // Optional fields
                if (mainReceiptInput) {
                    mainReceiptInput.required = false;
                    mainReceiptInput.removeAttribute('required');
                }
                if (receiptsLabel) {
                    receiptsLabel.innerHTML = receiptsLabel.innerHTML.replace(' <span class="text-danger">*</span>', '');
                }

                if (paymentModeSelect) {
                    paymentModeSelect.required = false;
                    paymentModeSelect.removeAttribute('required');
                    const label = paymentModeSelect.previousElementSibling;
                    if (label) label.innerHTML = label.innerHTML.replace(' <span class="text-danger">*</span>', '');
                }

                if (paymentDateInput) {
                    // Keep required if it was originally required, but user said "if paid amount is there ... mandatory"
                    // which might imply it's NOT mandatory if amount is 0. 
                    // However, payment_date usually is needed. I'll toggle it as requested.
                    paymentDateInput.required = false;
                    paymentDateInput.removeAttribute('required');
                    const label = paymentDateInput.previousElementSibling;
                    if (label) label.innerHTML = label.innerHTML.replace(' <span class="text-danger">*</span>', '');
                }
            }
        }

        function togglePaymentModeDetails(selectEl) {
            const modal = selectEl.closest('.modal');
            if (!modal) return;

            const val = selectEl.value;
            const detailsRow = modal.querySelector('.payment-mode-details');
            if (!detailsRow) return;

            const bankDetails = detailsRow.querySelectorAll('.bank-details');
            const upiDetails = detailsRow.querySelectorAll('.upi-details');

            // Remove required from all first
            const allInputs = detailsRow.querySelectorAll('select, input');
            allInputs.forEach(input => {
                input.required = false;
                input.removeAttribute('required');
            });
            
            // Remove asterisk from all labels
            const allLabels = detailsRow.querySelectorAll('.form-label');
            allLabels.forEach(label => {
                label.innerHTML = label.innerHTML.replace(' <span class="text-danger">*</span>', '');
            });

            // Reset display
            detailsRow.style.display = 'none';
            bankDetails.forEach(el => el.style.display = 'none');
            upiDetails.forEach(el => el.style.display = 'none');

            if (val === 'bank_transfer' || val === 'cheque') {
                detailsRow.style.display = 'flex';
                bankDetails.forEach(el => {
                    el.style.display = 'block';
                    const input = el.querySelector('select, input');
                    const label = el.querySelector('.form-label');
                    if (input) {
                        input.required = true;
                        input.setAttribute('required', 'required');
                    }
                    if (label && !label.innerHTML.includes('*')) {
                        label.innerHTML += ' <span class="text-danger">*</span>';
                    }
                });
            } else if (val === 'upi' || val === 'online') {
                detailsRow.style.display = 'flex';
                upiDetails.forEach(el => {
                    el.style.display = 'block';
                    const input = el.querySelector('select, input');
                    const label = el.querySelector('.form-label');
                    if (input) {
                        input.required = true;
                        input.setAttribute('required', 'required');
                    }
                    if (label && !label.innerHTML.includes('*')) {
                        label.innerHTML += ' <span class="text-danger">*</span>';
                    }
                });
            }
        }

        function handleTdsStatusBehavior(statusId, fileId) {
            const statusEl = document.getElementById(statusId);
            const fileEl = document.getElementById(fileId);

            if (!statusEl || !fileEl) return;

            const status = statusEl.value;
            if (status === 'received' || status === 'paid') {
                fileEl.required = true;
                fileEl.setAttribute('required', 'required');

                const label = fileEl.closest('[class*="col-"]').querySelector('.form-label');
                if (label && !label.innerHTML.includes('*')) {
                    label.innerHTML += ' <span class="text-danger">*</span>';
                }
            } else {
                fileEl.required = false;
                fileEl.removeAttribute('required');

                const label = fileEl.closest('[class*="col-"]').querySelector('.form-label');
                if (label) {
                    label.innerHTML = label.innerHTML.replace(' <span class="text-danger">*</span>', '');
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const statusSelect = document.getElementById('payment_status');
            const paidAmountInput = document.getElementById('paid_amount');
            const grandTotalInput = document.getElementById('grand_total');
            const splitPaymentSection = document.getElementById('splitPaymentSection');

            function checkSplitPayment() {
                const status = statusSelect.value;
                const paidAmount = parseFloat(paidAmountInput.value) || 0;
                const grandTotal = parseFloat(grandTotalInput.value) || 0;

                console.log(status === 'paid' && paidAmount > 0 && paidAmount < grandTotal)
                console.log(paidAmount)
                console.log(grandTotal)
                console.log(status)
                if (status === 'paid' && paidAmount > 0 && paidAmount < grandTotal) {
                    splitPaymentSection.style.display = 'block';
                    document.getElementById('split_payment').value = '1';
                    document.getElementById('create_new_for_balance').value = '1';
                } else {
                    splitPaymentSection.style.display = 'none';
                    document.getElementById('split_payment').value = '0';
                    document.getElementById('create_new_for_balance').value = '0';
                }
            }

            // Add event listeners with null checks
            if (statusSelect) statusSelect.addEventListener('change', checkSplitPayment);
            if (paidAmountInput) paidAmountInput.addEventListener('input', checkSplitPayment);

            // Initial check
            if (statusSelect && paidAmountInput && grandTotalInput) {
                checkSplitPayment();
            }
        });
        // View split history
        async function viewSplitHistory(expenseId) {
            try {
                const response = await fetch(
                    `https://xhtmlreviews.in/beta-finance/manager/expenses/${expenseId}/split-history`);
                const data = await response.json();

                const splitHistoryContent = document.getElementById('splitHistoryContent');
                splitHistoryContent.innerHTML = '';

                if (data.success && (data.parent_expense || data.children.length > 0)) {
                    document.getElementById('noSplitHistory').style.display = 'none';

                    let historyHTML = '';

                    // Show parent expense if this is a child
                    if (data.parent_expense) {
                        historyHTML += `
                            <h6 class="mb-3">Original Expense (Parent)</h6>
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered text-center">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Base amount</th>
                                            <th>gst (${parseFloat(data.parent_expense.gst_percentage || 0)}%)</th>
                                            <th>tds (${parseFloat(data.parent_expense.tds_percentage || 0)}%)</th>
                                            <th>payable</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>₹${parseFloat(data.parent_expense.original_total || 0).toFixed(2)}</td>
                                            <td>₹${parseFloat(data.parent_expense.gst_amount || 0).toFixed(2)}</td>
                                            <td>₹${parseFloat(data.parent_expense.tds_amount || 0).toFixed(2)}</td>
                                            <td>₹${parseFloat(data.parent_expense.planned_amount || 0).toFixed(2)}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        `;
                    }

                    // Show all children (split transactions)
                    if (data.children.length > 0) {
                        historyHTML += `
                                                                                                                                                                                <h6 class="mt-4 mb-3">Split Transactions:</h6>
                                                                                                                                                                                <div class="table-responsive">
                                                                                                                                                                                    <table class="table table-hover">
                                                                                                                                                                                        <thead>
                                                                                                                                                                                            <tr>
                                                                                                                                                                                                <th>Split #</th>
                                                                                                                                                                                                <th>Expense ID</th>
                                                                                                                                                                                                <th>Amount</th>
                                                                                                                                                                                                <th>GST Amount</th>
                                                                                                                                                                                                <th>TDS Amount</th>
                                                                                                                                                                                                <th>Status</th>
                                                                                                                                                                                                <th>Created Date</th>
                                                                                                                                                                                                <th>Due Date</th>
                                                                                                                                                                                            </tr>
                                                                                                                                                                                        </thead>
                                                                                                                                                                                        <tbody>
                                                                                                                                                                            `;

                        data.children.forEach((child, index) => {
                            const statusClass = {
                                'paid': 'success',
                                'pending': 'warning',
                                'overdue': 'danger',
                                'due': 'info'
                            }[child.status] || 'secondary';

                            historyHTML += `
                                                                                                                                                                                    <tr ${child.id == expenseId ? 'class="table-info"' : ''}>
                                                                                                                                                                                        <td>${index + 1}</td>
                                                                                                                                                                                        <td>
                                                                                                                                                                                            <span class="badge bg-${child.id == expenseId ? 'primary' : 'secondary'}">
                                                                                                                                                                                                #${child.id}
                                                                                                                                                                                            </span>
                                                                                                                                                                                        </td>
                                                                                                                                                                                        <td>₹${parseFloat(child.planned_amount).toFixed(2)}</td>
                                                                                                                                                                                        <td>₹${parseFloat(child.gst_amount || 0).toFixed(2)}</td>
                                                                                                                                                                                        <td>₹${parseFloat(child.tds_amount || 0).toFixed(2)}</td>
                                                                                                                                                                                        <td>
                                                                                                                                                                                            <span class="badge bg-${statusClass}">
                                                                                                                                                                                                ${child.status}
                                                                                                                                                                                            </span>
                                                                                                                                                                                        </td>
                                                                                                                                                                                        <td>${new Date(child.created_at).toLocaleDateString()}</td>
                                                                                                                                                                                        <td>${child.due_date ? new Date(child.due_date).toLocaleDateString() : '-'}</td>

                                                                                                                                                                                    </tr>
                                                                                                                                                                                `;
                            
                            if (child.status === 'settle' || child.settle_notes) {
                                let balanceAmt = parseFloat(child.balance_amount || 0).toFixed(2);
                                let notes = child.settle_notes ? `(${child.settle_notes})` : '';
                                if (parseFloat(balanceAmt) > 0) {
                                    historyHTML += `
                                        <tr class="table-light">
                                            <td colspan="2"></td>
                                            <td colspan="3">
                                                <span class="fw-bold text-secondary">₹${balanceAmt}</span>
                                                <div class="text-muted small mt-1">${notes}</div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">Settled</span>
                                            </td>
                                            <td colspan="2"></td>
                                        </tr>
                                    `;
                                }
                            }
                        });

                        historyHTML += `
                                                                                                                                                                                        </tbody>
                                                                                                                                                                                    </table>
                                                                                                                                                                                </div>
                                                                                                                                                                            `;
                    }

                    // Show summary
                    if (data.summary) {
                        historyHTML += `
                            <h6 class="mt-4 mb-3">Split Summary</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered text-center">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Original Payable Amount</th>
                                            <th>Total Paid</th>
                                            <th>Total Balance</th>
                                            <th>Tds Bal. Amount</th>
                                            <th>Split Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>₹${parseFloat(data.summary.original_amount || 0).toFixed(2)}</td>
                                            <td class="text-success">₹${parseFloat(data.summary.total_paid || 0).toFixed(2)}</td>
                                            <td class="text-warning">₹${parseFloat(data.summary.total_balance || 0).toFixed(2)}</td>
                                            <td>₹${parseFloat(data.summary.tds_balance || 0).toFixed(2)}</td>
                                            <td>${data.summary.split_count}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        `;
                    }

                    splitHistoryContent.innerHTML = historyHTML;
                } else {
                    document.getElementById('noSplitHistory').style.display = 'block';
                }

                const modal = new bootstrap.Modal(document.getElementById('splitHistoryModal'));
                modal.show();
            } catch (error) {
                console.error('Error loading split history:', error);
                alert('Error loading split history');
            }
        }
        window.viewSplitHistory = viewSplitHistory;
    </script>

    <style>
        .summary-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            height: 100%;
            transition: all 0.3s ease;
        }

        .summary-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .summary-header {
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .summary-header h6 {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin: 0;
        }

        .summary-body h3 {
            font-size: 24px;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }

        .summary-icon {
            font-size: 32px;
            opacity: 0.8;
        }

        .btn-group .btn.active {
            background-color: #4e73df;
            color: white;
            border-color: #4e73df;
        }

        .badge {
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 500;
        }

        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .card-tools {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-group-sm .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>

    <style>
        .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px 12px 0 0;
            padding: 20px 24px;
            border: none;
        }

        .modal-title {
            font-weight: 600;
            font-size: 1.25rem;
        }

        .btn-close {
            filter: brightness(0) invert(1);
        }

        .modal-body {
            padding: 24px;
            max-height: 70vh;
            overflow-y: auto;
        }

        .form-label {
            color: #4a5568;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 0.875rem;
        }

        .form-control,
        .form-select {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 0.9375rem;
            transition: all 0.2s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-control-sm,
        .form-select-sm {
            padding: 8px 12px;
            font-size: 0.875rem;
        }

        .tax-section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 16px;
        }

        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }

        .input-group-text {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-left: none;
            color: #64748b;
        }

        .grand-total-box {
            background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
            border: 2px solid #667eea;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 20px;
        }

        .grand-total-box .form-control {
            font-size: 1.5rem;
            font-weight: 700;
            color: #667eea;
            border: none;
            background: transparent;
            text-align: center;
        }

        .receipt-item {
            position: relative;
        }

        .btn-outline-danger {
            border-color: #ef4444;
            color: #ef4444;
        }

        .btn-outline-danger:hover {
            background: #ef4444;
            color: white;
        }

        .btn-outline-secondary {
            border-color: #cbd5e1;
            color: #64748b;
        }

        .btn-outline-secondary:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
        }

        .modal-footer {
            border-top: 1px solid #e2e8f0;
            padding: 16px 24px;
            background: #f8fafc;
            border-radius: 0 0 12px 12px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 10px 24px;
            font-weight: 500;
            border-radius: 8px;
            transition: transform 0.2s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #e2e8f0;
            border: none;
            color: #64748b;
            padding: 10px 24px;
            font-weight: 500;
            border-radius: 8px;
        }

        .section-divider {
            border-top: 2px solid #e2e8f0;
            margin: 24px 0;
        }

        .bg-light {
            background-color: #f8fafc !important;
        }

        textarea.form-control {
            resize: vertical;
        }

        .text-muted {
            color: #94a3b8 !important;
            font-size: 0.8125rem;
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            margin: 0;
        }

        .filter-badges .badge .btn-close {
            padding: 0.25rem;
            font-size: 0.6rem;
            line-height: 1;
            opacity: 0.7;
            filter: invert(1) grayscale(100%) brightness(200%);
            transition: opacity 0.2s;
        }

        .filter-badges .badge .btn-close:hover {
            opacity: 1;
        }

        /* Specific colors for close buttons */
        .filter-badges .badge.bg-success .btn-close {
            filter: brightness(0) invert(1);
        }

        .filter-badges .badge.bg-warning .btn-close {
            filter: brightness(0) invert(1);
        }

        .filter-badges .badge.bg-danger .btn-close {
            filter: brightness(0) invert(1);
        }

        .filter-badges .badge.bg-info .btn-close {
            filter: brightness(0) invert(1);
        }

        .filter-badges .badge.bg-primary .btn-close {
            filter: brightness(0) invert(1);
        }
    </style>

@endsection