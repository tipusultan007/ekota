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
        <h6 class="card-title">{{ __('messages.filter_by_date') }}</h6>
        <form action="{{ route('admin.reports.financial_summary') }}" method="GET">
            <div class="row">
                <div class="col-md-5"><input type="date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}" required></div>
                <div class="col-md-5"><input type="date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}" required></div>
                <div class="col-md-2 d-flex align-items-end"><button type="submit" class="btn btn-primary w-100">{{ __('messages.generate_report') }}</button></div>
            </div>
        </form>
    </div>
</div>

<div class="row">
    {{-- Balance Sheet --}}
    <div class="col-lg-5 grid-margin stretch-card">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ __('messages.balance_sheet', ['date' => \Carbon\Carbon::today()->isoFormat('LL')]) }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <h6 class="text-primary border-bottom pb-2">{{ __('messages.assets') }}</h6>
                        <dl>
                            <dt>{{ __('messages.cash_and_bank') }}</dt>
                            <dd>{{ number_format($assets['cash_and_bank'], 2) }}</dd>
                            <dt>{{ __('messages.loans_receivable') }}</dt>
                            <dd>{{ number_format($assets['loans_receivable'], 2) }}</dd>
                        </dl>
                    </div>
                    <div class="col-6">
                        <h6 class="text-danger border-bottom pb-2">{{ __('messages.liabilities_and_equity') }}</h6>
                        <dl>
                            <dt>{{ __('messages.members_savings_payable') }}</dt>
                            <dd>{{ number_format($liabilities['members_savings'], 2) }}</dd>
                            <dt>{{ __('messages.capital_invested') }}</dt>
                            <dd>{{ number_format($equity['capital_invested'], 2) }}</dd>
                            <dt>{{ __('messages.retained_earnings') }}</dt>
                            <dd>{{ number_format($equity['retained_earnings'], 2) }}</dd>
                        </dl>
                    </div>
                </div>
                <hr>
                <div class="row fw-bold">
                    <div class="col-6">
                        <p class="d-flex justify-content-between bg-light p-2 rounded"><span>{{ __('messages.total_assets') }}:</span> <span>{{ number_format($assets['total'], 2) }}</span></p>
                    </div>
                    <div class="col-6">
                        <p class="d-flex justify-content-between bg-light p-2 rounded"><span>{{ __('messages.total_liabilities_and_equity') }}:</span> <span>{{ number_format($liabilities['total'] + $equity['total'], 2) }}</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Income Statement --}}
    <div class="col-lg-7 grid-margin stretch-card">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ __('messages.income_statement', ['start' => $startDate->isoFormat('ll'), 'end' => $endDate->isoFormat('ll')]) }}</h5>
            </div>
            <div class="card-body">
                <h6 class="text-success border-bottom pb-2">{{ __('messages.income') }}</h6>
                <dl class="row">
                    <dt class="col-sm-8">{{ __('messages.interest_earned') }}</dt>
                    <dd class="col-sm-4 text-end">{{ number_format($income['interest_earned'], 2) }}</dd>
                    <dt class="col-sm-8">{{ __('messages.processing_fee_income') }}</dt>
                    <dd class="col-sm-4 text-end">{{ number_format($income['processing_fee_income'], 2) }}</dd>
                </dl>
                <p class="d-flex justify-content-between bg-light p-2 rounded"><strong>{{ __('messages.total_income') }}:</strong> <strong>{{ number_format($income['total'], 2) }}</strong></p>

                <h6 class="text-danger border-bottom pb-2 mt-4">{{ __('messages.expenses') }}</h6>
                <dl class="row">
                    <dt class="col-sm-8">{{ __('messages.profit_paid_to_members') }}</dt>
                    <dd class="col-sm-4 text-end">{{ number_format($expenses['profit_paid_to_members'], 2) }}</dd>
                    <dt class="col-sm-8">{{ __('messages.loan_grace_given') }}</dt>
                    <dd class="col-sm-4 text-end">{{ number_format($expenses['loan_grace_given'], 2) }}</dd>
                    <dt class="col-sm-8">{{ __('messages.salary_expenses') }}</dt>
                    <dd class="col-sm-4 text-end">{{ number_format($expenses['salary_expenses'], 2) }}</dd>
                    <dt class="col-sm-8">{{ __('messages.operational_expenses') }}</dt>
                    <dd class="col-sm-4 text-end">{{ number_format($expenses['operational_expenses'], 2) }}</dd>
                </dl>
                <p class="d-flex justify-content-between bg-light p-2 rounded"><strong>{{ __('messages.total_expenses') }}:</strong> <strong>{{ number_format($expenses['total'], 2) }}</strong></p>

                <hr>
                <div class="text-center mt-3">
                    <h5>{{ __('messages.net_profit_loss') }}</h5>
                    <h3 class="{{ $netProfitLoss >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($netProfitLoss, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection