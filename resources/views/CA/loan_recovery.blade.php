@extends('CA.layouts.app')

@section('content')
<div class="card shadow-sm mb-3">
  <div class="card-body">
    <h6 class="mb-1">Loan Recovery (Receipts linked to Loans)</h6>
    <div class="small-help">Company is locked. Each recovery receipt must reference the original Loan ID and include proof.</div>
    <hr>
    <form class="row g-2 align-items-end" action="{{ route('ca.loan-recovery') }}" method="GET">
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
        <label class="form-label small">From</label>
        <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control form-control-sm">
      </div>
      <div class="col-md-2">
        <label class="form-label small">To</label>
        <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control form-control-sm">
      </div>
      <div class="col-md-3">
        <label class="form-label small">Loan Reference</label>
        <input name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="e.g. LOAN-00007">
      </div>
      <div class="col-md-2">
        <label class="form-label small">Mode</label>
        <select name="mode" class="form-select form-select-sm">
          <option value="all">All</option>
          <option value="Bank" {{ request('mode') == 'Bank' ? 'selected' : '' }}>Bank</option>
          <option value="UPI" {{ request('mode') == 'UPI' ? 'selected' : '' }}>UPI</option>
          <option value="Cash" {{ request('mode') == 'Cash' ? 'selected' : '' }}>Cash</option>
        </select>
      </div>
      <div class="col-md-1 d-grid">
        <button type="submit" class="btn btn-sm btn-primary">Go</button>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span>Recovery Receipts</span>
    <div class="d-flex gap-2">
      <button class="btn btn-sm btn-outline-secondary">Export Excel</button>
      <button class="btn btn-sm btn-outline-secondary">Export PDF</button>
      <a href="{{ route('ca.loan-recovery.download-attachments', request()->query()) }}" class="btn btn-sm btn-primary">Download Proofs (ZIP)</a>
    </div>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th>Date</th><th>Party</th><th>Loan Ref</th><th>Receipt Ref</th>
            <th class="text-end">Amount</th><th>Mode</th><th>UTR/Txn</th>
            <th>Comments</th><th>Proof</th>
          </tr>
        </thead>
        <tbody>
          @forelse($paginated as $recovery)
            <tr data-company="{{ $recovery->company->name ?? 'N/A' }}">
              <td>{{ $recovery->transaction_date ? $recovery->transaction_date->format('d-m-Y') : 'N/A' }}</td>
              <td>{{ $recovery->party->name ?? 'N/A' }}</td>
              <td><code class="smallcode">{{ $recovery->originalAdvance->reference_number ?? 'N/A' }}</code></td>
              <td><code class="smallcode">{{ $recovery->reference_number }}</code></td>
              <td class="text-end">₹ {{ number_format($recovery->amount, 2) }}</td>
              <td>
                @if(stripos($recovery->comments, 'upi') !== false)
                  <span class="chip tag-rec">UPI</span>
                @elseif(stripos($recovery->comments, 'cash') !== false)
                  <span class="chip tag-rec">Cash</span>
                @else
                  <span class="chip tag-rec">Bank</span>
                @endif
              </td>
              <td>{{ $recovery->purpose ?? 'N/A' }}</td>
              <td>{{ $recovery->comments }}</td>
              <td>
                @if($recovery->attachments->count() > 0)
                  @foreach($recovery->attachments as $attachment)
                    <a class="link-muted d-block" href="{{ asset($attachment->file_path) }}" target="_blank">{{ basename($attachment->file_path) }}</a>
                  @endforeach
                @else
                  <span class="text-danger small">Missing</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="text-center text-muted py-3">No recoveries found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    
    <div class="mt-3 px-3">
      {{ $paginated->links('pagination::bootstrap-5') }}
    </div>
  </div>
  <div class="card-footer small-help">Every recovery updates the outstanding balance of the linked loan reference.</div>
</div>

  </div>
@endsection
