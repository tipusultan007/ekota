@extends('layout.master')
@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ __('messages.edit_account') }}: {{ $account->name }}</h5>
            <form action="{{ route('admin.accounts.update', $account->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="name" class="form-label">{{ __('messages.account_name') }} <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $account->name) }}" required>
                    @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="mb-3">
                    <label for="balance" class="form-label">{{ __('messages.balance') }}</label>
                    <input type="text" class="form-control" value="{{ number_format($account->balance, 2) }}" readonly disabled>
                    <small class="form-text text-muted">Balance can only be changed via transactions.</small>
                </div>
                <div class="mb-3">
                    <label for="details" class="form-label">{{ __('messages.details_acc_no') }}</label>
                    <textarea name="details" class="form-control">{{ old('details', $account->details) }}</textarea>
                </div>
                <div class="mb-3">
                    <label for="is_active" class="form-label">{{ __('messages.status') }}</label>
                    <select name="is_active" class="form-select">
                        <option value="1" {{ $account->is_active ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                        <option value="0" {{ !$account->is_active ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">{{ __('messages.update_account') }}</button>
            </form>
        </div>
    </div>
@endsection
