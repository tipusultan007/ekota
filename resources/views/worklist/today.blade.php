@extends('layout.master')
@push('style')
    <style>
        .due-list-item {
            border-bottom: 1px solid #f1f1f1;
            padding: 8px 12px;
            transition: all 0.3s ease;
        }
        .due-list-item:hover {
            background-color: #fcfcfc;
        }
        .collection-input-group {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: nowrap;
        }
        .collection-input {
            width: 80px;
            text-align: left;
            border-radius: 4px;
            padding: 4px 8px;
            height: 32px;
            font-weight: 800;
        }
        .submitted-input {
            background-color: #e9ecef !important;
            color: #6c757d !important;
            border-color: #dee2e6 !important;
            cursor: not-allowed;
        }
        .total-display-card {
            background-color: #f8f9fa;
            border-top: 2px solid #ddd;
            padding: 12px 15px;
            font-weight: bold;
        }
        .completed-item {
            background-color: #f8f9fa !important;
            opacity: 0.8 !important;
        }
        .completed-item h6 {
            color: #6c757d !important;
            text-decoration: line-through;
        }
        .cursor-default {
            cursor: default !important;
        }
        .cursor-pointer {
            cursor: pointer;
        }
    </style>
@endpush

@section('content')
    <nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ __('messages.todays_worklist') }} ({{ \Carbon\Carbon::today()->format('d M, Y') }})</li>
    </ol>
    </nav>

    @php
        $initialLoanTotal = $loanInstallmentsDueToday->sum(function($loan) {
            return $loan->installments->sum('paid_amount');
        });
        $initialSavingsTotal = $savingsDueToday->sum(function($saving) {
            return $saving->collections->sum('amount');
        });
    @endphp

    <div class="row">
        {{-- আজকের টার্গেট এবং সারাংশ --}}
        <div class="col-md-12 grid-margin">
            <div class="card">
                <div class="card-header bg-primary">
                    <h5 class="card-title mb-0 text-white  ">{{ __('messages.todays_summary_and_targets') }}</h5>
                </div>
                <div class="card-body">

                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="card-title  ">{{ __('messages.outstanding_loan_installments') }}</div>
                            <h4 class="text-danger">{{ $loanInstallmentsDueToday->count() }} {{ __('messages.accounts') }}</h4>
                        </div>
                        <div class="col-md-4">
                            <div class="card-title  ">{{ __('messages.outstanding_savings_collections') }}</div>
                            <h4 class="text-success">{{ $savingsDueToday->count() }} {{ __('messages.accounts') }}</h4>
                        </div>
                        <div class="col-md-4">
                            <div class="card-title  ">{{ __('messages.todays_loan_collection_target') }}</div>
                            <h4 class="text-primary">{{ number_format($totalTarget, 2) }} BDT</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Dues Lists (2-Column Layout) --}}
        <div class="row">
            <!-- Loan Installments Section -->
            <div class="col-lg-6 grid-margin stretch-card">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center cursor-pointer" 
                        data-bs-toggle="collapse" data-bs-target="#loanCollapse" aria-expanded="true">
                        <h6 class="mb-0 text-white">{{ __('messages.loan_installments_due') }}</h6>
                        <div>
                            <span class="badge bg-white text-danger me-2">{{ $loanInstallmentsDueToday->count() }}</span>
                            <i class="link-icon ms-1" data-lucide="chevron-down"></i>
                        </div>
                    </div>
                    <div class="collapse show" id="loanCollapse">
                        <div class="card-body p-0">
                            <div id="loan-due-list">
                                @forelse($loanInstallmentsDueToday as $loan)
                                    @php
                                        $isCompleted = $loan->installments->count() > 0;
                                    @endphp
                                    <div class="due-list-item d-flex justify-content-between align-items-center {{ $isCompleted ? 'completed-item' : '' }}" id="loan-item-{{ $loan->id }}">
                                        <div>
                                            <h6 class="mb-0 fw-bold">{{ $loan->member->name }}</h6>
                                            <small class="text-muted">{{ $loan->account_no }}</small>
                                        </div>
                                        <div class="collection-input-group">
                                            @if($isCompleted)
                                                <span class="fw-bold text-success me-2">{{ round($loan->installments->first()->paid_amount) }}</span>
                                                <span class="btn btn-xs btn-light text-success fw-bold border-0 cursor-default">
                                                    <i class="link-icon" data-lucide="check-circle" style="width: 14px; height: 14px;"></i> Collected(সংগৃহীত)
                                                </span>
                                            @else
                                                <input type="number" 
                                                    class="form-control form-control-sm collection-input loan-input" 
                                                    placeholder="{{ round($loan->installment_amount) }}" 
                                                    data-id="{{ $loan->id }}"
                                                    data-type="loan"
                                                    value="{{ round($loan->installment_amount) }}">
                                                <button class="btn btn-success btn-xs btn-submit-ajax d-none" 
                                                        onclick="submitCollection(this, 'loan', {{ $loan->id }})">
                                                    <i class="link-icon" data-lucide="check"></i> {{ __('messages.submit') }}
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-4 text-center text-muted">{{ __('messages.no_dues_today') }}</div>
                                @endforelse
                            </div>
                            <div class="total-display-card d-flex justify-content-between align-items-center">
                                <span>{{ __('messages.total_collection_today') }}</span>
                                <span id="loan-total-display" class="text-primary">{{ number_format($initialLoanTotal, 0) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Savings Due Section -->
            <div class="col-lg-6 grid-margin stretch-card">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center cursor-pointer"
                        data-bs-toggle="collapse" data-bs-target="#savingsCollapse" aria-expanded="true">
                        <h6 class="mb-0 text-white">{{ __('messages.savings_collections_due') }}</h6>
                        <div>
                            <span class="badge bg-white text-success me-2">{{ $savingsDueToday->count() }}</span>
                            <i class="link-icon ms-1" data-lucide="chevron-down"></i>
                        </div>
                    </div>
                    <div class="collapse show" id="savingsCollapse">
                        <div class="card-body p-0">
                            <div id="savings-due-list">
                                @forelse($savingsDueToday as $saving)
                                    @php
                                        $isCompleted = $saving->collections->count() > 0;
                                    @endphp
                                    <div class="due-list-item d-flex justify-content-between align-items-center {{ $isCompleted ? 'completed-item' : '' }}" id="savings-item-{{ $saving->id }}">
                                        <div>
                                            <h6 class="mb-0 fw-bold">{{ $saving->member->name }}</h6>
                                            <small class="text-muted">{{ $saving->account_no }} - {{ $saving->scheme_type }}</small>
                                        </div>
                                        <div class="collection-input-group">
                                            @if($isCompleted)
                                                <span class="fw-bold text-success me-2">{{ round($saving->collections->first()->amount) }}</span>
                                                <span class="btn btn-xs btn-light text-success fw-bold border-0 cursor-default">
                                                    <i class="link-icon" data-lucide="check-circle" style="width: 14px; height: 14px;"></i> Collected(সংগৃহীত)
                                                </span>
                                            @else
                                                <input type="number" 
                                                    class="form-control form-control-sm collection-input savings-input" 
                                                    placeholder="Amount" 
                                                    data-id="{{ $saving->id }}"
                                                    data-type="savings">
                                                <button class="btn btn-success btn-xs btn-submit-ajax d-none" 
                                                        onclick="submitCollection(this, 'savings', {{ $saving->id }})">
                                                    {{ __('messages.submit') }}
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-4 text-center text-muted">{{ __('messages.no_dues_today') }}</div>
                                @endforelse
                            </div>
                            <div class="total-display-card d-flex justify-content-between align-items-center">
                                <span>{{ __('messages.total_collection_today') }}</span>
                                <span id="savings-total-display" class="text-success">{{ number_format($initialSavingsTotal, 0) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection

@push('custom-scripts')
    <script>
        $(document).ready(function() {
            // Initialize icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Show submit button on input focus
            $(document).on('focus', '.collection-input', function() {
                if (!$(this).hasClass('submitted-input')) {
                    $(this).closest('.collection-input-group').find('.btn-submit-ajax').removeClass('d-none');
                    
                    // Set cursor to the end
                    const val = $(this).val();
                    $(this).val('').val(val);
                }
            });
        });

        function submitCollection(btn, type, id) {
            const $btn = $(btn);
            const $item = $btn.closest('.due-list-item');
            const $input = $item.find('.collection-input');
            const amount = $input.val();
            const originalBtnHtml = $btn.html();

            if (!amount || amount <= 0) {
                Swal.fire('Error', 'Please enter a valid amount', 'error');
                return;
            }

            // Disable UI
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
            $input.prop('readonly', true);

            const url = type === 'loan' ? "{{ route('loan-installments.store') }}" : "{{ route('savings-collections.store') }}";
            const data = {
                _token: "{{ csrf_token() }}",
                account_id: "{{ $defaultCashAccountId }}",
                [type === 'loan' ? 'loan_account_id' : 'savings_account_id']: id,
                [type === 'loan' ? 'paid_amount' : 'amount']: amount,
                [type === 'loan' ? 'payment_date' : 'collection_date']: "{{ date('Y-m-d') }}"
            };

            $.ajax({
                url: url,
                method: 'POST',
                data: data,
                success: function(response) {
                    // Success State
                    $item.addClass('completed-item');
                    $item.find('.collection-input-group').html(`
                        <span class="fw-bold text-success me-2">${Math.round(amount)}</span>
                        <span class="btn btn-xs btn-light text-success fw-bold border-0 cursor-default">
                            <i class="link-icon" data-lucide="check-circle" style="width: 14px; height: 14px;"></i> Collected(সংগৃহীত)
                        </span>
                    `);
                    
                    // Re-initialize icons in the newly added HTML
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                    
                    // Update Totals
                    updateSessionTotal(type, amount);

                    // Toast success
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Collection successful',
                        showConfirmButton: false,
                        timer: 2000
                    });
                },
                error: function(xhr) {
                    // Re-enable on error
                    $btn.prop('disabled', false).html(originalBtnHtml);
                    $input.prop('readonly', false);
                    
                    let errorMsg = 'Something went wrong';
                    if (xhr.status === 422 && xhr.responseJSON.errors) {
                        errorMsg = Object.values(xhr.responseJSON.errors)[0][0];
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        }

        function updateSessionTotal(type, amount) {
            const displayId = type === 'loan' ? '#loan-total-display' : '#savings-total-display';
            const $display = $(displayId);
            
            let currentTotal = parseFloat($display.text().replace(/,/g, '')) || 0;
            let newTotal = currentTotal + parseFloat(amount);
            
            $display.text(newTotal.toLocaleString());
        }
    </script>
@endpush
