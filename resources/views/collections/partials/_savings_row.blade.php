<tr>
    <td class="text-nowrap">{{ \Carbon\Carbon::parse($item->date)->format('d/m/Y') }}</td>
    <td class="text-nowrap">
        <div class="fw-bold text-primary">{{ $item->member->name }}</div>
        <div class="small text-muted">{{ $item->savingsAccount->account_no ?? 'N/A' }}</div>
    </td>
    <td class="text-end fw-bold text-success text-nowrap">
        {{ number_format($item->amount, 2) }}
    </td>
    <td class="text-center text-nowrap">
        <span class="badge bg-soft-info text-info border-0 px-3">{{ $item->collector->name ?? 'N/A' }}</span>
    </td>
    @if(Auth::user()->hasRole('Admin'))
        <td class="text-center text-nowrap">
            <div class="d-flex justify-content-center gap-2">
                 <a href="{{ route('savings-collections.edit', $item->id) }}" class="btn-action btn-action-edit" title="Edit">
                    <i data-lucide="edit-3" class="icon-xs"></i>
                </a>
                 <form id="delete-savings-{{ $item->id }}" action="{{ route('savings-collections.destroy', $item->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn-action btn-action-delete" title="Delete" onclick="showDeleteConfirm('delete-savings-{{ $item->id }}')">
                        <i data-lucide="trash-2" class="icon-xs"></i>
                    </button>
                </form>
            </div>
        </td>
    @endif
</tr>
