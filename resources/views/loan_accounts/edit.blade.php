@extends('layout.master')
@push('plugin-styles')
    <link rel="stylesheet" href="{{ asset('build/plugins/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}">
@endpush
@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('loan_accounts.show', $loanAccount->id) }}">Loan Details</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Edit Loan Account</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Edit Loan Account: {{ $loanAccount->account_no }}</h6>
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    <form action="{{ route('loan-accounts.update', $loanAccount->id) }}" method="POST"
                          enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Loan Details Section --}}
                        <div class="border p-3 mb-4 rounded">
                            <h5 class="mb-3">Loan Details</h5>
                            <div class="alert alert-warning"><strong>Warning:</strong> Changing these values will
                                recalculate the total payable and installment amount.
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Disburse From Account <span class="text-danger">*</span></label>
                                    <select name="account_id" class="form-select @error('account_id') is-invalid @enderror" required>
                                        @php
                                            $disbursementTransaction = $loanAccount->transactions()->where('type', 'debit')->first();
                                        @endphp
                                        @foreach ($accounts as $account)
                                            <option value="{{ $account->id }}" {{ old('account_id', $disbursementTransaction->account_id ?? '') == $account->id ? 'selected' : '' }}>
                                                {{ $account->name }} (Balance: {{ number_format($account->balance) }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Loan Amount <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="loan_amount" class="form-control @error('loan_amount') is-invalid @enderror" value="{{ old('loan_amount', $loanAccount->loan_amount) }}" required>
                                    @error('loan_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Disbursement Date <span class="text-danger">*</span></label>
                                    <input type="text" name="disbursement_date" class="form-control flatpickr" value="{{ old('disbursement_date', $loanAccount->disbursement_date->format('Y-m-d')) }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select" required>
                                        <option value="running" {{ $loanAccount->status == 'running' ? 'selected' : '' }}>Running</option>
                                        <option value="paid" {{ $loanAccount->status == 'paid' ? 'selected' : '' }}>Paid</option>
                                        <option value="defaulted" {{ $loanAccount->status == 'defaulted' ? 'selected' : '' }}>Defaulted</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Interest Rate (%) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="interest_rate" class="form-control" value="{{ old('interest_rate', $loanAccount->interest_rate) }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Number of Installments <span class="text-danger">*</span></label>
                                    <input type="number" name="number_of_installments" class="form-control" value="{{ old('number_of_installments', $loanAccount->number_of_installments) }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Installment Frequency <span class="text-danger">*</span></label>
                                    <select name="installment_frequency" class="form-select" required>
                                        <option value="daily" {{ $loanAccount->installment_frequency == 'daily' ? 'selected' : '' }}>Daily</option>
                                        <option value="weekly" {{ $loanAccount->installment_frequency == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                        <option value="monthly" {{ $loanAccount->installment_frequency == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Guarantor Section --}}
                        <div class="border p-3 mb-4 rounded">
                            <h5 class="mb-3">Guarantor Information</h5>
                            <div class="mb-3"><label class="form-label">Guarantor Type</label><select
                                    name="guarantor_type" id="guarantorType" class="form-select" required>
                                    <option
                                        value="member" {{ ($loanAccount->guarantor && $loanAccount->guarantor->member_id) ? 'selected' : '' }}>
                                        Existing Member
                                    </option>
                                    <option
                                        value="outsider" {{ ($loanAccount->guarantor && !$loanAccount->guarantor->member_id) ? 'selected' : '' }}>
                                        Outside Person
                                    </option>
                                </select></div>
                            <div id="memberGuarantor"
                                 style="display: {{ ($loanAccount->guarantor && $loanAccount->guarantor->member_id) ? 'block' : 'none' }};">
                                <div class="mb-3"><label class="form-label">Select Member</label><select
                                        name="member_guarantor_id" class="form-select">
                                        @foreach ($guarantors as $guarantor)
                                            <option value="{{ $guarantor->id }}" {{ old('member_guarantor_id') == $guarantor->id ? 'selected' : '' }}>{{ $guarantor->name }} (ID: {{ $guarantor->id }})</option>
                                        @endforeach
                                    </select></div>
                            </div>
                            <div id="outsiderGuarantor"
                                 style="display: {{ ($loanAccount->guarantor && !$loanAccount->guarantor->member_id) ? 'block' : 'none' }};">
                                <div class="row">
                                    <div class="col-md-6 mb-3"><label class="form-label">Name</label><input type="text"
                                                                                                            name="outsider_name"
                                                                                                            class="form-control"
                                                                                                            value="{{ old('outsider_name', $loanAccount->guarantor->name ?? '') }}">
                                    </div>
                                    <div class="col-md-6 mb-3"><label class="form-label">Phone</label><input type="text"
                                                                                                             name="outsider_phone"
                                                                                                             class="form-control"
                                                                                                             value="{{ old('outsider_phone', $loanAccount->guarantor->phone ?? '') }}">
                                    </div>
                                    {{-- ... outsider fields ... --}}
                                </div>
                            </div>
                        </div>

                        {{-- Loan Documents Section --}}
                        <div class="border p-3 mb-4 rounded">
                            <h5 class="mb-3">Loan Documents (From Borrower)</h5>
                            <h6>Existing Documents:</h6>
                            @if($loanAccount->getMedia('loan_documents')->count() > 0)
                                @foreach($loanAccount->getMedia('loan_documents') as $media)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               name="existing_documents_to_delete[]" value="{{ $media->id }}"
                                               id="media-{{ $media->id }}">
                                        <label class="form-check-label" for="media-{{ $media->id }}">
                                            Delete: <a href="{{ $media->getUrl() }}"
                                                       target="_blank">{{ $media->getCustomProperty('document_name', $media->name) }}</a>
                                        </label>
                                    </div>
                                @endforeach
                            @else
                                <p>No existing documents.</p>
                            @endif

                            <h6 class="mt-4">Add New Documents:</h6>
                            <div id="loan-documents-wrapper">
                                <div class="row mb-2 align-items-center">
                                    <div class="col-md-5"><input type="text" name="document_names[]"
                                                                 class="form-control" placeholder="Document Name"></div>
                                    <div class="col-md-5"><input type="file" name="loan_documents[]"
                                                                 class="form-control"></div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-sm btn-success" id="add-document-btn">Add
                                            More
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Update Loan Account</button>
                            <a href="{{ route('loan_accounts.show', $loanAccount->id) }}" class="btn btn-secondary">Cancel</a>
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

            $(".flatpickr").flatpickr({ altInput: true, dateFormat: 'Y-m-d', altFormat: 'd/m/Y' });


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
@endpush
