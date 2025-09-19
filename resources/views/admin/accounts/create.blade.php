@extends('layout.master')
@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ __('messages.add_new_account') }}</h5>
            <form action="{{ route('admin.accounts.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">{{ __('messages.account_name') }} <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <input type="hidden" name="initial_balance" value="0">
                <div class="mb-3">
                    <label for="details" class="form-label">{{ __('messages.details_acc_no') }}</label>
                    <textarea name="details" class="form-control">{{ old('details') }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary">{{ __('messages.create_account') }}</button>
            </form>
        </div>
    </div>
@endsection
