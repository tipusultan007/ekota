<div class="table-responsive">
    <table class="table table-hover table-bordered">
        <thead>
        @if($type === 'savings' || $type === 'loan')
            <tr><th>Time</th><th>Member</th><th>Account No</th><th class="text-end">Amount</th><th>User</th></tr>
        @elseif($type === 'withdrawal')
            <tr><th>Time</th><th>Member</th><th>Account No</th><th class="text-end">Amount Paid</th><th>User</th></tr>
        @elseif($type === 'expense')
            <tr><th>Time</th><th>Category</th><th>Description</th><th class="text-end">Amount</th><th>User</th></tr>
        @endif
        </thead>
        <tbody>
        @forelse ($items as $item)
            <tr>
                @if($type === 'savings')
                    <td>{{ $item->collection_date->format('d/m/Y').' '.$item->created_at->format('h:i A') }}</td>
                    <td>{{ $item->member->name }}</td>
                    <td>{{ $item->savingsAccount->account_no }}</td>
                    <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                    <td>{{ $item->collector->name }}</td>
                @elseif($type === 'loan')
                    <td>{{ $item->payment_date->format('d/m/Y').' '.$item->created_at->format('h:i A') }}</td>
                    <td>{{ $item->member->name }}</td>
                    <td>{{ $item->loanAccount->account_no }}</td>
                    <td class="text-end">{{ number_format($item->paid_amount, 2) }}</td>
                    <td>{{ $item->collector->name }}</td>
                @elseif($type === 'withdrawal')
                    <td>{{ $item->created_at->format('h:i A') }}</td>
                    <td>{{ $item->member->name }}</td>
                    <td>{{ $item->savingsAccount->account_no }}</td>
                    <td class="text-end">{{ number_format($item->total_amount, 2) }}</td>
                    <td>{{ $item->processedBy->name }}</td>
                @elseif($type === 'expense')
                    <td>{{ $item->created_at->format('h:i A') }}</td>
                    <td>{{ $item->category->name }}</td>
                    <td>{{ Str::limit($item->description, 30) }}</td>
                    <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                    <td>{{ $item->user->name }}</td>
                @endif
            </tr>
        @empty
            <tr><td colspan="5" class="text-center">No transactions found for this category on the selected date.</td></tr>
        @endforelse
        </tbody>
        @if($items->count() > 0)
            <tfoot class="table-light fw-bold">
            <tr>
                <td colspan="{{ ($type === 'expense') ? 3 : 3 }}" class="text-end">Total:</td>
                <td class="text-end">
                    @if($type === 'savings')
                        {{ number_format($items->sum('amount'), 2) }}
                    @elseif($type === 'loan')
                        {{ number_format($items->sum('paid_amount'), 2) }}
                    @elseif($type === 'withdrawal')
                        {{ number_format($items->sum('total_amount'), 2) }}
                    @elseif($type === 'expense')
                        {{ number_format($items->sum('amount'), 2) }}
                    @endif
                </td>
                <td></td>
            </tr>
            </tfoot>
        @endif
    </table>
</div>

@if ($items->hasPages())
    <div class="mt-4">
        {{-- appends() ফাংশনটি পেজিনেশন লিঙ্কগুলোর সাথে বর্তমান ফিল্টার (যেমন তারিখ, কালেক্টর) যোগ করে দেবে --}}
        {{ $items->appends(request()->query())->links() }}
    </div>
@endif
