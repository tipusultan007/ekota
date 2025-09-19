<div class="alert alert-info">
    {{ __('messages.loan_info_alert') }}
</div>

{{-- Loan Details Section --}}
<h6 class="mb-3">{{ __('messages.loan_details') }}</h6>
<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">{{ __('messages.disburse_from_account') }} <span class="text-danger">*</span></label>
        <select name="account_id" class="form-select @error('account_id') is-invalid @enderror" >
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
        <input type="number" name="loan_amount" class="form-control @error('loan_amount') is-invalid @enderror" value="{{ old('loan_amount') }}">
        @error('loan_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">{{ __('messages.interest_rate') }} (%)</label>
        <input type="number" step="0.01" name="loan_interest_rate" class="form-control @error('loan_interest_rate') is-invalid @enderror" value="{{ old('loan_interest_rate') }}">
        @error('loan_interest_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">{{ __('messages.number_of_installments') }}</label>
        <input type="number" name="number_of_installments" class="form-control @error('number_of_installments') is-invalid @enderror" value="{{ old('number_of_installments') }}">
        @error('number_of_installments') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">{{ __('messages.installment_frequency') }} <span class="text-danger">*</span></label>
        <select name="installment_frequency" class="form-select" >
            <option value="daily">{{ __('messages.daily') }}</option>
            <option value="weekly">{{ __('messages.weekly') }}</option>
            <option value="monthly" selected>{{ __('messages.monthly') }}</option>
        </select>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">{{ __('messages.disbursement_date') }}</label>
        <input type="date" name="disbursement_date" class="form-control @error('disbursement_date') is-invalid @enderror" value="{{ old('disbursement_date', date('Y-m-d')) }}">
        @error('disbursement_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

{{-- Guarantor Section --}}
<h5 class="mt-4 mb-3 border-bottom pb-2">{{ __('messages.guarantor_info') }}</h5>
<div class="mb-3">
    <label class="form-label">{{ __('messages.guarantor_type') }}</label>
    <select name="guarantor_type" id="guarantorType" class="form-select @error('guarantor_type') is-invalid @enderror" >
        <option value="" selected disabled>{{ __('messages.select_type') }}</option>
        <option value="member" {{ old('guarantor_type') == 'member' ? 'selected' : '' }}>{{ __('messages.existing_member') }}</option>
        <option value="outsider" {{ old('guarantor_type') == 'outsider' ? 'selected' : '' }}>{{ __('messages.outsider') }}</option>
    </select>
    @error('guarantor_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div id="memberGuarantor" style="display: {{ old('guarantor_type') == 'member' ? 'block' : 'none' }};">
    <div class="mb-3">
        <label class="form-label">{{ __('messages.select_member_guarantor') }}</label>
        <select name="member_guarantor_id" class="form-select @error('member_guarantor_id') is-invalid @enderror">
            <option value="">{{ __('messages.select_member') }}</option>
            @foreach ($guarantors as $guarantor)
                <option value="{{ $guarantor->id }}" {{ old('member_guarantor_id') == $guarantor->id ? 'selected' : '' }}>{{ $guarantor->name }} (ID: {{ $guarantor->id }})</option>
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
        <div class="col-md-5">
            <input type="text" name="document_names[]" class="form-control" placeholder="{{ __('messages.document_name_placeholder') }}">
        </div>
        <div class="col-md-5">
            <input type="file" name="loan_documents[]" class="form-control">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-sm btn-success" id="add-document-btn">{{ __('messages.add_more') }}</button>
        </div>
    </div>
</div>
