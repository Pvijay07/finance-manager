@extends('Manager.layouts.app')
@section('title', 'Edit Salary Sheet')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid"></div>
    </section>

    <section class="content">
        <div class="container-fluid">
            @include('Manager.salary.nav')

            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h6 class="mb-1 fw-bold">Create / Edit Salary Sheet — {{ $sheet->company->name }} ({{ $sheet->month_year }})</h6>
                            <div class="small-help text-muted">Manager fills attendance + variable components. System calculates Gross, Deductions, Net.</div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @if($sheet->status == 'Draft')
                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#lockModal">Lock Sheet</button>
                            @else
                                <span class="badge bg-{{ $sheet->status == 'Paid' ? 'success' : 'warning' }} fs-6">{{ $sheet->status }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('manager.salary.saveSheet', $sheet->id) }}" method="POST">
                @csrf
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Employee Rows</span>
                        <div class="d-flex align-items-center gap-2">
                            <label class="form-label small mb-0">Standard Days:</label>
                            <input name="standard_days" type="number" class="form-control form-control-sm w-auto text-end" value="{{ $sheet->standard_days }}" {{ $sheet->status != 'Draft' ? 'readonly' : '' }}>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0 align-middle table-bordered" id="sheetTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Employee</th>
                                        <th class="text-center" style="width: 70px;">Present</th>
                                        <th class="text-center" style="width: 70px;">LOP</th>
                                        <th class="text-center" style="width: 80px;">OT (₹)</th>
                                        <th class="text-center bg-success-subtle">Basic</th>
                                        <th class="text-center bg-success-subtle">HRA</th>
                                        <th class="text-center bg-success-subtle">Allowance</th>
                                        <th class="text-center bg-success-subtle">Incentive</th>
                                        <th class="text-center bg-success-subtle">Bonus</th>
                                        <th class="text-center bg-danger-subtle">PF</th>
                                        <th class="text-center bg-danger-subtle">ESIC</th>
                                        <th class="text-center bg-danger-subtle">TDS</th>
                                        <th class="text-center bg-danger-subtle">Adv Rec</th>
                                        <th class="text-center bg-danger-subtle">Other Ded</th>
                                        <th class="text-end">Gross</th>
                                        <th class="text-end">Deductions</th>
                                        <th class="text-end text-bg-primary">Net Pay</th>
                                        @if($sheet->status != 'Draft')
                                            <th class="text-center">Actions</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sheet->items as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $item->employee->full_name }}</div>
                                            <div class="small text-muted">{{ $item->employee->emp_id }}</div>
                                        </td>
                                        <td><input name="items[{{ $item->id }}][present_days]" type="number" step="0.5" class="form-control form-control-sm text-center px-1" value="{{ $item->present_days }}" {{ $sheet->status != 'Draft' ? 'readonly' : '' }}></td>
                                        <td><input name="items[{{ $item->id }}][lop_days]" type="number" step="0.5" class="form-control form-control-sm text-center px-1" value="{{ $item->lop_days }}" {{ $sheet->status != 'Draft' ? 'readonly' : '' }}></td>
                                        <td><input name="items[{{ $item->id }}][ot_amount]" type="number" class="form-control form-control-sm text-end px-1 earn" value="{{ $item->ot_amount }}" {{ $sheet->status != 'Draft' ? 'readonly' : '' }}></td>
                                        
                                        <td><input name="items[{{ $item->id }}][basic]" type="number" class="form-control form-control-sm text-end px-1 earn bg-success-subtle" value="{{ $item->basic }}" {{ $sheet->status != 'Draft' ? 'readonly' : '' }}></td>
                                        <td><input name="items[{{ $item->id }}][hra]" type="number" class="form-control form-control-sm text-end px-1 earn bg-success-subtle" value="{{ $item->hra }}" {{ $sheet->status != 'Draft' ? 'readonly' : '' }}></td>
                                        <td><input name="items[{{ $item->id }}][allowance]" type="number" class="form-control form-control-sm text-end px-1 earn bg-success-subtle" value="{{ $item->allowance }}" {{ $sheet->status != 'Draft' ? 'readonly' : '' }}></td>
                                        <td><input name="items[{{ $item->id }}][incentive]" type="number" class="form-control form-control-sm text-end px-1 earn bg-success-subtle" value="{{ $item->incentive }}" {{ $sheet->status != 'Draft' ? 'readonly' : '' }}></td>
                                        <td><input name="items[{{ $item->id }}][bonus]" type="number" class="form-control form-control-sm text-end px-1 earn bg-success-subtle" value="{{ $item->bonus }}" {{ $sheet->status != 'Draft' ? 'readonly' : '' }}></td>
                                        
                                        <td><input name="items[{{ $item->id }}][pf]" type="number" class="form-control form-control-sm text-end px-1 ded bg-danger-subtle" value="{{ $item->pf }}" {{ $sheet->status != 'Draft' ? 'readonly' : '' }}></td>
                                        <td><input name="items[{{ $item->id }}][esic]" type="number" class="form-control form-control-sm text-end px-1 ded bg-danger-subtle" value="{{ $item->esic }}" {{ $sheet->status != 'Draft' ? 'readonly' : '' }}></td>
                                        <td><input name="items[{{ $item->id }}][tds]" type="number" class="form-control form-control-sm text-end px-1 ded bg-danger-subtle" value="{{ $item->tds }}" {{ $sheet->status != 'Draft' ? 'readonly' : '' }}></td>
                                        <td><input name="items[{{ $item->id }}][advance_rec]" type="number" class="form-control form-control-sm text-end px-1 ded bg-danger-subtle" value="{{ $item->advance_rec }}" {{ $sheet->status != 'Draft' ? 'readonly' : '' }}></td>
                                        <td><input name="items[{{ $item->id }}][other_ded]" type="number" class="form-control form-control-sm text-end px-1 ded bg-danger-subtle" value="{{ $item->other_ded }}" {{ $sheet->status != 'Draft' ? 'readonly' : '' }}></td>
                                        
                                        <td class="text-end fw-semibold gross align-middle">0</td>
                                        <td class="text-end fw-semibold deds align-middle">0</td>
                                        <td class="text-end fw-bold net align-middle fs-6">0</td>
                                        @if($sheet->status != 'Draft')
                                            <td class="text-center align-middle">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <a href="{{ route('manager.salary.payslip.download', $item->id) }}" class="btn btn-sm btn-outline-primary py-0 px-2" title="Download Payslip">
                                                        <i class="fas fa-download small"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-success py-0 px-2" title="Email Payslip" onclick="document.getElementById('email-form-{{ $item->id }}').submit();">
                                                        <i class="fas fa-envelope small"></i>
                                                    </button>
                                                </div>
                                                <form id="email-form-{{ $item->id }}" action="{{ route('manager.salary.payslip.email', $item->id) }}" method="POST" class="d-none">
                                                    @csrf
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="small-help text-muted">Sheet Totals</div>
                        <div class="d-flex gap-4">
                            <div><span class="small-help text-muted">Gross:</span> <strong class="fs-5" id="totGross">₹ 0</strong></div>
                            <div><span class="small-help text-muted">Deductions:</span> <strong class="fs-5 text-danger" id="totDeds">₹ 0</strong></div>
                            <div><span class="small-help text-muted">Net Pay:</span> <strong class="fs-5 text-success" id="totNet">₹ 0</strong></div>
                        </div>
                        <div class="d-flex gap-2">
                            @if($sheet->status == 'Draft')
                                <button type="submit" class="btn btn-sm btn-outline-primary">Save Draft</button>
                            @endif
                            <a class="btn btn-sm btn-secondary" href="{{ route('manager.salary.sheets') }}">Back</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

@if($sheet->status == 'Draft')
<!-- Lock modal -->
<div class="modal fade" id="lockModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title fw-bold">Lock Salary Sheet</h5>
          <div class="small-help text-muted">Locking creates an Upcoming Payment for total Net Pay.</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('manager.salary.lockSheet', $sheet->id) }}" method="POST">
          @csrf
          <div class="modal-body">
            <div class="alert alert-warning py-2 mb-3 small">
              <i class="fas fa-exclamation-triangle"></i> You cannot edit the sheet once locked! An Upcoming Debit will be created under <strong>Salaries</strong>. Please ensure you have <strong>Saved Draft</strong> first if you made any changes.
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label small">Salary Payout Due Date</label>
                <input name="due_date" type="date" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-6">
                <label class="form-label small">Default Payment Mode</label>
                <select name="payment_mode" class="form-select form-select-sm">
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Cash">Cash</option>
                    <option value="Mixed">Mixed</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label small">Notes for CA (optional)</label>
                <textarea name="notes" class="form-control form-control-sm" rows="2"></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-success">Lock & Go to Payments</button>
          </div>
      </form>
    </div>
  </div>
</div>
@endif

<script>
  function fmtINR(n){ try { return new Intl.NumberFormat('en-IN').format(n);} catch(e){ return n; } }
  function num(v){ const x = Number(v); return isNaN(x) ? 0 : x; }
  
  function recalc(){
    const rows = document.querySelectorAll('#sheetTable tbody tr');
    let tg=0, td=0, tn=0;
    
    rows.forEach(r => {
      let gross = 0, deds = 0;
      r.querySelectorAll('input.earn').forEach(i => gross += num(i.value));
      r.querySelectorAll('input.ded').forEach(i => deds += num(i.value));
      
      const net = Math.max(0, gross - deds);
      r.querySelector('.gross').textContent = fmtINR(gross);
      r.querySelector('.deds').textContent = fmtINR(deds);
      r.querySelector('.net').textContent = fmtINR(net);
      
      tg += gross; 
      td += deds; 
      tn += net;
    });
    
    document.getElementById('totGross').textContent = '₹ ' + fmtINR(tg);
    document.getElementById('totDeds').textContent = '₹ ' + fmtINR(td);
    document.getElementById('totNet').textContent = '₹ ' + fmtINR(tn);
  }
  
  document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('#sheetTable input').forEach(i => i.addEventListener('input', recalc));
      recalc();
  });
</script>
@endsection
