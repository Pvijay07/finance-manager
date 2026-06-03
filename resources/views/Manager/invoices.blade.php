@extends('Admin.layouts.app')
@section('content')
    <section id="invoices-page" class="page">
        <div class="container-fluid">
            <h4 class="mb-3">Invoices &amp; Proformas</h4>

            <!-- Create Proforma Section -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Create Proforma Invoice</h6>
                    </div>
                    <small class="text-muted d-block mb-3">
                        Create a proforma invoice. Once payment is received, it will be converted to a taxable invoice
                        and a new proforma can be created for any remaining balance.
                    </small>

                    <form id="createProformaForm" class="row g-3">
                        @csrf
                        <div class="col-md-3">
                            <label class="form-label small">Company</label>
                            <select class="form-select form-select-sm" name="company_id" required>
                                <option value="">Select Company</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Invoice Type</label>
                            <input type="text" class="form-control form-control-sm" value="Proforma" disabled>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Issue Date</label> <!-- Changed from "Invoice Date" -->
                            <input type="date" class="form-control form-control-sm" name="issue_date" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Due Date</label>
                            <input type="date" class="form-control form-control-sm" name="due_date" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small">Client Name</label>
                            <input type="text" class="form-control form-control-sm" name="client_name"
                                placeholder="Client / Company name" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Client Email</label>
                            <input type="email" class="form-control form-control-sm" name="client_email"
                                placeholder="billing@email.com" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Client GSTIN (optional)</label>
                            <input type="text" class="form-control form-control-sm" name="client_gstin"
                                placeholder="GSTIN">
                        </div>

                        <div class="col-12">
                            <label class="form-label small">Billing Address</label>
                            <textarea class="form-control form-control-sm" name="billing_address" rows="2" required></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label small d-flex justify-content-between">
                                <span>Line Items</span>
                                <span class="text-muted">Use a single line or repeat this block later in full
                                    app.</span>
                            </label>
                            <div class="row g-2 align-items-end">
                                <div class="col-md-5">
                                    <input type="text" class="form-control form-control-sm"
                                        name="line_items[0][description]" placeholder="Description" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" class="form-control form-control-sm"
                                        name="line_items[0][quantity]" placeholder="Qty" value="1" min="1"
                                        required>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" class="form-control form-control-sm" name="line_items[0][rate]"
                                        placeholder="Rate" step="0.01" min="0" required>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control form-control-sm" name="line_items[0][amount]"
                                        placeholder="Line Amount" step="0.01" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small">Apply Tax?</label>
                            <div class="form-check">
                                <!-- Change 'apply_tax' to 'is_taxable' -->
                                <input class="form-check-input" type="checkbox" id="invoiceApplyTax" name="is_taxable"
                                    value="1">
                                <label class="form-check-label small" for="invoiceApplyTax">
                                    Apply tax
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Tax %</label>
                            <input type="number" class="form-control form-control-sm" name="tax_percentage"
                                placeholder="e.g. 18" value="18">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Total Amount (₹)</label>
                            <input type="number" class="form-control form-control-sm" name="total_amount"
                                placeholder="Total" step="0.01" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Grand Total (₹)</label>
                            <input type="number" class="form-control form-control-sm" name="grand_total"
                                placeholder="Total incl. tax" step="0.01" readonly>
                        </div>
                        
                        <!-- Add optional fields if needed -->
                        <input type="hidden" name="terms_conditions" value="">
                        <input type="hidden" name="is_recurring" value="0">
                        <input type="hidden" name="reminder_days" value="">
                        <input type="hidden" name="due_day" value="">
                        <input type="hidden" name="frequency" value="">

                        <div class="col-md-6">
                            <label class="form-label small">Purpose Comment (for CA / internal)</label>
                            <textarea class="form-control form-control-sm" name="purpose_comment" rows="2"
                                placeholder="Describe what this invoice is for..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Attach Supporting Document (optional)</label>
                            <input type="file" class="form-control form-control-sm" name="supporting_doc">
                            <small class="text-muted small">PDF / Image of proposal, email approval, etc.</small>
                        </div>

                        <div class="col-12 d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="saveDraftBtn">
                                Save Draft Proforma
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary">
                                Save &amp; Create Upcoming Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Invoices & Proformas List -->
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Invoices &amp; Proformas</span>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" style="width:auto;">
                            <option>All Companies</option>
                            <option>Petsfolio</option>
                            <option>IKC</option>
                            <option>Infasta</option>
                        </select>
                        <select class="form-select form-select-sm" style="width:auto;">
                            <option>All Types</option>
                            <option>Proforma</option>
                            <option>Invoice</option>
                        </select>
                        <select class="form-select form-select-sm" style="width:auto;">
                            <option>All Status</option>
                            <option>Pending</option>
                            <option>Paid</option>
                            <option>Overdue</option>
                            <option>Replaced</option>
                        </select>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice No</th>
                                    <th>Type</th>
                                    <th>Company</th>
                                    <th>Client</th>
                                    <th>Invoice Date</th>
                                    <th>Due Date</th>
                                    <th class="text-end">Amount (₹)</th>
                                    <th>Status</th>
                                    <th>Linked Payment</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Example Proforma that can be split/partial -->
                                <tr>
                                    <td>PF-25-26-PRO-010</td>
                                    <td><span class="badge bg-info">Proforma</span></td>
                                    <td>Infasta</td>
                                    <td>ABC Pvt Ltd</td>
                                    <td>20-11-2025</td>
                                    <td>27-11-2025</td>
                                    <td class="text-end">₹ 1,00,000</td>
                                    <td><span class="badge bg-warning text-dark">Pending</span></td>
                                    <td>#UP-555 (Credit)</td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary btn-receive-payment"
                                                data-bs-toggle="modal" data-bs-target="#partialPaymentModal"
                                                data-invoice-id="1" data-invoice-no="PF-25-26-PRO-010"
                                                data-client="ABC Pvt Ltd" data-amount="100000" data-company="Infasta"
                                                data-duedate="2025-11-27">
                                                Receive Payment
                                            </button>
                                            <button class="btn btn-outline-secondary">
                                                View Proforma
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Example fully paid Tax Invoice -->
                                <tr>
                                    <td>INF-25-26-INV-004</td>
                                    <td><span class="badge bg-primary">Invoice</span></td>
                                    <td>Infasta</td>
                                    <td>XYZ Solutions</td>
                                    <td>10-11-2025</td>
                                    <td>10-11-2025</td>
                                    <td class="text-end">₹ 50,000</td>
                                    <td><span class="badge bg-success">Paid</span></td>
                                    <td>#UP-501 (Credit)</td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-secondary">
                                            Download Tax Invoice
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Partial Payment / Split Modal -->
    <div class="modal fade" id="partialPaymentModal" tabindex="-1" aria-labelledby="partialPaymentLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="partialPaymentLabel">Record Payment – Partial Amount Received</h5>
                        <small class="text-muted">
                            Client has paid less than the scheduled amount. Confirm how to split this into a taxable
                            invoice and a new proforma.
                        </small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="partialPaymentForm" class="row g-3">
                        @csrf
                        <div class="col-md-4">
                            <label class="form-label small">Invoice No</label>
                            <input type="text" class="form-control form-control-sm" id="pp_invoice_no" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Company</label>
                            <input type="text" class="form-control form-control-sm" id="pp_company" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Client / Party</label>
                            <input type="text" class="form-control form-control-sm" id="pp_client" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small">Original Scheduled Amount</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control" id="pp_original_amount" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Amount Received Now</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control" id="pp_received_amount" min="0"
                                    step="0.01">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Balance Amount (auto)</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control" id="pp_balance_amount" readonly>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small">Original Due Date</label>
                            <input type="text" class="form-control form-control-sm" id="pp_original_due" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">New Due Date for Balance</label>
                            <input type="date" class="form-control form-control-sm" id="pp_new_due_date" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Payment Date</label>
                            <input type="date" class="form-control form-control-sm" id="pp_payment_date" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small">Internal Note (optional)</label>
                            <textarea class="form-control form-control-sm" id="pp_note" rows="2"
                                placeholder="e.g., Client paid 50,000 now, remaining 50,000 to be paid next month."></textarea>
                        </div>

                        <div class="col-12">
                            <div class="border rounded p-2 bg-light">
                                <div class="small fw-semibold mb-1">What will happen now?</div>
                                <ul class="small mb-0">
                                    <li>A <strong>taxable invoice</strong> will be generated for the amount received now.
                                    </li>
                                    <li>A <strong>new proforma</strong> will be created for the balance with the new due
                                        date.</li>
                                    <li>The original proforma will be marked as <strong>Replaced</strong> and will not be
                                        valid for accounting.</li>
                                </ul>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <div class="small text-muted">
                        Once confirmed, this action will split the scheduled amount into one paid invoice and one new
                        proforma.
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-sm btn-primary" id="pp_confirm_btn">
                            Confirm &amp; Split Payment
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Bootstrap tooltips if needed
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Calculate line item amount
            function calculateLineAmount() {
                const quantity = document.querySelector('input[name="line_items[0][quantity]"]').value || 0;
                const rate = document.querySelector('input[name="line_items[0][rate]"]').value || 0;
                const amount = (quantity * rate).toFixed(2);
                document.querySelector('input[name="line_items[0][amount]"]').value = amount;

                // Update totals
                calculateTotals();
            }

            // Calculate totals
            function calculateTotals() {
                const lineAmount = parseFloat(document.querySelector('input[name="line_items[0][amount]"]')
                    .value) || 0;
                const taxPercentage = parseFloat(document.querySelector('input[name="tax_percentage"]').value) || 0;
                const applyTax = document.querySelector('#invoiceApplyTax').checked;

                let subtotal = lineAmount;
                let taxAmount = 0;
                let grandTotal = lineAmount;

                if (applyTax && taxPercentage > 0) {
                    taxAmount = (subtotal * taxPercentage) / 100;
                    grandTotal = subtotal + taxAmount;
                }

                document.querySelector('input[name="subtotal"]').value = subtotal.toFixed(2);
                document.querySelector('input[name="grand_total"]').value = grandTotal.toFixed(2);
            }

            // Event listeners for calculations
            document.querySelector('input[name="line_items[0][quantity]"]').addEventListener('input',
                calculateLineAmount);
            document.querySelector('input[name="line_items[0][rate]"]').addEventListener('input',
                calculateLineAmount);
            document.querySelector('#invoiceApplyTax').addEventListener('change', calculateTotals);
            document.querySelector('input[name="tax_percentage"]').addEventListener('input', calculateTotals);

            // Initialize calculations
            calculateLineAmount();

            // Proforma Form Submission
            const proformaForm = document.getElementById('createProformaForm');
            if (proformaForm) {
                proformaForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // Gather form data
                    const formData = {
                        company_id: document.querySelector('select[name="company_id"]').value,
                        client_name: document.querySelector('input[name="client_name"]').value,
                        client_email: document.querySelector('input[name="client_email"]').value,
                        client_gstin: document.querySelector('input[name="client_gstin"]').value,
                        billing_address: document.querySelector('textarea[name="billing_address"]')
                            .value,
                        issue_date: document.querySelector('input[name="issue_date"]').value,
                        due_date: document.querySelector('input[name="due_date"]').value,
                        total_amount: parseFloat(document.querySelector('input[name="total_amount"]')
                            .value) || 0,
                        is_taxable: document.querySelector('input[name="is_taxable"]').checked ? 1 : 0,
                        tax_percentage: parseFloat(document.querySelector(
                            'input[name="tax_percentage"]').value) || 0,
                        purpose_comment: document.querySelector('textarea[name="purpose_comment"]')
                            .value,
                        terms_conditions: document.querySelector('input[name="terms_conditions"]')
                            .value,
                        is_recurring: document.querySelector('input[name="is_recurring"]').value,
                        reminder_days: document.querySelector('input[name="reminder_days"]').value,
                        due_day: document.querySelector('input[name="due_day"]').value,
                        frequency: document.querySelector('input[name="frequency"]').value,
                        line_items: JSON.stringify([{
                            description: document.querySelector(
                                'input[name="line_items[0][description]"]').value,
                            quantity: parseFloat(document.querySelector(
                                'input[name="line_items[0][quantity]"]').value) || 0,
                            rate: parseFloat(document.querySelector(
                                'input[name="line_items[0][rate]"]').value) || 0,
                            amount: parseFloat(document.querySelector(
                                'input[name="line_items[0][amount]"]').value) || 0
                        }])
                    };

                    // Validate required fields
                    if (!formData.company_id || !formData.client_name || !formData.client_email ||
                        !formData.billing_address || !formData.issue_date || !formData.due_date ||
                        !formData.total_amount) {
                        alert('Please fill in all required fields');
                        return;
                    }

                    console.log('Submitting proforma:', formData);

                    // Send request
                    fetch('{{ route('admin.invoices.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(formData)
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! Status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                alert('Proforma created successfully!');
                                proformaForm.reset();
                                // Reset totals if you have a function for that
                                if (typeof calculateLineAmount === 'function') {
                                    calculateLineAmount();
                                }
                            } else {
                                alert('Error: ' + (data.message || 'Unknown error occurred'));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while creating the proforma: ' + error.message);
                        });
                });
            }
            // Save Draft Button
            document.getElementById('saveDraftBtn').addEventListener('click', function() {
                alert('Draft saved functionality would be implemented here');
                // Similar to above but with draft status
            });

            // Partial Payment Modal Functionality
            const partialPaymentModal = document.getElementById('partialPaymentModal');
            if (partialPaymentModal) {
                const receivedAmountInput = document.getElementById('pp_received_amount');
                const balanceAmountInput = document.getElementById('pp_balance_amount');
                const originalAmountInput = document.getElementById('pp_original_amount');
                const confirmBtn = document.getElementById('pp_confirm_btn');

                let currentInvoice = null;

                // Calculate balance when received amount changes
                receivedAmountInput.addEventListener('input', function() {
                    const originalAmount = parseFloat(originalAmountInput.value) || 0;
                    const receivedAmount = parseFloat(this.value) || 0;
                    const balance = Math.max(0, originalAmount - receivedAmount);

                    balanceAmountInput.value = balance.toFixed(2);

                    // Validate received amount
                    if (receivedAmount <= 0 || receivedAmount >= originalAmount) {
                        this.classList.add('is-invalid');
                        confirmBtn.disabled = true;
                    } else {
                        this.classList.remove('is-invalid');
                        confirmBtn.disabled = false;
                    }
                });

                // Modal show event
                partialPaymentModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    currentInvoice = {
                        id: button.dataset.invoiceId,
                        invoiceNo: button.dataset.invoiceNo,
                        company: button.dataset.company,
                        client: button.dataset.client,
                        amount: button.dataset.amount,
                        dueDate: button.dataset.duedate
                    };

                    // Populate form
                    document.getElementById('pp_invoice_no').value = currentInvoice.invoiceNo;
                    document.getElementById('pp_company').value = currentInvoice.company;
                    document.getElementById('pp_client').value = currentInvoice.client;
                    document.getElementById('pp_original_amount').value = currentInvoice.amount;
                    document.getElementById('pp_original_due').value = currentInvoice.dueDate;

                    // Set default dates
                    const today = new Date().toISOString().split('T')[0];
                    document.getElementById('pp_payment_date').value = today;

                    // Set new due date to 30 days from today
                    const futureDate = new Date();
                    futureDate.setDate(futureDate.getDate() + 30);
                    document.getElementById('pp_new_due_date').value = futureDate.toISOString().split('T')[
                        0];

                    // Reset form
                    receivedAmountInput.value = '';
                    balanceAmountInput.value = '';
                    receivedAmountInput.classList.remove('is-invalid');
                    confirmBtn.disabled = true;
                });

                // Confirm partial payment
                confirmBtn.addEventListener('click', function() {
                    const formData = {
                        invoice_id: currentInvoice.id,
                        received_amount: receivedAmountInput.value,
                        payment_date: document.getElementById('pp_payment_date').value,
                        new_due_date: document.getElementById('pp_new_due_date').value,
                        note: document.getElementById('pp_note').value,
                        _token: '{{ csrf_token() }}'
                    };

                    // Validation
                    if (!formData.new_due_date) {
                        alert('Please select a new due date for the balance amount');
                        return;
                    }

                    if (!formData.payment_date) {
                        alert('Please select a payment date');
                        return;
                    }

                    console.log('Submitting partial payment:', formData);

                    // Simulate API call - replace with your actual endpoint
                    fetch('{{ route('admin.invoices.partial-payment') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(formData)
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                alert('Payment recorded successfully!');
                                const modal = bootstrap.Modal.getInstance(partialPaymentModal);
                                modal.hide();
                                location.reload();
                            } else {
                                alert('Error: ' + (data.message || 'Unknown error occurred'));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while processing the payment: ' + error.message);
                        });
                });
            }
        });
    </script>

    <style>
        .badge-status-proforma {
            background-color: #17a2b8;
            color: white;
        }

        .badge-status-invoice {
            background-color: #007bff;
            color: white;
        }

        .badge-status-replaced {
            background-color: #6c757d;
            color: white;
        }

        .small-label {
            font-size: 0.875rem;
            font-weight: 500;
        }
    </style>
@endsection
