@extends('Manager.layouts.app')
@section('title', 'Salary Sheets')

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
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h6 class="mb-1 fw-bold">Salary Sheets</h6>
                        <div class="small-help text-muted">Create monthly sheets per company.</div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createSheetModal">Create New Sheet</button>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ route('manager.salary.sheets') }}" class="row g-2 align-items-end mb-3">
                        <div class="col-md-3">
                            <label class="form-label small">Company</label>
                            <select name="company_id" class="form-select form-select-sm">
                                <option value="">All Companies</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ $companyId == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-grid">
                            <button type="submit" class="btn btn-sm btn-primary">Apply</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Company</th>
                                    <th>Month</th>
                                    <th>Status</th>
                                    <th class="text-end">Total Gross (₹)</th>
                                    <th class="text-end">Total Net (₹)</th>
                                    <th>Last Updated</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sheets as $sheet)
                                <tr>
                                    <td>{{ $sheet->company->name }}</td>
                                    <td>{{ $sheet->month_year }}</td>
                                    <td>
                                        <span class="badge bg-{{ $sheet->status == 'Draft' ? 'secondary' : ($sheet->status == 'Paid' ? 'success' : 'warning') }}">
                                            {{ $sheet->status }}
                                        </span>
                                    </td>
                                    <td class="text-end">{{ number_format($sheet->total_gross, 2) }}</td>
                                    <td class="text-end fw-bold">{{ number_format($sheet->total_net_pay, 2) }}</td>
                                    <td>{{ $sheet->updated_at->format('d-m-Y H:i') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('manager.salary.editSheet', $sheet->id) }}" class="btn btn-sm btn-outline-primary">
                                            {{ $sheet->status == 'Draft' ? 'Edit Sheet' : 'View Sheet' }}
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center text-muted">No sheets created yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-2">{{ $sheets->links() }}</div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Create Sheet Modal -->
<div class="modal fade" id="createSheetModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">Create Salary Sheet</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('manager.salary.sheets.create') }}" method="POST">
          @csrf
          <div class="modal-body">
            <div class="mb-3">
                <label class="form-label small">Company</label>
                <select name="company_id" class="form-select form-select-sm" required>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label small">Month & Year</label>
                <input name="month_year" type="month" class="form-control form-control-sm" value="{{ date('Y-m') }}" required>
            </div>
            <div class="alert alert-info py-2 small mb-0">
                This will automatically pull all Active employees for the selected company and create a Draft sheet.
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary">Generate Sheet</button>
          </div>
      </form>
    </div>
  </div>
</div>
@endsection
