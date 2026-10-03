@extends('layout.master')

@push('plugin-styles')
    <link rel="stylesheet" href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}">
@endpush

@section('content')
<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ __('messages.field_officer_wise_report') }}</li>
    </ol>
</nav>

<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0">{{ __('messages.field_officer_wise_report') }}</h5>
                    <form action="{{ route('admin.reports.field_officer_wise') }}" method="GET" class="d-flex gap-2">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0"><i data-lucide="calendar" class="icon-sm"></i></span>
                            <input type="text" name="start_date" class="form-control flatpickr border-start-0 ps-0" placeholder="{{ __('messages.start_date') }}" value="{{ $startDate->format('Y-m-d') }}" style="width: 130px;">
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0"><i data-lucide="calendar" class="icon-sm"></i></span>
                            <input type="text" name="end_date" class="form-control flatpickr border-start-0 ps-0" placeholder="{{ __('messages.end_date') }}" value="{{ $endDate->format('Y-m-d') }}" style="width: 130px;">
                        </div>
                        <button type="submit" class="btn btn-primary d-flex align-items-center">
                            <i data-lucide="filter" class="icon-sm me-1"></i> {{ __('messages.filter') }}
                        </button>
                    </form>
                </div>

                {{-- Summary Cards --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card bg-light-primary border-0 shadow-none h-100">
                            <div class="card-body p-3 text-center">
                                <p class="text-muted mb-1 small uppercase fw-bold">{{ __('messages.total_loan_collection') }}</p>
                                <h4 class="mb-0 text-primary">৳ {{ number_format($grandTotalLoan, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light-success border-0 shadow-none h-100">
                            <div class="card-body p-3 text-center">
                                <p class="text-muted mb-1 small uppercase fw-bold">{{ __('messages.total_savings_collected') }}</p>
                                <h4 class="mb-0 text-success">৳ {{ number_format($grandTotalSavings, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light-danger border-0 shadow-none h-100">
                            <div class="card-body p-3 text-center">
                                <p class="text-muted mb-1 small uppercase fw-bold">{{ __('messages.total_withdraw') }}</p>
                                <h4 class="mb-0 text-danger">৳ {{ number_format($grandTotalWithdraw, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Detailed Report Table --}}
                <div class="table-responsive">
                    <table class="table table-hover border">
                        <thead class="bg-light">
                            <tr>
                                <th>{{ __('messages.collector') }}</th>
                                <th class="text-end">{{ __('messages.loan_collection') }}</th>
                                <th class="text-end">{{ __('messages.savings_collection') }}</th>
                                <th class="text-end text-danger">{{ __('messages.withdraw') }}</th>
                                <th class="text-end fw-bold">{{ __('messages.net_collection') }}</th>
                                <th class="text-center">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($fieldOfficers as $officer)
                                @php
                                    $netCollection = ($officer->total_loan + $officer->total_savings) - $officer->total_withdraw;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-2 bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                {{ substr($officer->name, 0, 1) }}
                                            </div>
                                            <span class="fw-medium text-dark">{{ $officer->name }}</span>
                                        </div>
                                    </td>
                                    <td class="text-end">৳ {{ number_format($officer->total_loan, 2) }}</td>
                                    <td class="text-end">৳ {{ number_format($officer->total_savings, 2) }}</td>
                                    <td class="text-end text-danger">৳ {{ number_format($officer->total_withdraw, 2) }}</td>
                                    <td class="text-end fw-bold {{ $netCollection >= 0 ? 'text-success' : 'text-danger' }}">
                                        ৳ {{ number_format($netCollection, 2) }}
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{ route('admin.reports.field_officer.loans.print', ['officer_id' => $officer->id, 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" 
                                               class="btn btn-icon btn-sm btn-outline-primary shadow-none border-0" 
                                               target="_blank" 
                                               title="{{ __('messages.loan_collection_report') }}">
                                                <i data-lucide="printer" class="icon-sm"></i>
                                            </a>
                                            <a href="{{ route('admin.reports.field_officer.savings.print', ['officer_id' => $officer->id, 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" 
                                               class="btn btn-icon btn-sm btn-outline-success shadow-none border-0" 
                                               target="_blank" 
                                               title="{{ __('messages.savings_collection_withdraw_report') }}">
                                                <i data-lucide="printer" class="icon-sm"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        {{ __('messages.no_data_found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($fieldOfficers->count() > 0)
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td>{{ __('messages.total') }}</td>
                                <td class="text-end">৳ {{ number_format($grandTotalLoan, 2) }}</td>
                                <td class="text-end">৳ {{ number_format($grandTotalSavings, 2) }}</td>
                                <td class="text-end text-danger">৳ {{ number_format($grandTotalWithdraw, 2) }}</td>
                                <td class="text-end text-primary">৳ {{ number_format(($grandTotalLoan + $grandTotalSavings) - $grandTotalWithdraw, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-light-primary { background-color: rgba(79, 70, 229, 0.1); }
    .bg-light-success { background-color: rgba(5, 193, 115, 0.1); }
    .bg-light-danger { background-color: rgba(240, 101, 72, 0.1); }
    .text-primary { color: #4f46e5 !important; }
    .text-success { color: #05c173 !important; }
    .text-danger { color: #f06548 !important; }
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
