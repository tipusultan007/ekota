@extends('layout.master')
@section('title', __('messages.new_loan_application') . ' | ' . config('app.name'))

@push('plugin-styles')
    <link rel="stylesheet" href="{{ asset('build/plugins/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}">
    <style>
        /* Premium Design System - Red/Pink Loans Theme */
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

        .premium-header-blue {
             background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); /* Blue gradient for Summary */
        }
        
        .premium-header::before, .premium-header-blue::before {
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
            font-size: 1.1rem;
            display: flex;
            align-items: center;
        }

        .form-control-premium, .form-select, .input-group-text {
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
        
        .input-group .input-group-text {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            background-color: #e2e8f0;
            border-color: #e2e8f0;
            color: #475569;
            font-weight: 600;
        }
        
        .input-group .form-control-premium {
             border-top-left-radius: 0;
             border-bottom-left-radius: 0;
        }

        .btn-submit {
            background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
            border: none;
            border-radius: 0.75rem;
            padding: 0.75rem 2rem;
            font-weight: 600;
            box-shadow: 0 4px 6px -1px rgba(225, 29, 72, 0.2);
            transition: all 0.3s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(225, 29, 72, 0.3);
            background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
        }

        .select2-container--default .select2-selection--single {
            border-radius: 0.75rem;
            padding: 4px 12px;
            border: 1px solid #e2e8f0;
            height: 46px;
            background-color: #f8fafc;
            display: flex;
            align-items: center;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 44px; }
        
        .form-label {
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }
    </style>
@endpush
@section('content')

    <div class="row">
        {{-- Loan Application Form Section --}}
        <div class="col-lg-8 grid-margin stretch-card">
            <div class="card premium-card">
                <div class="premium-header">
                     <h5 class="premium-title">
                        <i data-lucide="file-plus" class="icon-sm me-2"></i> {{ __('messages.new_loan_application') }}
                    </h5>
                </div>
                <div class="card-body p-4">
                    @if (session('error')) <div class="alert alert-danger shadow-sm border-0 rounded-3 mb-4">{{ session('error') }}</div> @endif
                    @if ($errors->any())
                        <div class="alert alert-danger shadow-sm border-0 rounded-3 mb-4">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('loan.new.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label for="member_id" class="form-label">{{ __('messages.select_member') }} <span class="text-danger">*</span></label>
                            <select name="member_id" id="member_id" class="form-select @error('member_id') is-invalid @enderror" required>
                                <option value="">{{ __('messages.search_select_member') }}</option>
                                @foreach ($members as $member)
                                    <option value="{{ $member->id }}" {{ old('member_id') == $member->id ? 'selected' : '' }}>
                                        {{ $member->name }} - {{ $member->account_no }}
                                    </option>
                                @endforeach
                            </select>
                            @error('member_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <h5 class="mb-3 border-bottom pb-2 text-primary fw-bold">{{ __('messages.loan_details') }}</h5>
                        
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('messages.disburse_from_account') }} <span class="text-danger">*</span></label>
                                <select name="account_id" class="form-select @error('account_id') is-invalid @enderror" required>
                                    <option value="">{{ __('messages.select_account') }}</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}" {{ old('account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('messages.loan_amount') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">৳</span>
                                    <input type="number" name="loan_amount" id="loan_amount" class="form-control form-control-premium @error('loan_amount') is-invalid @enderror" value="{{ old('loan_amount') }}" required>
                                </div>
                                @error('loan_amount') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('messages.interest_rate') }} (%)</label>
                                <input type="number" step="0.01" name="interest_rate" id="interest_rate" class="form-control form-control-premium @error('interest_rate') is-invalid @enderror" value="{{ old('interest_rate') }}" required>
                                @error('interest_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('messages.number_of_installments') }}</label>
                                <input type="number" name="number_of_installments" id="number_of_installments" class="form-control form-control-premium @error('number_of_installments') is-invalid @enderror" value="{{ old('number_of_installments') }}" required>
                                @error('number_of_installments') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('messages.total_payable') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">৳</span>
                                    <input type="text" id="total_payable_display" class="form-control form-control-premium bg-light" readonly>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('messages.installment_amount') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">৳</span>
                                    <input type="text" id="calculated_installment" class="form-control form-control-premium bg-light" readonly>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('messages.installment_frequency') }} <span class="text-danger">*</span></label>
                                <select name="installment_frequency" class="form-select" required>
                                    <option value="daily">{{ __('messages.daily') }}</option>
                                    <option value="weekly">{{ __('messages.weekly') }}</option>
                                    <option value="monthly" selected>{{ __('messages.monthly') }}</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('messages.disbursement_date') }}</label>
                                <input type="text" name="disbursement_date" class="form-control flatpickr form-control-premium @error('disbursement_date') is-invalid @enderror" value="{{ old('disbursement_date', date('Y-m-d')) }}" required>
                                @error('disbursement_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                             <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('messages.processing_fee') }} (2%)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">৳</span>
                                    <input type="number" name="processing_fee" id="processing_fee" class="form-control form-control-premium @error('processing_fee') is-invalid @enderror" value="{{ old('processing_fee') }}" min="0" step="0.01" readonly>
                                </div>
                                @error('processing_fee') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Guarantor Section --}}
                        <h5 class="mt-4 mb-3 border-bottom pb-2 text-primary fw-bold">{{ __('messages.guarantor_information') }}</h5>
                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.guarantor_type') }}</label>
                            <select name="guarantor_type" id="guarantorType" class="form-select @error('guarantor_type') is-invalid @enderror" >
                                <option value="">-- {{ __('messages.select_type') }} --</option>
                                <option value="member" {{ old('guarantor_type') == 'member' ? 'selected' : '' }}>{{ __('messages.existing_member') }}</option>
                                <option value="outsider" {{ old('guarantor_type') == 'outsider' ? 'selected' : '' }}>{{ __('messages.outside_person') }}</option>
                            </select>
                            @error('guarantor_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div id="memberGuarantor" style="display: {{ old('guarantor_type') == 'member' ? 'block' : 'none' }};">
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.select_member_as_guarantor') }}</label>
                                <select name="member_guarantor_id" class="form-select @error('member_guarantor_id') is-invalid @enderror">
                                    <option value="">-- {{ __('messages.select_member') }} --</option>
                                    @foreach ($guarantors as $guarantor)
                                        <option value="{{ $guarantor->id }}" {{ old('member_guarantor_id') == $guarantor->id ? 'selected' : '' }}>
                                            {{ $guarantor->name }} ({{ __('messages.id') }}: {{ $guarantor->id }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('member_guarantor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div id="outsiderGuarantor" style="display: {{ old('guarantor_type') == 'outsider' ? 'block' : 'none' }};">
                            <div class="row g-3">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ __('messages.name') }}</label>
                                    <input type="text" name="outsider_name" class="form-control form-control-premium @error('outsider_name') is-invalid @enderror" value="{{ old('outsider_name') }}">
                                    @error('outsider_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ __('messages.phone') }}</label>
                                    <input type="text" name="outsider_phone" class="form-control form-control-premium @error('outsider_phone') is-invalid @enderror" value="{{ old('outsider_phone') }}">
                                    @error('outsider_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">{{ __('messages.address') }}</label>
                                    <textarea name="outsider_address" class="form-control form-control-premium @error('outsider_address') is-invalid @enderror" rows="2">{{ old('outsider_address') }}</textarea>
                                    @error('outsider_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ __('messages.nid_photo') }}</label>
                                    <input type="file" name="guarantor_nid" class="form-control form-control-premium @error('guarantor_nid') is-invalid @enderror">
                                    @error('guarantor_nid') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ __('messages.other_documents') }}</label>
                                    <input type="file" name="guarantor_documents[]" class="form-control form-control-premium" multiple>
                                </div>
                            </div>
                        </div>

                        {{-- Loan Documents Section --}}
                        <h5 class="mt-4 mb-3 border-bottom pb-2 text-primary fw-bold">{{ __('messages.loan_documents') }}</h5>
                        <div id="loan-documents-wrapper">
                            <div class="row mb-2 align-items-center g-2">
                                <div class="col-md-5"><input type="text" name="document_names[]" class="form-control form-control-premium" placeholder="{{ __('messages.document_name_placeholder') }}"></div>
                                <div class="col-md-5"><input type="file" name="loan_documents[]" class="form-control form-control-premium"></div>
                                <div class="col-md-2"><button type="button" class="btn btn-sm btn-success w-100 py-2 rounded-3" id="add-document-btn"><i data-lucide="plus" class="icon-xs"></i> {{ __('messages.add_more') }}</button></div>
                            </div>
                        </div>

                        <div class="d-grid mt-5">
                            <button type="submit" class="btn btn-submit text-white">{{ __('messages.create_loan_account') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Member Summary Area --}}
        <div class="col-lg-4 grid-margin stretch-card">
            <div class="card premium-card">
                 <div class="premium-header premium-header-blue">
                     <h5 class="premium-title">
                        <i data-lucide="user-check" class="icon-sm me-2"></i> {{ __('messages.member_summary') }}
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div id="member_summary_content" class="text-center text-muted mt-2">
                         <div class="d-flex flex-column align-items-center justify-content-center py-5">
                            <i data-lucide="search" class="icon-xl text-light mb-3"></i>
                            <p>{{ __('messages.select_member_to_view_summary') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('custom-scripts')
    <script>
        $(".form-select").select2({
            width: '100%',
        })
        $(".flatpickr").flatpickr({
            altInput: true,
            dateFormat: 'Y-m-d',
            altFormat: 'd/m/Y'
        })


        document.getElementById('add-document-btn').addEventListener('click', function() {
            const wrapper = document.getElementById('loan-documents-wrapper');
            const newRow = document.createElement('div');
            newRow.className = 'row mb-2 align-items-center';
            newRow.innerHTML = `
        <div class="col-md-5"><input type="text" name="document_names[]" class="form-control" placeholder="Document Name"></div>
        <div class="col-md-5"><input type="file" name="loan_documents[]" class="form-control"></div>
        <div class="col-md-2"><button type="button" class="btn btn-sm btn-danger remove-document-btn">Remove</button></div>
    `;
            wrapper.appendChild(newRow);
        });

        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('remove-document-btn')) {
                e.target.closest('.row').remove();
            }
        });
    </script>

    <script>
        $(document).ready(function() {
            const memberSelect = $('#member_id');
            const summaryContent = $('#member_summary_content');

            // Initialize Select2
            memberSelect.select2({
                placeholder: "Search and select a member...",
                width: '100%'
            });

            // Member selection change event
            memberSelect.on('change', function() {
                const memberId = $(this).val();

                if (!memberId) {
                    summaryContent.html('<p class="text-muted">Select a member to view their financial summary.</p>');
                    return;
                }

                // Show loading state
                summaryContent.html('<div class="spinner-border spinner-border-sm" role="status"></div>');

                // Fetch member account data via API
                $.ajax({
                    url: `/api/members/${memberId}/accounts`,
                    type: 'GET',
                    success: function(response) {
                        const member = response.member;

                        // Build and display summary HTML
                        let html = `
                        <div class="text-center mb-3">
                            <img src="${member.photo_url}" class="rounded-circle" width="80" height="80" alt="Photo" style="object-fit: cover;">
                        </div>
                        <h6 class="text-center">${member.name}</h6>
                        <p class="text-muted text-center small mb-3">
                            <i data-lucide="phone" class="icon-sm me-1"></i> ${member.phone}
                        </p>
                        <hr>
                    `;

                        // Savings Summary
                        html += '<h6 class="mb-3">Savings Summary</h6>';
                        if (response.savings.length > 0) {
                            let totalSavings = 0;
                            response.savings.forEach(acc => totalSavings += parseFloat(acc.current_balance));
                            html += `<p><strong>Total Balance:</strong> <span class="text-success">${totalSavings.toFixed(2)}</span> in ${response.savings.length} account(s).</p>`;
                        } else {
                            html += '<p class="small text-muted">No active savings accounts.</p>';
                        }

                        // Loan Summary
                        html += '<hr><h6 class="mb-3">Loan Summary</h6>';
                        if (response.loans.length > 0) {
                            let totalDue = 0;
                            response.loans.forEach(acc => totalDue += (parseFloat(acc.total_payable) - parseFloat(acc.total_paid)));
                            html += `<p><strong>Total Due:</strong> <span class="text-danger">${totalDue.toFixed(2)}</span> in ${response.loans.length} account(s).</p>`;
                        } else {
                            html += '<p class="small text-muted">No running loan accounts.</p>';
                        }

                        summaryContent.html(html);
                        lucide.createIcons(); // Render new icons
                    },
                    error: function() {
                        summaryContent.html('<p class="text-danger">Failed to load member summary.</p>');
                    }
                });
            });

            // Trigger change on page load if a member is already selected (due to validation error)
            if (memberSelect.val()) {
                memberSelect.trigger('change');
            }

            const guarantorTypeSelect = $('#guarantorType');
            const memberDiv = $('#memberGuarantor');
            const outsiderDiv = $('#outsiderGuarantor');

            guarantorTypeSelect.on('change', function() {
                if (this.value === 'member') {
                    memberDiv.slideDown();
                    outsiderDiv.slideUp();
                } else if (this.value === 'outsider') {
                    memberDiv.slideUp();
                    outsiderDiv.slideDown();
                } else {
                    memberDiv.slideUp();
                    outsiderDiv.slideUp();
                }
            });

            // Trigger on page load to set initial state (for validation errors)
            guarantorTypeSelect.trigger('change');

            const loanAmountInput = $('#loan_amount');
            const interestRateInput = $('#interest_rate');
            const installmentsInput = $('#number_of_installments');
            const calculatedInstallmentDisplay = $('#calculated_installment');
            const totalPayableDisplay = $('#total_payable_display');
            const processingFeeInput = $('#processing_fee');

            function calculateInstallment() {
                const loanAmount = parseFloat(loanAmountInput.val()) || 0;
                const interestRate = parseFloat(interestRateInput.val()) || 0;
                const installments = parseInt(installmentsInput.val()) || 0;
                
                // Calculate Processing Fee (2%)
                const processingFee = loanAmount * 0.02;
                processingFeeInput.val(processingFee.toFixed(2));

                if (loanAmount > 0 && interestRate >= 0 && installments > 0) {
                    const interest = (loanAmount * interestRate) / 100;
                    const totalPayable = loanAmount + interest;
                    const installmentAmount = totalPayable / installments;

                    // Display calculated values
                    calculatedInstallmentDisplay.val(installmentAmount.toFixed(2));
                    totalPayableDisplay.val(totalPayable.toFixed(2));
                } else {
                    calculatedInstallmentDisplay.val('');
                    totalPayableDisplay.val(''); 
                }
            }

            // Run calculation on input change
            loanAmountInput.on('input', calculateInstallment);
            interestRateInput.on('input', calculateInstallment);
            installmentsInput.on('input', calculateInstallment);
        });
    </script>
@endpush
