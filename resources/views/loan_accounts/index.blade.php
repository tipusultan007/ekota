@extends('layout.master')
@push('plugin-styles')
    <link rel="stylesheet" href="{{ asset('build/plugins/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}">
@endpush
@section('content')
    <div class="card my-3">
        <div class="card-body">
            <form action="{{ route('loan_accounts.index') }}" method="GET" class="mb-4">
                <div class="row">
                    @role('Admin')
                    <div class="col-md-3 mb-2"><select name="area_id" class="form-select">
                            <option value="">{{ __('messages.all_areas') }}</option>@foreach($areas as $area)
                                <option
                                    value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                            @endforeach</select></div>
                    @endrole
                    <div class="col-md-3 mb-2"><select name="member_id" id="member_id" class="form-select" data-allow-clear="on">
                            <option value="">{{ __('messages.all_members') }}</option>@foreach($members as $member)
                                <option
                                    value="{{ $member->id }}" {{ request('member_id') == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                            @endforeach</select></div>
                    <div class="col-md-2 mb-2"><select name="status" class="form-select">
                            <option value="">{{ __('messages.all_status') }}</option>
                            <option value="running" {{ request('status') == 'running' ? 'selected' : '' }}>{{ __('messages.running') }}</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>{{ __('messages.paid') }}</option>
                            <option value="defaulted" {{ request('status') == 'defaulted' ? 'selected' : '' }}>{{ __('messages.defaulted') }}</option>
                        </select></div>
                    <div class="col-md-2 mb-2"><input type="text" name="start_date" class="form-control flatpickr"
                                                      value="{{ request('start_date') }}" placeholder="{{ __('messages.start_date') }}"></div>
                    <div class="col-md-2 mb-2"><input type="text" name="end_date" class="form-control flatpickr"
                                                      value="{{ request('end_date') }}" placeholder="{{ __('messages.end_date') }}"></div>
                    <div class="col-md-12 mt-2">
                        <button type="submit" class="btn btn-primary">{{ __('messages.filter') }}</button>
                        <a href="{{ route('loan_accounts.index') }}" class="btn btn-secondary">{{ __('messages.reset') }}</a></div>
                </div>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ __('messages.loan_accounts_list') }}</h5>
            <a href="{{ route('loan.new') }}" class="btn btn-primary">{{  __('messages.new_loan_application') }}</a>
        </div>
        <div class="card-body ">

            <div class="table-responsive min-vh-100">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>{{ __('messages.account_no') }}</th>
                        <th>{{ __('messages.member') }}</th>
                        <th>{{ __('messages.area') }}</th>
                        <th class="text-end">{{ __('messages.loan_amount') }}</th>
                        <th class="text-end">{{ __('messages.paid') }}</th>
                        <th class="text-end">{{ __('messages.grace') }}</th>
                        <th class="text-end">{{ __('messages.due') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th>{{ __('messages.disbursement_date') }}</th>
                        <th>{{ __('messages.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($loanAccounts as $account)
                        <tr>
                            <td><a href="{{ route('loan_accounts.show', $account->id) }}">{{ $account->account_no }}</a></td>
                            <td>{{ $account->member->name }}</td>
                            <td>{{ $account->member->area->name }}</td>
                            <td class="text-end">{{ number_format($account->loan_amount, 2) }}</td>
                            <td class="text-end text-success">{{ number_format($account->total_paid, 2) }}</td>
                            <td class="text-end text-primary">{{ number_format($account->grace_amount, 2) }}</td>
                            <td class="text-end text-danger">{{ number_format($account->loan_due_amount, 2) }}</td>
                            <td>
                                @php
                                    $statusClass = 'danger';
                                    if ($account->status == 'running') $statusClass = 'warning';
                                    elseif ($account->status == 'paid') $statusClass = 'success';
                                @endphp
                                <span class="badge bg-{{ $statusClass }}">{{ __( 'messages.' . strtolower($account->status) ) }}</span>
                            </td>
                            <td>{{ $account->disbursement_date->format('d M, Y') }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton-loan-{{ $account->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                        {{ __('messages.actions') }}
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-loan-{{ $account->id }}">
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center" href="{{ route('loan_accounts.show', $account->id) }}">
                                                <i data-lucide="eye" class="icon-sm me-2"></i> {{ __('messages.view_details') }}
                                            </a>
                                        </li>
                                        @role('Admin')
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center" href="{{ route('loan-accounts.edit', $account->id) }}">
                                                <i data-lucide="edit" class="icon-sm me-2"></i> {{ __('messages.edit_account') }}
                                            </a>
                                        </li>
                                        @if($account->status == 'running')
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center" href="{{ route('loan_accounts.show', $account->id) }}">
                                                    <i data-lucide="check-circle" class="icon-sm me-2"></i> {{ __('messages.pay_off') }}
                                                </a>
                                            </li>
                                        @endif
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form id="delete-loan-account-{{ $account->id }}" action="{{ route('loan-accounts.destroy', $account->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')

                                                <button type="button" class="dropdown-item d-flex align-items-center text-danger" onclick="showDeleteConfirm('delete-loan-account-{{ $account->id }}')">
                                                    <i data-lucide="trash-2" class="icon-sm me-2"></i> {{ __('messages.delete_account') }}
                                                </button>
                                            </form>
                                        </li>
                                        @endrole
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">{{ __('messages.no_loan_accounts_found') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $loanAccounts->appends(request()->query())->links() }}</div>
        </div>
    </div>
@endsection
@push('plugin-scripts')
    <script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('build/plugins/select2/select2.min.js') }}"></script>
    <script src="{{ asset('build/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script>
        $('#member_id').select2({placeholder: "{{ __('messages.select_member') }}", width: '100%'});
        $(".flatpickr").flatpickr({
            altInput: true,
            dateFormat: 'Y-m-d',
            altFormat: 'd/m/Y',
        })
    </script>
@endpush
