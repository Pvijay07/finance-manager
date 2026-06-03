  @extends('Admin.layouts.app')
@section('content')
  <!-- Audit Logs Page -->
            <div id="audit-logs" class="page">
                <div class="filter-bar">
                    <div class="filter-group">
                        <div class="filter-label">Date Range</div>
                        <select>
                            <option>Today</option>
                            <option selected>Last 7 Days</option>
                            <option>Last 30 Days</option>
                            <option>Custom Range</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <div class="filter-label">User</div>
                        <select>
                            <option selected>All Users</option>
                            <option>Super Admin</option>
                            <option>John Manager</option>
                            <option>Raj Manager</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <div class="filter-label">Action Type</div>
                            <select>
                            <option selected>All Actions</option>
                            <option>Login</option>
                            <option>Create</option>
                            <option>Update</option>
                            <option>Delete</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <div class="filter-label">Resource</div>
                        <select>
                            <option selected>All Resources</option>
                            <option>User</option>
                            <option>Company</option>
                            <option>Expense</option>
                            <option>Settings</option>
                        </select>
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-header">
                        <div class="table-title">Audit Logs</div>
                        <div class="table-actions">
                            <button class="btn btn-outline">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <button class="btn btn-primary">
                                <i class="fas fa-download"></i> Export Logs
                            </button>
                        </div>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Resource</th>
                                <th>Details</th>
                                <th>IP Address</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->created_at->isToday() ? 'Today' : ($log->created_at->isYesterday() ? 'Yesterday' : $log->created_at->format('M d, Y')) }}, {{ $log->created_at->format('h:i A') }}</td>
                                <td>{{ $log->user ? $log->user->name : 'System' }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $log->action)) }}</td>
                                <td>{{ class_basename($log->model_type) }}{{ $log->model_id ? ': ID ' . $log->model_id : '' }}</td>
                                <td class="details-column small">{!! $log->formatted_details !!}</td>
                                <td>{{ $log->ip_address ?? 'N/A' }}</td>
                                <td>
                                    @php
                                        $statusClass = in_array($log->action, ['deleted', 'failed_login']) ? 'inactive' : 'active';
                                        $statusText = in_array($log->action, ['failed_login']) ? 'Failed' : 'Success';
                                    @endphp
                                    <span class="status {{ $statusClass }}">{{ $statusText }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center" style="text-align: center; padding: 20px;">No audit logs found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Audit Log Settings</div>
                    </div>
                    <div style="padding: 20px;">
                        <div class="form-group">
                            <label class="form-label">Log Retention Period</label>
                            <select class="form-control">
                                <option>30 days</option>
                                <option>90 days</option>
                                <option selected>1 year</option>
                                <option>2 years</option>
                                <option>Indefinite</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Log Detail Level</label>
                            <select class="form-control">
                                <option>Minimal</option>
                                <option selected>Standard</option>
                                <option>Detailed</option>
                                <option>Verbose</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <input type="checkbox" checked> Log user logins
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <input type="checkbox" checked> Log data modifications
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <input type="checkbox"> Log data reads
                            </label>
                        </div>
                        <div class="form-group">
                            <button class="btn btn-primary">Save Settings</button>
                        </div>
                    </div>
                </div>
            </div>
@endsection

@push('styles')
<style>
    .details-column {
        font-size: 0.85rem;
        line-height: 1.5;
        max-width: 400px;
        white-space: normal;
        word-break: break-all;
    }
    .details-column strong {
        color: #4f46e5;
    }
    .details-column i {
        color: #9ca3af;
    }
</style>
@endpush
