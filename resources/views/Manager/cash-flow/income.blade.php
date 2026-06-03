@extends('Manager.layouts.app')
@section('content')
    <div id="income" class="manager-panel">
        <!-- Date Range & Filter Section -->
        <div class="filter-section mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Date Range</label>
                    <select class="form-select form-select-sm" id="dateRangeFilter" onchange="applyFilters()">
                        <option value="today" {{ $dateRange == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="week" {{ $dateRange == 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ $dateRange == 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="quarter" {{ $dateRange == 'quarter' ? 'selected' : '' }}>This Quarter</option>
                        <option value="year" {{ $dateRange == 'year' ? 'selected' : '' }}>This Year</option>
                        <option value="next7days" {{ $dateRange == 'next7days' ? 'selected' : '' }}>Next 7 Days</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Company</label>
                    <select class="form-select form-select-sm" id="companyFilter" onchange="applyFilters()">
                        <option value="">All Companies</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}" {{ $companyId == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Category</label>
                    <select class="form-select form-select-sm" id="categoryFilter" onchange="applyFilters()">
                        <option value="all" {{ $category == 'all' ? 'selected' : '' }}>All Types</option>
                        <option value="standard" {{ $category == 'standard' ? 'selected' : '' }}>Standard</option>
                        <option value="non-standard" {{ $category == 'non-standard' ? 'selected' : '' }}>Non Standard
                        </option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Status</label>
                    <select class="form-select form-select-sm" id="statusFilter" onchange="applyFilters()">
                        <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All Status</option>
                        <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="received" {{ $status == 'received' ? 'selected' : '' }}>Received</option>
                        <option value="overdue" {{ $status == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="upcoming" {{ $status == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Currency</label>
                    <select class="form-select form-select-sm" id="currencyFilter" onchange="applyFilters()">
                        <option value="all" {{ $currency == 'all' ? 'selected' : '' }}>All Currencies</option>
                        <option value="INR" {{ $currency == 'INR' ? 'selected' : '' }}>INR (₹)</option>
                        <option value="USD" {{ $currency == 'USD' ? 'selected' : '' }}>USD ($)</option>
                    </select>
                </div>
            </div>

            <!-- Second row for Reset button -->
            <div class="row g-3 mt-2">
                <div class="col-md-12 d-flex justify-content-end">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetFilters()">
                        <i class="fas fa-redo me-1"></i> Reset All Filters
                    </button>
                </div>
            </div>
        </div>

        @php
            $selectedCurrencySymbol = ($currency == 'USD' ? '$' : ($currency == 'EUR' ? '€' : ($currency == 'GBP' ? '£' : '₹')));
        @endphp
        <!-- Summary Cards Row -->
        <div class="row mb-4">
            <div class="col-4 mb-3">
                <div class="summary-card">
                    <div class="summary-header">
                        <h6 class="mb-1">{{ $currentMonth }}-{{ $nextMonth }} {{ $currentYear }} Payments</h6>
                    </div>
                    <div class="summary-body">
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <h3 class="mb-0" id="totalPayments">
                                    {{ $selectedCurrencySymbol }}{{ number_format($stats['totalPayments'] ?? 0, 2) }}
                                </h3>
                                <small class="text-muted">{{ $stats['paymentItems'] ?? 0 }} Items</small>
                            </div>
                            <div class="summary-icon">
                                <i class="fas fa-money-bill-wave text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-4 mb-3">
                <div class="summary-card">
                    <div class="summary-header">
                        <h6 class="mb-1">{{ $currentMonth }}-{{ $nextMonth }} {{ $currentYear }} Received</h6>
                    </div>
                    <div class="summary-body">
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <h3 class="mb-0" id="receivedAmount">
                                    {{ $selectedCurrencySymbol }}{{ number_format($stats['totalReceived'] ?? 0, 2) }}
                                </h3>
                                <small class="text-muted">{{ $stats['receivedItems'] ?? 0 }} items</small>
                            </div>
                            <div class="summary-icon">
                                <i class="fas fa-check-circle text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-4 mb-3">
                <div class="summary-card">
                    <div class="summary-header">
                        <h6 class="mb-1">{{ $currentMonth }}-{{ $nextMonth }} {{ $currentYear }} Pending</h6>
                    </div>
                    <div class="summary-body">
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <h3 class="mb-0" id="pendingAmount">
                                    {{ $selectedCurrencySymbol }}{{ number_format($stats['totalPending'] ?? 0, 2) }}
                                </h3>
                                <small class="text-muted">{{ $stats['pendingItems'] ?? 0 }} items</small>
                            </div>
                            <div class="summary-icon">
                                <i class="fas fa-clock text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-4 mb-3">
                <div class="summary-card">
                    <div class="summary-header">
                        <h6 class="mb-1">{{ $currentMonth }}-{{ $nextMonth }} {{ $currentYear }} Over Due</h6>
                    </div>
                    <div class="summary-body">
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <h3 class="mb-0" id="overdueAmount">
                                    {{ $selectedCurrencySymbol }}{{ number_format($stats['overdue'] ?? 0, 2) }}
                                </h3>
                                <small class="text-muted">{{ $stats['overdueItems'] ?? 0 }} items</small>
                            </div>
                            <div class="summary-icon">
                                <i class="fas fa-exclamation-triangle text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-4 mb-3">
                <div class="summary-card">
                    <div class="summary-header">
                        <h6 class="mb-1">Total Over Due</h6>
                    </div>
                    <div class="summary-body">
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <h3 class="mb-0" id="totalOverdueAmount">
                                    {{ $selectedCurrencySymbol }}{{ number_format($stats['allTimeOverdue'] ?? 0, 2) }}
                                </h3>
                                <small class="text-muted">{{ $stats['allTimeOverdueItems'] ?? 0 }} items</small>
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
                        <h6 class="mb-1">All Payments</h6>
                    </div>
                    <div class="summary-body">
                        <div class="btn-group w-100">
                            <button
                                class="btn btn-outline-primary {{ request('status') == 'all' || !request('status') ? 'active' : '' }}"
                                onclick="filterPayments('all')" id="btnAll">
                                All Payments
                            </button>
                            <button class="btn btn-outline-warning {{ request('status') == 'pending' ? 'active' : '' }}"
                                onclick="filterPayments('pending')" id="btnPending">
                                Only Pending
                            </button>
                            <button class="btn btn-outline-info {{ request('status') == 'upcoming' ? 'active' : '' }}"
                                onclick="filterPayments('upcoming')" id="btnUpcoming">
                                Only Upcoming
                            </button>
                            <button class="btn btn-outline-success {{ request('status') == 'received' ? 'active' : '' }}"
                                onclick="filterPayments('received')" id="btnReceived">
                                Only Received
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
                <div class="card-tools">
                    <button class="btn btn-sm btn-primary" onclick="openAddIncomeModal()">
                        <i class="fas fa-plus"></i> Add Non-standard Income
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Invoice No</th>
                                <th>Company</th>
                                <th>Client Name</th>
                                <th>Payable Amount</th>
                                <th>Base Amount</th>
                                <th>Income Type</th>
                                <th>Status</th>
                                <th>Created Date</th>
                                <th>Mail Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="incomeTableBody">
                            @foreach ($incomes as $income)
                                <tr>
                                    <td>
                                        <a href="{{ route('manager.income.view', $income->id) }}" class="fw-bold text-primary text-decoration-none">
                                            {{ $income->invoice_number ?? ('#INC-' . $income->id) }}
                                        </a>
                                    </td>
                                    <td>
                                        <strong>{{ $income->company->name ?? 'N/A' }}</strong>
                                    </td>
                                    <td>{{ $income->client_name }}</td>

                                    <td>
                                        @php
                                            $itemCurrency = $income->currency ?? ($income->invoice->currency ?? 'INR');
                                            $itemSymbol = ($itemCurrency == 'USD' ? '$' : ($itemCurrency == 'EUR' ? '€' : ($itemCurrency == 'GBP' ? '£' : '₹')));
                                        @endphp
                                        <strong>₹{{ number_format($income->amount, 2) }}</strong>
                                    </td>
                                    <td>
                                        @php
                                            $itemCurrency = $income->currency ?? ($income->invoice->currency ?? 'INR');
                                            $itemSymbol = ($itemCurrency == 'USD' ? '$' : ($itemCurrency == 'EUR' ? '€' : ($itemCurrency == 'GBP' ? '£' : '₹')));
                                            $displayBaseAmount = $income->actual_amount ?? 0;
                                        @endphp
                                        <strong class="{{ $displayBaseAmount > 0 ? 'text-success' : 'text-muted' }}">
                                            {{ $itemSymbol }}{{ number_format($displayBaseAmount, 2) }}
                                        </strong>
                                    </td>

                                    <td>
                                        <span class="badge {{ $income->invoice_id ? 'bg-info' : 'bg-secondary' }}">
                                            {{ $income->invoice_id ? 'Standard' : 'Non-Standard' }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'received' => 'success',
                                                'pending' => 'warning',
                                                'upcoming' => 'info',
                                                'overdue' => 'danger',
                                            ];
                                            $statusText = [
                                                'received' => 'Paid',
                                                'pending' => 'Pending',
                                                'upcoming' => 'Upcoming',
                                                'overdue' => 'Overdue',
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$income->status] ?? 'secondary' }}">
                                            {{ $statusText[$income->status] ?? ucfirst($income->status) }}
                                        </span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($income->created_at)->format('d M Y') }}</td>
                                    <td>
                                        <span class="badge {{ $income->mail_status ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $income->mail_status ? 'Yes' : 'No' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            @if ($income->status != 'received')

                                                <button class="btn btn-sm btn-outline-primary"
                                                    onclick="openEditIncomeModal({{ $income->id }})">
                                                    <i class="fas fa-edit me-1"></i>
                                                </button>
                                            @endif
                                            <div class="btn-group btn-group-sm">

                                                <button class="btn btn-outline-secondary"
                                                    onclick="viewProforma({{ $income->id }})">
                                                    <i class="fas fa-eye me-1"></i>

                                                </button>
                                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                    data-bs-target="#sendInvoiceModal" data-income-id="{{ $income->id }}">
                                                    <i class="fas fa-envelope"></i>
                                                </button>

                                                @if ($income->is_split || $income->parent_id)
                                                    <button class="btn btn-outline-info btn-sm ms-1"
                                                        onclick="viewSplitHistory({{ $income->id }})" title="View Split History">
                                                        <i class="fas fa-code-branch"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($incomes->hasPages())
                    <div class="pagination-container">
                        <div class="pagination-info">
                            Showing <span>{{ $incomes->firstItem() }}</span> to <span>{{ $incomes->lastItem() }}</span> of
                            <span>{{ $incomes->total() }}</span> entries
                        </div>
                        <div class="pagination-links">
                            {{ $incomes->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Receive Payment Modal (Partial Payment) -->
    <div class="modal fade" id="receivePaymentModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Record Payment – Partial Amount Received</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="receivePaymentForm">
                    @csrf
                    <input type="hidden" id="receiveIncomeId" name="income_id">
                    <input type="hidden" id="originalAmount" name="original_amount">

                    <div class="modal-body">
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle"></i>
                            <strong>Client has paid less than the scheduled amount.</strong>
                            Confirm how to handle this payment.
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Invoice No</label>
                                <p class="form-control-plaintext fw-bold" id="invoiceNoDisplay">N/A</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Company</label>
                                <p class="form-control-plaintext fw-bold" id="companyDisplay"></p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Client / Party</label>
                                <p class="form-control-plaintext fw-bold" id="clientDisplay"></p>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Original Scheduled Amount</label>
                                <p class="form-control-plaintext fw-bold" id="originalAmountDisplay"></p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Amount Received Now *</label>
                                <input type="number" class="form-control" id="receivedAmount" name="received_amount"
                                    step="0.01" required>
                                <small class="text-muted">Must be less than original amount</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Balance Amount (auto)</label>
                                <p class="form-control-plaintext fw-bold text-danger" id="balanceAmountDisplay">0.00</p>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="createNewProforma"
                                        name="create_new_proforma" checked>
                                    <label class="form-check-label fw-bold" for="createNewProforma">
                                        Keep Balance & Create New Proforma
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4" id="newProformaSection">
                            <div class="col-md-6">
                                <label class="form-label">New Due Date for Balance *</label>
                                <input type="date" class="form-control" id="newDueDate" name="new_due_date" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Date *</label>
                                <input type="date" class="form-control" id="paymentDate" name="payment_date" max="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <label class="form-label">Internal Note (optional)</label>
                                <textarea class="form-control" id="internalNote" name="internal_note" rows="3"
                                    placeholder="e.g., Client paid 50,000 now, remaining 50,000 to be paid next month."></textarea>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle"></i> What will happen now?</h6>
                            <ul class="mb-0">
                                <li>A taxable invoice will be generated for the amount received now.</li>
                                <li>A new proforma will be created for the balance with the new due date.</li>
                                <li>The original proforma will be marked as Replaced.</li>
                                <li><strong>Once confirmed, this action cannot be undone.</strong></li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Confirm Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add/Edit Income Modal -->
    <div class="modal fade" id="incomeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Non-standard Income</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="incomeForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="incomeId" name="id">

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="companyId" class="form-label">Company *</label>
                                <select class="form-select" id="companyId" name="company_id" required>
                                    <option value="">Select Company</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="clientName" class="form-label">Client Name / Description *</label>
                                <input type="text" class="form-control" id="clientName" name="client_name" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="actualAmount" class="form-label">Base Amount *</label>
                                <input type="number" step="0.01" class="form-control" id="actualAmount" name="amount"
                                    value="" required placeholder="0.00">
                            </div>
                            <div class="col-md-3 mb-3">
                                <!-- <label for="originalTotalBase" class="form-label">Original Total (Base)</label> -->
                                <input type="hidden" readonly class="form-control bg-light" id="originalTotalBase">
                                <input type="hidden" id="grand_total" name="grand_total">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="mobileNumber" class="form-label">Mobile Number</label>
                                <input type="text" class="form-control" id="mobileNumber" name="mobile_number" placeholder="Enter mobile number">
                            </div>

                            <!-- Tax Section -->
                            <div class="mb-3" id="taxSection">
                                <!-- GST Section -->
                                <div class="d-flex justify-content-between mb-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="apply_gst" id="applyGst"
                                            value="1" checked>
                                        <label class="form-check-label" for="applyGst">Apply GST</label>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">GST %</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="gst_percentage"
                                                name="gst_percentage" value="18" min="0" max="100" step="0.01">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">GST Amount</label>
                                        <input type="number" class="form-control" id="gst_amount" name="gst_amount"
                                            readonly>
                                    </div>
                                </div>

                                <!-- TDS Section -->
                                <div class="d-flex justify-content-between mb-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="apply_tds" id="applyTds"
                                            value="1" checked>
                                        <label class="form-check-label" for="applyTds">Apply TDS</label>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">TDS %</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="tds_percentage"
                                                name="tds_percentage" value="10" min="0" max="100" step="0.01">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">TDS Amount</label>
                                        <input type="number" class="form-control" id="tds_amount" name="tds_amount"
                                            readonly>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-5">
                                        <label class="form-label">TDS Status</label>
                                        <select class="form-select" id="addTdsStatus" name="tds_status">
                                            <option value="" selected disabled>Select Status</option>
                                            <option value="received">Received</option>
                                            <option value="not_received">Not Received</option>
                                        </select>
                                    </div>
                                    <div class="col-md-7">
                                        <label class="form-label">Receipt</label>
                                        <input type="file" id="addTdsReceipt" name="tds_receipt" class="form-control"
                                            accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                    </div>
                                </div>

                            </div>

                            <!-- Amount Received Section -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Received Amount</label>
                                    <input type="number" class="form-control" id="received_amount" name="received_amount"
                                        step="0.01" value="0.00">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Payment Date</label>
                                    <input type="date" class="form-control" id="received_date" name="received_date" max="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Balance</label>
                                    <input type="number" class="form-control" id="balance_amount" name="balance_amount"
                                        step="0.01" readonly>
                                </div>
                            </div>


                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select" id="status" name="status" required
                                    onchange="handleStatusChange(this, 'dueDateContainer', 'dueDate')">
                                    <option value="due" selected>Due</option>
                                    <option value="settle">Settle</option>
                                    <!-- <option value="convert_to_tds" id="addConvertToTdsOption">Convert to TDS</option> -->
                                </select>
                            </div>
                            <div class="col-md-6 mb-3" id="addSettleNotesContainer" style="display:none;">
                                <label for="addSettleNotes" class="form-label">Settle Notes <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="addSettleNotes" name="settle_notes" rows="1" placeholder="Enter notes for settled status..."></textarea>
                            </div>
                            <div class="col-md-6 mb-3" id="dueDateContainer">
                                <label for="dueDate" class="form-label">Due Date *</label>
                                <input type="date" class="form-control" id="dueDate" name="due_date"
                                    min="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="mailStatus" class="form-label">Mail Status</label>
                                <select class="form-select" id="mailStatus" name="mail_status">
                                    <option value="1">Yes</option>
                                    <option value="0" selected>No</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="addNotes" name="notes" rows="3" placeholder="Add any additional notes..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save"></i> Save Income
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Edit Income Modal (Separate) -->
    <div class="modal fade" id="editIncomeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Edit Income
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <form id="editIncomeForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" id="editIncomeId" name="id">
                    <input type="hidden" id="editIncomeIsSplit" name="is_split">
                    <input type="hidden" id="editIncomeParentId" name="parent_id">

                    <div class="modal-body">
                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Company</label>
                                <select class="form-select" id="editCompanyId" name="company_id" required>
                                    <option value="">Select Company</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Client Name</label>
                                <input type="text" class="form-control" id="editClientName" name="client_name" required>
                            </div>
                        </div>

                        <!-- Amount Information -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required" id="editBaseAmountLabel">Base Amount (₹)</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="editBaseAmountSymbol">₹</span>
                                    <input type="number" class="form-control" id="editPlannedAmount" name="amount"
                                        step="0.01" min="0" required>
                                    <input type="hidden" class="form-control" id="editOriginalAmount"
                                        name="editOriginalAmount" step="0.01" min="0" required>
                                </div>
                                <div id="incomePlannedBreakdown" class="text-primary"
                                    style="text-transform: none; font-size: 0.85em;"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Paid Amount (₹)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control" id="editPaidAmount" name="received_amount"
                                        step="0.01" min="0">
                                </div>
                            </div>
                            <div class="row">

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Paid Date</label>
                                    <input type="date" class="form-control" id="editPaidDate" name="received_date" max="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Payment Mode</label>
                                    <select class="form-select" id="editPaymentMode" name="payment_mode" required
                                        onchange="togglePaymentModeDetails(this)">
                                        <option value="">Select Mode</option>
                                        <option value="cash">Cash</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="cheque">Cheque</option>
                                        <option value="upi">UPI</option>
                                    </select>
                                </div>

                                <!-- Receipts Upload -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Upload Receipts</label>
                                    <div class="input-group">
                                        <input type="file" class="form-control" id="editReceipts" name="receipts[]" required
                                            multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                        <label class="input-group-text" for="editReceipts">
                                            <i class="fas fa-paperclip"></i>
                                        </label>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- Payment Mode Details -->
                        <div class="row g-3 mb-4 payment-mode-details" style="display: none;">
                            <div class="col-md-4 bank-details" style="display: none;">
                                <label class="form-label fw-bold text-uppercase small text-muted">Bank *</label>
                                <select class="form-select" id="editEditableBankName" name="bank_name">
                                    <option value="">Select Bank</option>
                                    <option value="SBI">SBI</option>
                                    <option value="HDFC">HDFC</option>
                                    <option value="ICICI">ICICI</option>
                                    <option value="Axis">Axis</option>
                                </select>
                            </div>
                            <div class="col-md-4 upi-details" style="display: none;">
                                <label class="form-label fw-bold text-uppercase small text-muted">UPI Type *</label>
                                <select class="form-select" id="editEditableUpiType" name="upi_type">
                                    <option value="GPay">GPay</option>
                                    <option value="PhonePe">PhonePe</option>
                                    <option value="Paytm">Paytm</option>
                                </select>
                            </div>
                            <div class="col-md-4 upi-details" style="display: none;">
                                <label class="form-label fw-bold text-uppercase small text-muted">UPI Phone *</label>
                                <input type="text" class="form-control" id="editEditableUpiNumber" name="upi_number"
                                    placeholder="Number">
                            </div>
                        </div>
                        <!-- Status & Dates -->
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Balance Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control" id="editBalanceAmount" name="balance_amount"
                                        step="0.01" min="0" readonly>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required">Status</label>
                                <select class="form-select" id="editStatus" name="status" required
                                    onchange="handleStatusChange(this, 'editDueDateContainer', 'editDueDate')">
                                    <option value="" disabled>Select Status</option>
                                    <option value="due" selected>Due</option>
                                    <option value="settle">Settle</option>
                                    <!-- <option value="convert_to_tds" id="convertToTdsOption">Convert to TDS</option> -->
                                </select>
                            </div>
                            <div class="col-md-4 mb-3" id="editSettleNotesContainer" style="display:none;">
                                <label class="form-label" for="editSettleNotes">Settle Notes <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="editSettleNotes" name="settle_notes" rows="1"
                                    placeholder="Enter notes for settled status..."></textarea>
                            </div>
                            <div class="col-md-4 mb-3" id="editDueDateContainer">
                                <label class="form-label">Due Date *</label>
                                <input type="date" class="form-control" id="editDueDate" name="due_date"
                                    min="{{ date('Y-m-d') }}">
                            </div>

                        </div>

                        <!-- Tax Information -->
                        <div class="row mb-4" id="editTaxInfoContainer">
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-2 mb-3">Tax Information</h6>
                            </div>
                            <div id="gstSection">
                                <!-- GST Section -->
                                <div class="d-flex justify-content-between mb-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="apply_gst" id="editApplyGst"
                                            value="1" checked>
                                        <label class="form-check-label" for="editApplyGst">Apply GST</label>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">GST %</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="editGstPercentage"
                                                name="gst_percentage" value="18" min="0" max="100" step="0.01">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">GST Amount</label>
                                        <input type="number" class="form-control" id="editGstAmount" name="gst_amount"
                                            readonly>
                                    </div>
                                </div>

                            </div>
                            <div id="tdsSection">

                                <!-- TDS Section -->
                                <div class="d-flex justify-content-between mb-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="apply_tds" id="editApplyTds"
                                            value="1" checked>
                                        <label class="form-check-label" for="editApplyTds">Apply TDS</label>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">TDS %</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="editTdsPercentage"
                                                name="tds_percentage" value="10" min="0" max="100" step="0.01">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">TDS Amount</label>
                                        <input type="number" class="form-control" id="editTdsAmount" name="tds_amount"
                                            readonly>
                                    </div>
                                </div>
                                <div class="row mb-3">

                                    <div class="col-md-6">
                                        <label class="form-label">TDS Status</label>
                                        <select class="form-select" id="editTdsStatus" name="tds_status">
                                            <option value="" selected disabled>Select Status</option>
                                            <option value="received">Received</option>
                                            <option value="not_received">Not Received</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Receipt</label>
                                        <input type="file" id="editTdsReceipt" name="tds_receipt" class="form-control"
                                            accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!-- Vendor Information -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Vendor/Party Name</label>
                                <input type="text" class="form-control" id="editPartyName" name="party_name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mobile Number</label>
                                <input type="tel" class="form-control" id="editMobileNumber" name="mobile_number"
                                    pattern="[0-9]{10}" maxlength="10">
                            </div>
                        </div>


                        <!-- Notes -->
                        <div class="row mb-3">
                            <div class="col-md-12 mb-3">
                                <label class="form-label" for="editNotes">Notes</label>
                                <textarea class="form-control" id="editNotes" name="notes" rows="3"
                                    placeholder="Add any additional notes..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" id="editSubmitBtn">
                            <i class="fas fa-save me-1"></i>Update Income
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Add this modal to your HTML if not present -->
    <div class="modal fade" id="viewInvoiceModal" tabindex="-1" aria-labelledby="viewInvoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewInvoiceModalLabel">Invoice Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="invoiceDetailsContent">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="printInvoice()">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Correct Bootstrap modal structure -->
    <div class="modal fade" id="sendInvoiceModal" tabindex="-1" aria-labelledby="sendInvoiceLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sendInvoiceLabel">Send Invoice via Email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="sendInvoiceForm" method="POST" action="{{ route('income.send-email') }}">
                        @csrf
                        <input type="hidden" name="invoice_id" id="send_invoice_id">
                        <input type="hidden" name="income_id" id="send_income_id">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Invoice Number</label>
                                <input type="text" class="form-control" id="send_invoice_no" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Invoice Type</label>
                                <input type="text" class="form-control" id="send_invoice_type_display" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">To Email *</label>
                                <input type="email" class="form-control" id="send_to_email" name="to_email" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">CC Email (optional)</label>
                                <input type="text" class="form-control" id="send_cc_email" name="cc_email"
                                    placeholder="comma-separated emails">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Subject *</label>
                                <input type="text" class="form-control" id="send_subject" name="subject" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Message Body *</label>
                                <textarea class="form-control" id="send_message" name="message" rows="6"
                                    required></textarea>
                                {{-- <small class="text-muted">
                                    Available variables: {client_name}, {invoice_no}, {due_date}, {amount}, {company_name}
                                </small> --}}
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="attach_pdf" name="attach_pdf"
                                        checked>
                                    <label class="form-check-label" for="attach_pdf">
                                        Attach PDF copy of invoice
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="send_confirm_btn">
                        <i class="fas fa-paper-plane"></i> Send Invoice
                    </button>
                </div>
            </div>
        </div>
    </div>
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
        // Apply filters
        function applyFilters() {
            const companyId = document.getElementById('companyFilter').value;
            const category = document.getElementById('categoryFilter').value;
            const dateRange = document.getElementById('dateRangeFilter').value;
            const status = document.getElementById('statusFilter').value;
            const currency = document.getElementById('currencyFilter').value;

            const url = new URL(window.location.href);

            // Set or remove company filter
            if (companyId) {
                url.searchParams.set('company', companyId);
            } else {
                url.searchParams.delete('company');
            }

            // Set or remove category filter
            if (category && category !== 'all') {
                url.searchParams.set('category', category);
            } else {
                url.searchParams.delete('category');
            }

            // Set date range
            if (dateRange) {
                url.searchParams.set('date_range', dateRange);
            } else {
                url.searchParams.set('date_range', 'month');
            }

            // Set or remove status filter
            if (status && status !== 'all') {
                url.searchParams.set('status', status);
            } else {
                url.searchParams.delete('status');
            }

            // Set or remove currency filter
            if (currency && currency !== 'all') {
                url.searchParams.set('currency', currency);
            } else {
                url.searchParams.delete('currency');
            }

            // Reset to page 1 when filtering
            url.searchParams.delete('page');

            window.location.href = url.toString();
        }

        // Update date range (kept for backward compatibility)
        function updateDateRange() {
            applyFilters();
        }

        // Reset all filters
        function resetFilters() {
            const url = new URL(window.location.href);

            // Remove all query parameters except the base route
            url.search = '';

            window.location.href = url.toString();
        }

        // Filter payments by status
        function filterPayments(status) {
            const url = new URL(window.location.href);

            if (status === 'all') {
                url.searchParams.delete('status');
            } else {
                url.searchParams.set('status', status);
            }

            window.location.href = url.toString();
        }

        // Handle status change to show/hide due date
        function handleStatusChange(selectElement, containerId, inputId) {
            const container = document.getElementById(containerId);
            const input = document.getElementById(inputId);
            const form = selectElement.closest('form');
            const settleNotesEl = form.querySelector('[name="settle_notes"]');
            const settleNotesContainer = document.getElementById(inputId === 'dueDate' ? 'addSettleNotesContainer' : 'editSettleNotesContainer');
            
            if (settleNotesEl && settleNotesContainer) {
                if (selectElement.value === 'settle' || selectElement.value === 'paid') {
                    settleNotesContainer.style.display = 'block';
                    settleNotesEl.required = true;
                    settleNotesEl.setAttribute('required', 'required');
                } else {
                    settleNotesContainer.style.display = 'none';
                    settleNotesEl.required = false;
                    settleNotesEl.removeAttribute('required');
                }
            }

            if (selectElement.value === 'due') {
                container.style.display = 'block';
                input.required = true;
            } else {
                container.style.display = 'none';
                input.required = false;
                input.value = ''; // Clear if not due
            }
        }

        // Open add income modal
        function openAddIncomeModal() {
            document.getElementById('modalTitle').textContent = 'Add Non-standard Income';
            document.getElementById('incomeForm').reset();
            document.getElementById('incomeId').value = '';
            document.getElementById('dueDate').value = '{{ date("Y-m-d") }}';
            document.getElementById('status').value = 'due';
            document.getElementById('mailStatus').value = '0';

            // Trigger status change handler for initial state
            handleStatusChange(document.getElementById('status'), 'dueDateContainer', 'dueDate');

            // Show tax section (hidden for split records in edit)
            const taxSection = document.getElementById('taxSection');
            if (taxSection) taxSection.style.display = 'block';

            const modal = new bootstrap.Modal(document.getElementById('incomeModal'));
            modal.show();
        }

        // Edit income
        // Initialize tax calculation when modal is shown
        const incomeModal = document.getElementById('incomeModal');
        if (incomeModal) {
            // Add event listeners for tax calculation
            const actualAmountInput = document.getElementById('actualAmount');
            const gstCheckbox = document.getElementById('applyGst');
            const tdsCheckbox = document.getElementById('applyTds');
            const gstPercentageInput = document.getElementById('gst_percentage');
            const tdsPercentageInput = document.getElementById('tds_percentage');
            const receivedAmountInput = document.getElementById('received_amount');

            // Function to calculate taxes
            function calculateIncomeTax(event) {
                const isReceivedAmount = event && event.target && (event.target.id === 'received_amount' || event.target.id === 'receivedAmount');

                const actualAmount = parseFloat(actualAmountInput.value) || 0;
                const applyGst = gstCheckbox.checked;
                const applyTds = tdsCheckbox.checked;
                const gstPercentage = parseFloat(gstPercentageInput.value) || 0;
                const tdsPercentage = parseFloat(tdsPercentageInput.value) || 0;
                const receivedAmount = parseFloat(receivedAmountInput.value) || 0;

                let gstAmount = 0;
                let tdsAmount = 0;
                let amountAfterGst = actualAmount;
                let amountAfterTds = actualAmount;
                let grandTotal = actualAmount;

                // Only recalculate taxes if NOT triggered by received_amount input
                if (!isReceivedAmount) {
                    // Calculate GST if checked
                    if (applyGst && gstPercentage > 0) {
                        gstAmount = (actualAmount * gstPercentage) / 100;
                        amountAfterGst = actualAmount + gstAmount;
                        grandTotal = amountAfterGst; // Grand total includes GST
                    }

                    // Calculate TDS if checked
                    if (applyTds && tdsPercentage > 0) {
                        const baseForTds = applyGst ? amountAfterGst : actualAmount;
                        tdsAmount = (baseForTds * tdsPercentage) / 100;
                        amountAfterTds = baseForTds - tdsAmount;
                    }

                    // Update display fields
                    document.getElementById('gst_amount').value = gstAmount.toFixed(2);
                    document.getElementById('tds_amount').value = tdsAmount.toFixed(2);
                } else {
                    // If only received amount changed, get existing values for balance calculation
                    gstAmount = parseFloat(document.getElementById('gst_amount').value) || 0;
                    tdsAmount = parseFloat(document.getElementById('tds_amount').value) || 0;
                    grandTotal = parseFloat(document.getElementById('grand_total').value) || 0;
                }

                // Fixed Balance Calculation: (Grand Total - TDS) - Received Amount
                const isSplitIncome = document.getElementById('editIncomeIsSplit')?.value == '1' || document.getElementById('editIncomeParentId')?.value != '0' && document.getElementById('editIncomeParentId')?.value != '';
                const balance = Math.max(0, (isSplitIncome ? grandTotal : (grandTotal - tdsAmount)) - receivedAmount);
                document.getElementById('balance_amount').value = balance.toFixed(2);

                // Enable/disable percentage inputs based on checkbox state
                gstPercentageInput.disabled = !applyGst;
                tdsPercentageInput.disabled = !applyTds;
            }
            // Add event listeners
            actualAmountInput.addEventListener('input', calculateIncomeTax);
            gstCheckbox.addEventListener('change', calculateIncomeTax);
            tdsCheckbox.addEventListener('change', calculateIncomeTax);
            gstPercentageInput.addEventListener('input', calculateIncomeTax);
            tdsPercentageInput.addEventListener('input', calculateIncomeTax);
            receivedAmountInput.addEventListener('input', calculateIncomeTax);

            // Initialize calculation on modal show
            incomeModal.addEventListener('show.bs.modal', function () {
                // Set default date for due date
                const dueDateInput = document.getElementById('dueDate');
                if (dueDateInput && !dueDateInput.value) {
                    dueDateInput.value = new Date().toISOString().split('T')[0];
                }

                // Set default date for received date
                const receivedDateInput = document.getElementById('received_date');
                if (receivedDateInput && !receivedDateInput.value) {
                    receivedDateInput.value = new Date().toISOString().split('T')[0];
                }

                // Calculate initial tax
                setTimeout(() => {
                    calculateIncomeTax();
                    if (typeof handleTdsStatusBehavior === 'function') {
                        handleTdsStatusBehavior('addTdsStatus', 'addTdsReceipt');
                    }
                }, 100);
            });

            // Also handle the edit function
            window.editIncome = async function (incomeId) {
                try {
                    console.log('Editing income ID:', incomeId);

                    const response = await fetch(
                        `https://xhtmlreviews.in/beta-finance/manager/income/${incomeId}/edit`);
                    const data = await response.json();

                    console.log('API Response:', data);

                    if (data.success) {
                        const income = data.income;
                        console.log('Income data received:', income);

                        // Populate form fields
                        document.getElementById('modalTitle').textContent = 'Edit Income';
                        document.getElementById('incomeId').value = income.id;
                        document.getElementById('companyId').value = income.company_id;
                        document.getElementById('clientName').value = income.client_name;
                        document.getElementById('actualAmount').value = income.actual_amount_base || income.actual_amount || income.planned_amount;
                        document.getElementById('originalTotalBase').value = income.original_total_base || income.actual_amount;
                        document.getElementById('mobileNumber').value = income.mobile_number || '';
                        document.getElementById('dueDate').value = income.due_date || income.income_date || '';
                        const statusVal = (income.status === 'received' || income.status === 'settle') ? 'settle' : 'due';
                        document.getElementById('status').value = statusVal;
                        document.getElementById('mailStatus').value = income.mail_status ? '1' : '0';

                        // Trigger status change handler for initial state
                        handleStatusChange(document.getElementById('status'), 'dueDateContainer', 'dueDate');

                        const taxSection = document.getElementById('taxSection');
                        if (taxSection) {
                            taxSection.style.display = 'block';
                        }

                        // Store split info
                        document.getElementById('editIncomeIsSplit').value = income.is_split ? 1 : 0;
                        document.getElementById('editIncomeParentId').value = income.parent_id || 0;

                        // Debug log for tax values
                        console.log('Tax values from API:', {
                            gst_amount: income.gst_amount,
                            tds_amount: income.tds_amount,
                            gst_percentage: income.gst_percentage,
                            tds_percentage: income.tds_percentage
                        });

                        // Determine if GST/TDS should be checked
                        const hasGst = income.gstTax !== null || parseFloat(income.gst_amount) > 0 || parseFloat(income.gst_percentage) > 0;
                        const hasTds = income.tdsTax !== null || parseFloat(income.tds_amount) > 0 || parseFloat(income.tds_percentage) > 0;

                        // Show/hide "Convert to TDS" option in add/edit modal
                        const addConvertToTdsOption = document.getElementById('addConvertToTdsOption');
                        if (addConvertToTdsOption) {
                            addConvertToTdsOption.style.display = hasTds ? 'none' : 'block';
                        }

                        console.log('Checkbox states:', {
                            hasGst,
                            hasTds
                        });

                        // Set checkbox states
                        document.getElementById('applyGst').checked = hasGst;
                        document.getElementById('applyTds').checked = hasTds;

                        // Set percentage values
                        document.getElementById('gst_percentage').value = income.gst_percentage || 18;
                        document.getElementById('tds_percentage').value = income.tds_percentage || 10;

                        // Set amount values
                        document.getElementById('gst_amount').value = income.gst_amount || 0;
                        document.getElementById('tds_amount').value = income.tds_amount || 0;
                        document.getElementById('amount_after_tds').value = income.amount_after_tds || income
                            .actual_amount;
                        document.getElementById('grand_total').value = income.grand_total || income.actual_amount;
                        document.getElementById('addTdsStatus').value = income.tds_status || 'not_received';

                        // Received amounts
                        let defaultReceivedAmount = income.received_amount || 0;
                        if (income.is_split || income.parent_id) {
                            defaultReceivedAmount = parseFloat(income.planned_amount) || parseFloat(income.actual_amount) || defaultReceivedAmount;
                        }
                        document.getElementById('received_amount').value = defaultReceivedAmount;
                        document.getElementById('received_date').value = income.received_date || '';
                        document.getElementById('balance_amount').value = income.balance_amount || 0;

                        // Enable/disable percentage inputs based on checkbox state
                        document.getElementById('gst_percentage').disabled = !hasGst;
                        document.getElementById('tds_percentage').disabled = !hasTds;

                        // Show the modal
                        const modal = new bootstrap.Modal(document.getElementById('incomeModal'));
                        modal.show();

                        // Recalculate to ensure all fields are updated
                        setTimeout(() => {
                            console.log('Recalculating taxes after form population...');
                            calculateIncomeTax();
                        }, 500);
                    } else {
                        alert(data.message || 'Error loading income data');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error loading income data');
                }
            }

        }
        // Update the form submission handler
        document.getElementById('incomeForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            const incomeId = document.getElementById('incomeId').value;
            const url = incomeId ?
                `https://xhtmlreviews.in/beta-finance/manager/income/${incomeId}` :
                'https://xhtmlreviews.in/beta-finance/manager/income';

            // Add method spoofing for PUT
            if (incomeId) {
                formData.append('_method', 'PUT');
            }

            // Ensure checkbox values are properly set
            const checkboxes = ['apply_gst', 'apply_tds', 'mail_status'];
            checkboxes.forEach(name => {
                const checkbox = document.querySelector(`[name="${name}"]`);
                if (checkbox) {
                    formData.set(name, checkbox.checked ? '1' : '0');
                }
            });

            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            submitBtn.disabled = true;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .getAttribute(
                                'content')
                    }
                });

                const data = await response.json();

                if (data.success) {
                    // Show success message
                    showNotification('success', data.message);

                    // Hide modal
                    $('#incomeModal').modal('hide');

                    // Reload page after a short delay
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    // Show error message
                    showNotification('error', data.message || 'Error saving income');

                    // Reset button
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;

                    // Show validation errors if any
                    if (data.errors) {
                        console.error('Validation errors:', data.errors);
                        // Clear previous errors
                        document.querySelectorAll('.is-invalid').forEach(el => {
                            el.classList.remove('is-invalid');
                        });
                        document.querySelectorAll('.invalid-feedback').forEach(el => {
                            el.remove();
                        });

                        // Add new errors
                        Object.keys(data.errors).forEach(fieldName => {
                            const field = document.querySelector(`[name="${fieldName}"]`);
                            if (field) {
                                field.classList.add('is-invalid');
                                const errorElement = document.createElement('div');
                                errorElement.className = 'invalid-feedback';
                                errorElement.textContent = data.errors[fieldName][0];
                                field.parentNode.appendChild(errorElement);
                            }
                        });
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('error', 'Error saving income: ' + error.message);

                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
        // Open receive payment modal
        async function openReceivePaymentModal(incomeId) {
            try {
                const response = await fetch(
                    `https://xhtmlreviews.in/beta-finance/manager/income/${incomeId}/details`);
                const data = await response.json();

                if (data.success) {
                    const income = data.income;

                    // Populate modal fields
                    document.getElementById('receiveIncomeId').value = income.id;
                    document.getElementById('originalAmount').value = income.planned_amount || income
                        .amount;
                    document.getElementById('invoiceNoDisplay').textContent = income.invoice_no || 'N/A';
                    document.getElementById('companyDisplay').textContent = income.company?.name || 'N/A';
                    document.getElementById('clientDisplay').textContent = income.client_name || 'N/A';
                    document.getElementById('originalAmountDisplay').textContent = '₹' + (income
                        .planned_amount ||
                        income.amount);

                    // Set payment date to today
                    const today = new Date().toISOString().split('T')[0];
                    document.getElementById('paymentDate').value = today;

                    // Set new due date to 30 days from today
                    const futureDate = new Date();
                    futureDate.setDate(futureDate.getDate() + 30);
                    document.getElementById('newDueDate').value = futureDate.toISOString().split('T')[0];

                    // Reset received amount
                    document.getElementById('receivedAmount').value = '';
                    document.getElementById('balanceAmountDisplay').textContent = '0.00';

                    // Show modal
                    const modal = new bootstrap.Modal(document.getElementById('receivePaymentModal'));
                    modal.show();

                    // Initialize event listeners after modal is shown
                    initializeReceivePaymentListeners();
                } else {
                    alert(data.message || 'Error loading income details');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error loading income details');
            }
        }

        // Initialize event listeners for receive payment modal
        function initializeReceivePaymentListeners() {
            const receivedAmountInput = document.getElementById('receivedAmount');
            const originalAmount = parseFloat(document.getElementById('originalAmount').value) || 0;

            // Remove any existing listeners first
            const newReceivedAmountInput = receivedAmountInput.cloneNode(true);
            receivedAmountInput.parentNode.replaceChild(newReceivedAmountInput, receivedAmountInput);

            // Add new event listener
            document.getElementById('receivedAmount').addEventListener('input', function (e) {
                const originalAmount = parseFloat(document.getElementById('originalAmount').value) || 0;
                const receivedAmount = parseFloat(this.value) || 0;
                const balance = Math.max(0, originalAmount - receivedAmount);

                console.log('Original Amount:', originalAmount);
                console.log('Received Amount:', receivedAmount);
                console.log('Balance:', balance);

                document.getElementById('balanceAmountDisplay').textContent = balance.toFixed(2);

                // Validate that received amount is less than original amount
                if (receivedAmount >= originalAmount) {
                    this.classList.add('is-invalid');
                    document.getElementById('newProformaSection').style.display = 'none';
                } else {
                    this.classList.remove('is-invalid');
                    document.getElementById('newProformaSection').style.display = 'block';
                }
            });

            // Toggle new proforma section
            const createNewProforma = document.getElementById('createNewProforma');
            if (createNewProforma) {
                const newCreateNewProforma = createNewProforma.cloneNode(true);
                createNewProforma.parentNode.replaceChild(newCreateNewProforma, createNewProforma);

                document.getElementById('createNewProforma').addEventListener('change', function () {
                    document.getElementById('newProformaSection').style.display = this.checked ?
                        'block' : 'none';
                    if (!this.checked) {
                        document.getElementById('newDueDate').removeAttribute('required');
                    } else {
                        document.getElementById('newDueDate').setAttribute('required', 'required');
                    }
                });
            }
        }

        // Handle receive payment form submission
        document.getElementById('receivePaymentForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const originalAmount = parseFloat(document.getElementById('originalAmount').value) || 0;
            const receivedAmount = parseFloat(document.getElementById('receivedAmount').value);

            console.log('Form Submit - Original:', originalAmount);
            console.log('Form Submit - Received:', receivedAmount);

            // Validate received amount
            if (receivedAmount <= 0) {
                alert('Please enter a valid amount received');
                return;
            }

            if (receivedAmount >= originalAmount) {
                alert('Received amount must be less than the original scheduled amount');
                return;
            }

            if (!confirm(
                'Are you sure you want to record this partial payment? This action cannot be undone.'
            )) {
                return;
            }

            const formData = new FormData(this);
            const incomeId = document.getElementById('receiveIncomeId').value;

            // Convert checkbox value to boolean (1/0)
            const createNewProforma = document.getElementById('createNewProforma').checked ? 1 : 0;
            formData.set('create_new_proforma', createNewProforma); // Override the string value

            fetch(`https://xhtmlreviews.in/beta-finance/manager/income/${incomeId}/receive-payment`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                        .getAttribute(
                            'content')
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        $('#receivePaymentModal').modal('hide');
                        location.reload();
                    } else {
                        alert(data.message || 'Error recording payment');
                        // Show validation errors if any
                        if (data.errors) {
                            console.error('Validation errors:', data.errors);
                            Object.keys(data.errors).forEach(fieldName => {
                                const field = document.querySelector(
                                    `[name="${fieldName}"]`);
                                if (field) {
                                    field.classList.add('is-invalid');
                                    const errorElement = document.createElement('div');
                                    errorElement.className = 'invalid-feedback';
                                    errorElement.textContent = data.errors[fieldName][0];
                                    field.parentNode.appendChild(errorElement);
                                }
                            });
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error recording payment');
                });
        });
        // Handle income form submission

        // Helper function to show notifications
        function showNotification(type, message) {
            // Remove existing notifications
            const existingNotifications = document.querySelectorAll('.custom-notification');
            existingNotifications.forEach(notification => notification.remove());

            // Create notification element
            const notification = document.createElement('div');
            notification.className =
                `custom-notification alert alert-${type === 'success' ? 'success' : 'danger'}`;
            notification.style.cssText = `
                                                                            position: fixed;
                                                                            top: 20px;
                                                                            right: 20px;
                                                                            z-index: 9999;
                                                                            padding: 15px 20px;
                                                                            border-radius: 5px;
                                                                            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                                                                            min-width: 300px;
                                                                            max-width: 400px;
                                                                        `;
            notification.innerHTML = `
                                                                            <div class="d-flex justify-content-between align-items-center">
                                                                                <span>${message}</span>
                                                                                <button type="button" class="btn-close" onclick="this.parentElement.parentElement.remove()"></button>
                                                                            </div>
                                                                        `;

            document.body.appendChild(notification);

            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Ensure checkboxes send proper values
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function () {
                    if (this.checked) {
                        this.value = '1';
                    } else {
                        this.value = '0';
                    }
                });
            });
        });
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function () {
            console.log('Page loaded, income module initialized');
        });
    </script>

    <script>
        function calculateTax(event) {
            try {
                // Check if triggered by received_amount to avoid resetting taxes
                const isReceivedAmount = event && event.target && event.target.id === 'received_amount';

                if (!isReceivedAmount) {
                    // Get base amount
                    const baseAmount = parseFloat(document.getElementById('actualAmount').value) || 0;

                    // Check if taxes should be applied
                    const applyGst = document.getElementById('applyGst')?.checked || false;
                    const applyTds = document.getElementById('applyTds')?.checked || false;

                    // Get tax percentages
                    const gstPercentage = applyGst ? (parseFloat(document.getElementById('gst_percentage').value) || 0) : 0;
                    const tdsPercentage = applyTds ? (parseFloat(document.getElementById('tds_percentage').value) || 0) : 0;

                    // Calculate amounts
                    const gstAmount = (baseAmount * gstPercentage) / 100;
                    const tdsAmount = (baseAmount * tdsPercentage) / 100;
                    const grandTotal = baseAmount + gstAmount;

                    // Update fields
                    const gstAmountField = document.getElementById('gst_amount');
                    const tdsAmountField = document.getElementById('tds_amount');
                    const grandTotalField = document.getElementById('grand_total');

                    if (gstAmountField) gstAmountField.value = gstAmount.toFixed(2);
                    if (tdsAmountField) tdsAmountField.value = tdsAmount.toFixed(2);
                    if (grandTotalField) grandTotalField.value = grandTotal.toFixed(2);
                    
                    // Hide amount_after_tds field if it exists
                    const amountAfterTDSField = document.getElementById('amount_after_tds');
                    if (amountAfterTDSField && amountAfterTDSField.parentElement) {
                        amountAfterTDSField.parentElement.style.display = 'none';
                    }
                }

                // Recalculate balance
                calculateBalance();

            } catch (error) {
                console.error('Error in calculateTax:', error);
            }
        }

        function calculateBalance() {
            try {
                console.log('Calculating balance...');

                const grandTotalField = document.getElementById('grand_total');
                const receivedAmountField = document.getElementById('received_amount');
                const tdsAmountField = document.getElementById('tds_amount');
                const balanceField = document.getElementById('balance_amount');

                if (!grandTotalField || !receivedAmountField || !balanceField) {
                    console.error('Required fields not found');
                    return;
                }

                const grandTotal = parseFloat(grandTotalField.value) || 0;
                const receivedAmount = parseFloat(receivedAmountField.value) || 0;
                const tdsAmount = parseFloat(tdsAmountField ? tdsAmountField.value : 0) || 0;

                // Fixed logic: balance = (Grand Total - tds amount) - paid amount
                const balance = (grandTotal - tdsAmount) - receivedAmount;
                const balanceVal = Math.max(0, balance);

                console.log('Grand Total:', grandTotal, 'TDS:', tdsAmount, 'Received:', receivedAmount, 'Balance:', balanceVal);

                balanceField.value = balanceVal.toFixed(2);

                const statusSelect = document.getElementById('status');
                if (statusSelect) {
                    const statusContainer = statusSelect.closest('div[class^="col-"]');
                    if (balanceVal <= 0.01) {
                        if (statusContainer) statusContainer.style.display = 'none';
                        statusSelect.value = 'settle';
                        handleStatusChange(statusSelect, 'dueDateContainer', 'dueDate');
                    } else {
                        if (statusContainer) statusContainer.style.display = 'block';
                    }
                }

            } catch (error) {
                console.error('Error in calculateBalance:', error);
            }
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function () {
            console.log('DOM loaded, initializing tax calculator...');

            // Add event listeners to all calculation inputs
            const calcInputs = ['actualAmount', 'gst_percentage', 'tds_percentage', 'received_amount'];

            calcInputs.forEach(id => {
                const input = document.getElementById(id);
                if (input) {
                    console.log('Adding listener to:', id);
                    input.addEventListener('input', function () {
                        if (id === 'received_amount') {
                            // When Amount Received changes, ONLY calculate balance
                            calculateBalance();
                        } else {
                            // When other fields change, calculate tax and then balance
                            calculateTax();
                        }
                    });
                } else {
                    console.warn('Input not found:', id);
                }
            });

            // Add event listeners for checkboxes
            const gstCheckbox = document.getElementById('applyGst');
            const tdsCheckbox = document.getElementById('applyTds');

            if (gstCheckbox) {
                console.log('Adding GST checkbox listener');
                gstCheckbox.addEventListener('change', function () {
                    const gstPercentageInput = document.getElementById('gst_percentage');
                    if (gstPercentageInput) {
                        gstPercentageInput.disabled = !this.checked;
                    }
                    calculateTax();
                });
            }

            if (tdsCheckbox) {
                console.log('Adding TDS checkbox listener');
                tdsCheckbox.addEventListener('change', function () {
                    const tdsPercentageInput = document.getElementById('tds_percentage');
                    if (tdsPercentageInput) {
                        tdsPercentageInput.disabled = !this.checked;
                    }
                    calculateTax();
                    handleTdsStatusBehavior('addTdsStatus', 'addTdsReceipt');
                });
            }

            // Enable/disable tax percentage inputs based on initial checkbox state
            if (gstCheckbox && document.getElementById('gst_percentage')) {
                document.getElementById('gst_percentage').disabled = !gstCheckbox.checked;
            }

            if (tdsCheckbox && document.getElementById('tds_percentage')) {
                document.getElementById('tds_percentage').disabled = !tdsCheckbox.checked;
            }

            // Initial calculation
            console.log('Running initial calculation...');
            calculateTax();

            // Add listener for TDS Status in Add Modal
            const addTdsStatus = document.getElementById('addTdsStatus');
            if (addTdsStatus) {
                addTdsStatus.addEventListener('change', function () {
                    handleTdsStatusBehavior('addTdsStatus', 'addTdsReceipt');
                });
            }
        });

        function handleStatusBehavior(modalType) {
            let statusId, balanceId, dueDateId;

            if (modalType === 'income-edit') {
                statusId = 'editStatus';
                balanceId = 'editBalanceAmount';
                dueDateId = 'editDueDate';
            }

            const statusEl = document.getElementById(statusId);
            const balanceEl = document.getElementById(balanceId);
            const dueDateEl = document.getElementById(dueDateId);

            if (!statusEl) return;

            const status = statusEl.value;
            let balance = 0;
            if (balanceEl) {
                balance = parseFloat(balanceEl.value) || 0;
            }

            if (status === 'settle') {
                if (balanceEl) {
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
                }
            } else if (status === 'due' || status === 'pending' || status === 'overdue') {
                if (dueDateEl) {
                    dueDateEl.disabled = false;
                    if (balance > 0) {
                        dueDateEl.required = true;
                    } else {
                        dueDateEl.required = false;
                    }
                }
            } else {
                if (dueDateEl) {
                    dueDateEl.disabled = false;
                    dueDateEl.required = false;
                }
            }
        }

        function handleTdsStatusBehavior(statusId, fileId) {
            const statusEl = document.getElementById(statusId);
            const fileEl = document.getElementById(fileId);

            if (!statusEl || !fileEl) return;

            // Find if this is within a hidden section (e.g. TDS section)
            const section = fileEl.closest('.row')?.parentElement?.closest('[id$="Section"]');
            const isHidden = section && section.style.display === 'none';

            const status = statusEl.value;
            // For income, 'received' or 'paid' are the mandatory statuses
            // But only if the section itself is not hidden
            if (!isHidden && (status === 'received' || status === 'paid')) {
                fileEl.required = true;
                fileEl.setAttribute('required', 'required');

                // Add asterisk to label
                const label = fileEl.closest('div').querySelector('label');
                if (label && !label.querySelector('.text-danger')) {
                    label.innerHTML += ' <span class="text-danger">*</span>';
                }
            } else {
                fileEl.required = false;
                fileEl.removeAttribute('required');

                // Remove asterisk from label
                const label = fileEl.closest('div').querySelector('label');
                if (label) {
                    const asterisk = label.querySelector('.text-danger');
                    if (asterisk) asterisk.remove();
                }
            }
        }
    </script>

    <script>
        async function openEditIncomeModal(incomeId) {
            try {
                console.log('Opening edit modal for income ID:', incomeId);

                const response = await fetch(
                    `https://xhtmlreviews.in/beta-finance/manager/income/${incomeId}/edit`
                );
                const data = await response.json();

                if (data.success && data.income) {
                    const income = data.income;
                    console.log('Income data loaded:', income);
                    console.log('Has invoice_id?', income.invoice_id);

                    // Clear any existing lock icons from previous edits
                    document.querySelectorAll('#editIncomeModal .fa-lock').forEach(el => {
                        const small = el.closest('small');
                        if (small) small.remove();
                    });

                    // Populate basic form fields
                    document.getElementById('editIncomeId').value = income.id;
                    document.getElementById('editIncomeIsSplit').value = income.is_split ? 1 : 0;
                    document.getElementById('editIncomeParentId').value = income.parent_id || 0;
                    document.getElementById('editCompanyId').value = income.company_id || '';
                    document.getElementById('editClientName').value = income.client_name || '';

                    // Get currency first behavior
                    const isUSD = (income.currency === 'USD');

                    // Standard income logic: Gross = Subtotal + GST
                    // Non-standard logic: Net = Subtotal + GST - TDS, so Gross = Net + TDS
                    const netAmount = parseFloat(income.amount || 0);
                    const tdsAmountVal = parseFloat(income.tds_amount || 0);
                    const grossAmount = netAmount + tdsAmountVal;

                    const isSplitPayment = income.is_split == 1 || (income.parent_id && income.parent_id != 0);

                    // For foreign currency, the base amount input should be in INR
                    const isForeignCurrency = (income.currency !== 'INR');
                    
                    if (isForeignCurrency) {
                        // The amount in INR before any received
                        document.getElementById('editPlannedAmount').value = parseFloat(income.planned_amount || income.amount || 0).toFixed(2);
                    } else if (income.actual_amount) {
                        document.getElementById('editPlannedAmount').value = parseFloat(income.actual_amount).toFixed(2);
                    } else if (isSplitPayment) {
                        document.getElementById('editPlannedAmount').value = netAmount.toFixed(2);
                    } else {
                        document.getElementById('editPlannedAmount').value = grossAmount.toFixed(2);
                    }

                    document.getElementById('editPaidAmount').value = income.received_amount || income.paid_amount || (isSplitPayment ? netAmount.toFixed(2) : (grossAmount.toFixed(2) - tdsAmountVal));
                    document.getElementById('editOriginalAmount').value = income.actual_amount || 0;
                    document.getElementById('editOriginalAmount').dataset.originalTotal = income.amount || 0;
                    document.getElementById('editOriginalAmount').dataset.originalBase = document.getElementById('editPlannedAmount').value;
                    document.getElementById('editOriginalAmount').dataset.conversionCost = income.conversion_cost || 0;

                    // Format dates
                    if (income.paid_date) {
                        document.getElementById('editPaidDate').value = formatDateForInput(income.paid_date);
                    }
                    if (income.due_date) {
                        document.getElementById('editDueDate').value = formatDateForInput(income.due_date);
                    }

                    // Determine if it's standard income (has invoice_id)
                    const isStandardIncome = income.invoice_id && income.invoice_id > 0;

                    // Update Base Amount Currency Labels (revert to INR for the input, show breakdown for foreign)
                    const currencySymbols = { 'USD': '$', 'EUR': '€', 'GBP': '£', 'INR': '₹' };
                    const curSymbol = currencySymbols[income.currency] || '₹';
                    
                    const baseAmountLabel = document.getElementById('editBaseAmountLabel');
                    if (baseAmountLabel) {
                        baseAmountLabel.innerHTML = `Base Amount (₹)`;
                    }
                    const baseAmountSymbol = document.getElementById('editBaseAmountSymbol');
                    if (baseAmountSymbol) {
                        baseAmountSymbol.textContent = '₹';
                    }
                    
                    const breakdownEl = document.getElementById('incomePlannedBreakdown');
                    if (breakdownEl) {
                        if (isForeignCurrency) {
                            const actualAmt = parseFloat(income.actual_amount || 0).toFixed(2);
                            const convCost = parseFloat(income.conversion_cost || 0).toFixed(2);
                            const plannedAmt = parseFloat(income.planned_amount || (parseFloat(income.amount || 0) + parseFloat(income.conversion_cost || 0)) || 0).toFixed(2);
                            const netAmt = parseFloat(income.amount || 0).toFixed(2);
                            const foreignStr = `Base: ${curSymbol}${actualAmt} = ₹${plannedAmt} - Conversion Cost: ₹${convCost} = ₹${netAmt}`;
                            breakdownEl.dataset.foreignHtml = foreignStr;
                            breakdownEl.innerHTML = foreignStr;
                        } else {
                            delete breakdownEl.dataset.foreignHtml;
                            breakdownEl.innerHTML = '';
                        }
                    }

                    // Make base amount readonly for standard income
                    const plannedAmountInput = document.getElementById('editPlannedAmount');
                    if (plannedAmountInput) {
                        plannedAmountInput.readOnly = isStandardIncome;
                        if (isStandardIncome) {
                            plannedAmountInput.classList.add('bg-light');
                        } else {
                            plannedAmountInput.classList.remove('bg-light');
                        }
                    }

                    // Calculate and set balance for ALL incomes (Gross - TDS - Paid)
                    const paid = parseFloat(income.received_amount || income.paid_amount || netAmount);
                    const tdsAmount = parseFloat(income.tds_amount || 0);
                    const balance = netAmount - paid;

                    document.getElementById('editBalanceAmount').value = Math.max(0, balance).toFixed(2);
                    let statusVal = (income.status === 'received' || income.status === 'settle') ? 'settle' : 'due';
                    document.getElementById('editStatus').value = statusVal;
                    document.getElementById('editPaymentMode').value = income.payment_mode || '';
                    document.getElementById('editPartyName').value = income.party_name || '';
                    document.getElementById('editMobileNumber').value = income.mobile_number || '';
                    document.getElementById('editNotes').value = income.notes || '';
                    document.getElementById('editSettleNotes').value = income.settle_notes || '';

                    // Trigger status change handler for initial state
                    handleStatusChange(document.getElementById('editStatus'), 'editDueDateContainer', 'editDueDate');

                    // Get tax section elements
                    const gstSection = document.querySelector('#gstSection');
                    const tdsSection = document.querySelector('#tdsSection');
                    const gstCheckbox = document.getElementById('editApplyGst');
                    const tdsCheckbox = document.getElementById('editApplyTds');
                    const gstPercentageInput = document.getElementById('editGstPercentage');
                    const tdsPercentageInput = document.getElementById('editTdsPercentage');
                    const gstAmountInput = document.getElementById('editGstAmount');
                    const tdsAmountInput = document.getElementById('editTdsAmount');

                    console.log('Checkbox states:', {
                        hasGst: parseFloat(income.gst_amount) > 0 || parseFloat(income.gst_percentage) > 0,
                        hasTds: parseFloat(income.tds_amount) > 0 || parseFloat(income.tds_percentage) > 0
                    });

                    // GST handling
                    const hasGst = parseFloat(income.gst_amount) > 0 || parseFloat(income.gst_percentage) > 0;
                    // TDS handling
                    const hasTds = income.tdsTax !== null || parseFloat(income.tds_amount) > 0 || parseFloat(income.tds_percentage) > 0;

                    // Toggle whole tax block based on currency (hide for USD) or if standard and no taxes
                    const editTaxInfoContainer = document.getElementById('editTaxInfoContainer');
                    if (editTaxInfoContainer) {
                        // Hide for USD, or standard without taxes
                        if (income.currency === 'USD' || (isStandardIncome && !hasGst && !hasTds)) {
                            editTaxInfoContainer.style.display = 'none';
                        } else {
                            editTaxInfoContainer.style.display = 'flex';
                        }
                    }

                    if (gstCheckbox) {
                        gstCheckbox.checked = hasGst;
                        gstCheckbox.disabled = isStandardIncome;
                    }
                    if (gstPercentageInput) {
                        gstPercentageInput.value = income.gst_percentage || 18;
                        gstPercentageInput.disabled = isStandardIncome || !hasGst;
                    }
                    if (gstAmountInput) {
                        gstAmountInput.value = income.gst_amount || 0;
                    }

                    // Show/hide "Convert to TDS" option
                    const convertToTdsOption = document.getElementById('convertToTdsOption');
                    if (convertToTdsOption) {
                        convertToTdsOption.style.display = hasTds ? 'none' : 'block';
                    }

                    if (tdsCheckbox) {
                        tdsCheckbox.checked = hasTds;
                        tdsCheckbox.disabled = isStandardIncome;
                    }
                    if (tdsPercentageInput) {
                        tdsPercentageInput.value = income.tds_percentage || 10;
                        tdsPercentageInput.disabled = isStandardIncome || !hasTds;
                    }
                    if (tdsAmountInput) {
                        tdsAmountInput.value = income.tds_amount || 0;
                    }

                    // TDS status field
                    const tdsStatusSelect = document.getElementById('editTdsStatus');
                    if (tdsStatusSelect) {
                        tdsStatusSelect.value = income.tds_status || 'not_received';
                        tdsStatusSelect.disabled = false;
                        // if (isStandardIncome) tdsStatusSelect.style.pointerEvents = 'none';
                    }

                    // Show/hide entire tax sections based on checkbox state
                    if (isStandardIncome) {
                        // For standard income, always show GST/TDS sections if they have values
                        if (gstSection) {
                            gstSection.style.display = hasGst ? 'block' : 'none';
                        }
                        if (tdsSection) {
                            tdsSection.style.display = hasTds ? 'block' : 'none';
                        }

                        // Add labels to indicate they're read-only
                        if (hasGst && gstSection) {
                            const gstLabel = gstSection.querySelector('.form-check-label');
                            if (gstLabel && !gstLabel.querySelector('.fa-lock')) {
                                const readOnlyLabel = document.createElement('small');
                                readOnlyLabel.className = 'text-muted ms-2';
                                readOnlyLabel.innerHTML = '<i class="fas fa-lock"></i>';
                                gstLabel.appendChild(readOnlyLabel);
                            }
                        }

                        if (hasTds && tdsSection) {
                            const tdsLabel = tdsSection.querySelector('.form-check-label');
                            if (tdsLabel && !tdsLabel.querySelector('.fa-lock')) {
                                const readOnlyLabel = document.createElement('small');
                                readOnlyLabel.className = 'text-muted ms-2';
                                readOnlyLabel.innerHTML = '<i class="fas fa-lock"></i>';
                                tdsLabel.appendChild(readOnlyLabel);
                            }
                        }
                    }
                    console.log('Initializing tax calculation');
                    initializeEditTaxAndBalance();
                    if (typeof window.recalculateEditIncome === 'function') {
                        window.recalculateEditIncome();
                    }
                    const modal = new bootstrap.Modal(document.getElementById('editIncomeModal'));
                    modal.show();

                    // Initial behavior call
                    setTimeout(() => {
                        handleStatusBehavior('income-edit');
                        handleTdsStatusBehavior('editTdsStatus', 'editTdsReceipt');
                    }, 500);

                    // Add listeners for status changes in edit modal
                    const editStatus = document.getElementById('editStatus');
                    if (editStatus) {
                        editStatus.addEventListener('change', function () {
                            handleStatusBehavior('income-edit');
                            // If switching to 'due', recalculate to restore balance
                            if (this.value !== 'settle') {
                                if (typeof window.recalculateEditIncome === 'function') {
                                    window.recalculateEditIncome();
                                }
                            }
                        });
                    }

                    const editTdsStatus = document.getElementById('editTdsStatus');
                    if (editTdsStatus) {
                        editTdsStatus.addEventListener('change', function () {
                            handleTdsStatusBehavior('editTdsStatus', 'editTdsReceipt');
                        });
                    }

                    const editBalanceAmount = document.getElementById('editBalanceAmount');
                    if (editBalanceAmount) {
                        // We might need to watch for changes if it's updated via calculation
                        const observer = new MutationObserver(() => handleStatusBehavior('income-edit'));
                        observer.observe(editBalanceAmount, {
                            attributes: true,
                            attributeFilter: ['value']
                        });
                        // Also hook into the calculation function
                    }

                } else {
                    alert(data.message || 'Error loading income data');
                }
            } catch (error) {
                console.error('Error loading income:', error);
                alert('Error loading income data');
            }
        }

        // Removed redundant setupStandardIncomeBalanceCalculation
        async function viewSplitHistory(expenseId) {
            try {
                const response = await fetch(
                    `https://xhtmlreviews.in/beta-finance/manager/income/${expenseId}/split-history`);
                const data = await response.json();

                const splitHistoryContent = document.getElementById('splitHistoryContent');
                splitHistoryContent.innerHTML = '';

                if (data.success && (data.parent_expense || data.children.length > 0)) {
                    document.getElementById('noSplitHistory').style.display = 'none';

                    const isUSD = data.currency === 'USD';

                    let historyHTML = '';

                    // Show parent expense if this is a child
                    if (data.parent_expense) {
                        historyHTML += `
                            <h6 class="mb-3">Original Income (Parent)</h6>
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered text-center">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Base amount</th>
                                            ${!isUSD ? `
                                            <th>gst (${parseFloat(data.parent_expense.gst_percentage || 0)}%)</th>
                                            <th>tds (${parseFloat(data.parent_expense.tds_percentage || 0)}%)</th>
                                            ` : ''}
                                            <th>payable</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>₹${parseFloat(data.parent_expense.original_total || 0).toFixed(2)}</td>
                                            ${!isUSD ? `
                                            <td>₹${parseFloat(data.parent_expense.gst_amount || 0).toFixed(2)}</td>
                                            <td>₹${parseFloat(data.parent_expense.tds_amount || 0).toFixed(2)}</td>
                                            ` : ''}
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
                                                <th>Income ID</th>
                                                <th>Amount</th>
                                                ${!isUSD ? `
                                                <th>GST Amount</th>
                                                <th>TDS Amount</th>
                                                ` : ''}
                                                <th>Status</th>
                                                <th>Created Date</th>
                                                <th>Due Date</th>
                                                <th>Paid Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                            `;

                        data.children.forEach((child, index) => {
                            const statusClass = {
                                'paid': 'success',
                                'received': 'success',
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
                                        ${!isUSD ? `
                                        <td>₹${parseFloat(child.gst_amount || 0).toFixed(2)}</td>
                                        <td>₹${parseFloat(child.tds_amount || 0).toFixed(2)}</td>
                                        ` : ''}
                                        <td>
                                            <span class="badge bg-${statusClass}">
                                                ${child.status}
                                            </span>
                                        </td>
                                        <td>${new Date(child.created_at).toLocaleDateString()}</td>
                                        <td>${child.due_date ? new Date(child.due_date).toLocaleDateString() : '-'}</td>
                                        <td>${child.paid_date!='N/A'&& child.paid_date ? new Date(child.paid_date).toLocaleDateString() : '-'}</td>
                                    </tr>
                                `;
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
                                            ${!isUSD ? `<th>Tds Bal. Amount</th>` : ''}
                                            <th>Split Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>₹${parseFloat(data.summary.original_amount || 0).toFixed(2)}</td>
                                            <td class="text-success">₹${parseFloat(data.summary.total_paid || 0).toFixed(2)}</td>
                                            <td class="text-warning">₹${parseFloat(data.summary.total_balance || 0).toFixed(2)}</td>
                                            ${!isUSD ? `<td>₹${parseFloat(data.summary.tds_balance || 0).toFixed(2)}</td>` : ''}
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

        let editIncomeListenersInitialized = false;

        function initializeEditTaxAndBalance() {
            if (editIncomeListenersInitialized) return;
            const plannedAmountInput = document.getElementById('editPlannedAmount');
            const paidAmountInput = document.getElementById('editPaidAmount');
            const gstCheckbox = document.getElementById('editApplyGst');
            const tdsCheckbox = document.getElementById('editApplyTds');
            const gstPercentageInput = document.getElementById('editGstPercentage');
            const tdsPercentageInput = document.getElementById('editTdsPercentage');
            const gstAmountInput = document.getElementById('editGstAmount');
            const tdsAmountInput = document.getElementById('editTdsAmount');
            const balanceAmountInput = document.getElementById('editBalanceAmount');

            window.recalculateEditIncome = function () {
                if (paidAmountInput) {
                    paidAmountInput.dispatchEvent(new Event('input', {
                        bubbles: true
                    }));
                }
            };

            function updateBreakdownText(displayId, baseAmount, gstAmount, tdsAmount, conversionCost = 0, isSplit = false) {
                const el = document.getElementById(displayId);
                if (!el) return;

                el.className = "d-block mt-2";
                el.style.fontSize = "0.85em";
                el.style.textTransform = "none";

                const foreignHtml = el.dataset.foreignHtml;
                let html = foreignHtml ? `<span class="badge bg-light text-dark border">${foreignHtml}</span>` : `<span class="badge bg-light text-dark border">Base: ₹${baseAmount.toFixed(2)}</span>`;
                
                if (gstAmount > 0) html += ` <span class="text-muted mx-1">+</span> <span class="badge bg-info text-dark">GST: ₹${gstAmount.toFixed(2)}</span>`;
                if (isSplit && tdsAmount > 0) {
                    html += ` <span class="text-muted mx-1">-</span> <span class="badge bg-danger text-white">TDS: ₹${tdsAmount.toFixed(2)}</span>`;
                }
                let finalAmt = isSplit ? (baseAmount + gstAmount - tdsAmount) : (baseAmount + gstAmount);
                if (!foreignHtml) {
                    html += ` <span class="text-muted mx-1">=</span> <span class="badge bg-success text-white">₹${finalAmt.toFixed(2)}</span>`;
                }

                if (!isSplit && tdsAmount > 0) {
                    html += ` <div class="mt-1"><span class="text-danger" style="font-size:0.95em;"><i class="bi bi-info-circle"></i> TDS ₹${tdsAmount.toFixed(2)} deducted from payable</span></div>`;
                }
                el.innerHTML = html;
            }

            function calculateEditTaxAndBalance(source) {
                const baseAmount = parseFloat(plannedAmountInput.value) || 0; // plannedAmountInput maps to the Base amount in our new logic
                const paidAmount = parseFloat(paidAmountInput.value) || 0;
                
                const applyGst = gstCheckbox ? gstCheckbox.checked : false;
                const applyTds = tdsCheckbox ? tdsCheckbox.checked : false;
                const gstPercentage = applyGst ? (parseFloat(gstPercentageInput ? gstPercentageInput.value : 0) || 0) : 0;
                const tdsPercentage = applyTds ? (parseFloat(tdsPercentageInput ? tdsPercentageInput.value : 0) || 0) : 0;

                const gstAmount = (baseAmount * gstPercentage) / 100;
                const tdsAmount = (baseAmount * tdsPercentage) / 100;
                
                if (gstAmountInput) gstAmountInput.value = gstAmount.toFixed(2);
                if (tdsAmountInput) tdsAmountInput.value = tdsAmount.toFixed(2);

                const conversionCost = parseFloat(document.getElementById('editOriginalAmount').dataset.conversionCost || 0);
                const plannedAmount = baseAmount + gstAmount;
                let netPayable = plannedAmount - tdsAmount - conversionCost;
                
                // Fix rounding display in UI for split payments
                const originalTotal = parseFloat(document.getElementById('editOriginalAmount').dataset.originalTotal || 0);
                const originalBase = parseFloat(document.getElementById('editOriginalAmount').dataset.originalBase || document.getElementById('editOriginalAmount').value || 0);
                if (originalTotal > 0 && Math.abs(netPayable - originalTotal) <= 1.00 && Math.abs(baseAmount - originalBase) <= 1.00) {
                    netPayable = originalTotal;
                }
                
                updateBreakdownText('incomePlannedBreakdown', baseAmount, gstAmount, tdsAmount, conversionCost, false);

                // Validation: Paid amount should not be greater than Net Payable
                // Added a small 0.05 tolerance to account for past database decimal rounding truncations
                if (paidAmount > netPayable + 0.05 && netPayable > 0) {
                    paidAmountInput.classList.add('is-invalid');
                    if (!document.getElementById('paidAmountError')) {
                        const error = document.createElement('div');
                        error.id = 'paidAmountError';
                        error.className = 'invalid-feedback';
                        error.textContent = 'Paid amount cannot exceed payable amount (' + netPayable.toFixed(2) + ')';
                        paidAmountInput.parentNode.appendChild(error);
                    } else {
                        document.getElementById('paidAmountError').textContent = 'Paid amount cannot exceed payable amount (' + netPayable.toFixed(2) + ')';
                    }
                    if (document.getElementById('editSubmitBtn')) document.getElementById('editSubmitBtn').disabled = true;
                } else {
                    paidAmountInput.classList.remove('is-invalid');
                    if (document.getElementById('editSubmitBtn')) document.getElementById('editSubmitBtn').disabled = false;
                    const error = document.getElementById('paidAmountError');
                    if (error) error.remove();
                }
                
                const balance = netPayable - paidAmount;
                const balanceVal = Math.max(0, balance);
                
                if (balanceAmountInput) {
                    balanceAmountInput.value = balanceVal.toFixed(2);

                    const statusSelect = document.getElementById('editStatus');
                    if (statusSelect) {
                        const statusContainer = statusSelect.closest('div[class^="col-"]');
                        if (balanceVal <= 0.01) {
                            if (statusContainer) statusContainer.style.display = 'none';
                            statusSelect.value = 'settle';
                            handleStatusChange(statusSelect, 'editDueDateContainer', 'editDueDate');
                        } else {
                            if (statusContainer) statusContainer.style.display = 'block';
                            handleStatusChange(statusSelect, 'editDueDateContainer', 'editDueDate');
                        }
                    }

                    handleStatusBehavior('income-edit');
                }
            }


            if (plannedAmountInput) {
                plannedAmountInput.addEventListener('input', () => calculateEditTaxAndBalance('planned'));
            }

            if (paidAmountInput) {
                // Changing paid amount should only update balance, not recalculate taxes
                paidAmountInput.addEventListener('input', () => calculateEditTaxAndBalance('paid'));
            }


            if (gstCheckbox) {
                gstCheckbox.addEventListener('change', function () {
                    if (gstPercentageInput) {
                        gstPercentageInput.disabled = !this.checked;
                    }
                    calculateEditTaxAndBalance('gst');
                });
            }

            if (tdsCheckbox) {
                tdsCheckbox.addEventListener('change', function () {
                    if (tdsPercentageInput) {
                        tdsPercentageInput.disabled = !this.checked;
                    }
                    calculateEditTaxAndBalance('tds');
                    handleTdsStatusBehavior('editTdsStatus', 'editTdsReceipt');
                });
            }

            // Add percentage listeners only if not disabled
            if (gstPercentageInput) {
                gstPercentageInput.addEventListener('input', () => calculateEditTaxAndBalance('gst'));
            }

            if (tdsPercentageInput) {
                tdsPercentageInput.addEventListener('input', () => calculateEditTaxAndBalance('tds'));
            }

            // Initial calculation - REMOVED to prevent overwriting saved values from DB
            // calculateEditTaxAndBalance();
            editIncomeListenersInitialized = true;
        }
        // Format date for input field (YYYY-MM-DD)
        function formatDateForInput(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toISOString().split('T')[0];
        }

        // Handle edit form submission
        document.getElementById('editIncomeForm')?.addEventListener('submit', async function (e) {
            e.preventDefault();

            // Enable disabled fields temporarily for submission
            const disabledElements = this.querySelectorAll(':disabled');
            disabledElements.forEach(el => el.disabled = false);

            const paymentMode = document.getElementById('editPaymentMode').value;
            if (paymentMode === 'bank_transfer' || paymentMode === 'cheque') {
                const bankName = document.getElementById('editEditableBankName').value;
                if (!bankName) {
                    alert('Please select a Bank for the selected payment mode.');
                    disabledElements.forEach(el => el.disabled = true);
                    return;
                }
            } else if (paymentMode === 'upi') {
                const upiType = document.getElementById('editEditableUpiType').value;
                const upiNumber = document.getElementById('editEditableUpiNumber').value;
                if (!upiType) {
                    alert('Please select a UPI Type.');
                    disabledElements.forEach(el => el.disabled = true);
                    return;
                }
                if (!upiNumber) {
                    alert('Please enter a UPI Phone number.');
                    disabledElements.forEach(el => el.disabled = true);
                    return;
                }
            }

            const incomeId = document.getElementById('editIncomeId').value;
            if (!incomeId) {
                alert('Invalid income ID');
                return;
            }

            const formData = new FormData(this);
            const url = `https://xhtmlreviews.in/beta-finance/manager/income/${incomeId}`;

            // Show loading state
            const submitBtn = document.getElementById('editSubmitBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Updating...';
            submitBtn.disabled = true;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                            'content') || ''
                    }
                });

                const data = await response.json();

                if (data.success) {
                    // Show success message
                    showNotification('success', data.message || 'Income updated successfully');

                    // Hide modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editIncomeModal'));
                    if (modal) modal.hide();

                    // Reload page after delay
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    // Re-disable fields if error
                    disabledElements.forEach(el => el.disabled = true);

                    // Show error
                    showNotification('error', data.message || 'Error updating income');

                    // Reset button
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;

                    // Show validation errors
                    if (data.errors) {
                        Object.keys(data.errors).forEach(fieldName => {
                            const field = document.querySelector(`[name="${fieldName}"]`);
                            if (field) {
                                field.classList.add('is-invalid');
                                const errorDiv = document.createElement('div');
                                errorDiv.className = 'invalid-feedback';
                                errorDiv.textContent = data.errors[fieldName][0];
                                field.parentNode.appendChild(errorDiv);
                            }
                        });
                    }
                }
            } catch (error) {
                console.error('Update error:', error);
                showNotification('error', 'Error updating income: ' + error.message);

                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });

        // Delete receipt function
        async function deleteReceipt(receiptId, incomeId) {
            if (!confirm('Are you sure you want to delete this receipt?')) {
                return;
            }

            try {
                const response = await fetch(
                    `https://xhtmlreviews.in/beta-finance/manager/receipts/${receiptId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                            'content') || '',
                        'Accept': 'application/json'
                    }
                }
                );

                const data = await response.json();

                if (data.success) {
                    showNotification('success', 'Receipt deleted successfully');
                    // Reload the edit modal
                    openEditIncomeModal(incomeId);
                } else {
                    showNotification('error', data.message || 'Error deleting receipt');
                }
            } catch (error) {
                console.error('Delete error:', error);
                showNotification('error', 'Error deleting receipt');
            }
        }

        // Notification function (reuse from main page)
        function showNotification(type, message) {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
            notification.style.cssText = `
                                                                        position: fixed;
                                                                        top: 20px;
                                                                        right: 20px;
                                                                        z-index: 9999;
                                                                        min-width: 300px;
                                                                        max-width: 400px;
                                                                    `;
            notification.innerHTML = `
                                                                        <div class="d-flex justify-content-between align-items-center">
                                                                            <span>${message}</span>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                                                        </div>
                                                                    `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 5000);
        }

        function viewProforma(id) {
            if (!id) {
                console.error('No invoice ID provided');
                showUserAlert('Invoice ID is missing', 'error');
                return;
            }

            console.log('Fetching invoice data for ID:', id);
            showLoadingState(true);

            fetch(`https://xhtmlreviews.in/beta-finance/manager/getIncome/${id}`)
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('API Response:', data);
                    showLoadingState(false);

                    if (!data.success) {
                        throw new Error(data.message || 'Failed to load invoice details');
                    }

                    if (!data.invoice) {
                        throw new Error('No invoice data received');
                    }

                    renderInvoiceModal(data.invoice);
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    showLoadingState(false);
                    showUserAlert(error.message || 'Error loading invoice details. Please try again.', 'error');
                });
        }

        // Helper function to show/hide loading state
        function showLoadingState(isLoading) {
            const content = document.getElementById('invoiceDetailsContent');
            if (content && isLoading) {
                content.innerHTML = `
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3">Loading invoice details...</p>
                        </div>
                    `;
            }
        }

        // Helper function to show user-friendly alerts
        function showUserAlert(message, type = 'info') {
            // You can replace this with a toast notification or modal
            alert(message);

            // Optional: If you have a toast container
            // const toastContainer = document.getElementById('toastContainer');
            // if (toastContainer) {
            //     showToast(message, type);
            // }
        }

        // Helper function to format currency
        function formatCurrency(amount, currency = 'INR') {
            const value = parseFloat(amount) || 0;
            const symbols = {
                INR: '₹',
                USD: '$',
                EUR: '€',
                GBP: '£'
            };
            const symbol = symbols[currency] || currency;
            return `${symbol}${value.toFixed(2)}`;
        }

        // Helper function to format date
        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            try {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) return dateString;
                return date.toLocaleDateString('en-IN', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            } catch (e) {
                console.error('Date parsing error:', e);
                return dateString;
            }
        }

        // Helper function to get status badge class
        function getStatusBadgeClass(status) {
            const statusMap = {
                'pending': 'warning',
                'received': 'success',
                'overdue': 'danger',
                'paid': 'success',
                'cancelled': 'secondary'
            };
            return statusMap[status?.toLowerCase()] || 'secondary';
        }

        // Function to calculate tax totals
        function calculateTaxTotals(taxes) {
            if (!taxes || !taxes.length) {
                return {
                    gstTotal: 0,
                    tdsTotal: 0,
                    gstItems: [],
                    tdsItems: []
                };
            }

            const gstItems = taxes.filter(tax => tax.tax_type?.toLowerCase() === 'gst');
            const tdsItems = taxes.filter(tax => tax.tax_type?.toLowerCase() === 'tds');

            const gstTotal = gstItems.reduce((sum, tax) => sum + (parseFloat(tax.tax_amount) || 0), 0);
            const tdsTotal = tdsItems.reduce((sum, tax) => sum + (parseFloat(tax.tax_amount) || 0), 0);

            return {
                gstTotal,
                tdsTotal,
                gstItems,
                tdsItems
            };
        }

        // Function to render line items table
        function renderLineItems(lineItems, currency = 'INR') {
            if (!lineItems || !lineItems.length) {
                return '<tr><td colspan="4" class="text-center text-muted">No line items found</td></tr>';
            }

            return lineItems.map(item => `
                    <tr>
                        <td>${escapeHtml(item.description || 'Item')}</td>
                        <td class="text-end">${item.quantity || 1}</td>
                        <td class="text-end">${formatCurrency(item.rate || 0, currency)}</td>
                        <td class="text-end">${formatCurrency(item.amount || 0, currency)}</td>
                    </tr>
                `).join('');
        }

        // Function to render GST details
        function renderGstDetails(gstItems, gstTotal) {
            if (!gstItems.length) return '';

            return `
                    <div class="mt-4">
                        <h6 class="fw-bold">GST Details</h6>
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>GST Type</th>
                                    <th class="text-end">Percentage</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${gstItems.map(tax => `
                                    <tr>
                                        <td>${escapeHtml(tax.tax_type?.toUpperCase() || 'GST')}</td>
                                        <td class="text-end">${(parseFloat(tax.tax_percentage) || 0).toFixed(2)}%</td>
                                        <td class="text-end">${formatCurrency(tax.tax_amount || 0)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2" class="text-end">Total GST:</th>
                                    <td class="text-end"><strong>${formatCurrency(gstTotal)}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                `;
        }

        // Function to render TDS details
        function renderTdsDetails(tdsItems, tdsTotal) {
            if (!tdsItems.length) return '';

            return `
                    <div class="mt-3">
                        <h6 class="fw-bold text-danger">TDS Details</h6>
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>TDS Type</th>
                                    <th class="text-end">Percentage</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${tdsItems.map(tax => `
                                    <tr>
                                        <td>${escapeHtml(tax.tax_type?.toUpperCase() || 'TDS')}</td>
                                        <td class="text-end">${(parseFloat(tax.tax_percentage) || 0).toFixed(2)}%</td>
                                        <td class="text-end text-danger">-${formatCurrency(tax.tax_amount || 0)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2" class="text-end">Total TDS Deduction:</th>
                                    <td class="text-end"><strong class="text-danger">-${formatCurrency(tdsTotal)}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                `;
        }

        // Function to render conversion details for USD invoices
        function renderConversionDetails(invoice) {
            if (invoice.currency !== 'USD') return '';

            const originalAmount = parseFloat(invoice.original_currency_amount || invoice.actual_amount || invoice.grand_total || 0);
            const convertedAmount = parseFloat(invoice.invoice_converted_amount || invoice.converted_amount || 0);
            const conversionRate = originalAmount > 0 ? (convertedAmount / originalAmount) : 0;
            const displayConversionCost = parseFloat(invoice.original_conversion_cost || invoice.conversion_cost || 0);
            
            return `
                    <div class="card mb-4 border-info">
                        <div class="card-header bg-info text-white">
                            <i class="fas fa-exchange-alt me-2"></i>Currency Conversion Details
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Original Amount</small>
                                    <strong class="fs-5">${formatCurrency(originalAmount, 'USD')}</strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Conversion Rate</small>
                                    <strong>1 USD = ${formatCurrency(conversionRate)}</strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Converted Amount</small>
                                    <strong class="fs-5 text-primary">${formatCurrency(convertedAmount)}</strong>
                                </div>
                            </div>
                            ${displayConversionCost > 0 ? `
                                <div class="row mt-3">
                                    <div class="col-12 text-center">
                                        <small class="text-muted">Conversion Cost:</small>
                                        <strong>${formatCurrency(displayConversionCost)}</strong>
                                    </div>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;
        }

        // Main render function
        function renderInvoiceModal(invoice) {
            const content = document.getElementById('invoiceDetailsContent');
            if (!content) {
                console.error('Element #invoiceDetailsContent not found!');
                showUserAlert('Error: Invoice details container not found', 'error');
                return;
            }

            // Calculate taxes
            let {
                gstTotal,
                tdsTotal,
                gstItems,
                tdsItems
            } = calculateTaxTotals(invoice.taxes);

            // Calculate amounts
            const subtotal = parseFloat(invoice.subtotal) || 0;
            const isSplit = invoice.is_split == 1 || (invoice.parent_id && invoice.parent_id != 0);
            let netPayable = parseFloat(invoice.original_currency_amount || invoice.grand_total || invoice.total_amount || 0);
            let displayCurrency = invoice.currency;

            if (!invoice.invoice_id) {
                netPayable = parseFloat(invoice.amount || 0);
            } 
            
            // If the original currency was USD, it's always converted to INR for display/payment
            if (invoice.currency === 'USD') {
                displayCurrency = 'INR';
            }

            const isUSD = invoice.currency === 'USD';

            // Split Summary HTML pre-computation
            // original_base_amount = base amount of the root invoice, schedule_amount = base + GST (planned amount)
            const baseAmountForSplit = parseFloat(invoice.original_base_amount) || parseFloat(invoice.actual_amount) || subtotal || parseFloat(invoice.schedule_amount) || 0;
            const originalConversionCost = parseFloat(invoice.original_conversion_cost || invoice.conversion_cost || 0);
            
            // Prefer original_total_amount from backend to avoid floating point math discrepancies
            const originalNetAmount = invoice.original_total_amount 
                ? parseFloat(invoice.original_total_amount) 
                : (baseAmountForSplit + (parseFloat(invoice.original_gst_total) || 0) - (parseFloat(invoice.original_tds_total) || 0) - (isUSD ? originalConversionCost : 0));
                
            const pendingAmount = originalNetAmount - (parseFloat(invoice.total_paid_amount) || 0);

            const currentSplitBase = parseFloat(invoice.actual_amount) || subtotal;
            const actualConversionRate = (invoice.currency === 'USD' && parseFloat(invoice.original_currency_amount || 0) > 0) 
                ? (parseFloat(invoice.invoice_converted_amount || invoice.converted_amount || 0) / parseFloat(invoice.original_currency_amount)) 
                : 1;
                
            const currentSplitBaseDisplay = currentSplitBase;
            const plannedAmtStr = isUSD ? ` (${Math.round(parseFloat(invoice.planned_amount || (parseFloat(invoice.amount || 0) + parseFloat(invoice.conversion_cost || 0)) || 0))})` : '';

            const currentSplitSummaryHtml = isSplit ? `
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small fw-bold text-primary">Current Split Details</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small text-muted">This Split (Base):</span>
                        <span class="small fw-bold">${formatCurrency(currentSplitBaseDisplay, invoice.currency || displayCurrency)}${plannedAmtStr}</span>
                    </div>
                    ${(gstTotal > 0) ? `
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small text-muted">+ GST:</span>
                        <span class="small fw-bold">${formatCurrency(gstTotal, displayCurrency)}</span>
                    </div>
                    ` : ''}
                    ${(tdsTotal > 0) ? `
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small text-muted">- TDS:</span>
                        <span class="small fw-bold text-danger">-${formatCurrency(tdsTotal, displayCurrency)}</span>
                    </div>
                    ` : ''}
                    ${(isUSD && parseFloat(invoice.conversion_cost || 0) > 0) ? `
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small text-muted">This Split Conversion Cost:</span>
                        <span class="small fw-bold text-danger">-${formatCurrency(parseFloat(invoice.conversion_cost), displayCurrency)}</span>
                    </div>
                    ` : ''}
                    <div class="d-flex justify-content-between mb-2">
                        <span class="small text-muted">This Split Total:</span>
                        <span class="small fw-bold">${formatCurrency(netPayable, displayCurrency)}</span>
                    </div>
                    <hr class="my-1 border-secondary border-opacity-25">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small text-muted">Total Paid Overall:</span>
                        <span class="small fw-bold text-success">${formatCurrency(invoice.total_paid_amount, displayCurrency)}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="small text-muted">Total Pending Overall:</span>
                        <span class="small fw-bold text-danger">${formatCurrency(parseFloat(pendingAmount), displayCurrency)}</span>
                    </div>
                    <div class="text-center mt-2">
                        <button class="btn btn-sm btn-outline-secondary w-100" onclick="viewSplitHistory(${invoice.id})" data-bs-dismiss="modal">
                            <i class="bi bi-clock-history me-1"></i>View Full Split History
                        </button>
                    </div>
                    ` : '';

            // Format status badge
            const statusClass = getStatusBadgeClass(invoice.status);
            const statusText = (invoice.status || 'unknown').toUpperCase();

            // Build HTML
            const html = `
                    <div class="invoice-container shadow-none border-0 p-3">
                        <!-- Header with Company & Client Info -->
                        <div class="row g-4 mb-4">
                            <div class="col-lg-8">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="bi bi-receipt fs-2 me-2 text-primary"></i>
                                    <h4 class="section-title mb-0">Invoice Details</h4>
                                </div>

                                <table class="table table-sm info-table">
                                    <tr>
                                        <th width="160"><i class="bi bi-hash me-1"></i>Invoice Number</th>
                                        <td><span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">${escapeHtml(invoice.invoice_number || 'N/A')}</span></td>
                                    </tr>
                                    <tr>
                                        <th><i class="bi bi-building me-1"></i>Company</th>
                                        <td><strong>${escapeHtml(invoice.company?.name || 'N/A')}</strong></td>
                                    </tr>
                                    <tr>
                                        <th><i class="bi bi-person me-1"></i>Client</th>
                                        <td>${escapeHtml(invoice.client_details?.name || 'N/A')}</td>
                                    </tr>
                                    <tr>
                                        <th><i class="bi bi-envelope me-1"></i>Email</th>
                                        <td><a href="mailto:${escapeHtml(invoice.client_details?.email || '')}" class="text-decoration-none">${escapeHtml(invoice.client_details?.email || 'N/A')}</a></td>
                                    </tr>
                                    ${invoice.client_details?.gstin ? `
                                    <tr>
                                        <th><i class="bi bi-upc-scan me-1"></i>GSTIN</th>
                                        <td><code class="bg-light px-2 py-1 rounded">${escapeHtml(invoice.client_details.gstin)}</code></td>
                                    </tr>
                                    ` : ''}
                                    ${!isUSD && invoice.tax_type && (gstTotal > 0 || tdsTotal > 0) ? `
                                    <tr>
                                        <th><i class="bi bi-calculator me-1"></i>Tax Type</th>
                                        <td><span class="tax-badge bg-info bg-opacity-10 text-info">${escapeHtml(invoice.tax_type)}</span></td>
                                    </tr>
                                    ` : ''}
                                    <tr>
                                        <th><i class="bi bi-calendar-event me-1"></i>Due Date</th>
                                        <td>
                                            <span class="${new Date(invoice.due_date) < new Date() ? 'text-danger' : 'text-success'}">
                                                <i class="bi bi-clock me-1"></i>${formatDate(invoice.due_date)}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <div class="col-lg-4">
                                <div class="detail-card d-flex flex-column justify-content-center">
                                    <div class="text-center p-3">
                                        <span class="badge bg-${statusClass} status-badge mb-3">
                                            <i class="bi bi-${statusClass === 'success' ? 'check-circle' : statusClass === 'warning' ? 'exclamation-circle' : 'clock-history'} me-1"></i>
                                            ${statusText}
                                        </span>
                                        <div class="mt-2">
                                            <small class="text-white-50 d-block text-uppercase" style="letter-spacing: 1px;">Document Type</small>
                                            <h4 class="text-white mb-0 fw-bold">
                                                <i class="bi bi-file-text me-2"></i>
                                                ${(invoice.type || 'invoice').toUpperCase()}
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Currency Conversion Alert -->
                        ${renderConversionDetails(invoice)}

                        <!-- Line Items Section -->
                        ${invoice.line_items && invoice.line_items.length ? `
                        <div class="mt-4">
                            <h6 class="section-title">
                                <i class="bi bi-list-ul me-2"></i>
                                Line Items
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-items">
                                    <thead class="bg-light">
                                        <tr>
                                            <th width="45%">Description</th>
                                            <th width="15%" class="text-end">Quantity</th>
                                            <th width="20%" class="text-end">Rate (${invoice.currency || 'INR'})</th>
                                            <th width="20%" class="text-end">Amount (${invoice.currency || 'INR'})</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${renderLineItems(invoice.line_items, invoice.currency)}
                                    </tbody>
                                    <tfoot>
                                        <tr class="border-top">
                                            <td colspan="3" class="text-end fw-semibold pt-3">Subtotal</td>
                                            <td class="text-end fw-bold pt-3">${formatCurrency(isUSD ? parseFloat(invoice.invoice?.converted_amount || invoice.invoice_converted_amount || invoice.planned_amount || (parseFloat(invoice.amount || 0) + parseFloat(invoice.conversion_cost || 0)) || baseAmountForSplit) : baseAmountForSplit, displayCurrency)}</td>
                                        </tr>
                                        ${(invoice.original_gst_total > 0 || gstTotal > 0) ? `
                                        <tr>
                                            <td colspan="3" class="text-end text-muted">GST <span class="small">(Included)</span></td>
                                            <td class="text-end">${formatCurrency(invoice.original_gst_total || gstTotal, displayCurrency)}</td>
                                        </tr>` : ''}
                                        ${(invoice.original_tds_total > 0 || tdsTotal > 0) ? `
                                        <tr>
                                            <td colspan="3" class="text-end text-danger">TDS <span class="small">(Deducted)</span></td>
                                            <td class="text-end text-danger">-${formatCurrency(invoice.original_tds_total || tdsTotal, displayCurrency)}</td>
                                        </tr>` : ''}
                                        ${(isUSD && originalConversionCost > 0) ? `
                                        <tr>
                                            <td colspan="3" class="text-end text-danger">Conversion Cost <span class="small">(Deducted)</span></td>
                                            <td class="text-end text-danger">-${formatCurrency(originalConversionCost, displayCurrency)}</td>
                                        </tr>` : ''}
                                        <tr class="total-row bg-light">
                                            <td colspan="3" class="text-end fs-5 fw-bold py-3">Total Receivable</td>
                                            <td class="text-end fs-5 fw-bold text-success py-3">
                                                ${formatCurrency(isSplit ? originalNetAmount : netPayable, displayCurrency)}
                                            </td>
                                        </tr>
                                        ${isSplit ? `
                                        <tr>
                                            <td colspan="4" class="p-0 border-0">
                                                <div class="bg-white border-top p-3">
                                                    ${currentSplitSummaryHtml}
                                                </div>
                                            </td>
                                        </tr>
                                        ` : ''}
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        ` : `
                        <!-- Summary for No Line Items -->
                        <div class="alert alert-info" role="alert">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                                <strong class="fs-5">Invoice Summary</strong>
                            </div>
                            <div class="ms-4">
                                ${(!isUSD && (parseFloat(invoice.original_gst_total) > 0 || parseFloat(invoice.original_tds_total) > 0 || gstTotal > 0 || tdsTotal > 0)) ? `
                                    <div class="d-flex justify-content-between mb-1 text-muted">
                                        <span>Base Amount:</span>
                                        <span>${formatCurrency(isSplit ? baseAmountForSplit : parseFloat(invoice.original_currency_amount || (netPayable - gstTotal + tdsTotal)), displayCurrency)}</span>
                                    </div>
                                    ${(isSplit ? (parseFloat(invoice.original_gst_total) > 0) : (gstTotal > 0)) ? `
                                    <div class="d-flex justify-content-between mb-1 text-muted">
                                        <span>+ GST:</span>
                                        <span>${formatCurrency(isSplit ? parseFloat(invoice.original_gst_total) : gstTotal, displayCurrency)}</span>
                                    </div>
                                    ` : ''}
                                    ${(isSplit ? (parseFloat(invoice.original_tds_total) > 0) : (tdsTotal > 0)) ? `
                                    <div class="d-flex justify-content-between mb-2 text-danger">
                                        <span>- TDS:</span>
                                        <span>${formatCurrency(isSplit ? parseFloat(invoice.original_tds_total) : tdsTotal, displayCurrency)}</span>
                                    </div>
                                    ` : ''}
                                    <hr class="my-2 border-secondary opacity-25">
                                ` : ''}
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fs-6 fw-bold">Total Receivable:</span>
                                    <span class="fs-4 fw-bold text-success">${formatCurrency(isSplit ? originalNetAmount : netPayable, displayCurrency)}</span>
                                </div>
                                ${isSplit ? `
                                <div class="mt-3 pt-2 border-top border-secondary border-opacity-25">
                                    ${currentSplitSummaryHtml}
                                </div>
                                ` : ''}
                            </div>
                        </div>
                        `}

                        <!-- Tax Details Cards -->
                        ${!isUSD ? `
                        <div class="row g-3 mt-2">
                            ${gstItems && gstItems.length > 0 ? `
                            <div class="col-md-6">
                                <div class="tax-section bg-light border p-3 rounded">
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-percent me-2 text-primary"></i>
                                        GST Breakdown
                                    </h6>
                                    ${renderGstDetails(gstItems, gstTotal)}
                                </div>
                            </div>
                            ` : ''}
                            ${tdsItems && tdsItems.length > 0 ? `
                            <div class="col-md-6">
                                <div class="tax-section bg-light border p-3 rounded">
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-file-spreadsheet me-2 text-warning"></i>
                                        TDS Details
                                    </h6>
                                    ${renderTdsDetails(tdsItems, tdsTotal)}
                                </div>
                            </div>
                            ` : ''}
                        </div>
                        ` : ''}

                        <!-- Purpose / Comment -->
                        ${invoice.purpose_comment ? `
                        <div class="comment-box mt-4 p-3 bg-light rounded border-start border-warning border-4">
                            <h6 class="fw-bold mb-2">
                                <i class="bi bi-chat-left-text me-2"></i>
                                Purpose / Comments
                            </h6>
                            <div class="mb-0 text-muted">${escapeHtml(invoice.purpose_comment)}</div>
                        </div>
                        ` : ''}

                        <!-- Terms & Conditions -->
                        ${invoice.terms_conditions ? `
                        <div class="terms-box mt-3 p-3 bg-light rounded border-start border-secondary border-4">
                            <h6 class="fw-bold mb-2">
                                <i class="bi bi-file-ruled me-2"></i>
                                Terms & Conditions
                            </h6>
                            <div class="small text-muted" style="white-space: pre-line;">${escapeHtml(invoice.terms_conditions)}</div>
                        </div>
                        ` : ''}

                        <!-- Footer Note -->
                        <div class="text-center text-muted small mt-4 pt-3 border-top">
                            <div class="mb-1"><i class="bi bi-shield-check me-1"></i> This is a computer generated invoice. No signature required.</div>
                            ${invoice.created_at ? `<span><i class="bi bi-calendar3 me-1"></i>Generated: ${formatDate(invoice.created_at)}</span>` : ''}
                        </div>
                    </div>
                    `;

            content.innerHTML = html;

            // Show the modal
            const modalElement = document.getElementById('viewInvoiceModal');
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            } else {
                console.error('Modal element #viewInvoiceModal not found!');
                showUserAlert('Error: Could not display invoice modal', 'error');
            }
        }

        // Security: Escape HTML to prevent XSS attacks
        function escapeHtml(str) {
            if (!str) return '';
            return str
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        // Add print function
        function printInvoice() {
            const content = document.getElementById('invoiceDetailsContent');
            if (content) {
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                                                                        <!DOCTYPE html>
                                                                        <html>
                                                                        <head>
                                                                            <title>Invoice Print</title>
                                                                            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                                                                            <style>
                                                                                @media print {
                                                                                    body { margin: 0; padding: 20px; }
                                                                                    .no-print { display: none !important; }
                                                                                }
                                                                                .invoice-header { border-bottom: 2px solid #000; margin-bottom: 20px; padding-bottom: 10px; }
                                                                                .total-row { font-weight: bold; }
                                                                            </style>
                                                                        </head>
                                                                        <body>
                                                                            ${content.innerHTML}
                                                                            <div class="text-center mt-4 no-print">
                                                                                <button class="btn btn-primary" onclick="window.print()">Print</button>
                                                                                <button class="btn btn-secondary" onclick="window.close()">Close</button>
                                                                            </div>
                                                                        </body>
                                                                        </html>
                                                                    `);
                printWindow.document.close();
            }
        }

        function closeSendModal() {
            const modal = document.getElementById('sendInvoiceModal');
            if (modal) {
                const bootstrapModal = bootstrap.Modal.getInstance(modal);
                if (bootstrapModal) {
                    bootstrapModal.hide();
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

            // Reset
            detailsRow.style.display = 'none';
            bankDetails.forEach(el => el.style.display = 'none');
            upiDetails.forEach(el => el.style.display = 'none');

            if (val === 'bank_transfer' || val === 'cheque') {
                detailsRow.style.display = 'flex';
                bankDetails.forEach(el => el.style.display = 'block');
            } else if (val === 'upi' || val === 'online' || val === 'card') {
                detailsRow.style.display = 'flex';
                upiDetails.forEach(el => el.style.display = 'block');
            }
        }

        function downloadProforma(id) {
            // First get the invoice_id from income
            fetch(`https://xhtmlreviews.in/beta-finance/manager/getIncome/${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.income && data.income.invoice_id) {
                        // Use the invoice_id to download
                        window.open(
                            `https://xhtmlreviews.in/beta-finance/admin/income/${data.income.invoice_id}/download`,
                            '_blank');
                    } else {
                        alert('No invoice found for this income');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error fetching income details');
                });
        }

        // Updated modal handling with income ID
        document.addEventListener('DOMContentLoaded', function () {
            const sendInvoiceModal = document.getElementById('sendInvoiceModal');

            if (sendInvoiceModal) {
                const sendConfirmBtn = document.getElementById('send_confirm_btn');

                // Modal show event - now using income ID
                sendInvoiceModal.addEventListener('show.bs.modal', async function (event) {
                    const button = event.relatedTarget;
                    const incomeId = button.dataset.incomeId;

                    // Fetch income and invoice details
                    try {
                        const response = await fetch(
                            `https://xhtmlreviews.in/beta-finance/manager/getIncome/${incomeId}`);
                        const data = await response.json();

                        if (data.success && data.invoice) {
                            const invoice = data.invoice;
                            const income = data.income; // Get income data from response

                            // Parse client details
                            let clientDetails = invoice.client_details;
                            if (typeof clientDetails === 'string') {
                                try {
                                    clientDetails = JSON.parse(clientDetails);
                                } catch (e) {
                                    console.error('Error parsing client_details:', e);
                                    clientDetails = {};
                                }
                            }

                            const clientName = clientDetails?.name || 'Customer';
                            const clientEmail = clientDetails?.email || '';

                            // Set form values
                            document.getElementById('send_invoice_id').value = invoice.id || '';
                            document.getElementById('send_income_id').value = incomeId;
                            document.getElementById('send_invoice_no').value = invoice.invoice_number ||
                                '';

                            // Check if type exists, otherwise use a default
                            const invoiceType = invoice.type || 'invoice';
                            document.getElementById('send_invoice_type_display').value =
                                invoiceType === 'proforma' ? 'Proforma Invoice' : 'Tax Invoice';

                            document.getElementById('send_to_email').value = clientEmail;

                            // Format due date
                            let formattedDueDate = 'N/A';
                            if (invoice.due_date) {
                                try {
                                    formattedDueDate = new Date(invoice.due_date).toLocaleDateString(
                                        'en-IN', {
                                        day: 'numeric',
                                        month: 'short',
                                        year: 'numeric'
                                    });
                                } catch (e) {
                                    console.error('Error formatting due date:', e);
                                }
                            }

                            // Set default subject
                            const companyName = invoice.company?.name || income.company?.name || '';
                            const defaultSubject = invoiceType === 'proforma' ?
                                `Proforma Invoice ${invoice.invoice_number} from ${companyName}` :
                                `Invoice ${invoice.invoice_number} from ${companyName}`;
                            document.getElementById('send_subject').value = defaultSubject;

                            // The email attaches the full original invoice PDF, so the email text 
                            // should always state the FULL payable amount of the invoice, not a split portion.
                            let displayAmount = 0;
                            const isForeignCurrency = (invoice.currency && invoice.currency !== 'INR');
                            
                            if (isForeignCurrency && parseFloat(invoice.original_currency_amount) > 0) {
                                // For foreign currencies, the original total is exactly this field
                                displayAmount = parseFloat(invoice.original_currency_amount);
                            } else {
                                // For INR invoices, reconstruct the full net payable amount
                                const originalBaseTotal = parseFloat(invoice.original_base_amount) || 0;
                                const originalGstTotal = parseFloat(invoice.original_gst_total) || 0;
                                const originalTdsTotal = parseFloat(invoice.original_tds_total) || 0;
                                
                                displayAmount = originalBaseTotal + originalGstTotal - originalTdsTotal;
                                
                                // Fallback
                                if (displayAmount <= 0) {
                                    displayAmount = parseFloat(invoice.total_amount || invoice.amount || income?.amount || 0);
                                }
                            }
                                
                            const defaultMessage = `Dear ${clientName},

                                            ${invoiceType === 'proforma' ? 'Please find attached the proforma invoice' : 'Please find attached your invoice'} for ${invoice.currency == 'USD' ? '$' : (invoice.currency == 'EUR' ? '€' : (invoice.currency == 'GBP' ? '£' : '₹'))}${parseFloat(displayAmount).toFixed(2)}.

                                            Invoice Details:
                                            - Invoice Number: ${invoice.invoice_number || ''}
                                            - Amount: ${invoice.currency == 'USD' ? '$' : (invoice.currency == 'EUR' ? '€' : (invoice.currency == 'GBP' ? '£' : '₹'))}${parseFloat(displayAmount).toFixed(2)}
                                            ${invoice.due_date ? `- Due Date: ${formattedDueDate}` : ''}

                                            Please let us know if you have any questions.

                                            Best regards,
                                            ${companyName}`;

                            document.getElementById('send_message').value = defaultMessage;

                        } else {
                            alert('Error loading income details: ' + (data.message || 'Unknown error'));
                            bootstrap.Modal.getInstance(sendInvoiceModal).hide();
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Error loading details: ' + error.message);
                        bootstrap.Modal.getInstance(sendInvoiceModal).hide();
                    }
                });

                // Confirm send invoice
                sendConfirmBtn.addEventListener('click', function () {
                    const form = document.getElementById('sendInvoiceForm');
                    if (!form) {
                        alert('Form not found');
                        return;
                    }

                    const formData = new FormData(form);
                    const submitBtn = this;
                    const originalText = submitBtn.innerHTML;

                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
                    submitBtn.disabled = true;

                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .content
                        }
                    })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                alert(data.message || 'Invoice sent successfully!');
                                const modal = bootstrap.Modal.getInstance(sendInvoiceModal);
                                modal.hide();

                                // Optional: Reload the page or update UI
                                window.location.reload();
                            } else {
                                alert(data.message || 'Error sending invoice');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while sending the invoice: ' + error.message);
                        })
                        .finally(() => {
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                        });
                });
            }
        });
    </script>

    <style>
        /* Edit Modal Specific Styles */
        .required::after {
            content: " *";
            color: #dc3545;
        }

        .modal-header.bg-primary {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;
        }

        .btn-close-white {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        .form-control:read-only {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }

        .list-group-item {
            border-left: none;
            border-right: none;
            border-radius: 0 !important;
        }

        .list-group-item:first-child {
            border-top: none;
        }

        .list-group-item:last-child {
            border-bottom: none;
        }

        .input-group-text {
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }

        .invalid-feedback {
            display: block;
            font-size: 0.875rem;
        }

        .is-invalid {
            border-color: #dc3545 !important;
        }
    </style>
    <style>
        .manager-panel {
            padding: 20px;
        }

        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

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

        .card-tools {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-group-sm .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        table.table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }

        .table th {
            font-weight: 600;
            color: #495057;
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }

        .form-select-sm {
            font-size: 14px;
            padding: 5px 10px;
        }

        /* Pagination Styles */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: #fcfcfc;
            border-top: 1px solid #edf2f7;
            border-radius: 0 0 12px 12px;
            margin-top: 10px;
        }

        .pagination-info {
            font-size: 14px;
            color: #718096;
            font-weight: 500;
        }

        .pagination-info span {
            font-weight: 700;
            color: #2d3748;
        }

        .pagination-links .pagination {
            margin: 0;
            display: flex;
            gap: 6px;
        }

        .pagination-links .page-item .page-link {
            border: 1px solid #e2e8f0;
            color: #4a5568;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            background-color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 38px;
        }

        .pagination-links .page-item.active .page-link {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: transparent;
            color: #fff;
            box-shadow: 0 4px 10px rgba(102, 126, 234, 0.35);
        }

        .pagination-links .page-item.disabled .page-link {
            background-color: #f8fafc;
            color: #a0aec0;
            border-color: #edf2f7;
            cursor: not-allowed;
        }

        .pagination-links .page-item .page-link:hover:not(.active):not(.disabled) {
            background-color: #f1f5f9;
            border-color: #667eea;
            color: #667eea;
            transform: translateY(-1px);
        }

        /* Hide redundant Laravel pagination elements */
        .pagination-links nav div:first-child {
            display: none !important;
        }

        .pagination-links nav div:last-child {
            display: flex !important;
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

        #editTdsStatus,
        .form-select {
            color: #212529 !important;
            background-color: #fff !important;
        }
    </style>
@endsection