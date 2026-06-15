@extends('Manager.layouts.app')
@section('content')
<section class="pge">
    <div class="container-fluid">

        <div class="card shadow-sm mb-3">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="mb-0">Taxes Paid on Expenses</h5>
                    <div class="small-help">Track GST/TDS/Other taxes paid on expense bills for CA verification.</div>
                </div>
                <div class="topnav">
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('manager.gst') }}">Dashboard</a>
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('manager.gst-collected') }}">GST
                        Collected</a>
                    <a class="btn btn-sm btn-primary" href="{{ route('manager.taxes') }}">GST Payable</a>
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('manager.tds') }}">TDS on Income</a>
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('manager.tdsExpense') }}">TDS on Expense</a>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card kpi shadow-sm">
                    <div class="card-body">
                        <div class="label">Tax Period</div>
                        <div class="value">{{ $currentPeriod }}</div>
                        <div class="small-help">For CA handoff exports</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card kpi shadow-sm">
                    <div class="card-body">
                        <div class="label">Total Taxes Paid</div>
                        <div class="value">₹ {{ number_format($totalTaxPaid, 2) }}</div>
                        <div class="small-help">GST/TDS/Other on expenses</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card kpi shadow-sm">
                    <div class="card-body">
                        <div class="label">Bills with Tax</div>
                        <div class="value">{{ $gstRecordsCount }}</div>
                        <div class="small-help">Total unique expense bills</div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Filter Section -->
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div>
                        <h6 class="mb-1">Filter Tax Entries from Expenses</h6>
                        <div class="small-help">View and filter tax payments from expense bills.</div>
                    </div>
                </div>

                <form method="GET" action="{{ route('manager.taxes') }}" class="row g-2 align-items-end"
                    id="taxFilterForm">
                    <div class="col-md-3">
                        <label class="form-label small">Company</label>
                        <select class="form-select form-select-sm" name="company_id" id="company_id">
                            <option value="all">All Companies</option>
                            @foreach ($companies as $company)
                            <option value="{{ $company->id }}"
                                {{ $selectedCompany == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Tax Period</label>
                        <select class="form-select form-select-sm" name="period" id="period">
                            @foreach ($months as $month)
                            <option value="{{ $month['value'] }}"
                                {{ $selectedPeriod == $month['value'] ? 'selected' : '' }}>
                                {{ $month['label'] }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Search</label>
                        <input type="text" class="form-control form-control-sm" name="search" id="search" value="{{ request('search') }}" placeholder="Bill No / Name">
                    </div>

                    <div class="col-md-3 d-grid">
                        <button type="submit" class="btn btn-sm btn-primary">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tax Entries Table -->
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Tax Payments from Expense Bills</span>
                <div class="d-flex align-items-center gap-2">
                    <select class="form-select form-select-sm border-0 bg-light" style="width: auto;" id="perPageSelector" onchange="updatePerPage()">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 per page</option>
                        <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20 per page</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 per page</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 per page</option>
                    </select>
                    <button class="btn btn-sm btn-outline-secondary" onclick="exportTaxData('excel')">
                        Export Excel
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="exportTaxData('pdf')">
                        Export PDF
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="15%"><i class="bi bi-hash me-1"></i>Bill No</th>
                                <th>Date</th>
                                <th>Company</th>
                                <th>Expense</th>
                                <th>Vendor/Party</th>
                                <th>Tax Type</th>
                                <th class="text-end">Expense Amt (₹)</th>
                                <th class="text-end">Tax %</th>
                                <th class="text-end">Tax Amt (₹)</th>
                                <th>Payment Status</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($expenseTaxes->count() > 0)
                            @foreach ($expenseTaxes as $tax)
                            @php
                            $expense = $tax->taxable;
                            $badgeClass =
                            $tax->tax_type == 'gst'
                            ? 'bg-primary'
                            : ($tax->tax_type == 'tds'
                            ? 'bg-secondary'
                            : 'bg-dark');
                            $statusClass =
                            $tax->payment_status == 'received' ? 'bg-success' : 'bg-warning';
                            $taxTypeLabel = strtoupper($tax->tax_type);
                            $vendorName = $expense->party_name ?? ($expense->vendor_name ?? 'N/A');
                            $expenseName = $expense->expense_name ?? 'Expense';
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('manager.expense.view', $expense->id) }}" class="fw-bold text-primary text-decoration-none">
                                        #EXP-{{ $expense->id }}
                                    </a>
                                </td>
                                <td>{{ $expense->paid_date ? date('d-m-Y', strtotime($expense->paid_date)) : date('d-m-Y', strtotime($expense->created_at)) }}
                                </td>
                                <td>{{ $expense->company->name ?? 'N/A' }}</td>
                                <td>{{ $expenseName }}</td>
                                <td>{{ $vendorName }}</td>
                                <td>
                                    <span class="badge {{ $badgeClass }}">{{ $taxTypeLabel }}</span>
                                </td>
                                <td class="text-end">{{ number_format($tax->taxable_amount ?? $expense->actual_amount ?? 0, 2) }}</td>
                                <td class="text-end">{{ $tax->tax_percentage }}%</td>
                                <td class="text-end fw-semibold">{{ number_format($tax->tax_amount, 2) }}</td>
                                <td>
                                    @php
                                        $displayStatus = ucfirst(str_replace('_', ' ', $tax->payment_status));
                                        if ($tax->payment_status === 'received') $displayStatus = 'Paid';
                                        elseif ($tax->payment_status === 'not_received') $displayStatus = 'Not Paid';
                                    @endphp
                                    <span class="badge {{ $statusClass }}">
                                        {{ $displayStatus }}
                                    </span>
                                </td>
                                <td class="text-truncate" style="max-width: 200px;">
                                    {{ $tax->payment_notes ?? ($expense->notes ?? 'N/A') }}
                                </td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-receipt display-6"></i>
                                        <p class="mt-2">No tax payments found for the selected period.</p>
                                        <p class="small">Add tax amounts to your expense entries to see them here.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                        @if ($expenseTaxes->count() > 0)
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="5" class="text-end fw-semibold">Total:</td>
                                <td class="text-end fw-semibold">
                                    ₹ {{ number_format($totalExpenseAmount, 2) }}
                                </td>
                                <td></td>
                                <td class="text-end fw-semibold">
                                    ₹ {{ number_format($totalTaxPaid, 2) }}
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
                <!-- Pagination -->
                @if ($expenseTaxes->hasPages() || $expenseTaxes->total() > 0)
                <div class="d-flex justify-content-between align-items-center p-3 border-top">
                    <div class="text-muted small">
                        @if ($expenseTaxes->total() > 0)
                        Showing {{ $expenseTaxes->firstItem() }} to {{ $expenseTaxes->lastItem() }} of {{ $expenseTaxes->total() }} entries
                        @else
                        No entries found
                        @endif
                    </div>
                    <div class="laravel-pagination">
                        {{ $expenseTaxes->links('pagination::bootstrap-5') }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Information Card -->
        <div class="card shadow-sm mt-3">
            <div class="card-body">
                <h6 class="mb-2"><i class="bi bi-info-circle"></i> How to Use This Page</h6>
                <ul class="mb-0 small">
                    <li>This page shows all taxes paid on expenses (Input Tax Credit).</li>
                    <li>GST shown here can be claimed as Input Tax Credit against output GST.</li>
                    <li>TDS shown here can be claimed against your TDS liability.</li>
                    <li>To add tax entries, edit an expense and add tax details in the tax section.</li>
                    <li>Use filters to view taxes by company, period, or tax type.</li>
                    <li>Export data for your CA or tax consultant.</li>
                </ul>
            </div>
        </div>

    </div>
</section>

<script>
    function exportTaxData(type) {
        const company = document.getElementById('company_id').value;
        const period = document.getElementById('period').value;
        // const taxType = document.getElementById('tax_type').value;

        window.location.href =
            `{{ url('manager/taxes/export') }}/${type}?company=${company}&period=${period}`;
    }

    // Initialize with current filters
    document.addEventListener('DOMContentLoaded', function() {
        // Update KPI cards with current period
        const periodSelect = document.getElementById('period');
        const periodValue = periodSelect.options[periodSelect.selectedIndex].text;
        document.querySelectorAll('.kpi .value').forEach(el => {
            if (el.textContent.includes('Tax Period')) {
                el.textContent = periodValue;
            }
        });
    });

    function updatePerPage() {
        const perPage = document.getElementById('perPageSelector').value;
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', perPage);
        url.searchParams.set('page', 1); // Reset to page 1 when changing per page
        window.location.href = url.toString();
    }
</script>
@endsection