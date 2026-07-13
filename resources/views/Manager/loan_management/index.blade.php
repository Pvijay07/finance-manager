@extends('Manager.layouts.app')

@section('content')
<div class="manager-panel">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Loan Management</h4>
        <div>
            <a href="{{ route('manager.loan-management.index', ['direction' => 'taken']) }}" class="btn {{ $direction === 'taken' ? 'btn-primary' : 'btn-outline-primary' }}">Loans Taken</a>
            <a href="{{ route('manager.loan-management.index', ['direction' => 'given']) }}" class="btn {{ $direction === 'given' ? 'btn-primary' : 'btn-outline-primary' }}">Loans Given</a>
            <a href="{{ route('manager.loan-management.create', ['direction' => $direction]) }}" class="btn btn-success ml-2">
                <i class="fas fa-plus"></i> Add New Loan
            </a>
        </div>
    </div>

    <!-- Summary Widgets -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6>Total Active Loans</h6>
                    <h3>{{ $stats['total_active'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6>Total Outstanding (₹)</h6>
                    <h3>{{ number_format($stats['total_outstanding'], 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6>Total Overdue</h6>
                    <h3>{{ $stats['total_overdue'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6>Total Closed</h6>
                    <h3>{{ $stats['total_closed'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Loan List -->
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('manager.loan-management.index') }}" method="GET" class="mb-4">
                <input type="hidden" name="direction" value="{{ $direction }}">
                <div class="row">
                    <div class="col-md-3">
                        <select name="status" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="partial_paid" {{ request('status') == 'partial_paid' ? 'selected' : '' }}>Partial Paid</option>
                            <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="party_id" class="form-control">
                            <option value="">All Parties</option>
                            @foreach($parties as $party)
                                <option value="{{ $party->id }}" {{ request('party_id') == $party->id ? 'selected' : '' }}>{{ $party->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100">Filter</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Loan #</th>
                            <th>Party</th>
                            <th>Date</th>
                            <th>Principal (₹)</th>
                            <th>Balance (₹)</th>
                            <th>EMI (₹)</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loans as $loan)
                            <tr>
                                <td>{{ $loan->loan_number }}</td>
                                <td>{{ $loan->party->name ?? 'N/A' }}</td>
                                <td>{{ $loan->loan_date->format('d M Y') }}</td>
                                <td>{{ number_format($loan->principal_amount, 2) }}</td>
                                <td class="font-weight-bold">{{ number_format($loan->balance_amount, 2) }}</td>
                                <td>
                                    {{ $loan->enable_emi ? number_format($loan->emi_amount, 2) : 'N/A' }}
                                </td>
                                <td>
                                    <span class="badge badge-{{ $loan->status === 'active' ? 'primary' : ($loan->status === 'closed' ? 'success' : 'warning') }}">
                                        {{ ucfirst(str_replace('_', ' ', $loan->status)) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('manager.loan-management.show', $loan->id) }}" class="btn btn-sm btn-info">View Ledger</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No loans found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $loans->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
