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
