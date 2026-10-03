<tr>
    <td class="text-nowrap">{{ \Carbon\Carbon::parse($item->date)->format('d/m/Y') }}</td>
    <td class="text-nowrap">
        <div class="fw-bold">{{ $item->member->name }}</div>
        <div class="small text-muted">{{ $item->type == 'savings' ? ($item->savingsAccount->account_no ?? 'N/A') : ($item->loanAccount->account_no ?? 'N/A') }}</div>
    </td>
    <td class="text-end fw-medium text-nowrap">
        @if($item->type == 'savings')
            <span class="text-success fw-bold">{{ number_format($item->amount, 2) }}</span>
        @else
            -
        @endif
    </td>
    <td class="text-end fw-medium text-nowrap">
        @if($item->type == 'loan')
            <span class="text-danger fw-bold">{{ number_format($item->paid_amount, 2) }}</span>
        @else
            -
        @endif
    </td>
    <td class="text-end text-danger bg-light-soft text-nowrap">
        {{-- Loan Due logic or Savings Current Balance? --}}
        @if($item->type == 'loan')
             {{ number_format((optional($item->loanAccount)->total_payable - optional($item->loanAccount)->total_paid), 2) }}
        @else
            -
        @endif
    </td>
    <td class="text-end text-muted text-nowrap">
         @if($item->type == 'loan')
            {{ number_format($item->grace_amount, 2) }}
         @else
            -
        @endif
    </td>
    <td class="text-center text-nowrap">
        <span class="badge bg-light text-dark border">{{ $item->collector->name ?? 'N/A' }}</span>
    </td>
    @if(Auth::user()->hasRole('Admin'))
        <td class="text-center text-nowrap">
            <div class="d-flex justify-content-center gap-2">
                @if($item->type == 'savings')
                     {{-- Edit/Delete Savings Collection --}}
                     {{-- Not implemented edit yet separately in modal, but separate controller exists --}}
                     <form id="delete-savings-{{ $item->id }}" action="{{ route('savings-collections.destroy', $item->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-sm btn-light border text-danger" title="Delete" onclick="showDeleteConfirm('delete-savings-{{ $item->id }}')">
                            <i data-lucide="trash-2" class="icon-xs"></i>
                        </button>
                    </form>
                @else
                    {{-- Edit/Delete Loan Installment --}}
                     <form id="delete-loan-{{ $item->id }}" action="{{ route('loan-installments.destroy', $item->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-sm btn-light border text-danger" title="Delete" onclick="showDeleteConfirm('delete-loan-{{ $item->id }}')">
                            <i data-lucide="trash-2" class="icon-xs"></i>
                        </button>
                    </form>
                @endif
            </div>
        </td>
    @endif
</tr>
