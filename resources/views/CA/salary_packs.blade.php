@extends('CA.layouts.app')

@section('content')
<div class="card shadow-sm mb-3">
  <div class="card-body">
    <h6 class="mb-1">Salary Packs for CA</h6>
    <div class="small-help">Company is locked. Each pack contains salary sheet + attendance attachment + payment proof.</div>
    <hr>
    <form class="row g-2 align-items-end" action="{{ route('ca.salary-packs') }}" method="GET">
      <div class="col-md-3"><label class="form-label small">Company</label>
        <select name="company_id" data-company-select class="form-select form-select-sm">
          <option value="">All</option>
          @foreach($companies as $company)
            <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3"><label class="form-label small">Month</label>
        <select name="month_year" class="form-select form-select-sm">
          <option value="">All</option>
          @foreach($availableMonths as $month)
            <option value="{{ $month }}" {{ request('month_year') == $month ? 'selected' : '' }}>{{ date('M Y', strtotime($month . '-01')) }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3"><label class="form-label small">Status</label>
        <select name="status" class="form-select form-select-sm">
          <option value="all">All</option>
          <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
          <option value="locked" {{ request('status') == 'locked' ? 'selected' : '' }}>Locked</option>
          <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
        </select>
      </div>
      <div class="col-md-3 d-grid"><button type="submit" class="btn btn-sm btn-primary">Apply</button></div>
    </form>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span>Salary Packs</span>
    <a href="{{ route('ca.salary-packs.download-all', request()->query()) }}" class="btn btn-sm btn-primary">Download Selected Packs (ZIP)</a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th>Month</th><th>Employees</th>
            <th class="text-end">Net Pay</th><th>Sheet</th><th>Attendance</th><th>Payment Proof</th><th>Status</th><th class="text-end">Download</th>
          </tr>
        </thead>
        <tbody>
          @forelse($paginated as $sheet)
            <tr data-company="{{ $sheet->company->name ?? 'N/A' }}">
              <td>{{ $sheet->month_year ? date('M Y', strtotime($sheet->month_year . '-01')) : 'N/A' }}</td>
              <td>{{ $sheet->items->count() }}</td>
              <td class="text-end">₹ {{ number_format($sheet->total_net_pay, 2) }}</td>
              <td>
                @if(file_exists(public_path("downloads/salary_sheet_{$sheet->id}.pdf")))
                  <a class="link-muted" href="{{ asset("downloads/salary_sheet_{$sheet->id}.pdf") }}" target="_blank">Sheet.pdf</a>
                @else
                  <span class="text-muted small">None</span>
                @endif
              </td>
              <td>
                @php
                  // Count total attendance / proof docs attached across all payments
                  $proofCount = 0;
                  foreach($sheet->payments as $payment) {
                    if (!empty($payment->proof_path)) {
                      $proofCount++;
                    }
                  }
                @endphp
                @if($proofCount > 0)
                  <span class="badge bg-secondary">{{ $proofCount }} Proofs</span>
                @else
                  <span class="text-muted small">Pending</span>
                @endif
              </td>
              <td>
                @if($sheet->status == 'paid')
                  <span class="badge bg-success">Paid</span>
                @elseif($sheet->status == 'locked')
                  <span class="badge bg-warning text-dark">Locked</span>
                @else
                  <span class="badge bg-secondary">Draft</span>
                @endif
              </td>
              <td class="text-end">
                <a href="{{ route('ca.salary-packs.download', $sheet->id) }}" class="btn btn-sm btn-outline-secondary">Download Pack</a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-3">No salary packs found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    
    <div class="mt-3 px-3">
      {{ $paginated->links('pagination::bootstrap-5') }}
    </div>
  </div>
</div>

  </div>
@endsection
