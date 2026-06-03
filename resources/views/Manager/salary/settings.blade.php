@extends('Manager.layouts.app')
@section('title', 'Salary Settings')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid"></div>
    </section>

    <section class="content">
        <div class="container-fluid">
            @include('Manager.salary.nav')

            <div class="row mb-3">
                <div class="col-12">
                    <form method="GET" action="{{ route('manager.salary.settings') }}" class="d-flex align-items-center gap-2">
                        <label class="mb-0 fw-bold">Select Company:</label>
                        <select name="company_id" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ $companyId == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>

            @if($companyId)
            <div class="row g-3">
                <div class="col-lg-7">
                    <div class="card shadow-sm mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Salary Components (Template)</span>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addComponentModal">Add Component</button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0 align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Type</th>
                                            <th>Component</th>
                                            <th>Default</th>
                                            <th>Editable by Manager?</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td><span class="badge bg-success">Earning</span></td><td>Basic</td><td>Fixed</td><td>No</td><td class="text-end"></td></tr>
                                        <tr><td><span class="badge bg-success">Earning</span></td><td>HRA</td><td>Fixed</td><td>No</td><td class="text-end"></td></tr>
                                        <tr><td><span class="badge bg-success">Earning</span></td><td>Allowance</td><td>Fixed/0</td><td>Yes</td><td class="text-end"></td></tr>
                                        <tr><td><span class="badge bg-success">Earning</span></td><td>Incentives</td><td>0</td><td>Yes</td><td class="text-end"></td></tr>
                                        <tr><td><span class="badge bg-success">Earning</span></td><td>Bonus</td><td>0</td><td>Yes</td><td class="text-end"></td></tr>
                                        <tr><td><span class="badge bg-danger">Deduction</span></td><td>PF</td><td>Optional</td><td>Yes</td><td class="text-end"></td></tr>
                                        <tr><td><span class="badge bg-danger">Deduction</span></td><td>ESIC</td><td>Optional</td><td>Yes</td><td class="text-end"></td></tr>
                                        <tr><td><span class="badge bg-danger">Deduction</span></td><td>TDS</td><td>Optional</td><td>Yes</td><td class="text-end"></td></tr>
                                        <tr><td><span class="badge bg-danger">Deduction</span></td><td>Advance / Loan Recovery</td><td>0</td><td>Yes</td><td class="text-end"></td></tr>
                                        <tr><td><span class="badge bg-danger">Deduction</span></td><td>Other</td><td>0</td><td>Yes</td><td class="text-end"></td></tr>
                                        
                                        @foreach($components as $comp)
                                        <tr>
                                            <td><span class="badge bg-{{ $comp->type == 'earning' ? 'success' : 'danger' }}">{{ ucfirst($comp->type) }}</span></td>
                                            <td>{{ $comp->name }}</td>
                                            <td>{{ $comp->default_value ?? '0' }}</td>
                                            <td>{{ $comp->is_editable ? 'Yes' : 'No' }}</td>
                                            <td class="text-end"><button class="btn btn-sm btn-outline-secondary">Edit</button></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer small-help text-muted">These become columns in Salary Sheet (Create/Edit).</div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card shadow-sm mb-3">
                        <div class="card-header fw-bold">Default Rules</div>
                        <div class="card-body">
                            <form class="row g-3" method="POST" action="{{ route('manager.salary.settings.update') }}">
                                @csrf
                                <input type="hidden" name="company_id" value="{{ $companyId }}">
                                
                                <div class="col-md-6">
                                    <label class="form-label small">Standard Working Days</label>
                                    <input name="standard_days" type="number" class="form-control form-control-sm" value="{{ $settings->standard_days ?? 30 }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">OT Rate (₹) (optional)</label>
                                    <input name="ot_rate" type="number" step="0.01" class="form-control form-control-sm" placeholder="e.g., 250" value="{{ $settings->ot_rate ?? '' }}">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small">LOP Rule</label>
                                    <select name="lop_rule" class="form-select form-select-sm">
                                        <option value="CTC / Standard Days" {{ ($settings->lop_rule ?? '') == 'CTC / Standard Days' ? 'selected' : '' }}>CTC / Standard Days</option>
                                        <option value="Basic / Standard Days" {{ ($settings->lop_rule ?? '') == 'Basic / Standard Days' ? 'selected' : '' }}>Basic / Standard Days</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small">Notes</label>
                                    <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Keep simple. CA handles compliance in their system.">{{ $settings->notes ?? '' }}</textarea>
                                </div>
                                <div class="col-12 d-grid">
                                    <button type="submit" class="btn btn-sm btn-primary">Save Rules</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @else
                <div class="alert alert-info">Please select a company to configure settings.</div>
            @endif
        </div>
    </section>
</div>

<!-- Add Component Modal -->
<div class="modal fade" id="addComponentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title fw-bold">Add Salary Component</h5>
          <div class="small-help text-muted">Add earning or deduction column for salary sheet.</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('manager.salary.components.store') }}" method="POST">
          @csrf
          <input type="hidden" name="company_id" value="{{ $companyId }}">
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label small">Type</label>
                <select name="type" class="form-select form-select-sm" required>
                    <option value="earning">Earning</option>
                    <option value="deduction">Deduction</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label small">Component Name</label>
                <input name="name" class="form-control form-control-sm" placeholder="e.g., Attendance Bonus" required>
              </div>
              <div class="col-md-4">
                <label class="form-label small">Default</label>
                <input name="default_value" class="form-control form-control-sm" placeholder="Fixed / 0 / Optional">
              </div>
              <div class="col-md-4">
                <label class="form-label small">Editable by Manager?</label>
                <select name="is_editable" class="form-select form-select-sm" required>
                    <option value="1" selected>Yes</option>
                    <option value="0">No</option>
                </select>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary">Save Component</button>
          </div>
      </form>
    </div>
  </div>
</div>
@endsection
