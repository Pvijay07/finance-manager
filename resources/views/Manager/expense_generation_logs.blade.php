@extends('Manager.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Expense Generation Logs</h1>
            <a href="{{ route('admin.standard-expenses') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Expenses
            </a>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Generation History</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Run Date</th>
                                <th>Status</th>
                                <th>Generated</th>
                                <th>Trigger Type</th>
                                <th>Triggered By</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                <tr>
                                    <td>
                                        @php
                                            try {
                                                $date = \Carbon\Carbon::parse($log->run_date);
                                                echo $date->format('d M Y, h:i A');
                                            } catch (\Exception $e) {
                                                echo $log->run_date;
                                            }
                                        @endphp
                                    </td>
                                    <td>
                                        @php
                                            $statusBadge =
                                                [
                                                    'success' => 'success',
                                                    'partial' => 'warning',
                                                    'failed' => 'danger',
                                                ][$log->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge badge-{{ $statusBadge }}">
                                            {{ ucfirst($log->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $log->total_generated }} expenses</td>
                                    <td>{{ ucfirst($log->trigger_type) }}</td>
                                    <td>
                                        @if ($log->triggered_by)
                                            User #{{ $log->triggered_by }}
                                        @else
                                            System
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" type="button" data-toggle="collapse"
                                            data-target="#details-{{ $log->id }}">
                                            View Details
                                        </button>
                                        <div class="collapse mt-2" id="details-{{ $log->id }}">
                                            <div class="card card-body">
                                                <pre style="margin: 0; font-size: 12px;"></pre>
                                                {{-- {{ json_encode(json_decode($log->details), JSON_PRETTY_PRINT) }} --}}
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
