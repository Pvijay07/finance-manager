@extends('Admin.layouts.app')

@section('content')
<div id="audit-logs-page" class="page py-2">
    <!-- Header Section -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                    <i class="fas fa-shield-halved fa-lg text-primary"></i>
                </div>
                <div>
                    <h1 style="font-weight: 800; color: #0f172a; font-size: 1.85rem; letter-spacing: -0.5px; margin: 0;">Audit Logs</h1>
                    <p class="text-muted small mb-0 mt-0.5">Comprehensive audit trail of system activities, transactions, and user modifications.</p>
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.audit-logs.export', request()->query()) }}" class="btn btn-sm btn-outline-primary" style="border-radius: 8px; font-weight: 600; padding: 8px 16px;">
                <i class="fas fa-file-csv me-1.5"></i> Export CSV
            </a>
            <a href="{{ route('admin.audit-logs') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; font-weight: 600; padding: 8px 14px;">
                <i class="fas fa-rotate me-1.5"></i> Reset Filters
            </a>
        </div>
    </div>

    <!-- Summary Metrics Cards Row -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-5 g-3 mb-4">
        <!-- Total Logs -->
        <div class="col">
            <div class="summary-card flex flex-col justify-between hover:border-primary transition-all">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="summary-header">
                        <p class="font-label-md text-label-md text-on-surface-variant mb-0">Total Activities</p>
                    </div>
                    <div class="p-sm bg-secondary-fixed rounded-lg flex items-center justify-center" style="width: 40px; height: 40px;">
                        <span class="material-symbols-outlined text-secondary">history</span>
                    </div>
                </div>
                <div class="summary-body">
                    <h4 class="font-headline-md text-headline-md text-primary mt-xs mb-2">{{ number_format($totalLogs ?? 0) }}</h4>
                    <div class="mt-md d-flex align-items-center gap-sm">
                        <span class="font-data-mono text-data-mono text-on-surface-variant">All time recorded</span>
                        <div class="h-1 flex-1 bg-surface-container-high rounded-full overflow-hidden">
                            <div class="bg-secondary h-full" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Activity -->
        <div class="col">
            <div class="summary-card flex flex-col justify-between hover:border-tertiary transition-all">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="summary-header">
                        <p class="font-label-md text-label-md text-on-surface-variant mb-0">Today's Activity</p>
                    </div>
                    <div class="p-sm bg-tertiary-fixed rounded-lg flex items-center justify-center" style="width: 40px; height: 40px;">
                        <span class="material-symbols-outlined text-on-tertiary-fixed-variant" style="font-variation-settings: 'FILL' 1;">today</span>
                    </div>
                </div>
                <div class="summary-body">
                    <h4 class="font-headline-md text-headline-md text-tertiary mt-xs mb-2">{{ number_format($todayLogs ?? 0) }}</h4>
                    @php $todayPercent = ($totalLogs ?? 0) > 0 ? (($todayLogs ?? 0) / $totalLogs) * 100 : 0; @endphp
                    <div class="mt-md d-flex align-items-center gap-sm">
                        <span class="font-data-mono text-data-mono text-on-surface-variant">Logged today</span>
                        <div class="h-1 flex-1 bg-surface-container-high rounded-full overflow-hidden">
                            <div class="bg-on-tertiary-container h-full" style="width: {{ min(100, $todayPercent * 5) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Creates -->
        <div class="col">
            <div class="summary-card flex flex-col justify-between hover:border-success transition-all">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="summary-header">
                        <p class="font-label-md text-label-md text-on-surface-variant mb-0">Created Entries</p>
                    </div>
                    <div class="p-sm bg-emerald-100 rounded-lg flex items-center justify-center" style="width: 40px; height: 40px;">
                        <span class="material-symbols-outlined text-emerald-600">add_circle</span>
                    </div>
                </div>
                <div class="summary-body">
                    <h4 class="font-headline-md text-headline-md text-emerald-600 mt-xs mb-2">{{ number_format($createdCount ?? 0) }}</h4>
                    <div class="mt-md d-flex align-items-center gap-sm">
                        <span class="font-data-mono text-data-mono text-on-surface-variant">Creations</span>
                        <div class="h-1 flex-1 bg-surface-container-high rounded-full overflow-hidden">
                            <div class="bg-emerald-500 h-full" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Updates -->
        <div class="col">
            <div class="summary-card flex flex-col justify-between hover:border-indigo-400 transition-all">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="summary-header">
                        <p class="font-label-md text-label-md text-on-surface-variant mb-0">Modifications</p>
                    </div>
                    <div class="p-sm bg-indigo-100 rounded-lg flex items-center justify-center" style="width: 40px; height: 40px;">
                        <span class="material-symbols-outlined text-indigo-600">edit_note</span>
                    </div>
                </div>
                <div class="summary-body">
                    <h4 class="font-headline-md text-headline-md text-indigo-600 mt-xs mb-2">{{ number_format($updatedCount ?? 0) }}</h4>
                    <div class="mt-md d-flex align-items-center gap-sm">
                        <span class="font-data-mono text-data-mono text-on-surface-variant">Updates</span>
                        <div class="h-1 flex-1 bg-surface-container-high rounded-full overflow-hidden">
                            <div class="bg-indigo-500 h-full" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deletions -->
        <div class="col">
            <div class="summary-card flex flex-col justify-between hover:border-error transition-all">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="summary-header">
                        <p class="font-label-md text-label-md text-on-surface-variant mb-0">Deletions</p>
                    </div>
                    <div class="p-sm bg-error-container rounded-lg flex items-center justify-center" style="width: 40px; height: 40px;">
                        <span class="material-symbols-outlined text-error">delete</span>
                    </div>
                </div>
                <div class="summary-body">
                    <h4 class="font-headline-md text-headline-md text-error mt-xs mb-2">{{ number_format($deletedCount ?? 0) }}</h4>
                    <div class="mt-md d-flex align-items-center gap-sm">
                        <span class="font-data-mono text-data-mono text-on-surface-variant">Removals</span>
                        <div class="h-1 flex-1 bg-surface-container-high rounded-full overflow-hidden">
                            <div class="bg-error h-full" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; background: #ffffff;">
        <div class="card-body p-3.5">
            <form id="auditLogFilterForm" method="GET" action="{{ route('admin.audit-logs') }}" class="row g-3 align-items-end">
                <!-- Search -->
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small fw-semibold text-secondary mb-1">Search Keywords</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" name="search" value="{{ $search ?? '' }}" placeholder="Search action, details, IP, user...">
                    </div>
                </div>

                <!-- Date Range -->
                <div class="col-md-2 col-sm-6">
                    <label class="form-label small fw-semibold text-secondary mb-1">Date Range</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-calendar-alt text-muted"></i></span>
                        <select class="form-select border-start-0" name="date_range" id="auditDateRangeSelect" onchange="toggleAuditCustomDates(this.value)">
                            <option value="today" {{ ($dateRange ?? '') === 'today' ? 'selected' : '' }}>Today</option>
                            <option value="yesterday" {{ ($dateRange ?? '') === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                            <option value="last_7_days" {{ ($dateRange ?? 'last_7_days') === 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                            <option value="last_30_days" {{ ($dateRange ?? '') === 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
                            <option value="this_month" {{ ($dateRange ?? '') === 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="custom" {{ ($dateRange ?? '') === 'custom' ? 'selected' : '' }}>Custom Range</option>
                        </select>
                    </div>
                </div>

                <!-- User Filter -->
                <div class="col-md-2 col-sm-6">
                    <label class="form-label small fw-semibold text-secondary mb-1">User</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-user text-muted"></i></span>
                        <select class="form-select border-start-0" name="user_id" onchange="this.form.submit()">
                            <option value="all" {{ ($userId ?? 'all') === 'all' ? 'selected' : '' }}>All Users</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ ($userId ?? '') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Action Type -->
                <div class="col-md-2 col-sm-6">
                    <label class="form-label small fw-semibold text-secondary mb-1">Action Type</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-bolt text-muted"></i></span>
                        <select class="form-select border-start-0" name="action_type" onchange="this.form.submit()">
                            <option value="all" {{ ($actionType ?? 'all') === 'all' ? 'selected' : '' }}>All Actions</option>
                            <option value="create" {{ ($actionType ?? '') === 'create' ? 'selected' : '' }}>Create</option>
                            <option value="update" {{ ($actionType ?? '') === 'update' ? 'selected' : '' }}>Update</option>
                            <option value="delete" {{ ($actionType ?? '') === 'delete' ? 'selected' : '' }}>Delete</option>
                            <option value="login" {{ ($actionType ?? '') === 'login' ? 'selected' : '' }}>Login</option>
                        </select>
                    </div>
                </div>

                <!-- Resource -->
                <div class="col-md-2 col-sm-6">
                    <label class="form-label small fw-semibold text-secondary mb-1">Resource</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-cubes text-muted"></i></span>
                        <select class="form-select border-start-0" name="resource" onchange="this.form.submit()">
                            <option value="all" {{ ($resource ?? 'all') === 'all' ? 'selected' : '' }}>All Resources</option>
                            <option value="Expense" {{ ($resource ?? '') === 'Expense' ? 'selected' : '' }}>Expense</option>
                            <option value="Company" {{ ($resource ?? '') === 'Company' ? 'selected' : '' }}>Company</option>
                            <option value="Invoice" {{ ($resource ?? '') === 'Invoice' ? 'selected' : '' }}>Invoice</option>
                            <option value="Income" {{ ($resource ?? '') === 'Income' ? 'selected' : '' }}>Income</option>
                            <option value="Tax" {{ ($resource ?? '') === 'Tax' ? 'selected' : '' }}>Tax</option>
                            <option value="User" {{ ($resource ?? '') === 'User' ? 'selected' : '' }}>User</option>
                        </select>
                    </div>
                </div>

                <!-- Per Page & Submit -->
                <div class="col-md-1 col-sm-6">
                    <div class="d-flex gap-1.5">
                        <button type="submit" class="btn btn-sm btn-primary w-100" style="background-color: #4f46e5; border-color: #4f46e5; border-radius: 6px; font-weight: 600;">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                    </div>
                </div>

                <!-- Custom Date Row -->
                <div class="col-12 mt-2 {{ ($dateRange ?? '') === 'custom' ? '' : 'd-none' }}" id="auditCustomDateRow">
                    <div class="p-2.5 bg-light rounded-3 border d-flex align-items-center gap-3 flex-wrap">
                        <span class="small fw-semibold text-secondary"><i class="fas fa-calendar-day me-1"></i> Custom Range:</span>
                        <div class="d-flex align-items-center gap-1.5">
                            <label class="small text-muted mb-0">From:</label>
                            <input type="date" class="form-control form-control-sm" name="start_date" value="{{ $startDate ?? '' }}" style="width: auto;">
                        </div>
                        <div class="d-flex align-items-center gap-1.5">
                            <label class="small text-muted mb-0">To:</label>
                            <input type="date" class="form-control form-control-sm" name="end_date" value="{{ $endDate ?? '' }}" style="width: auto;">
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary px-3" style="background-color: #4f46e5; border-color: #4f46e5;">Apply Dates</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Audit Logs Main Table Card -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden; background: #ffffff;">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-0 fw-bold" style="color: #0f172a; font-size: 1.05rem;">
                    <i class="fas fa-list-check text-primary me-2"></i>Audit Log Entries
                </h5>
                <p class="text-muted small mb-0 mt-0.5">Showing system events and modification history</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-light text-secondary border px-3 py-1.5 rounded-full font-label-md">
                    Showing {{ $logs->firstItem() ?? 0 }} - {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} entries
                </span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="width: 100%; border-collapse: separate; border-spacing: 0;">
                <thead style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <tr>
                        <th style="padding: 12px 16px; font-weight: 600; font-size: 12px; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px;">Timestamp</th>
                        <th style="padding: 12px 16px; font-weight: 600; font-size: 12px; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px;">User</th>
                        <th style="padding: 12px 16px; font-weight: 600; font-size: 12px; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px;">Action</th>
                        <th style="padding: 12px 16px; font-weight: 600; font-size: 12px; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px;">Resource</th>
                        <th style="padding: 12px 16px; font-weight: 600; font-size: 12px; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; width: 38%;">Details & Changes</th>
                        <th style="padding: 12px 16px; font-weight: 600; font-size: 12px; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px;">IP Address</th>
                        <th style="padding: 12px 16px; font-weight: 600; font-size: 12px; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; text-align: center;">View</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;">
                        <!-- Timestamp -->
                        <td style="padding: 14px 16px; white-space: nowrap;">
                            <div class="fw-semibold text-dark" style="font-size: 13px;">
                                @if($log->created_at->isToday())
                                    <span class="text-primary fw-bold">Today</span>, {{ $log->created_at->format('h:i A') }}
                                @elseif($log->created_at->isYesterday())
                                    <span class="text-secondary">Yesterday</span>, {{ $log->created_at->format('h:i A') }}
                                @else
                                    {{ $log->created_at->format('d M Y') }}, <span class="text-muted">{{ $log->created_at->format('h:i A') }}</span>
                                @endif
                            </div>
                            <small class="text-muted" style="font-size: 11px;">
                                <i class="far fa-clock me-1"></i>{{ $log->created_at->diffForHumans() }}
                            </small>
                        </td>

                        <!-- User -->
                        <td style="padding: 14px 16px; white-space: nowrap;">
                            <div class="d-flex align-items-center gap-2">
                                @php
                                    $userName = $log->user ? $log->user->name : 'System';
                                    $initials = strtoupper(substr($userName, 0, 2));
                                    $bgColors = ['#e0e7ff', '#dcfce7', '#fef3c7', '#fee2e2', '#f3e8ff', '#e0f2fe'];
                                    $textColors = ['#4338ca', '#15803d', '#b45309', '#b91c1c', '#7e22ce', '#0369a1'];
                                    $colorIndex = crc32($userName) % count($bgColors);
                                @endphp
                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-xs"
                                     style="width: 32px; height: 32px; font-size: 11px; background-color: {{ $bgColors[$colorIndex] }}; color: {{ $textColors[$colorIndex] }};">
                                    {{ $initials }}
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark" style="font-size: 13px;">{{ $userName }}</div>
                                    @if($log->user)
                                        <small class="text-muted" style="font-size: 11px;">{{ $log->user->email ?? 'User' }}</small>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary" style="font-size: 9px;">Automated</span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- Action -->
                        <td style="padding: 14px 16px; white-space: nowrap;">
                            @php
                                $act = strtolower($log->action ?? '');
                                $badgeBg = '#f1f5f9';
                                $badgeColor = '#475569';
                                $badgeBorder = '#e2e8f0';
                                $badgeIcon = 'fas fa-circle';

                                if (str_contains($act, 'creat') || str_contains($act, 'store')) {
                                    $badgeBg = '#dcfce7';
                                    $badgeColor = '#15803d';
                                    $badgeBorder = '#bbf7d0';
                                    $badgeIcon = 'fas fa-plus-circle';
                                } elseif (str_contains($act, 'updat') || str_contains($act, 'edit')) {
                                    $badgeBg = '#e0e7ff';
                                    $badgeColor = '#4338ca';
                                    $badgeBorder = '#c7d2fe';
                                    $badgeIcon = 'fas fa-pen-to-square';
                                } elseif (str_contains($act, 'delet') || str_contains($act, 'destroy')) {
                                    $badgeBg = '#fee2e2';
                                    $badgeColor = '#b91c1c';
                                    $badgeBorder = '#fecaca';
                                    $badgeIcon = 'fas fa-trash-can';
                                } elseif (str_contains($act, 'login')) {
                                    $badgeBg = '#f3e8ff';
                                    $badgeColor = '#7e22ce';
                                    $badgeBorder = '#e9d5ff';
                                    $badgeIcon = 'fas fa-right-to-bracket';
                                }
                            @endphp
                            <span class="badge rounded-pill px-2.5 py-1 text-xs fw-semibold"
                                  style="background-color: {{ $badgeBg }}; color: {{ $badgeColor }}; border: 1px solid {{ $badgeBorder }};">
                                <i class="{{ $badgeIcon }} me-1"></i>{{ ucfirst(str_replace('_', ' ', $log->action)) }}
                            </span>
                        </td>

                        <!-- Resource -->
                        <td style="padding: 14px 16px; white-space: nowrap;">
                            @php
                                $baseModel = class_basename($log->model_type);
                                $modelIcons = [
                                    'Expense'   => 'fas fa-receipt text-indigo-500',
                                    'Company'   => 'fas fa-building text-blue-500',
                                    'Invoice'   => 'fas fa-file-invoice text-emerald-500',
                                    'Income'    => 'fas fa-money-bill-wave text-teal-500',
                                    'Tax'       => 'fas fa-percent text-amber-500',
                                    'User'      => 'fas fa-user text-purple-500',
                                ];
                                $iconClass = $modelIcons[$baseModel] ?? 'fas fa-cube text-slate-400';
                            @endphp
                            <span class="badge bg-light text-dark border px-2.5 py-1" style="font-size: 12px;">
                                <i class="{{ $iconClass }} me-1.5"></i>{{ $baseModel }}{{ $log->model_id ? ' #' . $log->model_id : '' }}
                            </span>
                        </td>

                        <!-- Details & Changes -->
                        <td style="padding: 14px 16px;">
                            @if(is_array($log->details) && isset($log->details['old']) && isset($log->details['new']))
                                <!-- Update Diff Badges -->
                                <div class="d-flex flex-column gap-1">
                                    @php $shownCount = 0; @endphp
                                    @foreach($log->details['new'] as $key => $newVal)
                                        @php
                                            if (in_array($key, ['updated_at', 'created_at', 'password'])) continue;
                                            $oldVal = $log->details['old'][$key] ?? null;
                                            if ($oldVal == $newVal) continue;
                                            $shownCount++;
                                            if ($shownCount > 3) continue;

                                            // Format date values nicely if ISO date string
                                            $formatVal = function($v) {
                                                if (empty($v)) return 'empty';
                                                if (is_array($v)) return json_encode($v);
                                                if (is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}T/', $v)) {
                                                    try { return \Carbon\Carbon::parse($v)->format('d M Y'); } catch(\Exception $e) {}
                                                }
                                                return (string)$v;
                                            };
                                        @endphp
                                        <div class="d-flex align-items-center flex-wrap gap-1" style="font-size: 12px;">
                                            <span class="badge bg-light text-secondary border px-1.5 py-0.5 fw-semibold" style="font-size: 11px;">
                                                {{ ucwords(str_replace('_', ' ', $key)) }}
                                            </span>
                                            <span class="text-danger text-decoration-line-through small" style="font-size: 11px;">
                                                {{ $formatVal($oldVal) }}
                                            </span>
                                            <i class="fas fa-arrow-right text-muted mx-0.5" style="font-size: 9px;"></i>
                                            <span class="text-success fw-semibold small" style="font-size: 11px;">
                                                {{ $formatVal($newVal) }}
                                            </span>
                                        </div>
                                    @endforeach
                                    @if($shownCount > 3)
                                        <span class="text-muted small" style="font-size: 11px;">+ {{ $shownCount - 3 }} more changed fields</span>
                                    @elseif($shownCount === 0)
                                        <span class="text-muted small fst-italic">Updated with minor attribute changes</span>
                                    @endif
                                </div>
                            @elseif(is_array($log->details))
                                <!-- Key Value Pairs -->
                                <div class="d-flex flex-wrap gap-1">
                                    @php $itemCount = 0; @endphp
                                    @foreach($log->details as $key => $val)
                                        @php
                                            if (in_array($key, ['updated_at', 'created_at', 'id', 'password']) || is_array($val)) continue;
                                            $itemCount++;
                                            if ($itemCount > 4) continue;
                                        @endphp
                                        <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 11px;">
                                            <span class="text-muted fw-normal">{{ ucwords(str_replace('_', ' ', $key)) }}:</span>
                                            <span class="fw-semibold">{{ \Illuminate\Support\Str::limit((string)$val, 28) }}</span>
                                        </span>
                                    @endforeach
                                    @if($itemCount > 4)
                                        <span class="badge bg-light text-secondary border px-1.5 py-1" style="font-size: 11px;">+{{ $itemCount - 4 }} more</span>
                                    @elseif($itemCount === 0)
                                        <span class="text-muted small">No extra attributes recorded</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted small">N/A</span>
                            @endif
                        </td>

                        <!-- IP Address -->
                        <td style="padding: 14px 16px; white-space: nowrap;">
                            @if($log->ip_address)
                                <span class="text-secondary small font-data-mono" style="font-size: 12px;">
                                    <i class="fas fa-laptop text-muted me-1"></i>{{ $log->ip_address }}
                                </span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>

                        <!-- View Button -->
                        <td style="padding: 14px 16px; text-align: center; white-space: nowrap;">
                            <button type="button" 
                                    class="btn btn-sm btn-light border text-primary" 
                                    style="border-radius: 6px; padding: 4px 10px;"
                                    title="View Full Payload"
                                    onclick='openAuditDetailsModal(@json($log->id), @json($log->user ? $log->user->name : "System"), @json(ucfirst(str_replace("_", " ", $log->action))), @json($baseModel . ($log->model_id ? " #" . $log->model_id : "")), @json($log->created_at->format("d M Y, h:i A")), @json($log->ip_address ?? "N/A"), @json($log->details))'>
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <div class="py-4">
                                <i class="fas fa-clipboard-list fa-3x mb-3 text-secondary opacity-40"></i>
                                <h6 class="fw-bold text-dark">No Audit Logs Found</h6>
                                <p class="text-muted small mb-0">No matching activities were recorded for the selected filter criteria.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="p-3.5 border-top d-flex justify-content-between align-items-center flex-wrap gap-2 bg-light">
            <div class="text-muted small">
                Showing <strong>{{ $logs->firstItem() }}</strong> to <strong>{{ $logs->lastItem() }}</strong> of <strong>{{ $logs->total() }}</strong> activities
            </div>
            <div>
                {{ $logs->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Modal: View Full Audit Log Details -->
<div class="modal fade" id="auditDetailModal" tabindex="-1" aria-labelledby="auditDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
            <div class="modal-header border-bottom" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: white; border-radius: 12px 12px 0 0;">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-shield-halved fa-lg"></i>
                    <h5 class="modal-title font-semibold text-white mb-0" id="auditDetailModalLabel">Audit Log Payload Details</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Metadata Grid -->
                <div class="row g-3 mb-4 p-3 bg-light rounded-3 border">
                    <div class="col-md-4">
                        <label class="text-muted small d-block">Log ID</label>
                        <span class="fw-bold text-dark" id="modalLogId">#</span>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small d-block">User</label>
                        <span class="fw-bold text-dark" id="modalLogUser">-</span>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small d-block">Timestamp</label>
                        <span class="fw-bold text-dark" id="modalLogTime">-</span>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small d-block">Action</label>
                        <span class="badge bg-primary text-white" id="modalLogAction">-</span>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small d-block">Resource</label>
                        <span class="badge bg-secondary text-white" id="modalLogResource">-</span>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small d-block">IP Address</label>
                        <span class="font-data-mono text-dark" id="modalLogIp">-</span>
                    </div>
                </div>

                <!-- Payload JSON view -->
                <div>
                    <label class="form-label small fw-semibold text-secondary mb-1">
                        <i class="fas fa-code me-1"></i>Raw Attributes / Diff Payload
                    </label>
                    <pre id="modalLogPayload" class="p-3 bg-dark text-light rounded-3 font-monospace small" style="max-height: 350px; overflow-y: auto; white-space: pre-wrap; word-break: break-all;"></pre>
                </div>
            </div>
            <div class="modal-footer border-top bg-light">
                <button type="button" class="btn btn-sm btn-outline-secondary px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleAuditCustomDates(value) {
        const row = document.getElementById('auditCustomDateRow');
        if (value === 'custom') {
            if (row) row.classList.remove('d-none');
        } else {
            if (row) row.classList.add('d-none');
            document.getElementById('auditLogFilterForm')?.submit();
        }
    }

    function openAuditDetailsModal(id, user, action, resource, time, ip, details) {
        document.getElementById('modalLogId').innerText = '#' + id;
        document.getElementById('modalLogUser').innerText = user;
        document.getElementById('modalLogAction').innerText = action;
        document.getElementById('modalLogResource').innerText = resource;
        document.getElementById('modalLogTime').innerText = time;
        document.getElementById('modalLogIp').innerText = ip;

        try {
            const pretty = typeof details === 'string' ? JSON.parse(details) : details;
            document.getElementById('modalLogPayload').innerText = JSON.stringify(pretty, null, 2);
        } catch(e) {
            document.getElementById('modalLogPayload').innerText = typeof details === 'object' ? JSON.stringify(details, null, 2) : (details || 'No payload');
        }

        const modalEl = document.getElementById('auditDetailModal');
        if (modalEl) {
            const bsModal = new bootstrap.Modal(modalEl);
            bsModal.show();
        }
    }
</script>

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
@endsection
