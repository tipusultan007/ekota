@extends('layout.master')

@push('plugin-styles')
    <style>
        /* Card Gradient Styles */
        .card-gradient-info { background: linear-gradient(to right, #4e54c8, #8f94fb); color: white; }
        .card-gradient-success { background: linear-gradient(to right, #1d976c, #93f9b9); color: white; }
        .card-gradient-danger { background: linear-gradient(to right, #cb2d3e, #ef473a); color: white; }
        .card-gradient-warning { background: linear-gradient(to right, #ff8008, #ffc837); color: white; }
        .card-gradient-primary { background: linear-gradient(to right, #00c6ff, #0072ff); color: white; }
        .card-gradient-dark { background: linear-gradient(to right, #434343, #000000); color: white; }

        .card[class*="card-gradient-"] h1,
        .card[class*="card-gradient-"] h2,
        .card[class*="card-gradient-"] h3,
        .card[class*="card-gradient-"] h4,
        .card[class*="card-gradient-"] h5,
        .card[class*="card-gradient-"] h6,
        .card[class*="card-gradient-"] .text-muted {
            color: rgba(255, 255, 255, 0.9);
        }
        .card[class*="card-gradient-"] .small {
            color: rgba(255, 255, 255, 0.8);
        }
    </style>
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3  mb-md-0">{{ __('messages.welcome_to_admin_dashboard') }}</h4>
        </div>
    </div>

    {{-- Overall Summary Cards (গ্র্যাডিয়েন্ট সহ) --}}
    <div class="row">
        <div class="col-md-4 col-lg-2 grid-margin stretch-card">
            <div class="card card-gradient-primary">
                <div class="card-body text-center">
                    <h5 class="text-uppercase">{{ __('messages.total_members') }}</h5>
                    <h4 class="mb-0">{{ $totalMembers }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2 grid-margin stretch-card">
            <div class="card card-gradient-success">
                <div class="card-body text-center">
                    <h5 class="text-uppercase">{{ __('messages.withdrawable_savings') }}</h5>
                    <h4 class="mb-0">{{ number_format($withdrawableAmount) }}</h4>
                    <small>{{ __('messages.total_savings_balance') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2 grid-margin stretch-card">
            <div class="card card-gradient-danger">
                <div class="card-body text-center">
                    <h5 class="text-uppercase">{{ __('messages.total_withdrawn') }}</h5>
                    <h4 class="mb-0">{{ number_format($totalWithdrawn) }}</h4>
                    <small>{{ __('messages.all_time') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2 grid-margin stretch-card">
            <div class="card card-gradient-info">
                <div class="card-body text-center">
                    <h5 class="text-uppercase">{{ __('messages.loan_disbursed') }}</h5>
                    <h4 class="mb-0">{{ number_format($totalLoanDisbursed) }}</h4>
                    <small>{{ __('messages.all_time') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2 grid-margin stretch-card">
            <div class="card card-gradient-warning">
                <div class="card-body text-center">
                    <h5 class="text-uppercase">{{ __('messages.loan_on_field') }}</h5>
                    <h4 class="mb-0">{{ number_format($totalLoanDue) }}</h4>
                    <small>{{ __('messages.total_due') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2 grid-margin stretch-card">
            <div class="card card-gradient-dark">
                <div class="card-body text-center">
                    <h5 class="text-uppercase">{{ __('messages.net_position') }}</h5>
                    @php
                        $netPosition = $withdrawableAmount - $totalLoanDue;
                    @endphp
                    <h4 class="mb-0 {{ $netPosition >= 0 ? '' : 'text-danger' }}">
                        {{ number_format($netPosition) }}
                    </h4>
                    <small class="text-light">{{ __('messages.savings_vs_due') }}</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Today's Summary & Charts --}}
    <div class="row">
        {{-- Today's Statistics Section --}}
        <div class="col-lg-5 col-xl-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0  ">{{ __('messages.todays_statistics') }}
                        ({{ \Carbon\Carbon::today()->format('d M, Y') }})</h4>
                </div>
                <div class="card-body">

                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between">
                            <span class="text-success ">{{ __('messages.savings_collection') }}</span>
                            <span class="fw-bold text-success">+ {{ number_format($todaySavings) }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between">
                            <span class="text-primary ">{{ __('messages.loan_collection') }}</span>
                            <span class="fw-bold text-primary">+ {{ number_format($todayInstallments) }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between">
                            <span class="text-danger ">{{ __('messages.savings_withdrawal') }}</span>
                            <span class="fw-bold text-danger">- {{ number_format($todayWithdrawals) }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between">
                            <span class="text-warning ">{{ __('messages.other_expenses') }}</span>
                            <span class="fw-bold text-warning">- {{ number_format($todayExpenses) }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between bg-light">
                            @php
                                $netCashFlow = ($todaySavings + $todayInstallments) - ($todayWithdrawals + $todayExpenses);
                            @endphp
                           <span class=""> <strong>{{ __('messages.net_cash_flow') }}</strong></span>
                            <strong class="{{ $netCashFlow >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($netCashFlow) }}
                            </strong>
                        </div>
                    </div>

                    <hr>
                    <h6 class="card-title   mt-4">{{ __('messages.members_by_area') }}</h6>
                    <div id="areaPieChart"></div>
                </div>
            </div>
        </div>

        {{-- Monthly Chart Section --}}
        <div class="col-lg-7 col-xl-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title  mb-0 ">{{ __('messages.monthly_collection_last_6_months') }}</h5>
                </div>
                <div class="card-body">

                    <div id="monthlyCollectionChart"></div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('plugin-scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@endpush

@push('custom-scripts')
    <script>
        // Monthly Collection Bar Chart
        var optionsBar = {
            chart: {type: 'bar', height: 350},
            series: [
                {name: "{{ __('messages.savings') }}", data: @json($monthlyCollections['savings']) },
                {name: "{{ __('messages.loans') }}", data: @json($monthlyCollections['loans']) }
            ],
            xaxis: {categories: @json($monthlyCollections['months']) }
        };
        var chartBar = new ApexCharts(document.querySelector("#monthlyCollectionChart"), optionsBar);
        chartBar.render();

        // Area wise Members Pie Chart
        var optionsPie = {
            chart: {type: 'pie', height: 250},
            series: @json($areaWiseMembers->pluck('count')),
            labels: @json($areaWiseMembers->pluck('name')),
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        };
        var chartPie = new ApexCharts(document.querySelector("#areaPieChart"), optionsPie);
        chartPie.render();
    </script>
@endpush
