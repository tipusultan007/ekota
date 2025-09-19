<tr>
    <td>{{ \Carbon\Carbon::parse($item->payment_date)->format('d/m/Y') }}</td>
    <td>{{ $item->member->name }}</td>
    <td class="text-end">{{ number_format($item->paid_amount, 2) }}</td>
    @role('Admin')
        <td class="text-center">
            <div class="d-inline-flex">
                <a href="{{ route('loan-installments.edit', $item->id) }}" class="btn btn-primary btn-xs me-1" title="Edit">
                    <i data-lucide="edit" class="icon-xs"></i>
                </a>
                <form id="delete-loan-{{ $item->id }}" action="{{ route('loan-installments.destroy', $item->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-danger btn-xs" title="Delete" onclick="showDeleteConfirm('delete-loan-{{ $item->id }}')">
                        <i data-lucide="trash-2" class="icon-xs"></i>
                    </button>
                </form>
            </div>
        </td>
    @endrole
</tr>
