{{--
@extends('layout.master')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">{{ __('messages.welcome_to_your_dashboard') }}</h4>
        </div>
    </div>

    --}}
{{-- Status Cards --}}{{--

    <div class="row">
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">{{ __('messages.my_members') }}</h5>
                    <h3>{{ $totalMembers }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">{{ __('messages.area_savings') }}</h5>
                    <h3>{{ number_format($totalSavings) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">{{ __('messages.area_due') }}</h5>
                    <h3>{{ number_format($totalLoanDue) }}</h3>
                </div>
            </div>
        </div>
    </div>

    --}}
{{-- Today's Dues Lists --}}{{--

    <div class="row">
        <div class="col-lg-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('messages.loan_installments_due_today') }}</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>{{ __('messages.member') }}</th><th>{{ __('messages.account_no') }}</th><th>{{ __('messages.due_amount') }}</th><th>Action</th></tr></thead>
                            <tbody>
                            @forelse($loanInstallmentsDueToday as $loan)
                                <tr>
                                    <td><a href="{{ route('members.show', $loan->member->id) }}">{{ $loan->member->name }}</a></td>
                                    <td>{{ $loan->account_no }}</td>
                                    <td class="text-danger fw-bold">{{ number_format($loan->installment_amount) }}</td>
                                    <td><a href="{{ route('loan-installments.create') }}" class="btn btn-primary btn-xs">{{ __('messages.collect') }}</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center">{{ __('messages.no_dues_today') }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('messages.savings_due_today') }}</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>{{ __('messages.member') }}</th><th>{{ __('messages.account_no') }}</th><th>Action</th></tr></thead>
                            <tbody>
                            @forelse($savingsDueToday as $saving)
                                <tr>
                                    <td><a href="{{ route('members.show', $saving->member->id) }}">{{ $saving->member->name }}</a></td>
                                    <td>{{ $saving->account_no }}</td>
                                    <td><a href="{{ route('savings-collections.create') }}" class="btn btn-primary btn-xs">{{ __('messages.collect') }}</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center">{{ __('messages.no_dues_today') }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


    --}}
{{-- Today's Performance & Top Defaulters --}}{{--

    <div class="row">
        <div class="col-md-5 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('messages.my_collection_today') }}</h5>
                    <div class="d-flex justify-content-around mt-4">
                        <div class="text-center">
                            <p class="text-muted">{{ __('messages.savings') }}</p>
                            <h4 class="text-success">{{ number_format($todaySavings) }}</h4>
                        </div>
                        <div class="text-center">
                            <p class="text-muted">{{ __('messages.loan_installment') }}</p>
                            <h4 class="text-primary">{{ number_format($todayInstallments) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-7 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('messages.top_5_defaulters') }}</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>{{ __('messages.member') }}</th><th>{{ __('messages.due_amount') }}</th></tr></thead>
                            <tbody>
                            @forelse($topDefaulters as $loan)
                                <tr>
                                    <td><a href="{{ route('members.show', $loan->member->id) }}">{{ $loan->member->name }}</a></td>
                                    <td class="text-danger fw-bold">{{ number_format($loan->due_amount) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center">{{ __('messages.no_defaulters_found') }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
--}}
@extends('layout.master')
@push('style')
    <style>
        /* public/css/custom.css */

        .card-gradient-info {
            background: linear-gradient(to right, #4e54c8, #8f94fb);
            color: #ffffff;
        }

        .card-gradient-success {
            background: linear-gradient(to right, #00b09b, #96c93d);
            color: #ffffff;
        }

        .card-gradient-danger {
            background: linear-gradient(to right, #f5567b, #fd6e6a);
            color: #ffffff;
        }

        .card-gradient-info .card-title,
        .card-gradient-success .card-title,
        .card-gradient-danger .card-title {
            color: rgba(255, 255, 255, 0.9);
        }

        /* Inline Collection Styles */
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
            text-align: right;
            border-radius: 4px;
            padding: 4px 8px;
            height: 32px;
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
        .cursor-pointer {
            cursor: pointer;
        }
        .card-header[aria-expanded="false"] [data-lucide="chevron-down"] {
            transform: rotate(-90deg);
        }
    </style>
@endpush
@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">{{ __('messages.welcome_to_your_dashboard') }}</h4>
        </div>
    </div>

    {{-- Status Cards (নতুন ডিজাইন) --}}
    <div class="row">
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card card-gradient-info">
                <div class="card-body text-center">
                    <h5 class="card-title text-uppercase small mb-3">{{ __('messages.my_members') }}</h5>
                    <h2 class="display-5 fw-bolder mb-0">{{ $totalMembers }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card card-gradient-success">
                <div class="card-body text-center">
                    <h5 class="card-title text-uppercase small mb-3">{{ __('messages.area_savings') }}</h5>
                    <h3 class="fw-bolder mb-0">{{ number_format($totalSavings) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card card-gradient-danger">
                <div class="card-body text-center">
                    <h5 class="card-title text-uppercase small mb-3">{{ __('messages.area_due') }}</h5>
                    <h3 class="fw-bolder mb-0">{{ number_format($totalLoanDue) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Today's Performance Card (একত্রিত) --}}
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('messages.my_collection_today') }}</h5>
                    <div class="row text-center mt-4">
                        <div class="col-6 border-end">
                            <p class="text-muted mb-1">{{ __('messages.savings') }}</p>
                            <h3 class="text-success mb-0">{{ number_format($todaySavings) }}</h3>
                        </div>
                        <div class="col-6">
                            <p class="text-muted mb-1">{{ __('messages.loan_installment') }}</p>
                            <h3 class="text-primary mb-0">{{ number_format($todayInstallments) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Today's Dues Lists (2-Column Layout) --}}
    <div class="row">
        <!-- Loan Installments Section -->
        <div class="col-lg-6 grid-margin stretch-card">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center cursor-pointer" 
                     data-bs-toggle="collapse" data-bs-target="#loanCollapse" aria-expanded="true">
                    <h6 class="mb-0">{{ __('messages.loan_installments_due_today') }}</h6>
                    <div>
                        <span class="badge bg-white text-danger me-2">{{ $loanInstallmentsDueToday->count() }}</span>
                        <i class="link-icon ms-1" data-lucide="chevron-down"></i>
                    </div>
                </div>
                <div class="collapse show" id="loanCollapse">
                    <div class="card-body p-0">
                        <div id="loan-due-list">
                        @forelse($loanInstallmentsDueToday as $loan)
                            <div class="due-list-item d-flex justify-content-between align-items-center" id="loan-item-{{ $loan->id }}">
                                <div>
                                    <h6 class="mb-0 fw-bold">{{ $loan->member->name }}</h6>
                                    <small class="text-muted">{{ $loan->account_no }}</small>
                                </div>
                                <div class="collection-input-group">
                                    <input type="number" 
                                           class="form-control form-control-sm collection-input loan-input" 
                                           placeholder="{{ round($loan->installment_amount) }}" 
                                           data-id="{{ $loan->id }}"
                                           data-type="loan"
                                           value="{{ round($loan->installment_amount) }}">
                                    <button class="btn btn-success btn-xs btn-submit-ajax d-none" 
                                            onclick="submitCollection(this, 'loan', {{ $loan->id }})">
                                        <i class="link-icon" data-lucide="check"></i> {{ __('messages.submit') ?? 'জমা দিন' }}
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted">{{ __('messages.no_dues_today') }}</div>
                        @endforelse
                    </div>
                    <div class="total-display-card d-flex justify-content-between align-items-center">
                        <span>{{ __('messages.total_collection_today') }}</span>
                        <span id="loan-total-display" class="text-primary">{{ number_format($todayInstallments) }}</span>
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
                    <h6 class="mb-0">{{ __('messages.savings_due_today') }}</h6>
                    <div>
                        <span class="badge bg-white text-success me-2">{{ $savingsDueToday->count() }}</span>
                        <i class="link-icon ms-1" data-lucide="chevron-down"></i>
                    </div>
                </div>
                <div class="collapse show" id="savingsCollapse">
                    <div class="card-body p-0">
                        <div id="savings-due-list">
                        @forelse($savingsDueToday as $saving)
                            <div class="due-list-item d-flex justify-content-between align-items-center" id="savings-item-{{ $saving->id }}">
                                <div>
                                    <h6 class="mb-0 fw-bold">{{ $saving->member->name }}</h6>
                                    <small class="text-muted">{{ $saving->account_no }} - {{ $saving->scheme_type }}</small>
                                </div>
                                <div class="collection-input-group">
                                    @php
                                        // Default savings amount prediction could be added here
                                        $placeholder = 0; 
                                        if($saving->scheme_type == 'Daily') $placeholder = 10; // Example
                                    @endphp
                                    <input type="number" 
                                           class="form-control form-control-sm collection-input savings-input" 
                                           placeholder="Amount" 
                                           data-id="{{ $saving->id }}"
                                           data-type="savings">
                                    <button class="btn btn-success btn-xs btn-submit-ajax d-none" 
                                            onclick="submitCollection(this, 'savings', {{ $saving->id }})">
                                        {{ __('messages.submit') ?? 'জমা দিন' }}
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted">{{ __('messages.no_dues_today') }}</div>
                        @endforelse
                    </div>
                    <div class="total-display-card d-flex justify-content-between align-items-center">
                        <span>{{ __('messages.total_collection_today') }}</span>
                        <span id="savings-total-display" class="text-success">{{ number_format($todaySavings) }}</span>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Defaulters (এই কার্ডটি এখন ঐচ্ছিক, কারণ উপরের তালিকায় বকেয়া দেখা যাচ্ছে) --}}
    {{-- <div class="row"> ... Top 5 Defaulters card ... </div> --}}
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

            // Handle input change (optional: could auto-calculate)

            // Feather icons initialization might be needed if they are dynamically added, 
            // but here they are static in the loop.
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
                    $input.addClass('submitted-input');
                    $btn.addClass('d-none');
                    
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

            // Also update the main summary card if present
            // Looking at the top of field_worker.blade.php:
            // Loans: h3.text-primary.mb-0
            // Savings: h3.text-success.mb-0
            const perfSelector = type === 'loan' ? '.text-primary.mb-0' : '.text-success.mb-0';
            const $perfDisplay = $(perfSelector).filter(function() {
                return !$(this).parents('.card-body p-0').length; // Avoid footer display
            }).first();
            
            if ($perfDisplay.length) {
                let currentPerf = parseFloat($perfDisplay.text().replace(/,/g, '')) || 0;
                $perfDisplay.text((currentPerf + parseFloat(amount)).toLocaleString());
            }
        }
    </script>
@endpush
