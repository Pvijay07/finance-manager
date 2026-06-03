@extends('Manager.layouts.app')
@section('content')
    <div id="expense-types">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <div class="table-container">
            <div class="table-header">
                <div class="table-title">Expense Types Management</div>
                <div class="table-actions">
                    <button class="btn btn-outline">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    {{-- <button class="btn btn-primary" id="add-expense-type-btn">
                        <i class="fas fa-plus"></i> Add Expense Type
                    </button> --}}
                </div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Expense Type</th>
                        <th>Category</th>
                        <th>Amount Type</th>
                        <th>Default Amount</th>
                        <th>Applicable Companies</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($expenseTypes as $expenseType)
                        <tr>
                            <td>{{ $expenseType->name }}</td>
                            <td>{{ $expenseType->category }}</td>
                            <td><span class="badge badge-primary">{{ $expenseType->amount_type }}</span></td>
                            <td>₹{{ $expenseType->default_amount }}</td>
                            <td>{{ $expenseType->company_names }}</td>
                            <td><span class="status active">{{ $expenseType->status }}</span></td>
                            <td>
                                <button class="btn btn-outline" style="padding: 5px 10px; font-size: 0.8rem;">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">Expense Type Settings</div>
            </div>
            <div style="padding: 20px;">
                <div class="form-group">
                    <label class="form-label">Default Reminder Days</label>
                    <input type="number" class="form-control" value="7" min="1" max="30">
                    <div style="font-size: 0.9rem; color: var(--gray); margin-top: 5px;">
                        Number of days before due date to send reminders
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Auto-Generation Day</label>
                    <select class="form-control">
                        <option>1st of each month</option>
                        <option>5th of each month</option>
                        <option selected>10th of each month</option>
                        <option>15th of each month</option>
                    </select>
                    <div style="font-size: 0.9rem; color: var(--gray); margin-top: 5px;">
                        When standard expenses are auto-generated for the month
                    </div>
                </div>
                <div class="form-group">
                    <button class="btn btn-primary">Save Settings</button>
                </div>
            </div>
        </div>
    </div>

    <div class="custom-modal-overlay" id="expense-type-modal">
        <div class="modal">

            <!-- Modal Header -->
            <div class="modal-header">
                <h2 class="modal-title" id="add-expense-type-modal">Add Expense Type</h2>
                <button class="modal-close" id="close-modal">&times;</button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <form id="expense-type-form">

                    <!-- Expense Type Name -->
                    <div class="form-group">
                        <label class="form-label" for="expense-name">Expense Type Name</label>
                        <input type="text" class="form-control" id="expense-name" placeholder="Enter expense type"
                            required>
                    </div>

                    <!-- Category -->
                    <div class="form-group">
                        <label class="form-label" for="expense-category">Category</label>
                        <select class="form-control" id="expense-category" required>
                            <option value="">Select Category</option>
                            <option value="Facility">Facility</option>
                            <option value="Personnel">Personnel</option>
                            <option value="Utilities">Utilities</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Travel">Travel</option>
                            <option value="Equipment">Equipment</option>
                        </select>
                    </div>

                    <!-- Amount Type -->
                    <div class="form-group">
                        <label class="form-label" for="amount-type">Amount Type</label>
                        <select class="form-control" id="amount-type" required>
                            <option value="">Select Amount Type</option>
                            <option value="Fixed">Fixed</option>
                            <option value="Adjustable">Adjustable</option>
                            <option value="Variable">Variable</option>
                        </select>
                    </div>

                    <!-- Default Amount -->
                    <div class="form-group">
                        <label class="form-label" for="default-amount">Default Amount (₹)</label>
                        <input type="number" class="form-control" id="default-amount" min="0" step="0.01"
                            placeholder="0.00" required>
                    </div>

                    <!-- Reminder Days -->
                    <div class="form-group">
                        <label class="form-label" for="reminder-days">Reminder Days</label>
                        <input type="number" class="form-control" id="reminder-days" min="1" max="30"
                            value="7" required>
                        <small class="form-hint">Number of days before due date to send reminders</small>
                    </div>

                    <!-- Applicable Companies -->
                    <div class="form-group">
                        <label class="form-label">Applicable Companies</label>

                        <div class="select-all">
                            <input type="checkbox" id="select-all-companies">
                            <label for="select-all-companies">Select All Companies</label>
                        </div>

                        <div class="checkbox-group" id="companies-checkbox-group">
                            @foreach ($companies as $company)
                                <div class="checkbox-item">
                                    <input type="checkbox" id="company-{{ $company->id }}" name="companies[]"
                                        value="{{ $company->id }}">
                                    <label for="company-{{ $company->id }}">{{ $company->name }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <div class="radio-group">
                            <label>
                                <input type="radio" name="status" value="Active" checked> Active
                            </label>
                            <label style="margin-left: 20px;">
                                <input type="radio" name="status" value="Inactive"> Inactive
                            </label>
                        </div>
                    </div>

                </form>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancel-btn">Cancel</button>
                <button class="btn btn-primary" id="save-expense-type">Save Expense Type</button>
            </div>

        </div>
    </div>



    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal functionality
            const modal = document.querySelector('.custom-modal-overlay');
            const addBtn = document.getElementById('add-expense-type-btn');
            const closeBtn = document.getElementById('close-modal');
            const cancelBtn = document.getElementById('cancel-btn');
            const saveBtn = document.getElementById('save-expense-type');
            const form = document.getElementById('expense-type-form');

            // Check if elements exist
            if (!modal || !addBtn || !closeBtn || !cancelBtn || !saveBtn || !form) {
                console.error('Required elements not found');
                return;
            }

            // Open modal
            addBtn.addEventListener('click', () => {
                modal.style.display = 'flex';
            });

            // Close modal
            closeBtn.addEventListener('click', closeModal);
            cancelBtn.addEventListener('click', closeModal);

            // Close on overlay click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeModal();
                }
            });

            function closeModal() {
                modal.style.display = 'none';
                form.reset();
                // Reset select all state
                const selectAll = document.getElementById('select-all-companies');
                if (selectAll) {
                    selectAll.checked = false;
                    selectAll.indeterminate = false;
                }
            }

            // Select All functionality
            const selectAll = document.getElementById('select-all-companies');
            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    const companyCheckboxes = document.querySelectorAll(
                        '#companies-checkbox-group input[type="checkbox"]:not(#select-all-companies)');
                    companyCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    this.indeterminate = false;
                });

                // Add change listeners to individual checkboxes
                document.addEventListener('change', function(e) {
                    if (e.target.matches(
                            '#companies-checkbox-group input[type="checkbox"]:not(#select-all-companies)'
                        )) {
                        updateSelectAllState();
                    }
                });

                function updateSelectAllState() {
                    const companyCheckboxes = document.querySelectorAll(
                        '#companies-checkbox-group input[type="checkbox"]:not(#select-all-companies)');
                    const checkedCount = Array.from(companyCheckboxes).filter(cb => cb.checked).length;
                    const totalCount = companyCheckboxes.length;

                    if (checkedCount === 0) {
                        selectAll.checked = false;
                        selectAll.indeterminate = false;
                    } else if (checkedCount === totalCount) {
                        selectAll.checked = true;
                        selectAll.indeterminate = false;
                    } else {
                        selectAll.checked = false;
                        selectAll.indeterminate = true;
                    }
                }

                // Initialize select all state
                updateSelectAllState();
            }

            // Form submission
            saveBtn.addEventListener('click', async (e) => {
                e.preventDefault();

                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                // Collect form data
                const selectedCompanies = Array.from(
                    document.querySelectorAll(
                        '#companies-checkbox-group input[name="companies[]"]:checked'),
                    cb => cb.value
                );

                const formData = {
                    name: document.getElementById('expense-name').value,
                    category: document.getElementById('expense-category').value,
                    amount_type: document.getElementById('amount-type').value,
                    default_amount: parseFloat(document.getElementById('default-amount').value),
                    reminder_days: parseInt(document.getElementById('reminder-days').value),
                    company_ids: selectedCompanies,
                    status: document.querySelector('input[name="status"]:checked').value
                };

                console.log('Form data to submit:', formData);

                // Show loading state
                const originalText = saveBtn.innerHTML;
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

                try {
                    const response = await fetch("{{ route('admin.expensetypes.store') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        },
                        body: JSON.stringify(formData)
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.message || 'Failed to save expense type');
                    }

                    // Success notification
                    alert('Expense type created successfully!');

                    // Close modal and reset form
                    closeModal();

                    // Reload page or update table
                    window.location.reload(); // Or implement dynamic table update

                } catch (error) {
                    console.error('Error saving expense type:', error);
                    alert('Error: ' + error.message);
                } finally {
                    // Reset button state
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = originalText;
                }
            });
        });

        document.addEventListener("DOMContentLoaded", function() {

            const selectAll = document.getElementById("select-all-companies");
            const companyCheckboxes = document.querySelectorAll("#companies-checkbox-group input[type='checkbox']");

            // When "Select All" is clicked
            selectAll.addEventListener("change", function() {
                companyCheckboxes.forEach(cb => cb.checked = selectAll.checked);
            });

            // If user manually checks/unchecks a company
            companyCheckboxes.forEach(cb => {
                cb.addEventListener("change", function() {
                    const allChecked = [...companyCheckboxes].every(item => item.checked);
                    const noneChecked = [...companyCheckboxes].every(item => !item.checked);

                    // Update Select All state
                    if (allChecked) {
                        selectAll.checked = true;
                        selectAll.indeterminate = false;
                    } else if (noneChecked) {
                        selectAll.checked = false;
                        selectAll.indeterminate = false;
                    } else {
                        selectAll.indeterminate = true; // shows a mixed-state
                    }
                });
            });

        });
    </script>
@endsection
