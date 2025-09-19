@extends('layout.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <h6 class="card-title">{{ __('messages.edit_expense_category') }}</h6>
            <form action="{{ route('admin.expense-categories.update', $expenseCategory->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label">{{ __('messages.category_name') }}</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $expenseCategory->name) }}">
                    @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label for="is_active" class="form-label">{{ __('messages.status') }}</label>
                    <select name="is_active" class="form-select">
                        <option value="1" {{ $expenseCategory->is_active ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                        <option value="0" {{ !$expenseCategory->is_active ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">{{ __('messages.update') }}</button>
                <a href="{{ route('admin.expense-categories.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
            </form>
        </div>
    </div>
@endsection
