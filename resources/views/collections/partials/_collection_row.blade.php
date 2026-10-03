<tr>
    <td class="text-nowrap">{{ \Carbon\Carbon::parse($item->date)->format('d/m/Y') }}</td>
    <td class="text-nowrap">
        <div class="fw-bold">{{ $item->member->name }}</div>
        <div class="small text-muted">{{ $item->account_no }}</div>
    </td>
    <td class="text-end fw-medium text-nowrap">{{ number_format($item->deposit, 2) }}</td>
    <td class="text-end fw-medium text-nowrap">{{ number_format($item->loan_installment, 2) }}</td>
    <td class="text-end text-danger bg-light-soft text-nowrap">{{ number_format($item->loan_balance, 2) }}</td>
    <td class="text-end text-muted text-nowrap">{{ number_format($item->grace_amount, 2) }}</td>
    <td class="text-center text-nowrap">
        <span class="badge bg-light text-dark border">{{ $item->user->name ?? 'N/A' }}</span>
    </td>
    @if(Auth::user()->hasRole('Admin'))
        <td class="text-center text-nowrap">
            <div class="d-flex justify-content-center gap-2">
                <a href="{{ route('collections.edit', $item->id) }}" class="btn btn-sm btn-light border text-primary" title="Edit">
                    <i data-lucide="edit-2" class="icon-xs"></i>
                </a>
                <form id="delete-collection-row-{{ $item->id }}" action="{{ route('collections.destroy', $item->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-sm btn-light border text-danger" title="Delete" onclick="showDeleteConfirm('delete-collection-row-{{ $item->id }}')">
                        <i data-lucide="trash-2" class="icon-xs"></i>
                    </button>
                    <input type="hidden" name="redirect_to" value="{{ route('collections.create') }}">
                </form>
            </div>
        </td>
    @endif
</tr>
