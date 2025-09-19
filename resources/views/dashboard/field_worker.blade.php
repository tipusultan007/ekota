{{--
@extends('layout.master')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">{{ __('messages.welcome_to_your_dashboard') }}</h4>
        </div>
    </div>

    --}}
{{-- Status Cards --}}{{--

    <div class="row">
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">{{ __('messages.my_members') }}</h5>
                    <h3>{{ $totalMembers }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">{{ __('messages.area_savings') }}</h5>
                    <h3>{{ number_format($totalSavings) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">{{ __('messages.area_due') }}</h5>
                    <h3>{{ number_format($totalLoanDue) }}</h3>
                </div>
            </div>
        </div>
    </div>

    --}}
{{-- Today's Dues Lists --}}{{--

    <div class="row">
        <div class="col-lg-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('messages.loan_installments_due_today') }}</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>{{ __('messages.member') }}</th><th>{{ __('messages.account_no') }}</th><th>{{ __('messages.due_amount') }}</th><th>Action</th></tr></thead>
                            <tbody>
                            @forelse($loanInstallmentsDueToday as $loan)
                                <tr>
                                    <td><a href="{{ route('members.show', $loan->member->id) }}">{{ $loan->member->name }}</a></td>
                                    <td>{{ $loan->account_no }}</td>
                                    <td class="text-danger fw-bold">{{ number_format($loan->installment_amount) }}</td>
                                    <td><a href="{{ route('loan-installments.create') }}" class="btn btn-primary btn-xs">{{ __('messages.collect') }}</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center">{{ __('messages.no_dues_today') }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('messages.savings_due_today') }}</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>{{ __('messages.member') }}</th><th>{{ __('messages.account_no') }}</th><th>Action</th></tr></thead>
                            <tbody>
                            @forelse($savingsDueToday as $saving)
                                <tr>
                                    <td><a href="{{ route('members.show', $saving->member->id) }}">{{ $saving->member->name }}</a></td>
                                    <td>{{ $saving->account_no }}</td>
                                    <td><a href="{{ route('savings-collections.create') }}" class="btn btn-primary btn-xs">{{ __('messages.collect') }}</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center">{{ __('messages.no_dues_today') }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


    --}}
{{-- Today's Performance & Top Defaulters --}}{{--

    <div class="row">
        <div class="col-md-5 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('messages.my_collection_today') }}</h5>
                    <div class="d-flex justify-content-around mt-4">
                        <div class="text-center">
                            <p class="text-muted">{{ __('messages.savings') }}</p>
                            <h4 class="text-success">{{ number_format($todaySavings) }}</h4>
                        </div>
                        <div class="text-center">
                            <p class="text-muted">{{ __('messages.loan_installment') }}</p>
                            <h4 class="text-primary">{{ number_format($todayInstallments) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-7 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('messages.top_5_defaulters') }}</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>{{ __('messages.member') }}</th><th>{{ __('messages.due_amount') }}</th></tr></thead>
                            <tbody>
                            @forelse($topDefaulters as $loan)
                                <tr>
                                    <td><a href="{{ route('members.show', $loan->member->id) }}">{{ $loan->member->name }}</a></td>
                                    <td class="text-danger fw-bold">{{ number_format($loan->due_amount) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center">{{ __('messages.no_defaulters_found') }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
--}}
@extends('layout.master')
@push('style')
    <style>
        /* public/css/custom.css */

        .card-gradient-info {
            background: linear-gradient(to right, #4e54c8, #8f94fb);
            color: #ffffff;
        }

        .card-gradient-success {
            background: linear-gradient(to right, #00b09b, #96c93d);
            color: #ffffff;
        }

        .card-gradient-danger {
            background: linear-gradient(to right, #f5567b, #fd6e6a);
            color: #ffffff;
        }

        .card-gradient-info .card-title,
        .card-gradient-success .card-title,
        .card-gradient-danger .card-title {
            color: rgba(255, 255, 255, 0.9);
        }
    </style>
@endpush
@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">{{ __('messages.welcome_to_your_dashboard') }}</h4>
        </div>
    </div>

    {{-- Status Cards (নতুন ডিজাইন) --}}
    <div class="row">
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card card-gradient-info">
                <div class="card-body text-center">
                    <h5 class="card-title text-uppercase small mb-3">{{ __('messages.my_members') }}</h5>
                    <h2 class="display-5 fw-bolder mb-0">{{ $totalMembers }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card card-gradient-success">
                <div class="card-body text-center">
                    <h5 class="card-title text-uppercase small mb-3">{{ __('messages.area_savings') }}</h5>
                    <h3 class="fw-bolder mb-0">{{ number_format($totalSavings) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card card-gradient-danger">
                <div class="card-body text-center">
                    <h5 class="card-title text-uppercase small mb-3">{{ __('messages.area_due') }}</h5>
                    <h3 class="fw-bolder mb-0">{{ number_format($totalLoanDue) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Today's Performance Card (একত্রিত) --}}
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('messages.my_collection_today') }}</h5>
                    <div class="row text-center mt-4">
                        <div class="col-6 border-end">
                            <p class="text-muted mb-1">{{ __('messages.savings') }}</p>
                            <h3 class="text-success mb-0">{{ number_format($todaySavings) }}</h3>
                        </div>
                        <div class="col-6">
                            <p class="text-muted mb-1">{{ __('messages.loan_installment') }}</p>
                            <h3 class="text-primary mb-0">{{ number_format($todayInstallments) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Today's Dues Lists --}}
    <div class="row">
        <div class="col-lg-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h6 class="card-title mb-0">{{ __('messages.loan_installments_due_today') }} <span class="badge bg-light text-danger ms-1">{{ $loanInstallmentsDueToday->count() }}</span></h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead><tr><th>{{ __('messages.member') }}</th><th class="text-end">{{ __('messages.due_amount') }}</th><th>Action</th></tr></thead>
                            <tbody>
                            @forelse($loanInstallmentsDueToday as $loan)
                                <tr>
                                    <td>
                                        <a href="{{ route('members.show', $loan->member->id) }}">{{ Str::limit($loan->member->name, 20) }}</a>
                                        <br><small class="text-muted">{{ $loan->account_no }}</small>
                                    </td>
                                    <td class="text-end text-danger fw-bold">{{ number_format($loan->installment_amount) }}</td>
                                    <td><a href="{{ route('loan-accounts.show',$loan->id) }}" class="btn btn-primary btn-xs">{{ __('messages.collect') }}</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center p-3 text-muted">{{ __('messages.no_dues_today') }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="card-title mb-0">{{ __('messages.savings_due_today') }} <span class="badge bg-light text-success ms-1">{{ $savingsDueToday->count() }}</span></h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead><tr><th>{{ __('messages.member') }}</th><th>{{ __('messages.scheme') }}</th><th>Action</th></tr></thead>
                            <tbody>
                            @forelse($savingsDueToday as $saving)
                                <tr>
                                    <td>
                                        <a href="{{ route('members.show', $saving->member->id) }}">{{ Str::limit($saving->member->name, 20) }}</a>
                                        <br><small class="text-muted">{{ $saving->account_no }}</small>
                                    </td>
                                    <td>{{ $saving->scheme_type }}</td>
                                    <td><a href="{{ route('savings-accounts.show', $saving->id) }}" class="btn btn-primary btn-xs">{{ __('messages.collect') }}</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center p-3 text-muted">{{ __('messages.no_dues_today') }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Defaulters (এই কার্ডটি এখন ঐচ্ছিক, কারণ উপরের তালিকায় বকেয়া দেখা যাচ্ছে) --}}
    {{-- <div class="row"> ... Top 5 Defaulters card ... </div> --}}
@endsection
