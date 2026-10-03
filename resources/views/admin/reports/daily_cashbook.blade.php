@extends('layout.master')

@push('plugin-styles')
    <link rel="stylesheet" href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}">
@endpush

@section('content')
<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ __('messages.daily_cashbook') }}</li>
    </ol>
</nav>

<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0">{{ __('messages.daily_cashbook') }}</h5>
                    <form action="{{ route('admin.reports.daily_cashbook') }}" method="GET" class="d-flex gap-2">
                        <input type="text" name="start_date" class="form-control flatpickr border-secondary-subtle" value="{{ $startDate->format('Y-m-d') }}" style="width: 150px;" placeholder="{{ __('messages.start_date') ?? 'Start Date' }}">
                        <input type="text" name="end_date" class="form-control flatpickr border-secondary-subtle" value="{{ $endDate->format('Y-m-d') }}" style="width: 150px;" placeholder="{{ __('messages.end_date') ?? 'End Date' }}">
                        <button type="submit" class="btn btn-primary d-flex align-items-center">
                            <i data-lucide="filter" class="icon-sm me-1"></i> {{ __('messages.filter') }}
                        </button>
                    </form>
                </div>

                {{-- Summary Cards --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card bg-light-info border-0 shadow-none h-100">
                            <div class="card-body p-3 text-center">
                                <p class="text-muted mb-1 small uppercase fw-bold">{{ __('messages.previous_balance') }}</p>
                                <h4 class="mb-0 text-info">৳ {{ number_format($overallOpeningBalance, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-success border-0 shadow-none h-100">
                            <div class="card-body p-3 text-center">
                                <p class="text-muted mb-1 small uppercase fw-bold">{{ __('messages.total_inflow') }}</p>
                                <h4 class="mb-0 text-success">৳ {{ number_format($overallTotalInflow, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-danger border-0 shadow-none h-100">
                            <div class="card-body p-3 text-center">
                                <p class="text-muted mb-1 small uppercase fw-bold">{{ __('messages.total_outflow') }}</p>
                                <h4 class="mb-0 text-danger">৳ {{ number_format($overallTotalOutflow, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-primary text-white border-0 shadow-none h-100">
                            <div class="card-body p-3 text-center">
                                <p class="text-white-50 mb-1 small uppercase fw-bold">{{ __('messages.closing_balance') }}</p>
                                <h4 class="mb-0 text-white">৳ {{ number_format($overallClosingBalance, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Categories Summary --}}
                <div class="row g-3 mb-4">
                    <div class="col-md">
                        <div class="border rounded p-3 bg-white shadow-sm h-100">
                            <div class="d-flex align-items-center mb-2">
                                <i data-lucide="piggy-bank" class="icon-sm text-success me-2"></i>
                                <span class="small fw-bold text-muted">{{ __('messages.total_savings_collected') }}</span>
                            </div>
                            <h5 class="mb-0">৳ {{ number_format($totalSavingsCollection, 2) }}</h5>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="border rounded p-3 bg-white shadow-sm h-100">
                            <div class="d-flex align-items-center mb-2">
                                <i data-lucide="banknote" class="icon-sm text-primary me-2"></i>
                                <span class="small fw-bold text-muted">{{ __('messages.total_loan_collection') }} ({{ __('messages.principal') }})</span>
                            </div>
                            <h5 class="mb-0">৳ {{ number_format($totalLoanCollection, 2) }}</h5>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="border rounded p-3 bg-white shadow-sm h-100">
                            <div class="d-flex align-items-center mb-2">
                                <i data-lucide="wallet" class="icon-sm text-info me-2"></i>
                                <span class="small fw-bold text-muted">{{ __('messages.total_gross_loan_collection') }}</span>
                            </div>
                            <h5 class="mb-0">৳ {{ number_format($totalGrossLoanCollection, 2) }}</h5>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="border rounded p-3 bg-white shadow-sm h-100">
                            <div class="d-flex align-items-center mb-2">
                                <i data-lucide="trending-up" class="icon-sm text-info me-2"></i>
                                <span class="small fw-bold text-muted">{{ __('messages.total_income') }}</span>
                            </div>
                            <h5 class="mb-0">৳ {{ number_format($totalIncome, 2) }}</h5>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="border rounded p-3 bg-white shadow-sm h-100">
                            <div class="d-flex align-items-center mb-2">
                                <i data-lucide="percent" class="icon-sm text-success me-2"></i>
                                <span class="small fw-bold text-muted">{{ __('messages.interest_income') }}</span>
                            </div>
                            <h5 class="mb-0">৳ {{ number_format($totalInterestIncome, 2) }}</h5>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="border rounded p-3 bg-white shadow-sm h-100">
                            <div class="d-flex align-items-center mb-2">
                                <i data-lucide="arrow-up-circle" class="icon-sm text-danger me-2"></i>
                                <span class="small fw-bold text-muted">{{ __('messages.total_withdrawn') }}</span>
                            </div>
                            <h5 class="mb-0">৳ {{ number_format($totalWithdraw, 2) }}</h5>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="border rounded p-3 bg-white shadow-sm h-100">
                            <div class="d-flex align-items-center mb-2">
                                <i data-lucide="credit-card" class="icon-sm text-warning me-2"></i>
                                <span class="small fw-bold text-muted">{{ __('messages.total_expenses') }}</span>
                            </div>
                            <h5 class="mb-0">৳ {{ number_format($totalExpense, 2) }}</h5>
                        </div>
                    </div>
                </div>

                {{-- Category Wise Summary --}}
                <div class="mb-5">
                    <h6 class="mb-3 d-flex align-items-center">
                        <i data-lucide="layout-grid" class="icon-sm me-2 text-primary"></i> 
                        {{ __('messages.category_wise_summary') }}
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-hover border">
                            <thead class="bg-light">
                                <tr>
                                    <th>{{ __('messages.category') }}</th>
                                    <th class="text-end text-success" style="width: 20%;">{{ __('messages.debit') }} (In)</th>
                                    <th class="text-end text-danger" style="width: 20%;">{{ __('messages.credit') }} (Out)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categorySummaries as $summary)
                                    <tr>
                                        <td class="fw-medium text-dark">
                                            {{ __('messages.' . $summary['name_key']) }}
                                        </td>
                                        <td class="text-end text-success fw-bold">
                                            {{ $summary['inflow'] > 0 ? '৳ ' . number_format($summary['inflow'], 2) : '-' }}
                                        </td>
                                        <td class="text-end text-danger fw-bold">
                                            {{ $summary['outflow'] > 0 ? '৳ ' . number_format($summary['outflow'], 2) : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light fw-bold">
                                <tr>
                                    <td>{{ __('messages.total_of_the_day') }}</td>
                                    <td class="text-end text-success">৳ {{ number_format($overallTotalInflow, 2) }}</td>
                                    <td class="text-end text-danger">৳ {{ number_format($overallTotalOutflow, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- Payment Method Summary --}}
                <div class="mb-5">
                    <h6 class="mb-3 d-flex align-items-center">
                        <i data-lucide="credit-card" class="icon-sm me-2 text-primary"></i> 
                        {{ __('messages.payment_method_summary') }}
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm border">
                            <thead class="bg-light">
                                <tr>
                                    <th>{{ __('messages.account') }}</th>
                                    <th class="text-end">{{ __('messages.previous_balance') }}</th>
                                    <th class="text-end text-success">{{ __('messages.total_inflow') }} (In)</th>
                                    <th class="text-end text-danger">{{ __('messages.total_outflow') }} (Out)</th>
                                    <th class="text-end fw-bold">{{ __('messages.closing_balance') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($paymentMethodSummaries as $summary)
                                    <tr>
                                        <td class="fw-medium text-dark">{{ $summary['account_name'] }}</td>
                                        <td class="text-end">৳ {{ number_format($summary['opening'], 2) }}</td>
                                        <td class="text-end text-success">৳ {{ number_format($summary['inflow'], 2) }}</td>
                                        <td class="text-end text-danger">৳ {{ number_format($summary['outflow'], 2) }}</td>
                                        <td class="text-end fw-bold">৳ {{ number_format($summary['closing'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light fw-bold">
                                <tr>
                                    <td>{{ __('messages.total') }}</td>
                                    <td class="text-end">৳ {{ number_format($overallOpeningBalance, 2) }}</td>
                                    <td class="text-end text-success">৳ {{ number_format($overallTotalInflow, 2) }}</td>
                                    <td class="text-end text-danger">৳ {{ number_format($overallTotalOutflow, 2) }}</td>
                                    <td class="text-end">৳ {{ number_format($overallClosingBalance, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>

<style>
    .bg-light-info { background-color: rgba(100, 197, 231, 0.1); }
    .bg-light-success { background-color: rgba(5, 193, 115, 0.1); }
    .bg-light-danger { background-color: rgba(240, 101, 72, 0.1); }
    .text-info { color: #64c5e7 !important; }
    .text-success { color: #05c173 !important; }
    .text-danger { color: #f06548 !important; }
    .icon-lg { width: 48px; height: 48px; }
</style>
@endsection

@push('plugin-scripts')
    <script src="{{ asset('build/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $(".flatpickr").flatpickr({ 
                altInput: true, 
                dateFormat: 'Y-m-d', 
                altFormat: 'd/m/Y' 
            });
        });
    </script>
@endpush
