@extends('layout.master')
@section('content')
    <div class="card">
        <div class="card-body">
            <h6 class="card-title">{{ __('messages.edit_savings_collection') }}</h6>
            <div class="alert alert-secondary">
                <p class="mb-1"><strong>{{ __('messages.member_summary') }}:</strong> {{ $savingsCollection->member->name }}</p>
                <p class="mb-0"><strong>{{ __('messages.account') }}:</strong> {{ $savingsCollection->savingsAccount->account_no }}</p>
            </div>
            <hr>
            <form action="{{ route('savings-collections.update', $savingsCollection->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('messages.amount') }} <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control"
                               value="{{ old('amount', $savingsCollection->amount) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('messages.collection_date') }} <span class="text-danger">*</span></label>
                        <input type="date" name="collection_date" class="form-control"
                               value="{{ old('collection_date', $savingsCollection->collection_date->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">{{ __('messages.deposit_to_account') }} <span class="text-danger">*</span></label>
                        <select name="account_id" class="form-select" required>
                            <option value="">{{ __('messages.select_account') }}</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}" {{ (isset($transaction) && $transaction->account_id == $account->id) ? 'selected' : '' }}>
                                    {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">{{ __('messages.notes') }}</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $savingsCollection->notes) }}</textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">{{ __('messages.update_collection') }}</button>
                <a href="{{ route('savings-collections.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
            </form>
        </div>
    </div>

@endsection
