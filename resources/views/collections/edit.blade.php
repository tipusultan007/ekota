@extends('layout.master')

@push('plugin-styles')
    <link rel="stylesheet" href="{{ asset('build/plugins/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}">
    <style>
        /* Premium Card Design */
        .card-premium {
            border: none;
            border-radius: 1.25rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            background: #fff;
        }

        /* Gradient Header */
        .premium-gradient-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            padding: 2.5rem 2rem;
            position: relative;
            border: none;
        }

        .premium-gradient-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at top right, rgba(255,255,255,0.2), transparent);
            pointer-events: none;
        }

        /* Form Controls */
        .form-control-premium {
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .form-control-premium:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1);
        }

        .btn-premium {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: none;
            border-radius: 0.75rem;
            padding: 0.8rem 2rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #fff;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
        }

        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
            color: #fff;
        }
    </style>
@endpush

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-premium mb-3 mb-lg-4">
                <div class="premium-gradient-header text-center py-4 py-lg-5">
                    <h3 class="fw-bold text-white mb-1">{{ __('messages.edit_collection') ?? 'Edit Collection' }}</h3>
                    <p class="text-white opacity-75 mb-0 small">{{ $collection->member->name }} - {{ $collection->date->format('d M, Y') }}</p>
                </div>
                <div class="card-body p-3 p-lg-4">
                    
                    @if ($errors->any())
                        <div class="alert alert-danger mb-4">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('collections.update', $collection->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-3 g-lg-4">
                            
                            {{-- Collection Date --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted fw-bold small text-uppercase tracking-wider">{{ __('messages.collection_date') }}</label>
                                <input type="text" name="date" class="form-control flatpickr form-control-premium" value="{{ old('date', $collection->date->format('Y-m-d')) }}" required>
                            </div>

                            {{-- Savings Section --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('messages.deposit') }} {{ __('messages.amount') }}</label>
                                <div class="input-group shadow-sm rounded-3 overflow-hidden">
                                    <span class="input-group-text bg-white border-end-0 text-muted small fw-bold">{{ __('messages.bdt') }}</span>
                                    <input type="number" step="0.01" name="amount" class="form-control form-control-premium border-start-0" value="{{ old('amount', $collection->deposit) }}" placeholder="0.00">
                                </div>
                            </div>

                            {{-- Withdrawal Section --}}
                             <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-danger">{{ __('messages.withdraw') ?? 'Withdraw' }} {{ __('messages.amount') }}</label>
                                <div class="input-group shadow-sm rounded-3 overflow-hidden">
                                    <span class="input-group-text bg-white border-end-0 text-muted small fw-bold">{{ __('messages.bdt') }}</span>
                                    <input type="number" step="0.01" name="withdraw_amount" class="form-control form-control-premium border-start-0" value="{{ old('withdraw_amount', $collection->withdraw) }}" placeholder="0.00">
                                </div>
                            </div>

                            {{-- Loan Section --}}
                            <div class="col-md-6 mb-3">
                                <div class="row g-2">
                                    <div class="col-7">
                                        <label class="form-label small fw-bold text-muted">{{ __('messages.loan_installment') }}</label>
                                        <div class="input-group shadow-sm rounded-3 overflow-hidden">
                                            <span class="input-group-text bg-white border-end-0 text-muted small fw-bold">{{ __('messages.bdt') }}</span>
                                            <input type="number" step="0.01" name="loan_installment" class="form-control form-control-premium border-start-0" value="{{ old('loan_installment', $collection->loan_installment) }}" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-5">
                                        <label class="form-label small fw-bold text-muted">{{ __('messages.grace_amount') ?? 'Grace' }}</label>
                                        <div class="input-group shadow-sm rounded-3 overflow-hidden">
                                            <input type="number" step="0.01" name="grace_amount" class="form-control form-control-premium" value="{{ old('grace_amount', $collection->grace_amount) }}" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div class="col-12 mb-3">
                                <label class="form-label text-muted fw-bold small text-uppercase tracking-wider">{{ __('messages.notes') }}</label>
                                <input name="notes" class="form-control form-control-premium" value="{{ old('notes', $collection->notes) }}" placeholder="{{ __('messages.enter_notes') ?? 'Optional collector notes...' }}">
                            </div>

                            {{-- Submit Button --}}
                            <div class="col-12 text-center mt-3 mt-lg-4">
                                <button type="submit" class="btn btn-premium w-100 py-3 d-flex align-items-center justify-content-center">
                                    <i data-lucide="check-circle" class="me-2 icon-sm"></i> {{ __('messages.update_collection') ?? 'Update Collection' }}
                                </button>
                                <a href="{{ route('members.show', $collection->member_id) }}" class="btn btn-link text-muted mt-2">{{ __('messages.cancel') }}</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('plugin-scripts')
    <script src="{{ asset('build/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            flatpickr(".flatpickr", {
                altInput: true,
                dateFormat: "Y-m-d",
                altFormat: "d M, Y"
            });
            lucide.createIcons();
        });
    </script>
@endpush
