@extends('CA.layouts.app')

@section('content')
<div class="card shadow-sm mb-3">
  <div class="card-body">
    <h6 class="mb-1">Records (Read-only)</h6>
    <div class="small-help">Company is locked. Filter expenses/incomes/taxes/loans. Each record shows reference + comments + attachment link.</div>
    <hr>
    <form class="row g-2 align-items-end" action="{{ route('ca.records') }}" method="GET">
      <div class="col-md-2">
        <label class="form-label small">Company</label>
        <select name="company_id" data-company-select class="form-select form-select-sm">
          <option value="">All</option>
          @foreach($companies as $company)
            <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2"><label class="form-label small">Type</label>
        <select name="type" class="form-select form-select-sm">
          <option value="">All</option>
          <option value="Expense" {{ request('type') == 'Expense' ? 'selected' : '' }}>Expense</option>
          <option value="Income" {{ request('type') == 'Income' ? 'selected' : '' }}>Income</option>
          <option value="Tax" {{ request('type') == 'Tax' ? 'selected' : '' }}>Tax</option>
          <option value="Loan" {{ request('type') == 'Loan' ? 'selected' : '' }}>Loan</option>
        </select>
      </div>
      <div class="col-md-2"><label class="form-label small">From</label><input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control form-control-sm"></div>
      <div class="col-md-2"><label class="form-label small">To</label><input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control form-control-sm"></div>
      <div class="col-md-3"><label class="form-label small">Search</label><input name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Category / description / ref"></div>
      <div class="col-md-1 d-grid"><button type="submit" class="btn btn-sm btn-primary">Go</button></div>
    </form>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span>Records List</span>
    <div class="d-flex gap-2">
      <button class="btn btn-sm btn-outline-secondary">Export Excel</button>
      <button class="btn btn-sm btn-outline-secondary">Export PDF</button>
      <a href="{{ route('ca.records.download-attachments', request()->query()) }}" class="btn btn-sm btn-primary">Download Attachments (ZIP)</a>
    </div>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th>Date</th><th>Type</th><th>Category</th><th>Description</th>
            <th class="text-end">Amount</th><th>Reference</th><th>Comments</th><th>Attachment</th>
          </tr>
        </thead>
        <tbody>
          @forelse($paginated as $record)
          <tr data-company="{{ $record->company_name }}">
            <td>{{ \Carbon\Carbon::parse($record->date)->format('d-m-Y') }}</td>
            <td><span class="chip">{{ $record->type_label }}</span></td>
            <td>{{ $record->category }}</td>
            <td>{{ Str::limit($record->description, 30) }}</td>
            <td class="text-end">₹ {{ number_format($record->amount, 2) }}</td>
            <td><code class="smallcode">{{ $record->reference }}</code></td>
            <td>{{ Str::limit($record->comments, 30) }}</td>
            <td>
              @if($record->attachments->count() > 0)
                @foreach($record->attachments as $attachment)
                  <a class="link-muted d-block" href="{{ asset($attachment->file_path ?? $attachment->path) }}" target="_blank">{{ basename($attachment->file_path ?? $attachment->path) }}</a>
                @endforeach
              @else
                <span class="text-muted small">None</span>
              @endif
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="8" class="text-center text-muted py-3">No records found.</td>
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
