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
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #9333ea 100%);
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
            border-color: #4f46e5;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .bg-soft-secondary {
            background: rgba(100, 116, 139, 0.1);
            color: #475569;
        }
    </style>
@endpush

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="premium-card">
                <div class="premium-header">
                    <div class="header-content">
                        <div class="d-flex align-items-center mb-3">
                            <div class="p-3 bg-white bg-opacity-20 rounded-4 me-3">
                                <i data-lucide="piggy-bank" class="text-white" style="width: 24px; height: 24px;"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-0">{{ __('messages.edit_savings_collection') }}</h4>
                                <p class="mb-0 opacity-75 small">{{ __('messages.update_collection_details') ?? 'Refine the savings collection details' }}</p>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="bg-white bg-opacity-10 p-3 rounded-3 border border-white border-opacity-10">
                                    <label class="small text-uppercase fw-bold opacity-75 d-block mb-1">{{ __('messages.member') }}</label>
                                    <span class="fw-bold">{{ $savingsCollection->member->name }}</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="bg-white bg-opacity-10 p-3 rounded-3 border border-white border-opacity-10">
                                    <label class="small text-uppercase fw-bold opacity-75 d-block mb-1">{{ __('messages.account_no') }}</label>
                                    <span class="fw-bold">{{ $savingsCollection->savingsAccount->account_no }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4 p-lg-5">
                    <form action="{{ route('savings-collections.update', $savingsCollection->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-4 mb-4">
                            <div class="col-md-4">
                                <label class="form-label-premium">{{ __('messages.deposit') ?? 'Deposit' }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light-soft border-0 text-muted small fw-bold"><i data-lucide="plus" class="icon-xs"></i></span>
                                    <input type="number" step="0.01" name="amount" class="form-control input-premium"
                                           value="{{ old('amount', $savingsCollection->amount) }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-premium">{{ __('messages.withdraw') ?? 'Withdraw' }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light-soft border-0 text-muted small fw-bold"><i data-lucide="minus" class="icon-xs text-danger"></i></span>
                                    <input type="number" step="0.01" name="withdraw_amount" class="form-control input-premium"
                                           value="{{ old('withdraw_amount', $savingsCollection->withdraw_amount) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-premium">{{ __('messages.interest') ?? 'Interest' }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light-soft border-0 text-muted small fw-bold"><i data-lucide="trending-up" class="icon-xs text-success"></i></span>
                                    <input type="number" step="0.01" name="interest_amount" class="form-control input-premium"
                                           value="{{ old('interest_amount', $savingsCollection->interest_amount) }}">
                                </div>
                            </div>

                            <div class="col-md-6 mt-4">
                                <label class="form-label-premium">{{ __('messages.collection_date') }} <span class="text-danger">*</span></label>
                                <input type="text" name="collection_date" class="form-control input-premium flatpickr"
                                       value="{{ old('collection_date', $savingsCollection->collection_date->format('Y-m-d')) }}" required>
                            </div>

                            <div class="col-md-6 mt-4">
                                <label class="form-label-premium">{{ __('messages.deposit_to_account') }} <span class="text-danger">*</span></label>
                                <select name="account_id" class="form-select input-premium" required>
                                    <option value="">{{ __('messages.select_account') }}</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}" {{ (isset($currentDepositAccount) && $currentDepositAccount->id == $account->id) ? 'selected' : '' }}>
                                            {{ $account->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mt-4">
                                <label class="form-label-premium">{{ __('messages.collector') ?? 'Collector' }} <span class="text-danger">*</span></label>
                                <select name="collector_id" class="form-select input-premium" required>
                                    <option value="">{{ __('messages.select_collector') ?? 'Select Collector' }}</option>
                                    @foreach ($collectors as $collector)
                                        <option value="{{ $collector->id }}" {{ old('collector_id', $savingsCollection->collector_id) == $collector->id ? 'selected' : '' }}>
                                            {{ $collector->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 mt-4">
                                <label class="form-label-premium">{{ __('messages.notes') }}</label>
                                <textarea name="notes" class="form-control input-premium" rows="3" placeholder="Enter notes here...">{{ old('notes', $savingsCollection->notes) }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex gap-3 pt-3">
                            <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 shadow-sm d-flex align-items-center">
                                <i data-lucide="save" class="icon-sm me-2"></i> {{ __('messages.update_collection') }}
                            </button>
                            <a href="{{ url()->previous() }}" class="btn btn-light px-4 py-2 rounded-3 border">
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
                    altFormat: "d/m/Y",
                    dateFormat: "Y-m-d",
                });
            }
        });
    </script>
@endpush
