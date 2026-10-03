<div class="modal fade" id="loanCollectionModal" tabindex="-1" aria-labelledby="loanCollectionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="loanCollectionModalLabel">
                    <i data-lucide="banknote" class="icon-sm me-2"></i> {{ __('messages.make_new_loan_collection') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="modal_member_info" class="mb-3 p-3 bg-light rounded text-center">
                    <h6 id="modal_member_name" class="fw-bold mb-1"></h6>
                    <p id="modal_account_no" class="text-muted small mb-0"></p>
                </div>
                
                @include('loan_accounts.partials._collection_form', [
                    'accounts' => $accounts
                ])
            </div>
        </div>
    </div>
</div>
