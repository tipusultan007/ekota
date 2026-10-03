@extends('layout.master')
@section('content')
<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ __('messages.financial_summary') }}</li>
    </ol>
</nav>

{{-- Filter Section --}}
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="card-title mb-0">{{ __('messages.filter_by_date') }}</h6>
            <div class="text-muted small">
                {{ __('messages.showing_results_from') }} <strong>{{ $startDate->format('d M Y') }}</strong> {{ __('messages.to') }} <strong>{{ $endDate->format('d M Y') }}</strong>
            </div>
        </div>
        <form action="{{ route('admin.reports.financial_summary') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label small text-uppercase">{{ __('messages.start_date') }}</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label small text-uppercase">{{ __('messages.end_date') }}</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="link-icon" data-feather="refresh-cw"></i> {{ __('messages.update') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Overall Financial Standing Cards --}}
<div class="row">
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-baseline">
                    <h6 class="card-title mb-0">{{ __('messages.current_cash_balance') }}</h6>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <h3 class="mb-2">{{ number_format($cashBalance, 2) }}</h3>
                        <p class="text-white-50">{{ __('messages.total_across_all_payment_accounts') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-baseline">
                    <h6 class="card-title mb-0">{{ __('messages.society_assets') }}</h6>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <h3 class="mb-2">{{ number_format($totalAssets, 2) }}</h3>
                        <p class="text-white-50">{{ __('messages.cash_plus_outstanding_loans') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card {{ $netProfitLoss >= 0 ? 'bg-info' : 'bg-danger' }} text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-baseline">
                    <h6 class="card-title mb-0">{{ __('messages.net_profit_loss') }}</h6>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <h3 class="mb-2">{{ number_format($netProfitLoss, 2) }}</h3>
                        <p class="text-white-50">{{ __('messages.income_minus_expenses_interest') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Savings Summary --}}
    <div class="col-md-6 grid-margin stretch-card">
        <div class="card border-start border-4 border-info">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="card-title text-info mb-0">{{ __('messages.savings_summary') }}</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-light py-3">
                        <div>
                            <p class="mb-0 text-muted small">{{ __('messages.total_savings_collected') }}</p>
                            <h5 class="fw-bold mb-0 text-dark">{{ number_format($totalSavingsCollected, 2) }}</h5>
                        </div>
                        <i class="link-icon text-info" data-feather="arrow-down-circle"></i>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-light py-3">
                        <div>
                            <p class="mb-0 text-muted small">{{ __('messages.interest_added_to_savings') }}</p>
                            <h5 class="fw-bold mb-0 text-dark">{{ number_format($totalSavingsInterestGiven, 2) }}</h5>
                        </div>
                        <i class="link-icon text-warning" data-feather="plus-circle"></i>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-light py-3">
                        <div>
                            <p class="mb-0 text-muted small">{{ __('messages.total_withdraw') }}</p>
                            <h5 class="fw-bold mb-0 text-dark">{{ number_format($totalSavingsWithdrawn, 2) }}</h5>
                        </div>
                        <i class="link-icon text-danger" data-feather="arrow-up-circle"></i>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-0 py-3 mt-2 rounded bg-light">
                        <div>
                            <p class="mb-0 text-muted small">{{ __('messages.current_savings_balance') }}</p>
                            <h4 class="fw-bold mb-0 text-info">{{ number_format($currentSavingsLiability, 2) }}</h4>
                        </div>
                        <span class="badge bg-info-soft text-info">{{ __('messages.liability') }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Loans Summary --}}
    <div class="col-md-6 grid-margin stretch-card">
        <div class="card border-start border-4 border-success">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="card-title text-success mb-0">{{ __('messages.loan_summary') }}</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-light py-3">
                        <div>
                            <p class="mb-0 text-muted small">{{ __('messages.total_loan_disbursed') }}</p>
                            <h5 class="fw-bold mb-0 text-dark">{{ number_format($totalLoansDisbursed, 2) }}</h5>
                        </div>
                        <i class="link-icon text-success" data-feather="external-link"></i>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-light py-3">
                        <div>
                            <p class="mb-0 text-muted small">{{ __('messages.total_loan_amount_collected') }}</p>
                            <h5 class="fw-bold mb-0 text-dark">{{ number_format($totalLoanPaidInPeriod, 2) }}</h5>
                        </div>
                        <i class="link-icon text-success" data-feather="check-circle"></i>
                    </li>
                    <li class="list-group-item bg-transparent border-light py-3">
                        <div class="row">
                            <div class="col-4 border-end text-center">
                                <p class="mb-0 text-muted small">{{ __('messages.interest_earned') }}</p>
                                <h6 class="fw-bold mb-0 text-success">+{{ number_format($totalInterestGained, 2) }}</h6>
                            </div>
                            <div class="col-4 border-end text-center ps-3">
                                <p class="mb-0 text-muted small">{{ __('messages.total_loan_fees_income') }}</p>
                                <h6 class="fw-bold mb-0 text-success">+{{ number_format($totalLoanFees, 2) }}</h6>
                            </div>
                            <div class="col-4 ps-3 text-center">
                                <p class="mb-0 text-muted small text-nowrap">{{ __('messages.total_income') }}</p>
                                <h6 class="fw-bold mb-0 text-success">+{{ number_format($totalOtherIncome, 2) }}</h6>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-0 py-3 mt-2 rounded bg-light">
                        <div>
                            <p class="mb-0 text-muted small">{{ __('messages.current_loan_outstanding_field') }}</p>
                            <h4 class="fw-bold mb-0 text-success">{{ number_format($loanOutstanding, 2) }}</h4>
                        </div>
                        <span class="badge bg-success-soft text-success">{{ __('messages.asset') }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Expense Breakdown --}}
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">{{ __('messages.expenses_inflow_outflow_breakdown') }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-0 uppercase small fw-bold text-muted">{{ __('messages.category') }}</th>
                                <th class="text-end uppercase small fw-bold text-muted">{{ __('messages.total_inflow') }}</th>
                                <th class="text-end uppercase small fw-bold text-muted">{{ __('messages.total_outflow') }}</th>
                                <th class="text-end uppercase small fw-bold text-muted">{{ __('messages.net_change') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="ps-0 fw-medium">{{ __('messages.members_savings_deposits_withdrawals') }}</td>
                                <td class="text-end text-success">{{ number_format($totalSavingsCollected, 2) }}</td>
                                <td class="text-end text-danger">{{ number_format($totalSavingsWithdrawn, 2) }}</td>
                                <td class="text-end fw-bold">{{ number_format($totalSavingsCollected - $totalSavingsWithdrawn, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="ps-0 fw-medium">{{ __('messages.loans_recovered_disbursed') }}</td>
                                <td class="text-end text-success">{{ number_format($totalLoanPaidInPeriod, 2) }}</td>
                                <td class="text-end text-danger">{{ number_format($totalLoansDisbursed, 2) }}</td>
                                <td class="text-end fw-bold">{{ number_format($totalLoanPaidInPeriod - $totalLoansDisbursed, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="ps-0 fw-medium text-danger">{{ __('messages.salary_expenses') }}</td>
                                <td class="text-end">-</td>
                                <td class="text-end text-danger">{{ number_format($totalSalaryExpense, 2) }}</td>
                                <td class="text-end text-danger">-{{ number_format($totalSalaryExpense, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="ps-0 fw-medium text-danger">{{ __('messages.operational_expenses') }}</td>
                                <td class="text-end">-</td>
                                <td class="text-end text-danger">{{ number_format($totalOtherExpense, 2) }}</td>
                                <td class="text-end text-danger">-{{ number_format($totalOtherExpense, 2) }}</td>
                            </tr>
                             <tr>
                                <td class="ps-0 fw-medium text-danger">{{ __('messages.savings_interest_profit_given') }}</td>
                                <td class="text-end">-</td>
                                <td class="text-end text-danger">{{ number_format($totalSavingsInterestGiven, 2) }}</td>
                                <td class="text-end text-danger">-{{ number_format($totalSavingsInterestGiven, 2) }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-light">
                            <tr class="fw-bold">
                                <td class="ps-0">{{ __('messages.total_flows') }}</td>
                                <td class="text-end text-success">{{ number_format($totalSavingsCollected + $totalLoanPaidInPeriod + $totalInterestGained + $totalLoanFees + $totalOtherIncome, 2) }}</td>
                                <td class="text-end text-danger">{{ number_format($totalSavingsWithdrawn + $totalLoansDisbursed + $totalSalaryExpense + $totalOtherExpense + $totalSavingsInterestGiven, 2) }}</td>
                                <td class="text-end h5 mb-0 {{ ($totalSavingsCollected + $totalLoanPaidInPeriod + $totalInterestGained + $totalLoanFees + $totalOtherIncome - ($totalSavingsWithdrawn + $totalLoansDisbursed + $totalSalaryExpense + $totalOtherExpense + $totalSavingsInterestGiven)) >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($totalSavingsCollected + $totalLoanPaidInPeriod + $totalInterestGained + $totalLoanFees + $totalOtherIncome - ($totalSavingsWithdrawn + $totalLoansDisbursed + $totalSalaryExpense + $totalOtherExpense + $totalSavingsInterestGiven), 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('custom-scripts')
<script>
    // Initialize feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
</script>
<style>
    .bg-info-soft { background-color: rgba(102, 209, 209, 0.1); }
    .bg-success-soft { background-color: rgba(5, 188, 116, 0.1); }
    .uppercase { text-transform: uppercase; }
</style>
@endpush