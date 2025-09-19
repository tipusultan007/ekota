@extends('layout.master')
@push('plugin-styles')
    <link rel="stylesheet" href="{{ asset('build/plugins/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}">
@endpush
@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.account-transfers.index') }}">{{ __('messages.balance_transfer') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('messages.edit_balance_transfer') }}</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ __('messages.edit_balance_transfer') }}</h5>
            <form action="{{ route('admin.account-transfers.update', $account_transfer->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-5 mb-3">
                        <label class="form-label">{{ __('messages.transfer_from') }} <span class="text-danger">*</span></label>
                        <select name="from_account_id" id="from_account_id" class="form-select" required>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ $account_transfer->from_account_id == $account->id ? 'selected' : '' }}>{{ $account->name }} ({{ __('messages.balance') }}: {{ number_format($account->balance) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5 mb-3">
                        <label class="form-label">{{ __('messages.transfer_to') }} <span class="text-danger">*</span></label>
                        <select name="to_account_id" id="to_account_id" class="form-select" required>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ $account_transfer->to_account_id == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">{{ __('messages.amount') }} <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount', $account_transfer->amount) }}" required>
                    </div>
                    <div class="col-md-5 mb-3">
                        <label class="form-label">{{ __('messages.transfer_date') }} <span class="text-danger">*</span></label>
                        <input type="text" name="transfer_date" class="form-control flatpickr" value="{{ old('transfer_date', $account_transfer->transfer_date->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-7 mb-3">
                        <label class="form-label">{{ __('messages.notes') }}</label>
                        <input type="text" name="notes" class="form-control" value="{{ old('notes', $account_transfer->notes) }}">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">{{ __('messages.update_transfer') }}</button>
                <a href="{{ route('admin.account-transfers.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
            </form>
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
