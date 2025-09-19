@extends('layout.master')
@push('plugin-styles')
    <link rel="stylesheet" href="{{ asset('build/plugins/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}">
@endpush
@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Daily Transaction Log</li>
        </ol>
    </nav>

    {{-- ফিল্টার সেকশন (আগের মতোই) --}}
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Filter Transactions</h6>
                    <form action="{{ route('reports.daily_transaction_log') }}" method="GET">
                        <div class="row">
                            <div class="col-md-5">
                                <input type="text" name="report_date" class="form-control flatpickr" value="{{ $reportDate->format('Y-m-d') }}">
                            </div>
                            @role('Admin')
                            <div class="col-md-5">
                                <select name="collector_id" class="form-select">
                                    <option value="">All Users</option>
                                    @foreach ($collectors as $collector)
                                        <option value="{{ $collector->id }}" {{ $collectorId == $collector->id ? 'selected' : '' }}>{{ $collector->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endrole
                            <div class="col-md-2 d-flex align-items-end"><button type="submit" class="btn btn-primary w-100">Filter</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 grid-margin">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Transaction Log for: {{ $reportDate->format('d F, Y') }}</h5>
                </div>
                <div class="card-body">
                    {{-- ট্যাব নেভিগেশন --}}
                    <ul class="nav nav-tabs nav-tabs-line" id="transactionTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="savings-deposit-tab" data-bs-toggle="tab" href="#savings-deposit" role="tab" aria-controls="savings-deposit" aria-selected="true">
                                Savings Deposit <span class="badge bg-success ms-1">{{ $savingsCollections->count() }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="loan-installment-tab" data-bs-toggle="tab" href="#loan-installment" role="tab" aria-controls="loan-installment" aria-selected="false">
                                Loan Installment <span class="badge bg-primary ms-1">{{ $loanInstallments->count() }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="savings-withdrawal-tab" data-bs-toggle="tab" href="#savings-withdrawal" role="tab" aria-controls="savings-withdrawal" aria-selected="false">
                                Savings Withdrawal <span class="badge bg-danger ms-1">{{ $savingsWithdrawals->count() }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="expenses-tab" data-bs-toggle="tab" href="#expenses" role="tab" aria-controls="expenses" aria-selected="false">
                                Expenses <span class="badge bg-warning ms-1">{{ $expenses->count() }}</span>
                            </a>
                        </li>
                    </ul>

                    {{-- ট্যাব কন্টেন্ট --}}
                    <div class="tab-content border border-top-0 p-3" id="transactionTabContent">

                        {{-- Savings Deposit Tab --}}
                        <div class="tab-pane fade show active" id="savings-deposit" role="tabpanel" aria-labelledby="savings-deposit-tab">
                            @include('reports.partials.transaction_table', ['items' => $savingsCollections, 'type' => 'savings'])
                        </div>

                        {{-- Loan Installment Tab --}}
                        <div class="tab-pane fade" id="loan-installment" role="tabpanel" aria-labelledby="loan-installment-tab">
                            @include('reports.partials.transaction_table', ['items' => $loanInstallments, 'type' => 'loan'])
                        </div>

                        {{-- Savings Withdrawal Tab --}}
                        <div class="tab-pane fade" id="savings-withdrawal" role="tabpanel" aria-labelledby="savings-withdrawal-tab">
                            @include('reports.partials.transaction_table', ['items' => $savingsWithdrawals, 'type' => 'withdrawal'])
                        </div>

                        {{-- Expenses Tab --}}
                        <div class="tab-pane fade" id="expenses" role="tabpanel" aria-labelledby="expenses-tab">
                            @include('reports.partials.transaction_table', ['items' => $expenses, 'type' => 'expense'])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('plugin-scripts')
    <script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('build/plugins/select2/select2.min.js') }}"></script>
    <script src="{{ asset('build/plugins/flatpickr/flatpickr.min.js') }}"></script>

    <script>
        $('.form-select').select2({placeholder: "Search Field Officer...", width: '100%'});
        $(".flatpickr").flatpickr({altInput: true, dateFormat: "Y-m-d", altFormat: "d/m/Y"});
    </script>
@endpush
