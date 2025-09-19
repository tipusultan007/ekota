@extends('layout.master')
@push('plugin-styles')
    <link rel="stylesheet" href="{{ asset('build/plugins/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}">
@endpush
@section('content')
    <div class="card my-3">
        <div class="card-body">
            <form action="{{ route('savings_accounts.index') }}" method="GET" class="mb-4">
                <div class="row">
                    @role('Admin')
                    <div class="col-md-3"><select name="area_id" class="form-select">
                            <option value="">{{ __('messages.all_areas') }}</option>@foreach($areas as $area)
                                <option
                                    value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                            @endforeach</select></div>
                    @endrole
                    <div class="col-md-3"><select name="member_id" id="member_id" class="form-select" data-allow-clear="on">
                            <option value="">{{ __('messages.all_members') }}</option>@foreach($members as $member)
                                <option
                                    value="{{ $member->id }}" {{ request('member_id') == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                            @endforeach</select></div>
                    <div class="col-md-2"><select name="status" class="form-select">
                            <option value="">{{ __('messages.all_status') }}</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>{{ __('messages.closed') }}</option>
                        </select></div>
                    <div class="col-md-2"><input type="text" name="start_date" class="form-control flatpickr"
                                                 value="{{ request('start_date') }}" placeholder="{{ __('messages.enter_start_date') }}"></div>
                    <div class="col-md-2"><input type="text" name="end_date" class="form-control flatpickr"
                                                 value="{{ request('end_date') }}" placeholder="{{ __('messages.enter_end_date') }}"></div>
                    <div class="col-md-12 mt-2">
                        <button type="submit" class="btn btn-primary">{{ __('messages.filter') }}</button>
                        <a href="{{ route('savings_accounts.index') }}" class="btn btn-secondary">{{ __('messages.reset') }}</a></div>
                </div>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ __('messages.savings_accounts_list') }}</h5>
            <a href="{{ route('savings.new_savings') }}" class="btn btn-primary">New Savings</a>
        </div>
        <div class="card-body">
            <div class="table-responsive min-vh-100">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>{{ __('messages.account_no') }}</th>
                        <th>{{ __('messages.member') }}</th>
                        <th>{{ __('messages.area') }}</th>
                        <th>{{ __('messages.scheme') }}</th>
                        <th>{{ __('messages.balance') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th>{{ __('messages.opening_date') }}</th>
                        <th>{{ __('messages.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($savingsAccounts as $account)
                        <tr>
                            <td>
                                <a href="{{ route('savings_accounts.show', $account->id) }}">{{ $account->account_no }}</a>
                            </td>
                            <td>{{ $account->member->name }}</td>
                            <td>{{ $account->member->area->name }}</td>
                            <td>{{ $account->scheme_type }}</td>
                            <td class="text-end">{{ number_format($account->current_balance, 2) }}</td>
                            <td><span
                                    class="badge bg-{{ $account->status == 'active' ? 'success' : 'secondary' }}">{{ __( 'messages.' . strtolower($account->status) ) }}</span>
                            </td>
                            <td>{{ $account->opening_date->format('d M, Y') }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton-{{ $account->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                        {{ __('messages.actions') }}
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-{{ $account->id }}">
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center" href="{{ route('savings_accounts.show', $account->id) }}">
                                                <i data-lucide="eye" class="icon-sm me-2"></i> {{ __('messages.view_details') }}
                                            </a>
                                        </li>
                                        @role('Admin')
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center" href="{{ route('savings-accounts.edit', $account->id) }}">
                                                <i data-lucide="edit" class="icon-sm me-2"></i> {{ __('messages.edit_account') }}
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center" href="{{ route('members.show', $account->member_id) }}">
                                                <i data-lucide="arrow-up-right" class="icon-sm me-2"></i> {{ __('messages.withdraw') }}
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form id="delete-savings-account-{{ $account->id }}" action="{{ route('savings-accounts.destroy', $account->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')

                                                <button type="button" class="dropdown-item d-flex align-items-center text-danger" onclick="showDeleteConfirm('delete-savings-account-{{ $account->id }}')">
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
                            <td colspan="8" class="text-center">{{ __('messages.no_savings_accounts_found') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $savingsAccounts->appends(request()->query())->links() }}</div>
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
