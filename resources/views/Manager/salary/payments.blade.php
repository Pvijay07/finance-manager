@extends('Manager.layouts.app')
@section('title', 'Salary Payments')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid"></div>
    </section>

    <section class="content">
        <div class="container-fluid">
            @include('Manager.salary.nav')

            <div class="card shadow-sm mb-3">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h6 class="mb-1 fw-bold">Salary Payments</h6>
                        <div class="small-help text-muted">Locked sheets create Upcoming Payments. Mark Paid / Partial here.</div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ route('manager.salary.payments') }}" class="row g-2 align-items-end mb-3">
                        <div class="col-md-3">
                            <label class="form-label small">Company</label>
                            <select name="company_id" class="form-select form-select-sm">
                                <option value="">All Companies</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-grid">
                            <button type="submit" class="btn btn-sm btn-primary">Apply</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Company</th>
                                    <th>Month</th>
                                    <th class="text-end">Net Pay (₹)</th>
                                    <th class="text-end">Paid (₹)</th>
                                    <th class="text-end">Balance (₹)</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $sheet)
                                <tr>
                                    <td class="fw-bold">{{ $sheet->company->name }}</td>
                                    <td>{{ $sheet->month_year }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($sheet->total_net_pay, 2) }}</td>
                                    <td class="text-end text-success">{{ number_format($sheet->total_paid, 2) }}</td>
                                    <td class="text-end text-danger">{{ number_format($sheet->total_net_pay - $sheet->total_paid, 2) }}</td>
                                    <td>{{ $sheet->due_date ? \Carbon\Carbon::parse($sheet->due_date)->format('d-m-Y') : 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $sheet->status == 'Paid' ? 'success' : 'warning' }}">{{ $sheet->status }}</span>
                                    </td>
                                    <td class="text-end">
                                        @if($sheet->status != 'Paid')
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#markPaidModal{{ $sheet->id }}">Pay</button>
                                        @else
                                            <button class="btn btn-sm btn-outline-secondary" disabled>Paid</button>
                                        @endif
                                        <a href="{{ route('manager.salary.editSheet', $sheet->id) }}" class="btn btn-sm btn-outline-info">View</a>
                                    </td>
                                </tr>
                                
                                @if($sheet->status != 'Paid')
                                <!-- Mark Paid Modal -->
                                <div class="modal fade" id="markPaidModal{{ $sheet->id }}" tabindex="-1" aria-hidden="true">
                                  <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                      <div class="modal-header">
                                        <div>
                                          <h5 class="modal-title fw-bold">Mark Salary Payment</h5>
                                          <div class="small-help text-muted">Supports partial payments.</div>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                      </div>
                                      <form action="{{ route('manager.salary.payments.mark') }}" method="POST">
                                          @csrf
                                          <input type="hidden" name="salary_sheet_id" value="{{ $sheet->id }}">
                                          <div class="modal-body text-start">
                                            <div class="row g-3">
                                              <div class="col-md-6">
                                                <label class="form-label small">Payment Date</label>
                                                <input name="payment_date" type="date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                                              </div>
                                              <div class="col-md-6">
                                                <label class="form-label small">Mode</label>
                                                <select name="payment_mode" class="form-select form-select-sm">
                                                    <option value="Bank Transfer">Bank Transfer</option>
                                                    <option value="Cash">Cash</option>
                                                    <option value="Mixed">Mixed</option>
                                                </select>
                                              </div>
                                              <div class="col-md-6">
                                                <label class="form-label small">Balance Pending (₹)</label>
                                                <input type="number" class="form-control form-control-sm" value="{{ $sheet->total_net_pay - $sheet->total_paid }}" readonly>
                                              </div>
                                              <div class="col-md-6">
                                                <label class="form-label small">Paid Now (₹)</label>
                                                <input name="amount" type="number" step="0.01" max="{{ $sheet->total_net_pay - $sheet->total_paid }}" class="form-control form-control-sm" value="{{ $sheet->total_net_pay - $sheet->total_paid }}" required>
                                              </div>
                                              <div class="col-12">
                                                <label class="form-label small">UTR / Reference</label>
                                                <input name="reference" class="form-control form-control-sm">
                                              </div>
                                              <div class="col-12">
                                                <label class="form-label small">Notes</label>
                                                <textarea name="notes" class="form-control form-control-sm" rows="2"></textarea>
                                              </div>
                                            </div>
                                          </div>
                                          <div class="modal-footer">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-sm btn-primary">Save Payment</button>
                                          </div>
                                      </form>
                                    </div>
                                  </div>
                                </div>
                                @endif
                                
                                @empty
                                <tr><td colspan="8" class="text-center text-muted">No pending payments found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-2">{{ $payments->links() }}</div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
