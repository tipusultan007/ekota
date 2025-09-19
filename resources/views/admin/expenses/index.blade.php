@extends('layout.master')

@push('plugin-styles')
    <link rel="stylesheet" href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}">
@endpush

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('messages.expense_management') }}</li>
        </ol>
    </nav>

    {{-- Record New Expense Form Section --}}
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-header bg-primary">
                    <h6 class="card-title mb-0 text-white">{{ __('messages.record_expense') }}</h6>
                </div>
                <div class="card-body">

                    <form action="{{ route('admin.expenses.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="expense_category_id" class="form-label">{{ __('messages.category') }} <span class="text-danger">*</span></label>
                                <select name="expense_category_id" class="form-select @error('expense_category_id') is-invalid @enderror" required>
                                    <option value="">{{ __('messages.select_category') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('expense_category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('expense_category_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="account_id" class="form-label">{{ __('messages.payment_from_account') }} <span class="text-danger">*</span></label>
                                <select name="account_id" class="form-select @error('account_id') is-invalid @enderror" required>
                                    <option value="">{{ __('messages.select_category') }}</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}" {{ old('account_id') == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                                    @endforeach
                                </select>
                                @error('account_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="amount" class="form-label">{{ __('messages.amount') }} <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}" required>
                                @error('amount') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="expense_date" class="form-label">{{ __('messages.date') }} <span class="text-danger">*</span></label>
                                <input type="text" name="expense_date" class="form-control flatpickr @error('expense_date') is-invalid @enderror" value="{{ old('expense_date', date('Y-m-d')) }}" required>
                                @error('expense_date') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="receipt" class="form-label">{{ __('messages.receipt_voucher') }}</label>
                                <input type="file" name="receipt" class="form-control @error('receipt') is-invalid @enderror">
                                @error('receipt') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="description" class="form-label">{{ __('messages.description') }}</label>
                                <input name="description" class="form-control" value="{{ old('description') }}">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">{{ __('messages.save_expense') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- All Expenses List Section --}}
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-header bg-danger">
                    <h6 class="card-title text-white mb-0">{{ __('messages.all_expenses') }}</h6>

                </div>
                <div class="card-body">

                    {{-- Filter Form --}}
                    <form action="{{ route('admin.expenses.index') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <select name="expense_category_id" class="form-select">
                                    <option value="">{{ __('messages.all_categories') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('expense_category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="start_date" class="form-control flatpickr" value="{{ request('start_date') }}" placeholder="{{ __('messages.start_date') }}">
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="end_date" class="form-control flatpickr" value="{{ request('end_date') }}" placeholder="{{ __('messages.end_date') }}">
                            </div>
                            <div class="col-md-2 d-flex align-items-center">
                                <button type="submit" class="btn btn-primary btn-sm me-2">{{ __('messages.filter') }}</button>
                                <a href="{{ route('admin.expenses.index') }}" class="btn btn-secondary btn-sm">{{ __('messages.reset') }}</a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>{{ __('messages.date') }}</th>
                                <th>{{ __('messages.category') }}</th>
                                <th>{{ __('messages.amount') }}</th>
                                <th>{{ __('messages.description') }}</th>
                                <th>{{ __('messages.receipt_voucher') }}</th>
                                <th>{{ __('messages.actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($expenses as $expense)
                                <tr>
                                    <td>{{ $expense->expense_date->format('d M, Y') }}</td>
                                    <td>{{ $expense->category->name }}</td>
                                    <td class="text-end">{{ number_format($expense->amount, 2) }}</td>
                                    <td>{{ Str::limit($expense->description, 50) }}</td>
                                    <td>
                                        @if($expense->getFirstMediaUrl('expense_receipts'))
                                            <a href="{{ $expense->getFirstMediaUrl('expense_receipts') }}" target="_blank">{{ __('messages.view') }}</a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            <a href="{{ route('admin.expenses.edit', $expense->id) }}" class="btn btn-primary btn-xs me-1">{{ __('messages.edit') }}</a>
                                            <form id="delete-expense-{{ $expense->id }}" action="{{ route('admin.expenses.destroy', $expense->id) }}" method="POST">
                                                @csrf @method('DELETE')
                                                <button type="button" class="btn btn-danger btn-xs" onclick="showDeleteConfirm('delete-expense-{{ $expense->id }}')">
                                                    {{ __('messages.delete') }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">{{ __('messages.no_expenses_found') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                            <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="2" class="text-end">{{ __('messages.total_on_page') }}</td>
                                <td class="text-end">{{ number_format($expenses->sum('amount'), 2) }}</td>
                                <td colspan="3"></td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="mt-4">{{ $expenses->appends(request()->query())->links() }}</div>
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
