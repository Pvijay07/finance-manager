@extends('CA.layouts.app')

@section('content')
<div class="card shadow-sm mb-3">
  <div class="card-body">
    <h6 class="mb-1">Expense Taxes (GST & TDS Paid)</h6>
    <div class="small-help">Company is locked. Shows GST/TDS amounts captured from expense invoices + reference details.</div>
    <hr>
    <form class="row g-2 align-items-end" action="{{ route('ca.expense-taxes') }}" method="GET">
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
        <label class="form-label small">Tax Type</label>
        <select name="tax_type" class="form-select form-select-sm">
          <option value="all">All</option>
          <option value="gst" {{ request('tax_type') == 'gst' ? 'selected' : '' }}>GST Input</option>
          <option value="tds" {{ request('tax_type') == 'tds' ? 'selected' : '' }}>TDS</option>
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
        <input name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Vendor / invoice no / expense ref">
      </div>
      <div class="col-md-1 d-grid">
        <button type="submit" class="btn btn-sm btn-primary">Go</button>
      </div>
    </form>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-4"><div class="card kpi shadow-sm"><div class="card-body">
    <div class="label">GST Input Total</div><div class="value">₹ {{ number_format($gstInputTotal, 2) }}</div><div class="small-help">Selected period</div>
  </div></div></div>
  <div class="col-md-4"><div class="card kpi shadow-sm"><div class="card-body">
    <div class="label">TDS Total</div><div class="value">₹ {{ number_format($tdsTotal, 2) }}</div><div class="small-help">Selected period</div>
  </div></div></div>
  <div class="col-md-4"><div class="card kpi shadow-sm"><div class="card-body">
    <div class="label">Missing Invoices</div><div class="value">{{ $missingInvoices }}</div><div class="small-help">Follow-up required</div>
  </div></div></div>
</div>

<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span>Taxes from Expense Invoices</span>
    <div class="d-flex gap-2">
      <button class="btn btn-sm btn-outline-secondary">Export Excel</button>
      <button class="btn btn-sm btn-outline-secondary">Export PDF</button>
      <a href="{{ route('ca.expense-taxes.download-attachments', request()->query()) }}" class="btn btn-sm btn-primary">Download Attachments (ZIP)</a>
    </div>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th>Date</th><th>Vendor</th><th>Expense Category</th>
            <th>Tax Type</th><th class="text-end">Tax Amount</th><th class="text-end">Base Amount</th><th class="text-end">Total</th>
            <th>Reference</th><th>Invoice No</th><th>Comments</th><th>Attachment</th>
          </tr>
        </thead>
        <tbody>
          @forelse($paginated as $tax)
            @php
              $expense = $tax->taxable;
              $companyName = $expense && $expense->company ? $expense->company->name : 'N/A';
              $categoryName = $expense && $expense->categoryRelation ? $expense->categoryRelation->name : ($expense->expense_name ?? 'N/A');
              $vendorName = $expense ? ($expense->party_name ?? 'N/A') : 'N/A';
              $baseAmount = $tax->taxable_amount ?? ($expense->planned_amount ?? 0);
              $total = $baseAmount + $tax->tax_amount;
              $invoiceNo = $expense ? ($expense->invoice_number ?? 'N/A') : 'N/A';
            @endphp
            <tr data-company="{{ $companyName }}">
              <td>{{ $tax->created_at->format('d-m-Y') }}</td>
              <td>{{ $vendorName }}</td>
              <td>{{ $categoryName }}</td>
              <td>
                @if($tax->tax_type == 'gst')
                  <span class="chip tag-gst">GST Input</span>
                @else
                  <span class="chip tag-tds">TDS</span>
                @endif
              </td>
              <td class="text-end">₹ {{ number_format($tax->tax_amount, 2) }}</td>
              <td class="text-end">₹ {{ number_format($baseAmount, 2) }}</td>
              <td class="text-end">₹ {{ number_format($total, 2) }}</td>
              <td><code class="smallcode">EXP-{{ str_pad($expense->id ?? 0, 5, '0', STR_PAD_LEFT) }}</code></td>
              <td>{{ $invoiceNo }}</td>
              <td>{{ Str::limit($tax->payment_notes, 30) }}</td>
              <td>
                @if($expense && $expense->attachments->count() > 0)
                  @foreach($expense->attachments as $attachment)
                    <a class="link-muted d-block" href="{{ asset($attachment->file_path ?? $attachment->path) }}" target="_blank">{{ basename($attachment->file_path ?? $attachment->path) }}</a>
                  @endforeach
                @else
                  <span class="text-muted small">None</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="11" class="text-center text-muted py-3">No taxes found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    
    <div class="mt-3 px-3">
      {{ $paginated->links('pagination::bootstrap-5') }}
    </div>
  </div>
  <div class="card-footer small-help">Reference shows the internal record id. Invoice No is vendor invoice number.</div>
</div>

  </div>
@endsection
