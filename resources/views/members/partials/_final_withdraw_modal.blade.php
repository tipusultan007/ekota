@role('Admin')
<div class="modal fade" id="finalWithdrawModal-{{ $account->id }}" tabindex="-1" aria-labelledby="finalWithdrawModalLabel-{{ $account->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="finalWithdrawModalLabel-{{ $account->id }}">{{ __('messages.process_final_withdrawal') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form class="final-withdraw-form" method="POST" action="{{ route('savings.withdraw.final', $account->id) }}">
                @csrf
                <div class="modal-body">
                    <h6 class="mb-2">{{ __('messages.account_no') }}: <span class="fw-bold">{{ $account->account_no }}</span></h6>
                    <p>{{ __('messages.current_balance') }}: <strong class="text-primary current-balance-text">{{ number_format($account->current_balance, 2) }}</strong> BDT</p>
                    <hr>
                    <div class="alert alert-warning">
                        This process will withdraw the <strong>full current balance</strong> and any added profit, and the account will be permanently closed.
                    </div>

                    <div class="mb-3">
                        <label for="profit_amount_input_{{ $account->id }}" class="form-label">{{ __('messages.profit_amount') }} ({{ __('messages.optional') }})</label>
                        <input type="number" step="0.01" name="profit_amount" class="form-control profit-amount-input" placeholder="0.00" id="profit_amount_input_{{ $account->id }}">
                    </div>

                    <div class="mt-3">
                        <h5>{{ __('messages.total_amount_to_pay') }}: <span class="fw-bold text-success total-payable-display"></span> BDT</h5>
                    </div>

                    <div class="mb-3 mt-3">
                        <label for="account_id_{{ $account->id }}_final" class="form-label">{{ __('messages.payment_from_account') }} <span class="text-danger">*</span></label>
                        <select name="account_id" id="account_id_{{ $account->id }}_final" class="form-select" required>
                            <option value="">{{ __('messages.select_account') }}</option>
                            @foreach (\App\Models\Account::active()->payment()->get() as $paymentAccount)
                                <option value="{{ $paymentAccount->id }}">{{ $paymentAccount->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3 mt-3">
                        <label for="withdrawal_date_{{ $account->id }}_final" class="form-label">{{ __('messages.withdrawal_date') }} <span class="text-danger">*</span></label>
                        <input type="date" name="withdrawal_date" id="withdrawal_date_{{ $account->id }}_final" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="notes_{{ $account->id }}_final" class="form-label">{{ __('messages.notes') }}</label>
                        <textarea name="notes" id="notes_{{ $account->id }}_final" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-danger">{{ __('messages.process_final_withdrawal') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endrole
