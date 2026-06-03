@extends('Manager.layouts.app')
@section('content')
    <section id="nonstandard-expenses-page" class="page">
        <!-- Add Expense Form Card -->
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Add Non-standard Expense</h6>
                </div>
                <form id="addExpenseForm" class="row g-3">
                    @csrf
                    <div class="col-md-3">
                        <label class="form-label small">Company *</label>
                        <select class="form-select form-select-sm" name="company_id" required>
                            <option value="">Select Company</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Date *</label>
                        <input type="date" class="form-control form-control-sm" name="due_date"
                            value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Expense Name *</label>
                        <input type="text" class="form-control form-control-sm" name="name"
                            placeholder="e.g. Repair work" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Category *</label>
                        <select class="form-select form-select-sm" name="category" required>
                            <option value="">Select Category</option>
                            <option value="repair">Repair</option>
                            <option value="legal">Legal</option>
                            <option value="marketing">Marketing</option>
                            <option value="miscellaneous">Miscellaneous</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Amount *</label>
                        <input type="number" class="form-control form-control-sm" name="planned_amount" placeholder="₹"
                            step="0.01" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Party/Vendor Name</label>
                        <input type="text" class="form-control form-control-sm" name="party_name" placeholder="Optional">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Status *</label>
                        <select class="form-select form-select-sm" name="status" required>
                            <option value="upcoming">Upcoming</option>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Payment Date (if paid)</label>
                        <input type="date" class="form-control form-control-sm" name="paid_date">
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Purpose/Notes</label>
                        <textarea class="form-control form-control-sm" name="purpose_comment" rows="2" placeholder="Any extra details"></textarea>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-save"></i> Save Expense
                        </button>
                        <button type="reset" class="btn btn-sm btn-outline-secondary">
                            Clear
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <form id="filterForm" class="d-flex flex-wrap gap-2 align-items-end">
                    <div>
                        <label class="form-label small mb-1">Company</label>
                        <select class="form-select form-select-sm" name="company" id="filterCompany">
                            <option value="">All Companies</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label small mb-1">Category</label>
                        <select class="form-select form-select-sm" name="category" id="filterCategory">
                            <option value="">All Categories</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category }}">{{ ucfirst($category) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label small mb-1">Status</label>
                        <select class="form-select form-select-sm" name="status" id="filterStatus">
                            <option value="">All Status</option>
                            <option value="upcoming">Upcoming</option>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary" onclick="applyFilters()">
                        Apply Filters
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetFilters()">
                        Reset
                    </button>
                </form>
            </div>
        </div>

        <!-- Expenses List Card -->
        <div class="card shadow-sm">
            <div class="card-header">
                Non-standard Expense List
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Company</th>
                                <th>Expense Name</th>
                                <th>Category</th>
                                <th class="text-end">Amount</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="expensesTableBody">
                            @forelse($expenses as $expense)
                                <tr data-id="{{ $expense->id }}">
                                    <td>{{ date('d-m-Y', strtotime($expense->created_at)) }}</td>
                                    <td>{{ $expense->company->name ?? 'N/A' }}</td>
                                    <td>{{ $expense->name }}</td>
                                    <td>{{ ucfirst($expense->category) }}</td>
                                    <td class="text-end">₹ {{ number_format($expense->planned_amount, 2) }}</td>
                                    <td>{{ date('d-m-Y', strtotime($expense->due_date)) }}</td>
                                    <td>
                                        @php
                                            $statusBadge =
                                                [
                                                    'paid' => 'badge-status-paid',
                                                    'pending' => 'badge-status-pending',
                                                    'upcoming' => 'badge-status-upcoming',
                                                ][$expense->status] ?? 'badge-status-upcoming';
                                        @endphp
                                        <span class="badge {{ $statusBadge }}">{{ ucfirst($expense->status) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-secondary"
                                                onclick="editExpense({{ $expense->id }})">
                                                Edit
                                            </button>
                                            @if ($expense->status !== 'paid')
                                                <button class="btn btn-outline-success"
                                                    onclick="markAsPaid({{ $expense->id }})">
                                                    Mark Paid
                                                </button>
                                            @endif
                                            <button class="btn btn-outline-danger"
                                                onclick="deleteExpense({{ $expense->id }})">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-receipt fa-2x mb-2"></i><br>
                                            No non-standard expenses found.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- Edit Expense Modal -->
    <div class="modal fade" id="editExpenseModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Non-standard Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editExpenseForm">
                    @csrf
                    <input type="hidden" id="editExpenseId" name="id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Company *</label>
                            <select class="form-select form-select-sm" id="editCompanyId" name="company_id" required>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Expense Name *</label>
                            <input type="text" class="form-control form-control-sm" id="editName" name="name"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category *</label>
                            <select class="form-select form-select-sm" id="editCategory" name="category" required>
                                <option value="repair">Repair</option>
                                <option value="legal">Legal</option>
                                <option value="marketing">Marketing</option>
                                <option value="miscellaneous">Miscellaneous</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount *</label>
                            <input type="number" class="form-control form-control-sm" id="editAmount"
                                name="planned_amount" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Due Date *</label>
                            <input type="date" class="form-control form-control-sm" id="editDueDate" name="due_date"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Party/Vendor Name</label>
                            <input type="text" class="form-control form-control-sm" id="editPartyName"
                                name="party_name">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status *</label>
                            <select class="form-select form-select-sm" id="editStatus" name="status" required>
                                <option value="upcoming">Upcoming</option>
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Purpose/Notes</label>
                            <textarea class="form-control form-control-sm" id="editPurpose" name="purpose_comment" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Add Expense Form Submission
        document.getElementById('addExpenseForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            submitBtn.disabled = true;

            fetch('{{ route('non-standard-expenses.store') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        this.reset();
                        window.location.reload();
                    } else {
                        let errorMessage = 'Error saving expense';
                        if (data.errors) {
                            errorMessage = Object.values(data.errors).flat().join('\n');
                        }
                        alert(errorMessage);
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error saving expense');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
        });

        function applyFilters() {
            const company = document.getElementById('filterCompany').value;
            const category = document.getElementById('filterCategory').value;
            const status = document.getElementById('filterStatus').value;

            const params = new URLSearchParams();
            if (company) params.append('company', company);
            if (category) params.append('category', category);
            if (status) params.append('status', status);

            window.location.href = '{{ route('non-standard-expenses.index') }}?' + params.toString();
        }

        function resetFilters() {
            document.getElementById('filterCompany').value = '';
            document.getElementById('filterCategory').value = '';
            document.getElementById('filterStatus').value = '';
            applyFilters();
        }

        function editExpense(expenseId) {
            fetch(`https://xhtmlreviews.in/beta-finance/manager/non-standard-expenses/${expenseId}/edit`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const expense = data.expense;

                        document.getElementById('editExpenseId').value = expense.id;
                        document.getElementById('editCompanyId').value = expense.company_id;
                        document.getElementById('editName').value = expense.name;
                        document.getElementById('editCategory').value = expense.category;
                        document.getElementById('editAmount').value = expense.planned_amount;
                        document.getElementById('editDueDate').value = expense.due_date;
                        document.getElementById('editPartyName').value = expense.party_name || '';
                        document.getElementById('editStatus').value = expense.status;
                        document.getElementById('editPurpose').value = expense.purpose_comment || '';

                        const modal = new bootstrap.Modal(document.getElementById('editExpenseModal'));
                        modal.show();
                    } else {
                        alert('Error loading expense data');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading expense data');
                });
        }

        // Edit Expense Form Submission
        document.getElementById('editExpenseForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const expenseId = document.getElementById('editExpenseId').value;
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            submitBtn.disabled = true;

            fetch(`https://xhtmlreviews.in/beta-finance/manager/non-standard-expenses/${expenseId}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        const modal = bootstrap.Modal.getInstance(document.getElementById('editExpenseModal'));
                        modal.hide();
                        window.location.reload();
                    } else {
                        alert(data.message || 'Error updating expense');
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating expense');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
        });

        function markAsPaid(expenseId) {
            if (confirm('Mark this expense as paid?')) {
                fetch(`https://xhtmlreviews.in/beta-finance/manager/non-standard-expenses/${expenseId}/mark-paid`, {
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
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error marking as paid');
                    });
            }
        }

        function deleteExpense(expenseId) {
            if (confirm('Are you sure you want to delete this expense?')) {
                fetch(`https://xhtmlreviews.in/beta-finance/manager/non-standard-expenses/${expenseId}`, {
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
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error deleting expense');
                    });
            }
        }
    </script>
@endsection
