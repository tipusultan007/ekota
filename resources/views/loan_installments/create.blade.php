@extends('layout.master')

@push('plugin-styles')
    <link rel="stylesheet" href="{{ asset('build/plugins/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}">
@endpush

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('messages.loan_installment_collection') }}</li>
        </ol>
    </nav>
    @if (session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        {{-- Collection Form Section --}}
        <div class="col-lg-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('messages.make_new_loan_collection') }}</h5>
                    <form action="{{ route('loan-installments.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">{{ __('messages.select_member_and_loan_account') }} <span class="text-danger">*</span></label>
                                <select name="loan_account_id" id="loan_account_id" class="form-select" required>
                                    <option value="">-- {{ __('messages.select') }} --</option>
                                    @foreach ($members as $member)
                                        <optgroup label="{{ $member->name }} (ID: {{ $member->id }})">
                                            @foreach ($member->loanAccounts as $account)
                                                <option value="{{ $account->id }}" data-due="{{ $account->total_payable - $account->total_paid }}">{{ $account->account_no }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('messages.payment_date') }}</label>
                                <input type="text" name="payment_date" class="form-control flatpickr" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label class="form-label">{{ __('messages.amount') }} <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="paid_amount" id="paid_amount_input" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3" id="grace_amount_wrapper">
                                <label class="form-label">Grace Amount</label>
                                <input type="number" step="0.01" name="grace_amount" id="grace_amount_input" class="form-control" placeholder="0.00">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Deposit To <span class="text-danger">*</span></label>
                                <select name="account_id" class="form-select" required>
                                    @foreach ($accounts as $account) <option value="{{ $account->id }}">{{ $account->name }}</option> @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.notes') }}</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">{{ __('messages.submit_installment') }}</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Account & Member Summary Area --}}
        <div class="col-lg-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Summary</h5>
                    <div id="summary_content" class="text-center text-muted mt-4">
                        <p>Select a loan account to view details.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Collections List Section --}}
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="card-title">{{ __('messages.recent_loan_collections') }}</h6>
                        <a href="{{ route('loan-installments.index') }}" class="btn btn-secondary btn-sm">
                            {{ __('messages.view_all_loan_collections') }}
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>{{ __('messages.receipt_no') }}</th>
                                <th>{{ __('messages.member_name') }}</th>
                                <th>{{ __('messages.account_no') }}</th>
                                <th class="text-end">{{ __('messages.amount') }}</th>
                                <th class="text-end">{{ __('messages.grace') }}</th>
                                <th>{{ __('messages.date') }}</th>
                                <th>{{ __('messages.collected_by') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($recentInstallments as $installment)
                                <tr>
                                    <td>{{ $installment->id }}</td>
                                    <td>{{ $installment->member->name }}</td>
                                    <td>{{ $installment->loanAccount->account_no }}</td>
                                    <td class="text-end">{{ number_format($installment->paid_amount, 2) }}</td>
                                    <td class="text-end">{{ number_format($installment->grace_amount, 2) }}</td>
                                    <td>{{ $installment->payment_date->format('d M, Y') }}</td>
                                    <td>{{ $installment->collector->name }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">{{ __('messages.no_recent_installments_found') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('plugin-scripts')
    {{-- আপনার টেমপ্লেটে যদি jQuery আগে থেকেই লোড করা থাকে, তাহলে নিচের লাইনটি বাদ দিতে পারেন --}}
    <script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('build/plugins/select2/select2.min.js') }}"></script>
    <script src="{{ asset('build/plugins/flatpickr/flatpickr.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            if ($("#loan_account_id").length) {
                $("#loan_account_id").select2({
                    placeholder: "{{ __('messages.search_and_select_account') }}",
                    width: '100%',
                });
            }

            $(".flatpickr").flatpickr({
                altInput: true,
                dateFormat: "Y-m-d",
                altFormat: "d/m/Y"
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            const loanAccountSelect = $("#loan_account_id");
            const summaryContent = $("#summary_content");
            const paidAmountInput = $('#paid_amount_input');
            const graceAmountWrapper = $('#grace_amount_wrapper');
            let currentDue = 0;

            loanAccountSelect.select2({
                placeholder: "{{ __('messages.search_and_select_account') }}",
                width: '100%',
            });

            $(".flatpickr").flatpickr({
                altInput: true,
                dateFormat: "Y-m-d",
                altFormat: "d/m/Y"
            });
            loanAccountSelect.on('change', function() {
                const accountId = $(this).val();
                const selectedOption = $(this).find('option:selected');
                currentDue = parseFloat(selectedOption.data('due') || 0);

                if (!accountId) { /* reset summary */ return; }
                summaryContent.html('<div class="spinner-border spinner-border-sm"></div>');

                $.ajax({
                    url: `/api/loan-accounts/${accountId}/details`,
                    type: 'GET',
                    success: function(response) {
                        const member = response.member;
                        const due = parseFloat(response.total_payable) - parseFloat(response.total_paid) - parseFloat(response.grace_amount);
                        let html = `
  <div class="text-center mb-3">
                            <img src="${member.photo_url}" class="rounded-circle" width="80" height="80" alt="Member Photo" style="object-fit: cover;">
                        </div>
                        <h6 class="text-center">${member.name}</h6>
                        <p class="text-muted text-center small mb-3">
                            <i data-lucide="phone" class="icon-sm me-1"></i> ${member.mobile_no}<br>
                            <i data-lucide="map-pin" class="icon-sm me-1"></i> ${member.address}
                        </p>                        <hr>
                        <h6 class="mb-3">Loan Details</h6>
                        <dl class="row">
                            <dt class="col-sm-5">Account No</dt><dd class="col-sm-7">${response.account_no}</dd>
                            <dt class="col-sm-5">Payable</dt><dd class="col-sm-7">${parseFloat(response.total_payable).toFixed(2)}</dd>
                            <dt class="col-sm-5">Paid</dt><dd class="col-sm-7">${parseFloat(response.total_paid).toFixed(2)}</dd>
                            <dt class="col-sm-5">Due</dt><dd class="col-sm-7 fw-bold text-danger">${due.toFixed(2)}</dd>
                            <dt class="col-sm-5">Installment</dt><dd class="col-sm-7">${parseFloat(response.installment_amount).toFixed(2)}</dd>
                        </dl>
                    `;
                        summaryContent.html(html);
                        paidAmountInput.val(response.installment_amount.toFixed(2));
                        paidAmountInput.trigger('input'); // Trigger input event to check for grace
                    }
                });
            });

            paidAmountInput.on('input', function() {
                const paidAmount = parseFloat($(this).val()) || 0;
                if (paidAmount >= currentDue && currentDue > 0) {
                    graceAmountWrapper.slideDown();
                } else {
                    graceAmountWrapper.slideUp();
                    graceAmountWrapper.find('input').val('');
                }
            });
        });
    </script>
@endpush
