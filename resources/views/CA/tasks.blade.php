@extends('CA.layouts.app')

@section('content')
<div class="card shadow-sm mb-3">
  <div class="card-body">
    <h6 class="mb-1">Default CA Tasks & Reminders</h6>
    <div class="small-help">Company is locked. CA can change only task status. Reminders can be emailed before due date.</div>
    <hr>
    <form class="row g-2 align-items-end" action="{{ route('ca.tasks') }}" method="GET">
      <div class="col-md-3">
        <label class="form-label small">Company</label>
        <select name="company_id" data-company-select class="form-select form-select-sm">
          <option value="">All</option>
          @foreach($companies as $company)
            <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small">Task Type</label>
        <select name="task_type" class="form-select form-select-sm">
          <option value="all">All</option>
          @foreach($availableTaskTypes as $type)
            <option value="{{ $type }}" {{ request('task_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small">Status</label>
        <select name="status" class="form-select form-select-sm">
          <option value="all">All</option>
          <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending / Open</option>
          <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
          <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Done / Completed</option>
        </select>
      </div>
      <div class="col-md-3 d-grid">
        <button type="submit" class="btn btn-sm btn-primary">Apply</button>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span>Tasks List</span>
    <button class="btn btn-sm btn-outline-secondary">Email Reminder Preview</button>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th>Task</th><th>Due Date</th><th>Frequency</th><th>Owner</th>
            <th>Status</th><th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($paginated as $task)
            <tr data-company="{{ $task->company->name ?? 'N/A' }}">
              <td>{{ $task->return_type }}<br><small class="text-muted">{{ $task->notes }}</small></td>
              <td>{{ $task->due_date ? $task->due_date->format('d-m-Y') : 'N/A' }}</td>
              <td>{{ $task->tax_period }}</td>
              <td>{{ $task->assigned_to ?? 'CA' }}</td>
              <td>
                <form action="{{ route('ca.tasks.update', $task->id) }}" method="POST" class="d-flex gap-2">
                  @csrf
                  <select name="status" class="form-select form-select-sm" style="max-width:160px">
                    <option value="pending" {{ $task->status == 'pending' ? 'selected' : '' }}>Open</option>
                    <option value="in_progress" {{ $task->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ $task->status == 'completed' ? 'selected' : '' }}>Done</option>
                  </select>
                  <button type="submit" class="btn btn-sm btn-primary">Save</button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center text-muted py-3">No tasks found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-3 px-3">
      {{ $paginated->links('pagination::bootstrap-5') }}
    </div>
  </div>
  <div class="card-footer small-help">Only task status updates are allowed for CA role. Everything else read-only.</div>
</div>

  </div>
@endsection
