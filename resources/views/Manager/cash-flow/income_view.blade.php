@extends('Manager.layouts.app')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 text-dark fw-bold">Income Details: {{ $income->invoice_number ?? ('#INC-' . $income->id) }}</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('manager.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('manager.balances.index') }}">Income</a></li>
                    <li class="breadcrumb-item active" aria-current="page">View</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('manager.balances.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Income List
            </a>
        </div>
    </div>

    @php
        $itemCurrency = $income->currency ?? ($income->invoice->currency ?? 'INR');
        $itemSymbol = ($itemCurrency == 'USD' ? '$' : ($itemCurrency == 'EUR' ? '€' : ($itemCurrency == 'GBP' ? '£' : '₹')));
    @endphp

    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="fw-bold mb-0 text-primary"><i class="fas fa-info-circle me-2"></i>Basic Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold mb-1">Company</label>
                            <div class="fw-medium text-dark">{{ $income->company?->name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold mb-1">Client Name</label>
                            <div class="fw-medium text-dark">{{ $income->client_name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold mb-1">Income Type</label>
                            <div>
                                <span class="badge {{ $income->invoice_id ? 'bg-info' : 'bg-secondary' }}">
                                    {{ $income->invoice_id ? 'Standard' : 'Non-Standard' }}
                                </span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold mb-1">Status</label>
                            <div>
                                @php
                                    $statusClass = match ($income->status) {
                                        'received' => 'bg-success',
                                        'due' => 'bg-warning text-dark',
                                        'overdue' => 'bg-danger',
                                        'settle' => 'bg-secondary',
                                        'convert to tds' => 'bg-info text-dark',
                                        default => 'bg-primary',
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">
                                    {{ ucfirst($income->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold mb-1">Created Date</label>
                            <div class="fw-medium text-dark">{{ \Carbon\Carbon::parse($income->created_at)->format('d M Y') }}</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold mb-1">Due Date</label>
                            <div class="fw-medium text-dark">{{ $income->due_date ? \Carbon\Carbon::parse($income->due_date)->format('d M Y') : 'N/A' }}</div>
                        </div>
                        @if($income->notes)
                        <div class="col-12">
                            <label class="text-muted small fw-bold mb-1">Notes</label>
                            <div class="p-3 bg-light rounded text-dark">{{ $income->notes }}</div>
                        </div>
                        @endif
                        @if($income->settle_notes)
                        <div class="col-12">
                            <label class="text-muted small fw-bold mb-1">Settle Notes</label>
                            <div class="p-3 bg-light rounded text-dark">{{ $income->settle_notes }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100 bg-light">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0">
                    <h5 class="fw-bold mb-0 text-success"><i class="fas fa-rupee-sign me-2"></i>Financial Details</h5>
                </div>
                <div class="card-body">
                    @php
                        $rootIncome = $uniqueFamily->sortBy('created_at')->first();
                        $displayBase = $rootIncome->original_amount > 0 ? $rootIncome->original_amount : ($rootIncome->actual_amount > 0 ? $rootIncome->actual_amount : $rootIncome->amount);
                        $displayTotal = $rootIncome->schedule_amount > 0 ? $rootIncome->schedule_amount : ($rootIncome->planned_amount > 0 ? $rootIncome->planned_amount : $original_total_amount);
                    @endphp
                    <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                        <span class="text-muted">Base Amount:</span>
                        <span class="fw-bold">{{ $itemSymbol }}{{ number_format($displayBase, 2) }}</span>
                    </div>
                    
                    @if($rootIncome->taxes && $rootIncome->taxes->count() > 0)
                        @foreach($rootIncome->taxes as $tax)
                            @php
                                $originalTaxAmount = $displayBase * ($tax->tax_percentage / 100);
                            @endphp
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted text-uppercase">{{ $tax->tax_type }} ({{ $tax->tax_percentage }}%):</span>
                                <span class="fw-medium text-{{ $tax->tax_type == 'tds' ? 'danger' : 'primary' }}">
                                    {{ $tax->tax_type == 'tds' ? '-' : '+' }}{{ $itemSymbol }}{{ number_format($originalTaxAmount, 2) }}
                                </span>
                            </div>
                        @endforeach
                    @endif

                    <div class="d-flex justify-content-between mt-4 pt-3 border-top border-dark">
                        <span class="text-dark fw-bold h5 mb-0">Total Amount:</span>
                        <span class="text-success fw-bold h5 mb-0">{{ $itemSymbol }}{{ number_format($displayTotal, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Split History Section -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white pt-4 pb-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-list-ol me-2 text-primary"></i>Split Payment History</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Split #</th>
                            <th>Income ID</th>
                            <th>Amount</th>
                            <th>Base Amount</th>
                            <th>Status</th>
                            <th>Created Date</th>
                            <th>Due Date</th>
                            <th>Paid Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($uniqueFamily->sortBy('created_at') as $index => $split)
                            <tr>
                                <td class="ps-4 fw-medium">{{ $index + 1 }}</td>
                                <td>#{{ $split->invoice_number ?? ('INC-' . $split->id) }}</td>
                                <td class="fw-bold text-success">{{ $itemSymbol }}{{ number_format($split->amount, 2) }}</td>
                                <td>{{ $itemSymbol }}{{ number_format($split->actual_amount, 2) }}</td>
                                <td>
                                    @php
                                        $splitStatusClass = match ($split->status) {
                                            'received' => 'bg-success',
                                            'due' => 'bg-warning text-dark',
                                            'overdue' => 'bg-danger',
                                            'settle' => 'bg-secondary',
                                            'convert to tds' => 'bg-info text-dark',
                                            default => 'bg-primary',
                                        };
                                    @endphp
                                    <span class="badge {{ $splitStatusClass }}">{{ ucfirst($split->status) }}</span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($split->created_at)->format('d M Y') }}</td>
                                <td>{{ $split->due_date ? \Carbon\Carbon::parse($split->due_date)->format('d M Y') : 'N/A' }}</td>
                                <td>{{ $split->paid_date ? \Carbon\Carbon::parse($split->paid_date)->format('d M Y') : 'N/A' }}</td>
                            </tr>
                            @if($split->balance_amount > 0 && $split->settle_notes)
                                <tr class="table-light">
                                    <td colspan="2"></td>
                                    <td colspan="2">
                                        <span class="fw-bold text-secondary">{{ $itemSymbol }}{{ number_format($split->balance_amount, 2) }}</span>
                                        <div class="text-muted small mt-1">({{ $split->settle_notes }})</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">Settled</span>
                                    </td>
                                    <td colspan="3"></td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No split history available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
