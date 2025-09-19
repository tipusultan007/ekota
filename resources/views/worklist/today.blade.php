@extends('layout.master')

@section('content')
    <nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ __('messages.todays_worklist') }} ({{ \Carbon\Carbon::today()->format('d M, Y') }})</li>
    </ol>
    </nav>

    <div class="row">
        {{-- আজকের টার্গেট এবং সারাংশ --}}
        <div class="col-md-12 grid-margin">
            <div class="card">
                <div class="card-header bg-primary">
                    <h5 class="card-title mb-0 text-white  ">{{ __('messages.todays_summary_and_targets') }}</h5>
                </div>
                <div class="card-body">

                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="card-title  ">{{ __('messages.outstanding_loan_installments') }}</div>
                            <h4 class="text-danger">{{ $loanInstallmentsDueToday->count() }} {{ __('messages.accounts') }}</h4>
                        </div>
                        <div class="col-md-4">
                            <div class="card-title  ">{{ __('messages.outstanding_savings_collections') }}</div>
                            <h4 class="text-success">{{ $savingsDueToday->count() }} {{ __('messages.accounts') }}</h4>
                        </div>
                        <div class="col-md-4">
                            <div class="card-title  ">{{ __('messages.todays_loan_collection_target') }}</div>
                            <h4 class="text-primary">{{ number_format($totalTarget, 2) }} BDT</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ঋণের কিস্তি তালিকা --}}
        <div class="col-lg-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-header bg-danger">
                    <h6 class="card-title text-white mb-0 ">{{ __('messages.loan_installments_due') }}</h6>
                </div>
                <div class="card-body">

                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                            <tr>
                                <th class="  ">{{ __('messages.member') }}</th>
                                <th class="  ">{{ __('messages.due_date') }}</th>
                                <th class="text-end  ">{{ __('messages.amount') }}</th>
                                <th class="  ">{{ __('messages.action') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($loanInstallmentsDueToday as $loan)
                                <tr>
                                    <td class="">
                                        <a href="{{ route('members.show', $loan->member->id) }}">{{ $loan->member->name }}</a>
                                        <br><small>{{ $loan->member->mobile_no }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">{{ \Carbon\Carbon::parse($loan->next_due_date)->format('d M, Y') }}</span>
                                    </td>
                                    <td class="text-end ">{{ number_format($loan->installment_amount, 2) }}</td>
                                    <td>
                                        <a href="{{ route('loan-accounts.show',$loan->id) }}" class="btn btn-primary btn-xs fw-bolder">{{ __('messages.collect') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">{{ __('messages.no_outstanding_loan_installments') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- সঞ্চয় আদায় তালিকা --}}
        <div class="col-lg-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-header bg-success">
                    <h6 class="card-title text-white mb-0 ">{{ __('messages.savings_collections_due') }}</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                            <tr>
                                <th class="  ">{{ __('messages.member') }}</th>
                                <th class="  ">{{ __('messages.due_date') }}</th>
                                <th class="text-end  ">{{ __('messages.scheme') }}</th>
                                <th class="  ">{{ __('messages.action') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($savingsDueToday as $saving)
                                <tr>
                                    <td class="">
                                        <a href="{{ route('members.show', $saving->member->id) }}">{{ $saving->member->name }}</a>
                                        <br><small>{{ $saving->member->mobile_no }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">{{ \Carbon\Carbon::parse($saving->next_due_date)->format('d M, Y') }}</span>
                                    </td>
                                    <td class="text-end">{{ __('messages.'.$saving->scheme_type) }}</td>
                                    <td>
                                        <a href="{{ route('savings-accounts.show',$saving->id) }}" class="btn btn-primary btn-xs fw-bolder">{{ __('messages.collect') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">{{ __('messages.no_outstanding_savings_collections') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
