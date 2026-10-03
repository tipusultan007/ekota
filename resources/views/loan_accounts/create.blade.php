@extends('layout.master')
@section('title', __('messages.new_loan_application') . ' | ' . config('app.name'))

@push('plugin-styles')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #FF3366 0%, #FF5E33 100%);
            --secondary-gradient: linear-gradient(135deg, #7000FF 0%, #D400FF 100%);
            --accent-gradient: linear-gradient(135deg, #00C6FF 0%, #0072FF 100%);
            --glass-bg: rgba(255, 255, 255, 0.95);
            --premium-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        /* Premium Select2 Styling */
        .select2-container--default .select2-selection--single {
            border-radius: 0.75rem;
            padding: 0px 12px;
            border: 1px solid #e2e8f0;
            height: 42px;
            background-color: #f8fafc;
            display: flex;
            align-items: center;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #334155;
            padding-left: 0;
            line-height: 40px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }
        .select2-dropdown {
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-radius: 0.75rem;
            overflow: hidden;
        }
        .select2-results__option {
            padding: 8px 15px;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background: var(--secondary-gradient);
        }

        .premium-card {
            border: none;
            border-radius: 1.25rem;
            background: var(--glass-bg);
            box-shadow: var(--premium-shadow);
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .premium-header {
            background: var(--primary-gradient);
            padding: 2rem;
            color: white;
            position: relative;
        }

        .premium-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 40px;
            background: linear-gradient(to top, var(--glass-bg), transparent);
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f3f4f6;
            color: #1f2937;
        }

        .section-header i {
            color: #4f46e5;
        }

        .form-group-custom {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .input-group-text {
            background-color: #f9fafb;
            border-color: #e5e7eb;
            color: #6b7280;
            font-weight: 500;
        }

        .form-control, .form-select {
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            border: 1px solid #e5e7eb;
            transition: all 0.2s;
        }

        .form-control:focus, .form-select:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .btn-premium {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 0.875rem 2rem;
            border-radius: 0.875rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
        }

        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.3);
            color: white;
        }

        .guarantor-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .document-row {
            background: #fff;
            padding: 1rem;
            border-radius: 0.75rem;
            border: 1px solid #f1f5f9;
            margin-bottom: 1rem;
            transition: all 0.2s;
        }

        .document-row:hover {
            border-color: #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
    </style>
@endpush

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('members.index') }}">{{ __('messages.all_members') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('members.show', $member->id) }}">{{ $member->name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('messages.issue_new_loan') }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-11 mx-auto grid-margin stretch-card">
            <div class="card premium-card">
                <div class="premium-header">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <a href="{{ route('members.show', $member->id) }}" class="btn btn-sm btn-white bg-white bg-opacity-20 text-dark border-0 rounded-pill px-3">
                            <i data-lucide="arrow-left" class="icon-sm me-1"></i> {{ __('messages.back_to_list') }}
                        </a>
                        <div class="d-inline-flex p-3 bg-white bg-opacity-10 rounded-pill shadow-inner">
                            <i data-lucide="credit-card" class="text-white" style="width: 32px; height: 32px;"></i>
                        </div>
                        <div style="width: 100px;"></div> <!-- Spacer -->
                    </div>
                    <div class="text-center">
                        <h3 class="fw-bold mb-1">{{ __('messages.new_loan_application') }}</h3>
                        <p class="text-white text-opacity-80">{{ __('messages.issue_loan_account') }} <strong>{{ $member->name }}</strong></p>
                    </div>
                </div>

                <div class="card-body p-xl-5">
                    @if (session('error'))
                        <div class="alert alert-fill-danger d-flex align-items-center mb-4" role="alert">
                            <i data-lucide="alert-circle" class="me-2" style="width: 20px; height: 20px;"></i>
                            <div>{{ session('error') }}</div>
                        </div>
                    @endif

                    <form action="{{ route('members.loan-accounts.store', $member->id) }}" method="POST" enctype="multipart/form-data" class="needs-validation">
                        @csrf

                        <!-- Loan Configuration Section -->
                        <div class="section-header">
                            <i data-lucide="settings" style="width: 24px; height: 24px; color: #FF3366;"></i>
                            <h4 class="fw-bold m-0">{{ __('messages.loan_details') }}</h4>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-4">
                                <label class="form-label d-flex justify-content-between">
                                    {{ __('messages.loan_account_no') }}
                                </label>
                                <div class="input-group">
                                    <input type="text" class="form-control bg-light" value="{{ $member->account_no }}" readonly disabled>
                                </div>
                                <small class="text-muted">{{ __('messages.id') }}: {{ $member->id }}</small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">{{ __('messages.disburse_from_account') }} <span class="text-danger">*</span></label>
                                <select name="account_id" class="form-select @error('account_id') is-invalid @enderror" required>
                                    <option value="" disabled selected>{{ __('messages.select_account') }}</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}" {{ old('account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }} (৳{{ number_format($account->balance) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">{{ __('messages.loan_amount') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text border-end-0">{{ __('messages.bdt') }}</span>
                                    <input type="number" name="loan_amount" id="loan_amount" class="form-control border-start-0 @error('loan_amount') is-invalid @enderror" 
                                           value="{{ old('loan_amount') }}" required placeholder="0.00">
                                </div>
                                @error('loan_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">{{ __('messages.interest_rate') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="interest_rate" id="interest_rate" class="form-control border-end-0 @error('interest_rate') is-invalid @enderror" 
                                           value="{{ old('interest_rate') }}" required placeholder="0.00">
                                    <span class="input-group-text border-start-0">%</span>
                                </div>
                                @error('interest_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">{{ __('messages.total_payable') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text border-end-0 bg-light">{{ __('messages.bdt') }}</span>
                                    <input type="text" id="total_payable_display" class="form-control border-start-0 bg-light" readonly placeholder="0.00">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">{{ __('messages.installment_amount') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text border-end-0 bg-light">{{ __('messages.bdt') }}</span>
                                    <input type="text" id="installment_amount_display" class="form-control border-start-0 bg-light" readonly placeholder="0.00">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">{{ __('messages.number_of_installments') }} <span class="text-danger">*</span></label>
                                <input type="number" name="number_of_installments" id="number_of_installments" class="form-control @error('number_of_installments') is-invalid @enderror" 
                                       value="{{ old('number_of_installments') }}" required placeholder="e.g., 12">
                                @error('number_of_installments') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">{{ __('messages.installment_frequency') }} <span class="text-danger">*</span></label>
                                <select name="installment_frequency" class="form-select @error('installment_frequency') is-invalid @enderror" required>
                                    <option value="daily" {{ old('installment_frequency') == 'daily' ? 'selected' : '' }}>{{ __('messages.daily') }}</option>
                                    <option value="weekly" {{ old('installment_frequency') == 'weekly' ? 'selected' : '' }}>{{ __('messages.weekly') }}</option>
                                    <option value="monthly" {{ old('installment_frequency', 'monthly') == 'monthly' ? 'selected' : '' }}>{{ __('messages.monthly') }}</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">{{ __('messages.disbursement_date') }} <span class="text-danger">*</span></label>
                                <input type="date" name="disbursement_date" class="form-control @error('disbursement_date') is-invalid @enderror" 
                                       value="{{ old('disbursement_date', date('Y-m-d')) }}" required>
                                @error('disbursement_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">{{ __('messages.processing_fee') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text border-end-0">{{ __('messages.bdt') }}</span>
                                    <input type="number" name="processing_fee" id="processing_fee" class="form-control border-start-0 @error('processing_fee') is-invalid @enderror" 
                                           value="{{ old('processing_fee') }}" min="0" step="0.01" placeholder="0.00">
                                </div>
                                @error('processing_fee') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <!-- Guarantor Information Section -->
                        <div class="section-header mt-5">
                            <i data-lucide="shield-check" style="width: 24px; height: 24px; color: #7000FF;"></i>
                            <h4 class="fw-bold m-0">{{ __('messages.guarantor_info') }}</h4>
                        </div>

                        <div class="guarantor-card mb-4">
                            <div class="row align-items-end">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ __('messages.guarantor_type') }} <span class="text-danger">*</span></label>
                                    <select name="guarantor_type" id="guarantorType" class="form-select @error('guarantor_type') is-invalid @enderror" required>
                                        <option value="" disabled {{ old('guarantor_type') == '' ? 'selected' : '' }}>{{ __('messages.select_type') }}</option>
                                        <option value="member" {{ old('guarantor_type') == 'member' ? 'selected' : '' }}>{{ __('messages.member') }}</option>
                                        <option value="outsider" {{ old('guarantor_type') == 'outsider' ? 'selected' : '' }}>{{ __('messages.outsider') }}</option>
                                    </select>
                                    @error('guarantor_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                
                                <div class="col-md-6 mb-3" id="memberGuarantor" style="display: {{ old('guarantor_type') == 'member' ? 'block' : 'none' }};">
                                    <label class="form-label">{{ __('messages.select_member') }}</label>
                                    <select name="member_guarantor_id" class="form-select js-select2 @error('member_guarantor_id') is-invalid @enderror" data-placeholder="{{ __('messages.search_select_member') }}">
                                        <option value=""></option>
                                        @foreach ($guarantors as $guarantor)
                                            <option value="{{ $guarantor->id }}" {{ old('member_guarantor_id') == $guarantor->id ? 'selected' : '' }}>
                                                {{ $guarantor->account_no }} - {{ $guarantor->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('member_guarantor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div id="outsiderGuarantor" style="display: {{ old('guarantor_type') == 'outsider' ? 'block' : 'none' }};">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('messages.full_name') }}</label>
                                        <input type="text" name="outsider_name" class="form-control @error('outsider_name') is-invalid @enderror" value="{{ old('outsider_name') }}" placeholder="John Doe">
                                        @error('outsider_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('messages.phone_number') }}</label>
                                        <input type="text" name="outsider_phone" class="form-control @error('outsider_phone') is-invalid @enderror" value="{{ old('outsider_phone') }}" placeholder="+8801xxx-xxxxxx">
                                        @error('outsider_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">{{ __('messages.present_address') }}</label>
                                        <textarea name="outsider_address" class="form-control @error('outsider_address') is-invalid @enderror" rows="2" placeholder="Full address of the guarantor">{{ old('outsider_address') }}</textarea>
                                        @error('outsider_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label d-flex align-items-center gap-1">
                                            <i data-lucide="image" style="width: 14px; height: 14px;"></i> {{ __('messages.nid_photo') }}
                                        </label>
                                        <input type="file" name="guarantor_nid" class="form-control @error('guarantor_nid') is-invalid @enderror">
                                        @error('guarantor_nid') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label d-flex align-items-center gap-1">
                                            <i data-lucide="file-text" style="width: 14px; height: 14px;"></i> Additional Files
                                        </label>
                                        <input type="file" name="guarantor_documents[]" class="form-control" multiple>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Loan Artifacts Section -->
                        <div class="section-header mt-5">
                            <i data-lucide="paperclip" style="width: 24px; height: 24px; color: #0072FF;"></i>
                            <h4 class="fw-bold m-0">{{ __('messages.documents') }}</h4>
                        </div>

                        <div id="loan-documents-wrapper">
                            <div class="document-row row g-3 align-items-center">
                                <div class="col-md-5">
                                    <label class="form-label small text-uppercase fw-bold text-muted mb-1">{{ __('messages.category_name') }}</label>
                                    <input type="text" name="document_names[]" class="form-control" placeholder="e.g., Security Cheque">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small text-uppercase fw-bold text-muted mb-1">File Upload</label>
                                    <input type="file" name="loan_documents[]" class="form-control">
                                </div>
                                <div class="col-md-2 pt-4 text-end">
                                    <button type="button" class="btn btn-soft-success btn-sm w-100" id="add-document-btn">
                                        <i data-lucide="plus" style="width: 14px; height: 14px;"></i> Add
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-5 pt-3">
                            <button type="submit" class="btn btn-premium px-5 py-3">
                                <i data-lucide="check-circle" style="width: 20px; height: 20px;"></i>
                                {{ __('messages.create_loan_account') }}
                            </button>
                            <p class="mt-2 text-muted small">{{ __('messages.confirm_pay_off_text') }}</p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            const guarantorTypeSelect = document.getElementById('guarantorType');
            const memberDiv = document.getElementById('memberGuarantor');
            const outsiderDiv = document.getElementById('outsiderGuarantor');

            function toggleGuarantorFields() {
                const type = guarantorTypeSelect.value;
                if (type === 'member') {
                    memberDiv.style.display = 'block';
                    outsiderDiv.style.display = 'none';
                } else if (type === 'outsider') {
                    memberDiv.style.display = 'none';
                    outsiderDiv.style.display = 'block';
                } else {
                    memberDiv.style.display = 'none';
                    outsiderDiv.style.display = 'none';
                }
            }

            guarantorTypeSelect.addEventListener('change', toggleGuarantorFields);
            toggleGuarantorFields();

            // Auto-calculation Logic
            const loanAmountInput = document.getElementById('loan_amount');
            const interestRateInput = document.getElementById('interest_rate');
            const installmentsInput = document.getElementById('number_of_installments');
            const processingFeeInput = document.getElementById('processing_fee');
            
            const totalPayableDisplay = document.getElementById('total_payable_display');
            const installmentAmountDisplay = document.getElementById('installment_amount_display');

            function calculateLoan() {
                const principal = parseFloat(loanAmountInput.value) || 0;
                const rate = parseFloat(interestRateInput.value) || 0;
                const installments = parseInt(installmentsInput.value) || 1;

                // Processing Fee: 2% of principal
                if (principal > 0) {
                    processingFeeInput.value = (principal * 0.02).toFixed(2);
                } else {
                    processingFeeInput.value = '';
                }

                // Total Payable: principal + (principal * rate / 100)
                const interest = (principal * rate) / 100;
                const totalPayable = principal + interest;
                totalPayableDisplay.value = totalPayable.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});

                // Installment Amount: totalPayable / installments
                const installmentAmount = totalPayable / (installments || 1);
                installmentAmountDisplay.value = installmentAmount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }

            [loanAmountInput, interestRateInput, installmentsInput].forEach(input => {
                input.addEventListener('input', calculateLoan);
            });

            // Trigger calculation on load if values exist
            calculateLoan();

            // Initialize Select2 if jQuery and select2 are available
            if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
                $('.js-select2').each(function() {
                    $(this).select2({
                        width: '100%',
                        placeholder: $(this).data('placeholder') || 'Select an option',
                        allowClear: true
                    });
                });
            }

            // Document Management
            const addButton = document.getElementById('add-document-btn');
            const wrapper = document.getElementById('loan-documents-wrapper');

            addButton.addEventListener('click', function() {
                const newRow = document.createElement('div');
                newRow.className = 'document-row row g-3 align-items-center';
                newRow.innerHTML = `
                    <div class="col-md-5">
                        <input type="text" name="document_names[]" class="form-control" placeholder="Document Name">
                    </div>
                    <div class="col-md-5">
                        <input type="file" name="loan_documents[]" class="form-control">
                    </div>
                    <div class="col-md-2 text-end">
                        <button type="button" class="btn btn-soft-danger btn-sm w-100 remove-document-btn">
                            <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i> Remove
                        </button>
                    </div>
                `;
                wrapper.appendChild(newRow);
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            });

            wrapper.addEventListener('click', function(e) {
                if (e.target && (e.target.classList.contains('remove-document-btn') || e.target.closest('.remove-document-btn'))) {
                    const btn = e.target.classList.contains('remove-document-btn') ? e.target : e.target.closest('.remove-document-btn');
                    btn.closest('.document-row').remove();
                }
            });
        });
    </script>
@endpush
