@extends('layout.master')
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
            background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
            padding: 1.5rem 2rem;
            position: relative;
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
            font-size: 1.1rem;
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
        .section-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1rem;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 0.5rem;
        }
        .btn-submit {
            background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
            border: none;
            border-radius: 0.75rem;
            padding: 0.75rem 2rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(225, 29, 72, 0.3);
            color: white;
        }
    </style>
@endpush
@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('loan_accounts.show', $loanAccount->id) }}">{{ __('messages.loan_details') }}</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('messages.edit_loan_account') }}</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-md-10 grid-margin stretch-card">
            <div class="card premium-card">
                <div class="premium-header">
                    <h5 class="premium-title">
                        <i data-lucide="edit-3" class="icon-sm me-2"></i> {{ __('messages.edit_loan_account') }}: {{ $loanAccount->account_no }}
                    </h5>
                </div>
                <div class="card-body p-4">
                    @if (session('error'))
                        <div class="alert alert-danger shadow-sm border-0 rounded-3 mb-4">{{ session('error') }}</div>
                    @endif
                    
                    <form action="{{ route('loan-accounts.update', $loanAccount->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Loan Details Section --}}
                        <div class="mb-5">
                            <h6 class="section-title text-primary"><i data-lucide="file-text" class="icon-xs me-1"></i> {{ __('messages.loan_details') }}</h6>
                            
                            <div class="alert alert-warning shadow-sm border-0 rounded-3 mb-4 d-flex align-items-center">
                                <i data-lucide="alert-circle" class="icon-sm me-2"></i>
                                <span><strong>{{ __('messages.warning') }}:</strong> {{ __('messages.edit_loan_warning') }}</span>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label mb-1">{{ __('messages.disburse_from_account') }} <span class="text-danger">*</span></label>
                                    <select name="account_id" class="form-select" required>
                                        @php
                                            $disbursementAccountId = null;
                                            // Get the first transaction (likely disbursement)
                                            $transaction = $loanAccount->transactions->first(); 
                                            if ($transaction) {
                                                // Find the credit entry (Cash Out from our account)
                                                // Using collection filtering on loaded relation
                                                $entry = $transaction->journalEntries->where('credit', '>', 0)->first();
                                                $disbursementAccountId = $entry ? $entry->account_id : null;
                                            }
                                        @endphp
                                        @foreach ($accounts as $account)
                                            <option value="{{ $account->id }}" {{ old('account_id', $disbursementAccountId) == $account->id ? 'selected' : '' }}>
                                                {{ $account->name }} (Balance: {{ number_format($account->balance) }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('account_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label mb-1">{{ __('messages.loan_amount') }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">৳</span>
                                        <input type="number" step="0.01" name="loan_amount" class="form-control form-control-premium @error('loan_amount') is-invalid @enderror" value="{{ old('loan_amount', $loanAccount->loan_amount) }}" required>
                                    </div>
                                    @error('loan_amount') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label mb-1">{{ __('messages.disbursement_date') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="disbursement_date" class="form-control flatpickr form-control-premium" value="{{ old('disbursement_date', $loanAccount->disbursement_date->format('Y-m-d')) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="status" class="form-label mb-1">{{ __('messages.status') }} <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="running" {{ $loanAccount->status == 'running' ? 'selected' : '' }}>{{ __('messages.running') }}</option>
                                        <option value="paid" {{ $loanAccount->status == 'paid' ? 'selected' : '' }}>{{ __('messages.paid') }}</option>
                                        <option value="defaulted" {{ $loanAccount->status == 'defaulted' ? 'selected' : '' }}>{{ __('messages.defaulted') }}</option>
                                    </select>
                                    @error('status') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label mb-1">{{ __('messages.interest_rate') }} (%) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="interest_rate" class="form-control form-control-premium" value="{{ old('interest_rate', $loanAccount->interest_rate) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label mb-1">{{ __('messages.number_of_installments') }} <span class="text-danger">*</span></label>
                                    <input type="number" name="number_of_installments" class="form-control form-control-premium" value="{{ old('number_of_installments', $loanAccount->number_of_installments) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label mb-1">{{ __('messages.installment_frequency') }} <span class="text-danger">*</span></label>
                                    <select name="installment_frequency" class="form-select" required>
                                        <option value="daily" {{ $loanAccount->installment_frequency == 'daily' ? 'selected' : '' }}>{{ __('messages.daily') }}</option>
                                        <option value="weekly" {{ $loanAccount->installment_frequency == 'weekly' ? 'selected' : '' }}>{{ __('messages.weekly') }}</option>
                                        <option value="monthly" {{ $loanAccount->installment_frequency == 'monthly' ? 'selected' : '' }}>{{ __('messages.monthly') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Guarantor Section --}}
                        <div class="mb-5">
                            <h6 class="section-title text-primary"><i data-lucide="users" class="icon-xs me-1"></i> {{ __('messages.guarantor_information') }}</h6>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label mb-1">{{ __('messages.guarantor_type') }}</label>
                                    <select name="guarantor_type" id="guarantorType" class="form-select" required>
                                        <option value="member" {{ ($loanAccount->guarantor && $loanAccount->guarantor->member_id) ? 'selected' : '' }}>{{ __('messages.existing_member') }}</option>
                                        <option value="outsider" {{ ($loanAccount->guarantor && !$loanAccount->guarantor->member_id) ? 'selected' : '' }}>{{ __('messages.outside_person') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <div id="memberGuarantor" style="display: {{ ($loanAccount->guarantor && $loanAccount->guarantor->member_id) ? 'block' : 'none' }};">
                                        <label class="form-label mb-1">{{ __('messages.select_member') }}</label>
                                        <select name="member_guarantor_id" class="form-select js-example-basic-single">
                                            @foreach ($guarantors as $guarantor)
                                                <option value="{{ $guarantor->id }}" {{ old('member_guarantor_id', $loanAccount->guarantor->member_id ?? '') == $guarantor->id ? 'selected' : '' }}>{{ $guarantor->name }} (ID: {{ $guarantor->id }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div id="outsiderGuarantor" style="display: {{ ($loanAccount->guarantor && !$loanAccount->guarantor->member_id) ? 'block' : 'none' }};">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label mb-1">{{ __('messages.name') }}</label>
                                                <input type="text" name="outsider_name" class="form-control form-control-premium" value="{{ old('outsider_name', $loanAccount->guarantor->name ?? '') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label mb-1">{{ __('messages.phone') }}</label>
                                                <input type="text" name="outsider_phone" class="form-control form-control-premium" value="{{ old('outsider_phone', $loanAccount->guarantor->phone ?? '') }}">
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label mb-1">{{ __('messages.address') }}</label>
                                                <textarea name="outsider_address" class="form-control form-control-premium" rows="2">{{ old('outsider_address', $loanAccount->guarantor->address ?? '') }}</textarea>
                                            </div>
                                            <div class="col-md-12">
                                                 <label class="form-label mb-1">{{ __('messages.guarantor_documents') }} (NID, etc)</label>
                                                 <input type="file" name="guarantor_documents[]" class="form-control form-control-premium" multiple>
                                                 
                                                  @if($loanAccount->guarantor && $loanAccount->guarantor->getMedia('guarantor_documents')->count() > 0)
                                                    <div class="mt-2">
                                                        <small class="text-muted fw-bold">Existing Guarantor Documents:</small>
                                                        <ul class="list-unstyled mt-1">
                                                            @foreach($loanAccount->guarantor->getMedia('guarantor_documents') as $media)
                                                                <li>
                                                                    <a href="{{ $media->getUrl() }}" target="_blank" class="text-primary text-decoration-none">
                                                                        <i data-lucide="file" class="icon-xs me-1"></i> {{ $media->file_name }}
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Loan Documents Section --}}
                        <div class="mb-5">
                            <h6 class="section-title text-primary"><i data-lucide="file-text" class="icon-xs me-1"></i> {{ __('messages.loan_documents') }}</h6>
                            
                            @if($loanAccount->getMedia('loan_documents')->count() > 0)
                                <div class="mb-3">
                                    <label class="form-label mb-2 fw-bold text-muted small text-uppercase">{{ __('messages.existing_documents') }}</label>
                                    <div class="row g-2">
                                        @foreach($loanAccount->getMedia('loan_documents') as $media)
                                            <div class="col-md-6">
                                                <div class="p-2 border rounded d-flex align-items-center justify-content-between bg-light">
                                                    <a href="{{ $media->getUrl() }}" target="_blank" class="text-decoration-none text-dark d-flex align-items-center">
                                                        <i data-lucide="file" class="icon-xs me-2 text-primary"></i>
                                                        <span class="text-truncate" style="max-width: 200px;">{{ $media->getCustomProperty('document_name', $media->name) }}</span>
                                                    </a>
                                                    <div class="form-check form-switch m-0">
                                                        <input class="form-check-input" type="checkbox" name="existing_documents_to_delete[]" value="{{ $media->id }}" id="media-{{ $media->id }}">
                                                        <label class="form-check-label text-danger small fw-bold" for="media-{{ $media->id }}">{{ __('messages.delete') }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <label class="form-label mb-2 fw-bold text-muted small text-uppercase mt-2">{{ __('messages.add_new_documents') }}</label>
                            <div id="loan-documents-wrapper">
                                <div class="row g-2 mb-2 align-items-center">
                                    <div class="col-md-5">
                                        <input type="text" name="document_names[]" class="form-control form-control-premium" placeholder="{{ __('messages.document_name') }}">
                                    </div>
                                    <div class="col-md-5">
                                        <input type="file" name="loan_documents[]" class="form-control form-control-premium">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-sm btn-success w-100" id="add-document-btn">
                                            <i data-lucide="plus" class="icon-xs"></i> {{ __('messages.add_more') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                             <a href="{{ route('loan_accounts.show', $loanAccount->id) }}" class="btn btn-light border">{{ __('messages.cancel') }}</a>
                            <button type="submit" class="btn btn-submit shadow-lg px-5">{{ __('messages.update_loan_account') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-scripts')
    <script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('build/plugins/select2/select2.min.js') }}"></script>
    <script src="{{ asset('build/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const guarantorTypeSelect = document.getElementById('guarantorType');
            const memberDiv = document.getElementById('memberGuarantor');
            const outsiderDiv = document.getElementById('outsiderGuarantor');

            if (typeof flatpickr !== 'undefined') {
                $(".flatpickr").flatpickr({ altInput: true, dateFormat: 'Y-m-d', altFormat: 'd/m/Y' });
            }
            
            // Initialize Select2 specifically for the member guarantor dropdown
            $('.js-example-basic-single').select2({
                 width: '100%',
                 placeholder: "{{ __('messages.select_member') }}",
                 allowClear: true
            });


            function toggleGuarantorFields() {
                if (guarantorTypeSelect.value === 'member') {
                    memberDiv.style.display = 'block';
                    outsiderDiv.style.display = 'none';
                } else if (guarantorTypeSelect.value === 'outsider') {
                    memberDiv.style.display = 'none';
                    outsiderDiv.style.display = 'block';
                } else {
                    memberDiv.style.display = 'none';
                    outsiderDiv.style.display = 'none';
                }
            }

            guarantorTypeSelect.addEventListener('change', toggleGuarantorFields);

            // Initial call to set state on page load (for validation errors)
            toggleGuarantorFields();
        });


        document.getElementById('add-document-btn').addEventListener('click', function() {
            const wrapper = document.getElementById('loan-documents-wrapper');
            const newRow = document.createElement('div');
            newRow.className = 'row g-2 mb-2 align-items-center';
            newRow.innerHTML = `
        <div class="col-md-5"><input type="text" name="document_names[]" class="form-control form-control-premium" placeholder="{{ __('messages.document_name') }}"></div>
        <div class="col-md-5"><input type="file" name="loan_documents[]" class="form-control form-control-premium"></div>
        <div class="col-md-2"><button type="button" class="btn btn-sm btn-danger w-100 remove-document-btn"><i data-lucide="trash-2" class="icon-xs"></i></button></div>
    `;
            wrapper.appendChild(newRow);
             // Re-initialize icons for the new row
             if (window.lucide) {
                window.lucide.createIcons();
            }
        });

        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('remove-document-btn')) {
                e.target.closest('.row').remove();
            }
        });
    </script>
@endpush
