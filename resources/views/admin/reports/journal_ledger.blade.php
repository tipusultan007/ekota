@extends('layout.master')
@push('custom-styles')
{{-- নতুন স্টাইল যোগ করা হলো --}}
<style>
    .transaction-header-row {
        background-color: #f8f9fa; /* হালকা ধূসর ব্যাকগ্রাউন্ড */
        border-top: 2px solid #dee2e6; /* প্রতিটি লেনদেনের শুরুতে একটি মোটা বর্ডার */
    }
    .journal-entry-row td:first-child {
        border-left: 2px solid #dee2e6;
    }
     .journal-entry-row td:last-child {
        border-right: 2px solid #dee2e6;
    }
     .journal-entry-row:last-of-type td {
        border-bottom: 2px solid #dee2e6;
    }
</style>
@endpush
@section('content')
<nav class.page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ __('messages.journal_entry_ledger') }}</li>
    </ol>
</nav>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">{{ __('messages.all_journal_entries') }}</h5>

        {{-- Filter Form --}}
        <form action="{{ route('admin.reports.journal_ledger') }}" method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-3"><input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" placeholder="{{ __('messages.start_date') }}"></div>
                <div class="col-md-3"><input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" placeholder="{{ __('messages.end_date') }}"></div>
                <div class="col-md-3">
                    <select name="account_id" class="form-select">
                        <option value="">{{ __('messages.filter_by_account') }}</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>{{ $account->name }} ({{ $account->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                     <select name="transaction_type" class="form-select">
                        <option value="">{{ __('messages.filter_by_transaction_type') }}</option>
                        @foreach($transactionTypes as $key => $value)
                            <option value="{{ $key }}" {{ request('transaction_type') == $key ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12 mt-2">
                    <button type="submit" class="btn btn-primary btn-sm">{{ __('messages.filter') }}</button>
                    <a href="{{ route('admin.reports.journal_ledger') }}" class="btn btn-secondary btn-sm">{{ __('messages.reset') }}</a>
                </div>
            </div>
        </form>

       <div class="table-responsive">
            <table class="table table-hover table-bordered w-100">
                <thead class="table-light">
                    <tr>
                        <th style="width: 12%;">{{ __('messages.date') }}</th>
                        <th style="width: 38%;">{{ __('messages.description') }}</th>
                        <th style="width: 25%;">{{ __('messages.account_name') }}</th>
                        <th class="text-end" style="width: 12.5%;">{{ __('messages.debit') }}</th>
                        <th class="text-end" style="width: 12.5%;">{{ __('messages.credit') }}</th>
                    </tr>
                </thead>
                <tbody>
                {{-- ======== পরিবর্তন এখানে: Transaction-এর উপর লুপ ======== --}}
                @forelse ($transactions as $transaction)
                    {{-- প্রতিটি লেনদেন একটি হালকা বর্ডার দিয়ে আলাদা করা হলো --}}
                    <tr class="transaction-header-row">
                        <td class="fw-bold">{{ $transaction->date->format('d M, Y') }}</td>
                        <td colspan="4" class="text-wrap">
                            {{ $transaction->description }}
                            <small class="text-muted">(Txn ID: {{ $transaction->id }})</small>
                        </td>
                    </tr>
                    
                    {{-- এখন এই লেনদেনের সকল Journal Entry-র উপর লুপ --}}
                    @foreach($transaction->journalEntries as $entry)
                    <tr class="journal-entry-row">
                        <td></td> {{-- তারিখের কলাম খালি থাকবে --}}
                        <td colspan="2" class="ps-4">
                            {{ $entry->account->name }}
                            <small class="text-muted">({{ $entry->account->code }})</small>
                        </td>
                        <td class="text-end text-danger">{{ $entry->debit ? number_format($entry->debit, 2) : '' }}</td>
                        <td class="text-end text-success">{{ $entry->credit ? number_format($entry->credit, 2) : '' }}</td>
                    </tr>
                    @endforeach
                @empty
                    <tr><td colspan="5" class="text-center">{{ __('messages.no_transactions_found') }}</td></tr>
                @endforelse
                {{-- ========================================================== --}}
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $transactions->appends(request()->query())->links() }}</div>
    </div>
</div>
@endsection