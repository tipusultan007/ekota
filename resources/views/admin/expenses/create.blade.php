@extends('layout.master')
@section('content')
    <div class="card">
        <div class="card-body">
            <h6 class="card-title">{{ __('messages.record_expense') }}</h6>
            <form action="{{ route('admin.expenses.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="expense_category_id" class="form-label">{{ __('messages.category') }}</label>
                        <select name="expense_category_id" class="form-select @error('expense_category_id') is-invalid @enderror">
                            <option value="">{{ __('messages.select_category') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('expense_category_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="amount" class="form-label">{{ __('messages.amount') }}</label>
                        <input type="number" step="0.01" name="amount" class="form-control @error('amount') is-invalid @enderror">
                        @error('amount') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="expense_date" class="form-label">{{ __('messages.date') }}</label>
                        <input type="date" name="expense_date" class="form-control @error('expense_date') is-invalid @enderror" value="{{ date('Y-m-d') }}">
                        @error('expense_date') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="receipt" class="form-label">{{ __('messages.receipt') }}</label>
                        <input type="file" name="receipt" class="form-control @error('receipt') is-invalid @enderror">
                        @error('receipt') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="description" class="form-label">{{ __('messages.description') }}</label>
                        <textarea name="description" class="form-control">{{ old('description') }}</textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">{{ __('messages.save_expense') }}</button>
            </form>
        </div>
    </div>
@endsection
