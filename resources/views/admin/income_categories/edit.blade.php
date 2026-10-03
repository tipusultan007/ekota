@extends('layout.master')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.income-categories.index') }}">{{ __('messages.income_categories') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('messages.edit_income_category') }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">{{ __('messages.edit_income_category') }}</h6>
                    <form action="{{ route('admin.income-categories.update', $incomeCategory->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('messages.category_name') }}</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $incomeCategory->name) }}">
                            @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="is_active" class="form-label">{{ __('messages.status') }}</label>
                            <select name="is_active" class="form-select @error('is_active') is-invalid @enderror">
                                <option value="1" {{ old('is_active', $incomeCategory->is_active) ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                                <option value="0" {{ !old('is_active', $incomeCategory->is_active) ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                            </select>
                            @error('is_active') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">{{ __('messages.update') }}</button>
                        <a href="{{ route('admin.income-categories.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
