@extends('CA.layouts.app')

@section('content')
<div class="card shadow-sm mb-3">
  <div class="card-body">
    <h6 class="mb-1">Loans Issued (Advance Given / Loan Receivable)</h6>
    <div class="small-help">Company is locked. Not an expense. Each loan has reference id + proof/agreement.</div>
    <hr>
    <form class="row g-2 align-items-end" action="{{ route('ca.loans-issued') }}" method="GET">
      <div class="col-md-2">
        <label class="form-label small">Company</label>
        <select name="company_id" data-company-select class="form-select form-select-sm">
          <option value="">All</option>
          @foreach($companies as $company)
            <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small">Status</label>
        <select name="status" class="form-select form-select-sm">
          <option value="all">All</option>
          <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
          <option value="partially_recovered" {{ request('status') == 'partially_recovered' ? 'selected' : '' }}>Partially Recovered</option>
          <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small">From</label>
        <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control form-control-sm">
      </div>
      <div class="col-md-2">
        <label class="form-label small">To</label>
        <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control form-control-sm">
      </div>
      <div class="col-md-3">
        <label class="form-label small">Search</label>
        <input name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Party / purpose / ref">
      </div>
      <div class="col-md-1 d-grid">
        <button type="submit" class="btn btn-sm btn-primary">Go</button>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span>Loans / Advances Given</span>
    <div class="d-flex gap-2">
      <button class="btn btn-sm btn-outline-secondary">Export Excel</button>
      <button class="btn btn-sm btn-outline-secondary">Export PDF</button>
      <a href="{{ route('ca.loans-issued.download-attachments', request()->query()) }}" class="btn btn-sm btn-primary">Download Agreements (ZIP)</a>
    </div>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th>Date</th><th>Party</th><th>Purpose</th>
            <th class="text-end">Issued</th><th class="text-end">Recovered</th><th class="text-end">Outstanding</th>
            <th>Expected Recovery</th><th>Status</th><th>Reference</th><th>Attachment</th>
          </tr>
        </thead>
        <tbody>
          @forelse($paginated as $loan)
            <tr data-company="{{ $loan->company->name ?? 'N/A' }}">
              <td>{{ $loan->transaction_date ? $loan->transaction_date->format('d-m-Y') : 'N/A' }}</td>
              <td>{{ $loan->party->name ?? 'N/A' }}</td>
              <td>{{ $loan->purpose }}</td>
              <td class="text-end">₹ {{ number_format($loan->amount, 2) }}</td>
              <td class="text-end">₹ {{ number_format($loan->recovered_amount, 2) }}</td>
              <td class="text-end">₹ {{ number_format($loan->outstanding_amount, 2) }}</td>
              <td>{{ $loan->expected_recovery_date ? $loan->expected_recovery_date->format('d-m-Y') : '—' }}</td>
              <td>
                @if($loan->status == 'outstanding')
                  <span class="chip tag-loan">Open</span>
                @elseif($loan->status == 'partially_recovered')
                  <span class="chip tag-loan">Partially Recovered</span>
                @elseif($loan->status == 'recovered')
                  <span class="chip tag-loan">Closed</span>
                @else
                  <span class="chip tag-loan">{{ ucfirst(str_replace('_', ' ', $loan->status)) }}</span>
                @endif
              </td>
              <td><code class="smallcode">{{ $loan->reference_number }}</code></td>
              <td>
                @if($loan->attachments->count() > 0)
                  @foreach($loan->attachments as $attachment)
                    <a class="link-muted d-block" href="{{ asset($attachment->file_path) }}" target="_blank">{{ basename($attachment->file_path) }}</a>
                  @endforeach
                @else
                  <span class="text-danger small">Missing</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="10" class="text-center text-muted py-3">No loans found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-3 px-3">
      {{ $paginated->links('pagination::bootstrap-5') }}
    </div>
  </div>
  <div class="card-footer small-help">Recoveries are listed in <strong>Loan Recovery</strong> page and linked by Reference.</div>
</div>

  </div>
@endsection
