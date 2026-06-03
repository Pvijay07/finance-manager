@extends('CA.layouts.app')

@section('content')
<div class="card shadow-sm mb-3">
  <div class="card-body">
    <h6 class="mb-1">Statements Download</h6>
    <div class="small-help">Company is locked. Statement includes purpose comments + links to attachments (invoice/bill/receipt).</div>
    <hr>
    <form class="row g-2 align-items-end" action="{{ route('ca.statements') }}" method="GET">
      <div class="col-md-3">
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
        <label class="form-label small">Include</label>
        <select name="include" class="form-select form-select-sm">
          <option value="all" {{ request('include') == 'all' ? 'selected' : '' }}>Expenses + Income + Payables + Loans</option>
          <option value="expenses" {{ request('include') == 'expenses' ? 'selected' : '' }}>Only Expenses</option>
          <option value="income" {{ request('include') == 'income' ? 'selected' : '' }}>Only Income</option>
          <option value="payables" {{ request('include') == 'payables' ? 'selected' : '' }}>Only Payables</option>
          <option value="loans" {{ request('include') == 'loans' ? 'selected' : '' }}>Only Loans</option>
        </select>
      </div>
      <div class="col-md-2 d-grid">
        <button type="submit" class="btn btn-sm btn-primary">Generate</button>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span>Statement Preview</span>
    <div class="d-flex gap-2">
      <button class="btn btn-sm btn-outline-secondary">Download Excel</button>
      <button class="btn btn-sm btn-outline-secondary">Download PDF</button>
      <a href="{{ route('ca.statements.download-attachments', request()->query()) }}" class="btn btn-sm btn-primary">Download Attachments (ZIP)</a>
    </div>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th>Date</th><th>Type</th><th>Category</th><th>Description</th>
            <th class="text-end">Amount</th><th>Purpose / Comments</th><th>Reference</th><th>Attachments</th>
          </tr>
        </thead>
        <tbody>
          @forelse($paginated as $record)
            <tr data-company="{{ $record->company_name }}">
              <td>{{ $record->date ? $record->date->format('d-m-Y') : 'N/A' }}</td>
              <td>
                @if($record->model == 'Expense')
                  <span class="chip tag-exp">{{ $record->type_label }}</span>
                @elseif($record->model == 'Income')
                  <span class="chip tag-inc">{{ $record->type_label }}</span>
                @elseif($record->model == 'Tax')
                  <span class="chip tag-gst">{{ $record->type_label }}</span>
                @elseif($record->model == 'Advance')
                  <span class="chip tag-loan">{{ $record->type_label }}</span>
                @endif
              </td>
              <td>{{ $record->category }}</td>
              <td>{{ $record->description }}</td>
              <td class="text-end">₹ {{ number_format($record->amount, 2) }}</td>
              <td>{{ Str::limit($record->comments, 40) }}</td>
              <td><code class="smallcode">{{ $record->reference }}</code></td>
              <td>
                @if($record->attachments && $record->attachments->count() > 0)
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
              <td colspan="8" class="text-center text-muted py-3">No statement records found for selected period.</td>
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
