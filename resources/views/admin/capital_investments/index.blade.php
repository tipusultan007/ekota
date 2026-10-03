@extends('layout.master')
@section('content')
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">{{ __('messages.add_capital_investment') }}</h5>
            <form action="{{ route('admin.capital-investments.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('messages.investor') }}</label>
                        <select name="user_id" class="form-select" required>
                            <option value="">{{ __('messages.select_investor') }}</option>
                            @foreach ($investors as $investor)
                                <option value="{{ $investor->id }}">{{ $investor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('messages.deposit_to_account') }}</label>
                        <select name="account_id" class="form-select" required>
                            <option value="">{{ __('messages.select_account') }}</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('messages.amount') }}</label>
                        <input type="number" step="0.01" name="amount" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('messages.investment_date') }}</label>
                        <input type="date" name="investment_date" class="form-control" value="{{ date('Y-m-d') }}"
                            required>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">{{ __('messages.description_notes') }}</label>
                        <input type="text" name="description" class="form-control">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">{{ __('messages.add_investment') }}</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ __('messages.investment_history') }}</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('messages.date') }}</th>
                            <th>{{ __('messages.investor') }}</th>
                            <th class="text-end">{{ __('messages.amount') }}</th>
                            <th>{{ __('messages.deposited_to') }}</th>
                            <th>{{ __('messages.description') }}</th>
                              <th>{{ __('messages.actions') }}</th>
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
                                <td>
                                    <div class="d-flex">
                                        <a href="{{ route('admin.capital-investments.edit', $investment->id) }}" class="btn btn-primary btn-xs me-1">{{ __('messages.edit') }}</a>
                                        <form id="delete-investment-{{ $investment->id }}" action="{{ route('admin.capital-investments.destroy', $investment->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-danger btn-xs" 
                                                    onclick="showDeleteConfirm('delete-investment-{{ $investment->id }}')">
                                                {{ __('messages.delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">{{ __('messages.no_investment_records_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $investments->links() }}</div>
        </div>
    </div>
@endsection
