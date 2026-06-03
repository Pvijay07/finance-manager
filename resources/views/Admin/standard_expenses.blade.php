@extends('Admin.layouts.app')
@section('content')
    <!-- Standard Templates Page -->
    <div id="standard-templates" class="page">
        <div class="page-header" style="margin-bottom: 30px;">
            <div>
                <h2 style="margin: 0; font-size: 24px; font-weight: 600; color: #1a1a1a;">Standard Expenses</h2>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="tabs-container" style="margin-bottom: 20px;">
            <div class="tabs-header">
                <button class="tab-button active" data-tab="form-tab" onclick="switchTab('form-tab')">
                    <i class="fas fa-plus-circle"></i> Add Expense
                </button>
                <button class="tab-button" data-tab="table-tab" onclick="switchTab('table-tab')">
                    <i class="fas fa-list"></i> Expense List
                    <span class="tab-badge">{{ $expenseTypes->total() }}</span>
                </button>
            </div>
        </div>

        <!-- Form Tab -->
        <div id="form-tab" class="tab-content active">
            <!-- Add / Edit Template Card -->
            <div class="card"
                style="margin-bottom: 30px; border: 1px solid #e0e0e0; border-radius: 8px; background: white;">
                <div class="card-header" style="padding: 20px; border-bottom: 1px solid #e0e0e0;">
                    <h3 style="margin: 0; font-size: 16px; font-weight: 600;">Add Standard Expense</h3>
                    <span id="form-mode-indicator" style="font-size: 12px; color: #666; margin-left: 10px;">(Add
                        Mode)</span>
                </div>
                <div style="padding: 25px;">
                    <form id="templateForm" action="{{ route('admin.standard-expenses.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="template_id" id="template_id">
                        <input type="hidden" name="category_type" id="actual_category_type">
                        <input type="hidden" name="sub_type" id="actual_sub_type">
                        <input type="hidden" name="planned_amount" id="default_amount" value="0">

                        @if ($errors->any())
                            <div
                                style="background: #fee; border: 1px solid #f00; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                                <ul style="margin: 0; padding-left: 20px;">
                                    @foreach ($errors->all() as $error)
                                        <li style="color: #f00;">{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Row 1 -->
                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 20px;">
                            <div class="form-group">
                                <label class="form-label"
                                    style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 13px;">Company</label>
                                <select name="company_id" id="company_id" class="form-control"
                                    style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    <option value="" selected disabled>Select Company</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label"
                                    style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 13px;">Expense
                                    Name</label>
                                <input type="text" name="expense_name" id="expense_name" class="form-control"
                                    placeholder="e.g. Office Rent - Jubilee Hills"
                                    style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                            </div>
                            <div class="form-group">
                                <label class="form-label"
                                    style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 13px;">Category
                                    Type</label>
                                <select name="category_type" id="category_type" class="form-control"
                                    style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                                    onchange="updateCategoryOptions()">
                                    <option value="">Select Expense Type</option>
                                    <option value="standard_fixed">Standard Fixed</option>
                                    <option value="standard_editable">Standard Editable</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label"
                                    style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 13px;">Category</label>
                                <select name="category_id" id="category" class="form-control"
                                    style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    <option value="">Select Category</option>
                                </select>
                            </div>
                        </div>

                        <!-- Row 2 -->
                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 20px;">

                            <div class="form-group">
                                <label class="form-label"
                                    style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 13px;">Party/Vendor
                                    Name</label>
                                <input type="text" name="party_name" id="party_name" class="form-control"
                                    placeholder="Optional"
                                    style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                            </div>

                            <div class="form-group">
                                <label class="form-label"
                                    style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 13px;">Mobile
                                    Number</label>
                                <input type="text" name="mobile_number" id="mobile_number" class="form-control"
                                    placeholder="Optional"
                                    style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                                    maxlength="10" minlength="10">
                            </div>
                            <div class="form-group">
                                <label class="form-label"
                                    style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 13px;">Actual
                                    Amount</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control" id="pp_original_amount" name="actual_amount"
                                        oninput="calculateTax()" step="1">
                                </div>
                            </div>
                        </div>

                        <!-- Row 3 - Tax Section -->
                        <div style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                            <h4 style="margin: 0 0 15px 0; font-size: 14px; font-weight: 600;">Tax Details</h4>
                            <div
                                style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 15px;">
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="applyGST" name="apply_gst"
                                            onchange="calculateTax()" value="1" checked>
                                        <label class="form-check-label small" for="applyGST">
                                            Apply GST
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label small">GST %</label>
                                    <input type="number" class="form-control form-control-sm" id="gst_percentage"
                                        name="gst_percentage" placeholder="e.g. 18" value="18" step="0.01">
                                </div>

                                <div class="form-group">
                                    <label class="form-label small">GST Amount</label>
                                    <input type="number" class="form-control form-control-sm" id="gst_subtotal"
                                        name="gst_subtotal" value="0" step="0.01" readonly style="background: #f8f9fa;">
                                </div>

                                <div class="form-group">
                                    <label class="form-label small">Total Amount</label>
                                    <input type="number" class="form-control form-control-sm" id="gst_total"
                                        name="gst_total" value="0" step="0.01" readonly style="background: #f8f9fa;">
                                </div>
                            </div>

                            <div
                                style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 15px;">
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="applyTDS" name="apply_tds"
                                            onchange="calculateTax()" value="1">
                                        <label class="form-check-label small" for="applyTDS">
                                            Apply TDS
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label small">TDS %</label>
                                    <input type="number" class="form-control form-control-sm" id="tds_percentage"
                                        name="tds_percentage" placeholder="e.g. 10" value="10" step="0.01">
                                </div>

                                <div class="form-group">
                                    <label class="form-label small">TDS Amount</label>
                                    <input type="number" class="form-control form-control-sm" id="tds_subtotal"
                                        name="tds_subtotal" value="0" step="0.01" readonly style="background: #f8f9fa;">
                                </div>

                                <div class="form-group">
                                    <label class="form-label small">Amount After TDS</label>
                                    <input type="number" class="form-control form-control-sm" id="tds_final"
                                        name="tds_final" value="0" step="0.01" readonly style="background: #f8f9fa;">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label"
                                    style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 13px;">Grand
                                    Total</label>
                                <input type="text" name="grand_total_display" id="grand_total_display" class="form-control"
                                    placeholder=""
                                    style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; font-weight: bold; color: #2563eb;"
                                    readonly>
                            </div>
                        </div>

                        <!-- Row 4 -->
                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 20px;">
                            <div class="form-group">
                                <label class="form-label"
                                    style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 13px;">Frequency</label>
                                <select name="frequency" id="frequency" class="form-control"
                                    style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                                    required>
                                    <option value="monthly" selected>Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label"
                                    style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 13px;">Due Day
                                    (of period)</label>
                                <input type="number" name="due_day" id="due_day" class="form-control" placeholder="e.g. 5"
                                    min="1" max="31" value="5"
                                    style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                                    required>
                            </div>

                            <div class="form-group">
                                <label class="form-label"
                                    style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 13px;">Default
                                    Reminder (days before)</label>
                                <input type="number" name="reminder_days" id="reminder_days" class="form-control" value="3"
                                    min="0"
                                    style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                                    required>
                            </div>
                            <div class="form-group">
                                <label class="form-label"
                                    style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 13px;">Active</label>
                                <select name="is_active" id="status" class="form-control"
                                    style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    <option value="1" selected>Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <button type="button" class="btn btn-outline" onclick="cancelEdit()"
                                style="padding: 10px 20px; margin-right: 10px; border: 1px solid #ddd; background: white;color:black; border-radius: 4px; cursor: pointer;">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary"
                                style="padding: 10px 30px; background: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;">
                                Save Expense
                            </button>
                        </div>
                        <!-- Add these hidden fields to your form, just before the closing </form> tag -->
                        <input type="hidden" name="gst_amount" id="hidden_gst_amount" value="0">
                        <input type="hidden" name="tds_amount" id="hidden_tds_amount" value="0">
                    </form>
                </div>
            </div>
        </div>
        <!-- Add this inside the card-header or after it -->
        <div id="editSuccessMessage"
            style="display: none; background: #d1fae5; color: #065f46; padding: 10px 15px; border-radius: 4px; margin: 10px 0; font-size: 14px;">
            <i class="fas fa-check-circle"></i> Editing expense template. Make your changes and click Update.
        </div>
        <!-- Table Tab -->
        <div id="table-tab" class="tab-content">
            <!-- Filter Section -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form id="filterForm" method="GET" action="{{ route('admin.standard-expenses') }}"
                        class="row g-3 align-items-end">
                        <!-- Search Field -->
                        <div class="col-md-3 col-sm-6">
                            <label class="form-label small mb-1">Search</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" name="search" value="{{ $search }}"
                                    placeholder="Search expense...">
                            </div>
                        </div>

                        <!-- Company Filter -->
                        <div class="col-md-2 col-sm-6">
                            <label class="form-label small mb-1">Company</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="fas fa-building"></i></span>
                                <select class="form-select" name="company_id">
                                    <option value="all" {{ ($companyFilter == 'all' || !$companyFilter) ? 'selected' : '' }}>
                                        All Companies</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}" {{ $companyFilter == $company->id ? 'selected' : '' }}>
                                            {{ $company->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Category Type Filter -->
                        <div class="col-md-2 col-sm-6">
                            <label class="form-label small mb-1">Expense Type</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="fas fa-filter"></i></span>
                                <select class="form-select" name="category_type">
                                    <option value="all" {{ ($categoryFilter == 'all' || !$categoryFilter) ? 'selected' : '' }}>All Types</option>
                                    <option value="standard_fixed" {{ $categoryFilter == 'standard_fixed' ? 'selected' : '' }}>
                                        Standard Fixed
                                    </option>
                                    <option value="standard_editable" {{ $categoryFilter == 'standard_editable' ? 'selected' : '' }}>
                                        Standard Editable
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Items Per Page -->
                        <div class="col-md-2 col-sm-6">
                            <label class="form-label small mb-1">Items per page</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="fas fa-list-ol"></i></span>
                                <select class="form-select" name="per_page" onchange="this.form.submit()">
                                    <option value="10" {{ ($perPage == 10 || !$perPage) ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                                </select>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="col-md-3 col-sm-12">
                            <div class="d-flex gap-2 mt-2">
                                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                    <i class="fas fa-search me-1"></i> Filter
                                </button>
                                <a href="{{ route('admin.standard-expenses') }}"
                                    class="btn btn-outline-secondary btn-sm flex-fill">
                                    <i class="fas fa-redo me-1"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f8f9fa; border-bottom: 1px solid #e0e0e0;">
                                <th
                                    style="padding: 12px 16px; text-align: left; font-weight: 600; font-size: 13px; color: #1a1a1a;">
                                    Expense</th>
                                <th
                                    style="padding: 12px 16px; text-align: left; font-weight: 600; font-size: 13px; color: #1a1a1a;">
                                    Company</th>
                                <th
                                    style="padding: 12px 16px; text-align: left; font-weight: 600; font-size: 13px; color: #1a1a1a;">
                                    Direction</th>
                                <th
                                    style="padding: 12px 16px; text-align: left; font-weight: 600; font-size: 13px; color: #1a1a1a;">
                                    Category</th>
                                <th
                                    style="padding: 12px 16px; text-align: left; font-weight: 600; font-size: 13px; color: #1a1a1a;">
                                    Expense Type</th>
                                <th
                                    style="padding: 12px 16px; text-align: left; font-weight: 600; font-size: 13px; color: #1a1a1a;">
                                    Standard Amount</th>
                                <th
                                    style="padding: 12px 16px; text-align: left; font-weight: 600; font-size: 13px; color: #1a1a1a;">
                                    Frequency</th>
                                <th
                                    style="padding: 12px 16px; text-align: center; font-weight: 600; font-size: 13px; color: #1a1a1a;">
                                    Due Day</th>
                                <th
                                    style="padding: 12px 16px; text-align: center; font-weight: 600; font-size: 13px; color: #1a1a1a;">
                                    Status</th>
                                <th
                                    style="padding: 12px 16px; text-align: center; font-weight: 600; font-size: 13px; color: #1a1a1a;">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody id="templatesTableBody">
                            @forelse($expenseTypes as $type)
                                <tr data-direction="{{ $type->entry_direction ?? 'expense' }}"
                                    data-company="{{ $type->company->name ?? 'All Companies' }}"
                                    style="border-bottom: 1px solid #f0f0f0;">
                                    <td style="padding: 14px 16px; font-size: 14px;">{{ $type->expense_name }}</td>
                                    <td style="padding: 14px 16px; font-size: 14px;">
                                        {{ $type->company->name ?? 'All Companies' }}
                                    </td>
                                    <td style="padding: 14px 16px; font-size: 14px; text-transform: capitalize;">
                                        {{ $type->entry_direction ?? 'Expense' }}
                                    </td>
                                    <td style="padding: 14px 16px; font-size: 14px; text-transform: capitalize;">
                                        {{ $type->category_name ?? 'N/A' }}
                                    </td>
                                    <td style="padding: 14px 16px; font-size: 14px; text-transform: capitalize;">
                                        @if ($type->categoryRelation->category_type == 'standard_fixed')
                                            Standard Fixed
                                        @elseif($type->categoryRelation->category_type == 'standard_editable')
                                            Standard Editable
                                        @else
                                            {{ $type->category_type ?? 'N/A' }}
                                        @endif

                                    </td>

                                    <td style="padding: 14px 16px; text-align: left; font-size: 14px; font-weight: 500;">₹
                                        {{ number_format($type->planned_amount, 0) }}
                                    </td>
                                    <td style="padding: 14px 16px; font-size: 14px; text-transform: capitalize;">
                                        {{ $type->frequency ?? 'Monthly' }}
                                    </td>
                                    <td style="padding: 14px 16px; text-align: center; font-size: 14px;">
                                        {{ $type->due_day ?? 1 }}
                                    </td>
                                    <td style="padding: 14px 16px; text-align: center;">
                                        <span
                                            style="display: inline-block; padding: 4px 12px; background: {{ $type->status == 'upcoming' ? '#22c55e' : '#94a3b8' }}; color: white; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                            {{ $type->status }}
                                        </span>
                                    </td>
                                    <td style="padding: 14px 16px; text-align: center;">
                                        @if ($type->status != 'paid' && !$type->is_split)
                                            <button class="btn-edit" onclick="editTemplate({{ $type->id }})"
                                                style="padding: 6px 16px; background: white; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; font-size: 13px; color: #2563eb;">
                                                Edit
                                            </button>
                                        @endif

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" style="padding: 40px; text-align: center; color: #6c757d;">
                                        No expenses found. Create your first expense above.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Replace the existing pagination section (around line 514-580) with this: -->
                <div style="margin-top: 20px;">
                    <!-- First pagination (custom styled) -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted small">
                            Showing {{ $expenseTypes->firstItem() ?? 0 }} to {{ $expenseTypes->lastItem() ?? 0 }}
                            of {{ $expenseTypes->total() }} entries
                        </div>

                        @if ($expenseTypes->hasPages())
                            <nav aria-label="Page navigation">
                                <ul class="pagination pagination-sm mb-0">
                                    {{-- Previous Page Link --}}
                                    @if ($expenseTypes->onFirstPage())
                                        <li class="page-item disabled">
                                            <span class="page-link">‹ Previous</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $expenseTypes->previousPageUrl() }}" rel="prev">‹
                                                Previous</a>
                                        </li>
                                    @endif

                                    {{-- Page Numbers --}}
                                    @php
                                        $current = $expenseTypes->currentPage();
                                        $last = $expenseTypes->lastPage();
                                        $range = 2; // Number of pages to show before and after current
                                        $start = max(1, $current - $range);
                                        $end = min($last, $current + $range);
                                    @endphp

                                    @if ($start > 1)
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $expenseTypes->url(1) }}">1</a>
                                        </li>
                                        @if ($start > 2)
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        @endif
                                    @endif

                                    @for ($page = $start; $page <= $end; $page++)
                                        @if ($page == $current)
                                            <li class="page-item active" aria-current="page">
                                                <span class="page-link">{{ $page }}</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $expenseTypes->url($page) }}">{{ $page }}</a>
                                            </li>
                                        @endif
                                    @endfor

                                    @if ($end < $last)
                                        @if ($end < $last - 1)
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        @endif
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $expenseTypes->url($last) }}">{{ $last }}</a>
                                        </li>
                                    @endif

                                    {{-- Next Page Link --}}
                                    @if ($expenseTypes->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $expenseTypes->nextPageUrl() }}" rel="next">Next ›</a>
                                        </li>
                                    @else
                                        <li class="page-item disabled">
                                            <span class="page-link">Next ›</span>
                                        </li>
                                    @endif
                                </ul>
                            </nav>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="editModal" class="modal"
        style="display: none; position: fixed; z-index: 1050; left: 0; top: 0; width: 100%; height: 100%; overflow: auto;">
        <div class="modal-content"
            style="background-color: #fefefe; margin: 5% auto; padding: 0; border: 1px solid #888; width: 90%; max-width: 800px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div class="modal-header"
                style="padding: 20px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 18px; font-weight: 600; color: #1a1a1a;">Edit Standard Expense
                </h3>
                <span class="close-modal" onclick="closeEditModal()"
                    style="font-size: 24px; font-weight: bold; color: #6b7280; cursor: pointer; padding: 0 10px;">&times;</span>
            </div>
            <div class="modal-body" style="padding: 20px; max-height: 70vh; overflow-y: auto;">
                <!-- The edit form will be loaded here -->
                <div id="editFormContainer"></div>
            </div>
            <div class="modal-footer" style="padding: 15px 20px; border-top: 1px solid #e5e7eb; text-align: right;">
                <button type="button" onclick="closeEditModal()" class="btn btn-outline"
                    style="padding: 8px 20px; margin-right: 10px; border: 1px solid #ddd; background: white;color:#000; border-radius: 4px; cursor: pointer;">Cancel</button>
                <button type="button" onclick="submitEditForm()" class="btn btn-primary"
                    style="padding: 8px 30px; background: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;">Update
                    Expense</button>
            </div>
        </div>
    </div>

    <script>
        // Tab switching functionality
        function switchTab(tabId, skipReset = false) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });

            // Show selected tab content
            const tabContent = document.getElementById(tabId);
            if (tabContent) {
                tabContent.classList.add('active');
            }

            // Update tab buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
            });

            const tabButton = document.querySelector(`[data-tab="${tabId}"]`);
            if (tabButton) {
                tabButton.classList.add('active');
            }

            // If switching to form tab and NOT skipping reset, reset form
            if (tabId === 'form-tab' && !skipReset) {
                // Only reset if we're not in edit mode
                const templateId = document.getElementById('template_id');
                if (!templateId || !templateId.value) {
                    resetForm();
                }
            }

            // Save last active tab to localStorage
            localStorage.setItem('lastActiveTab', tabId);
        }

        // Update the initial tab setup
        document.addEventListener('DOMContentLoaded', function () {
            const lastActiveTab = localStorage.getItem('lastActiveTab') || 'form-tab';
            switchTab(lastActiveTab, true); // Don't reset on initial load
        });



        // Check if we should switch to table tab (for when form submission redirects back)
        if (localStorage.getItem('switchToTableAfterSubmit') === 'true') {
            setTimeout(() => {
                switchTab('table-tab');
                localStorage.removeItem('switchToTableAfterSubmit');
            }, 100);
        }

        // Add keyboard shortcuts for tab switching
        document.addEventListener('keydown', function (e) {
            // Ctrl+1 for Form tab
            if (e.ctrlKey && e.key === '1') {
                e.preventDefault();
                switchTab('form-tab');
            }
            // Ctrl+2 for Table tab
            if (e.ctrlKey && e.key === '2') {
                e.preventDefault();
                switchTab('table-tab');
            }
        });

        // Show keyboard shortcut hint
        document.addEventListener('DOMContentLoaded', function () {
            console.log('Tab Navigation Shortcuts:');
            console.log('- Ctrl+1: Switch to Form tab');
            console.log('- Ctrl+2: Switch to Table tab');
        });
    </script>

    <script>
        const categoryTypeOptions = {
            'expense': [{
                value: 'standard_fixed',
                label: 'Standard Fixed'
            },
            {
                value: 'standard_editable',
                label: 'Standard Editable'
            }
            ],
            'income': [{
                value: 'income',
                label: 'Regular'
            }]
        };

        // Global variables to track amounts
        let calculatedGstAmount = 0;
        let calculatedTdsAmount = 0;
        let calculatedFinalAmount = 0;
        let originalAmount = 0;

        // DOM Ready
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize tax calculation
            calculateTax();

            // Set default entry direction to expense
            const entryDirectionEl = document.getElementById('entry_direction');
            if (entryDirectionEl) {
                entryDirectionEl.value = 'expense';
            }
            updateCategoryTypeOptions();

            // Add event listeners for tax calculation
            document.getElementById('pp_original_amount').addEventListener('input', calculateTax);

            // GST listeners
            document.getElementById('applyGST').addEventListener('change', calculateTax);
            document.getElementById('gst_percentage').addEventListener('input', calculateTax);

            // TDS listeners
            document.getElementById('applyTDS').addEventListener('change', calculateTax);
            document.getElementById('tds_percentage').addEventListener('input', calculateTax);

            // Payment field listeners
            document.getElementById('gst_amount_paid')?.addEventListener('input', updateGstPaymentStatus);
            document.getElementById('tds_amount_paid')?.addEventListener('input', updateTdsPaymentStatus);
            document.getElementById('amount_paid')?.addEventListener('input', updateMainPaymentStatus);

            // Initialize dropdowns when direction changes
            const entryDirectionDropdown = document.getElementById('entry_direction');
            if (entryDirectionDropdown) {
                entryDirectionDropdown.addEventListener('change', function () {
                    updateCategoryTypeOptions();
                });
            }

            // Initialize dropdowns when category type changes
            document.getElementById('category_type').addEventListener('change', function () {
                updateCategoryOptions();
            });
        });

        // Tax calculation function
        function calculateTax() {
            console.log('Tax calculation triggered');

            // Get original amount
            const originalAmount = parseFloat(document.getElementById('pp_original_amount').value) || 0;

            // GST Calculation
            const applyGst = document.getElementById('applyGST').checked;
            const gstPercentage = parseFloat(document.getElementById('gst_percentage').value) || 0;

            // TDS Calculation
            const applyTds = document.getElementById('applyTDS').checked;
            const tdsPercentage = parseFloat(document.getElementById('tds_percentage').value) || 0;

            let gstAmount = 0;
            let tdsAmount = 0;
            let amountAfterGst = originalAmount;
            let amountForTds = originalAmount;
            let finalAmount = originalAmount;

            // Calculate GST (if applicable)
            if (applyGst && gstPercentage > 0) {
                gstAmount = (originalAmount * gstPercentage) / 100;
                amountAfterGst = originalAmount + gstAmount;
                amountForTds = originalAmount;
            }

            // Calculate TDS (if applicable)
            if (applyTds && tdsPercentage > 0) {
                tdsAmount = (amountForTds * tdsPercentage) / 100;
            }

            // Calculate Grand Total (Base + GST - TDS)
            finalAmount = amountAfterGst - tdsAmount;

            // Update display fields
            // GST section
            document.getElementById('gst_subtotal').value = gstAmount.toFixed(2);
            document.getElementById('gst_total').value = amountAfterGst.toFixed(2);

            // TDS section
            document.getElementById('tds_subtotal').value = tdsAmount.toFixed(2);
            document.getElementById('tds_final').value = (amountForTds - tdsAmount).toFixed(2);

            // Grand Total
            document.getElementById('grand_total_display').value = '₹ ' + amountAfterGst.toFixed(2);

            // Hidden planned amount (Total before TDS)
            document.getElementById('default_amount').value = amountAfterGst.toFixed(2);

            // Update hidden tax amount fields for form submission
            document.getElementById('hidden_gst_amount').value = gstAmount.toFixed(2);
            document.getElementById('hidden_tds_amount').value = tdsAmount.toFixed(2);
        }

        // Update due amounts in payment section
        function updateDueAmounts() {
            const gstDueElement = document.getElementById('gst_due_amount');
            const tdsDueElement = document.getElementById('tds_due_amount');
            const mainDueElement = document.getElementById('main_due_amount');

            if (gstDueElement) {
                gstDueElement.textContent = '₹ ' + calculatedGstAmount.toFixed(2);
            }
            if (tdsDueElement) {
                tdsDueElement.textContent = '₹ ' + calculatedTdsAmount.toFixed(2);
            }
            if (mainDueElement) {
                mainDueElement.textContent = '₹ ' + calculatedFinalAmount.toFixed(2);
            }
        }

        // Update GST payment status based on amount paid
        function updateGstPaymentStatus() {
            const gstAmountPaid = parseFloat(document.getElementById('gst_amount_paid').value) || 0;
            const gstPaymentStatus = document.getElementById('gst_payment_status');

            if (gstAmountPaid <= 0) {
                gstPaymentStatus.value = 'pending';
            } else if (gstAmountPaid >= calculatedGstAmount) {
                gstPaymentStatus.value = 'paid';
            } else {
                gstPaymentStatus.value = 'partially_paid';
            }
        }

        // Update TDS payment status based on amount paid
        function updateTdsPaymentStatus() {
            const tdsAmountPaid = parseFloat(document.getElementById('tds_amount_paid').value) || 0;
            const tdsPaymentStatus = document.getElementById('tds_payment_status');

            if (tdsAmountPaid <= 0) {
                tdsPaymentStatus.value = 'pending';
            } else if (tdsAmountPaid >= calculatedTdsAmount) {
                tdsPaymentStatus.value = 'paid';
            } else {
                tdsPaymentStatus.value = 'partially_paid';
            }
        }

        // Update main payment status
        function updateMainPaymentStatus() {
            const amountPaid = parseFloat(document.getElementById('amount_paid').value) || 0;
            const paymentStatus = document.getElementById('payment_status');

            if (amountPaid <= 0) {
                paymentStatus.value = 'pending';
            } else if (amountPaid >= calculatedFinalAmount) {
                paymentStatus.value = 'paid';
            } else {
                paymentStatus.value = 'partially_paid';
            }

            // Update overall status
            updateOverallStatus();
        }

        // Update overall status
        function updateOverallStatus() {
            const paymentStatus = document.getElementById('payment_status').value;
            const statusSelect = document.getElementById('status');

            if (paymentStatus === 'paid') {
                statusSelect.value = 'paid';
            } else if (paymentStatus === 'partially_paid') {
                statusSelect.value = 'pending';
            } else {
                statusSelect.value = 'upcoming';
            }
        }

        // Update category type options based on entry direction
        function updateCategoryTypeOptions() {
            const direction = 'expense';
            const categoryTypeSelect = document.getElementById('category_type');
            const categorySelect = document.getElementById('category');

            // Reset dependent dropdown
            categoryTypeSelect.innerHTML = '<option value="">Select Category Type</option>';
            categorySelect.innerHTML = '<option value="">Select Category</option>';
            categorySelect.disabled = true;

            if (direction && categoryTypeOptions[direction]) {
                // Enable and populate category type dropdown
                categoryTypeSelect.disabled = false;

                categoryTypeOptions[direction].forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.value;
                    option.textContent = type.label;
                    categoryTypeSelect.appendChild(option);
                });
            } else {
                categoryTypeSelect.disabled = true;
            }
        }

        // Update category options based on category type
        function updateCategoryOptions() {
            const direction = 'expense';
            const combinedCategoryType = document.getElementById('category_type').value;
            const categorySelect = document.getElementById('category');

            // Reset category dropdown
            categorySelect.innerHTML = '<option value="">Select Category</option>';
            categorySelect.disabled = true;

            if (direction && combinedCategoryType) {
                // Split combined category type into category_type and sub_type
                let category_type, sub_type;

                if (combinedCategoryType === 'standard_fixed') {
                    category_type = 'standard';
                    sub_type = 'fixed';
                } else if (combinedCategoryType === 'standard_editable') {
                    category_type = 'standard';
                    sub_type = 'editable';
                } else if (combinedCategoryType === 'regular') {
                    category_type = 'regular';
                    sub_type = null;
                }

                // Set hidden fields
                document.getElementById('actual_category_type').value = category_type;
                document.getElementById('actual_sub_type').value = sub_type;

                // Fetch categories based on selected criteria
                fetchCategories(direction, category_type, sub_type);
            }
        }

        // Fetch categories from server
        function fetchCategories(direction, category_type, sub_type) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const categorySelect = document.getElementById('category');

            // Show loading
            categorySelect.innerHTML = '<option value="">Loading categories...</option>';

            fetch('https://xhtmlreviews.in/beta-finance/admin/standard-expenses/get-categories', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    direction: direction,
                    category_type: category_type + '_' + sub_type,
                    sub_type: sub_type
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.categories.length > 0) {
                        // Populate category dropdown
                        categorySelect.innerHTML = '<option value="">Select Category</option>';
                        categorySelect.disabled = false;

                        data.categories.forEach(category => {
                            const option = document.createElement('option');
                            option.value = category.id;
                            option.textContent = category.name;
                            if (category.description) {
                                option.dataset.description = category.description;
                            }
                            categorySelect.appendChild(option);
                        });
                    } else {
                        categorySelect.innerHTML = '<option value="">No categories found</option>';
                        categorySelect.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    categorySelect.innerHTML = '<option value="">Error loading categories</option>';
                    categorySelect.disabled = false;
                });
        }
        // Add this function to handle form validation
        function validateForm() {
            let isValid = true;
            const errors = [];

            // Get form values
            const companyId = document.getElementById('company_id').value;
            const expenseName = document.getElementById('expense_name').value.trim();
            const categoryType = document.getElementById('category_type').value;
            const categoryId = document.getElementById('category').value;
            const actualAmount = document.getElementById('pp_original_amount').value;
            const gstPercentage = document.getElementById('gst_percentage').value;
            const tdsPercentage = document.getElementById('tds_percentage').value;
            const mobileNumber = document.getElementById('mobile_number').value.trim();
            const dueDay = document.getElementById('due_day').value;
            const frequency = document.getElementById('frequency').value;

            // Reset error states
            resetErrorStates();
            if (!companyId) {
                showError('company_id', 'Please select Company');
                isValid = false;
                errors.push('Please select Company');
            }
            // 1. Expense Name validation
            if (!expenseName) {
                showError('expense_name', 'Expense Name is required');
                isValid = false;
                errors.push('Expense Name is required');
            } else if (expenseName.length > 255) {
                showError('expense_name', 'Expense Name cannot exceed 255 characters');
                isValid = false;
                errors.push('Expense Name is too long');
            }

            // 2. Category Type validation
            if (!categoryType) {
                showError('category_type', 'Category Type is required');
                isValid = false;
                errors.push('Category Type is required');
            }

            // 3. Category validation
            if (!categoryId) {
                showError('category', 'Category is required');
                isValid = false;
                errors.push('Category is required');
            }

            // 4. Actual Amount validation
            if (!actualAmount) {
                showError('pp_original_amount', 'Actual Amount is required');
                isValid = false;
                errors.push('Actual Amount is required');
            } else if (parseFloat(actualAmount) <= 0) {
                showError('pp_original_amount', 'Actual Amount must be greater than 0');
                isValid = false;
                errors.push('Actual Amount must be positive');
            } else if (parseFloat(actualAmount) > 999999999.99) {
                showError('pp_original_amount', 'Amount is too large');
                isValid = false;
                errors.push('Amount exceeds maximum limit');
            }

            // 5. GST Percentage validation if GST is checked
            if (document.getElementById('applyGST').checked) {
                if (!gstPercentage || gstPercentage === '') {
                    showError('gst_percentage', 'GST % is required when GST is applied');
                    isValid = false;
                    errors.push('GST percentage is required');
                } else if (parseFloat(gstPercentage) < 0 || parseFloat(gstPercentage) > 100) {
                    showError('gst_percentage', 'GST % must be between 0 and 100');
                    isValid = false;
                    errors.push('GST percentage is invalid');
                }
            }

            // 6. TDS Percentage validation if TDS is checked
            if (document.getElementById('applyTDS').checked) {
                if (!tdsPercentage || tdsPercentage === '') {
                    showError('tds_percentage', 'TDS % is required when TDS is applied');
                    isValid = false;
                    errors.push('TDS percentage is required');
                } else if (parseFloat(tdsPercentage) < 0 || parseFloat(tdsPercentage) > 100) {
                    showError('tds_percentage', 'TDS % must be between 0 and 100');
                    isValid = false;
                    errors.push('TDS percentage is invalid');
                }
            }

            // 7. Mobile Number validation (optional but if filled, validate format)
            if (mobileNumber && !/^\d{10}$/.test(mobileNumber)) {
                showError('mobile_number', 'Mobile number must be 10 digits');
                isValid = false;
                errors.push('Invalid mobile number format');
            }

            // 8. Due Day validation
            if (!dueDay) {
                showError('due_day', 'Due Day is required');
                isValid = false;
                errors.push('Due Day is required');
            } else {
                const day = parseInt(dueDay);
                if (day < 1 || day > 31) {
                    showError('due_day', 'Due Day must be between 1 and 31');
                    isValid = false;
                    errors.push('Invalid Due Day');
                } else if (frequency === 'monthly' && day > 28) {
                    // Additional validation for specific months could be added here
                    // Currently just warning
                    console.log('Warning: Due day might be invalid for some months');
                }
            }

            // 9. Reminder Days validation
            const reminderDays = document.getElementById('reminder_days').value;
            if (reminderDays < 0) {
                showError('reminder_days', 'Reminder days cannot be negative');
                isValid = false;
                errors.push('Invalid reminder days');
            }

            // 10. Party/Vendor Name length validation
            const partyName = document.getElementById('party_name').value.trim();
            if (partyName.length > 255) {
                showError('party_name', 'Party/Vendor Name cannot exceed 255 characters');
                isValid = false;
                errors.push('Party/Vendor Name is too long');
            }



            return isValid;
        }

        // Helper function to show error for a specific field
        function showError(fieldId, message) {
            const field = document.getElementById(fieldId);
            const formGroup = field.closest('.form-group');

            // Add error class to field
            field.style.borderColor = '#f7041d';

            // Create or update error message
            let errorElement = formGroup.querySelector('.error-message');
            if (!errorElement) {
                errorElement = document.createElement('div');
                errorElement.className = 'error-message';
                errorElement.style.color = '#fb0019';
                errorElement.style.fontSize = '14px';
                errorElement.style.marginTop = '4px';
                formGroup.appendChild(errorElement);
            }
            errorElement.textContent = message;
        }

        // Helper function to reset all error states
        function resetErrorStates() {
            // Clear all error messages
            const errorMessages = document.querySelectorAll('.error-message');
            errorMessages.forEach(el => el.remove());

            // Reset border colors
            const formControls = document.querySelectorAll('.form-control');
            formControls.forEach(control => {
                control.style.borderColor = '#ddd';
            });
        }


        // Function to validate mobile number in real-time
        function validateMobileNumber(input) {
            const value = input.value.replace(/\D/g, '');
            input.value = value; // Remove non-numeric characters

            if (value && !/^\d{10}$/.test(value)) {
                showError('mobile_number', 'Mobile number must be 10 digits');
            } else {
                resetError('mobile_number');
            }
        }

        // Function to validate number input (positive only)
        function validatePositiveNumber(input) {
            if (input.value < 0) {
                input.value = 0;
                showError(input.id, 'Value cannot be negative');
            } else {
                resetError(input.id);
            }
        }

        // Helper function to reset error for a specific field
        function resetError(fieldId) {
            const field = document.getElementById(fieldId);
            const formGroup = field.closest('.form-group');
            field.style.borderColor = '#ddd';

            const errorElement = formGroup.querySelector('.error-message');
            if (errorElement) {
                errorElement.remove();
            }
        }

        // Function to validate GST percentage
        function validateGSTPercentage() {
            const gstField = document.getElementById('gst_percentage');
            const value = parseFloat(gstField.value);

            if (isNaN(value) || value < 0 || value > 100) {
                showError('gst_percentage', 'GST % must be between 0 and 100');
                return false;
            }
            resetError('gst_percentage');
            return true;
        }

        // Function to validate TDS percentage
        function validateTDSPercentage() {
            const tdsField = document.getElementById('tds_percentage');
            const value = parseFloat(tdsField.value);

            if (isNaN(value) || value < 0 || value > 100) {
                showError('tds_percentage', 'TDS % must be between 0 and 100');
                return false;
            }
            resetError('tds_percentage');
            return true;
        }

        // Function to validate due day based on frequency
        function validateDueDay() {
            const dueDay = document.getElementById('due_day');
            const frequency = document.getElementById('frequency').value;
            const day = parseInt(dueDay.value);

            if (isNaN(day) || day < 1 || day > 31) {
                showError('due_day', 'Due Day must be between 1 and 31');
                return false;
            }

            if (frequency === 'monthly') {
                if (day > 31) {
                    showError('due_day', 'Day cannot exceed 31 for monthly frequency');
                    return false;
                }
            } else if (frequency === 'quarterly') {
                if (day > 90) {
                    showError('due_day', 'Day cannot exceed 90 for quarterly frequency');
                    return false;
                }
            } else if (frequency === 'yearly') {
                if (day > 365) {
                    showError('due_day', 'Day cannot exceed 365 for yearly frequency');
                    return false;
                }
            }

            resetError('due_day');
            return true;
        }
        // Handle form submission
        // Add event listeners for real-time validation
        document.addEventListener('DOMContentLoaded', function () {
            // Mobile number validation
            const mobileField = document.getElementById('mobile_number');
            mobileField.addEventListener('input', function () {
                validateMobileNumber(this);
            });

            // Amount validation
            const amountField = document.getElementById('pp_original_amount');
            amountField.addEventListener('input', function () {
                validatePositiveNumber(this);
                calculateTax(); // Recalculate tax when amount changes
            });

            // GST percentage validation
            const gstField = document.getElementById('gst_percentage');
            gstField.addEventListener('input', function () {
                if (document.getElementById('applyGST').checked) {
                    validateGSTPercentage();
                }
                calculateTax();
            });

            // TDS percentage validation
            const tdsField = document.getElementById('tds_percentage');
            tdsField.addEventListener('input', function () {
                if (document.getElementById('applyTDS').checked) {
                    validateTDSPercentage();
                }
                calculateTax();
            });

            // Due day validation
            const dueDayField = document.getElementById('due_day');
            dueDayField.addEventListener('input', validateDueDay);

            // Frequency change affects due day validation
            const frequencyField = document.getElementById('frequency');
            frequencyField.addEventListener('change', validateDueDay);

            // Expense name length validation
            const expenseNameField = document.getElementById('expense_name');
            expenseNameField.addEventListener('input', function () {
                if (this.value.length > 255) {
                    showError('expense_name', 'Maximum 255 characters allowed');
                } else {
                    resetError('expense_name');
                }
            });

            // Party name length validation
            const partyNameField = document.getElementById('party_name');
            partyNameField.addEventListener('input', function () {
                if (this.value.length > 255) {
                    showError('party_name', 'Maximum 255 characters allowed');
                } else {
                    resetError('party_name');
                }
            });
        });
        // Handle form submission
        document.getElementById('templateForm')?.addEventListener('submit', function (e) {
            e.preventDefault(); // Prevent default submission

            // Calculate final amounts
            calculateTax();

            // Validate form
            if (!validateForm()) {
                // Scroll to first error
                const firstError = document.querySelector('.error-message');
                if (firstError) {
                    firstError.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
                return false;
            }

            // Check if we're in edit mode
            const templateId = document.getElementById('template_id').value;
            const isEditMode = templateId !== '';
            // Show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.textContent = isEditMode ? 'Updating...' : 'Saving...';
            submitButton.disabled = true;

            // Submit the form
            this.submit();
        });

        // Fill form when editing template
        async function editTemplate(id) {
            try {
                console.log('Starting edit for ID:', id);

                // Store the edit button for loading state
                const editBtn = event.target;
                const originalText = editBtn.textContent;
                editBtn.textContent = 'Loading...';
                editBtn.disabled = true;
                editBtn.classList.add('loading');

                // Load expense data
                const expenseResponse = await fetch(
                    `https://xhtmlreviews.in/beta-finance/admin/standard-expenses/${id}`);
                const expenseData = await expenseResponse.json();

                // Load tax data
                let taxData = {};
                try {
                    const taxResponse = await fetch(
                        `https://xhtmlreviews.in/beta-finance/admin/standard-expenses/${id}/taxes`);
                    if (taxResponse.ok) {
                        taxData = await taxResponse.json();
                    }
                } catch (taxError) {
                    console.warn('Could not load tax data:', taxError);
                }

                console.log('Edit data:', expenseData);
                console.log('Tax data:', taxData);

                // Generate the edit form HTML
                const editFormHtml = generateEditForm(expenseData, taxData, id);

                // Insert the form into the modal
                document.getElementById('editFormContainer').innerHTML = editFormHtml;

                // Show the modal
                document.getElementById('editModal').style.display = 'block';

                // Initialize form functionality
                initializeEditForm(id, expenseData);

                // Restore edit button
                editBtn.textContent = originalText;
                editBtn.disabled = false;
                editBtn.classList.remove('loading');

                console.log('Edit form loaded in modal');

            } catch (error) {
                console.error('Error loading template:', error);
                alert('Error loading template data: ' + (error.message || 'Unknown error'));

                // Restore edit button on error
                const editBtn = event.target;
                if (editBtn) {
                    editBtn.textContent = 'Edit';
                    editBtn.disabled = false;
                    editBtn.classList.remove('loading');
                }
            }
        }

        // Generate the edit form HTML
        function generateEditForm(expenseData, taxData, id) {

            const categories = window.categories || [];

            // Build category options
            let categoryOptions = '<option value="">Select Category</option>';
            categories.forEach(category => {
                const selected = expenseData.category_id == category.id ? 'selected' : '';
                categoryOptions += `<option value="${category.id}" ${selected}>${category.name}</option>`;
            });
            return `
                                                                                    <form id="editTemplateForm" method="POST" action="">
                                                                                        @csrf
                                                                                        <input type="hidden" name="_method" value="PUT">
                                                                                        <input type="hidden" name="template_id" id="edit_template_id" value="${expenseData.id}" data-category-id="${expenseData.category_id}">
                                                                                        <input type="hidden" name="category_type" id="edit_actual_category_type">
                                                                                        <input type="hidden" name="sub_type" id="edit_actual_sub_type">
                                                                                        <input type="hidden" name="planned_amount" id="edit_default_amount" value="0">
                                                                                        <input type="hidden" name="gst_amount" id="edit_hidden_gst_amount" value="0">
                                                                                        <input type="hidden" name="tds_amount" id="edit_hidden_tds_amount" value="0">

                                                                                        <!-- Row 1 -->
                                                                                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 20px;">
                                                                                            <div class="form-group">
                                                                                                <label class="form-label" style="display: block; margin-bottom: 6px; font-weight: 500; font-size: 12px;">Company</label>
                                                                                                <select name="company_id" id="edit_company_id" class="form-control" style="width: 100%; padding: 6px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px;">
                                                                                                    <option value="" selected disabled>Select Company</option>
                                                                                                    @foreach ($companies as $company)
                                                                                                        <option value="{{ $company->id }}" ${expenseData.company_id == {{ $company->id }} ? 'selected' : ''}>{{ $company->name }}</option>
                                                                                                    @endforeach
                                                                                                </select>
                                                                                            </div>
                                                                                            <div class="form-group">
                                                                                                <label class="form-label" style="display: block; margin-bottom: 6px; font-weight: 500; font-size: 12px;">Expense Name</label>
                                                                                                <input type="text" name="expense_name" id="edit_expense_name" class="form-control" value="${expenseData.expense_name || expenseData.name || ''}" style="width: 100%; padding: 6px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px;">
                                                                                            </div>
                                                                                        </div>

                                                                                        <!-- Row 2 -->
                                                                                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 20px;">
                                                                                            <div class="form-group">
                                                                                                <label class="form-label" style="display: block; margin-bottom: 6px; font-weight: 500; font-size: 12px;">Category Type</label>
                                                                                                <select name="category_type" id="edit_category_type" class="form-control" style="width: 100%; padding: 6px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px;" onchange="updateEditCategoryOptions()">
                                                                                                    <option value="">Select Category Type</option>
                                                                                                    <option value="standard_fixed" ${expenseData.category_type === 'standard' && expenseData.sub_type === 'fixed' ? 'selected' : ''}>Standard Fixed</option>
                                                                                                    <option value="standard_editable" ${expenseData.category_type === 'standard' && expenseData.sub_type === 'editable' ? 'selected' : ''}>Standard Editable</option>
                                                                                                </select>
                                                                                            </div>
                                                                                            <div class="form-group">
                                                                                                <label class="form-label" style="display: block; margin-bottom: 6px; font-weight: 500; font-size: 12px;">Category</label>
                                                                                                <select name="category_id" id="edit_category" class="form-control" style="width: 100%; padding: 6px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px;" data-selected-id="${expenseData.category_id}">
                                                                                                    <option value="">Select Category</option>
                                                                                                </select>
                                                                                            </div>
                                                                                        </div>

                                                                                        <!-- Row 3 -->
                                                                                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 20px;">
                                                                                            <div class="form-group">
                                                                                                <label class="form-label" style="display: block; margin-bottom: 6px; font-weight: 500; font-size: 12px;">Party/Vendor Name</label>
                                                                                                <input type="text" name="party_name" id="edit_party_name" class="form-control" value="${expenseData.party_name || ''}" style="width: 100%; padding: 6px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px;">
                                                                                            </div>
                                                                                            <div class="form-group">
                                                                                                <label class="form-label" style="display: block; margin-bottom: 6px; font-weight: 500; font-size: 12px;">Mobile Number</label>
                                                                                                <input type="number" name="mobile_number" id="edit_mobile_number" class="form-control" value="${expenseData.mobile_number || ''}" style="width: 100%; padding: 6px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px;" maxlength="10">
                                                                                            </div>
                                                                                        </div>

                                                                                        <!-- Amount Row -->
                                                                                        <div style="margin-bottom: 20px;">
                                                                                            <div class="form-group">
                                                                                                <label class="form-label" style="display: block; margin-bottom: 6px; font-weight: 500; font-size: 12px;">Actual Amount</label>
                                                                                                <div class="input-group input-group-sm" style="display: flex;">
                                                                                                    <span class="input-group-text" style="padding: 6px 10px; background: #f8f9fa; border: 1px solid #ddd; border-right: none; border-radius: 4px 0 0 4px;">₹</span>
                                                                                                    <input type="number" class="form-control" id="edit_pp_original_amount" name="actual_amount" value="${parseFloat(expenseData.actual_amount) ? parseFloat(expenseData.actual_amount) : ((parseFloat(expenseData.planned_amount) || 0) - (parseFloat(taxData?.taxes?.gst?.amount) || 0) || 0)}" style="flex: 1; padding: 6px 10px; border: 1px solid #ddd; border-left: none; border-radius: 0 4px 4px 0; font-size: 13px;" oninput="calculateEditTax()">
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>

                                                                                        <!-- Tax Section -->
                                                                                        <div id="edit_tax_section_container" style="border: 1px solid #e0e0e0; border-radius: 6px; padding: 15px; margin-bottom: 20px;">
                                                                                            <h4 style="margin: 0 0 10px 0; font-size: 13px; font-weight: 600;">Tax Details</h4>

                                                                                            <!-- GST Section -->
                                                                                            <div id="edit_gst_section" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 10px;">
                                                                                                <div class="form-group">
                                                                                                    <div class="form-check">
                                                                                                        <input class="form-check-input" type="checkbox" id="edit_applyGST" name="apply_gst" value="1" ${taxData.apply_gst ? 'checked' : ''} onchange="calculateEditTax()">
                                                                                                        <label class="form-check-label" style="font-size: 12px;" for="edit_applyGST">Apply GST</label>
                                                                                                    </div>
                                                                                                </div>
                                                                                                <div class="form-group">
                                                                                                    <label class="form-label" style="font-size: 11px;">GST %</label>
                                                                                                    <input type="number" class="form-control form-control-sm" id="edit_gst_percentage" name="gst_percentage" value="${taxData.taxes?.gst?.percentage || 18}" style="font-size: 12px; padding: 4px 8px;" oninput="calculateEditTax()">
                                                                                                </div>
                                                                                                <div class="form-group">
                                                                                                    <label class="form-label" style="font-size: 11px;">GST Amount</label>
                                                                                                    <input type="number" class="form-control form-control-sm" id="edit_gst_subtotal" name="gst_subtotal" value="0" readonly style="font-size: 12px; padding: 4px 8px; background: #f8f9fa;">
                                                                                                </div>
                                                                                                <div class="form-group">
                                                                                                    <label class="form-label" style="font-size: 11px;">Total Amount</label>
                                                                                                    <input type="number" class="form-control form-control-sm" id="edit_gst_total" name="gst_total" value="0" readonly style="font-size: 12px; padding: 4px 8px; background: #f8f9fa;">
                                                                                                </div>
                                                                                            </div>

                                                                                            <!-- TDS Section -->
                                                                                            <div id="edit_tds_section" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 10px;">
                                                                                                <div class="form-group">
                                                                                                    <div class="form-check">
                                                                                                        <input class="form-check-input" type="checkbox" id="edit_applyTDS" name="apply_tds" value="1" ${taxData.apply_tds ? 'checked' : ''} onchange="calculateEditTax()">
                                                                                                        <label class="form-check-label" style="font-size: 12px;" for="edit_applyTDS">Apply TDS</label>
                                                                                                    </div>
                                                                                                </div>
                                                                                                <div class="form-group">
                                                                                                    <label class="form-label" style="font-size: 11px;">TDS %</label>
                                                                                                    <input type="number" class="form-control form-control-sm" id="edit_tds_percentage" name="tds_percentage" value="${taxData.taxes?.tds?.percentage || 10}" style="font-size: 12px; padding: 4px 8px;" oninput="calculateEditTax()">
                                                                                                </div>
                                                                                                <div class="form-group">
                                                                                                    <label class="form-label" style="font-size: 11px;">TDS Amount</label>
                                                                                                    <input type="number" class="form-control form-control-sm" id="edit_tds_subtotal" name="tds_subtotal" value="0" readonly style="font-size: 12px; padding: 4px 8px; background: #f8f9fa;">
                                                                                                </div>
                                                                                                <div class="form-group">
                                                                                                    <label class="form-label" style="font-size: 11px;">Amount After TDS</label>
                                                                                                    <input type="number" class="form-control form-control-sm" id="edit_tds_final" name="tds_final" value="0" readonly style="font-size: 12px; padding: 4px 8px; background: #f8f9fa;">
                                                                                                </div>
                                                                                            </div>

                                                                                            <div class="form-group">
                                                                                                <label class="form-label" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 12px;">Grand Total</label>
                                                                                                <input type="text" name="grand_total_display" id="edit_grand_total_display" class="form-control" value="" readonly style="padding: 6px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px; font-weight: bold; color: #2563eb;">
                                                                                            </div>
                                                                                        </div>

                                                                                        <!-- Settings Row -->
                                                                                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 20px;">
                                                                                            <div class="form-group">
                                                                                                <label class="form-label" style="display: block; margin-bottom: 6px; font-weight: 500; font-size: 12px;">Frequency</label>
                                                                                                <select name="frequency" id="edit_frequency" class="form-control" style="width: 100%; padding: 6px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px;">
                                                                                                    <option value="monthly" ${expenseData.frequency === 'monthly' ? 'selected' : ''}>Monthly</option>
                                                                                                    <option value="quarterly" ${expenseData.frequency === 'quarterly' ? 'selected' : ''}>Quarterly</option>
                                                                                                    <option value="yearly" ${expenseData.frequency === 'yearly' ? 'selected' : ''}>Yearly</option>
                                                                                                </select>
                                                                                            </div>
                                                                                            <div class="form-group">
                                                                                                <label class="form-label" style="display: block; margin-bottom: 6px; font-weight: 500; font-size: 12px;">Due Day</label>
                                                                                                <input type="number" name="due_day" id="edit_due_day" class="form-control" value="${expenseData.due_day || 5}" min="1" max="31" style="width: 100%; padding: 6px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px;">
                                                                                            </div>
                                                                                        </div>

                                                                                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 20px;">
                                                                                            <div class="form-group">
                                                                                                <label class="form-label" style="display: block; margin-bottom: 6px; font-weight: 500; font-size: 12px;">Reminder Days</label>
                                                                                                <input type="number" name="reminder_days" id="edit_reminder_days" class="form-control" value="${expenseData.reminder_days || 3}" min="0" style="width: 100%; padding: 6px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px;">
                                                                                            </div>
                                                                                            <div class="form-group">
                                                                                                <label class="form-label" style="display: block; margin-bottom: 6px; font-weight: 500; font-size: 12px;">Active</label>
                                                                                                <select name="is_active" id="edit_status" class="form-control" style="width: 100%; padding: 6px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px;">
                                                                                                    <option value="1" ${expenseData.is_active ? 'selected' : ''}>Yes</option>
                                                                                                    <option value="0" ${!expenseData.is_active ? 'selected' : ''}>No</option>
                                                                                                </select>
                                                                                            </div>
                                                                                        </div>

                                                                                        <div id="editFormErrors" style="display: none; background: #fee; border: 1px solid #f00; padding: 10px; margin-bottom: 20px; border-radius: 4px;"></div>
                                                                                    </form>
                                                                                `;
        }

        // Initialize edit form
        function initializeEditForm(id, expenseData) {
            // Set form action
            const form = document.getElementById('editTemplateForm');
            if (form) {
                form.action = `https://xhtmlreviews.in/beta-finance/admin/standard-expenses/${id}`;
            }

            // Set category dropdown
            expenseData = {
                category_id: expenseData.category_id
            };
            setTimeout(() => {
                // First set the category type dropdown
                const categoryTypeSelect = document.getElementById('edit_category_type');
                const categorySelect = document.getElementById('edit_category');

                // If category type is already selected, trigger the change to load categories
                if (categoryTypeSelect.value) {
                    updateEditCategoryOptions();

                    // Wait a bit for categories to load, then set the selected category
                    setTimeout(() => {
                        if (expenseData.category_id && categorySelect) {
                            categorySelect.value = expenseData.category_id;
                        }
                    }, 400);
                } else {
                    // If no category type selected, just set the category if possible
                    if (expenseData.category_id && categorySelect) {
                        categorySelect.value = expenseData.category_id;
                    }
                }
                // Calculate tax
                calculateEditTax();
            }, 200);

            // Initialize event listeners
            document.getElementById('edit_pp_original_amount').addEventListener('input', function () {
                calculateEditTax();
            });
            document.getElementById('edit_applyGST').addEventListener('change', function () {
                calculateEditTax();
            });
            document.getElementById('edit_gst_percentage').addEventListener('input', function () {
                calculateEditTax();
            });
            document.getElementById('edit_applyTDS').addEventListener('change', function () {
                calculateEditTax();
            });
            document.getElementById('edit_tds_percentage').addEventListener('input', function () {
                calculateEditTax();
            });
        }

        // Calculate tax for edit form
        function calculateEditTax() {
            const baseAmount = parseFloat(document.getElementById('edit_pp_original_amount').value) || 0;
            const applyGst = document.getElementById('edit_applyGST').checked;
            const gstPercentage = parseFloat(document.getElementById('edit_gst_percentage').value) || 0;
            const applyTds = document.getElementById('edit_applyTDS').checked;
            const tdsPercentage = parseFloat(document.getElementById('edit_tds_percentage').value) || 0;

            let amountAfterGst = baseAmount;
            let gstAmount = 0;
            let tdsAmount = 0;

            if (applyGst) {
                gstAmount = (baseAmount * gstPercentage) / 100;
                amountAfterGst = baseAmount + gstAmount;
            }

            let amountForTds = baseAmount;
            if (applyTds) {
                tdsAmount = (amountForTds * tdsPercentage) / 100;
            }

            const finalAmount = amountAfterGst - tdsAmount;

            // Update display fields
            document.getElementById('edit_gst_subtotal').value = gstAmount.toFixed(2);
            document.getElementById('edit_gst_total').value = amountAfterGst.toFixed(2);
            document.getElementById('edit_tds_subtotal').value = tdsAmount.toFixed(2);
            document.getElementById('edit_tds_final').value = (amountForTds - tdsAmount).toFixed(2);
            document.getElementById('edit_grand_total_display').value = '₹ ' + amountAfterGst.toFixed(2);

            // Update hidden fields
            document.getElementById('edit_default_amount').value = amountAfterGst.toFixed(2);
            document.getElementById('edit_hidden_gst_amount').value = gstAmount.toFixed(2);
            document.getElementById('edit_hidden_tds_amount').value = tdsAmount.toFixed(2);
        }
        // Update category options for edit form
        function updateEditCategoryOptions() {
            const direction = 'expense';
            const combinedCategoryType = document.getElementById('edit_category_type').value;
            const categorySelect = document.getElementById('edit_category');

            categorySelect.innerHTML = '<option value="">Select Category</option>';
            categorySelect.disabled = true;

            if (direction && combinedCategoryType) {
                let category_type, sub_type;

                if (combinedCategoryType === 'standard_fixed') {
                    category_type = 'standard';
                    sub_type = 'fixed';
                } else if (combinedCategoryType === 'standard_editable') {
                    category_type = 'standard';
                    sub_type = 'editable';
                }

                document.getElementById('edit_actual_category_type').value = category_type;
                document.getElementById('edit_actual_sub_type').value = sub_type;

                fetchEditCategories(direction, category_type, sub_type);
            }
        }

        // Fetch categories for edit form
        function fetchEditCategories(direction, category_type, sub_type) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const categorySelect = document.getElementById('edit_category');

            categorySelect.innerHTML = '<option value="">Loading categories...</option>';

            fetch('https://xhtmlreviews.in/beta-finance/admin/standard-expenses/get-categories', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    direction: direction,
                    category_type: category_type + '_' + sub_type,
                    sub_type: sub_type
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.categories.length > 0) {
                        categorySelect.innerHTML = '<option value="">Select Category</option>';
                        categorySelect.disabled = false;

                        data.categories.forEach(category => {
                            const option = document.createElement('option');
                            option.value = category.id;
                            option.textContent = category.name;
                            categorySelect.appendChild(option);
                        });
                    } else {
                        categorySelect.innerHTML = '<option value="">No categories found</option>';
                        categorySelect.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    categorySelect.innerHTML = '<option value="">Error loading categories</option>';
                    categorySelect.disabled = false;
                });
        }

        // Submit edit form
        function submitEditForm() {
            const form = document.getElementById('editTemplateForm');
            if (!form) return;

            // Calculate final tax before submission
            calculateEditTax();

            // Validate form
            if (!validateEditForm()) {
                return;
            }

            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) {
                console.error('CSRF token not found');
                alert('Security token missing. Please refresh the page.');
                return;
            }

            // Show loading
            const submitBtn = document.querySelector('.modal-footer .btn-primary');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Updating...';
            submitBtn.disabled = true;

            // Create FormData from the form
            const formData = new FormData(form);

            // Debug: Log all form data
            console.log('FormData entries:');
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }

            // Submit via fetch
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken, // Add CSRF token header
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    console.log('Response status:', response.status);

                    if (response.redirected) {
                        window.location.href = response.url;
                    } else if (response.ok) {
                        return response.json();
                    } else {
                        return response.json().then(data => {
                            throw new Error(data.message || `Server error: ${response.status}`);
                        });
                    }
                })
                .then(data => {
                    console.log('Success response:', data);
                    if (data.success) {
                        alert('Expense updated successfully!');
                        window.location.reload();
                    } else {
                        throw new Error(data.message || 'Update failed');
                    }
                })
                .catch(error => {
                    console.error('Update error:', error);

                    // Check if it's a validation error
                    if (error.errors) {
                        displayEditFormErrors(error.errors);
                    } else {
                        alert('Error: ' + error.message);
                    }

                    // Restore button
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                });
        }

        // Helper function to display validation errors
        function displayEditFormErrors(errors) {
            const errorContainer = document.getElementById('editFormErrors');
            if (!errorContainer) return;

            let errorHtml = '<strong>Please fix the following errors:</strong><ul>';
            for (const [field, messages] of Object.entries(errors)) {
                errorHtml += `<li><strong>${field}:</strong> ${messages.join(', ')}</li>`;
            }
            errorHtml += '</ul>';

            errorContainer.innerHTML = errorHtml;
            errorContainer.style.display = 'block';

            // Scroll to errors
            errorContainer.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
        }
        // Validate edit form
        function validateEditForm() {
            const errors = [];
            const errorContainer = document.getElementById('editFormErrors');

            // Clear previous errors
            errorContainer.innerHTML = '';
            errorContainer.style.display = 'none';

            // Required fields validation
            const requiredFields = [{

                id: 'edit_company_id',
                name: 'Company Name'
            }, {

                id: 'edit_expense_name',
                name: 'Expense Name'
            },
            {
                id: 'edit_category_type',
                name: 'Category Type'
            },
            {
                id: 'edit_category',
                name: 'Category'
            },
            {
                id: 'edit_pp_original_amount',
                name: 'Actual Amount'
            },
            {
                id: 'edit_frequency',
                name: 'Frequency'
            },
            {
                id: 'edit_due_day',
                name: 'Due Day'
            },
            {
                id: 'edit_reminder_days',
                name: 'Reminder Days'
            }
            ];

            requiredFields.forEach(field => {
                const element = document.getElementById(field.id);
                if (!element || !element.value.trim()) {
                    errors.push(`${field.name} is required`);
                }
            });

            // Amount validation
            const amount = parseFloat(document.getElementById('edit_pp_original_amount').value);
            if (amount <= 0) {
                errors.push('Amount must be greater than 0');
            }

            // Mobile number validation
            const mobile = document.getElementById('edit_mobile_number').value;
            if (mobile && !/^\d{10}$/.test(mobile)) {
                errors.push('Mobile number must be 10 digits');
            }

            // Due day validation
            const dueDay = parseInt(document.getElementById('edit_due_day').value);
            if (dueDay < 1 || dueDay > 31) {
                errors.push('Due day must be between 1 and 31');
            }

            // Display errors if any
            if (errors.length > 0) {
                errorContainer.innerHTML = `
                                                                                        <strong>Please fix the following errors:</strong>
                                                                                        <ul style="margin: 5px 0 0 0; padding-left: 20px;">
                                                                                            ${errors.map(error => `<li>${error}</li>`).join('')}
                                                                                        </ul>
                                                                                    `;
                errorContainer.style.display = 'block';

                // Scroll to errors
                errorContainer.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                return false;
            }

            return true;
        }

        // Modal functions
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
            document.getElementById('editFormContainer').innerHTML = '';
        }

        // Close modal when clicking outside
        window.onclick = function (event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                closeEditModal();
            }
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeEditModal();
            }
        });

        function filterTemplates() {
            const direction = document.getElementById('directionFilter').value.toLowerCase();
            const company = document.getElementById('companyFilter').value;
            const rows = document.querySelectorAll('#templatesTableBody tr');

            rows.forEach(row => {
                const rowDirection = row.dataset.direction?.toLowerCase() || '';
                const rowCompany = row.dataset.company || '';

                const matchDirection = !direction || rowDirection.includes(direction);
                const matchCompany = !company || rowCompany === company;

                row.style.display = (matchDirection && matchCompany) ? '' : 'none';
            });
        }

        function resetForm() {
            // Get the form
            const form = document.getElementById('templateForm');
            if (!form) return;

            // Reset form values
            form.reset();

            // Reset hidden ID
            const templateIdInput = document.getElementById('template_id');
            if (templateIdInput) {
                templateIdInput.value = '';
            }

            // Reset form action to create
            form.action = '{{route('admin.standard-expenses.store')}}';

            // Remove method spoofing if exists
            const methodInput = form.querySelector('input[name="_method"]');
            if (methodInput) {
                methodInput.remove();
            }

            // Set default values
            const defaultValues = {
                'gst_percentage': 18,
                'tds_percentage': 10,
                'frequency': 'monthly',
                'due_day': 5,
                'reminder_days': 3,
                'status': '1'
            };

            Object.keys(defaultValues).forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.value = defaultValues[id];
                }
            });

            // Set checkbox defaults
            const applyGST = document.getElementById('applyGST');
            const applyTDS = document.getElementById('applyTDS');
            if (applyGST) applyGST.checked = true;
            if (applyTDS) applyTDS.checked = false;

            // Reset amount field to default
            const amountInput = document.getElementById('pp_original_amount');
            if (amountInput) {
                amountInput.value = 0;
            }

            // Reset category dropdowns
            const categoryType = document.getElementById('category_type');
            const category = document.getElementById('category');

            if (categoryType) {
                categoryType.value = '';
            }
            if (category) {
                category.innerHTML = '<option value="">Select Category</option>';
                category.disabled = true;
            }

            // Update form mode indicator
            const modeIndicator = document.getElementById('form-mode-indicator');
            if (modeIndicator) {
                modeIndicator.textContent = '(Add Mode)';
                modeIndicator.style.background = '#e0f2fe';
                modeIndicator.style.color = '#0c4a6e';
            }

            // Update submit button text
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.textContent = 'Save Expense';
            }

            // Clear validation errors
            resetErrorStates();

            // Hide edit success message
            const successMsg = document.getElementById('editSuccessMessage');
            if (successMsg) {
                successMsg.style.display = 'none';
            }

            // Calculate tax
            setTimeout(calculateTax, 100);

            // Focus on first field
            const expenseName = document.getElementById('expense_name');
            if (expenseName) {
                expenseName.focus();
            }
        }

        function cancelEdit() {
            const templateId = document.getElementById('template_id').value;
            if (templateId) {
                if (confirm('Are you sure you want to cancel editing? Your changes will be lost.')) {
                    resetForm();
                    switchTab('table-tab');
                }
            } else {
                resetForm();
                switchTab('table-tab');
            }
        }
    </script>
    <style>
        /* Additional styles for better alignment */
        .card-body {
            padding: 1.25rem !important;
        }

        .form-label.small {
            font-size: 0.875rem;
            font-weight: 500;
            color: #495057;
            display: block;
            margin-bottom: 0.25rem;
        }

        .input-group-sm {
            border-radius: 0.375rem;
        }

        .form-select {
            color: #000 !important;
            background-color: #fff !important;
        }

        .input-group-sm .form-control,
        .input-group-sm .form-select {
            border-radius: 0 0.375rem 0.375rem 0 !important;
            font-size: 0.875rem !important;
            padding: 0.25rem 0.5rem !important;
            height: auto !important;
            min-height: 31px !important;
            line-height: 1.5 !important;
        }

        .input-group-sm .input-group-text {
            border-radius: 0.375rem 0 0 0.375rem !important;
            font-size: 0.875rem;
            padding: 0.25rem 0.5rem;
            background-color: #f8f9fa;
            border-color: #dee2e6;
            color: #6c757d;
        }

        .input-group-sm .form-control:focus,
        .input-group-sm .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
            z-index: 3;
        }

        .btn-group {
            display: flex;
            gap: 0.5rem;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 0.375rem;
        }

        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }

        .btn-outline-secondary {
            color: #6c757d;
            border-color: #6c757d;
        }

        .btn-outline-secondary:hover {
            background-color: #6c757d;
            color: white;
            border-color: #6c757d;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {

            .col-md-3,
            .col-md-2,
            .col-md-3 {
                margin-bottom: 0.75rem;
            }

            .d-flex.gap-2 {
                flex-direction: column;
            }

            .d-flex.gap-2 .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }

            .d-flex.gap-2 .btn:last-child {
                margin-bottom: 0;
            }
        }

        @media (min-width: 769px) and (max-width: 991px) {
            .col-md-3 {
                width: 50%;
            }

            .col-md-2 {
                width: 50%;
            }
        }
    </style>
    <style>
        /* Add this to your existing CSS */
        #editSuccessMessage {
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    <style>
        /* Modal Styles */
        .modal {
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            animation: slideIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Edit button styling */
        .btn-edit {
            padding: 6px 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-edit:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-edit:active {
            transform: translateY(0);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .btn-edit.loading {
            background: #94a3b8;
            cursor: not-allowed;
        }
    </style>
    <style>
        /* Tab Styles */
        .tabs-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
        }

        .tabs-header {
            display: flex;
            border-bottom: 1px solid #e5e7eb;
        }

        .tab-button {
            padding: 12px 24px;
            background: none;
            border: none;
            font-size: 14px;
            font-weight: 500;
            color: #6b7280;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            position: relative;
        }

        .tab-button:hover {
            color: #2563eb;
            background: #f8fafc;
        }

        .tab-button.active {
            color: #2563eb;
            font-weight: 600;
            border-bottom: 2px solid #2563eb;
            background: linear-gradient(to bottom, #f0f7ff, #ffffff);
        }

        .tab-button i {
            font-size: 16px;
        }

        .tab-badge {
            background: #2563eb;
            color: white;
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: 600;
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Form Mode Indicator */
        #form-mode-indicator {
            background: #e0f2fe;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .tabs-header {
                flex-direction: column;
            }

            .tab-button {
                justify-content: center;
                border-bottom: 1px solid #e5e7eb;
            }

            .tab-button.active {
                border-bottom: 2px solid #2563eb;
            }
        }
    </style>
@endsection