@extends('layout.master')
@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('messages.chart_of_accounts') }}</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title">{{ __('messages.all_accounts') }}</h5>
                <a href="{{ route('admin.accounts.create') }}" class="btn btn-primary btn-sm">{{ __('messages.add_new_account') }}</a>
            </div>
            @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
            @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead><tr><th>{{ __('messages.account_name') }}</th><th>{{ __('messages.details_acc_no') }}</th><th class="text-end">{{ __('messages.balance') }}</th><th>{{ __('messages.status') }}</th><th>{{ __('messages.actions') }}</th></tr></thead>
                    <tbody>
                    @forelse ($accounts as $account)
                        <tr>
                            <td><a href="{{ route('admin.accounts.show', $account->id) }}">{{ $account->name }}</a></td>
                            <td>{{ $account->details }}</td>
                            @php
                                // কন্ট্রোলার থেকে আসা pre-calculated মান ব্যবহার করুন
                                $balance = $account->total_credits - $account->total_debits;
                            @endphp
                            <td class="text-end fw-bold">{{ number_format($balance, 2) }}</td>
                            <td><span class="badge bg-{{ $account->is_active ? 'success' : 'danger' }}">{{ $account->is_active ? __('messages.active') : __('messages.inactive') }}</span></td>
                            <td>
                                <div class="d-flex">
                                    <a href="{{ route('admin.accounts.edit', $account->id) }}" class="btn btn-primary btn-xs me-1">{{ __('messages.edit') }}</a>
                                     @if(!$account->is_system_account)
                                    <form id="delete-account-{{ $account->id }}" action="{{ route('admin.accounts.destroy', $account->id) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-danger btn-xs" onclick="showDeleteConfirm('delete-account-{{ $account->id }}', '{{ __('messages.are_you_sure') }}', '{{ __('messages.confirm_delete_account') }}')">{{ __('messages.delete') }}</button>
                                    </form>
                                     @else
                                            <span class="badge bg-light text-dark">System Account</span>
                                        @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">{{ __('messages.no_accounts_found') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $accounts->links() }}</div>
        </div>
    </div>
@endsection
