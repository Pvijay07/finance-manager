@extends('Manager.layouts.app')
@section('content')
    <section class="pge">
        <div class="container-fluid">

            <div class="card shadow-sm mb-3">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="mb-0">GST & TDS</h5>
                        <div class="small-help">Simple internal tracking for CA handoff (invoices, taxes on expenses, filing
                            tasks).</div>
                    </div>
                    <div class="topnav">
                        <a class="btn btn-sm btn-primary" href="{{ route('manager.gst') }}">Dashboard</a>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('manager.gst-collected') }}">GST
                            Collected</a>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('manager.taxes') }}">GST Payable</a>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('manager.tds') }}">TDS on Income</a>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('manager.tdsExpense') }}">TDS on Expense</a>
                    </div>
                </div>
            </div>

            <!-- Dashboard Cards -->
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="card kpi shadow-sm">
                        <div class="card-body">
                            <div class="label">Tax Period</div>
                            <div class="value fw-bold">{{ $currentPeriod }}</div>
                            <div class="small-help">Filter by company/period.</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card kpi shadow-sm">
                        <div class="card-body">
                            <div class="label">Output GST</div>
                            <div class="value fw-bold" id="kpi_output">₹ {{ number_format($totalOutputGST, 2) }}</div>
                            <div class="small-help">GST from sales/income.</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card kpi shadow-sm">
                        <div class="card-body">
                            <div class="label">Input GST (ITC)</div>
                            <div class="value fw-bold" id="kpi_itc">₹ {{ number_format($totalInputGST, 2) }}</div>
                            <div class="small-help">GST on purchases/expenses.</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card kpi shadow-sm">
                        <div class="card-body">
                            <div class="label">Net GST Payable</div>
                            <div class="value fw-bold" id="kpi_net">
                                ₹ {{ number_format($netGSTPayable, 2) }}
                                @if ($netGSTPayable < 0)
                                    <span class="badge bg-success ms-1">Receivable</span>
                                @elseif($netGSTPayable > 0)
                                    <span class="badge bg-warning ms-1">Payable</span>
                                @else
                                    <span class="badge bg-secondary ms-1">Balanced</span>
                                @endif
                            </div>
                            <div class="small-help">Output − Input GST</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Filters -->
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h6 class="mb-2">Quick Filters</h6>
                    <form class="row g-2 align-items-end" id="filterForm">
                        @csrf
                        <div class="col-md-3">
                            <label class="form-label small">Company</label>
                            <select class="form-select form-select-sm" name="company_id" id="company_filter">
                                <option value="all">All</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Tax Period</label>
                            <select class="form-select form-select-sm" name="period" id="period_filter">
                                @foreach ($months as $month)
                                    <option value="{{ $month['value'] }}"
                                        {{ date('Y-m') == $month['value'] ? 'selected' : '' }}>
                                        {{ $month['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">GSTIN</label>
                            <select class="form-select form-select-sm" name="gstin" id="gstin_filter">
                                <option value="all">All GSTINs</option>
                                @foreach ($companies as $company)
                                    @if ($company->gstin)
                                        <option value="{{ $company->gstin }}">{{ $company->gstin }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-grid">
                            <button type="button" class="btn btn-sm btn-primary" onclick="applyFilters()">Apply</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="row g-3">
                <!-- Summary Column -->
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Period Summary</span>
                            <span class="small-help">For CA review.</span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Component</th>
                                            <th>Description</th>
                                            <th class="text-end">Amount (₹)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="pill bg-light border">Output GST</span></td>
                                            <td>GST collected from sales/income</td>
                                            <td class="text-end" id="tbl_output">₹ {{ number_format($totalOutputGST, 2) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><span class="pill bg-light border">Input GST (ITC)</span></td>
                                            <td>GST paid on purchases/expenses</td>
                                            <td class="text-end" id="tbl_itc">₹ {{ number_format($totalInputGST, 2) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><span class="pill bg-light border">TDS Deductions</span></td>
                                            <td>TDS deducted on payments</td>
                                            <td class="text-end">₹ {{ number_format($totalTDS, 2) }}</td>
                                        </tr>
                                        <tr
                                            class="{{ $netGSTPayable > 0 ? 'table-warning' : ($netGSTPayable < 0 ? 'table-success' : 'table-light') }}">
                                            <td>
                                                <span
                                                    class="pill {{ $netGSTPayable > 0 ? 'bg-warning' : ($netGSTPayable < 0 ? 'bg-success text-white' : 'bg-light border') }}">
                                                    Net GST
                                                    {{ $netGSTPayable > 0 ? 'Payable' : ($netGSTPayable < 0 ? 'Receivable' : 'Position') }}
                                                </span>
                                            </td>
                                            <td>Output GST − Input GST</td>
                                            <td class="text-end fw-semibold" id="tbl_net">₹
                                                {{ number_format(abs($netGSTPayable), 2) }}</td>
                                        </tr>
                                        @if ($totalTDS > 0)
                                            <tr>
                                                <td colspan="2" class="small text-muted">Note: TDS of
                                                    ₹{{ number_format($totalTDS, 2) }} is separate from GST calculation
                                                </td>
                                                <td class="text-end small text-muted">Overall:
                                                    ₹{{ number_format($netPosition, 2) }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            <div class="small-help">Note: For simplicity you can skip blocked-credit logic; CA will
                                finalize
                                compliance.</div>
                        </div>
                    </div>
                </div>

                <!-- Quick Links Column -->
                <div class="col-lg-4">
                    <div class="card shadow-sm mb-3">
                        <div class="card-header">Quick Actions</div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('manager.gst-collected') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-receipt"></i> View GST Collected Details
                                </a>
                                <a href="{{ route('manager.taxes') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-cash-stack"></i> View Taxes on Expenses
                                </a>
                                <button class="btn btn-outline-secondary" onclick="exportData('excel')">
                                    <i class="bi bi-file-earmark-excel"></i> Export Summary (Excel)
                                </button>
                                <button class="btn btn-outline-secondary" onclick="exportData('pdf')">
                                    <i class="bi bi-file-earmark-pdf"></i> Export Summary (PDF)
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="card shadow-sm">
                        <div class="card-header">Recent Activity</div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                @if ($gstIncomes->count() > 0)
                                    <div class="list-group-item px-0 py-2">
                                        <div class="d-flex w-100 justify-content-between">
                                            <small class="text-muted">Income with GST</small>
                                            <small>{{ $gstIncomes->count() }} entries</small>
                                        </div>
                                        <small>Total: ₹{{ number_format($gstIncomes->sum('amount'), 2) }}</small>
                                    </div>
                                @endif
                                @if ($gstExpenses->count() > 0)
                                    <div class="list-group-item px-0 py-2">
                                        <div class="d-flex w-100 justify-content-between">
                                            <small class="text-muted">Expenses with GST</small>
                                            <small>{{ $gstExpenses->count() }} entries</small>
                                        </div>
                                        <small>Total: ₹{{ number_format($gstExpenses->sum('actual_amount'), 2) }}</small>
                                    </div>
                                @endif
                                @if ($tdsIncomes->count() > 0)
                                    <div class="list-group-item px-0 py-2">
                                        <div class="d-flex w-100 justify-content-between">
                                            <small class="text-muted">Income with TDS</small>
                                            <small>{{ $tdsIncomes->count() }} entries</small>
                                        </div>
                                    </div>
                                @endif
                                @if ($tdsExpenses->count() > 0)
                                    <div class="list-group-item px-0 py-2">
                                        <div class="d-flex w-100 justify-content-between">
                                            <small class="text-muted">Expenses with TDS</small>
                                            <small>{{ $tdsExpenses->count() }} entries</small>
                                        </div>
                                    </div>
                                @endif
                                <div class="list-group-item px-0 py-2">
                                    <div class="d-flex w-100 justify-content-between">
                                        <small class="text-muted">Last Updated</small>
                                        <small>{{ date('d M Y') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <script>
        function fmtINR(n) {
            try {
                return new Intl.NumberFormat('en-IN').format(n);
            } catch (e) {
                return n;
            }
        }

        // Initialize with PHP data
        (function() {
            const out = {{ $totalOutputGST }};
            const itc = {{ $totalInputGST }};
            const net = {{ $netGSTPayable }};
            const tds = {{ $totalTDS }};
            const overall = {{ $netPosition }};

            // Format values
            document.getElementById('tbl_output').textContent = '₹ ' + fmtINR(out);
            document.getElementById('tbl_itc').textContent = '₹ ' + fmtINR(itc);
            document.getElementById('tbl_net').textContent = '₹ ' + fmtINR(Math.abs(net));

            document.getElementById('kpi_output').textContent = '₹ ' + fmtINR(out);
            document.getElementById('kpi_itc').textContent = '₹ ' + fmtINR(itc);
            document.getElementById('kpi_net').textContent = '₹ ' + fmtINR(Math.abs(net));

            // Add badge to net amount
            const netElement = document.getElementById('kpi_net');
            let badge = '';
            if (net > 0) {
                badge = '<span class="badge bg-warning ms-1">Payable</span>';
            } else if (net < 0) {
                badge = '<span class="badge bg-success ms-1">Receivable</span>';
            } else {
                badge = '<span class="badge bg-secondary ms-1">Balanced</span>';
            }
            netElement.innerHTML = '₹ ' + fmtINR(Math.abs(net)) + ' ' + badge;
        })();

        // Filter functions
        function applyFilters() {
            const formData = new FormData(document.getElementById('filterForm'));

            fetch('{{ route('manager.gst.filter') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update dashboard cards
                        document.getElementById('kpi_output').textContent = '₹ ' + fmtINR(data.outputGST);
                        document.getElementById('kpi_itc').textContent = '₹ ' + fmtINR(data.itc);
                        document.getElementById('kpi_net').textContent = '₹ ' + fmtINR(data.netPayable);

                        document.getElementById('tbl_output').textContent = fmtINR(data.outputGST);
                        document.getElementById('tbl_itc').textContent = fmtINR(data.itc);
                        document.getElementById('tbl_net').textContent = fmtINR(data.netPayable);

                        // Update period if changed
                        if (data.period) {
                            document.querySelector('.kpi .value:first-child').textContent = data.period;
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function exportData(type) {
            const company = document.getElementById('company_filter').value;
            const period = document.getElementById('period_filter').value;
            const gstin = document.getElementById('gstin_filter').value;

            window.location.href =
                `{{ url('https://xhtmlreviews.in/beta-finance/manager/gst/export') }}/${type}?company=${company}&period=${period}&gstin=${gstin}`;
        }
    </script>
@endsection
