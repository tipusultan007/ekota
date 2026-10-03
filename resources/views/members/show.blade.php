@extends('layout.master')

@push('plugin-styles')
    <style>
        .premium-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            background: #fff;
            margin-bottom: 2rem;
        }

        .premium-header {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 50%, #d946ef 100%);
            padding: 2.5rem 2rem;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .premium-header::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at top right, rgba(255,255,255,0.15) 0%, transparent 60%);
            pointer-events: none;
        }

        .header-content {
            position: relative;
            z-index: 1;
        }

        .member-avatar-large {
            width: 120px;
            height: 120px;
            border-radius: 25px;
            border: 5px solid rgba(255, 255, 255, 0.3);
            object-fit: cover;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            background: #fff;
        }

        .status-badge-premium {
            padding: 6px 16px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .nav-tabs-premium {
            border: none;
            background: #f8fafc;
            padding: 8px;
            border-radius: 15px;
            margin-bottom: 2rem;
            display: inline-flex;
        }

        .nav-tabs-premium .nav-link {
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
            color: #64748b;
            transition: all 0.3s;
        }

        .nav-tabs-premium .nav-link.active {
            background: #fff;
            color: #4f46e5;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .nav-tabs-premium .nav-link:hover:not(.active) {
            background: rgba(255, 255, 255, 0.5);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .info-item {
            padding: 1rem;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .info-label {
            font-size: 0.75rem;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-weight: 600;
            color: #1e293b;
            font-size: 1rem;
        }

        /* Table Premium - Bordered Version */
        .table-premium {
            border-collapse: collapse !important;
            width: 100%;
        }

        .table-premium thead th {
            border: 1px solid #e2e8f0 !important;
            text-transform: uppercase;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: #64748b;
            padding: 1rem;
            background-color: #f8fafc;
        }

        .table-premium tbody tr {
            background: #fff;
            transition: all 0.2s ease;
        }

        .table-premium tbody tr:hover {
            background-color: #f8fafc;
        }

        .table-premium tbody td {
            border: 1px solid #e2e8f0 !important;
            padding: 0.85rem 1rem;
            vertical-align: middle;
            color: #1e293b;
        }

        /* Premium Action Buttons */
        .btn-premium-action {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.65rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            text-decoration: none;
        }

        .btn-premium-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .btn-premium-action i {
            width: 16px;
            height: 16px;
        }

        .bg-soft-primary { background: rgba(99, 102, 241, 0.1); color: #6366f1 !important; }
        .bg-soft-success { background: rgba(34, 197, 94, 0.1); color: #22c55e !important; }
        .bg-soft-warning { background: rgba(245, 158, 11, 0.1); color: #f59e0b !important; }
        .bg-soft-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444 !important; }
        .bg-soft-info { background: rgba(6, 182, 212, 0.1); color: #06b6d4 !important; }
        
        /* Restricted date inputs */
        .flatpickr-input[readonly], .flatpickr-input[readonly] + input {
            background-color: #f1f5f9 !important;
            cursor: not-allowed !important;
            opacity: 0.8;
        }

        /* Select2 Premium Styling */
        .select2-container--default .select2-selection--single {
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            height: 38px;
            padding-top: 5px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab persistence
            var hash = window.location.hash;
            if (hash) {
                var tabEl = document.querySelector('a[href="' + hash + '"]');
                if (tabEl) {
                    var tab = new bootstrap.Tab(tabEl);
                    tab.show();
                }
            }

            // Update hash on tab click
            var tabLinks = document.querySelectorAll('.nav-tabs-premium .nav-link');
            tabLinks.forEach(function(link) {
                link.addEventListener('shown.bs.tab', function(e) {
                    window.location.hash = e.target.hash;
                });
            });
        });
    </script>


@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="row">
        <div class="col-md-12">
            <div class="premium-card">
                <div class="premium-header">
                    <div class="header-content d-flex flex-column flex-md-row align-items-center">
                        <img src="{{ $member->getFirstMediaUrl('member_photo') ?: 'https://placehold.co/200x200' }}" class="member-avatar-large mb-3 mb-md-0 me-md-4">
                        <div class="text-center text-md-start flex-grow-1">
                            <div class="d-flex align-items-center justify-content-center justify-content-md-start mb-2">
                                <h2 class="fw-bold mb-0 me-3 text-shadow">{{ $member->name }}</h2>
                                @php
                                    $statusClass = 'bg-white text-danger';
                                    if ($member->status == 'active') $statusClass = 'bg-white text-success';
                                    elseif ($member->status == 'inactive') $statusClass = 'bg-white text-secondary';
                                @endphp
                                <span class="status-badge-premium {{ $statusClass }}">
                                    {{ __('messages.' . strtolower($member->status)) }}
                                </span>
                            </div>
                                    <div class="info-grid mt-3" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));">
                                <div class="text-white opacity-90">
                                    <label class="small text-uppercase fw-bold opacity-75 d-block">{{ __('messages.account_no') }}</label>
                                    <span class="fs-5 fw-bold">{{ $member->account_no }}</span>
                                </div>
                                <div class="text-white opacity-90">
                                    <label class="small text-uppercase fw-bold opacity-75 d-block">{{ __('messages.mobile_no') }}</label>
                                    <span class="fs-5 fw-bold">{{ $member->mobile_no }}</span>
                                </div>
                                <div class="text-white opacity-90">
                                    <label class="small text-uppercase fw-bold opacity-75 d-block">{{ __('messages.area') }}</label>
                                    <span class="fs-5 fw-bold">{{ $member->area->name }}</span>
                                </div>
                                <div class="text-white opacity-90">
                                    <label class="small text-uppercase fw-bold opacity-75 d-block">{{ __('messages.joining_date') }}</label>
                                    <span class="fs-5 fw-bold">{{ $member->joining_date->format('d M, Y') }}</span>
                                </div>
                            </div>

                            @php
                                $savingsAccount = $member->savingsAccounts->first();
                                $totalDeposit = $member->collections()->sum('deposit');
                                $totalWithdraw = $member->collections()->sum('withdraw') + ($member->withdrawals()->sum('withdrawal_amount') ?? 0); 
                                // Note: We sum both new 'withdraw' column and old 'withdrawals' table for backward compatibility if needed, or just one if we fully migrated. User said "can use single model", implies future. We should display total.
                                $currentBalance = $savingsAccount ? $savingsAccount->current_balance : 0;
                            @endphp
                            
                            <div class="row mt-4 g-3">
                                <div class="col-md-4">
                                    <div class="p-3 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-20 text-white">
                                        <div class="small text-uppercase fw-bold opacity-75 mb-1">{{ __('messages.total_deposit') ?? 'Total Deposit' }}</div>
                                        <div class="fs-3 fw-bold">{{ number_format($totalDeposit, 2) }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-20 text-white">
                                        <div class="small text-uppercase fw-bold opacity-75 mb-1">{{ __('messages.total_withdraw') ?? 'Total Withdraw' }}</div>
                                        <div class="fs-3 fw-bold">{{ number_format($totalWithdraw, 2) }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 rounded-3 bg-white text-primary shadow-sm border-0">
                                        <div class="small text-uppercase fw-bold opacity-75 mb-1">{{ __('messages.current_balance') ?? 'Current Balance' }}</div>
                                        <div class="fs-3 fw-bold">{{ number_format($currentBalance, 2) }}</div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="mt-4 mt-md-0 d-flex flex-column">
                            <a href="{{ route('members.index') }}" class="btn btn-outline-light btn-sm fw-bold mb-2">
                                <i data-lucide="arrow-left" class="icon-xs me-1"></i> {{ __('messages.back_to_list') }}
                            </a>
                            @role('Admin')
                            <a href="{{ route('members.edit', $member->id) }}" class="btn btn-white btn-sm text-primary fw-bold shadow-sm">
                                <i data-lucide="edit" class="icon-xs me-1"></i> {{ __('messages.edit_profile') }}
                            </a>
                            @endrole
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="d-flex justify-content-center">
                                <ul class="nav nav-tabs-premium" id="memberTab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="savings-tab" data-bs-toggle="tab" href="#savings" role="tab"><i data-lucide="piggy-bank" class="icon-sm me-1"></i> {{ __('messages.savings_accounts') }}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="loans-tab" data-bs-toggle="tab" href="#loans" role="tab"><i data-lucide="banknote" class="icon-sm me-1"></i> {{ __('messages.loan_acounts') }}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="profile-details-tab" data-bs-toggle="tab" href="#profile-details" role="tab"><i data-lucide="user" class="icon-sm me-1"></i> {{ __('messages.profile') }}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="reports-tab" data-bs-toggle="tab" data-bs-target="#account-reports" href="javascript:void(0);" role="tab"><i data-lucide="file-text" class="icon-sm me-1"></i> {{ __('messages.account_statement') }}</a>
                                    </li>
                                </ul>
                            </div>

                            <div class="tab-content mt-4" id="memberTabContent">
                                {{-- Savings Tab --}}
                                <div class="tab-pane fade show active" id="savings" role="tabpanel">
                                    <div class="row g-4">
                                        <div class="col-lg-5">
                                            <div class="card bg-white border-0 shadow-sm rounded-4 mb-4">
                                                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                                                    <h6 class="fw-bold text-primary text-uppercase small letter-spacing-wide mb-0">
                                                        <i data-lucide="plus-circle" class="icon-sm me-2"></i>{{ __('messages.new_savings_collection') ?? 'New Savings Deposit' }}
                                                    </h6>
                                                </div>
                                                <div class="card-body p-4">
                                                    <form action="{{ route('savings-collections.store') }}" method="POST">
                                                        @csrf
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold text-muted text-uppercase">{{ __('messages.savings_account') }} <span class="text-danger">*</span></label>
                                                            <select name="savings_account_id" class="form-select" required>
                                                                @foreach ($member->savingsAccounts as $account)
                                                                    <option value="{{ $account->id }}" {{ $account->status == 'active' ? 'selected' : '' }}>
                                                                        {{ $account->account_no }} ({{ $account->scheme_type }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="row g-3 mb-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-bold text-muted text-uppercase">{{ __('messages.date') }}</label>
                                                                <input type="text" name="collection_date" class="form-control flatpickr" value="{{ date('Y-m-d') }}" required @role('Field Worker') readonly @endrole>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-bold text-muted text-uppercase">{{ __('messages.deposit_to') }} <span class="text-danger">*</span></label>
                                                                <select name="account_id" class="form-select" required>
                                                                    @foreach ($accounts as $account)
                                                                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="row g-3 mb-3">
                                                            <div class="col-md-4">
                                                                <label class="form-label small fw-bold text-muted text-uppercase">{{ __('messages.deposit') ?? 'Deposit' }}</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text bg-light border-0 text-muted small fw-bold"><i data-lucide="plus" class="icon-xs"></i></span>
                                                                    <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label small fw-bold text-muted text-uppercase">{{ __('messages.withdraw') ?? 'Withdraw' }}</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text bg-light border-0 text-muted small fw-bold"><i data-lucide="minus" class="icon-xs text-danger"></i></span>
                                                                    <input type="number" step="0.01" name="withdraw_amount" class="form-control" placeholder="0.00">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label small fw-bold text-muted text-uppercase">{{ __('messages.interest') ?? 'Interest' }}</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text bg-light border-0 text-muted small fw-bold"><i data-lucide="trending-up" class="icon-xs text-success"></i></span>
                                                                    <input type="number" step="0.01" name="interest_amount" class="form-control" placeholder="0.00">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="mb-4">
                                                            <label class="form-label small fw-bold text-muted text-uppercase">{{ __('messages.collector') }} <span class="text-danger">*</span></label>
                                                            @role('Admin')
                                                                <select name="collector_id" class="form-select select2-collector" required>
                                                                    @foreach ($collectors as $collector)
                                                                        <option value="{{ $collector->id }}" {{ $collector->id == Auth::id() ? 'selected' : '' }}>{{ $collector->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            @else
                                                                <input type="hidden" name="collector_id" value="{{ Auth::id() }}">
                                                                <div class="form-control bg-light border-0 text-muted">
                                                                    {{ Auth::user()->name }}
                                                                </div>
                                                            @endrole
                                                        </div>
                                                        <div class="mb-4">
                                                            <input name="notes" class="form-control" placeholder="{{ __('messages.enter_notes') ?? 'Optional notes...' }}">
                                                        </div>
                                                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-sm rounded-3">
                                                            <i data-lucide="check-circle" class="icon-sm me-2"></i> {{ __('messages.submit_deposit') ?? 'Deposit' }}
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-7">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h5 class="fw-bold mb-0 text-muted small text-uppercase letter-spacing-wide">{{ __('messages.all_savings_account') }}</h5>
                                            </div>
                                            <div class="table-responsive rounded-3 border border-light bg-white mb-4">
                                                <table class="table table-premium mb-0">
                                                    <thead class="bg-light-soft">
                                                        <tr>
                                                            <th class="">{{ __('messages.account_no') }}</th>
                                                            <th>{{ __('messages.scheme') }}</th>
                                                            <th>{{ __('messages.balance') }}</th>
                                                            @role('Admin') <th class="text-center">{{ __('messages.actions') }}</th> @endrole
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    @forelse ($member->savingsAccounts as $account)
                                                        <tr>
                                                            <td class="">
                                                                <a href="{{ route('savings_accounts.show', $account->id) }}" class="fw-bold text-primary text-decoration-none">
                                                                    {{ $account->account_no }}
                                                                </a>
                                                            </td>
                                                            <td><span class="badge bg-soft-info">{{ __("messages.{$account->scheme_type}") }}</span></td>
                                                            <td><span class="fw-bold fs-6">{{ number_format($account->current_balance, 2) }}</span></td>
                                                            @role('Admin')
                                                            <td class="text-center">
                                                                @if ($account->current_balance > 0)
                                                                    <button type="button" class="btn btn-soft-danger btn-xs" data-bs-toggle="modal" data-bs-target="#withdrawModal" data-account-id="{{ $account->id }}" data-account-no="{{ $account->account_no }}" data-current-balance="{{ $account->current_balance }}">
                                                                        {{ __('messages.withdraw') }}
                                                                    </button>
                                                                @else
                                                                    <span class="text-muted small italic">{{ __('messages.no_actions') }}</span>
                                                                @endif
                                                            </td>
                                                            @endrole
                                                        </tr>
                                                    @empty
                                                        <tr><td colspan="4" class="text-center py-4 text-muted">{{ __('messages.no_savings_accounts_found') }}</td></tr>
                                                    @endforelse
                                                    </tbody>
                                                </table>
                                            </div>

                                            <h5 class="fw-bold mb-3 text-muted small text-uppercase letter-spacing-wide">{{ __('messages.recent_savings_collections') ?? 'Recent Savings Collections' }}</h5>
                                            <div class="table-responsive rounded-3 border border-light bg-white">
                                                <table class="table table-premium mb-0">
                                                    <thead class="bg-light-soft">
                                                        <tr>
                                                            <th class="">{{ __('messages.date') }}</th>
                                                            <th>{{ __('messages.account_no') }}</th>
                                                            <th>{{ __('messages.deposit') ?? 'Deposit' }}</th>
                                                            <th>{{ __('messages.withdraw') ?? 'Withdraw' }}</th>
                                                            <th>{{ __('messages.interest') ?? 'Interest' }}</th>
                                                            <th class="text-center">{{ __('messages.actions') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($recentSavings as $collection)
                                                        <tr>
                                                            <td class="">{{ $collection->collection_date->format('d M, Y') }}</td>
                                                            <td><span class="fw-bold">{{ $collection->savingsAccount->account_no }}</span></td>
                                                            <td class="fw-bold {{ $collection->collector_id == 7 ? 'text-danger' : 'text-success' }}">{{ $collection->amount > 0 ? '+'.number_format($collection->amount, 2) : '-' }}</td>
                                                            <td class="fw-bold text-danger">{{ $collection->withdraw_amount > 0 ? '-'.number_format($collection->withdraw_amount, 2) : '-' }}</td>
                                                            <td class="fw-bold text-info">{{ $collection->interest_amount > 0 ? '+'.number_format($collection->interest_amount, 2) : '-' }}</td>
                                                            <td class="text-center">
                                                                <div class="d-flex justify-content-center gap-1">
                                                                    <a href="{{ route('savings-collections.edit', $collection->id) }}" class="btn btn-premium-action bg-soft-primary" title="Edit">
                                                                        <i data-lucide="edit-3"></i>
                                                                    </a>
                                                                    <form action="{{ route('savings-collections.destroy', $collection->id) }}" method="POST" class="d-inline" id="delete-savings-{{ $collection->id }}">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="button" class="btn btn-premium-action bg-soft-danger border-0" title="Delete" onclick="showDeleteConfirm('delete-savings-{{ $collection->id }}')">
                                                                            <i data-lucide="trash-2"></i>
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        @empty
                                                        <tr><td colspan="6" class="text-center py-4 text-muted">{{ __('messages.no_data_found') }}</td></tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="mt-3 d-flex justify-content-center">
                                                {{ $recentSavings->appends(['installments_page' => request('installments_page')])->fragment('savings')->links() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Loans Tab --}}
                                <div class="tab-pane fade" id="loans" role="tabpanel">
                                    <div class="row g-4">
                                        <div class="col-lg-5">
                                            <div class="card bg-white border-0 shadow-sm rounded-4 mb-4">
                                                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                                                    <h6 class="fw-bold text-danger text-uppercase small letter-spacing-wide mb-0">
                                                        <i data-lucide="plus-circle" class="icon-sm me-2"></i>{{ __('messages.new_loan_installment') ?? 'New Loan Installment' }}
                                                    </h6>
                                                </div>
                                                <div class="card-body p-4">
                                                    <form action="{{ route('loan-installments.store') }}" method="POST">
                                                        @csrf
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold text-muted text-uppercase">{{ __('messages.loan_account') }} <span class="text-danger">*</span></label>
                                                            <select name="loan_account_id" class="form-select" required>
                                                                @foreach ($member->loanAccounts as $account)
                                                                    @if($account->status == 'running')
                                                                    <option value="{{ $account->id }}">
                                                                        {{ $account->account_no }} (Due: {{ number_format($account->total_payable - $account->total_paid, 2) }})
                                                                    </option>
                                                                    @endif
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="row g-3 mb-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-bold text-muted text-uppercase">{{ __('messages.date') }}</label>
                                                                <input type="text" name="payment_date" class="form-control flatpickr" value="{{ date('Y-m-d') }}" required @role('Field Worker') readonly @endrole>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-bold text-muted text-uppercase">{{ __('messages.deposit_to') }} <span class="text-danger">*</span></label>
                                                                <select name="account_id" class="form-select" required>
                                                                    @foreach ($accounts as $account)
                                                                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="row g-2 mb-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-bold text-muted text-uppercase">{{ __('messages.amount') }} <span class="text-danger">*</span></label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text bg-light border-end-0 text-muted small fw-bold">{{ __('messages.bdt') }}</span>
                                                                    <input type="number" step="0.01" name="paid_amount" class="form-control border-start-0" placeholder="0.00" required>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-bold text-muted text-uppercase">{{ __('messages.grace') ?? 'Grace' }}</label>
                                                                <div class="input-group">
                                                                    <input type="number" step="0.01" name="grace_amount" class="form-control" placeholder="0.00">
                                                                </div>
                                                        </div>
                                                        <div class="mb-4">
                                                            <label class="form-label small fw-bold text-muted text-uppercase">{{ __('messages.collector') }} <span class="text-danger">*</span></label>
                                                            @role('Admin')
                                                                <select name="collector_id" class="form-select select2-collector" required>
                                                                    @foreach ($collectors as $collector)
                                                                        <option value="{{ $collector->id }}" {{ $collector->id == Auth::id() ? 'selected' : '' }}>{{ $collector->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            @else
                                                                <input type="hidden" name="collector_id" value="{{ Auth::id() }}">
                                                                <div class="form-control bg-light border-0 text-muted">
                                                                    {{ Auth::user()->name }}
                                                                </div>
                                                            @endrole
                                                        </div>
                                                        <div class="mb-4">
                                                            <input name="notes" class="form-control" placeholder="{{ __('messages.enter_notes') ?? 'Optional notes...' }}">
                                                        </div>
                                                        <button type="submit" class="btn btn-danger w-100 fw-bold py-2 shadow-sm rounded-3">
                                                            <i data-lucide="check-circle" class="icon-sm me-2"></i> {{ __('messages.submit_installment') ?? 'Submit Installment' }}
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        </div>

                                        <div class="col-lg-7">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h5 class="fw-bold mb-0 text-muted small text-uppercase letter-spacing-wide">{{ __('messages.all_loan_accounts') }}</h5>
                                                @role('Admin')
                                                <a href="{{ route('members.loan-accounts.create', $member->id) }}" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                                                    <i data-lucide="plus" class="icon-xs me-1"></i> {{ __('messages.issue_new_loan') }}
                                                </a>
                                                @endrole
                                            </div>
                                            <div class="table-responsive rounded-3 border border-light bg-white mb-4">
                                                <table class="table table-premium mb-0">
                                                    <thead class="bg-light-soft">
                                                        <tr>
                                                            <th class="">{{ __('messages.account_no') }}</th>
                                                            <th>{{ __('messages.loan') }}</th>
                                                            <th>{{ __('messages.due') }}</th>
                                                            <th>{{ __('messages.status') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    @forelse ($member->loanAccounts as $account)
                                                        <tr>
                                                            <td class="">
                                                                <a href="{{ route('loan-accounts.show', $account->id) }}" class="fw-bold text-primary text-decoration-none">
                                                                    {{ $account->account_no }}
                                                                </a>
                                                            </td>
                                                            <td>{{ number_format($account->loan_amount, 2) }}</td>
                                                            <td><span class="text-danger fw-bold">{{ number_format($account->total_payable - $account->total_paid, 2) }}</span></td>
                                                            <td>
                                                                <span class="badge bg-soft-{{ $account->status == 'running' ? 'warning' : 'success' }}">
                                                                    {{ __("messages.{$account->status}") }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr><td colspan="4" class="text-center py-4 text-muted">{{ __('messages.no_loan_accounts_found') }}</td></tr>
                                                    @endforelse
                                                    </tbody>
                                                </table>
                                            </div>

                                            <h5 class="fw-bold mb-3 text-muted small text-uppercase letter-spacing-wide">{{ __('messages.recent_loan_installments') ?? 'Recent Loan Installments' }}</h5>
                                            <div class="table-responsive rounded-3 border border-light bg-white">
                                                <table class="table table-premium mb-0">
                                                    <thead class="bg-light-soft">
                                                        <tr>
                                                            <th class="">{{ __('messages.date') }}</th>
                                                            <th>{{ __('messages.account_no') }}</th>
                                                            <th>{{ __('messages.paid') }}</th>
                                                            <th class="text-end ">{{ __('messages.actions') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($recentInstallments as $installment)
                                                        <tr>
                                                            <td class="">{{ $installment->payment_date->format('d M, Y') }}</td>
                                                            <td><span class="fw-bold">{{ $installment->loanAccount->account_no }}</span></td>
                                                            <td class="fw-bold {{ $installment->collector_id == 7 ? 'text-danger' : 'text-success' }}">{{ number_format($installment->paid_amount, 2) }}</td>
                                                            <td class="text-center">
                                                                <div class="d-flex justify-content-center gap-1">
                                                                    <a href="{{ route('loan-installments.edit', $installment->id) }}" class="btn btn-premium-action bg-soft-primary" title="Edit">
                                                                        <i data-lucide="edit-3"></i>
                                                                    </a>
                                                                    <form action="{{ route('loan-installments.destroy', $installment->id) }}" method="POST" class="d-inline" id="delete-loan-{{ $installment->id }}">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="button" class="btn btn-premium-action bg-soft-danger border-0" title="Delete" onclick="showDeleteConfirm('delete-loan-{{ $installment->id }}')">
                                                                            <i data-lucide="trash-2"></i>
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        @empty
                                                        <tr><td colspan="4" class="text-center py-4 text-muted">{{ __('messages.no_data_found') }}</td></tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="mt-3 d-flex justify-content-center">
                                                {{ $recentInstallments->appends(['savings_page' => request('savings_page')])->fragment('loans')->links() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Profile Details Tab --}}
                                <div class="tab-pane fade" id="profile-details" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="info-item mb-4 text-center">
                                                <div class="info-label">{{ __('messages.members_signature') }}</div>
                                                <div class="mt-2">
                                                    @if($member->getFirstMediaUrl('member_signature'))
                                                        <img src="{{ $member->getFirstMediaUrl('member_signature') }}" alt="Signature" class="img-fluid rounded border p-2 bg-white" style="max-height: 120px;">
                                                    @else
                                                        <div class="py-4 text-muted italic">{{ __('messages.signature_not_uploaded') }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="info-item mb-3">
                                                <div class="info-label">{{ __('messages.status') }}</div>
                                                <div class="info-value mt-1">
                                                    <span class="badge bg-soft-{{ $member->status == 'active' ? 'success' : 'danger' }} fs-6">
                                                        {{ __("messages.{$member->status}") }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="card border-0 bg-light rounded-4">
                                                <div class="card-body">
                                                    <h6 class="fw-bold text-primary text-uppercase small mb-3 letter-spacing-wide">{{ __('messages.personal_info') }}</h6>
                                                    <div class="row g-3">
                                                        <div class="col-6">
                                                            <div class="info-label">{{ __('messages.father_name') }}</div>
                                                            <div class="info-value">{{ $member->father_name }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="info-label">{{ __('messages.mother_name') }}</div>
                                                            <div class="info-value">{{ $member->mother_name }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="info-label">{{ __('messages.spouse_name') }}</div>
                                                            <div class="info-value">{{ $member->spouse_name ?? 'N/A' }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="info-label">{{ __('messages.date_of_birth') }}</div>
                                                            <div class="info-value">{{ $member->date_of_birth ? $member->date_of_birth->format('d M, Y') : 'N/A' }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="info-label">{{ __('messages.gender') }}</div>
                                                            <div class="info-value">{{ $member->gender ? __("messages.{$member->gender}") : 'N/A' }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="info-label">{{ __('messages.blood_group') }}</div>
                                                            <div class="info-value">{{ $member->blood_group ?? 'N/A' }}</div>
                                                        </div>
                                                    </div>

                                                    <h6 class="fw-bold text-primary text-uppercase small mt-4 mb-3 letter-spacing-wide">{{ __('messages.contact_info') }}</h6>
                                                    <div class="row g-3">
                                                        <div class="col-12">
                                                            <div class="info-label">{{ __('messages.present_address') }}</div>
                                                            <div class="info-value">{{ $member->present_address }}</div>
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="info-label">{{ __('messages.permanent_address') }}</div>
                                                            <div class="info-value">{{ $member->permanent_address ?? 'N/A' }}</div>
                                                        </div>
                                                    </div>

                                                    <h6 class="fw-bold text-primary text-uppercase small mt-4 mb-3 letter-spacing-wide">{{ __('messages.additional_info') }}</h6>
                                                    <div class="row g-3">
                                                        <div class="col-6">
                                                            <div class="info-label">{{ __('messages.nid_number') }}</div>
                                                            <div class="info-value">{{ $member->nid_no ?? 'N/A' }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="info-label">{{ __('messages.occupation') }}</div>
                                                            <div class="info-value">{{ $member->occupation ?? 'N/A' }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Reports Tab --}}
                                <div class="tab-pane fade" id="account-reports" role="tabpanel" aria-labelledby="reports-tab">
                                    <div class="row justify-content-center">
                                        <div class="col-md-10">
                                            <div class="info-item p-4">
                                                <h5 class="fw-bold mb-4 text-center text-primary">{{ __('messages.account_statement') }}</h5>
                                                <hr>
                                                <form id="statementForm" action="{{ route('reports.member_statement', $member->id) }}" method="GET" target="_blank">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold small text-muted text-uppercase">{{ __('messages.start_date') }}</label>
                                                            <input type="text" name="start_date" class="form-control flatpickr" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold small text-muted text-uppercase">{{ __('messages.end_date') }}</label>
                                                            <input type="text" name="end_date" class="form-control flatpickr" value="{{ date('Y-m-d') }}" required>
                                                        </div>
                                                        <div class="col-12 mt-4">
                                                            <button type="submit" class="btn btn-primary w-100 fw-bold py-3 shadow-sm rounded-3">
                                                                <i data-lucide="file-text" class="icon-sm me-2"></i> {{ __('messages.generate_pdf') }}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Withdrawal Modal --}}
    @role('Admin')
    <div class="modal fade" id="withdrawModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-danger text-white rounded-top-4">
                    <h5 class="modal-title fw-bold">{{ __('messages.process_final_withdrawal') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="withdrawForm" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-soft-danger p-3 rounded-circle me-3">
                                <i data-lucide="alert-triangle" class="text-danger" style="width: 32px; height: 32px;"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold">{{ __('messages.account_no') }}: <span id="modalAccountNo" class="text-danger"></span></h6>
                                <p class="mb-0 text-muted">{{ __('messages.current_balance') }}: <strong id="modalCurrentBalance"></strong> {{ __('messages.bdt') }}</p>
                            </div>
                        </div>

                        <div class="alert alert-soft-info border-0 mb-4 small fw-medium">
                            {{ __('messages.withdrawal_alert_message') }}
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase tracking-wider">{{ __('messages.add_profit_amount') }}</label>
                            <input type="number" step="0.01" name="profit_amount" class="form-control shadow-sm" placeholder="0.00" id="profit_amount_input">
                        </div>

                        <div class="bg-light p-3 rounded-3 mb-4 text-center">
                            <label class="small text-uppercase fw-bold text-muted d-block mb-1">{{ __('messages.total_amount_to_pay') }}</label>
                            <span class="fs-4 fw-bold text-success"><span id="total_payable_display"></span> {{ __('messages.bdt') }}</span>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase tracking-wider">{{ __('messages.withdrawal_date') }}</label>
                                <input type="text" name="withdrawal_date" class="form-control flatpickr shadow-sm" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase tracking-wider">{{ __('messages.payment_from_account') }}</label>
                                <select name="account_id" class="form-select shadow-sm" required>
                                    <option value="">{{ __('messages.select_account') }}</option>
                                    @foreach (\App\Models\Account::where('is_active', true)->where('is_payment_account', true)->get() as $paymentAccount)
                                        <option value="{{ $paymentAccount->id }}">{{ $paymentAccount->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label fw-bold small text-muted text-uppercase tracking-wider">{{ __('messages.notes') }}</label>
                            <textarea name="notes" class="form-control shadow-sm" rows="2" placeholder="Enter notes here..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">{{ __('messages.close') }}</button>
                        <button type="submit" class="btn btn-danger fw-bold px-4 shadow">{{ __('messages.process_final_withdrawal') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endrole
@endsection

@push('custom-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Initialize Flatpickr
            if (typeof flatpickr !== 'undefined') {
                flatpickr(".flatpickr:not([readonly])", {
                    altInput: true,
                    dateFormat: "Y-m-d",
                    altFormat: "d M, Y",
                    allowInput: true
                });

                // For readonly date fields (Field Workers), just show the formatted date without picker
                flatpickr(".flatpickr[readonly]", {
                    altInput: true,
                    dateFormat: "Y-m-d",
                    altFormat: "d M, Y",
                    clickOpens: false,
                    allowInput: false
                });
            }

            var withdrawModal = document.getElementById('withdrawModal');
            if (withdrawModal) {
                withdrawModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    var accountId = button.getAttribute('data-account-id');
                    var accountNo = button.getAttribute('data-account-no');
                    var currentBalance = parseFloat(button.getAttribute('data-current-balance'));

                    var modalAccountNo = withdrawModal.querySelector('#modalAccountNo');
                    var modalCurrentBalance = withdrawModal.querySelector('#modalCurrentBalance');
                    var withdrawForm = withdrawModal.querySelector('#withdrawForm');
                    var profitInput = withdrawModal.querySelector('#profit_amount_input');
                    var totalPayableDisplay = withdrawModal.querySelector('#total_payable_display');

                    profitInput.value = '';
                    modalAccountNo.textContent = accountNo;
                    modalCurrentBalance.textContent = currentBalance.toLocaleString(undefined, {minimumFractionDigits: 2});
                    totalPayableDisplay.textContent = currentBalance.toLocaleString(undefined, {minimumFractionDigits: 2});

                    var url = "{{ url('savings-accounts') }}/" + accountId + "/withdraw";
                    withdrawForm.setAttribute('action', url);

                    profitInput.addEventListener('input', function() {
                        var profit = parseFloat(this.value) || 0;
                        var totalPayable = currentBalance + profit;
                        totalPayableDisplay.textContent = totalPayable.toLocaleString(undefined, {minimumFractionDigits: 2});
                    });
                });
            }
        });

        $(document).ready(function() {
            $('.select2-collector').select2({
                width: '100%',
                placeholder: 'Select Collector'
            });
        });
    </script>
@endpush