@extends('layout.master')
@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ __('messages.all_savings_withdrawals') }}</h5>

            <div class="table-responsive mt-3">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>{{ __('messages.date') }}</th>
                        <th>{{ __('messages.member') }}</th>
                        <th>{{ __('messages.account_no') }}</th>
                        <th>{{ __('messages.principal') }}</th>
                        <th>{{ __('messages.profit') }}</th>
                        <th>{{ __('messages.total_paid') }}</th>
                        <th>{{ __('messages.processed_by') }}</th>
                        @role('Admin')<th>{{ __('messages.actions') }}</th>@endrole
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
                                {{-- ======== পরিবর্তন এখানে ======== --}}
                                {{-- ফর্মকে একটি ইউনিক আইডি দিন --}}
                                <form id="delete-withdrawal-{{ $withdrawal->id }}"
                                      action="{{ route('admin.savings_withdrawals.destroy', $withdrawal->id) }}"
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')

                                    {{-- onsubmit সরিয়ে onclick ব্যবহার করুন এবং অনুবাদ করা টেক্সট পাস করুন --}}
                                    <button type="button" class="btn btn-danger btn-xs"
                                            onclick="showDeleteConfirm('delete-withdrawal-{{ $withdrawal->id }}')">
                                        {{ __('messages.delete') }}
                                    </button>
                                </form>
                                {{-- ============================== --}}
                            </td>
                            @endrole
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">{{ __('messages.no_withdrawals_found') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $withdrawals->links() }}</div>
        </div>
    </div>
@endsection
