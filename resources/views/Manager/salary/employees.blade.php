@extends('Manager.layouts.app')
@section('title', 'Employee Master')

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
                        <h6 class="mb-1 fw-bold">Employee Master</h6>
                        <div class="small-help text-muted">Maintain employee list per company. Salary sheets auto-pull active employees.</div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addEmpModal">Add Employee</button>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form class="row g-2 align-items-end mb-3" method="GET" action="{{ route('manager.salary.employees') }}">
                        <div class="col-md-3">
                            <label class="form-label small">Company</label>
                            <select name="company_id" class="form-select form-select-sm">
                                <option value="">All Companies</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ $companyId == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="All" {{ $status == 'All' ? 'selected' : '' }}>All</option>
                                <option value="Active" {{ $status == 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Inactive" {{ $status == 'Inactive' ? 'selected' : '' }}>Inactive</option>
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
                                    <th>Emp ID</th>
                                    <th>Name</th>
                                    <th>Company</th>
                                    <th>Department</th>
                                    <th>Role</th>
                                    <th>Salary Type</th>
                                    <th class="text-end">Monthly CTC (₹)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $emp)
                                    <tr>
                                        <td>{{ $emp->emp_id ?? 'N/A' }}</td>
                                        <td class="fw-bold">{{ $emp->full_name }}</td>
                                        <td>{{ $emp->company->name }}</td>
                                        <td>{{ $emp->department }}</td>
                                        <td>{{ $emp->role }}</td>
                                        <td>{{ $emp->salary_type }}</td>
                                        <td class="text-end">{{ number_format($emp->monthly_ctc, 2) }}</td>
                                        <td>
                                            @if($emp->status == 'Active')
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center text-muted">No employees found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-2">{{ $employees->links() }}</div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Add Employee Modal -->
<div class="modal fade" id="addEmpModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title fw-bold">Add Employee</h5>
          <div class="small-help text-muted">Create employee profile used for salary sheets.</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('manager.salary.employees.store') }}" method="POST">
          @csrf
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label small">Company</label>
                <select name="company_id" class="form-select form-select-sm" required>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                    @endforeach
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label small">Employee ID</label>
                <input name="emp_id" class="form-control form-control-sm" placeholder="Auto or manual">
              </div>
              <div class="col-md-4">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select form-select-sm" required>
                    <option value="Active" selected>Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label small">Full Name</label>
                <input name="full_name" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-3">
                <label class="form-label small">Department</label>
                <input name="department" class="form-control form-control-sm" placeholder="Tech / Ops / Sales">
              </div>
              <div class="col-md-3">
                <label class="form-label small">Role</label>
                <input name="role" class="form-control form-control-sm" placeholder="Developer / Manager">
              </div>

              <div class="col-md-6">
                <label class="form-label small">Email</label>
                <input type="email" name="email" class="form-control form-control-sm" placeholder="employee@company.com">
              </div>

              <div class="col-md-4">
                <label class="form-label small">Salary Type</label>
                <select name="salary_type" class="form-select form-select-sm" required>
                    <option value="Monthly">Monthly</option>
                    <option value="Daily">Daily</option>
                    <option value="Hourly">Hourly</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label small">Monthly CTC (₹)</label>
                <input name="monthly_ctc" type="number" step="0.01" class="form-control form-control-sm" placeholder="e.g., 45000" required>
              </div>
              <div class="col-md-4">
                <label class="form-label small">Bank A/c (optional)</label>
                <input name="bank_account" class="form-control form-control-sm">
              </div>

              <div class="col-md-4">
                <label class="form-label small">PAN (optional)</label>
                <input name="pan" class="form-control form-control-sm">
              </div>
              <div class="col-md-4">
                <label class="form-label small">UAN (optional)</label>
                <input name="uan" class="form-control form-control-sm">
              </div>
              <div class="col-md-4">
                <label class="form-label small">ESIC (optional)</label>
                <input name="esic" class="form-control form-control-sm">
              </div>

              <div class="col-12">
                <label class="form-label small">Notes</label>
                <textarea name="notes" class="form-control form-control-sm" rows="2"></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary">Save Employee</button>
          </div>
      </form>
    </div>
  </div>
</div>
@endsection
