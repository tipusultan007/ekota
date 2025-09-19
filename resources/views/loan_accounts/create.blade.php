@extends('layout.master')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('members.index') }}">Members</a></li>
            <li class="breadcrumb-item"><a href="{{ route('members.show', $member->id) }}">{{ $member->name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Issue New Loan</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">New Loan Application for {{ $member->name }}</h6>

                    @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

                    <form action="{{ route('members.loan-accounts.store', $member->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- Loan Details Section --}}
                        <h5 class="mb-3 border-bottom pb-2">Loan Details</h5>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Disburse From Account <span class="text-danger">*</span></label>
                                <select name="account_id" class="form-select @error('account_id') is-invalid @enderror" required>
                                    <option value="">Select Account...</option>
                                    {{-- এই $accounts ভেরিয়েবলটি কন্ট্রোলার থেকে পাঠাতে হবে --}}
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}" {{ old('account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }} (Balance: {{ number_format($account->balance) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Loan Amount</label>
                                <input type="number" name="loan_amount" class="form-control @error('loan_amount') is-invalid @enderror" value="{{ old('loan_amount') }}" required>
                                @error('loan_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Interest Rate (%)</label>
                                <input type="number" step="0.01" name="interest_rate" class="form-control @error('interest_rate') is-invalid @enderror" value="{{ old('interest_rate') }}" required>
                                @error('interest_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Number of Installments</label>
                                <input type="number" name="number_of_installments" class="form-control @error('number_of_installments') is-invalid @enderror" value="{{ old('number_of_installments') }}" required>
                                @error('number_of_installments') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Installment Frequency <span class="text-danger">*</span></label>
                                <select name="installment_frequency" class="form-select" required>
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly" selected>Monthly</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Disbursement Date</label>
                                <input type="date" name="disbursement_date" class="form-control @error('disbursement_date') is-invalid @enderror" value="{{ old('disbursement_date', date('Y-m-d')) }}" required>
                                @error('disbursement_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Guarantor Section --}}
                        <h5 class="mt-4 mb-3 border-bottom pb-2">Guarantor Information</h5>
                        <div class="mb-3">
                            <label class="form-label">Guarantor Type</label>
                            <select name="guarantor_type" id="guarantorType" class="form-select @error('guarantor_type') is-invalid @enderror" required>
                                <option value="" selected disabled>-- Select Type --</option>
                                <option value="member" {{ old('guarantor_type') == 'member' ? 'selected' : '' }}>Existing Member</option>
                                <option value="outsider" {{ old('guarantor_type') == 'outsider' ? 'selected' : '' }}>Outside Person</option>
                            </select>
                            @error('guarantor_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div id="memberGuarantor" style="display: {{ old('guarantor_type') == 'member' ? 'block' : 'none' }};">
                            <div class="mb-3">
                                <label class="form-label">Select Member as Guarantor</label>
                                <select name="member_guarantor_id" class="form-select @error('member_guarantor_id') is-invalid @enderror">
                                    <option value="">-- Select Member --</option>
                                    @foreach ($guarantors as $guarantor)
                                        <option value="{{ $guarantor->id }}" {{ old('member_guarantor_id') == $guarantor->id ? 'selected' : '' }}>{{ $guarantor->name }} (ID: {{ $guarantor->id }})</option>
                                    @endforeach
                                </select>
                                @error('member_guarantor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div id="outsiderGuarantor" style="display: {{ old('guarantor_type') == 'outsider' ? 'block' : 'none' }};">
                            <div class="row">
                                <div class="col-md-6 mb-3"><label class="form-label">Name</label><input type="text" name="outsider_name" class="form-control @error('outsider_name') is-invalid @enderror" value="{{ old('outsider_name') }}">@error('outsider_name') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
                                <div class="col-md-6 mb-3"><label class="form-label">Phone</label><input type="text" name="outsider_phone" class="form-control @error('outsider_phone') is-invalid @enderror" value="{{ old('outsider_phone') }}">@error('outsider_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
                                <div class="col-md-12 mb-3"><label class="form-label">Address</label><textarea name="outsider_address" class="form-control @error('outsider_address') is-invalid @enderror">{{ old('outsider_address') }}</textarea>@error('outsider_address') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
                                <div class="col-md-6 mb-3"><label class="form-label">NID Photo</label><input type="file" name="guarantor_nid" class="form-control @error('guarantor_nid') is-invalid @enderror">@error('guarantor_nid') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
                                <div class="col-md-6 mb-3"><label class="form-label">Other Documents (Multiple)</label><input type="file" name="guarantor_documents[]" class="form-control" multiple></div>
                            </div>
                        </div>

                        {{-- Loan Documents Section --}}
                        <h5 class="mt-4 mb-3 border-bottom pb-2">Loan Documents (From Borrower)</h5>
                        <div id="loan-documents-wrapper">
                            <div class="row mb-2 align-items-center">
                                <div class="col-md-5"><input type="text" name="document_names[]" class="form-control" placeholder="Document Name (e.g., Cheque, Land Deed)"></div>
                                <div class="col-md-5"><input type="file" name="loan_documents[]" class="form-control"></div>
                                <div class="col-md-2"><button type="button" class="btn btn-sm btn-success" id="add-document-btn">Add More</button></div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mt-4">Create Loan Account</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const guarantorTypeSelect = document.getElementById('guarantorType');
            const memberDiv = document.getElementById('memberGuarantor');
            const outsiderDiv = document.getElementById('outsiderGuarantor');

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
