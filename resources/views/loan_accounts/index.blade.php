@extends('layout.master')
@section('title', __('messages.loan_accounts_list') . ' | ' . config('app.name'))
@push('plugin-styles')
    <link rel="stylesheet" href="{{ asset('build/plugins/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}">
    <style>
        /* Premium Design System - Reused */
        .premium-card {
            border: none;
            border-radius: 1.25rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            background: #fff;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .premium-header {
            background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%); /* Red/Pink gradient for Loans */
            padding: 1.5rem 2rem;
            position: relative;
        }
        
        /* Filter Header - Blue */
        .filter-header {
            background: linear-gradient(135deg, #0ea5e9 0%, #3b82f6 100%);
        }

        .premium-header::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at top right, rgba(255,255,255,0.2), transparent);
            pointer-events: none;
        }

        .premium-title {
            color: #fff;
            font-weight: 700;
            margin: 0;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
        }

        .form-control-premium, .form-select {
            border-radius: 0.75rem;
            padding: 0.65rem 1rem;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            transition: all 0.2s ease;
        }

        .form-control-premium:focus, .form-select:focus {
            background-color: #fff;
            border-color: #f43f5e;
            box-shadow: 0 0 0 4px rgba(244, 63, 94, 0.1);
        }

        .table-premium {
            border-collapse: separate;
            border-spacing: 0 0.5rem;
        }

        .table-premium thead th {
            border: none;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: #64748b;
            padding: 1rem;
            background: transparent;
            font-weight: 600;
        }

        .table-premium tbody tr {
            background: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
            border-radius: 0.75rem;
            transition: all 0.2s ease;
            position: relative; /* Ensure stacking context can be controlled */
        }

        .table-premium tbody tr:hover,
        .table-premium tbody tr:focus-within {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            z-index: 10; /* Bring hovered row or row with focused dropdown to front */
        }

        .table-premium tbody td {
            border: none;
            padding: 1rem;
            vertical-align: middle;
            color: #334155;
            font-weight: 500;
        }
        
        .table-premium tbody td:first-child { border-top-left-radius: 0.75rem; border-bottom-left-radius: 0.75rem; }
        .table-premium tbody td:last-child { border-top-right-radius: 0.75rem; border-bottom-right-radius: 0.75rem; }

        .btn-filter, .btn-reset {
            padding: 0.65rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-filter {
            background: linear-gradient(135deg, #0f172a 0%, #334155 100%);
            color: white !important;
            border: none;
        }
        
        .btn-filter:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(15, 23, 42, 0.2); color: white; }

        .select2-container--default .select2-selection--single {
            border-radius: 0.75rem;
            padding: 0px 12px;
            border: 1px solid #e2e8f0;
            height: 42px;
            background-color: #f8fafc;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; }

        /* Stats Cards */
        .stats-card {
            border: none;
            border-radius: 1.25rem;
            padding: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            color: #fff;
            height: 100%;
        }
        .stats-card:hover { transform: translateY(-5px); }
        .stats-card::after {
            content: '';
            position: absolute;
            top: -20%; right: -10%;
            width: 100px; height: 100px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }
        .stats-icon {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        .stats-value { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem; }
        .stats-label { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; opacity: 0.9; letter-spacing: 0.05em; }
        
        .bg-grad-loan { background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%); }
        .bg-grad-paid { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .bg-grad-due { background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%); }
        .bg-grad-interest { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
        .bg-grad-collections { background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%); }
    </style>
@endpush
@section('content')
    <div class="card premium-card mb-4">
        <div class="premium-header filter-header">
            <h5 class="premium-title">
                <i data-lucide="filter" class="icon-sm me-2"></i> {{ __('messages.filter') }} {{ __('messages.loan_accounts') }}
            </h5>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('loan_accounts.index') }}" method="GET">
                <div class="row g-3">
                    
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold text-uppercase">{{ __('messages.area') }}</label>
                        <select name="area_id" class="form-select">
                            <option value="">{{ __('messages.all_areas') }}</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                            @endforeach
                        </select>
                    </div>
                   
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold text-uppercase">{{ __('messages.member') }}</label>
                        <select name="member_id" id="member_id" class="form-select" data-allow-clear="on">
                            <option value="">{{ __('messages.all_members') }}</option>
                            @foreach($members as $member)
                                <option value="{{ $member->id }}" {{ request('member_id') == $member->id ? 'selected' : '' }}>{{ $member->name }} - {{  $member->account_no }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                         <label class="form-label text-muted small fw-bold text-uppercase">{{ __('messages.status') }}</label>
                         <select name="status" class="form-select">
                            <option value="">{{ __('messages.all_status') }}</option>
                            <option value="running" {{ request('status') == 'running' ? 'selected' : '' }}>{{ __('messages.running') }}</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>{{ __('messages.paid') }}</option>
                            <option value="defaulted" {{ request('status') == 'defaulted' ? 'selected' : '' }}>{{ __('messages.defaulted') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-muted small fw-bold text-uppercase">{{ __('messages.start_date') }}</label>
                        <input type="text" name="start_date" class="form-control flatpickr form-control-premium"
                             value="{{ request('start_date') }}" placeholder="YYYY-MM-DD">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-muted small fw-bold text-uppercase">{{ __('messages.end_date') }}</label>
                        <input type="text" name="end_date" class="form-control flatpickr form-control-premium"
                             value="{{ request('end_date') }}" placeholder="YYYY-MM-DD">
                    </div>
                    <div class="col-md-12 d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('loan_accounts.index') }}" class="btn btn-light btn-reset border">{{ __('messages.reset') }}</a>
                        <button type="submit" class="btn btn-filter shadow-sm">
                            <i data-lucide="search" class="icon-sm me-1"></i> {{ __('messages.filter') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Statistics Widget --}}
    <div class="row mb-4 g-3">
        <div class="col-md-6 col-xl">
            <div class="stats-card bg-grad-loan shadow-sm">
                <div class="stats-icon">
                    <i data-lucide="briefcase" class="text-white"></i>
                </div>
                <div class="stats-value">{{ number_format($statistics->total_loan_amount ?? 0, 2) }}</div>
                <div class="stats-label">{{ __('messages.total_loan_given') }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl">
            <div class="stats-card bg-grad-paid shadow-sm">
                <div class="stats-icon">
                    <i data-lucide="check-square" class="text-white"></i>
                </div>
                <div class="stats-value">{{ number_format($collectionStats->total_principal_collected ?? 0, 2) }}</div>
                <div class="stats-label">{{ __('messages.total_paid_loan') }}</div>
            </div>
        </div>
        <div class="col-md-12 col-xl">
            <div class="stats-card bg-grad-interest shadow-sm">
                <div class="stats-icon">
                    <i data-lucide="trending-up" class="text-white"></i>
                </div>
                <div class="stats-value">{{ number_format($collectionStats->total_interest_collected ?? 0, 2) }}</div>
                <div class="stats-label">{{ __('messages.total_interest_earned') }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl">
            <div class="stats-card bg-grad-collections shadow-sm">
                <div class="stats-icon">
                    <i data-lucide="banknote" class="text-white"></i>
                </div>
                <div class="stats-value">{{ number_format($collectionStats->total_collection ?? 0, 2) }}</div>
                <div class="stats-label">{{ __('messages.total_collection') }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl">
            <div class="stats-card bg-grad-due shadow-sm">
                <div class="stats-icon">
                    <i data-lucide="alert-circle" class="text-white"></i>
                </div>
                <div class="stats-value">{{ number_format($statistics->total_due_amount ?? 0, 2) }}</div>
                <div class="stats-label">{{ __('messages.total_due_loan') }}</div>
            </div>
        </div>
        
    </div>

    <div class="card premium-card">
        <div class="premium-header d-flex justify-content-between align-items-center">
            <h5 class="premium-title">
                <i data-lucide="banknote" class="icon-sm me-2"></i> {{ __('messages.loan_accounts_list') }} <span class="badge bg-light text-primary ms-2 fs-6">{{ $totalLoanAccounts }}</span>
            </h5>
            @role('Admin')
                <a href="{{ route('loan.new') }}" class="btn btn-light text-danger shadow-sm fw-bold">
                    <i data-lucide="plus-circle" class="icon-sm me-1"></i> {{ __('messages.new_loan_application') }}
                </a>
            @endrole
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="min-height: 400px;">
                <table class="table table-premium mb-0">
                    <thead>
                    <tr>
                        <th class="ps-4">{{ __('messages.account_no') }}</th>
                        <th>{{ __('messages.member') }}</th>
                        <th>{{ __('messages.area') }}</th>
                        <th class="text-end">{{ __('messages.loan_amount') }}</th>
                        <th class="text-end">{{ __('messages.paid') }}</th>
                        <th class="text-end">{{ __('messages.grace') }}</th>
                        <th class="text-end">{{ __('messages.due') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th>{{ __('messages.disbursement_date') }}</th>
                        <th class="pe-4 text-end">{{ __('messages.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($loanAccounts as $account)
                        <tr>
                            <td class="ps-4 fw-bold text-danger">
                                <a href="{{ route('loan_accounts.show', $account->id) }}" class="text-decoration-none text-danger">{{ $account->account_no ?? $account->member?->account_no ?? __('messages.not_available') }}</a>
                            </td>
                            <td><div class="fw-medium text-dark">{{ $account->member?->name ?? __('messages.not_available') }}</div></td>
                            <td><span class="badge bg-light text-muted border">{{ $account->member?->area?->name ?? __('messages.not_available') }}</span></td>
                            <td class="text-end fw-bold">{{ number_format($account->loan_amount, 2) }}</td>
                            <td class="text-end text-success fw-medium">{{ number_format($account->total_paid, 2) }}</td>
                            <td class="text-end text-primary fw-medium">{{ number_format($account->grace_amount, 2) }}</td>
                            <td class="text-end text-danger fw-bold">{{ number_format($account->loan_due_amount, 2) }}</td>
                            <td>
                                @php
                                    $statusClass = 'danger';
                                    if ($account->status == 'running') $statusClass = 'warning';
                                    elseif ($account->status == 'paid') $statusClass = 'success';
                                @endphp
                                <span class="badge bg-soft-{{ $statusClass }} text-{{ $statusClass }} rounded-pill">
                                    {{ __( 'messages.' . strtolower($account->status) ) }}
                                </span>
                            </td>
                            <td class="text-muted">{{ $account->disbursement_date ? $account->disbursement_date->format('d M, Y') : __('messages.not_available') }}</td>
                            <td class="pe-4 text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        {{ __('messages.actions') }}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('loan_accounts.show', $account->id) }}">
                                                <i data-lucide="eye" class="icon-xs me-2 text-primary"></i> {{ __('messages.view_details') }}
                                            </a>
                                        </li>
                                        @role('Admin')
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('loan-accounts.edit', $account->id) }}">
                                                <i data-lucide="edit" class="icon-xs me-2 text-info"></i> {{ __('messages.edit_account') }}
                                            </a>
                                        </li>
                                        @if($account->status == 'running')
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('loan_accounts.show', $account->id) }}">
                                                    <i data-lucide="check-circle" class="icon-xs me-2 text-success"></i> {{ __('messages.pay_off') }}
                                                </a>
                                            </li>
                                        @endif
                                        <li><hr class="dropdown-divider my-1"></li>
                                        <li>
                                            <form id="delete-loan-account-{{ $account->id }}" action="{{ route('loan-accounts.destroy', $account->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="dropdown-item d-flex align-items-center py-2 text-danger" onclick="showDeleteConfirm('delete-loan-account-{{ $account->id }}')">
                                                    <i data-lucide="trash-2" class="icon-xs me-2"></i> {{ __('messages.delete_account') }}
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
                            <td colspan="10" class="text-center py-5 text-muted">
                                <div class="d-flex flex-column align-items-center">
                                    <i data-lucide="inbox" class="icon-lg text-light mb-2"></i>
                                    <span>{{ __('messages.no_loan_accounts_found') }}</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
             <div class="px-4 py-3 border-top bg-light-soft rounded-bottom-4">
                {{ $loanAccounts->appends(request()->query())->links() }}
            </div>
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
        });
        
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
@endpush
