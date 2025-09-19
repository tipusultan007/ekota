@extends('layout.master')
@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Balance Transfer</h5>
            <form action="{{ route('admin.account_transfers.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-5 mb-3">
                        <label class="form-label">Transfer From</label>
                        <select name="from_account_id" class="form-select" required>
                            <option value="">-- Select Source --</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }} (Balance: {{ $account->balance }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5 mb-3">
                        <label class="form-label">Transfer To</label>
                        <select name="to_account_id" class="form-select" required>
                            <option value="">-- Select Destination --</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.01" name="amount" class="form-control" required>
                    </div>
                    <div class="col-md-5 mb-3">
                        <label class="form-label">Transfer Date</label>
                        <input type="date" name="transfer_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Submit Transfer</button>
            </form>
        </div>
    </div>
    {{-- Recent Transfers List can be added here --}}
@endsection
