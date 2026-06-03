@extends('CA.layouts.app')

@section('content')
<div class="card shadow-sm mb-3">
  <div class="card-body">
    <h6 class="mb-1">Invoices Repository</h6>
    <div class="small-help">Company is locked. Proforma + Tax invoices. Partial receipts generate a Tax Invoice for received amount + new proforma for balance.</div>
    <hr>
    <form class="row g-2 align-items-end" action="{{ route('ca.invoices') }}" method="GET">
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
        <label class="form-label small">Invoice Type</label>
        <select name="type" class="form-select form-select-sm">
          <option value="">All</option>
          <option value="Proforma" {{ request('type') == 'Proforma' ? 'selected' : '' }}>Proforma</option>
          <option value="Tax Invoice" {{ request('type') == 'Tax Invoice' ? 'selected' : '' }}>Tax Invoice</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small">Payment Status</label>
        <select name="status" class="form-select form-select-sm">
          <option value="">All</option>
          <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
          <option value="Partial" {{ request('status') == 'Partial' ? 'selected' : '' }}>Partial</option>
          <option value="Received" {{ request('status') == 'Received' ? 'selected' : '' }}>Received</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small">Search</label>
        <input name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Invoice no / client / amount">
      </div>
      <div class="col-md-1 d-grid">
        <button type="submit" class="btn btn-sm btn-primary">Go</button>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span>Invoices List</span>
    <div class="d-flex gap-2">
      <a href="{{ route('ca.invoices.download-attachments', request()->query()) }}" class="btn btn-sm btn-primary">Download Attachments (ZIP)</a>
      <button class="btn btn-sm btn-outline-secondary">Export List (Excel)</button>
    </div>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th>Invoice No</th><th>Client</th><th>Invoice Type</th>
            <th class="text-end">Total</th><th class="text-end">Received</th><th class="text-end">Balance</th>
            <th>Status</th><th>Reference</th><th>Download</th>
          </tr>
        </thead>
        <tbody>
          @forelse($invoices as $invoice)
            @php
              $clientName = is_array($invoice->client_details) ? ($invoice->client_details['name'] ?? 'N/A') : 'N/A';
              
              $badgeClass = 'bg-secondary';
              if ($invoice->status === 'paid' || $invoice->status === 'received') $badgeClass = 'bg-success';
              elseif ($invoice->status === 'partial') $badgeClass = 'bg-warning text-dark';
              elseif ($invoice->status === 'pending') $badgeClass = 'bg-info text-white';
              elseif ($invoice->status === 'overdue') $badgeClass = 'bg-danger';
            @endphp
            <tr data-company="{{ $invoice->company->name ?? 'N/A' }}">
              <td>{{ $invoice->invoice_number }}</td>
              <td>{{ $clientName }}</td>
              <td><span class="chip">{{ $invoice->type === 'proforma' ? 'Proforma' : 'Tax Invoice' }}</span></td>
              <td class="text-end">₹ {{ number_format($invoice->total_amount, 2) }}</td>
              <td class="text-end">₹ {{ number_format($invoice->received_amount, 2) }}</td>
              <td class="text-end">₹ {{ number_format($invoice->balance_amount, 2) }}</td>
              <td><span class="badge {{ $badgeClass }}">{{ ucfirst($invoice->status) }}</span></td>
              <td><code class="smallcode">{{ $invoice->id }}</code></td>
              <td>
                @if($invoice->attachments->count() > 0)
                  @foreach($invoice->attachments as $attachment)
                    <a class="link-muted d-block" href="{{ asset($attachment->file_path ?? $attachment->path) }}" target="_blank">{{ basename($attachment->file_path ?? $attachment->path) }}</a>
                  @endforeach
                @else
                  <span class="text-muted small">None</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="text-center text-muted py-3">No invoices found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    
    <div class="mt-3 px-3">
      {{ $invoices->links('pagination::bootstrap-5') }}
    </div>
  </div>
</div>

  </div>
@endsection
