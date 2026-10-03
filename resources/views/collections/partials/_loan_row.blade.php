<tr>
    <td class="text-nowrap">{{ \Carbon\Carbon::parse($item->date)->format('d/m/Y') }}</td>
    <td class="text-nowrap">
        <div class="fw-bold text-danger">{{ $item->member->name }}</div>
        <div class="small text-muted">{{ $item->loanAccount->account_no ?? 'N/A' }}</div>
    </td>
    <td class="text-end fw-bold text-danger text-nowrap">
        {{ number_format($item->paid_amount, 2) }}
    </td>
    <td class="text-end text-danger bg-light-soft text-nowrap fw-medium">
         {{ number_format((optional($item->loanAccount)->total_payable - optional($item->loanAccount)->total_paid), 2) }}
    </td>
    <td class="text-end text-muted text-nowrap">
        {{ number_format($item->grace_amount, 2) }}
    </td>
    <td class="text-center text-nowrap">
        <span class="badge bg-soft-info text-info border-0 px-3">{{ $item->collector->name ?? 'N/A' }}</span>
    </td>
    @if(Auth::user()->hasRole('Admin'))
        <td class="text-center text-nowrap">
            <div class="d-flex justify-content-center gap-2">
                 <a href="{{ route('loan-installments.edit', $item->id) }}" class="btn-action btn-action-edit" title="Edit">
                    <i data-lucide="edit-3" class="icon-xs"></i>
                </a>
                 <form id="delete-loan-{{ $item->id }}" action="{{ route('loan-installments.destroy', $item->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn-action btn-action-delete" title="Delete" onclick="showDeleteConfirm('delete-loan-{{ $item->id }}')">
                        <i data-lucide="trash-2" class="icon-xs"></i>
                    </button>
                </form>
            </div>
        </td>
    @endif
</tr>
