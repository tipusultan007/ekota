@extends('layout.master')
@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Edit Capital Investment</h5>
        <form action="{{ route('admin.capital-investments.update', $capitalInvestment->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Investor (Admin)</label>
                    <select name="user_id" class="form-select" required>
                        @foreach($investors as $investor)
                        <option value="{{ $investor->id }}" {{ $capitalInvestment->user_id == $investor->id ? 'selected' : '' }}>
                            {{ $investor->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Deposit to Account</label>
                    <select name="account_id" class="form-select" required>
                        @foreach($accounts as $account)
                        <option value="{{ $account->id }}" {{ $capitalInvestment->account_id == $account->id ? 'selected' : '' }}>
                            {{ $account->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Amount</label>
                    <input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount', $capitalInvestment->amount) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Investment Date</label>
                    <input type="date" name="investment_date" class="form-control" value="{{ old('investment_date', $capitalInvestment->investment_date->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Description / Notes</label>
                    <input type="text" name="description" class="form-control" value="{{ old('description', $capitalInvestment->description) }}">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Update Investment</button>
            <a href="{{ route('admin.capital-investments.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
