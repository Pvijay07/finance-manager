@extends('Admin.layouts.app')
@section('content')
    <!-- Standard Expenses Page -->
    <div id="standard-expenses" class="page">
        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="filter-group">
                <div class="filter-label">Company</div>
                <select id="companyFilter" onchange="filterExpenses()">
                    <option value="">All Companies</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <div class="filter-label">Month & Year</div>
                <select id="monthFilter" onchange="filterExpenses()">
                    <option value="">All Months</option>
                    @php
                        $months = [];
                        for ($i = 0; $i < 12; $i++) {
                            $date = date('Y-m', strtotime("-$i months"));
                            $months[] = $date;
                        }
                    @endphp
                    @foreach ($months as $month)
                        <option value="{{ $month }}" {{ $month == date('Y-m') ? 'selected' : '' }}>
                            {{ date('F Y', strtotime($month)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <div class="filter-label">Expense Type</div>
                <select id="typeFilter" onchange="filterExpenses()">
                    <option value="">All Types</option>
                    @foreach ($expenseTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <div class="filter-label">Status</div>
                <select id="statusFilter" onchange="filterExpenses()">
                    <option value="">All Status</option>
                    <option value="upcoming">Upcoming</option>
                    <option value="pending">Pending</option>
                    <option value="paid">Paid</option>
                    <option value="overdue">Overdue</option>
                </select>
            </div>
        </div>

        <!-- Table Container -->
        <div class="table-container">
            <div class="table-header">
                <div class="table-title">Standard Expenses (Auto-Generated)</div>
                <div class="table-actions">
                    <button class="btn btn-outline" onclick="resetFilters()">
                        <i class="fas fa-filter"></i> Clear Filters
                    </button>
                    <button class="btn btn-warning" onclick="regenerateExpenses()">
                        <i class="fas fa-sync"></i> Regenerate
                    </button>
                </div>
            </div>

            <!-- Expenses Table -->
            <table id="expensesTable">
                <thead>
                    <tr>
                        <th>Expense Name</th>
                        <th>Company</th>
                        <th>Month</th>
                        <th>Due Date</th>
                        <th>Planned Amount</th>
                        <th>Actual Amount</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="expensesTableBody">
                    @forelse($expenses as $expense)
                        <tr data-company="{{ $expense->company_id }}" data-month="{{ $expense->month_year }}"
                            data-type="{{ $expense->expense_type_id }}" data-status="{{ $expense->status }}">
                            <td>{{ $expense->name }}</td>
                            <td>{{ $expense->company->name ?? 'N/A' }}</td>
                            <td>{{ date('F Y', strtotime($expense->month_year . '-01')) }}</td>
                            <td>{{ \Carbon\Carbon::parse($expense->due_date)->format('d M Y') }}</td>
                            <td>₹{{ number_format($expense->planned_amount, 2) }}</td>
                            <td>
                                @if ($expense->actual_amount)
                                    ₹{{ number_format($expense->actual_amount, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusConfig = [
                                        'paid' => ['class' => 'active', 'label' => 'Paid'],
                                        'pending' => ['class' => 'pending', 'label' => 'Pending'],
                                        'upcoming' => ['class' => 'active', 'label' => 'Upcoming'],
                                        'overdue' => ['class' => 'pending', 'label' => 'Overdue'],
                                    ];
                                    $config = $statusConfig[$expense->status] ?? [
                                        'class' => 'active',
                                        'label' => $expense->status,
                                    ];
                                @endphp
                                <span class="status {{ $config['class'] }}">{{ $config['label'] }}</span>
                            </td>
                            <td>
                                @php
                                    $now = \Carbon\Carbon::now();
                                    $updated = \Carbon\Carbon::parse($expense->updated_at);

                                    if ($updated->isToday()) {
                                        echo 'Today, ' . $updated->format('h:i A');
                                    } elseif ($updated->isYesterday()) {
                                        echo 'Yesterday, ' . $updated->format('h:i A');
                                    } else {
                                        echo $updated->format('d M Y, h:i A');
                                    }
                                @endphp
                            </td>
                            <td>
                                <button class="btn btn-outline view-expense" style="padding: 5px 10px; font-size: 0.8rem;"
                                    data-id="{{ $expense->id }}">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 40px;">
                                <div style="margin-bottom: 20px;">
                                    <i class="fas fa-file-invoice-dollar fa-4x text-muted mb-3" style="opacity: 0.3;"></i>
                                    <h4 style="color: #6c757d;">No Standard Expenses Found</h4>
                                    <p class="text-muted">Click the button below to generate expenses for
                                        {{ date('F Y') }}</p>
                                </div>

                                <!-- SIMPLE WORKING BUTTON -->
                                <form id="generateForm" action="{{ route('admin.standard-expenses.generate') }}" method="POST">
                                    @csrf
                                    <button type="submit" id="generateButton" class="btn btn-primary btn-lg">
                                        <i class="fas fa-play mr-2"></i> Generate Standard Expenses
                                    </button>
                                    <div class="mt-2 text-muted small">
                                        <i class="fas fa-info-circle"></i> This will create expenses based on your expense
                                        types
                                    </div>
                                </form>

                                <!-- Loading indicator (hidden by default) -->
                                <div id="loadingIndicator" style="display: none; margin-top: 20px;">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Generating...</span>
                                    </div>
                                    <p class="mt-2">Generating expenses, please wait...</p>
                                </div>
                            </td>
                        </tr>
                    @endempty
            </tbody>
        </table>
    </div>

    <!-- Generation Log Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Standard Expense Generation Log</div>
        </div>
        <div style="padding: 20px;">
            @if ($latestLog)
                <div class="form-group">
                    <label class="form-label">Last Generation</label>
                    <div style="background-color: #f8f9fa; padding: 15px; border-radius: 4px;">
                        <div><strong>Date:</strong> {{ $latestLog->run_date->format('d F Y, H:i A') }}</div>
                        <div><strong>Status:</strong>
                            <span class="status {{ $latestLog->status == 'success' ? 'active' : 'pending' }}">
                                {{ ucfirst($latestLog->status) }}
                            </span>
                        </div>
                        <div><strong>Generated:</strong> {{ $latestLog->total_generated }} standard expenses across
                            {{ count($companies) }} companies</div>
                        <div><strong>Next Generation:</strong>
                            {{ now()->addMonth()->startOfMonth()->addMinutes(5)->format('d F Y, H:i A') }}
                        </div>
                    </div>
                </div>
            @else
                <div class="form-group">
                    <label class="form-label">Last Generation</label>
                    <div style="background-color: #f8f9fa; padding: 15px; border-radius: 4px;">
                        No generation log found. Run the first generation.
                    </div>
                </div>
            @endif

            <div class="form-group">
                <form action="{{ route('admin.standard-expenses.generate') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-primary" id="generateBtn">
                        <i class="fas fa-play"></i> Run Generation Now
                    </button>
                </form>
                <a href="{{ route('admin.standard-expenses.logs') }}" class="btn btn-outline">
                    <i class="fas fa-history"></i> View Full Log
                </a>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Filtering -->
<script>
    // Add this function to your existing JavaScript
    function generateExpenses() {
        if (confirm('Generate standard expenses for the current month?')) {
            // Submit the generation form
            document.getElementById('generateBtn').click();
        }
    }

    function filterExpenses() {
        const companyId = document.getElementById('companyFilter').value;
        const month = document.getElementById('monthFilter').value;
        const typeId = document.getElementById('typeFilter').value;
        const status = document.getElementById('statusFilter').value;

        const rows = document.querySelectorAll('#expensesTableBody tr');

        rows.forEach(row => {
            let show = true;

            if (companyId && row.dataset.company !== companyId) {
                show = false;
            }

            if (month && row.dataset.month !== month) {
                show = false;
            }

            if (typeId && row.dataset.type !== typeId) {
                show = false;
            }

            if (status && row.dataset.status !== status) {
                show = false;
            }

            row.style.display = show ? '' : 'none';
        });
    }

    function resetFilters() {
        document.getElementById('companyFilter').value = '';
        document.getElementById('monthFilter').value = '';
        document.getElementById('typeFilter').value = '';
        document.getElementById('statusFilter').value = '';
        filterExpenses();
    }

    function regenerateExpenses() {
        if (confirm(
                'Are you sure you want to regenerate standard expenses? This will create new expenses for the current month.'
            )) {
            document.getElementById('generateBtn').click();
        }
    }

    // View expense details
    document.querySelectorAll('.view-expense').forEach(button => {
        button.addEventListener('click', function() {
            const expenseId = this.dataset.id;
            window.location.href = `/expenses/${expenseId}`;
        });
    });

    // Auto-submit generation form with loading state
    // Show loading state on generate button
    function showLoading(button) {
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
        button.disabled = true;

        // Re-enable button after 10 seconds just in case
        setTimeout(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        }, 10000);
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle generate button click
        const generateButton = document.getElementById('generateButton');
        const generateForm = document.getElementById('generateForm');
        const loadingIndicator = document.getElementById('loadingIndicator');

        if (generateButton && generateForm) {
            generateForm.addEventListener('submit', function(e) {
                e.preventDefault(); // Prevent default form submission

                // Show loading
                generateButton.disabled = true;
                generateButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Generating...';

                if (loadingIndicator) {
                    loadingIndicator.style.display = 'block';
                }

                // Create FormData object
                const formData = new FormData(this);

                // Send AJAX request
                fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Response data:', data); // Debug log

                        if (data.success) {
                            // Show success message
                            alert('✅ Success! Generated ' + data.count + ' expenses.');
                            // Reload page after 1 second
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            throw new Error(data.message || 'Unknown error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);

                        // Reset button
                        generateButton.disabled = false;
                        generateButton.innerHTML =
                            '<i class="fas fa-play mr-2"></i> Generate Standard Expenses';

                        if (loadingIndicator) {
                            loadingIndicator.style.display = 'none';
                        }

                        // Show error
                        alert('❌ Error: ' + error.message + '\n\nCheck console for details.');
                    });
            });
        }

        // Also handle the main "Run Generation Now" button
        const mainGenerateBtn = document.getElementById('generateBtn');
        if (mainGenerateBtn) {
            mainGenerateBtn.addEventListener('click', function(e) {
                // Trigger the form submission
                if (generateForm) {
                    generateForm.dispatchEvent(new Event('submit'));
                    e.preventDefault();
                }
            });
        }
    });
</script>
@endsection
