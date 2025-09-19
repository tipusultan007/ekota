@extends('layout.master')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.expenses.index') }}">{{ __('messages.expense_management') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('messages.edit_expense') }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">{{ __('messages.edit_expense') }}</h6>
                    @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
                    <form action="{{ route('admin.expenses.update', $expense->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('messages.category') }} <span class="text-danger">*</span></label>
                                <select name="expense_category_id" class="form-select @error('expense_category_id') is-invalid @enderror" required>
                                    <option value="">{{ __('messages.select_category') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('expense_category_id', $expense->expense_category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('expense_category_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('messages.payment_from_account') }} <span class="text-danger">*</span></label>
                                <select name="account_id" class="form-select @error('account_id') is-invalid @enderror" required>
                                    <option value="">{{ __('messages.select_account') }}</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}" {{ old('account_id', $transaction->account_id ?? $expense->account_id) == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('account_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('messages.amount') }} <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $expense->amount) }}" required>
                                @error('amount') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('messages.date') }} <span class="text-danger">*</span></label>
                                <input type="date" name="expense_date" class="form-control @error('expense_date') is-invalid @enderror" value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" required>
                                @error('expense_date') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">{{ __('messages.description') }}</label>
                                <textarea name="description" class="form-control">{{ old('description', $expense->description) }}</textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('messages.change_receipt_voucher') }}</label>
                                <input type="file" name="receipt" class="form-control @error('receipt') is-invalid @enderror">
                                @if($expense->getFirstMediaUrl('expense_receipts'))
                                    <small class="form-text text-muted">{{ __('messages.current_receipt') }}:
                                        <a href="{{ $expense->getFirstMediaUrl('expense_receipts') }}" target="_blank">{{ __('messages.view') }}</a>
                                    </small>
                                @endif
                                @error('receipt') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">{{ __('messages.update_expense') }}</button>
                        <a href="{{ route('admin.expenses.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
