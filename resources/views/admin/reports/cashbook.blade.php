@extends('layout.master')

@push('plugin-styles')
    <link rel="stylesheet" href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('build/plugins/select2/select2.min.css') }}">
@endpush

@section('content')
<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ __('messages.cashbook') }}</li>
    </ol>
</nav>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">{{ __('messages.cashbook') }}</h5>

        {{-- Filter Form --}}
        <form action="{{ route('admin.reports.cashbook') }}" method="GET" class="mb-4 border-bottom pb-4">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label class="form-label">{{ __('messages.select_account_for_ledger') }}</label>
                    <select name="account_id" id="account_filter" class="form-select" required>
                        <option value="all" {{ request('account_id') == 'all' || !request()->filled('account_id') ? 'selected' : '' }}>
                            All Payment Accounts
                        </option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>
                                {{ $acc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('messages.start_date') }}</label>
                    <input type="text" name="start_date" class="form-control flatpickr" value="{{ $startDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('messages.end_date') }}</label>
                    <input type="text" name="end_date" class="form-control flatpickr" value="{{ $endDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">{{ __('messages.filter') }}</button>
                </div>
            </div>
        </form>

        @if($selectedAccount)
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="text-center mt-3">{{ __('messages.cashbook_report_for', ['account' => $selectedAccount->name]) }}</h4>
                <p class="text-muted mt-3">{{ $startDate->isoFormat('ll') }} to {{ $endDate->isoFormat('ll') }}</p>
            </div>

            <div class="table-responsive mt-3">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 10%;">{{ __('messages.date') }}</th>
                            @if(request('account_id') == 'all' || !request()->filled('account_id'))
                                <th style="width: 15%;">{{ __('messages.account') }}</th>
                            @endif
                            <th>{{ __('messages.transaction_details') }}</th>
                            <th class="text-end" style="width: 12%;">{{ __('messages.debit') }}</th>
                            <th class="text-end" style="width: 12%;">{{ __('messages.credit') }}</th>
                            <th class="text-end" style="width: 15%;">{{ __('messages.running_balance') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Opening Balance --}}
                        <tr>
                            <td colspan="{{ (request('account_id') == 'all' || !request()->filled('account_id')) ? '5' : '4' }}" class="fw-bold">{{ __('messages.opening_balance') }}</td>
                            <td class="text-end fw-bold">{{ number_format($openingBalance, 2) }}</td>
                        </tr>

                        @php
                            $runningBalance = $openingBalance;
                            $totalDebit = 0;
                            $totalCredit = 0;
                        @endphp

                        @forelse ($journalEntries as $entry)
                            @php
                                // প্রতিটি এন্ট্রির নিজস্ব অ্যাকাউন্টের প্রকার অনুযায়ী ব্যালেন্স গণনা
                                if ($entry->account->type === 'Asset' || $entry->account->type === 'Expense') {
                                    $runningBalance += ($entry->debit - $entry->credit);
                                } else { // Liability, Equity, Income
                                    $runningBalance += ($entry->credit - $entry->debit);
                                }
                                $totalDebit += $entry->debit;
                                $totalCredit += $entry->credit;
                            @endphp
                            <tr>
                                <td>{{ $entry->transaction->date->isoFormat('ll') }}</td>
                                @if(request('account_id') == 'all' || !request()->filled('account_id'))
                                    <td><span class="badge bg-secondary">{{ $entry->account->name }}</span></td>
                                @endif
                                <td>{{ $entry->transaction->description }}</td>
                                <td class="text-end text-danger">{{ $entry->debit ? number_format($entry->debit, 2) : '' }}</td>
                                <td class="text-end text-success">{{ $entry->credit ? number_format($entry->credit, 2) : '' }}</td>
                                <td class="text-end">{{ number_format($runningBalance, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ (request('account_id') == 'all' || !request()->filled('account_id')) ? '6' : '5' }}" class="text-center">{{ __('messages.no_transactions_found') }}</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="{{ (request('account_id') == 'all' || !request()->filled('account_id')) ? '3' : '2' }}" class="text-end">{{ __('messages.total_for_period') }}:</td>
                            <td class="text-end">{{ number_format($totalDebit, 2) }}</td>
                            <td class="text-end">{{ number_format($totalCredit, 2) }}</td>
                            <td></td>
                        </tr>
                         <tr>
                            <td colspan="{{ (request('account_id') == 'all' || !request()->filled('account_id')) ? '5' : '4' }}" class="text-end">{{ __('messages.closing_balance') }}:</td>
                            <td class="text-end">{{ number_format($runningBalance, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

@push('plugin-scripts')
    <script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('build/plugins/select2/select2.min.js') }}"></script>
    <script src="{{ asset('build/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#account_filter').select2({ width: '100%' });
            $(".flatpickr").flatpickr({ altInput: true, dateFormat: 'Y-m-d', altFormat: 'd/m/Y' });
        });
    </script>
@endpush