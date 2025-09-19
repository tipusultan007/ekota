@extends('layout.master')
@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ __('messages.salary_payment_history') }}</h5>
            <a href="{{ route('admin.salaries.create') }}" class="btn btn-primary btn-sm mb-3">{{ __('messages.pay_new_salary') }}</a>

            {{-- Filter Form --}}
            <form action="{{ route('admin.salaries.index') }}" method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-5">
                        <select name="user_id" class="form-select">
                            <option value="">{{ __('messages.all_employees') }}</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ request('user_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <input type="month" name="salary_month" class="form-control" value="{{ request('salary_month') }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-center">
                        <button type="submit" class="btn btn-primary btn-sm">{{ __('messages.filter') }}</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>{{ __('messages.payment_date') }}</th>
                        <th>{{ __('messages.employee') }}</th>
                        <th>{{ __('messages.salary_month') }}</th>
                        <th class="text-end">{{ __('messages.amount') }}</th>
                        <th>{{ __('messages.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($salaries as $salary)
                        <tr>
                            <td>{{ $salary->payment_date->format('d M, Y') }}</td>
                            <td>{{ $salary->user->name }}</td>
                            <td>{{ $salary->salary_month }}</td>
                            <td class="text-end">{{ number_format($salary->amount, 2) }}</td>
                            <td>
                                <a href="{{ route('admin.salaries.edit', $salary->id) }}" class="btn btn-primary btn-xs">{{ __('messages.edit') }}</a>
                                <form action="{{ route('admin.salaries.destroy', $salary->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('messages.confirm_delete') }}');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-xs">{{ __('messages.delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">{{ __('messages.no_salary_records') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $salaries->appends(request()->query())->links() }}</div>
        </div>
    </div>
@endsection
