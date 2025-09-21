@extends('layout.master')

@section('content')
<nav class.page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ __('messages.area_wise_report') }}</li>
    </ol>
</nav>

<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">{{ __('messages.filter') }}</h5>
        <form action="{{ route('admin.reports.area_wise') }}" method="GET">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label class="form-label">{{ __('messages.select_area') }} <span class="text-danger">*</span></label>
                    <select name="area_id" class="form-select" required>
                        <option value="">-- {{ __('messages.select') }} --</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>
                                {{ $area->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('messages.start_date') }}</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('messages.end_date') }}</label>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">{{ __('messages.generate_report') }}</button>
                </div>
            </div>
            <small class="form-text text-muted">Select an area to generate the report. To view all-time data, leave the date fields blank.</small>
        </form>
    </div>
</div>

@if($selectedArea)
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">{{ __('messages.summary_for_area', ['area' => $selectedArea->name]) }}</h4>
                @if(request('start_date') && request('end_date'))
                    <p class="text-muted mb-0">{{ __('messages.summary_for_period', ['start' => \Carbon\Carbon::parse(request('start_date'))->format('d M, Y'), 'end' => \Carbon\Carbon::parse(request('end_date'))->format('d M, Y')]) }}</p>
                @else
                    <p class="text-muted mb-0">{{ __('messages.all_time_summary') }}</p>
                @endif
            </div>
            <div class="card-body">
                <div class="row">
                    {{-- Savings Widgets --}}
                    <div class="col-md-4 grid-margin">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h6 class="text-uppercase small">{{ __('messages.total_savings_collected') }}</h6>
                                <h4 class="mb-0">{{ number_format($summary['total_savings_collected'], 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 grid-margin">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h6 class="text-uppercase small">{{ __('messages.total_amount_withdrawn') }}</h6>
                                <h4 class="mb-0">{{ number_format($summary['total_withdrawn'], 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 grid-margin">
                         <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h6 class="text-uppercase small">Net Savings</h6>
                                <h4 class="mb-0">{{ number_format($summary['total_savings_collected'] - $summary['total_withdrawn'], 2) }}</h4>
                            </div>
                        </div>
                    </div>

                    {{-- Loan Widgets --}}
                    <div class="col-md-3 grid-margin">
                        <div class="card"><div class="card-body text-center"><p class="text-muted mb-1">{{ __('messages.total_loan_disbursed') }}</p><h5>{{ number_format($summary['total_loan_disbursed'], 2) }}</h5></div></div>
                    </div>
                     <div class="col-md-3 grid-margin">
                        <div class="card"><div class="card-body text-center"><p class="text-muted mb-1">{{ __('messages.total_payable') }}</p><h5>{{ number_format($summary['total_payable'], 2) }}</h5></div></div>
                    </div>
                     <div class="col-md-3 grid-margin">
                        <div class="card"><div class="card-body text-center"><p class="text-muted mb-1">{{ __('messages.total_installments_paid') }}</p><h5 class="text-success">{{ number_format($summary['total_installments_paid'], 2) }}</h5></div></div>
                    </div>
                    <div class="col-md-3 grid-margin">
                        <div class="card"><div class="card-body text-center"><p class="text-muted mb-1">{{ __('messages.loan_on_field') }}</p><h5 class="text-danger">{{ number_format($summary['loan_on_field'], 2) }}</h5></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection