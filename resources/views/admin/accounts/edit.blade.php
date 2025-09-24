@extends('layout.master')

@section('content')
<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.accounts.index') }}">{{ __('messages.chart_of_accounts') }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ __('messages.edit_account') }}</li>
    </ol>
</nav>

<div class="row">
    <div class="col-md-8 mx-auto grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ __('messages.edit_account') }}: {{ $account->name }}</h5>
                <form action="{{ route('admin.accounts.update', $account->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="code" class="form-label">{{ __('messages.account_code') }}</label>
                            {{-- কোড সাধারণত অপরিবর্তনীয় থাকে --}}
                            <input type="text" id="code" class="form-control" value="{{ $account->code }}" readonly disabled>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label for="name" class="form-label">{{ __('messages.account_name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $account->name) }}" required>
                            @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="type" class="form-label">{{ __('messages.account_type') }}</label>
                         {{-- অ্যাকাউন্টের প্রকারও সাধারণত অপরিবর্তনীয় থাকে --}}
                        <input type="text" id="type" class="form-control" value="{{ $account->type }}" readonly disabled>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.balance') }} (Calculated)</label>
                        <input type="text" class="form-control" value="{{ number_format($account->calculatedBalance, 2) }}" readonly disabled>
                        <small class="form-text text-muted">Balance can only be changed via transactions.</small>
                    </div>

                    <div class="mb-3">
                        <label for="details" class="form-label">{{ __('messages.details_acc_no') }}</label>
                        <textarea name="details" id="details" class="form-control">{{ old('details', $account->details) }}</textarea>
                    </div>

                    <div class="form-check mb-3">
                         <input type="hidden" name="is_payment_account" value="0">
                        <input type="checkbox" class="form-check-input" name="is_payment_account" id="is_payment_account" value="1" {{ old('is_payment_account', $account->is_payment_account) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_payment_account">
                            Is this a payment account? (e.g., Cash, Bank)
                        </label>
                    </div>

                    <div class="mb-3">
                        <label for="is_active" class="form-label">{{ __('messages.status') }}</label>
                        <select name="is_active" id="is_active" class="form-select">
                            <option value="1" {{ old('is_active', $account->is_active) ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                            <option value="0" {{ !old('is_active', $account->is_active) ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">{{ __('messages.update_account') }}</button>
                    <a href="{{ route('admin.accounts.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection