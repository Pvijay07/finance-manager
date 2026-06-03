@extends( 'Admin.layouts.app' )
@section( 'content' )
<style>
    select.form-control,
    select.form-select,
    #currencySelect,
    #editHeaderCurrency,
    #footerCurrencySelect,
    #editCurrencySelect,
    #companySelect,
    #editCompanySelect,
    #status,
    #editStatus,
    #frequency,
    #editFrequency {
        height: 28px !important;
        font-size: 0.8rem !important;
        border-radius: 6px !important;
        background-color: #ffffff !important;
        cursor: pointer !important;
        font-weight: 600 !important;
        color: #2563eb !important;
        border: 1.5px solid #d1d5db !important;
        padding: 0 24px 0 10px !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%232563eb' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
        background-repeat: no-repeat !important;
        background-position: right 8px center !important;
        background-size: 12px 12px !important;
        width: auto !important;
        display: inline-block !important;
        vertical-align: middle !important;
        margin: 0 4px !important;
    }

    /* Styling for larger selects */
    select.form-control:not(#currencySelect):not(#editHeaderCurrency),
    select.form-select:not(#currencySelect):not(#editHeaderCurrency) {
        width: 100% !important;
        height: 38px !important;
        padding-left: 12px !important;
        margin: 0 !important;
    }

    #currencySelect:hover,
    #editHeaderCurrency:hover,
    #footerCurrencySelect:hover,
    #editCurrencySelect:hover {
        border-color: #2563eb !important;
        background-color: #f8fafc !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
    }

    #currencySelect:focus,
    #editHeaderCurrency:focus,
    #footerCurrencySelect:focus,
    #editCurrencySelect:focus {
        outline: none !important;
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1) !important;
    }

    /* Spacing between spans and inputs in groups */
    .input-group {
        gap: 10px !important;
        border: none !important;
        background: transparent !important;
    }

    .input-group>.input-group-text {
        border-radius: 8px !important;
        border: 1px solid #e2e8f0 !important;
        background-color: #f8fafc !important;
        color: #64748b !important;
        font-weight: 600 !important;
        padding: 0 12px !important;
        display: flex !important;
        align-items: center !important;
        min-width: 40px !important;
        justify-content: center !important;
    }

    .input-group>.form-control,
    .input-group>.form-select {
        border-radius: 8px !important;
        border: 1px solid #e2e8f0 !important;
        transition: all 0.2s !important;
    }

    .input-group>.form-control:focus,
    .input-group>.form-select:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1) !important;
    }

    /* Override Bootstrap's default joined layout */
    .input-group> :not(:first-child):not(.dropdown-menu):not(.valid-tooltip):not(.valid-feedback):not(.invalid-tooltip):not(.invalid-feedback) {
        margin-left: 0 !important;
        border-radius: 8px !important;
    }
</style>
<section id="invoices-page" class="page">
    <div class="container-fluid">
        @php
        $activeTab = request ()->get ( 'tab', 'create' );
        $settings = $settings ?? session ( 'settings' ) ?? [];
        @endphp

        <h4 class="mb-3">Invoices &amp; Proformas</h4>

        <!-- Statistics Cards -->
        <!-- <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2 text-muted">Pending Proformas</h6>
                            <h4 class="card-title text-warning">{{ $stats['pending_proformas'] }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2 text-muted">Pending Amount</h6>
                            <h4 class="card-title text-danger">₹{{ number_format ( $stats['pending_amount'] ) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2 text-muted">Paid This Month</h6>
                            <h4 class="card-title text-success">₹{{ number_format ( $stats['paid_this_month'] ) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2 text-muted">Total Invoices</h6>
                            <h4 class="card-title text-primary">{{ $stats['total_invoices'] }}</h4>
                        </div>
                    </div>
                </div>
            </div> -->

        <!-- Tabs for Invoice Types -->
        <ul class="nav nav-tabs mb-4" id="invoiceTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'create' ? 'active' : '' }}" id="create-tab"
                    data-bs-toggle="tab" data-bs-target="#create" type="button">
                    <i class="fas fa-plus-circle me-1"></i> Create Invoice
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'proformas' ? 'active' : '' }}" id="proformas-tab"
                    data-bs-toggle="tab" data-bs-target="#proformas" type="button">
                    <i class="fas fa-file-invoice me-1"></i> Repeated Incomes
                    <span class="badge bg-warning ms-1">{{ $pendingProformasCount }}</span>
                </button>
            </li>

        </ul>

        <div class="tab-content" id="invoiceTabsContent">
            <!-- Create Proforma Tab -->
            <div class="tab-pane fade {{ $activeTab === 'create' ? 'show active' : '' }}" id="create" role="tabpanel">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Create Proforma Invoice</h6>
                        </div>
                        <small class="text-muted d-block mb-3">
                            Create a proforma invoice. Once payment is received, it will be converted to a taxable
                            invoice and a new proforma can be created for any remaining balance.
                        </small>

                        <form id="createProformaForm" method="POST" action="{{ route ( 'admin.invoices.store' ) }}">
                            @csrf
                            <div class="row g-3">
                                <!-- Top Row: Company, Invoice Type, Dates -->
                                <div class="col-md-3">
                                    <label class="form-label small">Company *</label>
                                    <select class="form-select form-select-sm" name="company_id" required
                                        id="companySelect" onchange="updateCompanyDetails()">
                                        <option value="">Select Company</option>
                                        @foreach ( $companies as $company )
                                        <option value="{{ $company->id }}" data-gstin="{{ $company->gstin }}"
                                            data-address="{{ $company->address }}"
                                            data-currency="{{ $company->currency ?? 'INR' }}">
                                            {{ $company->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Invoice Date *</label>
                                    <input type="date" class="form-control form-control-sm" name="issue_date"
                                        value="{{ date ( 'Y-m-d' ) }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Due Date *</label>
                                    <input type="date" class="form-control form-control-sm" name="due_date"
                                        value="{{ date ( 'Y-m-d', strtotime ( '+30 days' ) ) }}" required>
                                </div>

                                <!-- Client Details -->
                                <div class="col-md-3">
                                    <label class="form-label small">Client Name *</label>
                                    <input type="text" class="form-control form-control-sm" name="client_name"
                                        placeholder="Client / Company name" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Client Email *</label>
                                    <input type="email" class="form-control form-control-sm" name="client_email"
                                        placeholder="billing@email.com" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Mobile Number</label>
                                    <input type="text" class="form-control form-control-sm" name="mobile_number"
                                        placeholder="Optional" maxlength="10"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Client GSTIN (optional)</label>
                                    <input type="text" class="form-control form-control-sm" name="client_gstin"
                                        placeholder="GSTIN">
                                </div>

                                <!-- Billing Address -->
                                <div class="col-12">
                                    <label class="form-label small">Billing Address *</label>
                                    <textarea class="form-control form-control-sm" name="billing_address" rows="2"
                                        required></textarea>
                                </div>

                                <!-- Line Items Section -->
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label small mb-0">Line Items</label>
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            onclick="addLineItem()">
                                            <i class="fas fa-plus"></i> Add Item
                                        </button>
                                    </div>
                                    <div class="row g-2 mb-1 px-1 fw-bold small text-muted border-bottom pb-1">
                                        <div class="col-md-4">Line Item(description)</div>
                                        <div class="col-md-2">Qnt.</div>
                                        <div class="col-md-2">Amt
                                            <select class="form-select form-select-sm d-inline-block w-auto py-0 px-1"
                                                style="height: auto; font-size: 0.75rem; border: none; background: transparent; font-weight: bold; color: #4b5563;"
                                                id="currencySelect" name="currency"
                                                onchange="updateCurrencySelection()">
                                                <option value="USD">USD</option>
                                                <option value="INR">INR</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">Total Amt</div>
                                        <div class="col-md-1"></div>
                                    </div>
                                    <div id="lineItemsContainer">
                                        <!-- Line items will be added here -->
                                        <div class="line-item row g-2 align-items-end mb-2">
                                            <div class="col-md-4">
                                                <input type="text" class="form-control form-control-sm"
                                                    name="line_items[0][description]" placeholder="Description"
                                                    required>
                                            </div>
                                            <div class="col-md-2">
                                                <input type="number" class="form-control form-control-sm quantity"
                                                    name="line_items[0][quantity]" placeholder="Qty" value="1" min="1"
                                                    step="0.01" required oninput="debouncedCalculateLineAmount()">
                                            </div>
                                            <div class="col-md-2">
                                                <input type="number" class="form-control form-control-sm rate"
                                                    name="line_items[0][rate]" placeholder="Rate" step="0.01"
                                                    oninput="debouncedCalculateLineAmount()" min="0" required>
                                            </div>
                                            <div class="col-md-2">
                                                <input type="number" class="form-control form-control-sm amount"
                                                    name="line_items[0][amount]" placeholder="Amount" step="0.01"
                                                    oninput="calculateTax()" readonly>
                                            </div>
                                            <div class="col-md-1">
                                                <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="removeLineItem(this)" disabled>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tax and Currency Section -->
                                <div class="col-12">
                                    <h6 class="mb-3">Tax & Currency Details</h6>

                                    <!-- GST Section -->
                                    <div id="gst_section"
                                        style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #f0f9ff;">
                                        <div class="row align-items-center mb-2">
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="applyGST"
                                                        name="apply_gst" value="1" checked onchange="calculateTax()">
                                                    <label class="form-check-label small fw-medium" for="applyGST">
                                                        Apply GST
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label small">GST %</label>
                                                <input type="number" class="form-control form-control-sm"
                                                    id="gst_percentage" name="gst_percentage"
                                                    value="{{ $settings['default_gst_rate'] ?? '18' }}" step="0.01"
                                                    oninput="calculateTax()">
                                            </div>
                                            <div class="col-md-3">

                                                <label class="form-label small">GST Amount</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text tax-symbol">$</span>

                                                    <input type="number" class="form-control form-control-sm"
                                                        id="gst_amount_display" value="0" readonly
                                                        style="background: #e0f2fe;">
                                                    <input type="hidden" id="gst_amount" name="gst_amount" value="0">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small">Amount After GST</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text tax-symbol">$</span>
                                                    <input type="number" class="form-control form-control-sm"
                                                        id="amount_after_gst" value="0" readonly
                                                        style="background: #e0f2fe;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- TDS Section -->
                                    <div id="tds_section"
                                        style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #fef2f2;">
                                        <div class="row align-items-center mb-2">
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="applyTDS"
                                                        name="apply_tds" value="1" onchange="calculateTax()">
                                                    <label class="form-check-label small fw-medium" for="applyTDS">
                                                        Apply TDS
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label small">TDS %</label>
                                                <input type="number" class="form-control form-control-sm"
                                                    id="tds_percentage" name="tds_percentage"
                                                    value="{{ $settings['default_tds_rate'] ?? '10' }}" step="0.01"
                                                    oninput="calculateTax()">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small">TDS Amount</label>

                                                <div class="input-group input-group-sm">

                                                    <span class="input-group-text tax-symbol">$</span>
                                                    <input type="number" class="form-control form-control-sm"
                                                        id="tds_amount_display" value="0" readonly
                                                        style="background: #fee2e2;">
                                                    <input type="hidden" id="tds_amount" name="tds_amount" value="0">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label small">Amount After TDS</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text tax-symbol">$</span>
                                                    <input type="number" class="form-control form-control-sm"
                                                        id="amount_after_tds" value="0" readonly
                                                        style="background: #fee2e2;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Currency Section - UPDATED to match screenshot -->
                                    <div id="currency_section"
                                        style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #f8fafc;">
                                        <h6 class="mb-3 small fw-bold">Currency Details</h6>
                                        <div class="row g-3">
                                            <!-- Amount in Foreign Currency -->
                                            <div class="col-md-3 d-none">
                                                <label class="form-label small">Currency</label>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small">Amount In ($)</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text" id="currencySymbol">$</span>
                                                    <input type="number" class="form-control form-control-sm"
                                                        id="amount_in_foreign" name="amount_in_foreign" value="0"
                                                        step="0.01" oninput="updateCurrencyConversionFromForeign()">
                                                </div>
                                                <small class="text-muted d-none" id="foreignAmountLabel"></small>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label small" id="rateLabelDisplay">USD to
                                                    INR</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">1 <span
                                                            class="selected-currency">USD</span> = ₹</span>
                                                    <input type="number" class="form-control form-control-sm"
                                                        id="base_conversion_rate" name="base_conversion_rate"
                                                        value="{{ $settings['default_base_rate'] ?? '83.00' }}"
                                                        step="0.01" oninput="updateCurrencyConversionFromForeign()">
                                                </div>
                                            </div>
                                            <!-- Amount in INR -->
                                            <div class="col-md-3">
                                                <label class="form-label small">Amount in ₹</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">₹</span>
                                                    <input type="number" class="form-control form-control-sm"
                                                        id="amount_in_inr" value="0" readonly
                                                        style="background: #f1f5f9;">
                                                    <input type="hidden" id="convertedAmount" name="converted_amount"
                                                        value="0">
                                                </div>
                                                <small class="text-muted d-none" id="conversionDisplay"></small>
                                            </div>
                                            <!-- Conversion Rate -->
                                            <div class="col-md-3">
                                                <label class="form-label small">Conversion Rate %</label>
                                                <div class="input-group input-group-sm">
                                                    <input type="number" class="form-control form-control-sm"
                                                        id="conversion_rate_percentage"
                                                        name="conversion_rate_percentage"
                                                        value="{{ $settings['default_conversion_rate'] ?? '1.5' }}"
                                                        step="0.01" min="0" max="100"
                                                        oninput="updateConversionDeduction()">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                                <small class="text-muted d-none" id="conversionRateLabel"></small>
                                            </div>

                                            <!-- Conversion Cost -->
                                            <div class="col-md-3">
                                                <label class="form-label small">Conversion Cost</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">₹</span>
                                                    <input type="number" class="form-control form-control-sm"
                                                        id="conversion_cost" name="conversion_cost" value="0"
                                                        step="0.01">
                                                </div>

                                            </div>

                                            <!-- Receivable Amount -->
                                            <div class="col-md-3">
                                                <label class="form-label small">Receivable Amt ₹</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text"
                                                        id="receivableCurrencySymbol">₹</span>
                                                    <input type="number" class="form-control form-control-sm"
                                                        id="receivable_amount_foreign" value="0" readonly
                                                        style="background: #f1f5f9; font-weight: bold;">
                                                </div>
                                            </div>
                                            <!-- Hidden fields for GST/TDS in original currency (to pass to backend) -->
                                            <input type="hidden" id="gst_amount_original_currency"
                                                name="gst_amount_original_currency" value="0">
                                            <input type="hidden" id="tds_amount_original_currency"
                                                name="tds_amount_original_currency" value="0">
                                            <input type="hidden" id="subtotal_original_currency"
                                                name="subtotal_original_currency" value="0">
                                        </div>
                                    </div>
                                </div>

                                <!-- Purpose Comment -->
                                <div class="col-md-6">
                                    <label class="form-label small">Purpose Comment (for CA / internal)</label>
                                    <textarea class="form-control form-control-sm" name="purpose_comment" rows="2"
                                        placeholder="Describe what this invoice is for..."></textarea>
                                </div>

                                <!-- Terms & Conditions -->
                                <div class="col-md-6">
                                    <label class="form-label small">Terms & Conditions</label>
                                    <textarea class="form-control form-control-sm" name="terms_conditions" rows="2"
                                        placeholder="Payment terms, delivery terms, etc.">
                                                        Please make sure that full payment is credited to our Bank account.
                                                        Payment should be made within 3 days of receiving this invoice.
                                                        Accepted payment modes: Bank Transfer / PayPal only.
                                                        Late payment penalty of 4% will apply for every 3 days delay.
                                                        Source files will be delivered only after receiving full payment.
                                                        Please send payment acknowledgment promptly to avoid communication issues.
                                            </textarea>
                                </div>

                                <!-- Frequency and Settings -->
                                <div
                                    style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 20px;">
                                    <div class="form-group">
                                        <label class="form-label small">Frequency</label>
                                        <select name="frequency" id="frequency" class="form-control form-control-sm">
                                            <option value="monthly">Monthly</option>
                                            <option value="quarterly">Quarterly</option>
                                            <option value="yearly">Yearly</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label small">Due Day (of period)</label>
                                        <input type="number" name="due_day" id="due_day"
                                            class="form-control form-control-sm" placeholder="e.g. 5" min="1" max="31"
                                            value="5">
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label small">Default Reminder (days before)</label>
                                        <input type="number" name="reminder_days" id="reminder_days"
                                            class="form-control form-control-sm" value="3" min="0">
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label small">Active</label>
                                        <select name="status" id="status" class="form-control form-control-sm">
                                            <option value="1" selected>Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="col-12 d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-sm btn-warning" onclick="resetForm()">
                                        <i class="fas fa-redo"></i> Reset
                                    </button>
                                    <button type="submit" class="btn btn-sm btn-primary" id="submitBtn">
                                        <i class="fas fa-save"></i> Save & Create Upcoming Payment
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Proformas Tab -->
            <div class="tab-pane fade {{ $activeTab === 'proformas' ? 'show active' : '' }}" id="proformas"
                role="tabpanel">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Pending Proformas</span>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm" style="width:auto;" id="companyFilter">
                                <option value="">All Companies</option>
                                @foreach ( $companies as $company )
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-sm btn-outline-secondary" onclick="resetFilters()">
                                <i class="fas fa-redo"></i> Reset
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Invoice No</th>
                                        <th>Invoice Date</th>
                                        <th>Company</th>
                                        <th>Client</th>
                                        <th>Receivable amount</th>
                                        <th>Actual amount</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <!-- <th>Linked Payment</th> -->
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="proformasTableBody">
                                    @foreach ( $pendingProformas as $invoice )
                                    <tr data-company-id="{{ $invoice->company_id }}">
                                        <td>{{ $invoice->invoice_number }}</td>
                                        <td>{{ \Carbon\Carbon::parse ( $invoice->issue_date )->format ( 'd-m-Y' ) }}
                                        </td>
                                        <td>{{ $invoice->company->name }}</td>
                                        <td>{{ $invoice->client_details['name'] }}</td>
                                        </td>
                                        <td>
                                            @if( $invoice->currency !== 'INR' )
                                            <div class="fw-bold">
                                                {{ $invoice->currency === 'USD' ? '$' : $invoice->currency }}
                                                {{ number_format ( $invoice->original_currency_amount ?? $invoice->received_amount, 2 ) }}
                                            </div>
                                            <small class="text-muted">₹
                                                {{ number_format ( $invoice->received_amount, 2 ) }}</small>
                                            @else
                                            ₹ {{ number_format ( $invoice->received_amount, 2 ) }}
                                            @endif
                                        </td>
                                        <td>
                                            {{ $invoice->currency === 'USD' ? '$' : $invoice->currency }}
                                            {{ number_format ( $invoice->subtotal ?? $invoice->received_amount, 2 ) }}
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse ( $invoice->due_date )->format ( 'd-m-Y' ) }}

                                        <td>
                                            @if ( $invoice->status == 'pending' )
                                            <span class="badge bg-warning text-dark">Pending</span>
                                            @elseif( $invoice->status == 'upcoming' )
                                            <span class="badge bg-secondary">Upcoming</span>
                                            @endif
                                        </td>

                                        <!-- <td>
                                                    @if ( $invoice->upcomingPayment )
                                                        {{ $invoice->upcomingPayment->payment_number }}
                                                    @else
                                                        -
                                                    @endif
                                                </td> -->
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-secondary"
                                                    onclick="viewProforma({{ $invoice->id }})">
                                                    View
                                                </button>
                                                <button class="btn btn-outline-success btn-update-invoice"
                                                    data-invoice-id="{{ $invoice->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>

                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            </tbody>
                            </table>

                            <div class="d-flex justify-content-between align-items-center p-3 bg-light border-top">
                                <small class="text-muted">
                                    Showing {{ $pendingProformas->firstItem () ?? 0 }} to
                                    {{ $pendingProformas->lastItem () ?? 0 }} of {{ $pendingProformas->total () }}
                                    entries
                                </small>
                                <div>
                                    {{ $pendingProformas->appends ( [
        'tab' =>
            'proformas'
    ] )->links ( 'pagination::bootstrap-4' ) }}
                                </div>
                            </div>
                        </div>
                        @if ( $pendingProformas->count () == 0 )
                        <div class="text-center py-4">
                            <i class="fas fa-file-invoice fa-2x text-muted mb-2"></i>
                            <p class="text-muted">No pending proformas found.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Partial Payment / Split Modal -->

<div class="modal" id="partialPaymentModal"
    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1050;">
    <div class="modal-dialog">
        <div class="modal-content"
            style="background: white; width: 90%; max-width: 800px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); position: fixed; left: 50%; transform: translate(-50%, -50%); top: 50%;">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="partialPaymentLabel">Record Payment – Partial Amount Received</h5>
                    <small class="text-muted">
                        Client has paid less than the scheduled amount. Confirm how to handle this payment.
                    </small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="closeUserModal()"></button>
            </div>
            <div class="modal-body">
                <form id="partialPaymentForm" method="POST" action="{{ route ( 'admin.invoices.partial-payment' ) }}"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="invoice_id" id="pp_invoice_id">
                    <input type="hidden" name="action_type" id="pp_action_type" value="keep_balance">

                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small">Invoice No</label>
                            <input type="text" class="form-control form-control-sm" id="pp_invoice_no" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Company</label>
                            <input type="text" class="form-control form-control-sm" id="pp_company" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Client / Party</label>
                            <input type="text" class="form-control form-control-sm" id="pp_client" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Grand Total Amount</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control form-control-sm" id="pp_grand_amount" readonly>
                            </div>
                            <small class="text-muted" id="billed_amount">billed amount:</small>

                        </div>

                        <div class="col-md-4">
                            <label class="form-label small">Original Scheduled Amount</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control" id="pp_original_amount" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Amount Received Now *</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control" id="pp_received_amount" name="received_amount"
                                    min="0" step="0.01" required>
                            </div>
                            <small class="text-muted">Must be less than original amount</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Balance Amount (auto)</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control" id="pp_balance_amount" readonly>
                            </div>
                        </div>

                        <!-- TDS Section - UPDATED with name attributes -->
                        <div class="col-md-3">
                            <label class="form-label small">TDS Amount</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control" id="pp_tds_amount" readonly name="tds_amount">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">TDS Status *</label>
                            <select class="form-select form-select-sm" name="tds_status" id="pp_tds_status" required>
                                <option value="">Select TDS Status</option>
                                <option value="received">Received</option>
                                <option value="not_received">Not Received</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">TDS Payment Proof (Optional)</label>
                            <div class="input-group input-group-sm">
                                <input type="file" class="form-control" id="pp_tds_proof" name="tds_proof"
                                    accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>

                        <!-- Balance Action Section (keep existing) -->
                        <div class="col-12 mt-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="balance_action"
                                            id="keepBalanceOption" value="keep_balance" checked
                                            onchange="toggleBalanceOptions('keep')">
                                        <label class="form-check-label small" for="keepBalanceOption">
                                            <strong>Keep Balance & Create New Proforma</strong>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="balance_action"
                                            id="settleBalanceOption" value="settle_invoice"
                                            onchange="toggleBalanceOptions('settle')">
                                        <label class="form-check-label small" for="settleBalanceOption">
                                            <strong>Settle Invoice (Write-off Balance)</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div id="keepBalanceSection">
                                <div class="row g-3 mt-2">
                                    <div class="col-md-6">
                                        <label class="form-label small">New Due Date for Balance *</label>
                                        <input type="date" class="form-control form-control-sm" id="pp_new_due_date"
                                            name="new_due_date">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Payment Date *</label>
                                        <input type="date" class="form-control form-control-sm" id="pp_payment_date"
                                            name="payment_date" value="{{ date ( 'Y-m-d' ) }}" required>
                                    </div>
                                </div>
                            </div>

                            <div id="settleBalanceSection" style="display: none;">
                                <div class="row g-3 mt-2">
                                    <div class="col-md-6">
                                        <label class="form-label small">Payment Date *</label>
                                        <input type="date" class="form-control form-control-sm"
                                            id="pp_settle_payment_date" name="settle_payment_date"
                                            value="{{ date ( 'Y-m-d' ) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Write-off Reason</label>
                                        <select class="form-select form-select-sm" id="pp_writeoff_reason"
                                            name="writeoff_reason">
                                            <option value="">Select Reason</option>
                                            <option value="discount_given">Discount Given</option>
                                            <option value="goodwill_adjustment">Goodwill Adjustment</option>
                                            <option value="rounding_adjustment">Rounding Adjustment</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small">Internal Note (optional)</label>
                            <textarea class="form-control form-control-sm" id="pp_note" name="note" rows="2"
                                placeholder="e.g., Client paid 50,000 now, remaining 50,000 to be paid next month. TDS status: received with certificate."></textarea>
                        </div>

                        <div class="col-12">
                            <div class="border rounded p-2 bg-light" id="actionDescription">
                                <div class="small fw-semibold mb-1">What will happen now?</div>
                                <ul class="small mb-0" id="keepBalanceDescription">
                                    <li>A <strong>taxable invoice</strong> will be generated for the amount received
                                        now.</li>
                                    <li>A <strong>new proforma</strong> will be created for the balance with the new due
                                        date.</li>
                                    <li>The original proforma will be marked as <strong>Replaced</strong>.</li>
                                    <li>TDS status will be recorded and can be tracked separately.</li>
                                </ul>
                                <ul class="small mb-0" id="settleBalanceDescription" style="display: none;">
                                    <li>A <strong>taxable invoice</strong> will be generated for the full original
                                        amount.</li>
                                    <li>The invoice will be marked as <strong>Paid</strong> with the received amount.
                                    </li>
                                    <li>The balance amount will be recorded as a <strong>write-off</strong>.</li>
                                    <li>No new proforma will be created for the balance.</li>
                                    <li>TDS status will be recorded for reconciliation.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-between">
                <div class="small text-muted">
                    Once confirmed, this action cannot be undone.
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="closeUserModal()"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-sm btn-primary" id="pp_confirm_btn">
                        Confirm Payment
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Invoice Modal -->
<div class="modal" id="viewInvoiceModal"
    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1050;">
    <div class="modal-dialog">

        <div class="modal-content" style="background: white; width: 90%; max-width: 800px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); position
                                                        : fixed;
                                                            left: 50%;
                                                            transform: translate(-50%, -50%);
                                                            top: 50%;">
            <div class="modal-header">
                <h5 class="modal-title">Invoice Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="invoiceDetailsContent">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printInvoice()">
                    <i class="fas fa-print"></i> Invoice
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Send Invoice Modal -->
<div class="modal" id="sendInvoiceModal"
    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1050;">
    <div class="modal-dialog">
        <div class="modal-content"
            style="background: white; width: 90%; max-width: 800px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); position: fixed; left: 50%; transform: translate(-50%, -50%); top: 50%;">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="sendInvoiceLabel">Send Invoice via Email</h5>
                    <small class="text-muted">
                        Send invoice/proforma to client with a custom message
                    </small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="closeSendModal()"></button>
            </div>
            <div class="modal-body">
                <form id="sendInvoiceForm" method="POST" action="{{ route ( 'admin.invoices.send-email' ) }}">
                    @csrf
                    <input type="hidden" name="invoice_id" id="send_invoice_id">
                    <input type="hidden" name="invoice_type" id="send_invoice_type">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small">Invoice Number</label>
                            <input type="text" class="form-control form-control-sm" id="send_invoice_no" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Invoice Type</label>
                            <input type="text" class="form-control form-control-sm" id="send_invoice_type_display"
                                readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">To Email *</label>
                            <input type="email" class="form-control form-control-sm" id="send_to_email" name="to_email"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">CC Email (optional)</label>
                            <input type="text" class="form-control form-control-sm" id="send_cc_email" name="cc_email"
                                placeholder="comma-separated emails">
                        </div>

                        <div class="col-12">
                            <label class="form-label small">Subject *</label>
                            <input type="text" class="form-control form-control-sm" id="send_subject" name="subject"
                                required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small">Message Body *</label>
                            <textarea class="form-control form-control-sm" id="send_message" name="message" rows="6"
                                required></textarea>
                            <small class="text-muted">
                                Available variables: {client_name}, {invoice_no}, {due_date}, {amount}, {company_name}
                            </small>
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="attach_pdf" name="attach_pdf"
                                    checked>
                                <label class="form-check-label small" for="attach_pdf">
                                    Attach PDF copy of invoice
                                </label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="border rounded p-2 bg-light">
                                <div class="small fw-semibold mb-1">Preview:</div>
                                <div class="small text-muted mb-2">To: <span id="preview_email"></span></div>
                                <div class="small text-muted mb-2">Subject: <span id="preview_subject"></span></div>
                                <div class="small text-muted" id="preview_message"
                                    style="max-height: 100px; overflow-y: auto;"></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-between">
                <div class="small text-muted">
                    Email will be sent immediately upon confirmation
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="closeSendModal()"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-sm btn-primary" id="send_confirm_btn">
                        <i class="fas fa-paper-plane"></i> Send Invoice
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Edit Invoice Modal -->
<div class="modal fade" id="editInvoiceModal" tabindex="-1" aria-labelledby="editInvoiceModalLabel" aria-hidden="true"
    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1050;">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content"
            style="background: white; width: 90%; max-width: 800px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); position: fixed; left: 50%; transform: translate(-50%, -50%); top: 50%;">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editInvoiceModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Proforma Invoice
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form id="editInvoiceForm" method="POST">
                @csrf
                <input type="hidden" id="editInvoiceId" name="id">
                <input type="hidden" id="editInvoiceType" name="type" value="proforma">

                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Edit invoice details. Changes will be reflected in the
                        invoice template.
                    </div>

                    <div class="row g-3">
                        <!-- Top Row: Company, Invoice Type, Dates -->
                        <div class="col-md-3">
                            <label class="form-label small">Company *</label>
                            <select class="form-select form-select-sm" name="company_id" required
                                id="editCompanySelect">
                                <option value="">Select Company</option>
                                @foreach ( $companies as $company )
                                <option value="{{ $company->id }}" data-gstin="{{ $company->gstin }}"
                                    data-address="{{ $company->address }}"
                                    data-currency="{{ $company->currency ?? 'INR' }}">
                                    {{ $company->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Invoice Date *</label>
                            <input type="date" class="form-control form-control-sm" name="issue_date" id="editIssueDate"
                                required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Due Date *</label>
                            <input type="date" class="form-control form-control-sm" name="due_date" id="editDueDate"
                                required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Invoice Number</label>
                            <input type="text" class="form-control form-control-sm bg-light" id="editInvoiceNumber"
                                readonly>
                            <input type="hidden" name="invoice_number" id="editInvoiceNumberHidden">
                        </div>

                        <!-- Client Details -->
                        <div class="col-md-3">
                            <label class="form-label small">Client Name *</label>
                            <input type="text" class="form-control form-control-sm" name="client_name"
                                id="editClientName" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Client Email *</label>
                            <input type="email" class="form-control form-control-sm" name="client_email"
                                id="editClientEmail" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Mobile Number</label>
                            <input type="text" class="form-control form-control-sm" name="mobile_number"
                                id="editMobileNumber" maxlength="10"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Client GSTIN (optional)</label>
                            <input type="text" class="form-control form-control-sm" name="client_gstin"
                                id="editClientGstin">
                        </div>

                        <!-- Billing Address -->
                        <div class="col-12">
                            <label class="form-label small">Billing Address *</label>
                            <textarea class="form-control form-control-sm" name="billing_address"
                                id="editBillingAddress" rows="2" required></textarea>
                        </div>

                        <!-- Line Items Section -->
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label small mb-0">Line Items</label>
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                    onclick="addEditLineItem()">
                                    <i class="fas fa-plus"></i> Add Item
                                </button>
                            </div>
                            <div id="editLineItemsHeader"
                                class="row g-2 mb-2 fw-semibold small text-muted px-2 border-bottom pb-1">
                                <div class="col-md-4">Description</div>
                                <div class="col-md-2">Quantity</div>
                                <div class="col-md-2">Rate
                                    <select id="editHeaderCurrency"
                                        class="form-select form-select-sm d-inline-block w-auto py-0 px-2 fw-bold text-primary border"
                                        style="height: 24px; font-size: 0.75rem; border-radius: 4px; background-color: #f8fafc; cursor: pointer; transition: all 0.2s;"
                                        onchange="updateEditCurrencySelection(this)">
                                        <option value="USD">USD</option>
                                        <option value="INR">INR</option>
                                    </select>
                                </div>
                                <div class="col-md-2" id="headerAmtLabel">Total Amt</div>
                                <div class="col-md-1"></div>
                            </div>
                            <div id="editLineItemsContainer">
                                <!-- Line items will be dynamically added here -->
                            </div>
                        </div>

                        <!-- Tax and Currency Section -->
                        <div class="col-12">
                            <h6 class="mb-3 d-none" id="edit_tax_heading">Tax & Currency Details</h6>

                            <!-- GST Section -->
                            <div id="edit_gst_section" class="d-none"
                                style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #f0f9ff;">
                                <div class="row align-items-center mb-2">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="editApplyGST"
                                                name="apply_gst" value="1" onchange="calculateEditTax()">
                                            <label class="form-check-label small fw-medium" for="editApplyGST">
                                                Apply GST
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label small">GST %</label>
                                        <input type="number" class="form-control form-control-sm" id="editGstPercentage"
                                            name="gst_percentage" value="18" step="0.01" oninput="calculateEditTax()">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">GST Amount</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text edit-tax-symbol">$</span>
                                            <input type="number" class="form-control form-control-sm"
                                                id="editGstAmountDisplay" value="0" readonly
                                                style="background: #e0f2fe;">
                                            <input type="hidden" id="editGstAmount" name="gst_amount" value="0">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Amount After GST</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text edit-tax-symbol">$</span>
                                            <input type="number" class="form-control form-control-sm"
                                                id="editAmountAfterGst" value="0" readonly style="background: #e0f2fe;">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TDS Section -->
                            <div id="edit_tds_section" class="d-none"
                                style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #fef2f2;">
                                <div class="row align-items-center mb-2">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="editApplyTDS"
                                                name="apply_tds" value="1" onchange="calculateEditTax()">
                                            <label class="form-check-label small fw-medium" for="editApplyTDS">
                                                Apply TDS
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label small">TDS %</label>
                                        <input type="number" class="form-control form-control-sm" id="editTdsPercentage"
                                            name="tds_percentage" value="10" step="0.01" oninput="calculateEditTax()">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">TDS Amount</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text edit-tax-symbol">$</span>
                                            <input type="number" class="form-control form-control-sm"
                                                id="editTdsAmountDisplay" value="0" readonly
                                                style="background: #fee2e2;">
                                            <input type="hidden" id="editTdsAmount" name="tds_amount" value="0">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Amount After TDS</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text edit-tax-symbol">$</span>
                                            <input type="number" class="form-control form-control-sm"
                                                id="editAmountAfterTds" value="0" readonly style="background: #fee2e2;">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Currency Section -->
                            <div id="edit_currency_section"
                                style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #f8fafc;">
                                <h6 class="mb-3 small fw-bold hide-if-inr" id="edit_currency_details_heading">Currency
                                    Details</h6>
                                <div class="row g-3">
                                    <!-- Currency Selection -->
                                    <div class="col-md-3">
                                        <label class="form-label small">Currency</label>
                                        <select class="form-select form-select-sm" name="currency"
                                            id="editCurrencySelect" onchange="updateEditCurrencySelection(this)">
                                            <option value="USD">USD</option>
                                            <option value="INR">INR</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3 hide-if-inr">
                                        <label class="form-label small" id="editForeignAmountLabel">Amount In
                                            ($)</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text" id="editCurrencySymbol">$</span>
                                            <input type="number" class="form-control form-control-sm"
                                                id="editAmountInForeign" name="amount_in_foreign" value="0" step="0.01"
                                                oninput="updateEditCurrencyConversionFromForeign()">
                                        </div>
                                        <small class="text-muted" id="editForeignAmountHelp">Enter amount in selected
                                            currency</small>
                                    </div>

                                    <div class="col-md-3 hide-if-inr">
                                        <label class="form-label small" id="editRateLabelDisplay">USD to INR</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">1 <span
                                                    class="edit-selected-currency">USD</span> = ₹</span>
                                            <input type="number" class="form-control form-control-sm"
                                                id="edit_base_conversion_rate" name="base_conversion_rate" value="83.00"
                                                step="0.01" oninput="updateEditCurrencyConversion()">
                                        </div>
                                    </div>

                                    <div class="col-md-3 hide-if-inr">
                                        <label class="form-label small">Amount in ₹</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" class="form-control form-control-sm"
                                                id="editAmountInInr" value="0" readonly style="background: #f1f5f9;">
                                            <input type="hidden" id="editConvertedAmount" name="converted_amount"
                                                value="0">
                                        </div>
                                        <small class="text-muted" id="editConversionDisplay">$0 = ₹0</small>
                                    </div>

                                    <!-- Conversion Rate -->
                                    <div class="col-md-3 hide-if-inr">
                                        <label class="form-label small">Conversion Rate %</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" class="form-control form-control-sm"
                                                id="editConversionRatePercentage" name="conversion_rate_percentage"
                                                value="0" step="0.01" min="0" max="100"
                                                oninput="updateEditConversionDeduction()">
                                            <span class="input-group-text">%</span>
                                        </div>
                                        <small class="text-muted" id="editConversionRateLabel">Deduction
                                            percentage</small>
                                    </div>

                                    <!-- Conversion Cost -->
                                    <div class="col-md-3 hide-if-inr">
                                        <label class="form-label small">Conversion Cost</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" class="form-control form-control-sm"
                                                id="editConversionCost" name="conversion_cost" value="0" step="0.01">
                                        </div>
                                    </div>

                                    <!-- Receivable Amount -->
                                    <div class="col-md-3">
                                        <label class="form-label small">Receivable Amt ₹</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text" id="editReceivableCurrencySymbol">₹</span>
                                            <input type="number" class="form-control form-control-sm"
                                                id="editReceivableAmountForeign" value="0" readonly
                                                style="background: #f1f5f9; font-weight: bold;">
                                        </div>
                                    </div>

                                    <!-- Hidden fields for totals -->
                                    <input type="hidden" id="editGstAmountOriginalCurrency"
                                        name="gst_amount_original_currency" value="0">
                                    <input type="hidden" id="editTdsAmountOriginalCurrency"
                                        name="tds_amount_original_currency" value="0">
                                    <input type="hidden" id="editSubtotalOriginalCurrency"
                                        name="subtotal_original_currency" value="0">
                                    <input type="hidden" id="editSubtotal" name="subtotal" value="0">
                                    <input type="hidden" id="editTotalAmount" name="total_amount" value="0">
                                </div>
                            </div>
                        </div>

                        <!-- Purpose Comment -->
                        <div class="col-md-6">
                            <label class="form-label small">Purpose Comment (for CA / internal)</label>
                            <textarea class="form-control form-control-sm" name="purpose_comment"
                                id="editPurposeComment" rows="2"></textarea>
                        </div>

                        <!-- Terms & Conditions -->
                        <div class="col-md-6">
                            <label class="form-label small">Terms & Conditions</label>
                            <textarea class="form-control form-control-sm" name="terms_conditions"
                                id="editTermsConditions" rows="2"></textarea>
                        </div>

                        <!-- Frequency and Settings -->
                        <div
                            style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 20px;">
                            <div class="form-group">
                                <label class="form-label small">Frequency</label>
                                <select name="frequency" id="editFrequency" class="form-control form-control-sm">
                                    <option value="monthly">Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label small">Due Day (of period)</label>
                                <input type="number" name="due_day" id="editDueDay" class="form-control form-control-sm"
                                    min="1" max="31" value="5">
                            </div>

                            <div class="form-group">
                                <label class="form-label small">Default Reminder (days before)</label>
                                <input type="number" name="reminder_days" id="editReminderDays"
                                    class="form-control form-control-sm" value="3" min="0">
                            </div>

                            <div class="form-group">
                                <label class="form-label small">Active</label>
                                <select name="status" id="editStatus" class="form-control form-control-sm">
                                    <option value="active">Yes</option>
                                    <option value="inactive">No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="editInvoiceBtn">
                        <i class="fas fa-save me-1"></i>Update Invoice
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Define conversionRates in global scope
    let conversionRates = {};

    // Initialize line items count
    let lineItemCount = 1;
    const currencySymbols = {
        'INR': '₹',
        'USD': '$',
        'EUR': '€',
        'GBP': '£'
    };

    // Company details update
    function updateCompanyDetails() {
        const companySelect = document.getElementById('companySelect');
        const selectedOption = companySelect.options[companySelect.selectedIndex];
        const currencySelect = document.getElementById('currencySelect');

        if (selectedOption.value) {
            // Get company currency from data attribute
            const companyCurrency = selectedOption.getAttribute('data-currency') || 'INR';

            // Set the currency select to company's default currency
            if (currencySelect) {
                currencySelect.value = companyCurrency;
                console.log('Company currency set to:', companyCurrency);

                // Update currency symbols and conversion rate
                updateCurrencySelection();
            }

            // Optional: Auto-fill company GSTIN in client GSTIN field if empty
            const clientGSTINField = document.querySelector('input[name="client_gstin"]');
            if (clientGSTINField && !clientGSTINField.value && selectedOption.getAttribute('data-gstin')) {
                clientGSTINField.value = selectedOption.getAttribute('data-gstin');
            }
        }
    }
    // Tax calculation
    window.calculateTax = function() {
        console.log('calculateTax called');

        if (window.taxCalculationInProgress) {
            return;
        }

        window.taxCalculationInProgress = true;

        try {
            const subtotal = window.currentSubtotal || 0;
            const applyGst = document.getElementById('applyGST').checked;
            const gstPercentage = parseFloat(document.getElementById('gst_percentage').value) || 0;
            const applyTds = document.getElementById('applyTDS').checked;
            const tdsPercentage = parseFloat(document.getElementById('tds_percentage').value) || 0;

            // Get currency info
            const currencySelect = document.getElementById('currencySelect');
            const currency = currencySelect ? currencySelect.value : 'USD';
            const baseConversionRate = parseFloat(document.getElementById('base_conversion_rate').value) || 83.0;

            console.log('Tax settings:', {
                subtotal,
                applyGst,
                gstPercentage,
                applyTds,
                tdsPercentage,
                currency,
                baseConversionRate
            });

            let gstAmount = 0;
            let tdsAmount = 0;
            let amountAfterGst = subtotal;
            let amountForTds = subtotal;
            let finalAmount = subtotal;

            // Calculate GST
            if (applyGst && gstPercentage > 0) {
                gstAmount = (subtotal * gstPercentage) / 100;
                amountAfterGst = subtotal + gstAmount;
                amountForTds = subtotal;
                finalAmount = amountAfterGst;
            }

            // Calculate TDS (TDS is calculated on subtotal, not on GST amount)
            if (applyTds && tdsPercentage > 0) {
                tdsAmount = (subtotal * tdsPercentage) / 100;
                finalAmount = amountAfterGst - tdsAmount;
            }

            // Display amounts in selected currency
            document.getElementById('gst_amount_display').value = gstAmount.toFixed(2);
            document.getElementById('tds_amount_display').value = tdsAmount.toFixed(2);
            document.getElementById('amount_after_gst').value = amountAfterGst.toFixed(2);
            document.getElementById('amount_after_tds').value = subtotal.toFixed(2) - tdsAmount.toFixed(2);

            // Store final amount
            window.finalAmount = finalAmount;

            // Store GST/TDS amounts in the original currency
            document.getElementById('gst_amount_original_currency').value = gstAmount.toFixed(2);
            document.getElementById('tds_amount_original_currency').value = tdsAmount.toFixed(2);
            document.getElementById('subtotal_original_currency').value = subtotal.toFixed(2);

            // Convert to INR for storage (if currency is not INR)
            let gstAmountInINR = gstAmount;
            let tdsAmountInINR = tdsAmount;

            if (currency !== 'INR') {
                gstAmountInINR = gstAmount * baseConversionRate;
                tdsAmountInINR = tdsAmount * baseConversionRate;
            }

            // Store converted amounts in hidden fields
            document.getElementById('gst_amount').value = gstAmountInINR.toFixed(2);
            document.getElementById('tds_amount').value = tdsAmountInINR.toFixed(2);

            // Update amount in foreign field with final amount (after all taxes)
            const amountInForeignField = document.getElementById('amount_in_foreign');
            if (amountInForeignField) {
                amountInForeignField.value = amountAfterGst.toFixed(2) ?? subtotal.toFixed(2);
            }

            console.log('Tax calculation complete:', {
                subtotal: subtotal.toFixed(2),
                gstAmount: gstAmount.toFixed(2),
                tdsAmount: tdsAmount.toFixed(2),
                finalAmount: finalAmount.toFixed(2),
                gstAmountINR: gstAmountInINR.toFixed(2),
                tdsAmountINR: tdsAmountInINR.toFixed(2)
            });

            // Trigger currency conversion
            setTimeout(() => {
                updateCurrencyConversionFromForeign();
            }, 0);

        } catch (error) {
            console.error('Error in calculateTax:', error);
        } finally {
            window.taxCalculationInProgress = false;
        }
    }

    // Line item calculations
    window.calculateLineAmount = function() {
        let subtotal = 0;
        console.log('calculateLineAmount called');

        document.querySelectorAll('.line-item').forEach((item, index) => {
            const quantity = parseFloat(item.querySelector('.quantity').value) || 0;
            const rate = parseFloat(item.querySelector('.rate').value) || 0;
            const amount = quantity * rate;

            // Update amount field
            const amountField = item.querySelector('.amount');
            amountField.value = amount.toFixed(2);

            subtotal += amount;
        });

        // Store subtotal in a data attribute or variable
        window.currentSubtotal = subtotal;

        console.log('Calculated subtotal:', subtotal);

        // First calculate tax to get the amount after GST
        calculateTax();

        // Then update the Amount In ($) field with amount after GST
        setTimeout(() => {
            const applyGst = document.getElementById('applyGST').checked;
            const gstPercentage = parseFloat(document.getElementById('gst_percentage').value) || 0;

            let amountAfterGst = subtotal;
            if (applyGst && gstPercentage > 0) {
                const gstAmount = (subtotal * gstPercentage) / 100;
                amountAfterGst = subtotal + gstAmount;
            } else {
                amountAfterGst = subtotal;
            }
            console.log('applyGst', applyGst, 'gstPercentage', gstPercentage)
            console.log('subtotal', subtotal, 'amountAfterGst', amountAfterGst);
            // Update amount in foreign currency field with amount after GST
            const amountInForeignField = document.getElementById('amount_in_foreign');
            if (amountInForeignField) {
                amountInForeignField.value = amountAfterGst.toFixed(2);
            }

            // Now update currency conversion
            updateCurrencyConversionFromForeign();
        }, 0);
    }
    // Debounced version of calculateLineAmount
    window.debouncedCalculateLineAmount = (function() {
        let timeout;
        return function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                calculateLineAmount();
            }, 100);
        };
    })();

    // Add line item
    window.addLineItem = function() {
        const container = document.getElementById('lineItemsContainer');
        const itemCount = container.querySelectorAll('.line-item').length;

        const newItem = document.createElement('div');
        newItem.className = 'line-item row g-2 align-items-end mb-2';
        newItem.innerHTML = `
                                            <div class="col-md-4">
                                                <input type="text" class="form-control form-control-sm" 
                                                       name="line_items[${itemCount}][description]" 
                                                       placeholder="Description" required>
                                            </div>
                                            <div class="col-md-2">
                                                <input type="number" class="form-control form-control-sm quantity" 
                                                       name="line_items[${itemCount}][quantity]" 
                                                       placeholder="Qty" value="1" min="1" step="0.01" required
                                                       oninput="debouncedCalculateLineAmount()">
                                            </div>
                                            <div class="col-md-2">
                                                <input type="number" class="form-control form-control-sm rate" 
                                                       name="line_items[${itemCount}][rate]" 
                                                       placeholder="Rate" step="0.01" min="0" required
                                                       oninput="debouncedCalculateLineAmount()">
                                            </div>
                                            <div class="col-md-2">
                                                <input type="number" class="form-control form-control-sm amount" 
                                                       name="line_items[${itemCount}][amount]" 
                                                       placeholder="Amount" step="0.01" readonly>
                                            </div>
                                            <div class="col-md-1">
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        onclick="removeLineItem(this)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        `;

        container.appendChild(newItem);
        updateDeleteButtons();
        debouncedCalculateLineAmount();
    }

    // Remove line item
    window.removeLineItem = function(button) {
        const container = document.getElementById('lineItemsContainer');
        const items = container.querySelectorAll('.line-item');

        if (items.length > 1) {
            const item = button.closest('.line-item');
            item.remove();
            renumberLineItems();
            updateDeleteButtons();
            calculateLineAmount();
        }
    }

    // Renumber line items
    window.renumberLineItems = function() {
        const container = document.getElementById('lineItemsContainer');
        const items = container.querySelectorAll('.line-item');

        items.forEach((item, index) => {
            item.querySelector('[name*="[description]"]').name = `line_items[${index}][description]`;
            item.querySelector('[name*="[quantity]"]').name = `line_items[${index}][quantity]`;
            item.querySelector('[name*="[rate]"]').name = `line_items[${index}][rate]`;
            item.querySelector('[name*="[amount]"]').name = `line_items[${index}][amount]`;
        });
    }

    // Update delete buttons
    window.updateDeleteButtons = function() {
        const container = document.getElementById('lineItemsContainer');
        const items = container.querySelectorAll('.line-item');
        const deleteButtons = container.querySelectorAll('.btn-danger');

        if (items.length === 1) {
            deleteButtons.forEach(btn => btn.disabled = true);
        } else {
            deleteButtons.forEach(btn => btn.disabled = false);
        }
    }


    // Currency conversion functions
    window.updateCurrencySelection = async function() {
        const currencySelect = document.getElementById('currencySelect');
        const currency = currencySelect.value;

        // Requirement 4: USD removes GST and TDS
        const gstSection = document.getElementById('gst_section');
        const tdsSection = document.getElementById('tds_section');
        const applyGST = document.getElementById('applyGST');
        const applyTDS = document.getElementById('applyTDS');

        if (currency === 'USD') {
            if (gstSection) gstSection.classList.add('d-none');
            if (tdsSection) tdsSection.classList.add('d-none');
            if (applyGST) applyGST.checked = false;
            if (applyTDS) applyTDS.checked = false;
        } else {
            if (gstSection) gstSection.classList.remove('d-none');
            if (tdsSection) tdsSection.classList.remove('d-none');
            // For INR, ensure GST/TDS are showing (user can still uncheck if needed)
            if (applyGST) applyGST.checked = true;
        }

        // Requirement 3: INR simplify conversion block
        const currencySectionRows = document.querySelectorAll('#currency_section .row.g-3 > .col-md-3');
        if (currency === 'INR') {
            currencySectionRows.forEach(col => {
                // Show only receivable amount
                if (col.querySelector('#receivable_amount_foreign')) {
                    col.classList.remove('d-none');
                } else if (!col.classList.contains('d-none')) {
                    // Mark as d-none if it's not the receivable column
                    const label = col.querySelector('label');
                    if (label && label.textContent !== 'Currency') {
                        col.classList.add('d-none-temp'); // Use a temp class to hide
                        col.style.display = 'none';
                    }
                }
            });
        } else {
            // Show all for USD
            currencySectionRows.forEach(col => {
                if (col.classList.contains('d-none-temp')) {
                    col.classList.remove('d-none-temp');
                    col.style.display = 'block';
                }
            });
        }

        updateCurrencySymbols(currency);
        await updateConversionRateForCurrency(currency);
        updateCurrencyConversionFromForeign();
        calculateTax();
    }

    window.updateCurrencyConversionFromForeign = function() {
        const amountInForeign = parseFloat(document.getElementById('amount_in_foreign').value) || 0;
        const currencySelect = document.getElementById('currencySelect');
        const currency = currencySelect ? currencySelect.value : 'USD';
        const baseConversionRate = parseFloat(document.getElementById('base_conversion_rate').value) || 83.0;

        // Calculate amount in INR
        let amountInINR = 0;
        if (currency === 'INR') {
            amountInINR = amountInForeign;
        } else {
            amountInINR = amountInForeign * baseConversionRate;
        }

        // Update display fields
        document.getElementById('amount_in_inr').value = amountInINR.toFixed(2);
        document.getElementById('convertedAmount').value = amountInINR.toFixed(2);

        // Update conversion display
        const conversionDisplay = document.getElementById('conversionDisplay');
        if (conversionDisplay) {
            conversionDisplay.textContent =
                `${amountInForeign.toFixed(2)} ${currency} = ₹${amountInINR.toFixed(2)}`;
        }

        // Update window.finalAmount for tax calculation
        window.finalAmount = amountInINR;

        // Trigger conversion deduction calculation
        updateConversionDeduction();
    }


    window.updateCurrencyConversion = function() {
        updateCurrencyConversionFromForeign();
    }

    window.updateReceivableAmount = function() {
        updateCurrencyConversionFromForeign();
    }
    window.updateEditConversionDeduction = function() {
        const amountInINR = parseFloat(document.getElementById('editAmountInInr').value) || 0;
        const editCurrencySelect = document.getElementById('editCurrencySelect');
        const currency = editCurrencySelect ? editCurrencySelect.value : 'USD';

        let conversionRatePercentage = 0;
        if (currency !== 'INR') {
            conversionRatePercentage = parseFloat(document.getElementById('editConversionRatePercentage').value) || 0;
        }

        // Calculate conversion cost (deduction amount)
        const conversionCost = (amountInINR * conversionRatePercentage) / 100;

        // Calculate receivable amount
        const receivableAmount = Math.max(0, amountInINR - conversionCost);

        // Update fields
        document.getElementById('editConversionCost').value = conversionCost.toFixed(2);
        document.getElementById('editReceivableAmountForeign').value = receivableAmount.toFixed(2);

        // Update labels for clarity
        const conversionRateLabel = document.getElementById('editConversionRateLabel');
        if (conversionRateLabel) {
            conversionRateLabel.textContent =
                `Deducting ${conversionRatePercentage}% = ₹${conversionCost.toFixed(2)}`;
        }
    }
    window.updateConversionDeduction = function() {
        const amountInINR = parseFloat(document.getElementById('amount_in_inr').value) || 0;
        const currencySelect = document.getElementById('currencySelect');
        const currency = currencySelect ? currencySelect.value : 'USD';

        let conversionRatePercentage = 0;
        if (currency !== 'INR') {
            conversionRatePercentage = parseFloat(document.getElementById('conversion_rate_percentage').value) || 0;
        }

        // Calculate conversion cost (deduction amount)
        let conversionCost = (amountInINR * conversionRatePercentage) / 100;
        if (currency === 'INR') {
            conversionCost = 0;
        }
        // Calculate receivable amount
        const receivableAmount = Math.max(0, amountInINR - conversionCost);

        // Update fields
        document.getElementById('conversion_cost').value = conversionCost.toFixed(2);
        document.getElementById('receivable_amount_foreign').value = receivableAmount.toFixed(2);

        // Requirement 6: remove conversion cost text
        const conversionRateLabel = document.getElementById('conversionRateLabel');
        if (conversionRateLabel) {
            conversionRateLabel.textContent = '';
            conversionRateLabel.classList.add('d-none');
        }
    }


    function updateCurrencySymbols(currency) {
        const symbols = {
            'INR': '₹',
            'USD': '$',
            'EUR': '€',
            'GBP': '£'
        };

        const symbol = symbols[currency] || '$';

        document.getElementById('currencySymbol').textContent = symbol;
        // document.getElementById('receivableCurrencySymbol').textContent = '₹'; // Always INR for receivable

        const foreignAmountLabel = document.getElementById('foreignAmountLabel');
        if (foreignAmountLabel) {
            foreignAmountLabel.textContent = `Enter amount in ${currency}`;
        }

        // Update currency indicators in conversion rate fields
        document.querySelectorAll('.selected-currency').forEach(el => el.textContent = currency);

        // Update tax section symbols
        document.querySelectorAll('.tax-symbol').forEach(el => el.textContent = symbol);

        const rateLabelDisplay = document.getElementById('rateLabelDisplay');
        if (rateLabelDisplay) {
            rateLabelDisplay.textContent = `${currency} to INR`;
        }
    }

    async function updateConversionRateForCurrency(currency) {
        if (currency === 'INR') {
            document.getElementById('base_conversion_rate').value = '1.0';
            updateRateDisplay();
            return;
        }

        // Use cached rate if available
        if (conversionRates[currency]) {
            const rate = conversionRates[currency];
            document.getElementById('base_conversion_rate').value = rate.toFixed(2);
            updateRateDisplay();
            return;
        }

        // Fallback rates while fetching or on failure
        const defaultRates = {
            'USD': 92.89,
            'EUR': 100.0,
            'GBP': 118.0
        };
        const fallbackRate = defaultRates[currency] || 92.89;
        document.getElementById('base_conversion_rate').value = fallbackRate.toFixed(2);
        updateRateDisplay();

        try {
            await fetchConversionRates();
            if (conversionRates[currency]) {
                document.getElementById('base_conversion_rate').value = conversionRates[currency].toFixed(2);
                updateRateDisplay();
            }
        } catch (error) {
            console.error('Failed to fetch conversion rate:', error);
            // Already set to fallback above
        }
    }

    function updateRateDisplay() {
        // This function is kept for compatibility but the UI now uses the base_conversion_rate input
        const currencySelect = document.getElementById('currencySelect');
        const currency = currencySelect ? currencySelect.value : 'USD';
        const baseConversionRate = parseFloat(document.getElementById('base_conversion_rate').value) || 83.0;

        const rateLabelDisplay = document.getElementById('rateLabelDisplay');
        if (rateLabelDisplay) {
            rateLabelDisplay.textContent = `${currency} to INR`;
        }
    }

    // Fetch conversion rates
    async function fetchConversionRates() {
        console.log('Fetching conversion rates...');

        try {
            const currencySelect = document.getElementById('currencySelect');
            const currency = currencySelect ? currencySelect.value : 'USD';

            // Fetch directly from USD to INR if that's the current currency
            const fromCurrency = currency !== 'INR' ? currency : 'USD';
            const response = await fetch(`https://api.frankfurter.app/latest?from=${fromCurrency}&to=INR`);

            if (response.ok) {
                const data = await response.json();
                console.log('Conversion rate fetched:', data.rates);

                if (data.rates && data.rates['INR']) {
                    const rate = data.rates['INR'];
                    conversionRates[fromCurrency] = rate;

                    if (currency === fromCurrency) {
                        const baseRateField = document.getElementById('base_conversion_rate');
                        if (baseRateField) baseRateField.value = rate.toFixed(2);

                        // Also update Edit modal if it's set to the same currency
                        const editBaseRateField = document.getElementById('edit_base_conversion_rate');
                        if (editBaseRateField) {
                            editBaseRateField.value = rate.toFixed(2);
                        }

                        updateCurrencyConversionFromForeign();
                        if (typeof updateEditCurrencyConversion === 'function') {
                            updateEditCurrencyConversion();
                        }
                    }
                }
            }
        } catch (error) {
            console.error('Error fetching conversion rates:', error);
        }
    }

    // Reset form
    function resetForm() {
        const createProformaForm = document.getElementById('createProformaForm');

        if (createProformaForm) {
            createProformaForm.reset();

            // Reset currency specific fields
            document.getElementById('amount_in_foreign').value = '0';
            document.getElementById('conversion_cost').value = '0';
            document.getElementById('receivable_amount_foreign').value = '0';

            document.getElementById('amount_in_inr').value = '0';
            document.getElementById('convertedAmount').value = '0';

            // Reset currency selection to USD
            document.getElementById('currencySelect').value = 'USD';
            updateCurrencySymbols('USD');
            updateRateDisplay();

            // Reset tax checkboxes
            document.getElementById('applyGST').checked = true;
            document.getElementById('applyTDS').checked = false;

            // Reset tax amounts
            document.getElementById('gst_amount_display').value = '0';
            document.getElementById('gst_amount').value = '0';
            document.getElementById('amount_after_gst').value = '0';
            document.getElementById('tds_amount_display').value = '0';
            document.getElementById('tds_amount').value = '0';
            document.getElementById('amount_after_tds').value = '0';

            // Reset line items
            const lineItemsContainer = document.getElementById('lineItemsContainer');
            if (lineItemsContainer) {
                lineItemsContainer.innerHTML = `
                                                    <div class="line-item row g-2 align-items-end mb-2">
                                                        <div class="col-md-4">
                                                            <input type="text" class="form-control form-control-sm" name="line_items[0][description]" placeholder="Description" required>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="number" class="form-control form-control-sm quantity" name="line_items[0][quantity]" placeholder="Qty" value="1" min="1" step="0.01" required oninput="debouncedCalculateLineAmount()">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="number" class="form-control form-control-sm rate" name="line_items[0][rate]" placeholder="Rate" step="0.01" min="0" required oninput="debouncedCalculateLineAmount()">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="number" class="form-control form-control-sm amount" name="line_items[0][amount]" placeholder="Amount" step="0.01" readonly>
                                                        </div>
                                                        <div class="col-md-1">
                                                            <button type="button" class="btn btn-sm btn-danger" onclick="removeLineItem(this)" disabled>
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                `;
            }

            // Reset calculation variables
            window.currentSubtotal = 0;
            window.finalAmount = 0;

            // Clear validation errors
            const errorElements = document.querySelectorAll('.is-invalid');
            errorElements.forEach(element => {
                element.classList.remove('is-invalid');
            });

            // Reinitialize calculations
            setTimeout(() => {
                calculateLineAmount();
                calculateTax();
                updateCurrencyConversionFromForeign();
                updateConversionDeduction();
            }, 100);

            console.log('Form has been reset');
        }
    }

    // Form Validation Functions
    function validateCreateProformaForm() {
        const form = document.getElementById('createProformaForm');
        let isValid = true;

        // Clear previous validation errors
        clearValidationErrors();

        // 1. Company Validation
        const companySelect = document.getElementById('companySelect');
        if (!companySelect.value) {
            showValidationError(companySelect, 'Company is required');
            isValid = false;
        }

        // 2. Invoice Date Validation
        const invoiceDate = form.querySelector('input[name="issue_date"]');
        if (!invoiceDate.value) {
            showValidationError(invoiceDate, 'Invoice date is required');
            isValid = false;
        } else if (!isValidDate(invoiceDate.value)) {
            showValidationError(invoiceDate, 'Invalid date format (YYYY-MM-DD)');
            isValid = false;
        }

        // 3. Due Date Validation
        const dueDate = form.querySelector('input[name="due_date"]');
        if (!dueDate.value) {
            showValidationError(dueDate, 'Due date is required');
            isValid = false;
        } else if (new Date(dueDate.value) < new Date(invoiceDate.value)) {
            showValidationError(dueDate, 'Due date must be on or after invoice date');
            isValid = false;
        }

        // 4. Client Name Validation
        const clientName = form.querySelector('input[name="client_name"]');
        if (!clientName.value.trim()) {
            showValidationError(clientName, 'Client name is required');
            isValid = false;
        } else if (clientName.value.trim().length < 3) {
            showValidationError(clientName, 'Client name must be at least 3 characters');
            isValid = false;
        } else if (clientName.value.trim().length > 100) {
            showValidationError(clientName, 'Client name cannot exceed 100 characters');
            isValid = false;
        }

        // 5. Client Email Validation
        const clientEmail = form.querySelector('input[name="client_email"]');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!clientEmail.value.trim()) {
            showValidationError(clientEmail, 'Client email is required');
            isValid = false;
        } else if (!emailRegex.test(clientEmail.value)) {
            showValidationError(clientEmail, 'Invalid email format');
            isValid = false;
        }

        // 6. Mobile Number Validation (optional but must be valid if provided)
        const mobileNumber = form.querySelector('input[name="mobile_number"]');
        if (mobileNumber.value.trim()) {
            const mobileRegex = /^\d{10}$/;
            if (!mobileRegex.test(mobileNumber.value.trim())) {
                showValidationError(mobileNumber, 'Mobile number must be 10 digits');
                isValid = false;
            }
        }

        // 7. Client GSTIN Validation (optional but must be valid if provided)
        const clientGstin = form.querySelector('input[name="client_gstin"]');
        if (clientGstin.value.trim()) {
            const gstinRegex = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/;
            if (clientGstin.value.trim().length !== 15) {
                showValidationError(clientGstin, 'GSTIN must be 15 characters');
                isValid = false;
            } else if (!gstinRegex.test(clientGstin.value.trim())) {
                showValidationError(clientGstin, 'Invalid GSTIN format');
                isValid = false;
            }
        }

        // 8. Billing Address Validation
        const billingAddress = form.querySelector('textarea[name="billing_address"]');
        if (!billingAddress.value.trim()) {
            showValidationError(billingAddress, 'Billing address is required');
            isValid = false;
        } else if (billingAddress.value.trim().length < 10) {
            showValidationError(billingAddress, 'Billing address must be at least 10 characters');
            isValid = false;
        } else if (billingAddress.value.trim().length > 250) {
            showValidationError(billingAddress, 'Billing address cannot exceed 250 characters');
            isValid = false;
        }

        // 9. Line Items Validation
        const lineItems = document.querySelectorAll('.line-item');
        if (lineItems.length === 0) {
            showGeneralError('At least one line item is required');
            isValid = false;
        } else {
            lineItems.forEach((item, index) => {
                const description = item.querySelector('input[name*="description"]');
                const quantity = item.querySelector('input[name*="quantity"]');
                const rate = item.querySelector('input[name*="rate"]');

                if (!description.value.trim()) {
                    showValidationError(description, 'Description is required');
                    isValid = false;
                }

                if (!quantity.value || parseFloat(quantity.value) <= 0) {
                    showValidationError(quantity, 'Quantity must be greater than 0');
                    isValid = false;
                }

                if (!rate.value || parseFloat(rate.value) < 0) {
                    showValidationError(rate, 'Rate must be 0 or greater');
                    isValid = false;
                }
            });
        }

        // 10. GST Validation if checked
        const applyGst = document.getElementById('applyGST');
        const gstPercentage = document.getElementById('gst_percentage');
        if (applyGst.checked) {
            if (!gstPercentage.value || parseFloat(gstPercentage.value) < 0) {
                showValidationError(gstPercentage, 'GST percentage is required when GST is applied');
                isValid = false;
            } else if (parseFloat(gstPercentage.value) > 100) {
                showValidationError(gstPercentage, 'GST percentage cannot exceed 100');
                isValid = false;
            }
        }

        // 11. TDS Validation if checked
        const applyTds = document.getElementById('applyTDS');
        const tdsPercentage = document.getElementById('tds_percentage');
        if (applyTds.checked) {
            if (!tdsPercentage.value || parseFloat(tdsPercentage.value) < 0) {
                showValidationError(tdsPercentage, 'TDS percentage is required when TDS is applied');
                isValid = false;
            } else if (parseFloat(tdsPercentage.value) > 100) {
                showValidationError(tdsPercentage, 'TDS percentage cannot exceed 100');
                isValid = false;
            }
        }

        // 12. Currency Validation
        const currencySelect = document.getElementById('currencySelect');
        if (!currencySelect.value) {
            showValidationError(currencySelect, 'Currency is required');
            isValid = false;
        }

        // 13. Conversion Rate Validation
        const conversionRate = document.getElementById('conversion_rate_percentage');
        // Conversion Rate Validation - Skip validation for INR
        if (currencySelect.value === 'INR') {
            // Don't validate conversion rate for INR
        } else if (!conversionRate.value || parseFloat(conversionRate.value) <= 0) {
            showValidationError(conversionRate, 'Conversion rate must be greater than 0');
            isValid = false;
        }



        // 14. Frequency Validation
        const frequency = document.getElementById('frequency');
        if (!frequency.value) {
            showValidationError(frequency, 'Frequency is required');
            isValid = false;
        } else if (!['monthly', 'quarterly', 'yearly'].includes(frequency.value)) {
            showValidationError(frequency, 'Invalid frequency option');
            isValid = false;
        }

        // 15. Due Day Validation
        const dueDay = document.getElementById('due_day');
        if (!dueDay.value) {
            showValidationError(dueDay, 'Due day is required');
            isValid = false;
        } else {
            const day = parseInt(dueDay.value);
            if (isNaN(day) || day < 1 || day > 31) {
                showValidationError(dueDay, 'Due day must be between 1 and 31');
                isValid = false;
            }
        }

        // 16. Default Reminder Validation
        const reminderDays = document.getElementById('reminder_days');
        if (!reminderDays.value) {
            showValidationError(reminderDays, 'Default reminder days is required');
            isValid = false;
        } else if (parseInt(reminderDays.value) < 0) {
            showValidationError(reminderDays, 'Default reminder days cannot be negative');
            isValid = false;
        }

        // 17. Active Status Validation
        const status = document.getElementById('status');
        if (!status.value) {
            showValidationError(status, 'Active status is required');
            isValid = false;
        } else if (!['1', '0'].includes(status.value)) {
            showValidationError(status, 'Invalid status option');
            isValid = false;
        }

        return isValid;
    }

    // Helper Functions for Validation
    function showValidationError(element, message) {
        element.classList.add('is-invalid');

        // Remove existing error message
        const existingError = element.parentElement.querySelector('.invalid-feedback');
        if (existingError) {
            existingError.remove();
        }

        // Add error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        element.parentElement.appendChild(errorDiv);

        // Scroll to first error
        if (element.getBoundingClientRect().top < 0) {
            element.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }
    }

    function clearValidationErrors() {
        // Remove is-invalid class
        document.querySelectorAll('.is-invalid').forEach(element => {
            element.classList.remove('is-invalid');
        });

        // Remove error messages
        document.querySelectorAll('.invalid-feedback').forEach(element => {
            element.remove();
        });

        // Remove general error
        const generalError = document.getElementById('generalValidationError');
        if (generalError) {
            generalError.remove();
        }
    }

    function showGeneralError(message) {
        const form = document.getElementById('createProformaForm');

        // Remove existing general error
        const existingError = document.getElementById('generalValidationError');
        if (existingError) {
            existingError.remove();
        }

        // Add general error
        const errorDiv = document.createElement('div');
        errorDiv.id = 'generalValidationError';
        errorDiv.className = 'alert alert-danger alert-dismissible fade show';
        errorDiv.innerHTML = `
                                        <strong>Validation Error!</strong> ${message}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    `;

        form.prepend(errorDiv);
    }

    function isValidDate(dateString) {
        const regex = /^\d{4}-\d{2}-\d{2}$/;
        if (!regex.test(dateString)) return false;

        const date = new Date(dateString);
        const timestamp = date.getTime();

        if (typeof timestamp !== 'number' || Number.isNaN(timestamp)) {
            return false;
        }

        return date.toISOString().slice(0, 10) === dateString;
    }

    // Add Real-time Validation
    function setupRealTimeValidation() {
        const form = document.getElementById('createProformaForm');

        // Company
        const companySelect = document.getElementById('companySelect');
        companySelect.addEventListener('change', function() {
            if (this.value) {
                this.classList.remove('is-invalid');
                const error = this.parentElement.querySelector('.invalid-feedback');
                if (error) error.remove();
            }
        });

        // Client Name
        const clientName = form.querySelector('input[name="client_name"]');
        clientName.addEventListener('input', function() {
            if (this.value.trim().length >= 3 && this.value.trim().length <= 100) {
                this.classList.remove('is-invalid');
                const error = this.parentElement.querySelector('.invalid-feedback');
                if (error) error.remove();
            }
        });

        // Client Email
        const clientEmail = form.querySelector('input[name="client_email"]');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        clientEmail.addEventListener('blur', function() {
            if (this.value.trim() && emailRegex.test(this.value)) {
                this.classList.remove('is-invalid');
                const error = this.parentElement.querySelector('.invalid-feedback');
                if (error) error.remove();
            }
        });

        // Mobile Number
        const mobileNumber = form.querySelector('input[name="mobile_number"]');
        mobileNumber.addEventListener('blur', function() {
            if (!this.value.trim()) return; // Optional field

            const mobileRegex = /^\d{10}$/;
            if (mobileRegex.test(this.value.trim())) {
                this.classList.remove('is-invalid');
                const error = this.parentElement.querySelector('.invalid-feedback');
                if (error) error.remove();
            }
        });

        // Due Date
        const invoiceDate = form.querySelector('input[name="issue_date"]');
        const dueDate = form.querySelector('input[name="due_date"]');

        invoiceDate.addEventListener('change', function() {
            validateDueDate();
        });

        dueDate.addEventListener('change', function() {
            validateDueDate();
        });

        function validateDueDate() {
            if (invoiceDate.value && dueDate.value) {
                if (new Date(dueDate.value) >= new Date(invoiceDate.value)) {
                    dueDate.classList.remove('is-invalid');
                    const error = dueDate.parentElement.querySelector('.invalid-feedback');
                    if (error) error.remove();
                }
            }
        }

        // Line Items
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('quantity')) {
                const value = parseFloat(e.target.value);
                if (value > 0) {
                    e.target.classList.remove('is-invalid');
                    const error = e.target.parentElement.querySelector('.invalid-feedback');
                    if (error) error.remove();
                }
            }

            if (e.target.classList.contains('rate')) {
                const value = parseFloat(e.target.value);
                if (value >= 0) {
                    e.target.classList.remove('is-invalid');
                    const error = e.target.parentElement.querySelector('.invalid-feedback');
                    if (error) error.remove();
                }
            }
        });
    }
    // Initialize everything when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, initializing invoice form...');

        // Initialize variables
        window.currentSubtotal = 0;
        window.finalAmount = 0;
        window.taxCalculationInProgress = false;

        // Initialize currency symbols and display
        const initialCurrency = document.getElementById('currencySelect').value || 'USD';
        updateCurrencySelection();
        updateRateDisplay();

        // Set up currency select change event
        const currencySelect = document.getElementById('currencySelect');
        if (currencySelect) {
            currencySelect.addEventListener('change', function() {
                console.log('Currency changed to:', this.value);
                updateCurrencySelection();
            });
        }

        // Set up conversion rate input change event
        const conversionRateInput = document.getElementById('conversion_rate_percentage');
        if (conversionRateInput) {
            conversionRateInput.addEventListener('input', function() {
                console.log('Conversion rate changed:', this.value);
                updateRateDisplay();
                updateCurrencyConversionFromForeign();
            });
        }

        // Set up conversion cost input change event
        const conversionCostInput = document.getElementById('conversion_cost');
        if (conversionCostInput) {
            conversionCostInput.addEventListener('input', function() {
                console.log('Conversion cost changed:', this.value);
                updateReceivableAmount();
            });
        }

        // Set up amount in foreign input change event
        const amountInForeignInput = document.getElementById('amount_in_foreign');
        if (amountInForeignInput) {
            amountInForeignInput.addEventListener('input', function() {
                console.log('Amount in foreign changed:', this.value);
                updateCurrencyConversionFromForeign();
            });
        }

        // Fetch real-time conversion rates
        fetchConversionRates();

        // Add event listeners to existing line items
        const initialQuantity = document.querySelector('.quantity');
        const initialRateInput = document.querySelector('.rate');

        if (initialQuantity) {
            initialQuantity.addEventListener('input', debouncedCalculateLineAmount);
        }

        if (initialRateInput) {
            initialRateInput.addEventListener('input', debouncedCalculateLineAmount);
        }

        // Initialize calculations
        calculateLineAmount();
        calculateTax();
        updateCurrencyConversionFromForeign();

        // Form submission handling
        const proformaForm = document.getElementById('createProformaForm');
        if (proformaForm) {
            proformaForm.addEventListener('submit', function(e) {
                e.preventDefault();
                // Validate form
                if (!validateCreateProformaForm()) {
                    // Scroll to first error
                    const firstError = document.querySelector('.is-invalid');
                    if (firstError) {
                        firstError.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        firstError.focus();
                    }
                    return;
                }
                const submitBtn = document.getElementById('submitBtn');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
                submitBtn.disabled = true;

                const formData = new FormData(this);

                // Get calculated values
                const subtotal = window.currentSubtotal || 0;
                const finalAmount = window.finalAmount || 0;

                // Get tax data
                const applyGst = document.getElementById('applyGST').checked;
                const applyTds = document.getElementById('applyTDS').checked;
                const gstAmount = parseFloat(document.getElementById('gst_amount').value) || 0;
                const tdsAmount = parseFloat(document.getElementById('tds_amount').value) || 0;
                const totalTax = gstAmount + tdsAmount;
                const convertedAmount = parseFloat(document.getElementById('convertedAmount').value) ||
                    0;

                // Get conversion deduction data
                const conversionRatePercentage = parseFloat(document.getElementById(
                    'conversion_rate_percentage').value) || 0;
                const conversionCost = parseFloat(document.getElementById('conversion_cost').value) ||
                    0;
                const receivableAmount = parseFloat(document.getElementById('receivable_amount_foreign')
                    .value) || 0;
                const amountInINR = parseFloat(document.getElementById('amount_in_inr').value) || 0;

                // Convert line items to JSON
                const lineItems = [];
                document.querySelectorAll('.line-item').forEach((item, index) => {
                    const description = item.querySelector(
                        `input[name="line_items[${index}][description]"]`).value;
                    const quantity = parseFloat(item.querySelector(
                        `input[name="line_items[${index}][quantity]"]`).value) || 0;
                    const rate = parseFloat(item.querySelector(
                        `input[name="line_items[${index}][rate]"]`).value) || 0;
                    const amount = parseFloat(item.querySelector(
                        `input[name="line_items[${index}][amount]"]`).value) || 0;

                    if (description && quantity > 0 && rate > 0) {
                        lineItems.push({
                            description: description,
                            quantity: quantity,
                            rate: rate,
                            amount: amount
                        });
                    }
                });

                // Validate line items
                if (lineItems.length === 0) {
                    alert('Please add at least one line item');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    return;
                }

                // Remove existing line_items and add new JSON
                formData.delete('line_items');
                formData.append('line_items', JSON.stringify(lineItems));


                // Add tax data to form
                formData.set('apply_gst', applyGst ? '1' : '0');
                formData.set('apply_tds', applyTds ? '1' : '0');
                formData.set('gst_amount', gstAmount);
                formData.set('tds_amount', tdsAmount);
                formData.set('subtotal', subtotal);
                formData.set('tax_amount', totalTax);
                formData.set('total_amount', finalAmount);
                formData.set('converted_amount', convertedAmount);

                // Add conversion deduction data to form
                formData.set('conversion_rate',
                    conversionRatePercentage); // conversion_rate is the percentage
                formData.set('conversion_cost', conversionCost);
                formData.set('receivable_amount', receivableAmount);
                formData.set('amount_in_inr', amountInINR);
                formData.set('base_conversion_rate', parseFloat(document.getElementById(
                    'base_conversion_rate').value) || 1);

                // Determine tax type
                let taxType = '';
                if (applyGst && applyTds) {
                    taxType = 'GST+TDS';
                } else if (applyGst) {
                    taxType = 'GST';
                } else if (applyTds) {
                    taxType = 'TDS';
                }
                formData.set('tax_type', taxType);

                console.log('Submitting form data:', {
                    conversionRatePercentage,
                    conversionCost,
                    receivableAmount,
                    amountInINR,
                    convertedAmount,
                    finalAmount,
                    subtotal,
                    gstAmount,
                    tdsAmount
                });

                fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .content
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            resetForm();

                            // Switch to proformas tab
                            const proformasTab = new bootstrap.Tab(document.getElementById(
                                'proformas-tab'));
                            proformasTab.show();
                            location.reload();
                        } else {
                            alert(data.message || 'Error creating proforma');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while creating the proforma');
                    })
                    .finally(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    });
            });
        }


        // Partial Payment Modal
        const partialPaymentModal = document.getElementById('partialPaymentModal');
        if (partialPaymentModal) {
            const receivedAmountInput = document.getElementById('pp_received_amount');
            const balanceAmountInput = document.getElementById('pp_balance_amount');
            const originalAmountInput = document.getElementById('pp_original_amount');
            const confirmBtn = document.getElementById('pp_confirm_btn');
            const newDueDateInput = document.getElementById('pp_new_due_date');
            const settlePaymentDateInput = document.getElementById('pp_settle_payment_date');

            // Set default dates
            const futureDate = new Date();
            futureDate.setDate(futureDate.getDate() + 30);
            newDueDateInput.value = futureDate.toISOString().split('T')[0];
            settlePaymentDateInput.value = new Date().toISOString().split('T')[0];

            // Calculate balance when received amount changes
            receivedAmountInput.addEventListener('input', function() {
                const originalAmount = parseFloat(originalAmountInput.value) || 0;
                const receivedAmount = parseFloat(this.value) || 0;
                const balance = Math.max(0, originalAmount - receivedAmount);

                balanceAmountInput.value = balance.toFixed(2);

                // Validate received amount
                if (receivedAmount <= 0 || receivedAmount > originalAmount) {
                    this.classList.add('is-invalid');
                    confirmBtn.disabled = true;
                } else {
                    this.classList.remove('is-invalid');
                    confirmBtn.disabled = false;

                    // Show/hide settle option based on balance
                    const settleOption = document.getElementById('settleBalanceOption');
                    if (balance > 0) {
                        settleOption.parentElement.style.display = 'block';
                    } else {
                        settleOption.parentElement.style.display = 'none';
                        document.getElementById('keepBalanceOption').checked = true;
                        toggleBalanceOptions('keep');
                    }
                }
            });

            // Modal show event
            partialPaymentModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;

                console.log('All data attributes:', button.dataset);

                const tdsAmount = parseFloat(button.dataset.tdsAmount || 0);
                const tdsPercentage = parseFloat(button.dataset.tdsPercentage || 0);
                const billedAmount = parseFloat(button.dataset.amount || 0);
                const gstAmount = parseFloat(button.dataset.gstAmount || 0);
                const taxPercentage = parseFloat(button.dataset.gstPercentage || 0);

                console.log('TDS Amount:', tdsAmount);
                console.log('TDS Percentage:', tdsPercentage);
                console.log('GST Amount:', gstAmount);
                console.log('Tax Percentage:', taxPercentage);
                console.log('Billed Amount:', billedAmount);

                // Populate invoice details
                document.getElementById('pp_invoice_id').value = button.dataset.invoiceId;
                document.getElementById('pp_invoice_no').value = button.dataset.invoiceNo;
                document.getElementById('pp_company').value = button.dataset.company;
                document.getElementById('pp_client').value = button.dataset.client;
                document.getElementById('pp_original_amount').value = billedAmount;

                // Calculate and display the breakdown
                const grandTotal = billedAmount;

                // Update the billed amount display
                const billedAmountElement = document.getElementById('billed_amount');
                if (billedAmountElement) {
                    // Create a clear breakdown
                    let breakdownText = `Billed: ₹${billedAmount.toFixed(2)}`;

                    if (gstAmount > 0) {
                        breakdownText += ` | GST: ₹${gstAmount.toFixed(2)}`;
                        if (taxPercentage > 0) {
                            breakdownText += ` (${taxPercentage}%)`;
                        }
                    }


                    breakdownText += ` | Grand Total: ₹${grandTotal.toFixed(2)}`;

                    billedAmountElement.textContent = breakdownText;
                }

                // Set TDS amount and grand total
                const tdsAmountField = document.getElementById('pp_tds_amount');
                const grandAmountField = document.getElementById('pp_grand_amount');

                if (tdsAmountField) {
                    tdsAmountField.value = tdsAmount.toFixed(2);
                }

                if (grandAmountField) {
                    grandAmountField.value = grandTotal.toFixed(2);
                }

                // Show/hide TDS section based on TDS amount
                toggleTdsSection(tdsAmount);

                // Default dates
                const futureDate = new Date();
                futureDate.setDate(futureDate.getDate() + 30);
                newDueDateInput.value = futureDate.toISOString().split('T')[0];

                const today = new Date().toISOString().split('T')[0];
                settlePaymentDateInput.value = today;

                // Reset form
                receivedAmountInput.value = '';
                balanceAmountInput.value = '';
                receivedAmountInput.classList.remove('is-invalid');
                confirmBtn.disabled = true;

                document.getElementById('keepBalanceOption').checked = true;
                toggleBalanceOptions('keep');

                document.getElementById('settleBalanceOption').parentElement.style.display = 'none';
            });

            // Function to show/hide TDS section
            function toggleTdsSection(tdsAmount) {
                // Get all TDS related elements
                const tdsAmountField = document.getElementById('pp_tds_amount');
                const tdsStatusField = document.getElementById('pp_tds_status');
                const tdsProofField = document.getElementById('pp_tds_proof');

                // Find the parent container or use labels
                const tdsAmountLabel = document.querySelector('label[for="pp_tds_amount"]');
                const tdsStatusLabel = document.querySelector('label[for="pp_tds_status"]');
                const tdsProofLabel = document.querySelector('label[for="pp_tds_proof"]');

                // Check if TDS amount exists
                const hasTds = tdsAmount > 0;

                // Show/hide all TDS related elements
                const tdsElements = [tdsAmountField, tdsStatusField, tdsProofField, tdsAmountLabel,
                    tdsStatusLabel, tdsProofLabel
                ];

                tdsElements.forEach(element => {
                    if (element) {
                        // Get the parent column div for Bootstrap layout
                        const parentColumn = element.closest('.col-md-3, .col-md-4');

                        if (parentColumn) {
                            parentColumn.style.display = hasTds ? 'block' : 'none';
                        } else {
                            // If no parent column, just hide the element
                            element.style.display = hasTds ? 'block' : 'none';
                        }
                    }
                });

                // If no TDS, make TDS status not required
                if (tdsStatusField) {
                    tdsStatusField.required = hasTds;

                    // If no TDS, set default value
                    if (!hasTds) {
                        tdsStatusField.value = 'not_received';
                    }
                }

                // Update any TDS references in descriptions
                const actionDescription = document.getElementById('actionDescription');
                if (actionDescription && !hasTds) {
                    // Remove TDS references from descriptions
                    const keepDescription = document.getElementById('keepBalanceDescription');
                    const settleDescription = document.getElementById('settleBalanceDescription');

                    if (keepDescription) {
                        keepDescription.innerHTML = keepDescription.innerHTML.replace(
                            /TDS status will be recorded and can be tracked separately\./gi, '');
                    }

                    if (settleDescription) {
                        settleDescription.innerHTML = settleDescription.innerHTML.replace(
                            /TDS status will be recorded for reconciliation\./gi, '');
                    }
                }
            }
            // Confirm partial payment
            confirmBtn.addEventListener('click', function() {
                const form = document.getElementById('partialPaymentForm');
                const actionType = document.querySelector('input[name="balance_action"]:checked').value;
                const receivedAmount = parseFloat(document.getElementById('pp_received_amount')
                    .value) || 0;
                const originalAmount = parseFloat(document.getElementById('pp_original_amount')
                    .value) || 0;
                const tdsStatus = document.getElementById('pp_tds_status').value;
                const today = new Date().toISOString().split('T')[0];

                // Validate received amount
                if (receivedAmount <= 0) {
                    alert('Please enter a valid received amount');
                    return;
                }

                if (receivedAmount > originalAmount) {
                    alert('Received amount cannot exceed original amount');
                    return;
                }

                // Validate TDS status is selected
                if (!tdsStatus) {
                    alert('Please select TDS status');
                    return;
                }

                // Validate required fields based on action type
                if (actionType === 'keep_balance') {
                    const newDueDate = document.getElementById('pp_new_due_date').value;
                    if (!newDueDate) {
                        alert('Please select a new due date for the balance');
                        return;
                    }

                    if (newDueDate <= today) {
                        alert('New due date must be in the future');
                        return;
                    }
                } else if (actionType === 'settle_invoice') {
                    const writeoffReason = document.getElementById('pp_writeoff_reason').value;
                    if (!writeoffReason) {
                        alert('Please select a write-off reason');
                        return;
                    }
                }

                const formData = new FormData(form);

                const submitBtn = this;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                submitBtn.disabled = true;

                fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .content,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            const modal = bootstrap.Modal.getInstance(partialPaymentModal);
                            modal.hide();

                            // Reload page after a short delay
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        } else {
                            alert(data.message || 'Error processing payment');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while processing the payment');
                    })
                    .finally(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    });
            });
        }

        // Filter proformas by company
        document.getElementById('companyFilter')?.addEventListener('change', function() {
            const companyId = this.value;
            const rows = document.querySelectorAll('#proformasTableBody tr');

            rows.forEach(row => {
                const rowCompanyId = row.getAttribute('data-company-id');
                
                if (!companyId || rowCompanyId === companyId) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Filter invoices
        document.getElementById('invoiceCompanyFilter')?.addEventListener('change', filterInvoices);
        document
            .getElementById('invoiceStatusFilter')?.addEventListener('change', filterInvoices);



    });



    // Function to close user modal
    function closeUserModal() {
        const modal = document.getElementById('partialPaymentModal');
        if (modal) {
            const bootstrapModal = bootstrap.Modal.getInstance(modal);
            if (bootstrapModal) {
                bootstrapModal.hide();
            }
        }
    }

    function toggleBalanceOptions(actionType) {
        const keepSection = document.getElementById('keepBalanceSection');
        const settleSection = document.getElementById('settleBalanceSection');
        const keepDescription = document.getElementById('keepBalanceDescription');
        const settleDescription = document.getElementById('settleBalanceDescription');
        const actionTypeField = document.getElementById('pp_action_type');
        const newDueDateInput = document.getElementById('pp_new_due_date');

        if (actionType === 'keep') {
            keepSection.style.display = 'block';
            settleSection.style.display = 'none';
            keepDescription.style.display = 'block';
            settleDescription.style.display = 'none';
            actionTypeField.value = 'keep_balance';

            // Set default new due date if not set
            if (!newDueDateInput.value) {
                const futureDate = new Date();
                futureDate.setDate(futureDate.getDate() + 30);
                newDueDateInput.value = futureDate.toISOString().split('T')[0];
            }
        } else {
            keepSection.style.display = 'none';
            settleSection.style.display = 'block';
            keepDescription.style.display = 'none';
            settleDescription.style.display = 'block';
            actionTypeField.value = 'settle_invoice';
        }
    }

    // View proforma function
    function viewProforma(id) {
        fetch(`https://xhtmlreviews.in/beta-finance/admin/invoices/${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const invoice = data.invoice;
                    const content = document.getElementById('invoiceDetailsContent');

                    console.log("response", data)
                    const currencyType = invoice.currency;

                    // Calculate amounts excluding TDS
                    let gstTotal = 0;
                    let tdsTotal = 0;
                    let gstItems = [];
                    let tdsItems = [];

                    if (invoice.taxes && invoice.taxes.length > 0) {
                        // Separate GST and TDS
                        gstItems = invoice.taxes.filter(tax => tax.tax_type === 'gst');
                        tdsItems = invoice.taxes.filter(tax => tax.tax_type === 'tds');

                        // Calculate totals
                        gstItems.forEach(tax => gstTotal += parseFloat(tax.tax_amount));
                        tdsItems.forEach(tax => tdsTotal += parseFloat(tax.tax_amount));
                    }

                    // Calculate total without TDS
                    const totalWithoutTds = parseFloat(invoice.total_amount);

                    // Format line items HTML
                    let lineItemsHtml = '';
                    invoice.line_items.forEach(item => {
                        lineItemsHtml += `
                                                            <tr>
                                                                <td>${item.description}</td>
                                                                <td class="text-end">${item.quantity}</td>
                                                                <td class="text-end">${currencyType === "USD" ? "$" : "₹"}${parseFloat(item.rate).toFixed(2)}</td>
                                                                <td class="text-end">${currencyType === "USD" ? "$" : "₹"}${parseFloat(item.amount).toFixed(2)}</td>
                                                            </tr>
                                                        `;
                    });

                    // Format GST details HTML (only GST, no TDS)
                    let gstHtml = '';

                    if (gstItems.length > 0) {
                        // Display GST breakdown
                        gstHtml = `
                                                            <h6 class="mt-4">GST Details</h6>
                                                            <table class="table table-sm">
                                                                <thead>
                                                                    <tr>
                                                                        <th>GST Type</th>
                                                                        <th class="text-end">Percentage</th>
                                                                        <th class="text-end">Amount</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                        `;

                        gstItems.forEach(tax => {
                            gstHtml += `
                                                                <tr>
                                                                    <td>${tax.tax_type.toUpperCase()}</td>
                                                                    <td class="text-end">${parseFloat(tax.tax_percentage).toFixed(2)}%</td>
                                                                    <td class="text-end">₹${parseFloat(tax.tax_amount).toFixed(2)}</td>
                                                                </tr>
                                                            `;
                        });

                        gstHtml += `
                                                                </tbody>
                                                                <tfoot>
                                                                    <tr>
                                                                        <th colspan="2" class="text-end">Total GST:</th>
                                                                        <td class="text-end"><strong>₹${gstTotal.toFixed(2)}</strong></td>
                                                                        <td></td>
                                                                    </tr>
                                                                </tfoot>
                                                            </table>
                                                        `;
                    }

                    const html = `
                                                        <div class="container-fluid">
                                                            <div class="row mb-4">
                                                                <div class="col-md-8">
                                                                    <h5>Invoice Details</h5>
                                                                    <table class="table table-sm">
                                                                        <tr>
                                                                            <th width="150">Invoice Number:</th>
                                                                            <td>${invoice.invoice_number}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Company:</th>
                                                                            <td>${invoice.company.name}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Client:</th>
                                                                            <td>${invoice.client_details.name}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Email:</th>
                                                                            <td>${invoice.client_details.email}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>GSTIN:</th>
                                                                            <td>${invoice.client_details.gstin || 'N/A'}</td>
                                                                        </tr>

                                                                        <tr>
                                                                            <th>Issue Date:</th>
                                                                            <td>${new Date(invoice.issue_date).toLocaleDateString()}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Due Date:</th>
                                                                            <td>${new Date(invoice.due_date).toLocaleDateString()}</td>
                                                                        </tr>
                                                                    </table>
                                                                </div>
                                                                <div class="col-md-4 text-end">
                                                                    <div class="alert ${invoice.status === 'pending' ? 'alert-warning' : invoice.status === 'paid' ? 'alert-success' : 'alert-secondary'}">
                                                                        <strong>Status:</strong> ${invoice.status.toUpperCase()}<br>
                                                                        <strong>Type:</strong> ${invoice.type.toUpperCase()}
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <h6>Line Items</h6>
                                                            <table class="table table-sm">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Description</th>
                                                                        <th class="text-end">Qty</th>
                                                                        <th class="text-end">Rate</th>
                                                                        <th class="text-end">Amount</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    ${lineItemsHtml}
                                                                </tbody>
     <tfoot>
        <tr>
            <th colspan="3" class="text-end">Subtotal:</th>
            <td class="text-end">
                ${currencyType === "USD" ? "$" : "₹"}${parseFloat(invoice.subtotal).toFixed(2)}
            </td>
        </tr>

        ${currencyType === "INR" && gstItems.length > 0
                                ? `
                <tr>
                    <th colspan="3" class="text-end">GST (+${gstItems[0].tax_percentage}%):</th>
                    <td class="text-end">₹${gstTotal.toFixed(2)}</td>
                </tr>
            `
                                : ''
                            }
    ${currencyType === "INR" && tdsItems.length > 0
                                ?
                                `<tr>
            <th colspan="3" class="text-end">TDS(-${tdsItems[0].tax_percentage}%):</th>
            <td class="text-end">
                <strong>₹${(parseFloat(tdsTotal)).toFixed(2)}</strong>
            </td>
        </tr>
    `
                                : ''
                            }
        <tr class="border-top">
            <th colspan="3" class="text-end">Net Amount Payable:</th>
            <td class="text-end">
                <strong>
                    ₹
                    ${(parseFloat(invoice.received_amount)).toFixed(2)}
                </strong>
            </td>
        </tr>
    </tfoot>
                                                            </table>

                                                            ${gstHtml}

                                                            ${invoice.purpose_comment ? `
                                                                                                                                                                                                                                                                                                                                        <div class="mt-3">
                                                                                                                                                                                                                                                                                                                                            <h6>Purpose Comment</h6>
                                                                                                                                                                                                                                                                                                                                            <p class="text-muted">${invoice.purpose_comment}</p>
                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                    ` : ''}

                                                            ${invoice.terms_conditions ? `
                                                                                                                                                                                                                                                                                                                                        <div class="mt-3">
                                                                                                                                                                                                                                                                                                                                            <h6>Terms & Conditions</h6>
                                                                                                                                                                                                                                                                                                                                            <div class="text-muted" style="white-space: pre-line;">${invoice.terms_conditions}</div>
                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                    ` : ''}
                                                        </div>
                                                    `;

                    content.innerHTML = html;
                    const modal = new bootstrap.Modal(document.getElementById('viewInvoiceModal'));
                    document.getElementById('viewInvoiceModal').dataset.invoiceId = id;
                    modal.show();
                }
            });
    }

    // Download functions
    function downloadProforma(id) {
        window.open(`https://xhtmlreviews.in/beta-finance/admin/invoices/${id}/download?type=proforma`, '_blank');
    }

    function downloadInvoice(id) {
        window.open(`https://xhtmlreviews.in/beta-finance/admin/invoices/${id}/download?type=invoice`, '_blank');
    }

    // Print invoice
    // function printInvoice(id) {
    //     window.open(`https://xhtmlreviews.in/beta-finance/admin/invoices/${id}/download?type=invoice`, '_blank');
    //     // const modalContent = document.getElementById('invoiceDetailsContent').innerHTML;
    //     // const printWindow = window.open('', '_blank');
    //     // printWindow.document.write(`
    //     //                                 <html>
    //     //                                     <head>
    //     //                                         <title>Invoice Print</title>
    //     //                                         <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    //     //                                         <style>
    //     //                                             @media print {
    //     //                                                 @page { margin: 20px; }
    //     //                                                 body { margin: 0; }
    //     //                                             }
    //     //                                         </style>
    //     //                                     </head>
    //     //                                     <body>
    //     //                                         ${modalContent}
    //     //                                         <script>
    //     //                                             window.onload = function() { window.print(); window.close(); }
    //     //                                         <\/script>
    //     //                                     </body>
    //     //                                 </html>
    //     //                             `);
    //     // printWindow.document.close();
    // }
    
    function printInvoice() {
        const modal = document.getElementById('viewInvoiceModal');

        // Option 1: from dataset
        const invoiceId = modal.dataset.invoiceId;

        // Option 2: from global
        // const invoiceId = window.currentInvoiceId;

        if (!invoiceId) {
            alert('Invoice ID not found');
            return;
        }
        window.open(`https://xhtmlreviews.in/beta-finance/admin/invoices/${invoiceId}/download?type=invoice`, '_blank');
    }
    // Filter invoices
    function filterInvoices() {
        const companyId = document.getElementById('invoiceCompanyFilter').value;
        const status = document.getElementById('invoiceStatusFilter').value;
        const rows = document.querySelectorAll('#invoicesTableBody tr');

        rows.forEach(row => {
            const companyName = row.cells[1].textContent;
            const statusText = row.cells[6].textContent.toLowerCase();
            const companyOption = Array.from(document.querySelectorAll('#invoiceCompanyFilter option')).find(
                opt => opt.textContent === companyName);

            let show = true;

            if (companyId && (!companyOption || companyOption.value !== companyId)) {
                show = false;
            }

            if (status) {
                if (status === 'paid' && !statusText.includes('paid')) show = false;
                if (status === 'partial' && !statusText.includes('partial')) show = false;
                if (status === 'overdue' && !statusText.includes('overdue')) show = false;
            }

            row.style.display = show ? '' : 'none';
        });
    }

    // Reset filters
    function resetFilters() {
        document.getElementById('companyFilter').value = '';
        document.querySelectorAll('#proformasTableBody tr').forEach(row => {
            row.style.display = '';
        });
    }

    // Function to close send modal
    function closeSendModal() {
        const modal = document.getElementById('sendInvoiceModal');
        if (modal) {
            const bootstrapModal = bootstrap.Modal.getInstance(modal);
            if (bootstrapModal) {
                bootstrapModal.hide();
            }
        }
    }

    // Send Invoice functionality
    document.addEventListener('DOMContentLoaded', function() {
        const sendInvoiceModal = document.getElementById('sendInvoiceModal');

        if (sendInvoiceModal) {
            const sendConfirmBtn = document.getElementById('send_confirm_btn');
            const subjectInput = document.getElementById('send_subject');
            const messageInput = document.getElementById('send_message');
            const toEmailInput = document.getElementById('send_to_email');

            // Update preview when inputs change
            subjectInput.addEventListener('input', updatePreview);
            messageInput.addEventListener('input', updatePreview);
            toEmailInput.addEventListener('input', updatePreview);

            function updatePreview() {
                document.getElementById('preview_email').textContent = toEmailInput.value;
                document.getElementById('preview_subject').textContent = subjectInput.value;
                document.getElementById('preview_message').textContent = messageInput.value;
            }

            // Modal show event
            sendInvoiceModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;

                const invoiceId = button.dataset.invoiceId;
                const invoiceNo = button.dataset.invoiceNo;
                const invoiceType = button.dataset.type;
                const clientEmail = button.dataset.clientEmail;
                const clientName = button.dataset.client;
                const amount = button.dataset.amount;
                const dueDate = button.dataset.dueDate;
                const company = button.dataset.company;

                // Format due date
                const formattedDueDate = new Date(dueDate).toLocaleDateString('en-IN', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric'
                });

                // Set form values
                document.getElementById('send_invoice_id').value = invoiceId;
                document.getElementById('send_invoice_type').value = invoiceType;
                document.getElementById('send_invoice_no').value = invoiceNo;
                document.getElementById('send_invoice_type_display').value = invoiceType ===
                    'proforma' ? 'Proforma Invoice' : 'Tax Invoice';
                document.getElementById('send_to_email').value = clientEmail;

                // Set default subject
                const defaultSubject = invoiceType === 'proforma' ?
                    `Proforma Invoice ${invoiceNo} from ${company}` :
                    `Invoice ${invoiceNo} from ${company}`;
                document.getElementById('send_subject').value = defaultSubject;

                // Set default message
                const defaultMessage = `Dear ${clientName},

                                        ${invoiceType === 'proforma' ? 'Please find attached the proforma invoice' : 'Please find attached your invoice'} for ₹${parseFloat(amount).toFixed(2)}.

                                        Invoice Details:
                                        - Invoice Number: ${invoiceNo}
                                        - Amount: ₹${parseFloat(amount).toFixed(2)}
                                        ${dueDate ? `- Due Date: ${formattedDueDate}` : ''}

                                        Please let us know if you have any questions.

                                        Best regards,
                                        ${company}`;

                document.getElementById('send_message').value = defaultMessage;

                // Update preview
                updatePreview();
            });

            // Confirm send invoice
            sendConfirmBtn.addEventListener('click', function() {
                const form = document.getElementById('sendInvoiceForm');
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
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message || 'Invoice sent successfully!');
                            const modal = bootstrap.Modal.getInstance(sendInvoiceModal);
                            modal.hide();
                        } else {
                            alert(data.message || 'Error sending invoice');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while sending the invoice');
                    })
                    .finally(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    });
            });
        }
    });

    // Also add event listeners for send invoice buttons
    document.querySelectorAll('.btn-send-invoice').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            // Data will be handled by Bootstrap modal
        });
    });

    function resetForm() {
        // Get the form element
        const createProformaForm = document.getElementById('createProformaForm');

        // Reset the form
        if (createProformaForm) {
            createProformaForm.reset();
            console.log('Form has been reset');
        }

        // If you need to reset additional elements not part of form.reset()
        // You can manually reset specific fields:

        // Reset textareas
        const notesField = document.getElementById('notes');
        if (notesField) {
            notesField.value = '';
        }

        // Reset select elements to first option
        const selectElements = createProformaForm.querySelectorAll('select');
        selectElements.forEach(select => {
            if (select.options.length > 0) {
                select.selectedIndex = 0;
            }
        });

        // Reset any custom fields or calculations
        const totalAmountField = document.getElementById('total_amount');
        if (totalAmountField) {
            totalAmountField.value = '';
        }

        // Clear any validation errors
        const errorElements = document.querySelectorAll('.is-invalid');
        errorElements.forEach(element => {
            element.classList.remove('is-invalid');
        });
    }
</script>

<style>
    .stat-card {
        border-left: 4px solid;
        transition: transform 0.2s;
        height: 100px;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-card:nth-child(1) {
        border-color: #ffc107;
    }

    .stat-card:nth-child(2) {
        border-color: #dc3545;
    }

    .stat-card:nth-child(3) {
        border-color: #28a745;
    }

    .stat-card:nth-child(4) {
        border-color: #007bff;
    }

    .stat-card .card-title {
        font-size: 1.5rem;
        font-weight: bold;
    }

    .nav-tabs .nav-link {
        border: none;
        border-bottom: 2px solid transparent;
        color: #6c757d;
        font-weight: 500;
    }

    .nav-tabs .nav-link.active {
        border-bottom: 2px solid #007bff;
        color: #007bff;
        background: none;
    }

    .line-item {
        transition: all 0.3s ease;
    }

    .line-item:hover {
        background-color: #f8f9fa;
    }

    .badge {
        font-size: 0.75em;
        font-weight: 500;
    }

    .table th {
        font-weight: 600;
        color: #495057;
        font-size: 0.85rem;
    }

    .table td {
        font-size: 0.85rem;
        vertical-align: middle;
    }

    .btn-group-sm>.btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }

    .form-check-input:checked {
        background-color: #007bff;
        border-color: #007bff;
    }

    #keepBalanceSection,
    #settleBalanceSection {
        padding-left: 1.5rem;
        margin-top: 0.5rem;
        border-left: 2px solid #e9ecef;
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

    /* Validation Styles */
    .is-invalid {
        border-color: #dc3545 !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }

    .is-invalid:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
    }

    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875em;
        color: #dc3545;
    }

    /* Alert styling */
    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
        border-radius: 8px;
        margin-bottom: 1rem;
    }

    .alert-danger .btn-close {
        filter: brightness(0) saturate(100%) invert(15%) sepia(75%) saturate(4000%) hue-rotate(350deg) brightness(80%) contrast(100%);
    }
</style>
<script>
    function openEditInvoiceModal(invoiceId) {
        fetch(`{{ route ( 'admin.invoices.edit', '__ID__' ) }}`.replace('__ID__', invoiceId))
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const invoice = data.invoice;
                    const clientDetails = JSON.parse(invoice.client_details);
                    const lineItems = JSON.parse(invoice.line_items);

                    // Extract tax information from taxes array
                    const taxes = invoice.taxes || [];
                    const gstTax = taxes.find(tax => tax.tax_type === 'gst');
                    const tdsTax = taxes.find(tax => tax.tax_type === 'tds');
                    console.log(tdsTax)
                    // Calculate GST and TDS amounts from taxes array
                    const gstAmount = gstTax ? parseFloat(gstTax.tax_amount) : 0;
                    const tdsAmount = tdsTax ? parseFloat(tdsTax.tax_amount) : 0;
                    const gstPercentage = gstTax ? parseFloat(gstTax.tax_percentage) : 0;
                    const tdsPercentage = tdsTax ? parseFloat(tdsTax.tax_percentage) : 0;

                    // Determine if GST/TDS are applied based on tax amounts
                    const applyGst = gstAmount > 0;
                    const applyTds = tdsAmount > 0;

                    // Populate basic fields
                    document.getElementById('editInvoiceId').value = invoice.id;
                    document.getElementById('editInvoiceNumber').value = invoice.invoice_number;
                    document.getElementById('editInvoiceNumberHidden').value = invoice.invoice_number;
                    document.getElementById('editCompanySelect').value = invoice.company_id;
                    document.getElementById('editIssueDate').value = formatDateForInput(invoice.issue_date);
                    document.getElementById('editDueDate').value = formatDateForInput(invoice.due_date);

                    document.getElementById('editClientName').value = clientDetails.name || '';
                    document.getElementById('editClientEmail').value = clientDetails.email || '';
                    document.getElementById('editMobileNumber').value = clientDetails.mobile_number || '';
                    document.getElementById('editClientGstin').value = clientDetails.gstin || '';
                    document.getElementById('editBillingAddress').value = clientDetails.billing_address || '';
                    document.getElementById('editPurposeComment').value = invoice.purpose_comment || '';
                    document.getElementById('editTermsConditions').value = invoice.terms_conditions || '';
                    document.getElementById('editFrequency').value = invoice.frequency || 'monthly';
                    document.getElementById('editDueDay').value = invoice.due_day || 5;
                    document.getElementById('editReminderDays').value = invoice.reminder_days || 3;
                    document.getElementById('editStatus').value = invoice.is_active ? 'active' : 'inactive';

                    // Currency fields
                    const invoiceCurrency = invoice.currency || 'USD';
                    document.getElementById('editCurrencySelect').value = invoiceCurrency;

                    let baseRate = invoice.base_conversion_rate;
                    // If the stored rate is missing or the default 83.0, use the latest fetched rate if available
                    if ((!baseRate || baseRate == 1) && conversionRates[invoiceCurrency]) {
                        baseRate = conversionRates[invoiceCurrency];
                    }

                    document.getElementById('edit_base_conversion_rate').value = baseRate || 1;
                    document.getElementById('editConversionRatePercentage').value = invoice.conversion_rate || 0;
                    document.getElementById('editTotalAmount').value = invoice.original_currency_amount || invoice
                        .total_amount;
                    document.getElementById('editConvertedAmount').value = invoice.converted_amount || invoice
                        .total_amount;

                    // Tax fields - populate from taxes array
                    document.getElementById('editApplyGST').checked = applyGst;
                    document.getElementById('editGstPercentage').value = gstPercentage || 18;
                    document.getElementById('editGstAmount').value = gstAmount || 0;
                    document.getElementById('editGstAmountDisplay').value = gstAmount || 0;

                    document.getElementById('editApplyTDS').checked = applyTds;
                    document.getElementById('editTdsPercentage').value = tdsPercentage || 10;
                    document.getElementById('editTdsAmount').value = tdsAmount || 0;
                    document.getElementById('editTdsAmountDisplay').value = tdsAmount || 0;

                    // Populate line items
                    populateEditLineItems(lineItems);

                    // Update form action
                    document.getElementById('editInvoiceForm').action = `/admin/invoices/${invoiceId}`;

                    // Calculate tax based on the populated values
                    setTimeout(() => {
                        const currency = document.getElementById('editCurrencySelect').value;
                        updateEditCurrencySymbols(currency);
                        updateEditCurrencySelection();
                        calculateEditTax();
                    }, 100);

                    // Show modal
                    const modal = new bootstrap.Modal(document.getElementById('editInvoiceModal'));
                    modal.show();
                }
            })
            .catch(error => {
                console.error('Error loading invoice data:', error);
                alert('Failed to load invoice data');
            });
    }

    // Function to populate line items in edit form
    function populateEditLineItems(lineItems) {
        const container = document.getElementById('editLineItemsContainer');
        container.innerHTML = '';

        lineItems.forEach((item, index) => {
            const lineItemHTML = `
                                                                                                                                                                <div class="line-item row g-2 align-items-end mb-2" data-index="${index}">
                                                                                                                                                                    <div class="col-md-4">
                                                                                                                                                                        <input type="text" class="form-control form-control-sm" 
                                                                                                                                                                               name="line_items[${index}][description]" 
                                                                                                                                                                               value="${item.description || ''}" 
                                                                                                                                                                               placeholder="Description" required>
                                                                                                                                                                    </div>
                                                                                                                                                                    <div class="col-md-2">
                                                                                                                                                                        <input type="number" class="form-control form-control-sm quantity" 
                                                                                                                                                                               name="line_items[${index}][quantity]" 
                                                                                                                                                                               value="${item.quantity || 1}" 
                                                                                                                                                                               placeholder="Qty" min="1" step="0.01" required
                                                                                                                                                                               oninput="debouncedCalculateEditLineAmount()">
                                                                                                                                                                    </div>
                                                                                                                                                                    <div class="col-md-2">
                                                                                                                                                                        <input type="number" class="form-control form-control-sm rate" 
                                                                                                                                                                               name="line_items[${index}][rate]" 
                                                                                                                                                                               value="${item.rate || 0}" 
                                                                                                                                                                               placeholder="Rate" step="0.01" min="0" required
                                                                                                                                                                               oninput="debouncedCalculateEditLineAmount()">
                                                                                                                                                                    </div>
                                                                                                                                                                    <div class="col-md-2">
                                                                                                                                                                        <input type="number" class="form-control form-control-sm amount" 
                                                                                                                                                                               name="line_items[${index}][amount]" 
                                                                                                                                                                               value="${item.amount || 0}" 
                                                                                                                                                                               placeholder="Amount" step="0.01" readonly>
                                                                                                                                                                    </div>
                                                                                                                                                                    <div class="col-md-1">
                                                                                                                                                                        <button type="button" class="btn btn-sm btn-danger" 
                                                                                                                                                                                onclick="removeEditLineItem(this)"
                                                                                                                                                                                ${lineItems.length <= 1 ? 'disabled' : ''}>
                                                                                                                                                                            <i class="fas fa-trash"></i>
                                                                                                                                                                        </button>
                                                                                                                                                                    </div>
                                                                                                                                                                </div>
                                                                                                                                                            `;
            container.innerHTML += lineItemHTML;
        });
    }

    // Function to add new line item in edit form
    function addEditLineItem() {
        const container = document.getElementById('editLineItemsContainer');
        const items = container.querySelectorAll('.line-item');
        const newIndex = items.length;

        const lineItemHTML = `
                                                                                                                                                            <div class="line-item row g-2 align-items-end mb-2" data-index="${newIndex}">
                                                                                                                                                                <div class="col-md-4">
                                                                                                                                                                    <input type="text" class="form-control form-control-sm" 
                                                                                                                                                                           name="line_items[${newIndex}][description]" 
                                                                                                                                                                           placeholder="Description" required>
                                                                                                                                                                </div>
                                                                                                                                                                <div class="col-md-2">
                                                                                                                                                                    <input type="number" class="form-control form-control-sm quantity" 
                                                                                                                                                                           name="line_items[${newIndex}][quantity]" 
                                                                                                                                                                           value="1" placeholder="Qty" min="1" step="0.01" required
                                                                                                                                                                           oninput="debouncedCalculateEditLineAmount()">
                                                                                                                                                                </div>
                                                                                                                                                                <div class="col-md-2">
                                                                                                                                                                    <input type="number" class="form-control form-control-sm rate" 
                                                                                                                                                                           name="line_items[${newIndex}][rate]" 
                                                                                                                                                                           placeholder="Rate" step="0.01" min="0" required
                                                                                                                                                                           oninput="debouncedCalculateEditLineAmount()">
                                                                                                                                                                </div>
                                                                                                                                                                <div class="col-md-2">
                                                                                                                                                                    <input type="number" class="form-control form-control-sm amount" 
                                                                                                                                                                           name="line_items[${newIndex}][amount]" 
                                                                                                                                                                           placeholder="Amount" step="0.01" readonly>
                                                                                                                                                                </div>
                                                                                                                                                                <div class="col-md-1">
                                                                                                                                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                                                                                                                                            onclick="removeEditLineItem(this)">
                                                                                                                                                                        <i class="fas fa-trash"></i>
                                                                                                                                                                    </button>
                                                                                                                                                                </div>
                                                                                                                                                            </div>
                                                                                                                                                        `;
        container.innerHTML += lineItemHTML;

        // Enable delete buttons for all items if more than 1
        if (items.length + 1 > 1) {
            container.querySelectorAll('.btn-danger').forEach(btn => {
                btn.disabled = false;
            });
        }
    }

    // Function to remove line item in edit form
    function removeEditLineItem(button) {
        const lineItem = button.closest('.line-item');
        lineItem.remove();

        // Re-index remaining items
        const container = document.getElementById('editLineItemsContainer');
        const items = container.querySelectorAll('.line-item');

        items.forEach((item, index) => {
            item.setAttribute('data-index', index);
            const inputs = item.querySelectorAll('input');
            inputs[0].name = `line_items[${index}][description]`;
            inputs[1].name = `line_items[${index}][quantity]`;
            inputs[2].name = `line_items[${index}][rate]`;
            inputs[3].name = `line_items[${index}][amount]`;
        });

        // Disable delete button if only 1 item remains
        if (items.length <= 1) {
            container.querySelector('.btn-danger').disabled = true;
        }

        calculateEditTax();
    }

    // Tax calculation for edit form
    function calculateEditTax() {
        // Calculate subtotal from line items
        let subtotal = 0;
        const container = document.getElementById('editLineItemsContainer');
        const lineItems = container.querySelectorAll('.line-item');

        lineItems.forEach(item => {
            const quantity = parseFloat(item.querySelector('.quantity').value) || 0;
            const rate = parseFloat(item.querySelector('.rate').value) || 0;
            const amount = quantity * rate;

            // Update amount field
            item.querySelector('.amount').value = amount.toFixed(2);
            subtotal += amount;
        });

        // Update subtotal
        document.getElementById('editSubtotal').value = subtotal.toFixed(2);
        document.getElementById('editSubtotalOriginalCurrency').value = subtotal.toFixed(2);

        // Calculate GST
        const applyGst = document.getElementById('editApplyGST').checked;
        const gstPercent = parseFloat(document.getElementById('editGstPercentage').value) || 0;
        let gstAmount = 0;

        if (applyGst && gstPercent > 0) {
            gstAmount = (subtotal * gstPercent) / 100;
            document.getElementById('editGstAmount').value = gstAmount.toFixed(2);
            document.getElementById('editGstAmountDisplay').value = gstAmount.toFixed(2);
        } else {
            document.getElementById('editGstAmount').value = '0.00';
            document.getElementById('editGstAmountDisplay').value = '0.00';
        }

        const amountAfterGst = subtotal + gstAmount;
        document.getElementById('editAmountAfterGst').value = amountAfterGst.toFixed(2);

        // Calculate TDS
        const applyTds = document.getElementById('editApplyTDS').checked;
        const tdsPercent = parseFloat(document.getElementById('editTdsPercentage').value) || 0;
        let tdsAmount = 0;

        if (applyTds && tdsPercent > 0) {
            tdsAmount = (subtotal * tdsPercent) / 100;
            document.getElementById('editTdsAmount').value = tdsAmount.toFixed(2);
            document.getElementById('editTdsAmountDisplay').value = tdsAmount.toFixed(2);
        } else {
            document.getElementById('editTdsAmount').value = '0.00';
            document.getElementById('editTdsAmountDisplay').value = '0.00';
        }

        document.getElementById('editAmountAfterTds').value = (subtotal - tdsAmount).toFixed(2);

        // Update total and foreign amount
        const totalAmount = subtotal + gstAmount - tdsAmount;
        document.getElementById('editTotalAmount').value = totalAmount.toFixed(2);
        document.getElementById('editAmountInForeign').value = amountAfterGst.toFixed(2);

        // UPDATE: For USD currency, convert GST/TDS to INR before storing
        const currency = document.getElementById('editCurrencySelect').value;
        const baseConversionRate = parseFloat(document.getElementById('edit_base_conversion_rate').value) || 83.0;

        let gstAmountToStore = gstAmount;
        let tdsAmountToStore = tdsAmount;

        if (currency !== 'INR') {
            gstAmountToStore = gstAmount * baseConversionRate;
            tdsAmountToStore = tdsAmount * baseConversionRate;
        }

        document.getElementById('editGstAmount').value = gstAmountToStore.toFixed(2);
        document.getElementById('editTdsAmount').value = tdsAmountToStore.toFixed(2);

        // Trigger currency conversion to update INR amount and conversion costs
        updateEditCurrencyConversion();
    }

    // Currency functions for edit form
    function updateEditCurrencySelection(el) {
        const currencySelect = document.getElementById('editCurrencySelect');
        const headerSelect = document.getElementById('editHeaderCurrency');

        // Sync logic
        if (el) {
            if (el.id === 'editHeaderCurrency') {
                currencySelect.value = el.value;
            } else if (el.id === 'editCurrencySelect') {
                headerSelect.value = el.value;
            }
        } else {
            // If called without element (e.g. on modal open), sync header from the main select
            if (currencySelect && headerSelect) {
                headerSelect.value = currencySelect.value;
            }
        }

        const currency = currencySelect ? currencySelect.value : 'USD';
        const gstSection = document.getElementById('edit_gst_section');
        const tdsSection = document.getElementById('edit_tds_section');
        const taxHeading = document.getElementById('edit_tax_heading');
        const editCurrencySection = document.getElementById('edit_currency_section');
        const applyGST = document.getElementById('editApplyGST');
        const applyTDS = document.getElementById('editApplyTDS');

        if (currency === 'USD') {
            if (gstSection) gstSection.classList.add('d-none');
            if (tdsSection) tdsSection.classList.add('d-none');
            if (taxHeading) taxHeading.classList.add('d-none');
            if (editCurrencySection) {
                editCurrencySection.classList.remove('d-none');
                // Requirement: show conversion fields for USD
                editCurrencySection.querySelectorAll('.hide-if-inr').forEach(el => el.classList.remove('d-none'));
                const receivableCol = document.getElementById('editReceivableAmountForeign')?.closest('.col-md-3, .col-md-4');
                if (receivableCol) {
                    receivableCol.className = 'col-md-3';
                }
            }
            if (el) {
                if (applyGST) applyGST.checked = false;
                if (applyTDS) applyTDS.checked = false;
            }
        } else {
            if (gstSection) gstSection.classList.remove('d-none');
            if (tdsSection) tdsSection.classList.remove('d-none');
            if (taxHeading) taxHeading.classList.remove('d-none');
            if (editCurrencySection) {
                if (currency === 'INR') {
                    // Requirement: show only receivable amount for INR
                    editCurrencySection.querySelectorAll('.hide-if-inr').forEach(el => el.classList.add('d-none'));
                    const receivableCol = document.getElementById('editReceivableAmountForeign')?.closest('.col-md-3, .col-md-4');
                    if (receivableCol) {
                        receivableCol.className = 'col-md-4';
                    }
                } else {
                    editCurrencySection.querySelectorAll('.hide-if-inr').forEach(el => el.classList.remove('d-none'));
                    const receivableCol = document.getElementById('editReceivableAmountForeign')?.closest('.col-md-3, .col-md-4');
                    if (receivableCol) {
                        receivableCol.className = 'col-md-3';
                    }
                }
            }
            if (el && applyGST) applyGST.checked = true;
        }

        updateEditCurrencySymbols(currency);
        updateEditConversionRateForCurrency(currency);
        if (typeof calculateEditTax === 'function') {
            calculateEditTax();
        }
    }

    function syncEditCurrency(val) {
        updateEditCurrencySelection();
    }

    function updateEditConversionRateForCurrency(currency) {
        if (currency === 'INR') {
            document.getElementById('edit_base_conversion_rate').value = '1.00';
        } else if (conversionRates[currency]) {
            document.getElementById('edit_base_conversion_rate').value = conversionRates[currency].toFixed(2);
        } else {
            // Fallback rates
            const fallbacks = {
                'USD': 92.89,
                'EUR': 100.0,
                'GBP': 118.0
            };
            document.getElementById('edit_base_conversion_rate').value = fallbacks[currency] || 1.0;
        }
        updateEditCurrencyConversion();
    }

    function updateEditCurrencySymbols(currency) {
        const editHeaderAmtLabel = document.getElementById('editHeaderAmtLabel');
        if (editHeaderAmtLabel) {
            editHeaderAmtLabel.textContent = `Total Amt (${currency === 'INR' ? '₹' : '$'})`;
        }

        const symbols = {
            'INR': '₹',
            'USD': '$',
            'EUR': '€',
            'GBP': '£'
        };

        const symbol = symbols[currency] || '$';

        const currencySymbol = document.getElementById('editCurrencySymbol');
        // const receivableCurrencySymbol = document.getElementById('editReceivableCurrencySymbol');

        if (currencySymbol) currencySymbol.textContent = symbol;
        // if (receivableCurrencySymbol) receivableCurrencySymbol.textContent = '₹';

        const editForeignAmountHelp = document.getElementById('editForeignAmountHelp');
        if (editForeignAmountHelp) {
            editForeignAmountHelp.textContent = `Enter amount in ${currency}`;
        }

        // Update currency indicators in conversion rate fields
        document.querySelectorAll('.edit-selected-currency').forEach(el => el.textContent = currency);

        // Update tax section symbols
        document.querySelectorAll('.edit-tax-symbol').forEach(el => el.textContent = symbol);

        const rateLabelDisplay = document.getElementById('editRateLabelDisplay');
        if (rateLabelDisplay) {
            rateLabelDisplay.textContent = `${currency} to INR`;
        }
    }

    function updateEditCurrencyConversion() {
        const amountInForeign = parseFloat(document.getElementById('editAmountInForeign').value) || 0;
        const baseConversionRate = parseFloat(document.getElementById('edit_base_conversion_rate').value) || 83.0;
        const currency = document.getElementById('editCurrencySelect').value;

        let amountInInr = 0;
        if (currency === 'INR') {
            amountInInr = amountInForeign;
        } else {
            amountInInr = amountInForeign * baseConversionRate;
        }

        document.getElementById('editAmountInInr').value = amountInInr.toFixed(2);
        document.getElementById('editConvertedAmount').value = amountInInr.toFixed(2);
        const conversionDisplay = document.getElementById('editConversionDisplay');
        if (conversionDisplay) {
            conversionDisplay.textContent = `${amountInForeign.toFixed(2)} ${currency} = ₹${amountInInr.toFixed(2)}`;
        }

        updateEditConversionDeduction();
    }

    function updateEditCurrencyConversionFromForeign() {
        updateEditCurrencyConversion();
    }

    // Debounce helper
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Debounced version for better performance
    const debouncedCalculateEditLineAmount = debounce(calculateEditTax, 300);

    // ============ UPDATE FORM SUBMISSION HANDLER ============
    document.addEventListener('DOMContentLoaded', function() {
        const editInvoiceForm = document.getElementById('editInvoiceForm');

        if (editInvoiceForm) {
            editInvoiceForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const submitBtn = document.getElementById('editInvoiceBtn');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
                submitBtn.disabled = true;

                // Debug: Check which elements are null
                const requiredElements = {
                    'editInvoiceId': document.getElementById('editInvoiceId'),
                    'editSubtotal': document.getElementById('editSubtotal'),
                    'editGstAmount': document.getElementById('editGstAmount'),
                    'editTdsAmount': document.getElementById('editTdsAmount'),
                    'editTotalAmount': document.getElementById('editTotalAmount'),
                    'editConvertedAmount': document.getElementById('editConvertedAmount'),
                    'editApplyGST': document.getElementById('editApplyGST'),
                    'editApplyTDS': document.getElementById('editApplyTDS'),
                    'editGstPercentage': document.getElementById('editGstPercentage'),
                    'editTdsPercentage': document.getElementById('editTdsPercentage'),
                    'editCurrencySelect': document.getElementById('editCurrencySelect'),
                    'editConversionRate': document.getElementById('edit_base_conversion_rate'),
                    'editConversionRatePercentage': document.getElementById(
                        'editConversionRatePercentage'),
                    'editLineItemsContainer': document.getElementById('editLineItemsContainer')
                };

                // Check for null elements
                for (const [id, element] of Object.entries(requiredElements)) {
                    if (!element) {
                        console.error(`Element with id '${id}' is null!`);
                        alert(
                            `Error: Required element '${id}' not found. Please refresh the page and try again.`
                        );
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                        return;
                    }
                }

                // Get the invoice ID from the form
                const invoiceId = requiredElements.editInvoiceId.value;
                if (!invoiceId) {
                    alert('Invoice ID is missing!');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    return;
                }

                // Use the form's action attribute which should already be set
                const updateUrl =
                    `{{ route ( 'admin.invoices.update', '__ID__' ) }}`.replace('__ID__', invoiceId);
                console.log('Update URL:', updateUrl);

                // Prepare form data
                const formData = new FormData(this);

                // Convert line items to JSON
                const lineItems = [];
                const lineItemElements = requiredElements.editLineItemsContainer.querySelectorAll(
                    '.line-item');

                if (lineItemElements.length === 0) {
                    alert('Please add at least one line item');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    return;
                }

                lineItemElements.forEach((item, index) => {
                    const descriptionInput = item.querySelector('input[name*="description"]');
                    const quantityInput = item.querySelector('input[name*="quantity"]');
                    const rateInput = item.querySelector('input[name*="rate"]');
                    const amountInput = item.querySelector('input[name*="amount"]');

                    // Check if inputs exist
                    if (!descriptionInput || !quantityInput || !rateInput || !amountInput) {
                        console.warn(`Line item ${index} is missing some inputs`);
                        return;
                    }

                    const description = descriptionInput.value.trim();
                    const quantity = parseFloat(quantityInput.value) || 0;
                    const rate = parseFloat(rateInput.value) || 0;
                    const amount = parseFloat(amountInput.value) || 0;

                    if (description && quantity > 0 && rate >= 0) {
                        lineItems.push({
                            description: description,
                            quantity: quantity,
                            rate: rate,
                            amount: amount
                        });
                    }
                });

                // Validate line items
                if (lineItems.length === 0) {
                    alert('Please add at least one valid line item');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    return;
                }

                // Add line items JSON to form data
                formData.set('line_items', JSON.stringify(lineItems));

                // Add calculated values with null checks
                const addFormData = (key, elementId) => {
                    const element = requiredElements[elementId];
                    if (element) {
                        formData.set(key, element.value || '0');
                    }
                };

                addFormData('subtotal', 'editSubtotal');
                addFormData('gst_amount', 'editGstAmount');
                addFormData('tds_amount', 'editTdsAmount');
                addFormData('total_amount', 'editTotalAmount');
                addFormData('converted_amount', 'editConvertedAmount');
                addFormData('original_currency_amount', 'editTotalAmount');

                // Add tax checkboxes
                formData.set('apply_gst', requiredElements.editApplyGST.checked ? '1' : '0');
                formData.set('apply_tds', requiredElements.editApplyTDS.checked ? '1' : '0');
                formData.set('gst_percentage', requiredElements.editGstPercentage.value || '0');
                formData.set('tds_percentage', requiredElements.editTdsPercentage.value || '0');

                // Add currency fields
                formData.set('currency', requiredElements.editCurrencySelect.value || 'INR');
                formData.set('base_conversion_rate', requiredElements.editConversionRate.value || '1');
                formData.set('conversion_cost', document.getElementById('editConversionCost').value ||
                    '0');
                formData.set('conversion_rate', requiredElements.editConversionRatePercentage.value ||
                    '0'); // conversion_rate is the percentage
                formData.set('receivable_amount', document.getElementById('editReceivableAmountForeign')
                    .value || '0');
                formData.set('amount_in_inr', document.getElementById('editAmountInInr').value || '0');


                // Add method spoofing for PUT
                formData.append('_method', 'PUT');

                // Log form data for debugging
                console.log('Form Data to send:', Object.fromEntries(formData));
                console.log('Line Items:', lineItems);

                // Send update request
                fetch(updateUrl, {
                        method: 'POST', // Use POST with method spoofing
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .content
                        }
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Update response:', data);

                        if (data.success) {
                            alert(data.message || 'Invoice updated successfully!');
                            const modal = bootstrap.Modal.getInstance(document.getElementById(
                                'editInvoiceModal'));
                            if (modal) {
                                modal.hide();
                            }
                            // Reload the page to reflect changes
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        } else {
                            alert(data.message || 'Error updating invoice');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while updating the invoice: ' + error.message);
                    })
                    .finally(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    });
            });
        }
    });

    // ============ UPDATE BUTTON EVENT LISTENER ============
    document.addEventListener('DOMContentLoaded', function() {
        // Attach click event to all edit buttons
        document.querySelectorAll('.btn-update-invoice').forEach(button => {
            button.addEventListener('click', function() {
                const invoiceId = this.getAttribute('data-invoice-id') ||
                    this.closest('tr').querySelector('[data-invoice-id]')?.getAttribute(
                        'data-invoice-id');

                if (invoiceId) {
                    openEditInvoiceModal(invoiceId);
                }
            });
        });
    });

    function formatDateForInput(dateString) {
        if (!dateString) return '';

        try {
            // If it's already in YYYY-MM-DD format, return as is
            if (/^\d{4}-\d{2}-\d{2}$/.test(dateString)) {
                return dateString;
            }

            // Parse the date string
            const date = new Date(dateString);

            // Check if date is valid
            if (isNaN(date.getTime())) {
                console.warn('Invalid date:', dateString);
                return '';
            }

            // Format to YYYY-MM-DD
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');

            return `${year}-${month}-${day}`;
        } catch (error) {
            console.error('Error formatting date:', error, dateString);
            return '';
        }
    }
</script>
@endsection