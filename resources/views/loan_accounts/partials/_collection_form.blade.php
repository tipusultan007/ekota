<form action="{{ route('collections.store') }}" method="POST" id="loanCollectionForm">
    @csrf
    <input type="hidden" name="member_id" id="form_member_id" value="{{ $member_id ?? ($loanAccount->member_id ?? '') }}">
    <input type="hidden" name="loan_account_id" id="form_loan_account_id" value="{{ $loanAccount->id ?? '' }}">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label mb-1">{{ __('messages.payment_date') }}</label>
            @role('Admin')
                <input type="text" name="date" class="form-control flatpickr form-control-premium" value="{{ date('Y-m-d') }}" required>
            @else
                <input type="text" name="date_display" class="form-control form-control-premium" value="{{ date('d/m/Y') }}" readonly>
                <input type="hidden" name="date" value="{{ date('Y-m-d') }}">
            @endrole
        </div>
        <div class="col-md-6">
            <label class="form-label mb-1">{{ __('messages.installment_amount') }} <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text">৳</span>
                <input type="number" step="0.01" name="loan_installment" class="form-control form-control-premium text-end fw-bold paid_amount_input" value="{{ $installment_amount ?? '' }}" required>
            </div>
        </div>
        <div class="col-md-6 grace_amount_wrapper">
            <label class="form-label mb-1">{{ __('messages.grace_amount') }}</label>
            <div class="input-group">
                <span class="input-group-text">৳</span>
                <input type="number" step="0.01" name="grace_amount" class="form-control form-control-premium text-end grace_amount_input" placeholder="0.00">
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label mb-1">{{ __('messages.deposit_to') }} <span class="text-danger">*</span></label>
            <select name="account_id" class="form-select" required>
                @foreach ($accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-12">
            <label class="form-label mb-1">{{ __('messages.notes') }}</label>
            <textarea name="notes" class="form-control form-control-premium" rows="2"></textarea>
        </div>
    </div>
    <div class="d-grid mt-4">
        <button type="submit" class="btn btn-submit shadow-lg">{{ __('messages.submit_installment') }}</button>
    </div>
</form>
