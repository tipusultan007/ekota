@extends('layout.master')

@push('plugin-styles')
    <link rel="stylesheet" href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}">
@endpush

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('messages.income_management') }}</li>
        </ol>
    </nav>

    {{-- Record New Income Form Section --}}
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-header bg-success">
                    <h6 class="card-title mb-0 text-white">{{ __('messages.record_income') }}</h6>
                </div>
                <div class="card-body">

                    <form action="{{ route('admin.incomes.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="income_category_id" class="form-label">{{ __('messages.category') }} <span class="text-danger">*</span></label>
                                <select name="income_category_id" class="form-select @error('income_category_id') is-invalid @enderror" required>
                                    <option value="">{{ __('messages.select_category') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('income_category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('income_category_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="account_id" class="form-label">{{ __('messages.payment_to_account') }} <span class="text-danger">*</span></label>
                                <select name="account_id" class="form-select @error('account_id') is-invalid @enderror" required>
                                    <option value="">{{ __('messages.select_account') }}</option>
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
                                <label for="income_date" class="form-label">{{ __('messages.date') }} <span class="text-danger">*</span></label>
                                <input type="text" name="income_date" class="form-control flatpickr @error('income_date') is-invalid @enderror" value="{{ old('income_date', date('Y-m-d')) }}" required>
                                @error('income_date') <span class="invalid-feedback">{{ $message }}</span> @enderror
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

                        <button type="submit" class="btn btn-success">{{ __('messages.save_income') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- All Incomes List Section --}}
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-header bg-primary">
                    <h6 class="card-title text-white mb-0">{{ __('messages.all_incomes') }}</h6>
                </div>
                <div class="card-body">

                    {{-- Filter Form --}}
                    <form action="{{ route('admin.incomes.index') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <select name="income_category_id" class="form-select">
                                    <option value="">{{ __('messages.all_categories') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('income_category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
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
                                <a href="{{ route('admin.incomes.index') }}" class="btn btn-secondary btn-sm">{{ __('messages.reset') }}</a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>{{ __('messages.date') }}</th>
                                <th>{{ __('messages.category') }}</th>
                                <th>{{ __('messages.account') }}</th>
                                <th class="text-end">{{ __('messages.amount') }}</th>
                                <th>{{ __('messages.description') }}</th>
                                <th>{{ __('messages.receipt_voucher') }}</th>
                                <th>{{ __('messages.actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($incomes as $income)
                                <tr>
                                    <td>{{ $income->income_date->format('d M, Y') }}</td>
                                    <td>{{ $income->category->name }}</td>
                                    <td>{{ $income->account->name }}</td>
                                    <td class="text-end fw-bold">{{ number_format($income->amount, 2) }}</td>
                                    <td>{{ Str::limit($income->description, 50) }}</td>
                                    <td>
                                        @if($income->getFirstMediaUrl('income_receipts'))
                                            <a href="{{ $income->getFirstMediaUrl('income_receipts') }}" target="_blank">{{ __('messages.view') }}</a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            <a href="{{ route('admin.incomes.edit', $income->id) }}" class="btn btn-primary btn-xs me-1">{{ __('messages.edit') }}</a>
                                            <form id="delete-income-{{ $income->id }}" action="{{ route('admin.incomes.destroy', $income->id) }}" method="POST" onsubmit="return confirm('{{ __('messages.are_you_sure') }}')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-xs">
                                                    {{ __('messages.delete') }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">{{ __('messages.no_incomes_found') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                            <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="3" class="text-end">{{ __('messages.total_on_page') }}</td>
                                <td class="text-end">{{ number_format($incomes->sum('amount'), 2) }}</td>
                                <td colspan="3"></td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="mt-4">{{ $incomes->appends(request()->query())->links() }}</div>
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
