@php
    $typeLabel = ($cardType ?? 'expense') === 'income' ? 'Incomes' : 'Payments';
    $paidLabel = ($cardType ?? 'expense') === 'income' ? 'Received' : 'Paid';
    $tItems = $cardStats['totalItems'] ?? 0;
    $paidPercent = $tItems > 0 ? (($cardStats['paidCount'] ?? 0) / $tItems) * 100 : 0;
    $pendingPercent = $tItems > 0 ? (($cardStats['pendingCount'] ?? 0) / $tItems) * 100 : 0;
    $overduePercent = $tItems > 0 ? (($cardStats['overdueCount'] ?? 0) / $tItems) * 100 : 0;
    $totOverPercent = $tItems > 0 ? (($cardStats['totalOverdueCount'] ?? 0) / $tItems) * 100 : 0;
@endphp

<div class="row row-cols-1 row-cols-sm-2 row-cols-md-5 g-3 mb-4">
    <!-- Card 1: Total Payments / Incomes -->
    <div class="col">
        <div class="summary-card flex flex-col justify-between hover:border-primary transition-all">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="summary-header">
                    <p class="font-label-md text-label-md text-on-surface-variant mb-0">{{ $dateRangeTitle ?? date('M-Y') }} {{ $typeLabel }}</p>
                </div>
                <div class="p-sm bg-secondary-fixed rounded-lg flex items-center justify-center" style="width: 40px; height: 40px;">
                    <span class="material-symbols-outlined text-secondary">payments</span>
                </div>
            </div>
            <div class="summary-body">
                <h4 class="font-headline-md text-headline-md text-primary mt-xs mb-2">₹{{ number_format($cardStats['totalPayments'] ?? 0, 2) }}</h4>
                <div class="mt-md d-flex align-items-center gap-sm">
                    <span class="font-data-mono text-data-mono text-on-surface-variant">{{ $cardStats['totalItems'] ?? 0 }} Items</span>
                    <div class="h-1 flex-1 bg-surface-container-high rounded-full overflow-hidden">
                        <div class="bg-secondary h-full" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Paid / Received -->
    <div class="col">
        <div class="summary-card flex flex-col justify-between hover:border-tertiary transition-all">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="summary-header">
                    <p class="font-label-md text-label-md text-on-surface-variant mb-0">{{ $dateRangeTitle ?? date('M-Y') }} {{ $paidLabel }}</p>
                </div>
                <div class="p-sm bg-tertiary-fixed rounded-lg flex items-center justify-center" style="width: 40px; height: 40px;">
                    <span class="material-symbols-outlined text-on-tertiary-fixed-variant" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                </div>
            </div>
            <div class="summary-body">
                <h4 class="font-headline-md text-headline-md text-tertiary mt-xs mb-2">₹{{ number_format($cardStats['paidAmount'] ?? 0, 2) }}</h4>
                <div class="mt-md d-flex align-items-center gap-sm">
                    <span class="font-data-mono text-data-mono text-on-surface-variant">{{ $cardStats['paidCount'] ?? 0 }} Items</span>
                    <div class="h-1 flex-1 bg-surface-container-high rounded-full overflow-hidden">
                        <div class="bg-on-tertiary-container h-full" style="width: {{ min(100, $paidPercent) }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 3: Pending -->
    <div class="col">
        <div class="summary-card flex flex-col justify-between hover:border-secondary-container transition-all">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="summary-header">
                    <p class="font-label-md text-label-md text-on-surface-variant mb-0">{{ $dateRangeTitle ?? date('M-Y') }} Pending</p>
                </div>
                <div class="p-sm bg-secondary-fixed-dim/30 rounded-lg flex items-center justify-center" style="width: 40px; height: 40px;">
                    <span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 1;">schedule</span>
                </div>
            </div>
            <div class="summary-body">
                <h4 class="font-headline-md text-headline-md text-secondary mt-xs mb-2">₹{{ number_format($cardStats['pendingAmount'] ?? 0, 2) }}</h4>
                <div class="mt-md d-flex align-items-center gap-sm">
                    <span class="font-data-mono text-data-mono text-on-surface-variant">{{ $cardStats['pendingCount'] ?? 0 }} Items</span>
                    <div class="h-1 flex-1 bg-surface-container-high rounded-full overflow-hidden">
                        <div class="bg-secondary-container h-full" style="width: {{ min(100, $pendingPercent) }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 4: Over Due (Current Period) -->
    <div class="col">
        <div class="summary-card flex flex-col justify-between hover:border-error transition-all">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="summary-header">
                    <p class="font-label-md text-label-md text-on-surface-variant mb-0">{{ $dateRangeTitle ?? date('M-Y') }} Over Due</p>
                </div>
                <div class="p-sm bg-error-container rounded-lg flex items-center justify-center" style="width: 40px; height: 40px;">
                    <span class="material-symbols-outlined text-error" style="font-variation-settings: 'FILL' 1;">warning</span>
                </div>
            </div>
            <div class="summary-body">
                <h4 class="font-headline-md text-headline-md text-error mt-xs mb-2">₹{{ number_format($cardStats['overdueAmount'] ?? 0, 2) }}</h4>
                <div class="mt-md d-flex align-items-center gap-sm">
                    <span class="font-data-mono text-data-mono text-on-surface-variant">{{ $cardStats['overdueCount'] ?? 0 }} Items</span>
                    <div class="h-1 flex-1 bg-surface-container-high rounded-full overflow-hidden">
                        <div class="bg-error h-full" style="width: {{ min(100, $overduePercent) }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 5: Total Over Due (All Time) -->
    <div class="col">
        <div class="summary-card flex flex-col justify-between hover:border-error transition-all">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="summary-header">
                    <p class="font-label-md text-label-md text-on-surface-variant mb-0">Total Over Due</p>
                </div>
                <div class="p-sm bg-error-container rounded-lg flex items-center justify-center" style="width: 40px; height: 40px;">
                    <span class="material-symbols-outlined text-error" style="font-variation-settings: 'FILL' 1;">error</span>
                </div>
            </div>
            <div class="summary-body">
                <h4 class="font-headline-md text-headline-md text-error mt-xs mb-2">₹{{ number_format($cardStats['totalOverdueAmount'] ?? 0, 2) }}</h4>
                <div class="mt-md d-flex align-items-center gap-sm">
                    <span class="font-data-mono text-data-mono text-on-surface-variant">{{ $cardStats['totalOverdueCount'] ?? 0 }} Items</span>
                    <div class="h-1 flex-1 bg-surface-container-high rounded-full overflow-hidden">
                        <div class="bg-error h-full" style="width: {{ min(100, $totOverPercent) }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.summary-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px 18px;
    height: 100%;
    transition: all 0.25s ease-in-out;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}
.summary-card:hover {
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
    transform: translateY(-2px);
}
.summary-header {
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 8px;
    margin-bottom: 10px;
    width: 100%;
}
</style>
