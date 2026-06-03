@extends('Manager.layouts.app')
@section('title', 'Salary Reports')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid"></div>
    </section>

    <section class="content">
        <div class="container-fluid">
            @include('Manager.salary.nav')

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h6 class="mb-1 fw-bold">Salary Reports & Exports</h6>
                    <div class="small-help text-muted">Generate CA-ready salary packs (Excel/PDF) containing attendance, gross, net, and component breakdown.</div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header fw-bold">Monthly Salary Pack (CA Export)</div>
                        <div class="card-body">
                            <form action="{{ route('manager.salary.reports.generate') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label small">Company</label>
                                    <select class="form-select form-select-sm" name="company_id" required>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">Month & Year</label>
                                    <input type="month" name="month_year" class="form-control form-control-sm" value="{{ date('Y-m') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">Format</label>
                                    <select class="form-select form-select-sm" name="format">
                                        <option value="excel">Excel (.xlsx)</option>
                                        <option value="pdf">PDF (.pdf)</option>
                                    </select>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-sm btn-primary">Generate Report</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header fw-bold">Employee Ledger</div>
                        <div class="card-body">
                            <form>
                                <div class="mb-3">
                                    <label class="form-label small">Company</label>
                                    <select class="form-select form-select-sm">
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">Employee</label>
                                    <select class="form-select form-select-sm">
                                        <option>All Employees</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">Financial Year</label>
                                    <select class="form-select form-select-sm">
                                        <option>FY 2025-26</option>
                                        <option>FY 2024-25</option>
                                    </select>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary">View Ledger</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
