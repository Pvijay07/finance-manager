@extends('CA.layouts.app')

@section('content')
<div class="row g-3 mb-3">
  <div class="col-md-3"><div class="card kpi shadow-sm"><div class="card-body">
    <div class="label">Pending Docs</div><div class="value">{{ $pendingDocsCount }}</div><div class="small-help">Missing invoice/bill attachments</div>
  </div></div></div>
  <div class="col-md-3"><div class="card kpi shadow-sm"><div class="card-body">
    <div class="label">Open Tasks</div><div class="value">{{ $openTasksCount }}</div><div class="small-help">Due this week</div>
  </div></div></div>
  <div class="col-md-3"><div class="card kpi shadow-sm"><div class="card-body">
    <div class="label">GST Input (This Month)</div><div class="value">₹ {{ number_format($gstInputThisMonth, 2) }}</div><div class="small-help">From expense invoices</div>
  </div></div></div>
  <div class="col-md-3"><div class="card kpi shadow-sm"><div class="card-body">
    <div class="label">Outstanding Loans</div><div class="value">₹ {{ number_format($outstandingLoans, 2) }}</div><div class="small-help">Receivable balance</div>
  </div></div></div>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card shadow-sm mb-3">
      <div class="card-header">Quick Downloads</div>
      <div class="card-body">
        <div class="row g-2">
          <div class="col-md-6 d-grid"><a data-ca-nav class="btn btn-sm btn-primary" href="{{ route('ca.statements') }}">Download Statement (PDF/Excel)</a></div>
          <div class="col-md-6 d-grid"><a data-ca-nav class="btn btn-sm btn-outline-primary" href="{{ route('ca.invoices') }}">Download Invoices (ZIP)</a></div>
          <div class="col-md-6 d-grid"><a data-ca-nav class="btn btn-sm btn-outline-primary" href="{{ route('ca.expense-taxes') }}">Expense Taxes (GST/TDS)</a></div>
          <div class="col-md-6 d-grid"><a data-ca-nav class="btn btn-sm btn-outline-secondary" href="{{ route('ca.salary-packs') }}">Salary Pack</a></div>
        </div>
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-header">Data Quality Alerts</div>
      <div class="card-body">
        <ul class="small mb-0">
          <li class="mb-2">2 records missing <strong>purpose comments</strong>.</li>
          <li class="mb-2">4 records missing <strong>attachments</strong>.</li>
          <li>1 loan entry missing <strong>agreement/proof</strong>.</li>
        </ul>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card shadow-sm mb-3">
      <div class="card-header">Recent Activity</div>
      <div class="card-body">
        <div class="small">
          @forelse($recentActivities as $activity)
            <div class="d-flex justify-content-between">
              <span>{{ ucfirst($activity->action) }} {{ class_basename($activity->model_type) }}</span>
              <span class="text-muted" title="{{ $activity->created_at }}">{{ $activity->created_at->diffForHumans() }}</span>
            </div>
            @if(!$loop->last)<hr class="my-2">@endif
          @empty
            <div class="text-muted">No recent activity.</div>
          @endforelse
        </div>
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-header">Task Reminders</div>
      <div class="card-body">
        <div class="d-grid gap-2">
          <a data-ca-nav class="btn btn-sm btn-outline-primary" href="{{ route('ca.tasks') }}">Open Tasks</a>
          <button class="btn btn-sm btn-outline-secondary">Email reminders (preview)</button>
        </div>
      </div>
    </div>
  </div>
</div>

  </div>
@endsection
