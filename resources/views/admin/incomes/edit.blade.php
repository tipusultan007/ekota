@extends('layout.master')

@push('plugin-styles')
    <link rel="stylesheet" href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}">
@endpush

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.incomes.index') }}">{{ __('messages.income_management') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('messages.edit_income') }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">{{ __('messages.edit_income') }}</h6>
                    <form action="{{ route('admin.incomes.update', $income->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="income_category_id" class="form-label">{{ __('messages.category') }} <span class="text-danger">*</span></label>
                                <select name="income_category_id" class="form-select @error('income_category_id') is-invalid @enderror" required>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('income_category_id', $income->income_category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('income_category_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="account_id" class="form-label">{{ __('messages.payment_to_account') }} <span class="text-danger">*</span></label>
                                <select name="account_id" class="form-select @error('account_id') is-invalid @enderror" required>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}" {{ old('account_id', $income->account_id) == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                                    @endforeach
                                </select>
                                @error('account_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="amount" class="form-label">{{ __('messages.amount') }} <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $income->amount) }}" required>
                                @error('amount') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="income_date" class="form-label">{{ __('messages.date') }} <span class="text-danger">*</span></label>
                                <input type="text" name="income_date" class="form-control flatpickr @error('income_date') is-invalid @enderror" value="{{ old('income_date', $income->income_date->format('Y-m-d')) }}" required>
                                @error('income_date') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="receipt" class="form-label">{{ __('messages.receipt_voucher') }}</label>
                                <input type="file" name="receipt" class="form-control @error('receipt') is-invalid @enderror">
                                @if($income->getFirstMediaUrl('income_receipts'))
                                    <div class="mt-2">
                                        <a href="{{ $income->getFirstMediaUrl('income_receipts') }}" target="_blank" class="btn btn-info btn-xs">{{ __('messages.view_current_receipt') }}</a>
                                    </div>
                                @endif
                                @error('receipt') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="description" class="form-label">{{ __('messages.description') }}</label>
                                <input name="description" class="form-control" value="{{ old('description', $income->description) }}">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">{{ __('messages.update') }}</button>
                        <a href="{{ route('admin.incomes.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('plugin-scripts')
    <script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('build/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $(".flatpickr").flatpickr({
                altInput: true,
                dateFormat: "Y-m-d",
                altFormat: "d/m/Y"
            });
        });
    </script>
@endpush
