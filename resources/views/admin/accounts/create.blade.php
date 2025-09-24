@extends('layout.master')

@section('content')
<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.accounts.index') }}">{{ __('messages.chart_of_accounts') }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ __('messages.add_new_account') }}</li>
    </ol>
</nav>

<div class="row">
    <div class="col-md-8 mx-auto grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ __('messages.add_new_account') }}</h5>
                <form action="{{ route('admin.accounts.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="code" class="form-label">{{ __('messages.account_code') }} <span class="text-danger">*</span></label>
                            <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" required>
                            @error('code') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-8 mb-3">
                            <label for="name" class="form-label">{{ __('messages.account_name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="type" class="form-label">{{ __('messages.account_type') }} <span class="text-danger">*</span></label>
                        <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                            <option value="">-- Select Type --</option>
                            <option value="Asset" {{ old('type') == 'Asset' ? 'selected' : '' }}>Asset</option>
                            <option value="Liability" {{ old('type') == 'Liability' ? 'selected' : '' }}>Liability</option>
                            <option value="Equity" {{ old('type') == 'Equity' ? 'selected' : '' }}>Equity</option>
                            <option value="Income" {{ old('type') == 'Income' ? 'selected' : '' }}>Income</option>
                            <option value="Expense" {{ old('type') == 'Expense' ? 'selected' : '' }}>Expense</option>
                        </select>
                        @error('type') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="initial_balance" class="form-label">{{ __('messages.initial_balance') }}</label>
                        <input type="number" step="0.01" name="initial_balance" id="initial_balance" class="form-control @error('initial_balance') is-invalid @enderror" value="{{ old('initial_balance', '0.00') }}">
                        <small class="form-text text-muted">Only provide if this is a new account with a starting balance.</small>
                        @error('initial_balance') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="details" class="form-label">{{ __('messages.details_acc_no') }}</label>
                        <textarea name="details" id="details" class="form-control">{{ old('details') }}</textarea>
                    </div>

                    <div class="form-check mb-3">
                        <input type="hidden" name="is_payment_account" value="0"> {{-- ডিফল্ট ভ্যালু 0 পাঠানোর জন্য --}}
                        <input type="checkbox" class="form-check-input" name="is_payment_account" id="is_payment_account" value="1" {{ old('is_payment_account') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_payment_account">
                            Is this a payment account? (e.g., Cash, Bank)
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary">{{ __('messages.create_account') }}</button>
                    <a href="{{ route('admin.accounts.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection