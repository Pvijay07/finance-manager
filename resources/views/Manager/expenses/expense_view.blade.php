@extends('Manager.layouts.app')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 text-dark fw-bold">Expense Details: {{ $expense->expense_number }}</h4>
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
                        
                        $originalSum = $uniqueFamily->sum('planned_amount');
                        $displayBase = $rootExpense->original_amount > 0 ? $rootExpense->original_amount : $rootExpense->schedule_amount;
                        
                        $rootGst = $rootExpense->taxes->where('tax_type', 'gst')->first();
                        $rootTds = $rootExpense->taxes->where('tax_type', 'tds')->first();
                        $gstPercentage = $rootGst ? $rootGst->tax_percentage : 0;
                        $tdsPercentage = $rootTds ? $rootTds->tax_percentage : 0;

                        if (!$displayBase || $displayBase <= 0) {
                            $displayBase = $originalSum;
                            if ($gstPercentage > 0 || $tdsPercentage > 0) {
                                $displayBase = $originalSum / (1 + ($gstPercentage - $tdsPercentage) / 100);
                            }
                        }
                        
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

                    @php
                        $displayGst = 0;
                        if ($rootExpense->taxes && $rootExpense->taxes->count() > 0) {
                            $gstTaxObj = $rootExpense->taxes->where('tax_type', 'gst')->first();
                            if ($gstTaxObj) {
                                $displayGst = $displayBase * ($gstTaxObj->tax_percentage / 100);
                            }
                        }
                        $displayPlanned = $displayBase + $displayGst;
                    @endphp
                    <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                        <span class="text-muted">Planned Amount:</span>
                        <span class="fw-bold">{{ $itemSymbol }}{{ fmod($displayPlanned, 1) == 0 ? number_format($displayPlanned, 0, '.', '') : number_format($displayPlanned, 2) }}</span>
                    </div>

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
                            <th>Payable Amt</th>
                            <th>Base Amount (-TDS)</th>
                            <th>GST Amount</th>
                            <th>Status</th>
                            <th>Paid Date</th>
                            <th>Due Date</th>
                            <th>TDS Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $formatAmount = function($amt) {
                                return fmod($amt, 1) == 0 ? number_format($amt, 0, '.', '') : number_format($amt, 2);
                            };
                             $totalPaidAmt = 0;
                             $totalBaseAmount = 0;
                             $totalBaseAmountMinusTds = 0;
                             $totalGstAmount = 0;
                             $totalTdsAmount = 0;
                        @endphp
                        @forelse($uniqueFamily->sortBy('created_at') as $index => $split)
                            @php
                                 $rowGstAmount = $split->taxes->where('tax_type', 'gst')->sum('tax_amount');
                                 $rowTdsTax = $split->taxes->where('tax_type', 'tds')->first();
                                 $rowTdsAmount = $rowTdsTax ? $rowTdsTax->tax_amount : 0;
                                 
                                 $displayAmount = ($split->is_split || $split->parent_id) ? ($split->status === 'settle' ? $split->schedule_amount : ($split->planned_amount - $rowTdsAmount)) : $displayTotal;
                                 $rowBaseAmount = $split->actual_amount > 0 ? $split->actual_amount : ($split->original_amount ?? 0);
                                 
                                
                                $rowTdsStatus = 'pending';
                                if ($rowTdsTax) {
                                    $paymentStatus = strtolower($rowTdsTax->payment_status);
                                    if ($paymentStatus === 'received' || $paymentStatus === 'paid') {
                                        $rowTdsStatus = 'Paid';
                                    } elseif ($paymentStatus === 'not_received' || $paymentStatus === 'pending') {
                                        $rowTdsStatus = 'pending';
                                    } else {
                                        $rowTdsStatus = ucfirst($paymentStatus);
                                    }
                                }

                                 $totalPaidAmt += $displayAmount;
                                 $totalBaseAmount += $rowBaseAmount;
                                 $totalGstAmount += $rowGstAmount;
                                 $totalTdsAmount += $rowTdsAmount;
                                 $totalBaseAmountMinusTds += ($rowBaseAmount - $rowTdsAmount);

                                $splitStatusClass = match ($split->status) {
                                    'paid' => 'bg-success',
                                    'due' => 'bg-warning text-dark',
                                    'overdue' => 'bg-danger',
                                    'settle', 'settled' => 'bg-secondary',
                                    'convert to tds' => 'bg-info text-dark',
                                    default => 'bg-primary',
                                };

                                $tdsStatusClass = match (strtolower($rowTdsStatus)) {
                                    'paid', 'received' => 'bg-success',
                                    'pending', 'not_received' => 'bg-warning text-dark',
                                    'n/a' => 'bg-light text-muted',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <tr>
                                <td class="ps-4 fw-medium">{{ $index + 1 }}</td>
                                <td>
                                    <a href="{{ route('manager.expense.view', $split->id) }}" class="text-decoration-none fw-medium">{{ $split->expense_number }}</a>
                                </td>
                                <td class="fw-bold text-dark">
                                    {{ $formatAmount($displayAmount) }}
                                    @if($split->status === 'settle' && $split->settle_notes)
                                        <div class="text-muted small mt-1">({{ $split->settle_notes }})</div>
                                    @endif
                                </td>
                                <td class="fw-bold text-danger">{{ $formatAmount($rowBaseAmount - $rowTdsAmount) }}</td>
                                <td class="fw-bold text-primary">{{ $formatAmount($rowGstAmount) }}</td>
                                <td>
                                    <span class="badge {{ $splitStatusClass }}">{{ ucfirst($split->status) }}</span>
                                </td>
                                <td>{{ $split->paid_date ? \Carbon\Carbon::parse($split->paid_date)->format('n/j/Y') : 'N/A' }}</td>
                                <td>{{ $split->due_date ? \Carbon\Carbon::parse($split->due_date)->format('n/j/Y') : 'N/A' }}</td>
                                <td class="fw-bold text-danger">{{ $formatAmount($rowTdsAmount) }}</td>
                                <td>
                                    @if($rowTdsTax)
                                        <span class="badge {{ $tdsStatusClass }}">{{ ucfirst($rowTdsStatus) }}</span>
                                    @else
                                        <span class="text-muted small">N/A</span>
                                    @endif
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">No split history available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($uniqueFamily->isNotEmpty())
                        <tfoot>
                            <tr class="table-light fw-bold border-top border-dark">
                                <td class="ps-4"></td>
                                <td></td>
                                <td class="text-dark">{{ $itemSymbol }}{{ $formatAmount($totalPaidAmt) }}</td>
                                 <td class="text-danger">{{ $itemSymbol }}{{ $formatAmount($totalBaseAmountMinusTds) }}</td>
                                <td class="text-primary">{{ $itemSymbol }}{{ $formatAmount($totalGstAmount) }}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-danger">{{ $itemSymbol }}{{ $formatAmount($totalTdsAmount) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
