@extends('layout.master')

@section('content')

    <div class="row">
        {{-- Loan Application Form Section --}}
        <div class="col-lg-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">{{ __('messages.new_loan_application') }}</h6>
                    @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
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
                            <select name="member_id" id="member_id" class="form-select" required>
                                <option value="">{{ __('messages.search_select_member') }}</option>
                                @foreach ($members as $member)
                                    <option value="{{ $member->id }}" {{ old('member_id') == $member->id ? 'selected' : '' }}>
                                        {{ $member->name }} ({{ __('messages.id') }}: {{ $member->id }})
                                    </option>
                                @endforeach
                            </select>
                            @error('member_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        {{-- Loan Details Section --}}
                        <h5 class="mb-3 border-bottom pb-2">{{ __('messages.loan_details') }}</h5>
                        <div class="row">
                            <div class="col-md-4 mb-3">
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
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('messages.loan_amount') }}</label>
                                <input type="number" name="loan_amount" id="loan_amount" class="form-control @error('loan_amount') is-invalid @enderror" value="{{ old('loan_amount') }}" required>
                                @error('loan_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('messages.interest_rate') }} (%)</label>
                                <input type="number" step="0.01" name="interest_rate" id="interest_rate" class="form-control @error('interest_rate') is-invalid @enderror" value="{{ old('interest_rate') }}" required>
                                @error('interest_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('messages.number_of_installments') }}</label>
                                <input type="number" name="number_of_installments" id="number_of_installments" class="form-control @error('number_of_installments') is-invalid @enderror" value="{{ old('number_of_installments') }}" required>
                                @error('number_of_installments') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('messages.installment_amount') }}</label>
                                <input type="text" id="calculated_installment" class="form-control" readonly style="background-color: #e9ecef;">
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
                                <input type="text" name="disbursement_date" class="form-control flatpickr @error('disbursement_date') is-invalid @enderror" value="{{ old('disbursement_date', date('Y-m-d')) }}" required>
                                @error('disbursement_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Guarantor Section --}}
                        <h5 class="mt-4 mb-3 border-bottom pb-2">{{ __('messages.guarantor_information') }}</h5>
                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.guarantor_type') }}</label>
                            <select name="guarantor_type" id="guarantorType" class="form-select @error('guarantor_type') is-invalid @enderror" required>
                                <option value="" selected disabled>-- {{ __('messages.select_type') }} --</option>
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
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ __('messages.name') }}</label>
                                    <input type="text" name="outsider_name" class="form-control @error('outsider_name') is-invalid @enderror" value="{{ old('outsider_name') }}">
                                    @error('outsider_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ __('messages.phone') }}</label>
                                    <input type="text" name="outsider_phone" class="form-control @error('outsider_phone') is-invalid @enderror" value="{{ old('outsider_phone') }}">
                                    @error('outsider_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">{{ __('messages.address') }}</label>
                                    <textarea name="outsider_address" class="form-control @error('outsider_address') is-invalid @enderror">{{ old('outsider_address') }}</textarea>
                                    @error('outsider_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ __('messages.nid_photo') }}</label>
                                    <input type="file" name="guarantor_nid" class="form-control @error('guarantor_nid') is-invalid @enderror">
                                    @error('guarantor_nid') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ __('messages.other_documents') }}</label>
                                    <input type="file" name="guarantor_documents[]" class="form-control" multiple>
                                </div>
                            </div>
                        </div>

                        {{-- Loan Documents Section --}}
                        <h5 class="mt-4 mb-3 border-bottom pb-2">{{ __('messages.loan_documents') }}</h5>
                        <div id="loan-documents-wrapper">
                            <div class="row mb-2 align-items-center">
                                <div class="col-md-5"><input type="text" name="document_names[]" class="form-control" placeholder="{{ __('messages.document_name_placeholder') }}"></div>
                                <div class="col-md-5"><input type="file" name="loan_documents[]" class="form-control"></div>
                                <div class="col-md-2"><button type="button" class="btn btn-sm btn-success" id="add-document-btn">{{ __('messages.add_more') }}</button></div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mt-4">{{ __('messages.create_loan_account') }}</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Member Summary Area --}}
        <div class="col-lg-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('messages.member_summary') }}</h5>
                    <div id="member_summary_content" class="text-center text-muted mt-4">
                        <p>{{ __('messages.select_member_to_view_summary') }}</p>
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

            function calculateInstallment() {
                const loanAmount = parseFloat(loanAmountInput.val()) || 0;
                const interestRate = parseFloat(interestRateInput.val()) || 0;
                const installments = parseInt(installmentsInput.val()) || 0;

                if (loanAmount > 0 && interestRate >= 0 && installments > 0) {
                    const interest = (loanAmount * interestRate) / 100;
                    const totalPayable = loanAmount + interest;
                    const installmentAmount = totalPayable / installments;

                    // গণনাকৃত মানটি read-only ফিল্ডে দেখান
                    calculatedInstallmentDisplay.val(installmentAmount.toFixed(2));
                } else {
                    calculatedInstallmentDisplay.val(''); // যদি কোনো মান না থাকে তাহলে খালি রাখুন
                }
            }

            // তিনটি ইনপুট ফিল্ডের যেকোনো একটি পরিবর্তন হলেই গণনা আবার চালান
            loanAmountInput.on('input', calculateInstallment);
            interestRateInput.on('input', calculateInstallment);
            installmentsInput.on('input', calculateInstallment);


        });
    </script>
@endpush
