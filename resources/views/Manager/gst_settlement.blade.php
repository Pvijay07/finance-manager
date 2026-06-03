@extends('Manager.layouts.app')
@section('content')
    <section class="pge">
        <div class="container-fluid">

            <div class="card shadow-sm mb-3">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="mb-0">GST Settlements</h5>
                        <div class="small-help">Record GST payments to government and attach challans for CA verification.</div>
                    </div>
                    <div class="topnav">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('manager.gst') }}">Dashboard</a>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('manager.gst-collected') }}">GST Collected</a>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('manager.taxes') }}">Taxes on Expenses</a>
                        <a class="btn btn-sm btn-primary" href="{{ route('manager.gst-settlements') }}">Settlements</a>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('manager.gst-returns') }}">Returns & tasks</a>
                    </div>
                </div>
            </div>

            <!-- GST Summary Cards -->
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="card kpi shadow-sm">
                        <div class="card-body">
                            <div class="label">Output GST ({{ date('M Y') }})</div>
                            <div class="value">₹ {{ number_format($gstSummary['output_gst'], 2) }}</div>
                            <div class="small-help">From sales/income</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card kpi shadow-sm">
                        <div class="card-body">
                            <div class="label">Eligible ITC ({{ date('M Y') }})</div>
                            <div class="value">₹ {{ number_format($gstSummary['input_gst'], 2) }}</div>
                            <div class="small-help">From expenses</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card kpi shadow-sm">
                        <div class="card-body">
                            <div class="label">Net GST Payable</div>
                            <div class="value">₹ {{ number_format($gstSummary['net_payable'], 2) }}</div>
                            <div class="small-help">Estimate for current period</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Create Settlement Button -->
            <div class="card shadow-sm mb-3">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h6 class="mb-1">Create GST Settlement Payment</h6>
                        <div class="small-help">Record payment to Govt (not an expense). Attach challan.</div>
                    </div>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#settleModal">
                        <i class="fas fa-plus me-1"></i> Create Settlement
                    </button>
                </div>
            </div>

            <!-- Settlements History -->
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Settlement History</span>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary" onclick="exportSettlements('excel')">
                            <i class="fas fa-file-excel me-1"></i> Export
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Tax Period</th>
                                    <th>Company</th>
                                    <th class="text-end">Amount (₹)</th>
                                    <th>Payment Date</th>
                                    <th>Mode</th>
                                    <th>Challan/UTR</th>
                                    <th>Attachment</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($settlements->count() > 0)
                                    @foreach ($settlements as $settlement)
                                        <tr>
                                            <td>{{ date('M Y', strtotime($settlement->tax_period . '-01')) }}</td>
                                            <td>{{ $settlement->company->name }}</td>
                                            <td class="text-end fw-semibold">₹ {{ number_format($settlement->amount, 2) }}</td>
                                            <td>{{ date('d-m-Y', strtotime($settlement->payment_date)) }}</td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ ucfirst(str_replace('_', ' ', $settlement->payment_mode)) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($settlement->challan_number)
                                                    <small>Challan: {{ $settlement->challan_number }}</small>
                                                @endif
                                                @if($settlement->utr_number)
                                                    <br><small>UTR: {{ $settlement->utr_number }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $attachmentCount = $settlement->attachments ? $settlement->attachments->count() : 0;
                                                @endphp
                                                @if($attachmentCount > 0)
                                                    <a href="#" class="small" onclick="viewAttachments({{ $settlement->id }})">
                                                        {{ $attachmentCount }} file{{ $attachmentCount > 1 ? 's' : '' }}
                                                    </a>
                                                @else
                                                    <span class="small text-muted">No files</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $settlement->status == 'paid' ? 'success' : ($settlement->status == 'verified' ? 'primary' : 'warning') }}">
                                                    {{ ucfirst($settlement->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-secondary" onclick="editSettlement({{ $settlement->id }})">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-outline-secondary" onclick="viewSettlement({{ $settlement->id }})">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-file-invoice-dollar display-6"></i>
                                                <p class="mt-2">No GST settlements found.</p>
                                                <p class="small">Create your first GST settlement payment.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Create Settlement Modal -->
            <div class="modal fade" id="settleModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title">Create GST Settlement</h5>
                                <div class="small-help">Attach challan/UTR for CA verification.</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="settlementForm" enctype="multipart/form-data">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Company *</label>
                                        <select class="form-select" name="company_id" required>
                                            <option value="">Select Company</option>
                                            @foreach ($companies as $company)
                                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tax Period *</label>
                                        <select class="form-select" name="tax_period" required>
                                            <option value="">Select Period</option>
                                            @foreach ($months as $month)
                                                <option value="{{ $month['value'] }}">{{ $month['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Amount (₹) *</label>
                                        <input type="number" class="form-control" name="amount" step="0.01" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Payment Date *</label>
                                        <input type="date" class="form-control" name="payment_date" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Payment Mode *</label>
                                        <select class="form-select" name="payment_mode" required>
                                            <option value="netbanking">NetBanking</option>
                                            <option value="upi">UPI</option>
                                            <option value="neft_rtgs">NEFT/RTGS</option>
                                            <option value="cheque">Cheque</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Challan Number</label>
                                        <input type="text" class="form-control" name="challan_number">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">UTR/Reference Number</label>
                                        <input type="text" class="form-control" name="utr_number">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Attach Challan (PDF/Image)</label>
                                        <input type="file" class="form-control" name="attachment" accept=".pdf,.jpg,.jpeg,.png">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Purpose/Comment</label>
                                        <textarea class="form-control" name="purpose_comment" rows="2" placeholder="Optional comments..."></textarea>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary" onclick="saveSettlement()">Save Settlement</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <script>
        function saveSettlement() {
            const form = document.getElementById('settlementForm');
            const formData = new FormData(form);
            
            // Show loading
            const submitBtn = document.querySelector('#settleModal .btn-primary');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            submitBtn.disabled = true;
            
            fetch('{{ route("manager.gst.settlement.store") }}', {
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
                    alert('Settlement created successfully!');
                    $('#settleModal').modal('hide');
                    form.reset();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving the settlement.');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        }
        
        function exportSettlements(type) {
            window.location.href = '{{ route("manager.gst.settlement.export") }}?type=' + type;
        }
        
        function viewAttachments(id) {
            window.location.href = '{{ url("manager/gst/settlement") }}/' + id + '/attachments';
        }
        
        function editSettlement(id) {
            // Implement edit functionality
            alert('Edit settlement ID: ' + id);
        }
        
        function viewSettlement(id) {
            window.location.href = '{{ url("manager/gst/settlement") }}/' + id;
        }
        
        // Initialize modal with today's date
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.querySelector('input[name="payment_date"]').value = today;
        });
    </script>
@endsection