@extends('layout.master')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('messages.financial_summary') }}</li>
        </ol>
    </nav>

    {{-- Filter Section --}}
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">{{ __('messages.filter_summary_by_date') }}</h6>
                    <form action="{{ route('admin.reports.financial_summary') }}" method="GET">
                        <div class="row">
                            <div class="col-md-5">
                                <label class="form-label">{{ __('messages.start_date') }}</label>
                                <input type="date" name="start_date" class="form-control"
                                    value="{{ $startDate ? $startDate->format('Y-m-d') : '' }}" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">{{ __('messages.end_date') }}</label>
                                <input type="date" name="end_date" class="form-control"
                                    value="{{ $endDate ? $endDate->format('Y-m-d') : '' }}" required>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">{{ __('messages.generate') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

 <div class="row">
        {{-- Balance Sheet (স্থিতিপত্র) --}}
        <div class="col-lg-5 grid-margin stretch-card">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Balance Sheet (as of {{ \Carbon\Carbon::today()->format('d M, Y') }})</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <h6 class="text-primary border-bottom pb-2">Assets (সম্পদ)</h6>
                            <dl>
                                <dt>Cash & Bank</dt>
                                <dd>{{ number_format($assets['cash_and_bank'], 2) }}</dd>
                                <dt>Loan Principal on Field</dt>
                                <dd>{{ number_format($assets['loan_principal_on_field'], 2) }}</dd>
                            </dl>
                        </div>
                        <div class="col-6">
                            <h6 class="text-danger border-bottom pb-2">Liabilities & Equity (দায় ও সত্তা)</h6>
                             <dl>
                                <dt>Members' Savings</dt>
                                <dd>{{ number_format($liabilities['members_savings'], 2) }}</dd>
                                <dt>Capital Invested</dt>
                                <dd>{{ number_format($equity['capital_invested'], 2) }}</dd>
                                <dt>Retained Earnings</dt>
                                <dd>{{ number_format($equity['retained_earnings'], 2) }}</dd>
                            </dl>
                        </div>
                    </div>
                    <hr>
                    <div class="row fw-bold">
                        <div class="col-6"><p class="d-flex justify-content-between bg-light p-2 rounded"><span>Total Assets:</span> <span>{{ number_format($assets['total'], 2) }}</span></p></div>
                        <div class="col-6"><p class="d-flex justify-content-between bg-light p-2 rounded"><span>Total L & E:</span> <span>{{ number_format($liabilities['total'] + $equity['total'], 2) }}</span></p></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Income Statement (আয় বিবরণী) --}}
        <div class="col-lg-7 grid-margin stretch-card">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Income Statement ({{ $startDate->format('d M') }} - {{ $endDate->format('d M, Y') }})</h5></div>
                <div class="card-body">
                    <h6 class="text-success border-bottom pb-2">Income (আয়)</h6>
                    <dl class="row">
                        <dt class="col-sm-8">Interest Earned from Loans</dt>
                        <dd class="col-sm-4 text-end">{{ number_format($income['interest_earned'], 2) }}</dd>
                        {{-- Other incomes can be added here --}}
                    </dl>
                    <p class="d-flex justify-content-between bg-light p-2 rounded"><strong>Total Income:</strong> <strong>{{ number_format($income['total'], 2) }}</strong></p>

                    <h6 class="text-danger border-bottom pb-2 mt-4">Expenses (ব্যয়)</h6>
                    <dl class="row">
                        <dt class="col-sm-8">Profit Paid to Members</dt>
                        <dd class="col-sm-4 text-end">{{ number_format($expenses['profit_paid_to_members'], 2) }}</dd>
                        <dt class="col-sm-8">Loan Grace / Discount Given</dt>
                        <dd class="col-sm-4 text-end">{{ number_format($expenses['loan_grace_given'], 2) }}</dd>
                        <dt class="col-sm-8">Salary Expenses</dt>
                        <dd class="col-sm-4 text-end">{{ number_format($expenses['salary_expenses'], 2) }}</dd>
                        <dt class="col-sm-8">Operational Expenses</dt>
                        <dd class="col-sm-4 text-end">{{ number_format($expenses['operational_expenses'], 2) }}</dd>
                    </dl>
                    <p class="d-flex justify-content-between bg-light p-2 rounded"><strong>Total Expenses:</strong> <strong>{{ number_format($expenses['total'], 2) }}</strong></p>

                    <hr>
                    <div class="text-center mt-3">
                        <h5>Net Profit / Loss</h5>
                        <h3 class="{{ $netProfitLoss >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($netProfitLoss, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
