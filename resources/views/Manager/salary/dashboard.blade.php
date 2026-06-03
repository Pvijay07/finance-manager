@extends('Manager.layouts.app')
@section('title', 'Salary Dashboard')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid"></div>
    </section>

    <section class="content">
        <div class="container-fluid">
            @include('Manager.salary.nav')

            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="card kpi shadow-sm">
                        <div class="card-body">
                            <div class="label text-muted small text-uppercase">Current Month</div>
                            <div class="value fs-4 fw-bold">{{ date('M Y') }}</div>
                            <div class="small-help text-muted">Select month in Salary Sheets.</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card kpi shadow-sm">
                        <div class="card-body">
                            <div class="label text-muted small text-uppercase">Sheets Created</div>
                            <div class="value fs-4 fw-bold">{{ $sheetsCreated }}</div>
                            <div class="small-help text-muted">Across companies.</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card kpi shadow-sm">
                        <div class="card-body">
                            <div class="label text-muted small text-uppercase">Upcoming Payout</div>
                            <div class="value fs-4 fw-bold text-danger">₹ {{ number_format($upcomingPayout, 2) }}</div>
                            <div class="small-help text-muted">In Pending Expenses</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card kpi shadow-sm">
                        <div class="card-body">
                            <div class="label text-muted small text-uppercase">Last Updated</div>
                            <div class="value fs-4 fw-bold">{{ $lastUpdated ? \Carbon\Carbon::parse($lastUpdated)->format('d-m-Y') : 'N/A' }}</div>
                            <div class="small-help text-muted">Auto from latest action.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-7">
                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Quick Actions</span>
                            <span class="small-help text-muted">Most used</span>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a class="btn btn-sm btn-primary" href="{{ route('manager.salary.sheets') }}">Create / Manage Salary Sheets</a>
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('manager.salary.employees') }}">Add / Edit Employees</a>
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('manager.salary.payments') }}">Mark Salary Payment</a>
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('manager.salary.reports') }}">Export Salary Pack for CA</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card shadow-sm">
                        <div class="card-header fw-bold">This Month Status</div>
                        <div class="card-body">
                            <ul class="small mb-0 text-muted">
                                @foreach($companies as $company)
                                    @php
                                        $sheet = \App\Models\SalarySheet::where('company_id', $company->id)->where('month_year', date('Y-m'))->first();
                                    @endphp
                                    <li class="mb-2">
                                        <strong>{{ $company->name }}</strong>: 
                                        @if($sheet)
                                            <span class="badge bg-{{ $sheet->status == 'Paid' ? 'success' : ($sheet->status == 'Locked' ? 'warning' : 'secondary') }}">
                                                {{ $sheet->status }}
                                            </span>
                                        @else
                                            Not started
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
