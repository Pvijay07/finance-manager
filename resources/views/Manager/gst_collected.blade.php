@extends('Manager.layouts.app')
@section('content')
    <section class="pge">
        <div class="container-fluid">

            <div class="card shadow-sm mb-3">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="mb-0">Tax Collected (Output)</h5>
                        <div class="small-help">From taxable income/sales. Shows GST and TDS collected.</div>
                    </div>
                    <div class="topnav">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('manager.gst') }}">Dashboard</a>
                        <a class="btn btn-sm btn-primary" href="{{ route('manager.gst-collected') }}">GST Collected</a>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('manager.taxes') }}">GST Payable</a>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('manager.tds') }}">TDS on Income</a>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('manager.tdsExpense') }}">TDS on Expense</a>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="card kpi shadow-sm">
                        <div class="card-body">
                            <div class="label">Tax Period</div>
                            <div class="value">{{ $currentPeriod }}</div>
                            <div class="small-help">For CA handoff exports</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card kpi shadow-sm">
                        <div class="card-body">
                            <div class="label">Total GST Collected</div>
                            <div class="value">₹ {{ number_format($totalGSTCollected, 2) }}</div>
                            <div class="small-help">Output GST on income records</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card kpi shadow-sm">
                        <div class="card-body">
                            <div class="label">Incomes with GST</div>
                            <div class="value">{{ $gstRecordsCount }}</div>
                            <div class="small-help">Total income records with GST</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <h6 class="mb-1">Filter Income Records</h6>
                            <div class="small-help">View and filter taxable income records.</div>
                        </div>
                    </div>

                    <form class="row g-2 align-items-end" id="taxFilterForm">
                        @csrf
                        <div class="col-md-3">
                            <label class="form-label small">Company</label>
                            <select class="form-select form-select-sm" name="company_id" id="company_filter">
                                <option value="all">All</option>
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
                            <select class="form-select form-select-sm" name="period" id="period_filter">
                                @foreach ($periods as $p)
                                    <option value="{{ $p }}" {{ $currentPeriod == $p ? 'selected' : '' }}>
                                        {{ date('M Y', strtotime($p)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Search</label>
                            <input type="text" class="form-control form-control-sm" name="search" id="search_filter" value="{{ request('search') }}" placeholder="Invoice No / Client">
                        </div>
                        <div class="col-md-2 d-grid">
                            <button type="button" class="btn btn-sm btn-primary" onclick="applyTaxFilters()">Apply</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Income Records Table -->
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Income Records with Tax</span>
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
                                    <th width="15%"><i class="bi bi-hash me-1"></i>Invoice No</th>
                                    <th>Date</th>
                                    <th>Client</th>
                                    <th class="text-end">Taxable (₹)</th>
                                    <th class="text-end">GST (₹)</th>
                                    <th>Status</th>
                                    <!-- <th> Attachment</th> -->

                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="taxRecordsTable">
                                @if ($incomesWithTax->count() > 0)
                                    @foreach ($incomesWithTax as $income)
                                        @php
                                            // Get tax details for this income
                                            $gstTax = $income->taxes->where('tax_type', 'gst')->first();
                                            $gstAmount = $gstTax ? $gstTax->tax_amount : 0;
                                        @endphp
                                        <tr>
                                            <td>
                                                <a href="{{ route('manager.income.view', $income->id) }}" class="fw-bold text-primary text-decoration-none">
                                                    {{ $income->invoice_number ?? ('#INC-' . $income->id) }}
                                                </a>
                                            </td>
                                            <td>{{ date('d-m-Y', strtotime($income->income_date)) }}</td>
                                            <td>{{ $income->company->name ?? 'N/A' }}</td>
                                            <!-- <td>{{ $income->description ?: ($income->client_name ?: 'Income') }}</td> -->
                                            <td class="text-end">{{ number_format($gstTax->taxable_amount, 2) }}</td>
                                            <td class="text-end text-success fw-semibold">{{ number_format($gstAmount, 2) }}</td>
                                            <td>
                                                <span class="badge bg-{{ ($gstTax && $gstTax->payment_status == 'received') ? 'success' : 'warning' }}">
                                                    {{ ucfirst($gstTax->payment_status ?? 'N/A') }}
                                                </span>
                                            </td>
                                            <!-- <td></td> -->
                                            <td class="text-end">
                                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('manager.income.view', $income->id) }}">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="9" class="text-center">No income records with tax found for this
                                            period.</td>
                                    </tr>
                                @endif
                            </tbody>
                            @if ($incomesWithTax->count() > 0)
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="2" class="text-end fw-semibold">Total:</td>
                                    <td class="text-end fw-semibold">
                                        ₹ {{ number_format($totalTaxableAmount, 2) }}
                                    </td>
                                    <td class="text-end text-success fw-semibold">
                                        ₹ {{ number_format($totalGSTCollected, 2) }}
                                    </td>
                                    <td colspan="3"></td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                    <!-- Pagination -->
                    @if ($incomesWithTax->hasPages() || $incomesWithTax->total() > 0)
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                            <div class="text-muted small">
                                Showing {{ $incomesWithTax->firstItem() ?? 0 }} to {{ $incomesWithTax->lastItem() ?? 0 }} of
                                {{ $incomesWithTax->total() }} entries
                            </div>
                            <div class="laravel-pagination">
                                {{ $incomesWithTax->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tax Summary Section -->
            {{-- <div class="card shadow-sm mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Tax Summary</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>GST Collected</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Company</th>
                                            <th class="text-end">Taxable Amount</th>
                                            <th class="text-end">GST Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $gstByCompany = $gstTaxes->groupBy(function ($tax) {
                                                return $tax->taxable->company_id ?? 0;
                                            });
                                        @endphp
                                        @foreach ($gstByCompany as $companyId => $taxes)
                                            @php
                                                $company = \App\Models\Company::find($companyId);
                                                $totalTaxable = $taxes->sum(function ($tax) {
                                                    return $tax->taxable->amount ?? 0;
                                                });
                                                $totalTax = $taxes->sum('tax_amount');
                                            @endphp
                                            <tr>
                                                <td>{{ $company->name ?? 'Unknown' }}</td>
                                                <td class="text-end">₹ {{ number_format($totalTaxable, 2) }}</td>
                                                <td class="text-end">₹ {{ number_format($totalTax, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-light">
                                            <td><strong>Total GST</strong></td>
                                            <td class="text-end">₹
                                                {{ number_format(
                                                    $gstTaxes->sum(function ($tax) {
                                                        return $tax->taxable->amount ?? 0;
                                                    }),
                                                    2,
                                                ) }}
                                            </td>
                                            <td class="text-end"><strong>₹
                                                    {{ number_format($totalGSTCollected, 2) }}</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>TDS Collected</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Company</th>
                                            <th class="text-end">Taxable Amount</th>
                                            <th class="text-end">TDS Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $tdsByCompany = $tdsTaxes->groupBy(function ($tax) {
                                                return $tax->taxable->company_id ?? 0;
                                            });
                                        @endphp
                                        @foreach ($tdsByCompany as $companyId => $taxes)
                                            @php
                                                $company = \App\Models\Company::find($companyId);
                                                $totalTaxable = $taxes->sum(function ($tax) {
                                                    return $tax->taxable->amount ?? 0;
                                                });
                                                $totalTax = $taxes->sum('tax_amount');
                                            @endphp
                                            <tr>
                                                <td>{{ $company->name ?? 'Unknown' }}</td>
                                                <td class="text-end">₹ {{ number_format($totalTaxable, 2) }}</td>
                                                <td class="text-end">₹ {{ number_format($totalTax, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-light">
                                            <td><strong>Total TDS</strong></td>
                                            <td class="text-end">₹
                                                {{ number_format(
                                                    $tdsTaxes->sum(function ($tax) {
                                                        return $tax->taxable->amount ?? 0;
                                                    }),
                                                    2,
                                                ) }}
                                            </td>
                                            <td class="text-end"><strong>₹
                                                    {{ number_format($totalTDSCollected, 2) }}</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}

            <!-- Attach Receipt Modal -->
            <div class="modal fade" id="attachModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title">Attach Receipt/Document</h5>
                                <div class="small-help">Upload supporting documents for tax records.</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form class="row g-3" id="attachReceiptForm" enctype="multipart/form-data">
                                @csrf
                                <div class="col-md-6">
                                    <label class="form-label small">Select Income Record</label>
                                    <select class="form-select form-select-sm" name="income_id" required>
                                        <option value="">Select Income</option>
                                        @foreach ($incomesWithTax as $income)
                                            <option value="{{ $income->id }}">
                                                {{ $income->company->name ?? 'Unknown' }} -
                                                ₹{{ number_format($income->amount, 2) }} -
                                                {{ date('d-m-Y', strtotime($income->income_date)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Document Type</label>
                                    <select class="form-select form-select-sm" name="document_type" required>
                                        <option value="receipt">Receipt</option>
                                        <option value="invoice">Invoice</option>
                                        <option value="tds_certificate">TDS Certificate</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small">Attach File</label>
                                    <input type="file" class="form-control form-control-sm" name="document_file"
                                        accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small">Notes</label>
                                    <textarea class="form-control form-control-sm" name="notes" rows="2"></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                            <button class="btn btn-sm btn-primary" onclick="attachReceipt()">Save</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <script>
        function applyTaxFilters() {
            const form = document.getElementById('taxFilterForm');
            const formData = new FormData(form);
            const params = new URLSearchParams(formData).toString();

            window.location.href = `{{ route('manager.gst-collected') }}?${params}`;
        }

        function exportTaxData(type) {
            const company = document.getElementById('company_filter').value;
            const period = document.getElementById('period_filter').value;
            const taxType = document.getElementById('tax_type_filter').value;

            window.location.href =
                `{{ url('manager/gst-collected/export') }}/${type}?company=${company}&period=${period}&tax_type=${taxType}`;
        }

        function attachReceipt() {
            const form = document.getElementById('attachReceiptForm');
            const formData = new FormData(form);

            fetch('{{ route('manager.gst.attach-receipt') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Receipt attached successfully!');
                        form.reset();
                        $('#attachModal').modal('hide');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while attaching the receipt.');
                });
        }

        function viewIncomeDetails(id) {
            // Redirect to income page and search for this ID/Item
            window.location.href = `{{ url('manager/income') }}?search=${id}`;
        }

        // Initialize with current filters
        document.addEventListener('DOMContentLoaded', function() {
            // Set current period in title
            const periodValue = document.getElementById('period_filter').value;
            const periodDate = new Date(periodValue + '-01');
            document.querySelector('.kpi .value:first-child').textContent =
                periodDate.toLocaleDateString('en-IN', {
                    month: 'short',
                    year: 'numeric'
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
