@extends('Manager.layouts.app')
@section('content')
<div id="loans" class="manager-panel">
  <!-- Dashboard Widgets -->
  <div class="row mb-4">
    <!-- Loans to be Paid (Advances given by company) -->
    <div class="col-md-3">
      <div class="summary-card">
        <div class="summary-header">
          <h6 class="mb-1">Total Loans Paid</h6>
          <small class="text-muted">Advances Given</small>
        </div>
        <div class="summary-body">
          <div class="d-flex justify-content-between align-items-end">
            <div>
              <h3 class="mb-0" id="totalPayableIssued">₹0.00</h3>
              <small class="text-muted" id="payableOutstanding">₹0.00 Outstanding</small>
            </div>
            <div class="summary-icon">
              <i class="fas fa-hand-holding-usd text-primary"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="summary-card">
        <div class="summary-header">
          <h6 class="mb-1">Total Recovered</h6>
          <small class="text-muted">From Advances Given</small>
        </div>
        <div class="summary-body">
          <div class="d-flex justify-content-between align-items-end">
            <div>
              <h3 class="mb-0" id="totalPayableRecovered">₹0.00</h3>
            </div>
            <div class="summary-icon">
              <i class="fas fa-undo text-success"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loans to be Recovered (Advances received by company) -->
    <div class="col-md-3">
      <div class="summary-card">
        <div class="summary-header">
          <h6 class="mb-1">Total Loans Received</h6>
          <small class="text-muted">Advances Received</small>
        </div>
        <div class="summary-body">
          <div class="d-flex justify-content-between align-items-end">
            <div>
              <h3 class="mb-0" id="totalReceivableIssued">₹0.00</h3>
              <small class="text-muted" id="receivableOutstanding">₹0.00 Outstanding</small>
            </div>
            <div class="summary-icon">
              <i class="fas fa-hand-holding text-info"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="summary-card">
        <div class="summary-header">
          <h6 class="mb-1">Total Repaid</h6>
          <small class="text-muted">Advances Repaid</small>
        </div>
        <div class="summary-body">
          <div class="d-flex justify-content-between align-items-end">
            <div>
              <h3 class="mb-0" id="totalReceivableRecovered">₹0.00</h3>
            </div>
            <div class="summary-icon">
              <i class="fas fa-credit-card text-warning"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Tabs Navigation -->
  <div class="row mb-4">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <ul class="nav nav-tabs card-header-tabs" id="loansTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link {{ $activeTab == 'payable' ? 'active' : '' }}"
                id="payable-tab" data-bs-toggle="tab" data-bs-target="#payable"
                type="button" role="tab" onclick="switchTab('payable')">
                <i class="fas fa-arrow-up me-2"></i>Loans to be Paid
                <span class="badge bg-primary ms-2" id="payableBadge">0</span>
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link {{ $activeTab == 'receivable' ? 'active' : '' }}"
                id="receivable-tab" data-bs-toggle="tab" data-bs-target="#receivable"
                type="button" role="tab" onclick="switchTab('receivable')">
                <i class="fas fa-arrow-down me-2"></i>Loans to be Recovered
                <span class="badge bg-info ms-2" id="receivableBadge">0</span>
              </button>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <!-- Filter Section -->
  <div class="filter-section mb-4">
    <div class="row g-3">
      <div class="col-md-3">
        <label class="form-label small fw-bold">Company</label>
        <select class="form-select form-select-sm" id="companyFilter">
          <option value="all">All Companies</option>
          @foreach ($companies as $company)
          <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
            {{ $company->name }}
          </option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-bold">Party Type</label>
        <select class="form-select form-select-sm" id="partyTypeFilter">
          <option value="all">All Types</option>
          <option value="employee" {{ request('party_type') == 'employee' ? 'selected' : '' }}>Employee</option>
          <option value="vendor" {{ request('party_type') == 'vendor' ? 'selected' : '' }}>Vendor</option>
          <option value="partner" {{ request('party_type') == 'partner' ? 'selected' : '' }}>Partner</option>
          <option value="other" {{ request('party_type') == 'other' ? 'selected' : '' }}>Other</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-bold">Status</label>
        <select class="form-select form-select-sm" id="statusFilter">
          <option value="all">All Status</option>
          <option value="outstanding" {{ request('status') == 'outstanding' ? 'selected' : '' }}>Outstanding</option>
          <option value="partially_recovered" {{ request('status') == 'partially_recovered' ? 'selected' : '' }}>Partially Recovered</option>
          <option value="recovered" {{ request('status') == 'recovered' ? 'selected' : '' }}>Recovered</option>
          <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-bold">Date Range</label>
        <input type="text" class="form-control form-control-sm" id="dateRangeFilter"
          placeholder="Select date range" value="{{ request('date_from') && request('date_to') ? request('date_from') . ' - ' . request('date_to') : '' }}">
      </div>
    </div>
    <div class="row mt-3">
      <div class="col-md-12">
        <button class="btn btn-sm btn-primary" onclick="applyFilters()">
          <i class="fas fa-filter"></i> Apply Filters
        </button>
        <button class="btn btn-sm btn-secondary" onclick="resetFilters()">
          <i class="fas fa-redo"></i> Reset
        </button>
      </div>
    </div>
  </div>

  <!-- Tab Content -->
  <div class="tab-content" id="loansTabContent">
    <!-- Tab 1: Loans to be Paid (Advances given by company) -->
    <div class="tab-pane fade {{ $activeTab == 'payable' ? 'show active' : '' }}" id="payable" role="tabpanel">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">
            <i class="fas fa-arrow-up text-primary me-2"></i>Loans to be Paid
            <small class="text-muted ms-2">(Advances given by company to others)</small>
          </h5>
          <div class="card-tools">
            <button class="btn btn-sm btn-primary" onclick="openAddAdvanceModal('payable')">
              <i class="fas fa-plus"></i> Issue New Advance
            </button>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover" id="payableTable">
              <thead>
                <tr>
                  <th>Ref No</th>
                  <th>Party</th>
                  <th>Type</th>
                  <th>Company</th>
                  <th>Amount Given</th>
                  <th>Recovered</th>
                  <th>Outstanding</th>
                  <th>Date Given</th>
                  <th>Due Date</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="payableTableBody">
                @foreach ($advances as $advance)
                @if($advance->transaction_type == 'recoverable_advance' && $advance->direction == 'OUT')
                <tr data-id="{{ $advance->id }}">
                  <td>{{ $advance->reference_number ?? 'N/A' }}</td>
                  <td>{{ $advance->party->name ?? 'N/A' }}</td>
                  <td>
                    <span class="badge bg-info">
                      {{ ucfirst($advance->party_type) }}
                    </span>
                  </td>
                  <td>{{ $advance->company->name ?? 'N/A' }}</td>
                  <td>₹{{ number_format($advance->amount, 2) }}</td>
                  <td>₹{{ number_format($advance->recovered_amount, 2) }}</td>
                  <td>
                    <span class="fw-bold text-warning">₹{{ number_format($advance->outstanding_amount, 2) }}</span>
                  </td>
                  <td>{{ $advance->transaction_date->format('d M Y') }}</td>
                  <td>
                    @if($advance->expected_recovery_date)
                    {{ $advance->expected_recovery_date->format('d M Y') }}
                    @else
                    N/A
                    @endif
                  </td>
                  <td>
                    @php
                    $statusClass = [
                    'outstanding' => 'warning',
                    'partially_recovered' => 'info',
                    'recovered' => 'success',
                    'overdue' => 'danger',
                    ][$advance->status] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $statusClass }}">
                      {{ ucfirst(str_replace('_', ' ', $advance->status)) }}
                    </span>
                  </td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <button class="btn btn-outline-info" onclick="viewAdvance({{ $advance->id }})">
                        <i class="fas fa-eye"></i>
                      </button>
                      @if($advance->outstanding_amount > 0)
                      <button class="btn btn-outline-success" onclick="openRecoveryModal({{ $advance->id }})">
                        <i class="fas fa-undo"></i> Recover
                      </button>
                      @endif
                      <button class="btn btn-outline-primary" onclick="editAdvance({{ $advance->id }})">
                        <i class="fas fa-edit"></i>
                      </button>
                      @if($advance->recoveries()->count() == 0)
                      <button class="btn btn-outline-danger" onclick="deleteAdvance({{ $advance->id }})">
                        <i class="fas fa-trash"></i>
                      </button>
                      @endif
                    </div>
                  </td>
                </tr>
                @endif
                @endforeach
              </tbody>
            </table>
          </div>

          @if($advances->where('transaction_type', 'recoverable_advance')->where('direction', 'OUT')->count() == 0)
          <div class="text-center py-5">
            <i class="fas fa-hand-holding-usd fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No advances to be paid</h5>
            <p class="text-muted">Click "Issue New Advance" to add loans given by company</p>
          </div>
          @endif
        </div>
      </div>
    </div>

    <!-- Tab 2: Loans to be Recovered (Advances received by company) -->
    <div class="tab-pane fade {{ $activeTab == 'receivable' ? 'show active' : '' }}" id="receivable" role="tabpanel">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">
            <i class="fas fa-arrow-down text-info me-2"></i>Loans to be Recovered
            <small class="text-muted ms-2">(Advances received by company from others)</small>
          </h5>
          <div class="card-tools">
            <button class="btn btn-sm btn-info" onclick="openAddAdvanceModal('receivable')">
              <i class="fas fa-plus"></i> Record New Receivable
            </button>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover" id="receivableTable">
              <thead>
                <tr>
                  <th>Ref No</th>
                  <th>Party</th>
                  <th>Type</th>
                  <th>Company</th>
                  <th>Amount Received</th>
                  <th>Repaid</th>
                  <th>Outstanding</th>
                  <th>Date Received</th>
                  <th>Due Date</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="receivableTableBody">
                @foreach ($advances as $advance)
                @if($advance->transaction_type == 'receivable_advance' && $advance->direction == 'IN')
                <tr data-id="{{ $advance->id }}">
                  <td>{{ $advance->reference_number ?? 'N/A' }}</td>
                  <td>{{ $advance->party->name ?? 'N/A' }}</td>
                  <td>
                    <span class="badge bg-info">
                      {{ ucfirst($advance->party_type) }}
                    </span>
                  </td>
                  <td>{{ $advance->company->name ?? 'N/A' }}</td>
                  <td>₹{{ number_format($advance->amount, 2) }}</td>
                  <td>₹{{ number_format($advance->recovered_amount, 2) }}</td>
                  <td>
                    <span class="fw-bold text-info">₹{{ number_format($advance->outstanding_amount, 2) }}</span>
                  </td>
                  <td>{{ $advance->transaction_date->format('d M Y') }}</td>
                  <td>
                    @if($advance->expected_recovery_date)
                    {{ $advance->expected_recovery_date->format('d M Y') }}
                    @else
                    N/A
                    @endif
                  </td>
                  <td>
                    @php
                    $statusClass = [
                    'outstanding' => 'warning',
                    'partially_recovered' => 'info',
                    'recovered' => 'success',
                    'overdue' => 'danger',
                    ][$advance->status] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $statusClass }}">
                      {{ ucfirst(str_replace('_', ' ', $advance->status)) }}
                    </span>
                  </td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <button class="btn btn-outline-info" onclick="viewAdvance({{ $advance->id }})">
                        <i class="fas fa-eye"></i>
                      </button>
                      @if($advance->outstanding_amount > 0)
                      <button class="btn btn-outline-warning" onclick="openRecoveryModal({{ $advance->id }})">
                        <i class="fas fa-credit-card"></i> Repay
                      </button>
                      @endif
                      <button class="btn btn-outline-primary" onclick="editAdvance({{ $advance->id }})">
                        <i class="fas fa-edit"></i>
                      </button>
                      @if($advance->recoveries()->count() == 0)
                      <button class="btn btn-outline-danger" onclick="deleteAdvance({{ $advance->id }})">
                        <i class="fas fa-trash"></i>
                      </button>
                      @endif
                    </div>
                  </td>
                </tr>
                @endif
                @endforeach
              </tbody>
            </table>
          </div>

          @if($advances->where('transaction_type', 'receivable_advance')->where('direction', 'IN')->count() == 0)
          <div class="text-center py-5">
            <i class="fas fa-hand-holding fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No advances to be recovered</h5>
            <p class="text-muted">Click "Record New Receivable" to add loans received by company</p>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  <!-- Pagination -->
  @if($advances->count() > 0)
  <div class="d-flex justify-content-between align-items-center mt-3">
    <div class="text-muted" id="paginationInfo">
      Showing {{ $advances->firstItem() }} to {{ $advances->lastItem() }} of {{ $advances->total() }} entries
    </div>
    <nav>
      {{ $advances->links() }}
    </nav>
  </div>
  @endif
</div>

<!-- Add Advance Modal - Updated for both types -->
<div class="modal fade" id="addAdvanceModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addModalTitle">Issue New Advance</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="addAdvanceForm" enctype="multipart/form-data">
        @csrf
        <input type="hidden" id="advanceType" name="advance_type" value="payable">

        <div class="modal-body">
          <div class="alert" id="advanceTypeAlert" style="display: none;">
            <!-- Alert message will be inserted here -->
          </div>

          <div class="row g-3">
            <!-- Party Selection -->
            <div class="col-md-6">
              <label class="form-label">Party <span class="text-danger">*</span></label>
              <select class="form-select" id="partySelect" name="party_id" required>
                <option value="">Select Party</option>
                @foreach($parties as $party)
                <option value="{{ $party->id }}" data-type="{{ $party->type }}">
                  {{ $party->name }} ({{ ucfirst($party->type) }})
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Party Type <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="partyTypeDisplay" readonly>
              <input type="hidden" id="partyTypeInput" name="party_type">
            </div>

            <!-- Amount & Dates -->
            <div class="col-md-6">
              <label class="form-label" id="amountLabel">Amount (₹) <span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="amount" required min="0" step="0.01">
            </div>
            <div class="col-md-6">
              <label class="form-label">Reference Number</label>
              <input type="text" class="form-control" name="reference_number"
                placeholder="Optional">
            </div>

            <div class="col-md-6">
              <label class="form-label" id="dateLabel">Transaction Date <span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="transaction_date"
                value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label" id="dueDateLabel">Expected Date</label>
              <input type="date" class="form-control" name="expected_date">
            </div>

            <!-- Company -->
            <div class="col-md-6">
              <label class="form-label">Company <span class="text-danger">*</span></label>
              <select class="form-select" name="company_id" required>
                <option value="">Select Company</option>
                @foreach($companies as $company)
                <option value="{{ $company->id }}">{{ $company->name }}</option>
                @endforeach
              </select>
            </div>

            <!-- Purpose & Comments -->
            <div class="col-md-12">
              <label class="form-label" id="purposeLabel">Purpose <span class="text-danger">*</span></label>
              <textarea class="form-control" name="purpose" rows="2" required
                placeholder="Describe the purpose of this advance"></textarea>
            </div>
            <div class="col-md-12">
              <label class="form-label">Comments</label>
              <textarea class="form-control" name="comments" rows="2"
                placeholder="Additional comments"></textarea>
            </div>

            <!-- Attachments -->
            <div class="col-md-12">
              <label class="form-label">Attachments (Loan Agreement, etc.)</label>
              <div id="attachmentsContainer">
                <div class="input-group mb-2">
                  <input type="file" name="attachments[]" class="form-control"
                    accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                  <button type="button" class="btn btn-outline-danger"
                    onclick="removeAttachment(this)">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
              <button type="button" class="btn btn-outline-secondary btn-sm mt-2"
                onclick="addMoreAttachment()">
                <i class="fas fa-plus me-1"></i> Add Another File
              </button>
              <small class="text-muted d-block">Supported: JPG, PNG, PDF, DOC (Max: 5MB each)</small>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="submitButton">
            <i class="fas fa-save me-2"></i>Save Advance
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Advance Modal -->
<div class="modal fade" id="editAdvanceModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Advance</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="editAdvanceForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" id="editAdvanceId" name="id">
        <div class="modal-body">
          <div class="row g-3">
            <!-- Read-only fields -->
            <div class="col-md-6">
              <label class="form-label">Party</label>
              <input type="text" class="form-control" id="editPartyName" readonly>
            </div>
            <div class="col-md-6">
              <label class="form-label">Amount (₹)</label>
              <input type="text" class="form-control" id="editAmount" readonly>
            </div>

            <!-- Editable fields -->
            <div class="col-md-6">
              <label class="form-label">Reference Number</label>
              <input type="text" class="form-control" id="editReferenceNumber"
                name="reference_number">
            </div>
            <div class="col-md-6">
              <label class="form-label">Expected Recovery Date</label>
              <input type="date" class="form-control" id="editExpectedRecoveryDate"
                name="expected_recovery_date">
            </div>

            <div class="col-md-6">
              <label class="form-label">Status <span class="text-danger">*</span></label>
              <select class="form-control" id="editStatus" name="status" required>
                <option value="outstanding">Outstanding</option>
                <option value="partially_recovered">Partially Recovered</option>
                <option value="recovered">Recovered</option>
                <option value="overdue">Overdue</option>
              </select>
            </div>

            <!-- Purpose & Comments -->
            <div class="col-md-12">
              <label class="form-label">Purpose <span class="text-danger">*</span></label>
              <textarea class="form-control" id="editPurpose" name="purpose"
                rows="2" required></textarea>
            </div>
            <div class="col-md-12">
              <label class="form-label">Comments</label>
              <textarea class="form-control" id="editComments" name="comments"
                rows="2"></textarea>
            </div>

            <!-- Existing Attachments -->
            <div class="col-md-12" id="existingAttachmentsSection" style="display: none;">
              <label class="form-label">Existing Attachments</label>
              <div id="existingAttachmentsList" class="list-group mb-3">
                <!-- Existing attachments will be loaded here -->
              </div>
            </div>

            <!-- New Attachments -->
            <div class="col-md-12">
              <label class="form-label">Add New Attachments</label>
              <div id="editAttachmentsContainer">
                <div class="input-group mb-2">
                  <input type="file" name="attachments[]" class="form-control"
                    accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                  <button type="button" class="btn btn-outline-danger"
                    onclick="removeAttachment(this, true)">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
              <button type="button" class="btn btn-outline-secondary btn-sm mt-2"
                onclick="addMoreAttachment(true)">
                <i class="fas fa-plus me-1"></i> Add Another File
              </button>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-2"></i>Update Advance
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Recovery Modal -->
<div class="modal fade" id="recoveryModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Record Recovery</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="recoveryForm" enctype="multipart/form-data">
        @csrf
        <input type="hidden" id="recoveryAdvanceId" name="advance_id">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-12">
              <label class="form-label">Advance Details</label>
              <div class="alert alert-info">
                <div class="d-flex justify-content-between">
                  <span id="recoveryPartyName"></span>
                  <span id="recoveryOutstanding" class="fw-bold"></span>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Recovery Amount (₹) <span class="text-danger">*</span></label>
              <input type="number" class="form-control" id="recoveryAmount"
                name="recovery_amount" required min="0.01" step="0.01">
              <small class="text-muted">Max: <span id="maxRecoveryAmount">0.00</span></small>
            </div>
            <div class="col-md-6">
              <label class="form-label">Recovery Date <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="recoveryDate"
                name="recovery_date" value="{{ date('Y-m-d') }}" required>
            </div>

            <div class="col-md-12">
              <label class="form-label">Comments</label>
              <textarea class="form-control" id="recoveryComments" name="comments"
                rows="2" placeholder="Recovery comments"></textarea>
            </div>

            <div class="col-md-12">
              <label class="form-label">Receipt/Attachment</label>
              <input type="file" class="form-control" id="recoveryAttachment"
                name="attachment" accept=".jpg,.jpeg,.png,.pdf">
              <small class="text-muted">Upload recovery receipt if any</small>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">
            <i class="fas fa-check me-2"></i>Record Recovery
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- View Advance Modal -->
<div class="modal fade" id="viewAdvanceModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Advance Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div class="card mb-3">
              <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Basic Information</h6>
                <div class="row">
                  <div class="col-md-6">
                    <p><strong>Reference No:</strong> <span id="viewRefNo"></span></p>
                    <p><strong>Party:</strong> <span id="viewParty"></span></p>
                    <p><strong>Type:</strong> <span id="viewType"></span></p>
                  </div>
                  <div class="col-md-6">
                    <p><strong>Company:</strong> <span id="viewCompany"></span></p>
                    <p><strong>Transaction Date:</strong> <span id="viewDate"></span></p>
                    <p><strong>Due Date:</strong> <span id="viewDueDate"></span></p>
                  </div>
                </div>
              </div>
            </div>

            <div class="card mb-3">
              <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Amount Details</h6>
                <div class="row">
                  <div class="col-md-4">
                    <p class="mb-1"><small class="text-muted">Total Amount</small></p>
                    <h4>₹<span id="viewAmount"></span></h4>
                  </div>
                  <div class="col-md-4">
                    <p class="mb-1"><small class="text-muted">Recovered</small></p>
                    <h4 class="text-success">₹<span id="viewRecovered"></span></h4>
                  </div>
                  <div class="col-md-4">
                    <p class="mb-1"><small class="text-muted">Outstanding</small></p>
                    <h4 class="text-warning">₹<span id="viewOutstanding"></span></h4>
                  </div>
                </div>
              </div>
            </div>

            <div class="card mb-3">
              <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Additional Information</h6>
                <p><strong>Purpose:</strong> <span id="viewPurpose"></span></p>
                <p><strong>Comments:</strong> <span id="viewComments"></span></p>
                <p><strong>Status:</strong> <span id="viewStatus" class="badge"></span></p>
              </div>
            </div>

            <!-- Recovery History -->
            <div class="card mb-3">
              <div class="card-header">
                <h6 class="card-subtitle mb-0">Recovery History</h6>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-sm">
                    <thead>
                      <tr>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Comments</th>
                      </tr>
                    </thead>
                    <tbody id="recoveryHistory">
                      <!-- Recovery history will be loaded here -->
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Attachments -->
            <div class="card">
              <div class="card-header">
                <h6 class="card-subtitle mb-0">Attachments</h6>
              </div>
              <div class="card-body">
                <div id="viewAttachments" class="row">
                  <!-- Attachments will be loaded here -->
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirm Action</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p id="confirmMessage">Are you sure you want to delete this advance?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmButton">Delete</button>
      </div>
    </div>
  </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
  // Global variables
  let currentAdvanceId = null;
  let currentTab = '{{ $activeTab }}';

  // Initialize date range picker
  $(document).ready(function() {
    // Initialize date range picker
    $('#dateRangeFilter').daterangepicker({
      opens: 'left',
      locale: {
        format: 'YYYY-MM-DD'
      }
    });

    // Load initial stats
    loadStats();

    // Party type auto-fill
    $('#partySelect').change(function() {
      const selected = $(this).find(':selected');
      const partyType = selected.data('type');
      $('#partyTypeDisplay').val(ucfirst(partyType));
      $('#partyTypeInput').val(partyType);
    });

    // Set initial tab
    setActiveTab(currentTab);
  });

  // Switch between tabs
  function switchTab(tab) {
    currentTab = tab;
    const url = new URL(window.location.href);
    url.searchParams.set('tab', tab);
    window.location.href = url.toString();
  }

  function setActiveTab(tab) {
    if (tab === 'receivable') {
      $('#receivable-tab').addClass('active');
      $('#payable-tab').removeClass('active');
      $('#receivable').addClass('show active');
      $('#payable').removeClass('show active');
    } else {
      $('#payable-tab').addClass('active');
      $('#receivable-tab').removeClass('active');
      $('#payable').addClass('show active');
      $('#receivable').removeClass('show active');
    }
  }

  // Open Add Advance Modal with specific type
  function openAddAdvanceModal(type = 'payable') {
    // Reset form
    $('#addAdvanceForm')[0].reset();
    $('#advanceType').val(type);
    $('#partyTypeDisplay').val('');
    $('#partyTypeInput').val('');
    $('#attachmentsContainer').html(`
        <div class="input-group mb-2">
            <input type="file" name="attachments[]" class="form-control" 
                   accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
            <button type="button" class="btn btn-outline-danger" 
                    onclick="removeAttachment(this)">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `);

    // Set default dates
    $('input[name="transaction_date"]').val(new Date().toISOString().split('T')[0]);

    // Update modal based on type
    if (type === 'receivable') {
      $('#addModalTitle').text('Record New Receivable Advance');
      $('#amountLabel').text('Amount Received (₹) *');
      $('#dateLabel').text('Date Received *');
      $('#dueDateLabel').text('Expected Repayment Date');
      $('#purposeLabel').text('Purpose of Receivable *');
      $('#submitButton').html('<i class="fas fa-save me-2"></i>Save Receivable');

      // Show alert for receivable
      $('#advanceTypeAlert').html(`
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Receivable Advance:</strong> Money received by company from others that needs to be repaid.
            </div>
        `).show();
    } else {
      $('#addModalTitle').text('Issue New Advance (Payable)');
      $('#amountLabel').text('Amount Given (₹) *');
      $('#dateLabel').text('Date Given *');
      $('#dueDateLabel').text('Expected Recovery Date');
      $('#purposeLabel').text('Purpose of Advance *');
      $('#submitButton').html('<i class="fas fa-save me-2"></i>Save Advance');

      // Show alert for payable
      $('#advanceTypeAlert').html(`
            <div class="alert alert-primary">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Payable Advance:</strong> Money given by company to others that needs to be recovered.
            </div>
        `).show();
    }

    $('#addAdvanceModal').modal('show');
  }

  // Load dashboard statistics
  function loadStats() {
    $.ajax({
      url: '{{ route("manager.loans.stats") }}',
      type: 'GET',
      success: function(response) {
        if (response.success) {
          const stats = response.data;

          // Payable advances (loans given by company)
          $('#totalPayableIssued').text('₹' + stats.total_payable_issued.toLocaleString('en-IN', {
            minimumFractionDigits: 2
          }));
          $('#totalPayableRecovered').text('₹' + stats.total_payable_recovered.toLocaleString('en-IN', {
            minimumFractionDigits: 2
          }));
          $('#payableOutstanding').text('₹' + stats.total_payable_outstanding.toLocaleString('en-IN', {
            minimumFractionDigits: 2
          }) + ' Outstanding');

          // Receivable advances (loans received by company)
          $('#totalReceivableIssued').text('₹' + stats.total_receivable_issued.toLocaleString('en-IN', {
            minimumFractionDigits: 2
          }));
          $('#totalReceivableRecovered').text('₹' + stats.total_receivable_recovered.toLocaleString('en-IN', {
            minimumFractionDigits: 2
          }));
          $('#receivableOutstanding').text('₹' + stats.total_receivable_outstanding.toLocaleString('en-IN', {
            minimumFractionDigits: 2
          }) + ' Outstanding');

          // Update tab badges
          $('#payableBadge').text(Math.round(stats.total_payable_outstanding).toLocaleString('en-IN'));
          $('#receivableBadge').text(Math.round(stats.total_receivable_outstanding).toLocaleString('en-IN'));

          // Overdue stats (combined)
          $('#overdueAmount').text('₹' + stats.overdue_amount.toLocaleString('en-IN', {
            minimumFractionDigits: 2
          }));
          $('#overdueCount').text(stats.overdue_count + ' Items');
        }
      }
    });
  }

  // Add more attachment fields
  function addMoreAttachment(isEdit = false) {
    const container = isEdit ? '#editAttachmentsContainer' : '#attachmentsContainer';
    const html = `
        <div class="input-group mb-2">
            <input type="file" name="attachments[]" class="form-control" 
                   accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
            <button type="button" class="btn btn-outline-danger" 
                    onclick="removeAttachment(this, ${isEdit})">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    $(container).append(html);
  }

  // Remove attachment field
  function removeAttachment(button, isEdit = false) {
    $(button).closest('.input-group').remove();
  }

  // Submit Add Advance Form
  $('#addAdvanceForm').submit(function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    $.ajax({
      url: '{{ route("manager.loans.store") }}',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      beforeSend: function() {
        showLoading();
      },
      success: function(response) {
        if (response.success) {
          toastr.success(response.message);
          $('#addAdvanceModal').modal('hide');
          reloadTable();
          loadStats();
        } else {
          toastr.error(response.message);
        }
      },
      error: function(xhr) {
        const errors = xhr.responseJSON.errors;
        if (errors) {
          Object.keys(errors).forEach(key => {
            toastr.error(errors[key][0]);
          });
        } else {
          toastr.error('An error occurred. Please try again.');
        }
      },
      complete: function() {
        hideLoading();
      }
    });
  });

  // Apply filters
  function applyFilters() {
    const companyId = $('#companyFilter').val();
    const partyType = $('#partyTypeFilter').val();
    const status = $('#statusFilter').val();
    const dateRange = $('#dateRangeFilter').val();

    let url = '{{ route("manager.loans.index") }}?tab=' + currentTab + '&';

    if (companyId !== 'all') url += 'company_id=' + companyId + '&';
    if (partyType !== 'all') url += 'party_type=' + partyType + '&';
    if (status !== 'all') url += 'status=' + status + '&';
    if (dateRange) {
      const dates = dateRange.split(' - ');
      if (dates.length === 2) {
        url += 'date_from=' + dates[0] + '&date_to=' + dates[1] + '&';
      }
    }

    window.location.href = url;
  }

  // Reset filters
  function resetFilters() {
    window.location.href = '{{ route("manager.loans.index") }}?tab=' + currentTab;
  }

  // Reload table
  function reloadTable() {
    $.ajax({
      url: window.location.href,
      type: 'GET',
      success: function(data) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(data, 'text/html');

        // Update active tab content
        if (currentTab === 'receivable') {
          const newReceivableBody = doc.getElementById('receivableTableBody')?.innerHTML || '';
          const newReceivableEmpty = doc.querySelector('#receivable .text-center')?.outerHTML || '';
          $('#receivableTableBody').html(newReceivableBody);
          if (newReceivableBody === '') {
            $('#receivable .card-body').append(newReceivableEmpty);
          }
        } else {
          const newPayableBody = doc.getElementById('payableTableBody')?.innerHTML || '';
          const newPayableEmpty = doc.querySelector('#payable .text-center')?.outerHTML || '';
          $('#payableTableBody').html(newPayableBody);
          if (newPayableBody === '') {
            $('#payable .card-body').append(newPayableEmpty);
          }
        }

        // Update pagination
        const newPagination = doc.querySelector('.pagination')?.innerHTML || '';
        const newPaginationInfo = doc.getElementById('paginationInfo')?.innerHTML || '';
        $('.pagination').html(newPagination);
        $('#paginationInfo').html(newPaginationInfo);
      }
    });
  }

  // Utility functions
  function ucfirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
  }

  function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-IN', {
      day: '2-digit',
      month: 'short',
      year: 'numeric'
    });
  }

  function showLoading() {
    $('body').append(`
        <div id="loadingOverlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
              background: rgba(0,0,0,0.3); z-index: 9999; display: flex; align-items: center; justify-content: center;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `);
  }

  function hideLoading() {
    $('#loadingOverlay').remove();
  }


  // Apply filters
  function applyFilters() {
    const companyId = $('#companyFilter').val();
    const partyType = $('#partyTypeFilter').val();
    const status = $('#statusFilter').val();
    const dateRange = $('#dateRangeFilter').val();

    let url = '{{ route("manager.loans.index") }}?';

    if (companyId !== 'all') url += 'company_id=' + companyId + '&';
    if (partyType !== 'all') url += 'party_type=' + partyType + '&';
    if (status !== 'all') url += 'status=' + status + '&';
    if (dateRange) {
      const dates = dateRange.split(' - ');
      if (dates.length === 2) {
        url += 'date_from=' + dates[0] + '&date_to=' + dates[1] + '&';
      }
    }

    window.location.href = url;
  }

  // Reset filters
  function resetFilters() {
    window.location.href = '{{ route("manager.loans.index") }}';
  }

  // Reload table
  function reloadTable() {
    $.ajax({
      url: window.location.href,
      type: 'GET',
      success: function(data) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(data, 'text/html');
        const newBody = doc.getElementById('advancesTableBody').innerHTML;
        const newPagination = doc.querySelector('.pagination').innerHTML;
        const newPaginationInfo = doc.getElementById('paginationInfo').innerHTML;

        $('#advancesTableBody').html(newBody);
        $('.pagination').html(newPagination);
        $('#paginationInfo').html(newPaginationInfo);
      }
    });
  }

  // Utility functions
  function ucfirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
  }

  function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-IN', {
      day: '2-digit',
      month: 'short',
      year: 'numeric'
    });
  }

  $(function() {
    $('[data-bs-toggle="tooltip"]').tooltip();
  });

  // View Advance
  function viewAdvance(id) {
    $.ajax({
      url: '/manager/loans/' + id,
      type: 'GET',
      beforeSend: showLoading,
      success: function(response) {
        if (response.success) {
          const advance = response.data;
          $('#viewRefNo').text(advance.reference_number || 'N/A');
          $('#viewParty').text(advance.party ? advance.party.name : 'N/A');
          $('#viewType').text(ucfirst(advance.party_type));
          $('#viewCompany').text(advance.company ? advance.company.name : 'N/A');
          $('#viewDate').text(formatDate(advance.transaction_date));
          $('#viewDueDate').text(advance.expected_recovery_date ? formatDate(advance.expected_recovery_date) : 'N/A');
          $('#viewAmount').text(Number(advance.amount).toLocaleString('en-IN', {minimumFractionDigits: 2}));
          $('#viewRecovered').text(Number(advance.recovered_amount).toLocaleString('en-IN', {minimumFractionDigits: 2}));
          $('#viewOutstanding').text(Number(advance.outstanding_amount).toLocaleString('en-IN', {minimumFractionDigits: 2}));
          $('#viewPurpose').text(advance.purpose || 'N/A');
          $('#viewComments').text(advance.comments || 'N/A');
          
          let statusClass = 'secondary';
          if (advance.status === 'outstanding') statusClass = 'warning';
          else if (advance.status === 'partially_recovered') statusClass = 'info';
          else if (advance.status === 'recovered') statusClass = 'success';
          else if (advance.status === 'overdue') statusClass = 'danger';
          
          $('#viewStatus').removeClass().addClass('badge bg-' + statusClass).text(ucfirst(advance.status.replace('_', ' ')));

          // Recovery history
          let recoveryHtml = '';
          if (advance.recoveries && advance.recoveries.length > 0) {
            advance.recoveries.forEach(rec => {
              recoveryHtml += `
                <tr>
                  <td>${formatDate(rec.transaction_date)}</td>
                  <td>₹${Number(rec.amount).toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                  <td>${rec.comments || 'N/A'}</td>
                </tr>
              `;
            });
          } else {
            recoveryHtml = '<tr><td colspan="3" class="text-center text-muted">No recovery records found.</td></tr>';
          }
          $('#recoveryHistory').html(recoveryHtml);

          // Attachments
          let attachmentsHtml = '';
          if (advance.attachments && advance.attachments.length > 0) {
            advance.attachments.forEach(att => {
              attachmentsHtml += `
                <div class="col-md-6 mb-2">
                  <div class="card shadow-sm border-0 bg-light">
                    <div class="card-body p-2 d-flex justify-content-between align-items-center">
                      <div class="text-truncate">
                        <i class="fas fa-file-alt text-primary me-2"></i>
                        <span class="small">${att.file_name}</span>
                      </div>
                      <a href="/storage/${att.file_path}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2">
                        <i class="fas fa-download"></i>
                      </a>
                    </div>
                  </div>
                </div>
              `;
            });
          } else {
            attachmentsHtml = '<div class="col-12 text-muted small">No attachments available.</div>';
          }
          $('#viewAttachments').html(attachmentsHtml);

          $('#viewAdvanceModal').modal('show');
        } else {
          toastr.error('Failed to load advance details.');
        }
      },
      error: function() {
        toastr.error('An error occurred.');
      },
      complete: hideLoading
    });
  }

  // Edit Advance
  function editAdvance(id) {
    $.ajax({
      url: '/manager/loans/' + id,
      type: 'GET',
      beforeSend: showLoading,
      success: function(response) {
        if (response.success) {
          const advance = response.data;
          $('#editAdvanceId').val(advance.id);
          $('#editPartyName').val(advance.party ? advance.party.name : 'N/A');
          $('#editAmount').val(Number(advance.amount).toLocaleString('en-IN', {minimumFractionDigits: 2}));
          $('#editReferenceNumber').val(advance.reference_number || '');
          if (advance.expected_recovery_date) {
            $('#editExpectedRecoveryDate').val(advance.expected_recovery_date.split('T')[0]);
          } else {
            $('#editExpectedRecoveryDate').val('');
          }
          $('#editStatus').val(advance.status);
          $('#editPurpose').val(advance.purpose || '');
          $('#editComments').val(advance.comments || '');
          
          $('#editAttachmentsContainer').html(`
            <div class="input-group mb-2">
              <input type="file" name="attachments[]" class="form-control" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
              <button type="button" class="btn btn-outline-danger" onclick="removeAttachment(this, true)"><i class="fas fa-times"></i></button>
            </div>
          `);

          let existingAttachmentsHtml = '';
          if (advance.attachments && advance.attachments.length > 0) {
            advance.attachments.forEach(att => {
              existingAttachmentsHtml += `
                <div class="list-group-item d-flex justify-content-between align-items-center p-2">
                  <span class="small text-truncate"><i class="fas fa-paperclip me-2 text-muted"></i>${att.file_name}</span>
                  <a href="/storage/${att.file_path}" target="_blank" class="btn btn-sm btn-link py-0"><i class="fas fa-external-link-alt"></i></a>
                </div>
              `;
            });
            $('#existingAttachmentsList').html(existingAttachmentsHtml);
            $('#existingAttachmentsSection').show();
          } else {
            $('#existingAttachmentsSection').hide();
          }

          $('#editAdvanceModal').modal('show');
        }
      },
      error: function() {
        toastr.error('Failed to load advance details for editing.');
      },
      complete: hideLoading
    });
  }

  // Submit Edit Advance Form
  $('#editAdvanceForm').submit(function(e) {
    e.preventDefault();
    const id = $('#editAdvanceId').val();
    const formData = new FormData(this);
    
    // We are simulating PUT using POST + _method
    $.ajax({
      url: '/manager/loans/' + id,
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      beforeSend: showLoading,
      success: function(response) {
        if (response.success) {
          toastr.success(response.message);
          $('#editAdvanceModal').modal('hide');
          reloadTable();
          loadStats();
        } else {
          toastr.error(response.message);
        }
      },
      error: function(xhr) {
        const errors = xhr.responseJSON?.errors;
        if (errors) {
          Object.keys(errors).forEach(key => toastr.error(errors[key][0]));
        } else {
          toastr.error('Update failed.');
        }
      },
      complete: hideLoading
    });
  });

  // Open Recovery Modal
  function openRecoveryModal(id) {
    $.ajax({
      url: '/manager/loans/' + id,
      type: 'GET',
      beforeSend: showLoading,
      success: function(response) {
        if (response.success) {
          const advance = response.data;
          $('#recoveryAdvanceId').val(advance.id);
          $('#recoveryPartyName').text(advance.party ? advance.party.name : 'Unknown Party');
          
          const outstanding = Number(advance.outstanding_amount);
          $('#recoveryOutstanding').text('Outstanding: ₹' + outstanding.toLocaleString('en-IN', {minimumFractionDigits: 2}));
          
          $('#recoveryAmount').val('');
          $('#recoveryAmount').attr('max', outstanding);
          $('#maxRecoveryAmount').text(outstanding.toLocaleString('en-IN', {minimumFractionDigits: 2}));
          
          $('#recoveryDate').val(new Date().toISOString().split('T')[0]);
          $('#recoveryComments').val('');
          $('#recoveryAttachment').val('');
          
          $('#recoveryModal').modal('show');
        }
      },
      error: function() {
        toastr.error('Failed to load advance details for recovery.');
      },
      complete: hideLoading
    });
  }

  // Submit Recovery Form
  $('#recoveryForm').submit(function(e) {
    e.preventDefault();
    const id = $('#recoveryAdvanceId').val();
    const formData = new FormData(this);
    
    $.ajax({
      url: '/manager/loans/' + id + '/recovery',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      beforeSend: showLoading,
      success: function(response) {
        if (response.success) {
          toastr.success(response.message);
          $('#recoveryModal').modal('hide');
          reloadTable();
          loadStats();
        } else {
          toastr.error(response.message);
        }
      },
      error: function(xhr) {
        const errors = xhr.responseJSON?.errors;
        if (errors) {
          Object.keys(errors).forEach(key => toastr.error(errors[key][0]));
        } else {
          toastr.error(xhr.responseJSON?.message || 'Recovery submission failed.');
        }
      },
      complete: hideLoading
    });
  });

  // Delete Advance
  function deleteAdvance(id) {
    currentAdvanceId = id;
    $('#confirmModal').modal('show');
  }

  $('#confirmButton').click(function() {
    if (!currentAdvanceId) return;
    
    $.ajax({
      url: '/manager/loans/' + currentAdvanceId,
      type: 'DELETE',
      data: {
        _token: '{{ csrf_token() }}'
      },
      beforeSend: showLoading,
      success: function(response) {
        if (response.success) {
          toastr.success(response.message);
          $('#confirmModal').modal('hide');
          reloadTable();
          loadStats();
        } else {
          toastr.error(response.message);
        }
      },
      error: function(xhr) {
        toastr.error(xhr.responseJSON?.message || 'Failed to delete advance.');
      },
      complete: hideLoading
    });
  });

</script>
@endsection