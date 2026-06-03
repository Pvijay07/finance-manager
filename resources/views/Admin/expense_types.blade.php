@extends('Admin.layouts.app')
@section('content')
    <div id="expense-types" class="page">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <div class="table-container">
            <div class="table-header">
                <div class="table-title">Expense Types Management</div>
                <div class="table-actions">
                    <button class="btn btn-outline">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <button class="btn btn-primary" id="add-expense-type-btn">
                        <i class="fas fa-plus"></i> Add Expense Type
                    </button>
                </div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Expense Type</th>
                        <th>Category</th>
                        <th>Amount Type</th>
                        <th>Default Amount</th>
                        <th>Recurring Type</th>
                        <th>Applicable Companies</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($expenseTypes as $expenseType)
                        <tr data-id="{{ $expenseType->id }}">
                            <td>{{ $expenseType->name }}</td>
                            <td>{{ $expenseType->category }}</td>
                            <td><span class="badge badge-primary">{{ $expenseType->amount_type }}</span></td>
                            <td>₹{{ $expenseType->default_amount }}</td>
                            <td>
                                <span class="badge badge-{{ $expenseType->is_recurring == 'recurring' ? 'success' : 'info' }}">
                                    {{ $expenseType->is_recurring == 'recurring' ? 'Recurring' : 'One Time' }}
                                </span>
                            </td>
                            <td>{{ $expenseType->company_names }}</td>
                            <td>
                                <span class="status {{ strtolower($expenseType->status) }}">
                                    {{ $expenseType->status }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-outline settings-btn" data-id="{{ $expenseType->id }}"
                                    style="padding: 5px 10px; font-size: 0.8rem;">
                                    <i class="fas fa-cog"></i> Settings
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for Add/Edit Expense Type -->
    <div class="custom-modal-overlay" id="expense-type-modal" style="display: none;">
        <div class="modal">
            <div class="modal-header">
                <h2 class="modal-title" id="modal-title">Add Expense Type</h2>
                <button class="modal-close" id="close-modal">&times;</button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <form id="expense-type-form">
                    <input type="hidden" id="expense-type-id" value="">

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
                            <option value="facility">Facility</option>
                            <option value="personnel">Personnel</option>
                            <option value="utilities">Utilities</option>
                            <option value="marketing">Marketing</option>
                            <option value="travel">Travel</option>
                            <option value="equipment">Equipment</option>
                        </select>
                    </div>

                    <!-- Amount Type -->
                    <div class="form-group">
                        <label class="form-label" for="amount-type">Amount Type</label>
                        <select class="form-control" id="amount-type" required>
                            <option value="">Select Amount Type</option>
                            <option value="fixed">Fixed</option>
                            <option value="adjustable">Adjustable</option>
                            {{-- <option value="variable">Variable</option> --}}
                        </select>
                    </div>

                    <!-- Recurring Type -->
                    <div class="form-group">
                        <label class="form-label" for="is-recurring">Recurring Type</label>
                        <select class="form-control" id="is-recurring" required>
                            <option value="0">One Time</option>
                            <option value="1">Recurring</option>
                        </select>
                        <small class="form-hint">Select whether this expense occurs once or repeats regularly</small>
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
                                <input type="radio" name="status" value="active" checked> Active
                            </label>
                            <label style="margin-left: 20px;">
                                <input type="radio" name="status" value="inactive"> Inactive
                            </label>
                        </div>
                    </div>

                </form>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancel-btn">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-expense-type">Save Expense Type</button>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded - expense types page');

            // Modal functionality
            const modal = document.getElementById('expense-type-modal');
            const addBtn = document.getElementById('add-expense-type-btn');
            const closeBtn = document.getElementById('close-modal');
            const cancelBtn = document.getElementById('cancel-btn');
            const saveBtn = document.getElementById('save-expense-type');
            const form = document.getElementById('expense-type-form');
            const modalTitle = document.getElementById('modal-title');
            const expenseIdInput = document.getElementById('expense-type-id');

            // Debug logging
            console.log('Elements found:', {
                modal: !!modal,
                addBtn: !!addBtn,
                closeBtn: !!closeBtn,
                cancelBtn: !!cancelBtn,
                saveBtn: !!saveBtn,
                form: !!form
            });

            // Check if elements exist
            if (!modal || !addBtn || !closeBtn || !cancelBtn || !saveBtn || !form) {
                console.error('Required elements not found');
                return;
            }

            // Open modal for adding new expense type
            addBtn.addEventListener('click', function() {
                console.log('Add button clicked');
                resetForm();
                modalTitle.textContent = 'Add Expense Type';
                expenseIdInput.value = '';
                modal.style.display = 'flex';
                console.log('Modal should be visible now');
            });

            // Close modal
            closeBtn.addEventListener('click', closeModal);
            cancelBtn.addEventListener('click', closeModal);

            // Close on overlay click
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal();
                }
            });

            function closeModal() {
                console.log('Closing modal');
                modal.style.display = 'none';
                form.reset();
                // Reset select all state
                const selectAll = document.getElementById('select-all-companies');
                if (selectAll) {
                    selectAll.checked = false;
                    selectAll.indeterminate = false;
                }
            }

            // Function to reset form
            function resetForm() {
                console.log('Resetting form');
                form.reset();
                expenseIdInput.value = '';
                
                // Set default status to active
                const activeRadio = document.querySelector('input[name="status"][value="active"]');
                if (activeRadio) {
                    activeRadio.checked = true;
                }

                // Set default recurring type to one_time
                const recurringSelect = document.getElementById('is-recurring');
                if (recurringSelect) {
                    recurringSelect.value = '0';
                }

                // Reset all company checkboxes
                const companyCheckboxes = document.querySelectorAll(
                    '#companies-checkbox-group input[type="checkbox"]:not(#select-all-companies)'
                );
                companyCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });

                // Reset select all
                const selectAll = document.getElementById('select-all-companies');
                if (selectAll) {
                    selectAll.checked = false;
                    selectAll.indeterminate = false;
                }
            }

            // Function to load expense type data into form
            async function loadExpenseType(id) {
                console.log('Loading expense type:', id);
                try {
                    const response = await fetch(`https://xhtmlreviews.in/beta-finance/admin/expensetypes/${id}/edit`);
                    const result = await response.json();

                    if (result.success) {
                        const data = result.data;

                        // Fill form fields
                        expenseIdInput.value = data.id;
                        document.getElementById('expense-name').value = data.name;
                        document.getElementById('expense-category').value = data.category;
                        document.getElementById('amount-type').value = data.amount_type;
                        
                        // Set recurring type
                        const recurringSelect = document.getElementById('is-recurring');
                        if (recurringSelect && data.is_recurring) {
                            recurringSelect.value = data.is_recurring;
                        }
                        
                        document.getElementById('default-amount').value = data.default_amount;
                        document.getElementById('reminder-days').value = data.reminder_days;

                        // Set status radio button
                        const statusRadio = document.querySelector(`input[name="status"][value="${data.status}"]`);
                        if (statusRadio) {
                            statusRadio.checked = true;
                        }

                        // Set company checkboxes
                        const companyIds = data.applicable_companies || [];
                        const companyCheckboxes = document.querySelectorAll(
                            '#companies-checkbox-group input[type="checkbox"]:not(#select-all-companies)'
                        );

                        companyCheckboxes.forEach(checkbox => {
                            checkbox.checked = companyIds.includes(parseInt(checkbox.value));
                        });

                        // Update select all state
                        updateSelectAllState();

                        // Change modal title
                        modalTitle.textContent = 'Edit Expense Type';

                        // Show modal
                        modal.style.display = 'flex';
                    }
                } catch (error) {
                    console.error('Error loading expense type:', error);
                    alert('Failed to load expense type data');
                }
            }

            // Event delegation for settings buttons
            document.addEventListener('click', function(e) {
                if (e.target.closest('.settings-btn')) {
                    const button = e.target.closest('.settings-btn');
                    const id = button.getAttribute('data-id');
                    loadExpenseType(id);
                }
            });

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
            saveBtn.addEventListener('click', async function(e) {
                e.preventDefault();

                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                // Collect form data
                const selectedCompanies = Array.from(
                    document.querySelectorAll(
                        '#companies-checkbox-group input[name="companies[]"]:checked'),
                    cb => parseInt(cb.value)
                );

                const formData = {
                    name: document.getElementById('expense-name').value,
                    category: document.getElementById('expense-category').value,
                    amount_type: document.getElementById('amount-type').value,
                    is_recurring: document.getElementById('is-recurring').value,
                    default_amount: parseFloat(document.getElementById('default-amount').value),
                    reminder_days: parseInt(document.getElementById('reminder-days').value),
                    company_ids: selectedCompanies,
                    status: document.querySelector('input[name="status"]:checked').value
                };

                console.log('Submitting form data:', formData);

                const expenseId = expenseIdInput.value;
                const url = expenseId ?
                    `https://xhtmlreviews.in/beta-finance/admin/expensetypes/${expenseId}` :
                    "{{ route('admin.expensetypes.store') }}";
                const method = expenseId ? 'PUT' : 'POST';

                // Show loading state
                const originalText = saveBtn.innerHTML;
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'X-HTTP-Method-Override': method === 'PUT' ? 'PUT' : 'POST'
                        },
                        body: JSON.stringify(formData)
                    });

                    const result = await response.json();
                    console.log('Response:', result);

                    if (!response.ok) {
                        throw new Error(result.message || 'Failed to save expense type');
                    }

                    // Success notification
                    alert(expenseId ? 'Expense type updated successfully!' :
                        'Expense type created successfully!');

                    // Close modal and reset form
                    closeModal();

                    // Reload page to see changes
                    // window.location.reload();

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
    </script>

@endsection