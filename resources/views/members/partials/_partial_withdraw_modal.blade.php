@role('Admin')
<div class="modal fade" id="partialWithdrawModal-{{ $account->id }}" tabindex="-1" aria-labelledby="partialWithdrawModalLabel-{{ $account->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="partialWithdrawModalLabel-{{ $account->id }}">{{ __('messages.process_partial_withdrawal') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('savings.withdraw.partial', $account->id) }}">
                @csrf
                <div class="modal-body">
                    <h6 class="mb-2">{{ __('messages.account_no') }}: <span class="fw-bold">{{ $account->account_no }}</span></h6>
                    <p>{{ __('messages.current_balance') }}: <strong class="text-primary">{{ number_format($account->current_balance, 2) }}</strong> BDT</p>
                    <hr>

                    <div class="mb-3">
                        <label for="withdrawal_amount_{{ $account->id }}" class="form-label">{{ __('messages.withdrawal_amount') }} <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="withdrawal_amount" id="withdrawal_amount_{{ $account->id }}" class="form-control" required max="{{ $account->current_balance }}" placeholder="0.00">
                        @error('withdrawal_amount') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="account_id_{{ $account->id }}_partial" class="form-label">{{ __('messages.payment_from_account') }} <span class="text-danger">*</span></label>
                        <select name="account_id" id="account_id_{{ $account->id }}_partial" class="form-select" required>
                            <option value="">{{ __('messages.select_account') }}</option>
                            @foreach (\App\Models\Account::active()->payment()->get() as $paymentAccount)
                                <option value="{{ $paymentAccount->id }}">{{ $paymentAccount->name }}</option>
                            @endforeach
                        </select>
                        @error('account_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="withdrawal_date_{{ $account->id }}_partial" class="form-label">{{ __('messages.withdrawal_date') }} <span class="text-danger">*</span></label>
                        <input type="date" name="withdrawal_date" id="withdrawal_date_{{ $account->id }}_partial" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="notes_{{ $account->id }}_partial" class="form-label">{{ __('messages.notes') }}</label>
                        <textarea name="notes" id="notes_{{ $account->id }}_partial" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('messages.process_withdrawal') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endrole
