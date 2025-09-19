@extends('layout.master')
@push('plugin-styles')
    <link rel="stylesheet" href="{{ asset('build/plugins/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}">
@endpush
@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.accounts.index') }}">{{ __('messages.all_accounts') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('messages.balance_transfer') }}</li>
        </ol>
    </nav>

    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

    {{-- Create Transfer Form --}}
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">{{ __('messages.balance_transfer') }}</h5>
            <form action="{{ route('admin.account-transfers.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-5 mb-3">
                        <label class="form-label">{{ __('messages.transfer_from') }} <span class="text-danger">*</span></label>
                        <select name="from_account_id" id="from_account_id" class="form-select" required>
                            <option value="">-- {{ __('messages.select_source_account') }} --</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }} ({{ __('messages.balance') }}: {{ number_format($account->balance) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5 mb-3">
                        <label class="form-label">{{ __('messages.transfer_to') }} <span class="text-danger">*</span></label>
                        <select name="to_account_id" id="to_account_id" class="form-select" required>
                            <option value="">-- {{ __('messages.select_destination_account') }} --</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">{{ __('messages.amount') }} <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control" required>
                    </div>
                    <div class="col-md-5 mb-3">
                        <label class="form-label">{{ __('messages.transfer_date') }} <span class="text-danger">*</span></label>
                        <input type="text" name="transfer_date" class="form-control flatpickr" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-7 mb-3">
                        <label class="form-label">{{ __('messages.notes') }}</label>
                        <input type="text" name="notes" class="form-control">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">{{ __('messages.submit_transfer') }}</button>
            </form>
        </div>
    </div>

    {{-- Recent Transfers List --}}
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ __('messages.recent_transfers') }}</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead><tr><th>{{ __('messages.date') }}</th><th>{{ __('messages.from_account') }}</th><th>{{ __('messages.to_account') }}</th><th class="text-end">{{ __('messages.amount') }}</th><th>{{ __('messages.actions') }}</th></tr></thead>
                    <tbody>
                    @forelse($transfers as $transfer)
                        <tr>
                            <td>{{ $transfer->transfer_date->format('d M, Y') }}</td>
                            <td>{{ $transfer->fromAccount->name }}</td>
                            <td>{{ $transfer->toAccount->name }}</td>
                            <td class="text-end">{{ number_format($transfer->amount, 2) }}</td>
                            <td>
                                <div class="d-flex">
                                    <a href="{{ route('admin.account-transfers.edit', $transfer) }}" class="btn btn-primary btn-xs me-1">{{ __('messages.edit') }}</a>
                                    <form id="delete-transfer-{{ $transfer->id }}" action="{{ route('admin.account-transfers.destroy', $transfer->id) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-danger btn-xs" onclick="showDeleteConfirm('delete-transfer-{{ $transfer->id }}', '{{ __('messages.are_you_sure') }}', '{{ __('messages.confirm_delete_transfer') }}')">{{ __('messages.delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">{{ __('messages.no_transfers_found') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $transfers->links() }}</div>
        </div>
    </div>
@endsection
@push('plugin-scripts')
    <script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('build/plugins/select2/select2.min.js') }}"></script>
    <script src="{{ asset('build/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#from_account_id').select2({ placeholder: "{{ __('messages.select_source_account') }}", width: '100%' });
            $('#to_account_id').select2({ placeholder: "{{ __('messages.select_destination_account') }}", width: '100%' });
            $(".flatpickr").flatpickr({ altInput: true, dateFormat: 'Y-m-d', altFormat: 'd/m/Y' });
        });
    </script>
@endpush
