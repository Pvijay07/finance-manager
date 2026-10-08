@extends('Admin.layouts.app')
@section('content')
<!-- Standard Templates Page -->
<div id="standard-templates" class="page">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            @if(($mainTab ?? '') === 'standard')
            <h1 style="font-weight: 800; color: #0f172a; font-size: 1.85rem; letter-spacing: -0.5px; margin: 0;">Standard Expenses</h1>
            <p class="text-muted small mb-0 mt-1">Manage and configure corporate recurring standard expenditure templates.</p>
            @elseif(($mainTab ?? '') === 'non-standard')
            <h1 style="font-weight: 800; color: #0f172a; font-size: 1.85rem; letter-spacing: -0.5px; margin: 0;">Non-Standard Expenses</h1>
            <p class="text-muted small mb-0 mt-1">Create and track non-standard expenditures, receipts, and payment settlements.</p>
            @else
            <h1 style="font-weight: 800; color: #0f172a; font-size: 1.85rem; letter-spacing: -0.5px; margin: 0;">Expenses</h1>
            <p class="text-muted small mb-0 mt-1">Unified view of all corporate standard and non-standard expenditures.</p>
            @endif
        </div>
        <div class="d-flex gap-2">
            @if(($mainTab ?? '') === 'standard')
            <button type="button" class="btn btn-sm btn-primary" onclick="switchTab('form-tab')" style="border-radius: 8px; font-weight: 600; padding: 8px 16px; background-color: #4f46e5; border-color: #4f46e5;">
                <i class="fas fa-plus-circle me-1"></i> Add Standard Expense
            </button>
            @elseif(($mainTab ?? '') === 'non-standard')
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addNonStandardModal" style="border-radius: 8px; font-weight: 600; padding: 8px 16px; background-color: #4f46e5; border-color: #4f46e5;">
                <i class="fas fa-receipt me-1"></i> Add Non-Standard Expense
            </button>
            @else
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addNonStandardModal" style="border-radius: 8px; font-weight: 600; padding: 8px 16px; background-color: #4f46e5; border-color: #4f46e5;">
                <i class="fas fa-receipt me-1"></i> Add Non-Standard Expense
            </button>
            @endif
        </div>
    </div>

    @if(($mainTab ?? 'expenses') === 'expenses')
    <!-- All Expenses Section (Show all Standard and Non-Standard Expenses in one place) -->
    <div id="all-expenses-section">
        <!-- Summary Cards -->
        @if(isset($cardStats))
            @include('Admin.partials.summary_cards', ['cardType' => 'expense'])
        @endif

        <!-- Status Tabs for All Expenses -->
        <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
            <button type="button"
                class="btn btn-sm py-2 px-3 status-tab-btn {{ ($statusFilter ?? 'all') == 'all' ? 'active shadow-sm text-white' : 'btn-light border text-muted' }}"
                onclick="setAllExpensesStatusFilter('all')"
                style="border-radius: 8px; font-weight: 600; font-size: 0.85rem; {{ ($statusFilter ?? 'all') == 'all' ? 'background-color: #4f46e5; border-color: #4f46e5;' : 'background-color: #ffffff;' }}">
                <i class="fas fa-list-ul me-1"></i> All Expenses
                <span class="badge ms-1 {{ ($statusFilter ?? 'all') == 'all' ? 'bg-white text-dark' : 'bg-secondary text-white' }}">{{ $allStatusCounts['all'] ?? 0 }}</span>
            </button>
            <button type="button"
                class="btn btn-sm py-2 px-3 status-tab-btn {{ ($statusFilter ?? '') == 'pending' ? 'active shadow-sm text-white' : 'btn-light border text-muted' }}"
                onclick="setAllExpensesStatusFilter('pending')"
                style="border-radius: 8px; font-weight: 600; font-size: 0.85rem; {{ ($statusFilter ?? '') == 'pending' ? 'background-color: #f59e0b; border-color: #f59e0b;' : 'background-color: #ffffff;' }}">
                <i class="fas fa-clock me-1"></i> Pending
                <span class="badge ms-1 {{ ($statusFilter ?? '') == 'pending' ? 'bg-white text-dark' : 'bg-warning text-dark' }}">{{ $allStatusCounts['pending'] ?? 0 }}</span>
            </button>
            <button type="button"
                class="btn btn-sm py-2 px-3 status-tab-btn {{ ($statusFilter ?? '') == 'upcoming' ? 'active shadow-sm text-white' : 'btn-light border text-muted' }}"
                onclick="setAllExpensesStatusFilter('upcoming')"
                style="border-radius: 8px; font-weight: 600; font-size: 0.85rem; {{ ($statusFilter ?? '') == 'upcoming' ? 'background-color: #3b82f6; border-color: #3b82f6;' : 'background-color: #ffffff;' }}">
                <i class="fas fa-calendar-check me-1"></i> Upcoming
                <span class="badge ms-1 {{ ($statusFilter ?? '') == 'upcoming' ? 'bg-white text-primary' : 'bg-info text-white' }}">{{ $allStatusCounts['upcoming'] ?? 0 }}</span>
            </button>
            <button type="button"
                class="btn btn-sm py-2 px-3 status-tab-btn {{ ($statusFilter ?? '') == 'paid' ? 'active shadow-sm text-white' : 'btn-light border text-muted' }}"
                onclick="setAllExpensesStatusFilter('paid')"
                style="border-radius: 8px; font-weight: 600; font-size: 0.85rem; {{ ($statusFilter ?? '') == 'paid' ? 'background-color: #10b981; border-color: #10b981;' : 'background-color: #ffffff;' }}">
                <i class="fas fa-check-circle me-1"></i> Paid
                <span class="badge ms-1 {{ ($statusFilter ?? '') == 'paid' ? 'bg-white text-success' : 'bg-success text-white' }}">{{ $allStatusCounts['paid'] ?? 0 }}</span>
            </button>
        </div>

        <!-- Filter Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form id="allExpensesFilterForm" method="GET" action="{{ route('admin.standard-expenses') }}" class="row g-3 align-items-end">
                    <input type="hidden" name="tab" value="expenses">
                    <input type="hidden" name="status" id="allExpenseStatusInput" value="{{ $statusFilter ?? 'all' }}">

                    <!-- Search Field -->
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small mb-1">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" name="search" value="{{ $search }}" placeholder="Search expense, vendor...">
                        </div>
                    </div>

                    <!-- Company Filter -->
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label small mb-1">Company</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fas fa-building"></i></span>
                            <select class="form-select" name="company_id" onchange="this.form.submit()">
                                <option value="all" {{ ($companyFilter == 'all' || !$companyFilter) ? 'selected' : '' }}>All Companies</option>
                                @foreach ($companies as $company)
                                <option value="{{ $company->id }}" {{ $companyFilter == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Type Filter (Standard vs Non-Standard) -->
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label small mb-1">Expense Type</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fas fa-filter"></i></span>
                            <select class="form-select" name="type" onchange="this.form.submit()">
                                <option value="all" {{ ($typeFilter == 'all' || !$typeFilter) ? 'selected' : '' }}>All Types</option>
                                <option value="standard" {{ $typeFilter == 'standard' ? 'selected' : '' }}>Standard (All)</option>
                                <option value="standard_fixed" {{ ($typeFilter == 'standard_fixed' || $typeFilter == 'fixed') ? 'selected' : '' }}>Standard Fixed</option>
                                <option value="standard_editable" {{ ($typeFilter == 'standard_editable' || $typeFilter == 'editable') ? 'selected' : '' }}>Standard Editable</option>
                                <option value="non-standard" {{ $typeFilter == 'non-standard' ? 'selected' : '' }}>Non-Standard Expenses</option>
                            </select>
                        </div>
                    </div>

                    <!-- Category Filter -->
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label small mb-1">Category</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fas fa-folder"></i></span>
                            <select class="form-select" name="category_type" onchange="this.form.submit()">
                                <option value="all" {{ ($categoryFilter == 'all' || !$categoryFilter) ? 'selected' : '' }}>All Categories</option>
                                @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $categoryFilter == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Date Range Filter -->
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label small mb-1">Date Range</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            <select class="form-select" name="date_range" id="allExpDateRange" onchange="handleAllExpDateRangeChange(this.value)">
                                <option value="all" {{ ($dateRange == 'all' || !$dateRange) ? 'selected' : '' }}>All Dates</option>
                                <option value="today" {{ $dateRange == 'today' ? 'selected' : '' }}>Today</option>
                                <option value="week" {{ $dateRange == 'week' ? 'selected' : '' }}>This Week</option>
                                <option value="month" {{ $dateRange == 'month' ? 'selected' : '' }}>This Month</option>
                                <option value="quarter" {{ $dateRange == 'quarter' ? 'selected' : '' }}>This Quarter</option>
                                <option value="year" {{ $dateRange == 'year' ? 'selected' : '' }}>This Year</option>
                                <option value="custom" {{ $dateRange == 'custom' ? 'selected' : '' }}>Custom Range</option>
                            </select>
                        </div>
                    </div>

                    <!-- Per Page -->
                    <div class="col-md-1 col-sm-6">
                        <label class="form-label small mb-1">Per Page</label>
                        <div class="input-group input-group-sm">
                            <select class="form-select" name="per_page" onchange="this.form.submit()">
                                <option value="10" {{ ($perPage == 10 || !$perPage) ? 'selected' : '' }}>10</option>
                                <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                    </div>

                    <!-- Custom Date Fields -->
                    <div class="col-12 {{ $dateRange == 'custom' ? '' : 'd-none' }}" id="allExpCustomDateContainer">
                        <div class="row g-2 pt-2 border-top">
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Start Date</label>
                                <input type="date" class="form-control form-control-sm" name="start_date" value="{{ $startDate }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1">End Date</label>
                                <input type="date" class="form-control form-control-sm" name="end_date" value="{{ $endDate }}">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-sm btn-primary w-100">Apply Date</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- All Expenses Table -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-bold" style="color: #0f172a; font-size: 1.05rem;">
                        <i class="fas fa-layer-group text-primary me-2"></i>Expenses (All Standard & Non-Standard)
                    </h5>
                    <p class="text-muted small mb-0 mt-1">Unified view of all corporate expenditures</p>
                </div>
                <div class="text-muted small">
                    Showing <strong>{{ $allExpenses->count() }}</strong> of <strong>{{ $allExpenses->total() }}</strong> records
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                        <thead class="table-light text-uppercase text-secondary" style="font-size: 11px; letter-spacing: 0.5px;">
                            <tr>
                                <th class="ps-4">Due Date</th>
                                <th>Expense Name & Ref</th>
                                <th>Type</th>
                                <th>Company</th>
                                <th>Category</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Paid</th>
                                <th class="text-end">Balance</th>
                                <th>Mode</th>
                                <th class="text-center">Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allExpenses as $exp)
                            <tr>
                                <td class="ps-4 text-nowrap">
                                    <div class="fw-semibold text-dark">{{ $exp->due_date ? \Carbon\Carbon::parse($exp->due_date)->format('d M Y') : 'N/A' }}</div>
                                    @if($exp->payment_date)
                                    <div class="text-muted text-xs">Paid: {{ \Carbon\Carbon::parse($exp->payment_date)->format('d M Y') }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $exp->expense_name ?: $exp->name }}</div>
                                    <div class="text-muted text-xs">Ref: {{ $exp->expense_number ?? 'EXP-'.$exp->id }}</div>
                                    @if($exp->source !== 'standard' && $exp->creator)
                                    <div class="text-xs text-secondary mt-1">
                                        <i class="fas fa-user-circle me-1 text-muted"></i>{{ $exp->creator->name }}
                                        <span class="badge bg-light text-secondary border text-capitalize py-0 px-1" style="font-size: 10px;">
                                            {{ str_replace('_', ' ', $exp->creator->role ?? 'User') }}
                                        </span>
                                    </div>
                                    @endif
                                </td>
                                <td>
                                    @if($exp->source === 'standard')
                                    @php
                                    $catType = $exp->categoryRelation->category_type ?? $exp->sub_type ?? '';
                                    @endphp
                                    @if($catType === 'standard_fixed' || $catType === 'fixed')
                                    <span class="badge rounded-pill px-2.5 py-1 text-xs fw-semibold" style="background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;">
                                        <i class="fas fa-lock me-1"></i>Standard Fixed
                                    </span>
                                    @elseif($catType === 'standard_editable' || $catType === 'editable')
                                    <span class="badge rounded-pill px-2.5 py-1 text-xs fw-semibold" style="background-color: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe;">
                                        <i class="fas fa-pen-to-square me-1"></i>Standard Editable
                                    </span>
                                    @else
                                    <span class="badge rounded-pill px-2.5 py-1 text-xs fw-semibold" style="background-color: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe;">
                                        <i class="fas fa-calendar-alt me-1"></i>Standard
                                    </span>
                                    @endif
                                    @else
                                    <span class="badge rounded-pill px-2.5 py-1 text-xs fw-semibold" style="background-color: #f3e8ff; color: #6b21a8; border: 1px solid #e9d5ff;">
                                        <i class="fas fa-receipt me-1"></i>Non-Standard
                                    </span>
                                    @endif
                                </td>
                                <td class="text-dark">{{ $exp->company->name ?? 'All Companies' }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1">
                                        {{ $exp->categoryRelation->name ?? $exp->category_name ?? 'General' }}
                                    </span>
                                </td>
                                <td class="text-end fw-bold text-dark text-nowrap">
                                    ₹{{ number_format($exp->schedule_amount ?: ($exp->planned_amount ?: $exp->actual_amount), 2) }}
                                </td>
                                <td class="text-end text-success fw-semibold text-nowrap">
                                    ₹{{ number_format($exp->paid_amount ?: 0, 2) }}
                                </td>
                                <td class="text-end {{ ($exp->balance_amount > 0) ? 'text-danger fw-bold' : 'text-muted' }} text-nowrap">
                                    ₹{{ number_format($exp->balance_amount ?: 0, 2) }}
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border text-capitalize">
                                        {{ str_replace('_', ' ', $exp->payment_mode ?? 'cash') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @php
                                    $s = strtolower($exp->status ?? 'pending');
                                    $badgeBg = '#64748b';
                                    if (in_array($s, ['paid', 'settle', 'settled'])) {
                                    $badgeBg = '#10b981';
                                    } elseif ($s === 'upcoming') {
                                    $badgeBg = '#3b82f6';
                                    } elseif ($s === 'pending' || $s === 'due') {
                                    $badgeBg = '#f59e0b';
                                    }
                                    @endphp
                                    <span class="badge text-white px-2.5 py-1 rounded-pill text-xs text-capitalize" style="background-color: {{ $badgeBg }};">
                                        {{ $s }}
                                    </span>
                                </td>
                                <td class="text-end pe-4 text-nowrap">
                                    <div class="btn-group btn-group-sm">
                                        @if(!in_array($exp->status, ['paid', 'settle', 'settled']))
                                        <button type="button" class="btn btn-outline-success btn-sm" onclick="markAllExpenseAsPaid({{ $exp->id }}, '{{ addslashes($exp->expense_name ?: $exp->name) }}', {{ $exp->balance_amount > 0 ? $exp->balance_amount : ($exp->schedule_amount ?: $exp->planned_amount) }}, '{{ $exp->source }}')" title="Mark as Paid">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        @if($exp->source === 'standard')
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="editTemplate({{ $exp->id }})" title="Edit Standard Expense">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @else
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="editNonStandardExpense({{ $exp->id }})" title="Edit Non-Standard Expense">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @endif
                                        @endif
                                        @if($exp->source !== 'standard')
                                        <form action="{{ route('admin.standard-expenses.non-standard.destroy', $exp->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this non-standard expense record?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 text-secondary opacity-50"></i>
                                    <p class="mb-0">No corporate expenses found matching the selected filters.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($allExpenses->hasPages())
                <div class="p-3 border-top d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Showing {{ $allExpenses->firstItem() }} to {{ $allExpenses->lastItem() }} of {{ $allExpenses->total() }} entries
                    </div>
                    <div>
                        {{ $allExpenses->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @elseif(($mainTab ?? '') === 'standard')
    <!-- Tab Navigation for Standard Expenses (Add Form vs List) -->
    <div class="tabs-container" style="margin-bottom: 20px;">
        <div class="tabs-header">
            <button class="tab-button" data-tab="form-tab" onclick="switchTab('form-tab')">
                <i class="fas fa-plus-circle"></i> Add Expense
            </button>
            <button class="tab-button active" data-tab="table-tab" onclick="switchTab('table-tab')">
                <i class="fas fa-list"></i> Expense List
                <span class="tab-badge">{{ $expenseTypes->total() }}</span>
            </button>
        </div>
    </div>

    <!-- Form Tab -->
    <div id="form-tab" class="tab-content">
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
        <!-- Summary Cards -->
        @if(isset($cardStats))
            @include('Admin.partials.summary_cards', ['cardType' => 'expense'])
        @endif

        <!-- Status Tabs -->
        <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
            <button type="button"
                class="btn btn-sm py-2 px-3 status-tab-btn {{ ($statusFilter ?? 'all') == 'all' ? 'active shadow-sm text-white' : 'btn-light border text-muted' }}"
                onclick="setStatusFilter('all')"
                style="border-radius: 8px; font-weight: 600; font-size: 0.85rem; {{ ($statusFilter ?? 'all') == 'all' ? 'background-color: #4f46e5; border-color: #4f46e5;' : 'background-color: #ffffff;' }}">
                <i class="fas fa-list-ul me-1"></i> All Expenses
                <span class="badge ms-1 {{ ($statusFilter ?? 'all') == 'all' ? 'bg-white text-dark' : 'bg-secondary text-white' }}">{{ $statusCounts['all'] ?? 0 }}</span>
            </button>
            <button type="button"
                class="btn btn-sm py-2 px-3 status-tab-btn {{ ($statusFilter ?? '') == 'pending' ? 'active shadow-sm text-white' : 'btn-light border text-muted' }}"
                onclick="setStatusFilter('pending')"
                style="border-radius: 8px; font-weight: 600; font-size: 0.85rem; {{ ($statusFilter ?? '') == 'pending' ? 'background-color: #f59e0b; border-color: #f59e0b;' : 'background-color: #ffffff;' }}">
                <i class="fas fa-clock me-1"></i> Pending
                <span class="badge ms-1 {{ ($statusFilter ?? '') == 'pending' ? 'bg-white text-dark' : 'bg-warning text-dark' }}">{{ $statusCounts['pending'] ?? 0 }}</span>
            </button>
            <button type="button"
                class="btn btn-sm py-2 px-3 status-tab-btn {{ ($statusFilter ?? '') == 'upcoming' ? 'active shadow-sm text-white' : 'btn-light border text-muted' }}"
                onclick="setStatusFilter('upcoming')"
                style="border-radius: 8px; font-weight: 600; font-size: 0.85rem; {{ ($statusFilter ?? '') == 'upcoming' ? 'background-color: #3b82f6; border-color: #3b82f6;' : 'background-color: #ffffff;' }}">
                <i class="fas fa-calendar-check me-1"></i> Upcoming
                <span class="badge ms-1 {{ ($statusFilter ?? '') == 'upcoming' ? 'bg-white text-primary' : 'bg-info text-white' }}">{{ $statusCounts['upcoming'] ?? 0 }}</span>
            </button>
            <button type="button"
                class="btn btn-sm py-2 px-3 status-tab-btn {{ ($statusFilter ?? '') == 'paid' ? 'active shadow-sm text-white' : 'btn-light border text-muted' }}"
                onclick="setStatusFilter('paid')"
                style="border-radius: 8px; font-weight: 600; font-size: 0.85rem; {{ ($statusFilter ?? '') == 'paid' ? 'background-color: #10b981; border-color: #10b981;' : 'background-color: #ffffff;' }}">
                <i class="fas fa-check-circle me-1"></i> Paid
                <span class="badge ms-1 {{ ($statusFilter ?? '') == 'paid' ? 'bg-white text-success' : 'bg-success text-white' }}">{{ $statusCounts['paid'] ?? 0 }}</span>
            </button>
        </div>

        <!-- Filter Section -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form id="filterForm" method="GET" action="{{ route('admin.standard-expenses') }}"
                    class="row g-3 align-items-end">
                    <input type="hidden" name="status" id="expenseStatusInput" value="{{ $statusFilter ?? 'all' }}">
                    <input type="hidden" name="tab" value="table-tab">

                    <!-- Search Field -->
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small mb-1">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" name="search" value="{{ $search }}"
                                placeholder="Search expense, party...">
                        </div>
                    </div>

                    <!-- Company Filter -->
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label small mb-1">Company</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fas fa-building"></i></span>
                            <select class="form-select" name="company_id" onchange="this.form.submit()">
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
                            <select class="form-select" name="category_type" onchange="this.form.submit()">
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

                    <!-- Date Range Filter -->
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label small mb-1">Date Range</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            <select class="form-select" name="date_range" id="expenseDateRange" onchange="handleDateRangeChange(this.value)">
                                <option value="all" {{ ($dateRange == 'all' || !$dateRange) ? 'selected' : '' }}>All Dates</option>
                                <option value="today" {{ $dateRange == 'today' ? 'selected' : '' }}>Today</option>
                                <option value="week" {{ $dateRange == 'week' ? 'selected' : '' }}>This Week</option>
                                <option value="month" {{ $dateRange == 'month' ? 'selected' : '' }}>This Month</option>
                                <option value="quarter" {{ $dateRange == 'quarter' ? 'selected' : '' }}>This Quarter</option>
                                <option value="year" {{ $dateRange == 'year' ? 'selected' : '' }}>This Year</option>
                                <option value="custom" {{ $dateRange == 'custom' ? 'selected' : '' }}>Custom Range</option>
                            </select>
                        </div>
                    </div>

                    <!-- Items Per Page -->
                    <div class="col-md-1 col-sm-6">
                        <label class="form-label small mb-1">Per Page</label>
                        <div class="input-group input-group-sm">
                            <select class="form-select" name="per_page" onchange="this.form.submit()">
                                <option value="10" {{ ($perPage == 10 || !$perPage) ? 'selected' : '' }}>10</option>
                                <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="col-md-2 col-sm-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill" style="background-color: #4f46e5; border-color: #4f46e5;">
                                <i class="fas fa-search me-1"></i> Filter
                            </button>
                            <a href="{{ route('admin.standard-expenses', ['tab' => 'table-tab']) }}"
                                class="btn btn-outline-secondary btn-sm flex-fill">
                                <i class="fas fa-redo me-1"></i> Reset
                            </a>
                        </div>
                    </div>

                    <!-- Custom Date Range Row (Shown when Custom Range is selected) -->
                    <div class="col-12 mt-2" id="customDateRangeRow" style="display: {{ $dateRange == 'custom' ? 'block' : 'none' }};">
                        <div class="p-3 bg-light rounded border d-flex align-items-center gap-3 flex-wrap">
                            <span class="fw-semibold small text-muted"><i class="fas fa-calendar-day me-1"></i> Custom Range:</span>
                            <div class="d-flex align-items-center gap-2">
                                <label class="form-label small mb-0">From:</label>
                                <input type="date" class="form-control form-control-sm" name="start_date" id="expenseStartDate" value="{{ $startDate ?? '' }}" style="width: auto;">
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <label class="form-label small mb-0">To:</label>
                                <input type="date" class="form-control form-control-sm" name="end_date" id="expenseEndDate" value="{{ $endDate ?? '' }}" style="width: auto;">
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary" style="background-color: #4f46e5; border-color: #4f46e5;">Apply Dates</button>
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
                                @php
                                $statusColor = '#94a3b8'; // default gray
                                if (in_array($type->status, ['paid', 'settle', 'settled'])) {
                                $statusColor = '#10b981'; // green
                                } elseif ($type->status == 'upcoming') {
                                $statusColor = '#3b82f6'; // blue
                                } elseif ($type->status == 'pending') {
                                $statusColor = '#f59e0b'; // orange
                                }
                                @endphp
                                <span
                                    style="display: inline-block; padding: 4px 12px; background: {{ $statusColor }}; color: white; border-radius: 12px; font-size: 12px; font-weight: 500; text-transform: capitalize;">
                                    {{ $type->status }}
                                </span>
                            </td>
                            <td style="padding: 14px 16px; text-align: center;">
                                @if(strtolower($type->status ?? '') !== 'paid')
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
                                @if ($page==$current)
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
    @else
    <!-- Non-Standard Expenses Section -->
    <div id="non-standard-section">
        <!-- Summary Cards -->
        @if(isset($cardStats))
            @include('Admin.partials.summary_cards', ['cardType' => 'expense'])
        @endif

        <!-- Status Tabs -->
        <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
            <button type="button"
                class="btn btn-sm py-2 px-3 status-tab-btn {{ ($statusFilter ?? 'all') == 'all' ? 'active shadow-sm text-white' : 'btn-light border text-muted' }}"
                onclick="setNonStandardStatusFilter('all')"
                style="border-radius: 8px; font-weight: 600; font-size: 0.85rem; {{ ($statusFilter ?? 'all') == 'all' ? 'background-color: #4f46e5; border-color: #4f46e5;' : 'background-color: #ffffff;' }}">
                <i class="fas fa-list-ul me-1"></i> All Expenses
                <span class="badge ms-1 {{ ($statusFilter ?? 'all') == 'all' ? 'bg-white text-dark' : 'bg-secondary text-white' }}">{{ $nsStatusCounts['all'] ?? 0 }}</span>
            </button>
            <button type="button"
                class="btn btn-sm py-2 px-3 status-tab-btn {{ ($statusFilter ?? '') == 'pending' ? 'active shadow-sm text-white' : 'btn-light border text-muted' }}"
                onclick="setNonStandardStatusFilter('pending')"
                style="border-radius: 8px; font-weight: 600; font-size: 0.85rem; {{ ($statusFilter ?? '') == 'pending' ? 'background-color: #f59e0b; border-color: #f59e0b;' : 'background-color: #ffffff;' }}">
                <i class="fas fa-clock me-1"></i> Pending
                <span class="badge ms-1 {{ ($statusFilter ?? '') == 'pending' ? 'bg-white text-dark' : 'bg-warning text-dark' }}">{{ $nsStatusCounts['pending'] ?? 0 }}</span>
            </button>
            <button type="button"
                class="btn btn-sm py-2 px-3 status-tab-btn {{ ($statusFilter ?? '') == 'upcoming' ? 'active shadow-sm text-white' : 'btn-light border text-muted' }}"
                onclick="setNonStandardStatusFilter('upcoming')"
                style="border-radius: 8px; font-weight: 600; font-size: 0.85rem; {{ ($statusFilter ?? '') == 'upcoming' ? 'background-color: #3b82f6; border-color: #3b82f6;' : 'background-color: #ffffff;' }}">
                <i class="fas fa-calendar-check me-1"></i> Upcoming
                <span class="badge ms-1 {{ ($statusFilter ?? '') == 'upcoming' ? 'bg-white text-primary' : 'bg-info text-white' }}">{{ $nsStatusCounts['upcoming'] ?? 0 }}</span>
            </button>
            <button type="button"
                class="btn btn-sm py-2 px-3 status-tab-btn {{ ($statusFilter ?? '') == 'paid' ? 'active shadow-sm text-white' : 'btn-light border text-muted' }}"
                onclick="setNonStandardStatusFilter('paid')"
                style="border-radius: 8px; font-weight: 600; font-size: 0.85rem; {{ ($statusFilter ?? '') == 'paid' ? 'background-color: #10b981; border-color: #10b981;' : 'background-color: #ffffff;' }}">
                <i class="fas fa-check-circle me-1"></i> Paid
                <span class="badge ms-1 {{ ($statusFilter ?? '') == 'paid' ? 'bg-white text-success' : 'bg-success text-white' }}">{{ $nsStatusCounts['paid'] ?? 0 }}</span>
            </button>
        </div>

        <!-- Filter Section -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form id="nonStandardFilterForm" method="GET" action="{{ route('admin.standard-expenses') }}" class="row g-3 align-items-end">
                    <input type="hidden" name="tab" value="non-standard">
                    <input type="hidden" name="status" id="nsExpenseStatusInput" value="{{ $statusFilter ?? 'all' }}">

                    <!-- Search Field -->
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small mb-1">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" name="search" value="{{ $search }}" placeholder="Search expense, vendor...">
                        </div>
                    </div>

                    <!-- Company Filter -->
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label small mb-1">Company</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fas fa-building"></i></span>
                            <select class="form-select" name="company_id" onchange="this.form.submit()">
                                <option value="all" {{ ($companyFilter == 'all' || !$companyFilter) ? 'selected' : '' }}>All Companies</option>
                                @foreach ($companies as $company)
                                <option value="{{ $company->id }}" {{ $companyFilter == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Category Filter -->
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label small mb-1">Category</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fas fa-folder"></i></span>
                            <select class="form-select" name="category_type" onchange="this.form.submit()">
                                <option value="all" {{ ($categoryFilter == 'all' || !$categoryFilter) ? 'selected' : '' }}>All Categories</option>
                                @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $categoryFilter == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Date Range Filter -->
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label small mb-1">Date Range</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            <select class="form-select" name="date_range" id="nsDateRange" onchange="handleNsDateRangeChange(this.value)">
                                <option value="all" {{ ($dateRange == 'all' || !$dateRange) ? 'selected' : '' }}>All Dates</option>
                                <option value="today" {{ $dateRange == 'today' ? 'selected' : '' }}>Today</option>
                                <option value="week" {{ $dateRange == 'week' ? 'selected' : '' }}>This Week</option>
                                <option value="month" {{ $dateRange == 'month' ? 'selected' : '' }}>This Month</option>
                                <option value="quarter" {{ $dateRange == 'quarter' ? 'selected' : '' }}>This Quarter</option>
                                <option value="year" {{ $dateRange == 'year' ? 'selected' : '' }}>This Year</option>
                                <option value="custom" {{ $dateRange == 'custom' ? 'selected' : '' }}>Custom Range</option>
                            </select>
                        </div>
                    </div>

                    <!-- Items Per Page -->
                    <div class="col-md-1 col-sm-6">
                        <label class="form-label small mb-1">Per Page</label>
                        <div class="input-group input-group-sm">
                            <select class="form-select" name="per_page" onchange="this.form.submit()">
                                <option value="10" {{ ($perPage == 10 || !$perPage) ? 'selected' : '' }}>10</option>
                                <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="col-md-2 col-sm-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill" style="background-color: #4f46e5; border-color: #4f46e5;">
                                <i class="fas fa-search me-1"></i> Filter
                            </button>
                            <a href="{{ route('admin.standard-expenses', ['tab' => 'non-standard']) }}" class="btn btn-outline-secondary btn-sm flex-fill">
                                <i class="fas fa-redo me-1"></i> Reset
                            </a>
                        </div>
                    </div>

                    <!-- Custom Date Range Row -->
                    <div class="col-12 mt-2" id="nsCustomDateRangeRow" style="display: {{ $dateRange == 'custom' ? 'block' : 'none' }};">
                        <div class="p-3 bg-light rounded border d-flex align-items-center gap-3 flex-wrap">
                            <span class="fw-semibold small text-muted"><i class="fas fa-calendar-day me-1"></i> Custom Range:</span>
                            <div class="d-flex align-items-center gap-2">
                                <label class="form-label small mb-0">From:</label>
                                <input type="date" class="form-control form-control-sm" name="start_date" value="{{ $startDate ?? '' }}" style="width: auto;">
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <label class="form-label small mb-0">To:</label>
                                <input type="date" class="form-control form-control-sm" name="end_date" value="{{ $endDate ?? '' }}" style="width: auto;">
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary" style="background-color: #4f46e5; border-color: #4f46e5;">Apply</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Non-Standard Expenses Table -->
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa; border-bottom: 1px solid #e0e0e0;">
                            <th style="padding: 12px 16px; text-align: left; font-weight: 600; font-size: 13px; color: #1a1a1a;">Expense Name</th>
                            <th style="padding: 12px 16px; text-align: left; font-weight: 600; font-size: 13px; color: #1a1a1a;">Company</th>
                            <th style="padding: 12px 16px; text-align: left; font-weight: 600; font-size: 13px; color: #1a1a1a;">Party / Vendor</th>
                            <th style="padding: 12px 16px; text-align: left; font-weight: 600; font-size: 13px; color: #1a1a1a;">Category</th>
                            <th style="padding: 12px 16px; text-align: left; font-weight: 600; font-size: 13px; color: #1a1a1a;">Created By</th>
                            <th style="padding: 12px 16px; text-align: right; font-weight: 600; font-size: 13px; color: #1a1a1a;">Planned Amount</th>
                            <th style="padding: 12px 16px; text-align: right; font-weight: 600; font-size: 13px; color: #1a1a1a;">Paid Amount</th>
                            <th style="padding: 12px 16px; text-align: center; font-weight: 600; font-size: 13px; color: #1a1a1a;">Due Date</th>
                            <th style="padding: 12px 16px; text-align: center; font-weight: 600; font-size: 13px; color: #1a1a1a;">Status</th>
                            <th style="padding: 12px 16px; text-align: center; font-weight: 600; font-size: 13px; color: #1a1a1a;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($nonStandardExpenses as $nsExpense)
                        <tr style="border-bottom: 1px solid #f0f0f0;">
                            <td style="padding: 12px 16px;">
                                <div class="fw-semibold text-dark">{{ $nsExpense->expense_name ?? $nsExpense->name ?? 'Expense' }}</div>
                                @if($nsExpense->purpose_comment)
                                <div class="text-muted small text-truncate" style="max-width: 200px;">{{ $nsExpense->purpose_comment }}</div>
                                @endif
                            </td>
                            <td style="padding: 12px 16px;">
                                <span class="badge bg-light text-dark border">{{ $nsExpense->company->name ?? 'N/A' }}</span>
                            </td>
                            <td style="padding: 12px 16px; font-size: 13px;">
                                {{ $nsExpense->party_name ?: '—' }}
                            </td>
                            <td style="padding: 12px 16px;">
                                <span class="badge bg-secondary-subtle text-secondary" style="font-size: 11px;">
                                    {{ $nsExpense->categoryRelation->name ?? $nsExpense->category ?? 'General' }}
                                </span>
                            </td>
                            <td style="padding: 12px 16px; font-size: 13px;">
                                @if($nsExpense->creator)
                                <div class="fw-semibold text-dark">{{ $nsExpense->creator->name }}</div>
                                <span class="badge bg-light text-secondary border text-capitalize" style="font-size: 11px;">
                                    {{ str_replace('_', ' ', $nsExpense->creator->role ?? 'User') }}
                                </span>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td style="padding: 12px 16px; text-align: right; font-weight: 600; color: #1e293b;">
                                ₹ {{ number_format($nsExpense->planned_amount, 2) }}
                            </td>
                            <td style="padding: 12px 16px; text-align: right; font-weight: 600; color: #10b981;">
                                ₹ {{ number_format($nsExpense->actual_amount ?? $nsExpense->paid_amount ?? 0, 2) }}
                            </td>
                            <td style="padding: 12px 16px; text-align: center; font-size: 13px;">
                                {{ $nsExpense->due_date ? \Carbon\Carbon::parse($nsExpense->due_date)->format('d M Y') : '—' }}
                            </td>
                            <td style="padding: 12px 16px; text-align: center;">
                                @if(in_array($nsExpense->status, ['paid', 'settle', 'settled']))
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Paid</span>
                                @elseif($nsExpense->status === 'pending')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">Pending</span>
                                @else
                                <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">{{ ucfirst($nsExpense->status) }}</span>
                                @endif
                            </td>
                            <td style="padding: 12px 16px; text-align: center;">
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    @if(!in_array($nsExpense->status, ['paid', 'settle', 'settled']))
                                    <form action="{{ route('admin.standard-expenses.non-standard.mark-paid', $nsExpense->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success py-1 px-2" title="Mark Paid" onclick="return confirm('Mark this expense as paid?')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2" title="Edit" onclick="editNonStandardExpense({{ $nsExpense->id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @endif
                                    <form action="{{ route('admin.standard-expenses.non-standard.destroy', $nsExpense->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" title="Delete" onclick="return confirm('Delete this non-standard expense?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" style="padding: 40px; text-align: center; color: #94a3b8;">
                                <i class="fas fa-receipt mb-2" style="font-size: 36px; color: #cbd5e1; display: block;"></i>
                                No non-standard expenses found matching your filter criteria.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Non-Standard Pagination -->
            @if ($nonStandardExpenses->hasPages())
            <div class="p-3 border-top">
                {{ $nonStandardExpenses->links() }}
            </div>
            @endif
        </div>
    </div>
    @endif
</div>

<!-- Add Non-standard Expense Modal (Same as Manager Panel) -->
<div class="modal fade" id="addNonStandardModal" tabindex="-1" aria-labelledby="addNonStandardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addNonStandardModalLabel">Add Non-standard Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addNonStandardForm" action="{{ route('admin.standard-expenses.non-standard.store') }}" method="POST"
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
                                step="0.01" required value="" placeholder="0.00">
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
                                    <label class="form-check-label fw-bold text-uppercase small text-muted" for="apply_gst">GST</label>
                                </div>
                            </div>
                            <div class="col">
                                <label class="form-label fw-bold text-uppercase small text-muted">GST %</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="gst_percentage" name="gst_percentage"
                                        value="18" min="0" max="100" step="0.01">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col">
                                <label class="form-label fw-bold text-uppercase small text-muted">GST Amount</label>
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
                                    <label class="form-check-label fw-bold text-uppercase small text-muted" for="apply_tds">TDS</label>
                                </div>
                            </div>
                            <div class="col">
                                <label class="form-label fw-bold text-uppercase small text-muted">TDS %</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="tds_percentage" name="tds_percentage"
                                        value="10" min="0" max="100" step="0.01">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col">
                                <label class="form-label fw-bold text-uppercase small text-muted">TDS Amount</label>
                                <input type="number" class="form-control" id="tds_amount" name="tds_amount" value="0.00"
                                    readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Amount After TDS & TDS Details -->
                    <div class="row g-3 mb-4">
                        <input type="hidden" class="form-control" id="amount_after_tds" name="amount_after_tds"
                            value="0.00" readonly>
                        <div class="col-md-4 tds-status-field">
                            <label class="form-label fw-bold text-uppercase small text-muted">TDS Status</label>
                            <select class="form-select" id="addTdsStatus" name="tds_status">
                                <option value="" selected disabled>Select Status</option>
                                <option value="received">Paid</option>
                                <option value="not_received">Not Paid</option>
                            </select>
                        </div>
                        <div class="col-md-4 tds-receipt-field">
                            <label class="form-label fw-bold text-uppercase small text-muted">TDS Certificate/Receipt</label>
                            <input type="file" id="addTdsReceipt" name="tds_receipt" class="form-control"
                                accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                        </div>
                    </div>

                    <input type="hidden" class="form-control" id="grand_total" name="grand_total" value="0.00" readonly>

                    <div class="section-divider"></div>

                    <!-- Schedule, Paid, Balance -->
                    <div class="row g-3 mb-3">
                        <input type="hidden" class="form-control" id="schedule_amount" name="planned_amount" step="0.01"
                            value="0.00">
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
                            <input type="date" class="form-control" name="payment_date" id="payment_date" max="{{ date('Y-m-d') }}">
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
                    <input type="hidden" name="split_payment" id="split_payment" value="0">
                    <input type="hidden" name="create_new_for_balance" id="create_new_for_balance" value="0">

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
<div id="editModal" class="modal fade" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
            <div class="modal-header" style="padding: 20px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                <h3 class="modal-title" id="editModalLabel" style="margin: 0; font-size: 18px; font-weight: 600; color: #1a1a1a;">Edit Standard Expense</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closeEditModal()"></button>
            </div>
            <div class="modal-body" style="padding: 20px; max-height: 70vh; overflow-y: auto;">
                <!-- The edit form will be loaded here -->
                <div id="editFormContainer"></div>
            </div>
            <div class="modal-footer" style="padding: 15px 20px; border-top: 1px solid #e5e7eb; text-align: right;">
                <button type="button" onclick="closeEditModal()" class="btn btn-outline" data-bs-dismiss="modal"
                    style="padding: 8px 20px; margin-right: 10px; border: 1px solid #ddd; background: white;color:#000; border-radius: 4px; cursor: pointer;">Cancel</button>
                <button type="button" onclick="submitEditForm()" class="btn btn-primary"
                    style="padding: 8px 30px; background: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;">Update Expense</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Non-standard Expense Modal -->
<div class="modal fade" id="editNonStandardModal" tabindex="-1" aria-labelledby="editNonStandardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editNonStandardModalLabel"><i class="fas fa-edit me-2"></i>Edit Non-standard Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editNonStandardExpenseForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_ns_expense_id" name="id">
                <input type="hidden" name="source" value="manual">

                <div class="modal-body">
                    <!-- Company & Expense Name -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Company <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_ns_company_id" name="company_id" required>
                                <option value="" selected disabled>Select Company</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Expense Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_ns_expense_name" name="expense_name" required
                                placeholder="e.g., Server Maintenance">
                        </div>
                    </div>

                    <!-- Category & Amount -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select class="form-select" id="edit_ns_category_id" name="category_id">
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Actual / Base Amount (₹) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit_ns_actual_amount" name="actual_amount"
                                step="0.01" required value="" placeholder="0.00">
                        </div>
                    </div>
                    <div class="section-divider"></div>

                    <!-- GST Section -->
                    <div class="tax-section mb-3">
                        <div class="row g-3 align-items-end">
                            <div class="col-auto">
                                <div class="form-check" style="margin-top: 32px;">
                                    <input class="form-check-input" type="checkbox" id="edit_ns_apply_gst" name="apply_gst"
                                        value="1">
                                    <label class="form-check-label fw-bold text-uppercase small text-muted" for="edit_ns_apply_gst">GST</label>
                                </div>
                            </div>
                            <div class="col">
                                <label class="form-label fw-bold text-uppercase small text-muted">GST %</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="edit_ns_gst_percentage" name="gst_percentage"
                                        value="18" min="0" max="100" step="0.01">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col">
                                <label class="form-label fw-bold text-uppercase small text-muted">GST Amount</label>
                                <input type="number" class="form-control" id="edit_ns_gst_amount" name="gst_amount" value="0.00"
                                    readonly>
                            </div>
                        </div>
                    </div>

                    <!-- TDS Section -->
                    <div class="tax-section mb-3">
                        <div class="row g-3 align-items-end">
                            <div class="col-auto">
                                <div class="form-check" style="margin-top: 32px;">
                                    <input class="form-check-input" type="checkbox" id="edit_ns_apply_tds" name="apply_tds"
                                        value="1">
                                    <label class="form-check-label fw-bold text-uppercase small text-muted" for="edit_ns_apply_tds">TDS</label>
                                </div>
                            </div>
                            <div class="col">
                                <label class="form-label fw-bold text-uppercase small text-muted">TDS %</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="edit_ns_tds_percentage" name="tds_percentage"
                                        value="10" min="0" max="100" step="0.01">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col">
                                <label class="form-label fw-bold text-uppercase small text-muted">TDS Amount</label>
                                <input type="number" class="form-control" id="edit_ns_tds_amount" name="tds_amount" value="0.00"
                                    readonly>
                            </div>
                        </div>
                    </div>

                    <!-- TDS Details -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-uppercase small text-muted">TDS Status</label>
                            <select class="form-select" id="edit_ns_tds_status" name="tds_status">
                                <option value="received">Paid</option>
                                <option value="not_received">Not Paid</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-uppercase small text-muted">TDS Certificate/Receipt</label>
                            <input type="file" id="edit_ns_tds_receipt" name="tds_receipt" class="form-control"
                                accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                        </div>
                    </div>

                    <input type="hidden" class="form-control" id="edit_ns_grand_total" name="grand_total" value="0.00">
                    <input type="hidden" class="form-control" id="edit_ns_schedule_amount" name="planned_amount" value="0.00">

                    <div class="section-divider"></div>

                    <!-- Paid, Mode, Date, Receipt -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Paid Amount (₹)</label>
                            <input type="number" class="form-control" id="edit_ns_paid_amount" name="paid_amount" step="0.01"
                                value="0.00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Payment Mode</label>
                            <select class="form-select" name="payment_mode" id="edit_ns_payment_mode"
                                onchange="toggleEditNsPaymentModeDetails()">
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                                <option value="upi">UPI</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Payment Date</label>
                            <input type="date" class="form-control" name="payment_date" id="edit_ns_payment_date" max="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Add Receipts</label>
                            <input type="file" name="receipts[]" class="form-control" id="edit_ns_receipts"
                                multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                        </div>
                    </div>

                    <!-- Payment Mode Details Row -->
                    <div class="row g-3 mb-3" id="edit_ns_payment_mode_details" style="display: none;">
                        <div class="col-md-6" id="edit_ns_bank_details" style="display: none;">
                            <label class="form-label">Select Bank</label>
                            <select class="form-select" name="bank_name" id="edit_ns_bank_name">
                                <option value="">Select Bank</option>
                                <option value="SBI">State Bank of India</option>
                                <option value="HDFC">HDFC Bank</option>
                                <option value="ICICI">ICICI Bank</option>
                                <option value="Axis">Axis Bank</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="edit_ns_upi_details" style="display: none;">
                            <label class="form-label">UPI Type</label>
                            <select class="form-select" name="upi_type" id="edit_ns_upi_type">
                                <option value="GPay">Google Pay</option>
                                <option value="PhonePe">PhonePe</option>
                                <option value="Paytm">Paytm</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="edit_ns_upi_number_details" style="display: none;">
                            <label class="form-label">UPI Phone Number</label>
                            <input type="text" class="form-control" id="edit_ns_upi_number" name="upi_number" placeholder="Enter phone number" maxlength="10" pattern="[0-9]{10}">
                        </div>
                    </div>

                    <!-- Balance, Status, Settle Notes, Due Date -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Balance</label>
                            <input type="number" class="form-control bg-light" id="edit_ns_balance_amount" name="balance_amount"
                                step="0.01" readonly value="0.00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" required id="edit_ns_status" onchange="toggleEditNsStatusDetails()">
                                <option value="upcoming">Upcoming</option>
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="settle">Settle</option>
                            </select>
                        </div>
                        <div class="col-md-3" id="edit_ns_settle_notes_container" style="display:none;">
                            <label class="form-label" for="edit_ns_settle_notes">Settle Notes <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="edit_ns_settle_notes" name="settle_notes" rows="1" placeholder="Enter notes..."></textarea>
                        </div>
                        <div class="col-md-3" id="edit_ns_due_date_container">
                            <label class="form-label">Due Date</label>
                            <input type="date" class="form-control" name="due_date" id="edit_ns_due_date">
                        </div>
                    </div>
                    <div class="section-divider"></div>

                    <!-- Party/Vendor & Mobile -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Party/Vendor Name</label>
                            <input type="text" class="form-control" id="edit_ns_party_name" name="party_name" placeholder="Optional">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mobile Number</label>
                            <input type="number" class="form-control" id="edit_ns_mobile_number" name="mobile_number" placeholder="Optional">
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label class="form-label" for="edit_ns_notes">Notes</label>
                        <textarea class="form-control" id="edit_ns_notes" name="notes" rows="2" placeholder="Optional notes..."></textarea>
                    </div>

                    <!-- Existing Receipts -->
                    <div class="mb-2" id="edit_ns_existing_receipts"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Status tab switching
    function setStatusFilter(status) {
        const statusInput = document.getElementById('expenseStatusInput');
        if (statusInput) {
            statusInput.value = status;
        }
        const form = document.getElementById('filterForm');
        if (form) {
            form.submit();
        }
    }

    // Non-standard Status tab switching
    function setNonStandardStatusFilter(status) {
        const statusInput = document.getElementById('nsExpenseStatusInput');
        if (statusInput) {
            statusInput.value = status;
        }
        const form = document.getElementById('nonStandardFilterForm');
        if (form) {
            form.submit();
        }
    }

    // Non-standard date range handler
    function handleNsDateRangeChange(value) {
        const customRow = document.getElementById('nsCustomDateRangeRow');
        if (value === 'custom') {
            if (customRow) customRow.style.display = 'block';
        } else {
            if (customRow) customRow.style.display = 'none';
            const form = document.getElementById('nonStandardFilterForm');
            if (form) form.submit();
        }
    }

    // Date range select handler
    function handleDateRangeChange(value) {
        const customRow = document.getElementById('customDateRangeRow');
        if (value === 'custom') {
            if (customRow) customRow.style.display = 'block';
        } else {
            if (customRow) customRow.style.display = 'none';
            const form = document.getElementById('filterForm');
            if (form) form.submit();
        }
    }

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
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'non-standard') {
            return;
        }
        const hasFilterParams = urlParams.has('search') || urlParams.has('company_id') ||
            urlParams.has('category_type') || urlParams.has('status') ||
            urlParams.has('date_range') || urlParams.has('start_date') ||
            urlParams.has('end_date') || urlParams.has('page') ||
            urlParams.has('per_page') || urlParams.get('tab') === 'table-tab';

        const lastActiveTab = hasFilterParams ? 'table-tab' : (localStorage.getItem('lastActiveTab') || 'table-tab');
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
    document.addEventListener('keydown', function(e) {
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
    document.addEventListener('DOMContentLoaded', function() {
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
    document.addEventListener('DOMContentLoaded', function() {
        const ppOriginalAmountEl = document.getElementById('pp_original_amount');
        if (ppOriginalAmountEl) {
            calculateTax();
            ppOriginalAmountEl.addEventListener('input', calculateTax);
            document.getElementById('applyGST')?.addEventListener('change', calculateTax);
            document.getElementById('gst_percentage')?.addEventListener('input', calculateTax);
            document.getElementById('applyTDS')?.addEventListener('change', calculateTax);
            document.getElementById('tds_percentage')?.addEventListener('input', calculateTax);
        }

        // Set default entry direction to expense
        const entryDirectionEl = document.getElementById('entry_direction');
        if (entryDirectionEl) {
            entryDirectionEl.value = 'expense';
            entryDirectionEl.addEventListener('change', function() {
                updateCategoryTypeOptions();
            });
        }
        if (document.getElementById('category_type')) {
            updateCategoryTypeOptions();
            document.getElementById('category_type')?.addEventListener('change', function() {
                updateCategoryOptions();
            });
        }

        // Payment field listeners
        document.getElementById('gst_amount_paid')?.addEventListener('input', updateGstPaymentStatus);
        document.getElementById('tds_amount_paid')?.addEventListener('input', updateTdsPaymentStatus);
        document.getElementById('amount_paid')?.addEventListener('input', updateMainPaymentStatus);
    });

    // Tax calculation function
    function calculateTax() {
        const ppOriginalEl = document.getElementById('pp_original_amount');
        if (!ppOriginalEl) return;

        // Get original amount
        const originalAmount = parseFloat(ppOriginalEl.value) || 0;

        // GST Calculation
        const applyGstEl = document.getElementById('applyGST');
        const gstPercentageEl = document.getElementById('gst_percentage');
        const applyGst = applyGstEl ? applyGstEl.checked : false;
        const gstPercentage = gstPercentageEl ? (parseFloat(gstPercentageEl.value) || 0) : 0;

        // TDS Calculation
        const applyTdsEl = document.getElementById('applyTDS');
        const tdsPercentageEl = document.getElementById('tds_percentage');
        const applyTds = applyTdsEl ? applyTdsEl.checked : false;
        const tdsPercentage = tdsPercentageEl ? (parseFloat(tdsPercentageEl.value) || 0) : 0;

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

        // Update display fields safely
        const gstSubtotal = document.getElementById('gst_subtotal');
        if (gstSubtotal) gstSubtotal.value = gstAmount.toFixed(2);
        const gstTotal = document.getElementById('gst_total');
        if (gstTotal) gstTotal.value = amountAfterGst.toFixed(2);

        const tdsSubtotal = document.getElementById('tds_subtotal');
        if (tdsSubtotal) tdsSubtotal.value = tdsAmount.toFixed(2);
        const tdsFinal = document.getElementById('tds_final');
        if (tdsFinal) tdsFinal.value = (amountForTds - tdsAmount).toFixed(2);

        const grandTotalDisplay = document.getElementById('grand_total_display');
        if (grandTotalDisplay) grandTotalDisplay.value = '₹ ' + amountAfterGst.toFixed(2);

        const defaultAmount = document.getElementById('default_amount');
        if (defaultAmount) defaultAmount.value = amountAfterGst.toFixed(2);

        const hiddenGst = document.getElementById('hidden_gst_amount');
        if (hiddenGst) hiddenGst.value = gstAmount.toFixed(2);
        const hiddenTds = document.getElementById('hidden_tds_amount');
        if (hiddenTds) hiddenTds.value = tdsAmount.toFixed(2);
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
        const paymentStatusEl = document.getElementById('payment_status');
        const statusSelect = document.getElementById('status');
        if (!paymentStatusEl || !statusSelect) return;
        const paymentStatus = paymentStatusEl.value;

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
        if (!categoryTypeSelect || !categorySelect) return;

        // Reset dependent dropdown
        categoryTypeSelect.innerHTML = '<option value="">Select Category Type</option>';
        categorySelect.innerHTML = '<option value="">Select Category</option>';
        categorySelect.disabled = true;

        if (direction && typeof categoryTypeOptions !== 'undefined' && categoryTypeOptions[direction]) {
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
        const categoryTypeEl = document.getElementById('category_type');
        const categorySelect = document.getElementById('category');
        if (!categoryTypeEl || !categorySelect) return;
        const combinedCategoryType = categoryTypeEl.value;

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

        fetch(`${window.APP_URL}/admin/standard-expenses/get-categories`, {
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
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile number validation
        const mobileField = document.getElementById('mobile_number');
        if (mobileField) {
            mobileField.addEventListener('input', function() {
                validateMobileNumber(this);
            });
        }

        // Amount validation
        const amountField = document.getElementById('pp_original_amount');
        if (amountField) {
            amountField.addEventListener('input', function() {
                validatePositiveNumber(this);
                calculateTax(); // Recalculate tax when amount changes
            });
        }

        // GST percentage validation
        const gstField = document.getElementById('gst_percentage');
        if (gstField) {
            gstField.addEventListener('input', function() {
                const applyGstEl = document.getElementById('applyGST');
                if (applyGstEl && applyGstEl.checked) {
                    validateGSTPercentage();
                }
                calculateTax();
            });
        }

        // TDS percentage validation
        const tdsField = document.getElementById('tds_percentage');
        if (tdsField) {
            tdsField.addEventListener('input', function() {
                const applyTdsEl = document.getElementById('applyTDS');
                if (applyTdsEl && applyTdsEl.checked) {
                    validateTDSPercentage();
                }
                calculateTax();
            });
        }

        // Due day validation
        const dueDayField = document.getElementById('due_day');
        if (dueDayField) {
            dueDayField.addEventListener('input', validateDueDay);
        }

        // Frequency change affects due day validation
        const frequencyField = document.getElementById('frequency');
        if (frequencyField) {
            frequencyField.addEventListener('change', validateDueDay);
        }

        // Expense name length validation
        const expenseNameField = document.getElementById('expense_name');
        if (expenseNameField) {
            expenseNameField.addEventListener('input', function() {
                if (this.value.length > 255) {
                    showError('expense_name', 'Maximum 255 characters allowed');
                } else {
                    resetError('expense_name');
                }
            });
        }

        // Party name length validation
        const partyNameField = document.getElementById('party_name');
        if (partyNameField) {
            partyNameField.addEventListener('input', function() {
                if (this.value.length > 255) {
                    showError('party_name', 'Maximum 255 characters allowed');
                } else {
                    resetError('party_name');
                }
            });
        }
    });
    // Handle form submission
    document.getElementById('templateForm')?.addEventListener('submit', function(e) {
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
        const editBtn = (typeof event !== 'undefined' && event && event.target) ? (event.target.closest('button') || event.target) : null;
        const originalHtml = editBtn ? editBtn.innerHTML : '';
        try {
            console.log('Starting edit for ID:', id);

            if (editBtn) {
                editBtn.disabled = true;
                editBtn.classList.add('loading');
            }

            // Load expense data
            const expenseResponse = await fetch(
                `${window.APP_URL}/admin/standard-expenses/${id}`);
            const expenseData = await expenseResponse.json();

            // Load tax data
            let taxData = {};
            try {
                const taxResponse = await fetch(
                    `${window.APP_URL}/admin/standard-expenses/${id}/taxes`);
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
            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('editModal'));
            modal.show();

            // Initialize form functionality
            initializeEditForm(id, expenseData);

            // Restore edit button
            if (editBtn) {
                editBtn.innerHTML = originalHtml;
                editBtn.disabled = false;
                editBtn.classList.remove('loading');
            }

            console.log('Edit form loaded in modal');

        } catch (error) {
            console.error('Error loading template:', error);
            alert('Error loading template data: ' + (error.message || 'Unknown error'));

            // Restore edit button on error
            if (editBtn) {
                editBtn.innerHTML = originalHtml;
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
            form.action = `${window.APP_URL}/admin/standard-expenses/${id}`;
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
        document.getElementById('edit_pp_original_amount').addEventListener('input', function() {
            calculateEditTax();
        });
        document.getElementById('edit_applyGST').addEventListener('change', function() {
            calculateEditTax();
        });
        document.getElementById('edit_gst_percentage').addEventListener('input', function() {
            calculateEditTax();
        });
        document.getElementById('edit_applyTDS').addEventListener('change', function() {
            calculateEditTax();
        });
        document.getElementById('edit_tds_percentage').addEventListener('input', function() {
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

        fetch(`${window.APP_URL}/admin/standard-expenses/get-categories`, {
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
        const modalEl = document.getElementById('editModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) {
            modal.hide();
        }
        document.getElementById('editFormContainer').innerHTML = '';
    }

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
        form.action = '{{ route('admin.standard-expenses.store') }}';

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

    // Non-Standard Expense calculations (Manager Panel Equivalent)
    function calculateTaxNonStandardAdd() {
        const baseAmount = parseFloat(document.getElementById('actual_amount')?.value) || 0;

        let gstAmount = 0;
        let tdsAmount = 0;
        let grandTotal = baseAmount;
        let amountAfterTDS = baseAmount;

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

        const applyTDS = document.getElementById('apply_tds');
        if (applyTDS && applyTDS.checked) {
            const tdsPercentage = parseFloat(document.getElementById('tds_percentage')?.value) || 0;
            tdsAmount = (baseAmount * tdsPercentage) / 100;
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

        const grandTotalField = document.getElementById('grand_total');
        const netPayable = baseAmount + gstAmount - tdsAmount;

        if (grandTotalField) grandTotalField.value = (baseAmount + gstAmount).toFixed(2);

        const scheduleAmountInput = document.getElementById('schedule_amount');
        const paidAmountInput = document.getElementById('paid_amount');

        const oldScheduleAmount = parseFloat(scheduleAmountInput?.value) || 0;
        const currentPaidAmount = parseFloat(paidAmountInput?.value) || 0;

        if (scheduleAmountInput) scheduleAmountInput.value = netPayable.toFixed(2);

        if (paidAmountInput && (Math.abs(currentPaidAmount - oldScheduleAmount) < 0.01)) {
            paidAmountInput.value = netPayable.toFixed(2);
        }

        calculateBalance();
        handleStatusBehavior('non-standard-add');
    }

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

            if (paidAmount > scheduleAmount + 0.01 && scheduleAmount > 0) {
                paidAmount = scheduleAmount;
                paid_amountInput.value = scheduleAmount.toFixed(2);
            } else if (paidAmount < 0) {
                paidAmount = 0;
                paid_amountInput.value = '0.00';
            }

            const balance = Math.max(0, scheduleAmount - paidAmount);
            balanceAmountInput.value = balance.toFixed(2);

            if (statusDropdown) {
                if (balance > 0.01) {
                    if (statusDropdown.value === 'paid' || (!statusDropdown.value && paidAmount <= 0 && statusDropdown.value !== 'settle')) {
                        statusDropdown.value = 'due';
                    }
                }
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

        const allInputs = detailsRow.querySelectorAll('select, input');
        allInputs.forEach(input => {
            input.required = false;
            input.removeAttribute('required');
        });

        const allLabels = detailsRow.querySelectorAll('.form-label');
        allLabels.forEach(label => {
            label.innerHTML = label.innerHTML.replace(' <span class="text-danger">*</span>', '');
        });

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

    function handleStatusBehavior(modalType) {
        if (modalType !== 'non-standard-add') return;

        const statusEl = document.getElementById('payment_status');
        const dueDateEl = document.getElementById('add_due_date');
        const notesEl = document.getElementById('addSettleNotes');
        const notesContainer = document.getElementById('addSettleNotesContainer');
        const dueDateContainer = document.getElementById('due_date_container');

        if (!statusEl) return;
        const status = statusEl.value;
        const isDue = (status === 'due' || status === 'pending' || status === 'upcoming');

        if (dueDateContainer) {
            dueDateContainer.style.display = isDue ? 'block' : 'none';
        }

        if (status === 'settle' || status === 'paid') {
            if (dueDateEl) {
                dueDateEl.disabled = true;
                dueDateEl.required = false;
                dueDateEl.value = '';
            }
            if (notesEl && notesContainer) {
                notesContainer.style.display = 'block';
                notesEl.required = true;
            }
        } else if (status === 'due') {
            if (dueDateEl) {
                dueDateEl.disabled = false;
                dueDateEl.required = true;
            }
            if (notesEl && notesContainer) {
                notesContainer.style.display = 'none';
                notesEl.required = false;
            }
        } else {
            if (dueDateEl) {
                dueDateEl.disabled = false;
                dueDateEl.required = false;
            }
            if (notesEl && notesContainer) {
                notesContainer.style.display = 'none';
                notesEl.required = false;
            }
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
            const label = fileEl.closest('[class*="col-"]')?.querySelector('.form-label');
            if (label && !label.innerHTML.includes('*')) {
                label.innerHTML += ' <span class="text-danger">*</span>';
            }
        } else {
            fileEl.required = false;
            fileEl.removeAttribute('required');
            const label = fileEl.closest('[class*="col-"]')?.querySelector('.form-label');
            if (label) {
                label.innerHTML = label.innerHTML.replace(' <span class="text-danger">*</span>', '');
            }
        }
    }

    function setAllExpensesStatusFilter(status) {
        const input = document.getElementById('allExpenseStatusInput');
        if (input) input.value = status;
        const form = document.getElementById('allExpensesFilterForm');
        if (form) form.submit();
    }

    function handleAllExpDateRangeChange(val) {
        const container = document.getElementById('allExpCustomDateContainer');
        if (container) {
            if (val === 'custom') {
                container.classList.remove('d-none');
            } else {
                container.classList.add('d-none');
                document.getElementById('allExpensesFilterForm')?.submit();
            }
        }
    }

    function markAllExpenseAsPaid(expenseId, expenseName, amount, source) {
        if (!confirm(`Mark "${expenseName}" as paid (Amount: ₹${parseFloat(amount || 0).toLocaleString()})?`)) {
            return;
        }

        let url = `{{ url('admin/standard-expenses/non-standard') }}/${expenseId}/mark-paid`;
        if (source === 'standard') {
            url = `{{ url('admin/standard-expenses') }}/${expenseId}/mark-paid`;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);

        const paidInput = document.createElement('input');
        paidInput.type = 'hidden';
        paidInput.name = 'paid_amount';
        paidInput.value = amount;
        form.appendChild(paidInput);

        document.body.appendChild(form);
        form.submit();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const nsModal = document.getElementById('addNonStandardModal');
        if (nsModal) {
            const actualAmountInput = document.getElementById('actual_amount');
            const gstCheckbox = document.getElementById('apply_gst');
            const gstPercentageInput = document.getElementById('gst_percentage');
            const tdsCheckbox = document.getElementById('apply_tds');
            const tdsPercentageInput = document.getElementById('tds_percentage');
            const paidAmountInput = document.getElementById('paid_amount');
            const paymentStatusSelect = document.getElementById('payment_status');
            const addTdsStatusSelect = document.getElementById('addTdsStatus');

            if (actualAmountInput) actualAmountInput.addEventListener('input', calculateTaxNonStandardAdd);
            if (gstCheckbox) gstCheckbox.addEventListener('change', calculateTaxNonStandardAdd);
            if (gstPercentageInput) gstPercentageInput.addEventListener('input', calculateTaxNonStandardAdd);
            if (tdsCheckbox) tdsCheckbox.addEventListener('change', calculateTaxNonStandardAdd);
            if (tdsPercentageInput) tdsPercentageInput.addEventListener('input', calculateTaxNonStandardAdd);
            if (paidAmountInput) paidAmountInput.addEventListener('input', () => {
                calculateBalance();
                handleStatusBehavior('non-standard-add');
            });
            if (paymentStatusSelect) paymentStatusSelect.addEventListener('change', () => {
                handleStatusBehavior('non-standard-add');
                if (paymentStatusSelect.value !== 'settle' && paymentStatusSelect.value !== 'paid') {
                    calculateTaxNonStandardAdd();
                }
            });
            if (addTdsStatusSelect) addTdsStatusSelect.addEventListener('change', () => {
                handleTdsStatusBehavior('addTdsStatus', 'addTdsReceipt');
            });

            nsModal.addEventListener('show.bs.modal', function() {
                const form = document.getElementById('addNonStandardForm');
                if (form) form.reset();
                calculateTaxNonStandardAdd();
            });
        }

        const editNsActual = document.getElementById('edit_ns_actual_amount');
        const editNsApplyGst = document.getElementById('edit_ns_apply_gst');
        const editNsGstPct = document.getElementById('edit_ns_gst_percentage');
        const editNsApplyTds = document.getElementById('edit_ns_apply_tds');
        const editNsTdsPct = document.getElementById('edit_ns_tds_percentage');
        const editNsPaid = document.getElementById('edit_ns_paid_amount');

        if (editNsActual) editNsActual.addEventListener('input', calculateEditNsTax);
        if (editNsApplyGst) editNsApplyGst.addEventListener('change', calculateEditNsTax);
        if (editNsGstPct) editNsGstPct.addEventListener('input', calculateEditNsTax);
        if (editNsApplyTds) editNsApplyTds.addEventListener('change', calculateEditNsTax);
        if (editNsTdsPct) editNsTdsPct.addEventListener('input', calculateEditNsTax);
        if (editNsPaid) editNsPaid.addEventListener('input', calculateEditNsTax);
    });

    function toggleEditNsPaymentModeDetails() {
        const mode = document.getElementById('edit_ns_payment_mode')?.value;
        const detailsRow = document.getElementById('edit_ns_payment_mode_details');
        const bankDetails = document.getElementById('edit_ns_bank_details');
        const upiDetails = document.getElementById('edit_ns_upi_details');
        const upiNumberDetails = document.getElementById('edit_ns_upi_number_details');

        if (!detailsRow) return;

        if (mode === 'bank_transfer' || mode === 'cheque') {
            detailsRow.style.display = 'flex';
            if (bankDetails) bankDetails.style.display = 'block';
            if (upiDetails) upiDetails.style.display = 'none';
            if (upiNumberDetails) upiNumberDetails.style.display = 'none';
        } else if (mode === 'upi') {
            detailsRow.style.display = 'flex';
            if (bankDetails) bankDetails.style.display = 'none';
            if (upiDetails) upiDetails.style.display = 'block';
            if (upiNumberDetails) upiNumberDetails.style.display = 'block';
        } else {
            detailsRow.style.display = 'none';
            if (bankDetails) bankDetails.style.display = 'none';
            if (upiDetails) upiDetails.style.display = 'none';
            if (upiNumberDetails) upiNumberDetails.style.display = 'none';
        }
    }

    function toggleEditNsStatusDetails() {
        const status = document.getElementById('edit_ns_status')?.value;
        const settleContainer = document.getElementById('edit_ns_settle_notes_container');
        const settleNotes = document.getElementById('edit_ns_settle_notes');
        const dueDateContainer = document.getElementById('edit_ns_due_date_container');
        const dueDate = document.getElementById('edit_ns_due_date');

        if (status === 'settle') {
            if (settleContainer) settleContainer.style.display = 'block';
            if (settleNotes) settleNotes.required = true;
            if (dueDateContainer) dueDateContainer.style.display = 'none';
            if (dueDate) dueDate.required = false;
        } else if (status === 'upcoming') {
            if (settleContainer) settleContainer.style.display = 'none';
            if (settleNotes) settleNotes.required = false;
            if (dueDateContainer) dueDateContainer.style.display = 'block';
            if (dueDate) dueDate.required = true;
        } else {
            if (settleContainer) settleContainer.style.display = 'none';
            if (settleNotes) settleNotes.required = false;
            if (dueDateContainer) dueDateContainer.style.display = 'block';
            if (dueDate) dueDate.required = false;
        }
    }

    function calculateEditNsTax() {
        const baseAmount = parseFloat(document.getElementById('edit_ns_actual_amount')?.value) || 0;
        let gstAmount = 0;
        let tdsAmount = 0;

        const applyGst = document.getElementById('edit_ns_apply_gst');
        if (applyGst && applyGst.checked) {
            const gstPercentage = parseFloat(document.getElementById('edit_ns_gst_percentage')?.value) || 0;
            gstAmount = (baseAmount * gstPercentage) / 100;
            const gstField = document.getElementById('edit_ns_gst_amount');
            if (gstField) gstField.value = gstAmount.toFixed(2);
        } else {
            const gstField = document.getElementById('edit_ns_gst_amount');
            if (gstField) gstField.value = '0.00';
        }

        const applyTds = document.getElementById('edit_ns_apply_tds');
        if (applyTds && applyTds.checked) {
            const tdsPercentage = parseFloat(document.getElementById('edit_ns_tds_percentage')?.value) || 0;
            tdsAmount = (baseAmount * tdsPercentage) / 100;
            const tdsField = document.getElementById('edit_ns_tds_amount');
            if (tdsField) tdsField.value = tdsAmount.toFixed(2);
        } else {
            const tdsField = document.getElementById('edit_ns_tds_amount');
            if (tdsField) tdsField.value = '0.00';
        }

        const grandTotal = baseAmount + gstAmount;
        const netPayable = grandTotal - tdsAmount;

        const grandTotalEl = document.getElementById('edit_ns_grand_total');
        if (grandTotalEl) grandTotalEl.value = grandTotal.toFixed(2);

        const scheduleEl = document.getElementById('edit_ns_schedule_amount');
        if (scheduleEl) scheduleEl.value = netPayable.toFixed(2);

        const paidAmountInput = document.getElementById('edit_ns_paid_amount');
        const paidAmount = parseFloat(paidAmountInput?.value) || 0;

        const balance = Math.max(0, netPayable - paidAmount);
        const balanceField = document.getElementById('edit_ns_balance_amount');
        if (balanceField) balanceField.value = balance.toFixed(2);
    }

    async function editNonStandardExpense(id) {
        try {
            const response = await fetch(`${window.APP_URL}/admin/standard-expenses/non-standard/${id}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();
            if (!data.success || !data.expense) {
                alert(data.message || 'Could not load expense data');
                return;
            }
            const exp = data.expense;
            const form = document.getElementById('editNonStandardExpenseForm');
            form.action = `${window.APP_URL}/admin/standard-expenses/non-standard/${exp.id}`;

            document.getElementById('edit_ns_expense_id').value = exp.id;
            document.getElementById('edit_ns_company_id').value = exp.company_id || '';
            document.getElementById('edit_ns_expense_name').value = exp.expense_name || '';
            document.getElementById('edit_ns_category_id').value = exp.category_id || '';
            document.getElementById('edit_ns_actual_amount').value = exp.actual_amount || '';

            document.getElementById('edit_ns_apply_gst').checked = !!exp.has_gst;
            document.getElementById('edit_ns_gst_percentage').value = exp.gst_percentage || 18;
            document.getElementById('edit_ns_gst_amount').value = (exp.gst_amount || 0).toFixed(2);

            document.getElementById('edit_ns_apply_tds').checked = !!exp.has_tds;
            document.getElementById('edit_ns_tds_percentage').value = exp.tds_percentage || 10;
            document.getElementById('edit_ns_tds_amount').value = (exp.tds_amount || 0).toFixed(2);
            document.getElementById('edit_ns_tds_status').value = exp.tds_status || 'not_received';

            document.getElementById('edit_ns_paid_amount').value = exp.paid_amount || 0;
            document.getElementById('edit_ns_payment_mode').value = exp.payment_mode || 'cash';
            document.getElementById('edit_ns_payment_date').value = exp.payment_date || '';
            document.getElementById('edit_ns_due_date').value = exp.due_date || '';
            document.getElementById('edit_ns_status').value = (exp.status === 'upcoming' || exp.status === 'due') ? 'upcoming' : (exp.status || 'pending');
            document.getElementById('edit_ns_party_name').value = exp.party_name || '';
            document.getElementById('edit_ns_mobile_number').value = exp.mobile_number || '';
            document.getElementById('edit_ns_notes').value = exp.notes || '';
            document.getElementById('edit_ns_settle_notes').value = exp.settle_notes || '';

            if (exp.bank_name) document.getElementById('edit_ns_bank_name').value = exp.bank_name;
            if (exp.upi_type) document.getElementById('edit_ns_upi_type').value = exp.upi_type;
            if (exp.upi_number) document.getElementById('edit_ns_upi_number').value = exp.upi_number;

            toggleEditNsPaymentModeDetails();
            toggleEditNsStatusDetails();
            calculateEditNsTax();

            const receiptsContainer = document.getElementById('edit_ns_existing_receipts');
            if (receiptsContainer) {
                receiptsContainer.innerHTML = '';
                if (exp.receipts && exp.receipts.length > 0) {
                    let html = '<label class="form-label small text-muted fw-bold">Existing Receipts:</label><div class="d-flex flex-wrap gap-2">';
                    exp.receipts.forEach(r => {
                        html += `<a href="${window.APP_URL}/${r.file_path}" target="_blank" class="badge bg-light text-dark border p-2 text-decoration-none"><i class="fas fa-file me-1 text-primary"></i>${r.file_name}</a>`;
                    });
                    html += '</div>';
                    receiptsContainer.innerHTML = html;
                }
            }

            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('editNonStandardModal'));
            modal.show();
        } catch (err) {
            console.error('Error in editNonStandardExpense:', err);
            alert('Failed to load expense details');
        }
    }
</script>
<style>
    .tax-section {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 16px;
    }

    .section-divider {
        border-top: 2px solid #e2e8f0;
        margin: 24px 0;
    }
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