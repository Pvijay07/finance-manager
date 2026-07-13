@extends('Manager.layouts.app')

@section('content')
<div class="manager-panel">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Loan Details: {{ $loan->loan_number }}</h4>
        <div>
            @if($loan->status !== 'closed')
            <button class="btn btn-success" data-toggle="modal" data-target="#paymentModal">Record Payment</button>
            <form action="{{ route('manager.loan-management.close', $loan->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to close this loan?');">
                @csrf
                <button type="submit" class="btn btn-danger">Close Loan</button>
            </form>
            @endif
            <a href="{{ route('manager.loan-management.index', ['direction' => $loan->direction]) }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h6 class="text-muted">Principal Amount</h6>
                    <h4>₹{{ number_format($loan->principal_amount, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h6 class="text-muted">Outstanding Principal</h6>
                    <h4>₹{{ number_format($loan->outstanding_principal, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h6 class="text-muted">Interest Due</h6>
                    <h4>₹{{ number_format($loan->interest_due, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h6 class="text-muted">Total Balance Amount</h6>
                    <h4 class="text-danger">₹{{ number_format($loan->balance_amount, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs mb-4" id="loanTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="overview-tab" data-toggle="tab" href="#overview" role="tab">Overview</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="ledger-tab" data-toggle="tab" href="#ledger" role="tab">Ledger (Transactions)</a>
        </li>
        @if($loan->enable_emi)
        <li class="nav-item">
            <a class="nav-link" id="emi-tab" data-toggle="tab" href="#emi" role="tab">EMI Schedule</a>
        </li>
        @endif
    </ul>

    <div class="tab-content">
        <!-- Overview Tab -->
        <div class="tab-pane fade show active" id="overview" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr><th width="40%">Direction</th><td>{{ ucfirst($loan->direction) }}</td></tr>
                                <tr><th>Party</th><td>{{ $loan->party->name ?? 'N/A' }}</td></tr>
                                <tr><th>Company</th><td>{{ $loan->company->name ?? 'N/A' }}</td></tr>
                                <tr><th>Loan Date</th><td>{{ $loan->loan_date->format('d M Y') }}</td></tr>
                                <tr><th>Status</th><td>
                                    <span class="badge badge-{{ $loan->status === 'active' ? 'primary' : ($loan->status === 'closed' ? 'success' : 'warning') }}">
                                        {{ ucfirst(str_replace('_', ' ', $loan->status)) }}
                                    </span>
                                </td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr><th width="40%">Interest Rate</th><td>{{ $loan->interest_applicable ? $loan->interest_rate . '% ' . ucfirst($loan->interest_frequency) : 'N/A' }}</td></tr>
                                <tr><th>EMI Enabled</th><td>{{ $loan->enable_emi ? 'Yes' : 'No' }}</td></tr>
                                @if($loan->enable_emi)
                                <tr><th>EMI Amount</th><td>₹{{ number_format($loan->emi_amount, 2) }}</td></tr>
                                <tr><th>Total Installments</th><td>{{ $loan->emi_count }}</td></tr>
                                @endif
                                <tr><th>Processing Fee</th><td>₹{{ number_format($loan->processing_fee, 2) }}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ledger Tab -->
        <div class="tab-pane fade" id="ledger" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Total (₹)</th>
                                <th>Principal (₹)</th>
                                <th>Interest (₹)</th>
                                <th>TDS (₹)</th>
                                <th>Mode/Ref</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($loan->transactions as $txn)
                                <tr>
                                    <td>{{ $txn->transaction_date->format('d M Y') }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $txn->type)) }}</td>
                                    <td class="font-weight-bold">{{ number_format($txn->total_amount, 2) }}</td>
                                    <td>{{ number_format($txn->principal_component, 2) }}</td>
                                    <td>{{ number_format($txn->interest_component, 2) }}</td>
                                    <td>{{ number_format($txn->tds_component, 2) }}</td>
                                    <td>{{ $txn->payment_mode }} <br><small class="text-muted">{{ $txn->reference_number }}</small></td>
                                </tr>
                            @endforeach
                            @if($loan->transactions->isEmpty())
                                <tr><td colspan="7" class="text-center">No transactions found.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- EMI Tab -->
        @if($loan->enable_emi)
        <div class="tab-pane fade" id="emi" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Due Date</th>
                                <th>EMI (₹)</th>
                                <th>Principal (₹)</th>
                                <th>Interest (₹)</th>
                                <th>Remaining Bal (₹)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($loan->amortizationSchedules as $emi)
                                <tr>
                                    <td>{{ $emi->installment_number }}</td>
                                    <td>{{ $emi->due_date->format('d M Y') }}</td>
                                    <td>{{ number_format($emi->emi_amount, 2) }}</td>
                                    <td>{{ number_format($emi->principal_component, 2) }}</td>
                                    <td>{{ number_format($emi->interest_component, 2) }}</td>
                                    <td>{{ number_format($emi->remaining_balance, 2) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $emi->status === 'paid' ? 'success' : ($emi->status === 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($emi->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Record Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('manager.loan-management.payment', $loan->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Record Payment</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    
                    @if($loan->enable_emi && $loan->next_emi)
                        <div class="alert alert-info">
                            <strong>Next EMI Due:</strong> {{ $loan->next_emi->due_date->format('d M Y') }}<br>
                            <strong>EMI Amount:</strong> ₹{{ number_format($loan->next_emi->emi_amount, 2) }}
                            <input type="hidden" name="schedule_id" value="{{ $loan->next_emi->id }}">
                        </div>
                    @endif

                    <div class="form-group">
                        <label>Transaction Date *</label>
                        <input type="date" name="transaction_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Total Payment Amount (₹) *</label>
                        <input type="number" step="0.01" name="total_amount" class="form-control" value="{{ $loan->enable_emi && $loan->next_emi ? $loan->next_emi->emi_amount : '' }}" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Principal Component</label>
                            <input type="number" step="0.01" name="principal_component" class="form-control" value="{{ $loan->enable_emi && $loan->next_emi ? $loan->next_emi->principal_component : '0' }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Interest Component</label>
                            <input type="number" step="0.01" name="interest_component" class="form-control" value="{{ $loan->enable_emi && $loan->next_emi ? $loan->next_emi->interest_component : '0' }}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Payment Mode</label>
                        <select name="payment_mode" class="form-control">
                            <option value="bank">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="upi">UPI</option>
                            <option value="cash">Cash</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Reference Number</label>
                        <input type="text" name="reference_number" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
