@extends('layout.master')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('loan-installments.index') }}">{{ __('messages.installment_history') }}</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('messages.edit_installment') }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8 mx-auto grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">{{ __('messages.edit_installment') }}</h6>

                    {{-- Transaction Summary --}}
                    <div class="alert alert-secondary">
                        <p class="mb-1"><strong>{{ __('messages.member_summary') }}:</strong> {{ $loanInstallment->member->name }}</p>
                        <p class="mb-1"><strong>{{ __('messages.loan_details') }}:</strong> {{ $loanInstallment->loanAccount->account_no }}</p>
                        <p class="mb-0"><strong>{{ __('messages.original_paid_amount') }}:</strong> {{ number_format($loanInstallment->paid_amount, 2) }} BDT</p>
                    </div>

                    <hr>

                    <form action="{{ route('loan-installments.update', $loanInstallment->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="paid_amount" class="form-label">{{ __('messages.amount') }} <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="paid_amount" id="paid_amount"
                                       class="form-control @error('paid_amount') is-invalid @enderror"
                                       value="{{ old('paid_amount', $loanInstallment->paid_amount) }}" required>
                                @error('paid_amount')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3" id="grace_amount_wrapper">
                                <label class="form-label">{{ __('messages.grace_amount') }}</label>
                                <input type="number" step="0.01" name="grace_amount" id="grace_amount_input"
                                       class="form-control" placeholder="0.00">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="payment_date" class="form-label">{{ __('messages.payment_date') }} <span class="text-danger">*</span></label>
                                <input type="text" name="payment_date" id="payment_date"
                                       class="form-control flatpickr @error('payment_date') is-invalid @enderror"
                                       value="{{ old('payment_date', $loanInstallment->payment_date->format('Y-m-d')) }}" required>
                                @error('payment_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.deposit_to_account') }} <span class="text-danger">*</span></label>
                            <select name="account_id" class="form-select" required>
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}"
                                        {{ ($loanInstallment->transaction && $loanInstallment->transaction->account_id == $account->id) ? 'selected' : '' }}>
                                        {{ $account->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">{{ __('messages.notes') }}</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes', $loanInstallment->notes) }}</textarea>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">{{ __('messages.update_installment') }}</button>
                            <a href="{{ route('loan-installments.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('custom-scripts')
    <script>
        $(".flatpickr").flatpickr({
            altInput: true,
            dateFormat: 'Y-m-d',
            altFormat: 'd/m/Y'
        })
    </script>
@endpush
