@extends('Manager.layouts.app')
@section('content')
<section class="pge">
  <div class="container-fluid">
    
    <div class="card shadow-sm mb-3">
      <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
          <h5 class="mb-0">GST Returns & Tasks</h5>
          <div class="small-help">Track due dates + reminder schedule. Update task status for CA workflow.</div>
        </div>
        <div class="topnav">
          <a class="btn btn-sm btn-outline-primary" href="{{ route('manager.gst') }}">Dashboard</a>
          <a class="btn btn-sm btn-outline-primary" href="{{ route('manager.gst-collected') }}">GST Collected</a>
          <a class="btn btn-sm btn-outline-primary" href="{{ route('manager.taxes') }}">Taxes on Expenses</a>
          <a class="btn btn-sm btn-outline-primary" href="{{ route('manager.gst-settlements') }}">Settlements</a>
          <a class="btn btn-sm btn-primary" href="{{ route('manager.gst-returns') }}">Returns & tasks</a>
        </div>
      </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row g-3 mb-3">
      <div class="col-md-3">
        <div class="card kpi shadow-sm">
          <div class="card-body">
            <div class="label">Total Tasks</div>
            <div class="value">{{ $stats['total'] }}</div>
            <div class="small-help">All return tasks</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card kpi shadow-sm">
          <div class="card-body">
            <div class="label">Pending</div>
            <div class="value">{{ $stats['pending'] }}</div>
            <div class="small-help">Awaiting action</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card kpi shadow-sm">
          <div class="card-body">
            <div class="label">In Progress</div>
            <div class="value">{{ $stats['in_progress'] }}</div>
            <div class="small-help">Being processed</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card kpi shadow-sm">
          <div class="card-body">
            <div class="label">Overdue</div>
            <div class="value">{{ $stats['overdue'] }}</div>
            <div class="small-help">Past due date</div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Filters -->
    <div class="card shadow-sm mb-3">
      <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
          <div>
            <h6 class="mb-1">Filter Tasks</h6>
            <div class="small-help">Filter by status, company, or return type.</div>
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary" onclick="sendReminderEmails()">
              <i class="fas fa-envelope me-1"></i> Send Reminders
            </button>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
              <i class="fas fa-plus me-1"></i> Add Task
            </button>
          </div>
        </div>
        
        <form method="GET" action="{{ route('manager.gst-returns') }}" class="row g-2 align-items-end">
          <div class="col-md-3">
            <label class="form-label small">Company</label>
            <select class="form-select form-select-sm" name="company_id">
              <option value="all">All Companies</option>
              @foreach ($companies as $company)
                <option value="{{ $company->id }}" {{ $filters['company_id'] == $company->id ? 'selected' : '' }}>
                  {{ $company->name }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label small">Return Type</label>
            <select class="form-select form-select-sm" name="return_type">
              <option value="all">All Types</option>
              @foreach ($returnTypes as $type)
                <option value="{{ $type }}" {{ $filters['return_type'] == $type ? 'selected' : '' }}>
                  {{ $type }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label small">Status</label>
            <select class="form-select form-select-sm" name="status">
              <option value="all" {{ $filters['status'] == 'all' ? 'selected' : '' }}>All Status</option>
              <option value="pending" {{ $filters['status'] == 'pending' ? 'selected' : '' }}>Pending</option>
              <option value="in_progress" {{ $filters['status'] == 'in_progress' ? 'selected' : '' }}>In Progress</option>
              <option value="completed" {{ $filters['status'] == 'completed' ? 'selected' : '' }}>Completed</option>
              <option value="overdue" {{ $filters['status'] == 'overdue' ? 'selected' : '' }}>Overdue</option>
            </select>
          </div>
          <div class="col-md-3 d-grid">
            <button type="submit" class="btn btn-sm btn-primary">Apply Filters</button>
          </div>
        </form>
      </div>
    </div>
    
    <!-- Task List -->
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>Task List ({{ $tasks->count() }} tasks)</span>
        <div class="d-flex gap-2">
          <button class="btn btn-sm btn-outline-secondary" onclick="exportTasks('excel')">
            <i class="fas fa-file-excel me-1"></i> Export
          </button>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm mb-0 align-middle">
            <thead class="table-light">
              <tr>
                <th>Company</th>
                <th>Period</th>
                <th>Return</th>
                <th>Due Date</th>
                <th>Reminder</th>
                <th>Assigned To</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              @if ($tasks->count() > 0)
                @foreach ($tasks as $task)
                  @php
                    $isOverdue = $task->due_date < now() && $task->status != 'completed';
                    $status = $isOverdue ? 'overdue' : $task->status;
                  @endphp
                  <tr class="{{ $isOverdue ? 'table-warning' : '' }}">
                    <td>{{ $task->company->name }}</td>
                    <td>{{ date('M Y', strtotime($task->tax_period . '-01')) }}</td>
                    <td>{{ $task->return_type }}</td>
                    <td>
                      {{ date('d-m-Y', strtotime($task->due_date)) }}
                      @if($isOverdue)
                        <br><small class="text-danger">Overdue by {{ now()->diffInDays($task->due_date) }} days</small>
                      @endif
                    </td>
                    <td>{{ date('d-m-Y', strtotime($task->reminder_date)) }}</td>
                    <td>{{ $task->assigned_to }}</td>
                    <td>
                      <span class="badge {{ $task->getStatusBadgeAttribute() }}">
                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                      </span>
                    </td>
                    <td class="text-end">
                      <div class="btn-group btn-group-sm">
                        @if($task->status != 'completed')
                          <button class="btn btn-outline-info" onclick="updateTaskStatus({{ $task->id }}, 'in_progress')">
                            In-progress
                          </button>
                          <button class="btn btn-outline-success" onclick="updateTaskStatus({{ $task->id }}, 'completed')">
                            Complete
                          </button>
                        @else
                          <button class="btn btn-outline-secondary" onclick="updateTaskStatus({{ $task->id }}, 'pending')">
                            Re-open
                          </button>
                        @endif
                        <button class="btn btn-outline-secondary" onclick="editTask({{ $task->id }})">
                          <i class="fas fa-edit"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                @endforeach
              @else
                <tr>
                  <td colspan="8" class="text-center py-4">
                    <div class="text-muted">
                      <i class="fas fa-tasks display-6"></i>
                      <p class="mt-2">No tasks found.</p>
                      <p class="small">Add your first filing task to get started.</p>
                    </div>
                  </td>
                </tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>
    
    <!-- Add Task Modal -->
    <div class="modal fade" id="addTaskModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <div>
              <h5 class="modal-title">Add Filing Task</h5>
              <div class="small-help">Creates task + reminder schedule.</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form id="taskForm">
              @csrf
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Company *</label>
                  <select class="form-select" name="company_id" required>
                    <option value="">Select Company</option>
                    @foreach ($companies as $company)
                      <option value="{{ $company->id }}">{{ $company->name }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Tax Period *</label>
                  <select class="form-select" name="tax_period" required>
                    <option value="">Select Period</option>
                    @foreach ($months as $month)
                      <option value="{{ $month['value'] }}">{{ $month['label'] }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Return Type *</label>
                  <select class="form-select" name="return_type" required>
                    <option value="">Select Return</option>
                    @foreach ($returnTypes as $type)
                      <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Assigned To *</label>
                  <input type="text" class="form-control" name="assigned_to" value="CA" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Due Date *</label>
                  <input type="date" class="form-control" name="due_date" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Reminder Date *</label>
                  <input type="date" class="form-control" name="reminder_date" required>
                </div>
                <div class="col-12">
                  <label class="form-label">Notes</label>
                  <textarea class="form-control" name="notes" rows="2" placeholder="Optional notes..."></textarea>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button class="btn btn-primary" onclick="saveTask()">Create Task</button>
          </div>
        </div>
      </div>
    </div>
    
  </div>
</section>

<script>
  function saveTask() {
    const form = document.getElementById('taskForm');
    const formData = new FormData(form);
    
    // Validate dates
    const dueDate = new Date(formData.get('due_date'));
    const reminderDate = new Date(formData.get('reminder_date'));
    
    if (reminderDate > dueDate) {
      alert('Reminder date must be on or before due date.');
      return;
    }
    
    // Show loading
    const submitBtn = document.querySelector('#addTaskModal .btn-primary');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
    submitBtn.disabled = true;
    
    fetch('{{ route("manager.gst.task.store") }}', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(Object.fromEntries(formData))
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert('Task created successfully!');
        $('#addTaskModal').modal('hide');
        form.reset();
        location.reload();
      } else {
        alert('Error: ' + data.message);
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('An error occurred while creating the task.');
      submitBtn.innerHTML = originalText;
      submitBtn.disabled = false;
    });
  }
  
  function updateTaskStatus(taskId, status) {
    if (!confirm('Are you sure you want to update the task status?')) {
      return;
    }
    
    fetch('' + taskId, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert('Task status updated successfully!');
        location.reload();
      } else {
        alert('Error: ' + data.message);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('An error occurred while updating the task.');
    });
  }
  
  function sendReminderEmails() {
    if (!confirm('Send reminder emails for upcoming and overdue tasks?')) {
      return;
    }
    
    fetch('{{ route("manager.gst.task.send-reminders") }}', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
      },
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert(data.message || 'Reminder emails sent successfully!');
      } else {
        alert('Error: ' + data.message);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('An error occurred while sending reminders.');
    });
  }
  
  function exportTasks(type) {
    const company = document.querySelector('select[name="company_id"]').value;
    const returnType = document.querySelector('select[name="return_type"]').value;
    const status = document.querySelector('select[name="status"]').value;
    
    window.location.href = '{{ route("manager.gst.task.export") }}?type=' + type + 
      '&company=' + company + '&return_type=' + returnType + '&status=' + status;
  }
  
  function editTask(id) {
    // Implement edit functionality
    alert('Edit task ID: ' + id + ' - To be implemented');
  }
  
  // Initialize modal with default dates
  document.addEventListener('DOMContentLoaded', function() {
    // Set due date to 11th of next month (common GST due date)
    const nextMonth = new Date();
    nextMonth.setMonth(nextMonth.getMonth() + 1);
    nextMonth.setDate(11);
    const dueDate = nextMonth.toISOString().split('T')[0];
    
    // Set reminder date to 5 days before due date
    const reminderDate = new Date(dueDate);
    reminderDate.setDate(reminderDate.getDate() - 5);
    const reminderDateStr = reminderDate.toISOString().split('T')[0];
    
    document.querySelector('input[name="due_date"]').value = dueDate;
    document.querySelector('input[name="reminder_date"]').value = reminderDateStr;
  });
</script>
@endsection