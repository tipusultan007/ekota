@extends('layout.master')

@push('plugin-styles')
    <link rel="stylesheet" href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}">
    <style>
        .premium-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            background: #fff;
        }

        .premium-header {
            background: linear-gradient(135deg, #059669 0%, #10b981 50%, #34d399 100%);
            padding: 2rem;
            color: white;
            position: relative;
        }

        .header-content {
            position: relative;
            z-index: 1;
        }

        .form-label-premium {
            font-size: 0.75rem;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 0.5rem;
            letter-spacing: 0.025em;
        }

        .input-premium {
            border-radius: 12px;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            transition: all 0.3s;
        }

        .input-premium:focus {
            background: #fff;
            border-color: #10b981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }

        .bg-white-10 {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .border-white-10 {
            border-color: rgba(255, 255, 255, 0.1) !important;
        }
    </style>
@endpush

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="premium-card">
                <div class="premium-header">
                    <div class="header-content">
                        <div class="d-flex align-items-center mb-4">
                            <div class="p-3 bg-white bg-opacity-20 rounded-4 me-3">
                                <i data-lucide="landmark" class="text-white" style="width: 24px; height: 24px;"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-0">{{ __('messages.edit_installment') }}</h4>
                                <p class="mb-0 opacity-75 small">{{ __('messages.update_installment_details') ?? 'Modify the loan repayment record' }}</p>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-4">
                                <div class="bg-white-10 p-3 rounded-3 border border-white-10">
                                    <label class="small text-uppercase fw-bold opacity-75 d-block mb-1">{{ __('messages.member') }}</label>
                                    <span class="fw-bold">{{ $loanInstallment->member->name }}</span>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="bg-white-10 p-3 rounded-3 border border-white-10">
                                    <label class="small text-uppercase fw-bold opacity-75 d-block mb-1">{{ __('messages.loan_account') ?? 'Loan Account' }}</label>
                                    <span class="fw-bold">{{ $loanInstallment->loanAccount->account_no }}</span>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="bg-white-10 p-3 rounded-3 border border-white-10">
                                    <label class="small text-uppercase fw-bold opacity-75 d-block mb-1">{{ __('messages.original_paid_amount') }}</label>
                                    <span class="fw-bold">{{ number_format($loanInstallment->paid_amount, 2) }} BDT</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4 p-lg-5">
                    <form action="{{ route('loan-installments.update', $loanInstallment->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-4 mb-4">
                            <div class="col-md-4">
                                <label class="form-label-premium">{{ __('messages.amount') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light-soft border-0 text-muted small fw-bold"><i data-lucide="banknote" class="icon-xs"></i></span>
                                    <input type="number" step="0.01" name="paid_amount" id="paid_amount"
                                           class="form-control input-premium @error('paid_amount') is-invalid @enderror"
                                           value="{{ old('paid_amount', $loanInstallment->paid_amount) }}" required>
                                </div>
                                @error('paid_amount')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4" id="grace_amount_wrapper">
                                <label class="form-label-premium">{{ __('messages.grace_amount') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light-soft border-0 text-muted small fw-bold"><i data-lucide="gift" class="icon-xs text-info"></i></span>
                                    <input type="number" step="0.01" name="grace_amount" id="grace_amount_input"
                                           class="form-control input-premium" placeholder="0.00"
                                           value="{{ old('grace_amount', $loanInstallment->grace_amount ?? 0) }}">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label-premium">{{ __('messages.payment_date') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light-soft border-0 text-muted small fw-bold"><i data-lucide="calendar" class="icon-xs"></i></span>
                                    <input type="text" name="payment_date" id="payment_date"
                                           class="form-control input-premium flatpickr @error('payment_date') is-invalid @enderror"
                                           value="{{ old('payment_date', $loanInstallment->payment_date->format('Y-m-d')) }}" required>
                                </div>
                                @error('payment_date')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mt-4">
                                <label class="form-label-premium">{{ __('messages.deposit_to_account') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light-soft border-0 text-muted small fw-bold"><i data-lucide="credit-card" class="icon-xs"></i></span>
                                    <select name="account_id" class="form-select input-premium" required>
                                        @foreach ($accounts as $account)
                                            <option value="{{ $account->id }}"
                                                {{ (isset($currentDepositAccount) && $currentDepositAccount->id == $account->id) ? 'selected' : '' }}>
                                                {{ $account->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 mt-4">
                                <label class="form-label-premium">{{ __('messages.collector') ?? 'Collector' }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light-soft border-0 text-muted small fw-bold"><i data-lucide="user" class="icon-xs"></i></span>
                                    <select name="collector_id" class="form-select input-premium" required>
                                        <option value="">{{ __('messages.select_collector') ?? 'Select Collector' }}</option>
                                        @foreach ($collectors as $collector)
                                            <option value="{{ $collector->id }}" {{ old('collector_id', $loanInstallment->collector_id) == $collector->id ? 'selected' : '' }}>
                                                {{ $collector->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <label class="form-label-premium">{{ __('messages.notes') }}</label>
                                <textarea name="notes" id="notes" class="form-control input-premium" rows="3" placeholder="Enter repayment notes...">{{ old('notes', $loanInstallment->notes) }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex gap-3 pt-3">
                            <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 shadow-sm d-flex align-items-center" style="background-color: #059669; border: none;">
                                <i data-lucide="check-circle" class="icon-sm me-2"></i> {{ __('messages.update_installment') }}
                            </button>
                            <a href="{{ route('loan-installments.index') }}" class="btn btn-light px-4 py-2 rounded-3 border">
                                {{ __('messages.cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-scripts')
    <script src="{{ asset('build/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
            
            if (typeof flatpickr !== 'undefined') {
                $(".flatpickr").flatpickr({
                    altInput: true,
                    dateFormat: 'Y-m-d',
                    altFormat: 'd/m/Y'
                });
            }
        });
    </script>
@endpush
