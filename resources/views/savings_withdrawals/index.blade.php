@extends('layout.master')
@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">All Savings Withdrawals</h5>
            {{-- এখানে তারিখ ফিল্টারের জন্য একটি ফর্ম যোগ করা যেতে পারে --}}
            <div class="table-responsive mt-3">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Member</th>
                        <th>Account No</th>
                        <th>Principal</th>
                        <th>Profit</th>
                        <th>Total Paid</th>
                        <th>Processed By</th>
                        @role('Admin')<th>Actions</th>@endrole
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($withdrawals as $withdrawal)
                        <tr>
                            <td>{{ $withdrawal->withdrawal_date->format('d M, Y') }}</td>
                            <td><a href="{{ route('members.show', $withdrawal->member_id) }}">{{ $withdrawal->member->name }}</a></td>
                            <td>{{ $withdrawal->savingsAccount->account_no }}</td>
                            <td>{{ number_format($withdrawal->withdrawal_amount, 2) }}</td>
                            <td>{{ number_format($withdrawal->profit_amount, 2) }}</td>
                            <td class="fw-bold">{{ number_format($withdrawal->total_amount, 2) }}</td>
                            <td>{{ $withdrawal->processedBy->name }}</td>
                            @role('Admin')
                            <td>
                                <form action="{{ route('admin.savings_withdrawals.destroy', $withdrawal->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure? This will restore the balance.');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-xs">Delete</button>
                                </form>
                            </td>
                            @endrole
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">No withdrawals found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $withdrawals->links() }}</div>
        </div>
    </div>
@endsection
