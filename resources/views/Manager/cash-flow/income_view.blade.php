@extends('Manager.layouts.app')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 text-dark fw-bold">Income Details: {{ $income->invoice_number ?? ('#INC-' . $income->getRootParentId()) }}</h4>
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
        $itemSymbol = '₹';
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
                        <div class="col-sm-6 mt-4">
                            <label class="text-muted small fw-bold mb-1">Paid Date</label>
                            <div class="fw-medium text-dark">{{ $income->paid_date ? \Carbon\Carbon::parse($income->paid_date)->format('d M Y') : (($income->status === 'received' || $income->status === 'settle') && $income->income_date ? \Carbon\Carbon::parse($income->income_date)->format('d M Y') : 'N/A') }}</div>
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
                        
                        $originalSum = $uniqueFamily->sum('amount');
                        $displayBase = $rootIncome->original_amount > 0 ? $rootIncome->original_amount : $rootIncome->schedule_amount;
                        
                        $rootGst = $rootIncome->taxes->where('tax_type', 'gst')->first();
                        $rootTds = $rootIncome->taxes->where('tax_type', 'tds')->first();
                        $gstPercentage = $rootGst ? $rootGst->tax_percentage : 0;
                        $tdsPercentage = $rootTds ? $rootTds->tax_percentage : 0;

                        if (!$displayBase || $displayBase <= 0) {
                            $displayBase = $originalSum;
                            if ($gstPercentage > 0 || $tdsPercentage > 0) {
                                $displayBase = $originalSum / (1 + ($gstPercentage - $tdsPercentage) / 100);
                            }
                        }
                        
                        $displayTotal = $displayBase;
                        if($rootIncome->taxes && $rootIncome->taxes->count() > 0) {
                            foreach($rootIncome->taxes as $tax) {
                                $originalTaxAmount = $displayBase * ($tax->tax_percentage / 100);
                                if ($tax->tax_type == 'tds') {
                                    $displayTotal -= $originalTaxAmount;
                                } else {
                                    $displayTotal += $originalTaxAmount;
                                }
                            }
                        }
                        if($itemCurrency === 'USD') {
                            $exactConversionCost = $displayBase * 0.015;
                            $displayTotal -= $exactConversionCost;
                            $original_conversion_cost = $exactConversionCost;
                        } elseif(isset($original_conversion_cost) && $original_conversion_cost > 0) {
                            $displayTotal -= $original_conversion_cost;
                        }
                    @endphp
                    <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                        <span class="text-muted">Base Amount:</span>
                        <span class="fw-bold">₹{{ number_format($displayBase, 2) }}</span>
                    </div>
                    
                    @if($rootIncome->taxes && $rootIncome->taxes->count() > 0)
                        @foreach($rootIncome->taxes as $tax)
                            @php
                                $originalTaxAmount = $displayBase * ($tax->tax_percentage / 100);
                            @endphp
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted text-uppercase">{{ $tax->tax_type }} ({{ $tax->tax_percentage }}%):</span>
                                <span class="fw-medium text-{{ $tax->tax_type == 'tds' ? 'danger' : 'primary' }}">
                                    {{ $tax->tax_type == 'tds' ? '-' : '+' }}₹{{ number_format($originalTaxAmount, 2) }}
                                </span>
                            </div>
                        @endforeach
                    @endif
                    
                    @if(isset($original_conversion_cost) && $original_conversion_cost > 0)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Conversion Cost:</span>
                            <span class="fw-medium text-danger">
                                -₹{{ number_format($original_conversion_cost, 2) }}
                            </span>
                        </div>
                    @endif

                    @php
                        $displayGst = 0;
                        if ($rootIncome->taxes && $rootIncome->taxes->count() > 0) {
                            $gstTaxObj = $rootIncome->taxes->where('tax_type', 'gst')->first();
                            if ($gstTaxObj) {
                                $displayGst = $displayBase * ($gstTaxObj->tax_percentage / 100);
                            }
                        }
                        $displayPlanned = $displayBase + $displayGst;
                    @endphp
                    <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                        <span class="text-muted">Planned Amount:</span>
                        <span class="fw-bold">₹{{ number_format($displayPlanned, 2) }}</span>
                    </div>

                    <div class="d-flex justify-content-between mt-4 pt-3 border-top border-dark">
                        <span class="text-dark fw-bold h5 mb-0">Total Amount:</span>
                        <span class="text-success fw-bold h5 mb-0">₹{{ number_format($displayTotal, 2) }}</span>
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
                            <th>Receivable Amt</th>
                            <th>Base Amount (-TDS)</th>
                            <th>GST Amount</th>
                            <th>Status</th>
                            <th>Received Date</th>
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
                            $totalGstAmount = 0;
                            $totalTdsAmount = 0;
                        @endphp
                        @forelse($uniqueFamily->sortBy('created_at') as $index => $split)
                            @php
                                $displayAmount = ($split->is_split || $split->is_partial || $split->parent_id) ? $split->amount : $displayTotal;
                                $rowBaseAmount = ($split->is_split || $split->is_partial || $split->parent_id) ? ($split->actual_amount > 0 ? $split->actual_amount : $split->amount) : $displayBase;

                                $rowGstAmount = $split->taxes->where('tax_type', 'gst')->sum('tax_amount');
                                $rowTdsTax = $split->taxes->where('tax_type', 'tds')->first();
                                $rowTdsAmount = $rowTdsTax ? $rowTdsTax->tax_amount : 0;
                                
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

                                $splitStatusClass = match ($split->status) {
                                    'received' => 'bg-success',
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
                                    <a href="{{ route('manager.income.view', $split->id) }}" class="text-decoration-none fw-medium">#{{ $split->id }}</a>
                                </td>
                                <td class="fw-bold text-dark">
                                    {{ $formatAmount($displayAmount) }}
                                    @if(in_array($split->status, ['settle', 'settled']))
                                        @php
                                            $notes = $split->settle_notes;
                                            if (!$notes && $split->parent_id) {
                                                $parentSplit = $uniqueFamily->where('id', $split->parent_id)->first();
                                                if ($parentSplit) {
                                                    $notes = $parentSplit->settle_notes;
                                                }
                                            }
                                        @endphp
                                        @if($notes)
                                            <div class="text-muted small mt-1">({{ $notes }})</div>
                                        @endif
                                    @endif
                                </td>
                                <td class="fw-bold text-danger">{{ $formatAmount($rowBaseAmount - $rowTdsAmount) }}</td>
                                <td class="fw-bold text-primary">{{ $formatAmount($rowGstAmount) }}</td>
                                <td>
                                    <span class="badge {{ $splitStatusClass }}">{{ ucfirst($split->status) }}</span>
                                </td>
                                <td>{{ $split->paid_date ? \Carbon\Carbon::parse($split->paid_date)->format('n/j/Y') : (($split->status === 'received' || $split->status === 'settle') && $split->income_date ? \Carbon\Carbon::parse($split->income_date)->format('n/j/Y') : 'N/A') }}</td>
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
                                <td class="text-danger">{{ $itemSymbol }}{{ $formatAmount($totalBaseAmount - $totalTdsAmount) }}</td>
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
