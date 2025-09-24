@extends('layout.master')

@push('plugin-styles')
<link rel="stylesheet" href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}">
@endpush

@section('content')
{{-- Account Header Card --}}
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title">{{ __('messages.account_details') }}: {{ $account->name }} ({{ $account->code }})</h5>
                <p class="text-muted mb-0"><strong>{{ __('messages.closing_balance') }}:</strong>
                    <span class="fw-bold fs-5">{{ number_format($account->calculated_balance, 2) }}</span>
                </p>
            </div>
            <a href="{{ route('admin.accounts.index') }}" class="btn btn-secondary btn-sm">{{ __('messages.back_to_list') }}</a>
        </div>
    </div>
</div>

{{-- Summary Widgets for Selected Date Range --}}
<div class="row">
    <div class="col-md-6 grid-margin">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h6 class="text-uppercase small">{{ __('messages.total_credit') }}</h6>
                <h4 class="mb-0">{{ number_format($totalCredit, 2) }}</h4>
                @if(request('start_date')) <small>(in selected range)</small> @endif
            </div>
        </div>
    </div>
    <div class="col-md-6 grid-margin">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <h6 class="text-uppercase small">{{ __('messages.total_debit') }}</h6>
                <h4 class="mb-0">{{ number_format($totalDebit, 2) }}</h4>
                @if(request('start_date')) <small>(in selected range)</small> @endif
            </div>
        </div>
    </div>
</div>

{{-- Transaction Ledger Card --}}
<div class="card">
    <div class="card-body">
        <h5 class="card-title">{{ __('messages.transaction_history_ledger') }}</h5>

        {{-- Filter Form --}}
        <form action="{{ route('admin.accounts.show', $account->id) }}" method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-5"><input type="text" name="start_date" class="form-control flatpickr" value="{{ request('start_date') }}" placeholder="{{ __('messages.start_date') }}"></div>
                <div class="col-md-5"><input type="text" name="end_date" class="form-control flatpickr" value="{{ request('end_date') }}" placeholder="{{ __('messages.end_date') }}"></div>
                <div class="col-md-2 d-flex align-items-center"><button type="submit" class="btn btn-primary btn-sm me-2">{{ __('messages.filter') }}</button><a href="{{ route('admin.accounts.show', $account->id) }}" class="btn btn-secondary btn-sm">{{ __('messages.reset') }}</a></div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('messages.date') }}</th>
                        <th>{{ __('messages.description') }}</th>
                        <th class="text-end">{{ __('messages.debit') }}</th>
                        <th class="text-end">{{ __('messages.credit') }}</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- ======== পরিবর্তন এখানে: $journalEntries ব্যবহার করা হচ্ছে ======== --}}
                    @forelse ($journalEntries as $entry)
                    <tr>
                        <td>{{ $entry->transaction->date->format('d M, Y') }}</td>
                        <td>{{ $entry->transaction->description }}</td>
                        <td class="text-end text-danger">
                            {{ $entry->debit > 0 ? number_format($entry->debit, 2) : '-' }}
                        </td>
                        <td class="text-end text-success">
                            {{ $entry->credit > 0 ? number_format($entry->credit, 2) : '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">{{ __('messages.no_transactions_found') }}</td>
                    </tr>
                    @endforelse
                    {{-- ========================================================== --}}
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $journalEntries->appends(request()->query())->links() }}</div>
    </div>
</div>
@endsection

@push('plugin-scripts')
<script src="{{ asset('build/plugins/flatpickr/flatpickr.min.js') }}"></script>
<script>
    $(".flatpickr").flatpickr({
        altInput: true,
        dateFormat: 'Y-m-d',
        altFormat: 'd/m/Y',
    });
</script>
@endpush