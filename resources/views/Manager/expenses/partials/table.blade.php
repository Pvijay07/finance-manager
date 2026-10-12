@foreach ($allExpenses as $expense)
    <tr data-status="{{ $expense->status }}" data-type="{{ $expense->source }}">
        <td>{{ $expense->expense_name }}</td>
        <td>{{ $expense->company->name ?? 'All Companies' }}</td>
        <td>{{ $expense->categoryRelation->name ?? 'N/A' }}</td>
        <td>₹{{ number_format($expense->planned_amount, 2) }}</td>
        <td>₹{{ number_format($expense->actual_amount, 2) }}</td>
        <td>{{ ucfirst($expense->frequency) }}</td>
        <td>{{ $expense->due_day }}</td>
        <td>
            <span class="badge {{ $expense->source == 'standard' ? 'bg-primary' : 'bg-secondary' }}">
                {{ $expense->source == 'standard' ? 'Standard' : 'Non Standard' }}
            </span>
        </td>
        <td>
            @php
                $statusClass =
                    [
                        'paid' => 'success',
                        'pending' => 'warning',
                        'upcoming' => 'info',
                        'overdue' => 'danger',
                    ][$expense->status] ?? 'secondary';
            @endphp
            <span class="badge bg-{{ $statusClass }}">
                {{ ucfirst($expense->status) }}
            </span>
        </td>
        <td>
            @if($expense->creator)
                <div class="fw-semibold text-dark">{{ $expense->creator->name }}</div>
                <span class="badge bg-light text-secondary border text-capitalize py-0 px-1" style="font-size: 10px;">
                    {{ str_replace('_', ' ', $expense->creator->role ?? 'User') }}
                </span>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td>
            @php
                $paidUser = $expense->paidByUser;
                $paidName = $paidUser ? $paidUser->name : $expense->paid_by_name;
                $paidRole = $paidUser ? $paidUser->role : null;
            @endphp
            @if($paidName && in_array(strtolower($expense->status), ['paid', 'settle', 'settled']))
                <div class="fw-semibold text-dark">{{ $paidName }}</div>
                @if($paidRole)
                <span class="badge bg-light text-secondary border text-capitalize py-0 px-1" style="font-size: 10px;">
                    {{ str_replace('_', ' ', $paidRole) }}
                </span>
                @endif
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td>{{ $expense->created_at->format('d M Y') }}</td>
        <td>
            @if (count($expense->receipts ?? []) > 0)
                <button class="btn btn-sm btn-outline-info" onclick="viewReceipts({{ $expense->id }})">
                    <i class="fas fa-receipt"></i> Receipts
                </button>
            @else
                <button class="btn btn-sm btn-outline-primary" onclick="editExpense({{ $expense->id }})">
                    <i class="fas fa-edit"></i>
                </button>
            @endif
        </td>
    </tr>
@endforeach
