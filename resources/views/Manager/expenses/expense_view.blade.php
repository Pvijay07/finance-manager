@extends('Manager.layouts.app')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 text-dark fw-bold">Expense Details: #EXP-{{ $expense->id }}</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('manager.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('manager.expenses') }}">Expenses</a></li>
                    <li class="breadcrumb-item active" aria-current="page">View</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('manager.expenses') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Expenses
            </a>
        </div>
    </div>

    @php
        $itemSymbol = '₹'; // Defaulting to INR for expenses, adjust if currency is dynamic
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
                            <label class="text-muted small fw-bold mb-1">Expense Name</label>
                            <div class="fw-medium text-dark">{{ $expense->expense_name }}</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold mb-1">Company</label>
                            <div class="fw-medium text-dark">{{ $expense->company?->name ?? 'All Companies' }}</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold mb-1">Category</label>
                            <div class="fw-medium text-dark">{{ $expense->categoryRelation->name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold mb-1">Vendor/Party Name</label>
                            <div class="fw-medium text-dark">{{ $expense->party_name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold mb-1">Status</label>
                            <div>
                                @php
                                    $statusClass = match ($expense->status) {
                                        'paid' => 'bg-success',
                                        'due' => 'bg-warning text-dark',
                                        'overdue' => 'bg-danger',
                                        'settle' => 'bg-secondary',
                                        'convert to tds' => 'bg-info text-dark',
                                        default => 'bg-primary',
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">
                                    {{ ucfirst($expense->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold mb-1">Payment Mode</label>
                            <div class="fw-medium text-dark">{{ ucfirst($expense->payment_mode ?? 'N/A') }}</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold mb-1">Created Date</label>
                            <div class="fw-medium text-dark">{{ \Carbon\Carbon::parse($expense->created_at)->format('d M Y') }}</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold mb-1">Due Date</label>
                            <div class="fw-medium text-dark">{{ $expense->due_date ? \Carbon\Carbon::parse($expense->due_date)->format('d M Y') : 'N/A' }}</div>
                        </div>
                        @if($expense->notes)
                        <div class="col-12">
                            <label class="text-muted small fw-bold mb-1">Notes</label>
                            <div class="p-3 bg-light rounded text-dark">{{ $expense->notes }}</div>
                        </div>
                        @endif
                        @if($expense->settle_notes)
                        <div class="col-12">
                            <label class="text-muted small fw-bold mb-1">Settle Notes</label>
                            <div class="p-3 bg-light rounded text-dark">{{ $expense->settle_notes }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100 bg-light">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0">
                    <h5 class="fw-bold mb-0 text-danger"><i class="fas fa-rupee-sign me-2"></i>Financial Details</h5>
                </div>
                <div class="card-body">
                    @php
                        $rootExpense = $uniqueFamily->sortBy('created_at')->first();
                        $displayBase = $rootExpense->original_amount > 0 ? $rootExpense->original_amount : $rootExpense->actual_amount;
                        
                        $displayTotal = $displayBase;
                        if($rootExpense->taxes && $rootExpense->taxes->count() > 0) {
                            foreach($rootExpense->taxes as $tax) {
                                $originalTaxAmount = $displayBase * ($tax->tax_percentage / 100);
                                if ($tax->tax_type == 'tds') {
                                    $displayTotal -= $originalTaxAmount;
                                } else {
                                    $displayTotal += $originalTaxAmount;
                                }
                            }
                        }
                        $totalPaidAmount = $uniqueFamily->sum('planned_amount');
                        $calculatedBalance = max(0, $displayTotal - $totalPaidAmount);
                    @endphp
                    <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                        <span class="text-muted">Base Amount:</span>
                        <span class="fw-bold">{{ $itemSymbol }}{{ fmod($displayBase, 1) == 0 ? number_format($displayBase, 0, '.', '') : number_format($displayBase, 2) }}</span>
                    </div>
                    
                    @if($rootExpense->taxes && $rootExpense->taxes->count() > 0)
                        @foreach($rootExpense->taxes as $tax)
                            @php
                                $originalTaxAmount = $displayBase * ($tax->tax_percentage / 100);
                            @endphp
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">{{ strtolower($tax->tax_type) }} ({{ number_format($tax->tax_percentage, 2) }}%):</span>
                                <span class="fw-medium text-{{ $tax->tax_type == 'tds' ? 'danger' : 'primary' }}">
                                    {{ $tax->tax_type == 'tds' ? '-' : '+' }}{{ $itemSymbol }}{{ fmod($originalTaxAmount, 1) == 0 ? number_format($originalTaxAmount, 0, '.', '') : number_format($originalTaxAmount, 2) }}
                                </span>
                            </div>
                        @endforeach
                    @endif

                    <div class="d-flex justify-content-between mt-4 pt-3 border-top border-dark">
                        <span class="text-dark fw-bold h5 mb-0">Total payable Amount:</span>
                        <span class="text-danger fw-bold h5 mb-0">{{ $itemSymbol }}{{ fmod($displayTotal, 1) == 0 ? number_format($displayTotal, 0, '.', '') : number_format($displayTotal, 2) }}</span>
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
                            <th>Expense ID</th>
                            <th>Total Amount</th>
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
                                <td>#EXP-{{ $split->id }}</td>
                                <td>{{ $itemSymbol }}{{ number_format($split->planned_amount, 2) }}</td>
                                <td class="fw-bold text-danger">{{ $itemSymbol }}{{ number_format($split->actual_amount ?: $split->planned_amount, 2) }}</td>
                                <td>
                                    @php
                                        $splitStatusClass = match ($split->status) {
                                            'paid' => 'bg-success',
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
                            @if($split->settle_notes)
                                @php
                                    $showBalance = $loop->last ? $calculatedBalance : $split->balance_amount;
                                @endphp
                                @if($showBalance > 0)
                                <tr class="table-light">
                                    <td colspan="2"></td>
                                    <td colspan="2">
                                        <span class="fw-bold text-secondary">{{ $itemSymbol }}{{ fmod($showBalance, 1) == 0 ? number_format($showBalance, 0, '.', '') : number_format($showBalance, 2) }}</span>
                                        <div class="text-muted small mt-1">({{ $split->settle_notes }})</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">settled</span>
                                    </td>
                                    <td colspan="3"></td>
                                </tr>
                                @endif
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
