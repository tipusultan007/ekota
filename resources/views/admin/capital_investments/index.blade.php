@extends('layout.master')
@section('content')
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">{{ __('add_capital_investment') }}</h5>
            <form action="{{ route('admin.capital_investments.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('investor') }}</label>
                        <select name="user_id" class="form-select" required>
                            <option value="">{{ __('select_investor') }}</option>
                            @foreach ($investors as $investor)
                                <option value="{{ $investor->id }}">{{ $investor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('deposit_to_account') }}</label>
                        <select name="account_id" class="form-select" required>
                            <option value="">{{ __('select_account') }}</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('amount') }}</label>
                        <input type="number" step="0.01" name="amount" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('investment_date') }}</label>
                        <input type="date" name="investment_date" class="form-control" value="{{ date('Y-m-d') }}"
                            required>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">{{ __('description_notes') }}</label>
                        <input type="text" name="description" class="form-control">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">{{ __('add_investment') }}</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ __('Investment_History') }}</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('date') }}</th>
                            <th>{{ __('investor') }}</th>
                            <th class="text-end">{{ __('amount') }}</th>
                            <th>{{ __('deposited_to') }}</th>
                            <th>{{ __('description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($investments as $investment)
                            <tr>
                                <td>{{ $investment->investment_date->format('d M, Y') }}</td>
                                <td>{{ $investment->user->name }}</td>
                                <td class="text-end">{{ number_format($investment->amount, 2) }}</td>
                                <td>{{ $investment->account->name }}</td>
                                <td>{{ $investment->description }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">{{ __('no_investment_records_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $investments->links() }}</div>
        </div>
    </div>
@endsection
