@extends('layout.master')

@push('plugin-styles')
    <link rel="stylesheet" href="{{ asset('build/plugins/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}">
@endpush

@section('content')
    <div class="row">
        {{-- Main Collection Area --}}
        <div class="col-lg-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-header bg-primary">
                    <h4 class="card-title text-white mb-0">{{ __('messages.collection_entry') }}</h4>
                </div>
                <div class="card-body">
                    <div id="form-message-alert" class="mb-3"></div>
                    {{-- Integrated Collection Form --}}
                    <form action="{{ route('collections.store') }}" method="POST" id="integrated-collection-form">
                        @csrf
                        {{-- Member Selection & Date --}}
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">{{ __('messages.select_member') }} <span
                                        class="text-danger">*</span></label>
                                <select name="member_id" id="member_selector" class="form-select" required>
                                    <option value="">{{ __('messages.search_member') }}</option>
                                    @foreach ($members as $member)
                                        <option value="{{ $member->id }}">{{ $member->name }} (ID: {{ $member->id }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('messages.collection_date') }}</label>
                                <!-- <input type="text" name="date" id="date" class="form-control flatpickr"
                                    value="{{ date('Y-m-d') }}" required> -->
                                    @role('Admin')
                                        <input type="text" name="date" class="form-control flatpickr" 
                                            value="{{ old('date', date('Y-m-d')) }}" required>
                                    @else
                                        <input type="text" class="form-control" 
                                            value="{{ date('d/m/Y') }}" readonly style="background-color: #e9ecef;">
                                        <input type="hidden" name="date" value="{{ date('Y-m-d') }}">
                                    @endrole
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div id="savings_fields_wrapper">
                                    <label class="form-label">{{ __('messages.savings_account') }} <span
                                            class="text-danger">*</span></label>
                                    <select name="savings_account_id" id="savings_account_dropdown" class="form-select mb-2"
                                        disabled>
                                        <option value="">{{ __('messages.select_savings_account') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('messages.deposit') }} <span
                                        class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="amount" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div id="loan_fields_wrapper">
                                    <label class="form-label">{{ __('messages.loan_account') }} <span
                                            class="text-danger">*</span></label>
                                    <select name="loan_account_id" id="loan_account_dropdown" class="form-select mb-2"
                                        disabled>
                                        <option value="">{{ __('messages.select_loan_account') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('messages.loan_installment') }} <span
                                        class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="loan_installment" class="form-control">
                            </div>
                        </div>

                        {{-- Common Fields --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('messages.notes') }}</label>
                                <input name="notes" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('messages.deposit_to_account') }} <span
                                        class="text-danger">*</span></label>
                                <select name="account_id" id="payment_account_id" class="form-select" required>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mb-3">{{ __('messages.submit_collection') }}</button>
                    </form>

                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ __('messages.success') }}: {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ __('messages.error') }}: {{ session('error') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Member Summary Area --}}
        <div class="col-lg-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-header bg-success">
                    <h5 class="card-title mb-0 text-white">{{ __('messages.member_summary') }}</h5>
                </div>
                <div class="card-body">

                    <div id="member_summary_content" class="text-center text-muted mt-4">
                        <p>{{ __('messages.select_member_summary') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ======== Today's Collections List Section (নতুন সেকশন) ======== --}}
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('today_collections') }} ({{ \Carbon\Carbon::today()->format('d M, Y') }})</h5>

                    {{-- ট্যাব নেভিগেশন --}}
                    <ul class="nav nav-tabs nav-tabs-line" id="todayCollectionTab" role="tablist">
                        <li class="nav-item"><a class="nav-link active" id="today-savings-tab" data-bs-toggle="tab"
                                href="#today-savings" role="tab">{{ __('messages.savings') }}</a></li>
                        <li class="nav-item"><a class="nav-link" id="today-loans-tab" data-bs-toggle="tab"
                                href="#today-loans" role="tab">{{ __('messages.loans') }}</a></li>
                    </ul>

                    {{-- ট্যাব কন্টেন্ট --}}
                    <div class="tab-content border border-top-0 p-3" id="todayCollectionTabContent">
                        <div class="tab-pane fade show active" id="today-savings" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.date') }}</th>
                                            <th>{{ __('messages.member') }}</th>
                                            <th class="text-end">{{ __('messages.amount') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="today-savings-table-body">
                                        {{-- AJAX দ্বারা ডেটা লোড হবে --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="today-loans" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.date') }}</th>
                                            <th>{{ __('messages.member') }}</th>
                                            <th class="text-end">{{ __('messages.amount') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="today-loans-table-body">
                                        {{-- AJAX দ্বারা ডেটা লোড হবে --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('plugin-scripts')
    <script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('build/plugins/select2/select2.min.js') }}"></script>
    <script src="{{ asset('build/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Initialize plugins
            $('#member_selector').select2({
                placeholder: "{{ __('messages.search_member') }}",
                width: '100%'
            });
            $(".flatpickr").flatpickr({
                altInput: true,
                dateFormat: "Y-m-d",
                altFormat: "d/m/Y"
            });

            const summaryContent = $('#member_summary_content');
            const savingsDropdown = $('#savings_account_dropdown');
            const loanDropdown = $('#loan_account_dropdown');
            const notesInput = $('#notes_input');

            const memberSelector = $('#member_selector');
            const collectionForm = $('#integrated-collection-form');
            const submitButton = collectionForm.find('button[type="submit"]');
            const originalButtonText = submitButton.html();
            const messageAlert = $('#form-message-alert');

            // Member selection change event
            $('#member_selector').on('change', function() {
                const memberId = $(this).val();
                resetFormsAndSummary();

                if (!memberId) return;

                summaryContent.html(
                    '<div class="spinner-border spinner-border-sm"></div> {{ __('messages.loading') }}'
                );

                $.ajax({
                    url: `/api/members/${memberId}/accounts`,
                    type: 'GET',
                    success: function(response) {
                        // Populate Summary
                        populateSummary(response);

                        // Populate and enable/disable forms
                        populateDropdown(savingsDropdown, response.savings, 'savings');
                        populateDropdown(loanDropdown, response.loans, 'loan');
                    },
                    error: function() {
                        summaryContent.html(
                            '<p class="text-danger">{{ __('messages.failed_load_details') }}</p>'
                        );
                    }
                });
            });



            collectionForm.on('submit', function(e) {
                e.preventDefault(); // ডিফল্ট সাবমিশন সম্পূর্ণরূপে বন্ধ করুন

                // পূর্ববর্তী মেসেজ সরিয়ে দিন
                messageAlert.html('');

                // বাটন নিষ্ক্রিয় করুন এবং লোডিং স্টেট দেখান
                submitButton.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm"></span> Processing...');

                // ফর্মের ডেটা সংগ্রহ করুন
                const formData = $(this).serialize();

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            // সফল বার্তা দেখান
                            messageAlert.html(
                                `<div class="alert alert-success">${response.message}</div>`
                                );

                            // ফর্মটি সম্পূর্ণরূপে রিসেট করুন
                            resetFullForm();

                            // ডানদিকের সামারি এবং নিচের তালিকা রিলোড করুন
                            reloadMemberSummary(); // এটি এখন ফাঁকা হয়ে যাবে
                            loadTodayCollections();
                        }
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON;
                        let errorMessage = 'An unknown error occurred. Please try again.';
                        if (errors && errors.message) {
                            errorMessage = errors.message;
                        }
                        // এরর বার্তা দেখান
                        messageAlert.html(
                            `<div class="alert alert-danger">${errorMessage}</div>`);
                    },
                    complete: function() {
                        // বাটনকে আবার তার আগের অবস্থায় ফিরিয়ে আনুন
                        submitButton.prop('disabled', false).html(originalButtonText);
                    }
                });
            });

            function resetFullForm() {
                // ফর্ম রিসেট করুন
                collectionForm[0].reset();

                // Select2 ড্রপডাউন রিসেট করুন
                memberSelector.val(null).trigger('change');

                // ড্রপডাউনগুলো রিসেট এবং নিষ্ক্রিয় করুন
                $('#savings_account_dropdown').html('<option value="">Select Savings Account...</option>').prop(
                    'disabled', true);
                $('#loan_account_dropdown').html('<option value="">Select Loan Account...</option>').prop(
                    'disabled', true);

                $(".flatpickr").flatpickr({
                    altInput: true,
                    dateFormat: "Y-m-d",
                    altFormat: "d/m/Y",
                    defaultDate: "{{ date('Y-m-d') }}"
                });
            }

            function reloadMemberSummary() {
                // memberSelector-এর 'change' ইভেন্ট ট্রিগার করলেই সামারি রিসেট হয়ে যাবে
                memberSelector.trigger('change');
            }



            const savingsTableBody = $('#today-savings-table-body');
            const loansTableBody = $('#today-loans-table-body');
            const IS_ADMIN = @json(Auth::user()->hasRole('Admin'));

            function loadTodaySavings() {
                // ডাইনামিক হেডার সেট করুন
                $('#today-savings-table-header').html(`
                <tr>
                    <th>Time</th><th>Member</th><th class="text-end">Amount</th>
                    ${IS_ADMIN ? '<th class="text-center">Actions</th>' : ''}
                </tr>
            `);
                savingsTableBody.html(
                    `<tr><td colspan="${IS_ADMIN ? 4 : 3}" class="text-center">Loading...</td></tr>`);

                $.ajax({
                    url: '{{ route('api.collections.today_savings') }}',
                    type: 'GET',
                    success: function(response) {
                        // সার্ভার থেকে আসা রেডিমেড HTML বসিয়ে দিন
                        savingsTableBody.html(response.html);
                        if (IS_ADMIN) lucide.createIcons(); // নতুন আইকন রেন্ডার করুন
                    }
                });
            }

            function loadTodayLoans() {
                // ডাইনামিক হেডার সেট করুন
                $('#today-loans-table-header').html(`
                <tr>
                    <th>Time</th><th>Member</th><th class="text-end">Amount</th>
                    ${IS_ADMIN ? '<th class="text-center">Actions</th>' : ''}
                </tr>
            `);
                loansTableBody.html(
                    `<tr><td colspan="${IS_ADMIN ? 4 : 3}" class="text-center">Loading...</td></tr>`);

                $.ajax({
                    url: '{{ route('api.collections.today_loans') }}',
                    type: 'GET',
                    success: function(response) {
                        // সার্ভার থেকে আসা রেডিমেড HTML বসিয়ে দিন
                        loansTableBody.html(response.html);
                        if (IS_ADMIN) lucide.createIcons();
                    }
                });
            }

            function loadTodayCollections() {
                loadTodaySavings();
                loadTodayLoans();
            }

            // --- পেজ লোড হওয়ার সময় আজকের কালেকশন লোড করুন ---
            loadTodayCollections();

            function resetFormsAndSummary() {
                summaryContent.html('<p>{{ __('messages.select_member_summary') }}</p>');
                savingsDropdown.html('<option value="">{{ __('messages.select_savings_account') }}</option>').prop(
                    'disabled', true);
                loanDropdown.html('<option value="">{{ __('messages.select_loan_account') }}</option>').prop(
                    'disabled', true);
            }

            function populateSummary(data) {
                const member = data.member;

                let html = `
                    <div class="text-center mb-3">
                        <img src="${member.photo_url}" class="rounded-circle" width="80" height="80" alt="Member Photo">
                    </div>
                    <h6 class="text-center">${member.name}</h6>
                    <p class="text-muted text-center small mb-3">
                        ${member.phone}<br>
                        ${member.address}
                    </p>
                `;

                html += '<hr>';
                html += '<strong>{{ __('messages.savings_accounts') }}</strong>';
                if (data.savings.length > 0) {
                    html += '<ul class="list-group list-group-flush">';
                    data.savings.forEach(acc => {
                        html += `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <small>${acc.account_no}</small><br>
                        <small class="text-muted">${acc.scheme_type}</small>
                    </div>
                    <span class="fw-bold">${parseFloat(acc.current_balance).toFixed(2)}</span>
                </li>`;
                    });
                    html += '</ul>';
                } else {
                    html += '<p class="small text-muted ps-2">{{ __('messages.no_savings') }}</p>';
                }

                html += '<hr>';
                html += '<strong>{{ __('messages.loan_accounts') }}</strong>';
                if (data.loans.length > 0) {
                    html += '<ul class="list-group list-group-flush">';
                    data.loans.forEach(acc => {
                        const due = parseFloat(acc.total_payable) - parseFloat(acc.total_paid);
                        html += `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <small>${acc.account_no}</small><br>
                        <small class="text-muted">{{ __('messages.installment') }}: ${parseFloat(acc.installment_amount).toFixed(2)}</small>
                    </div>
                    <span class="text-danger fw-bold">{{ __('messages.due') }}: ${due.toFixed(2)}</span>
                </li>`;
                    });
                    html += '</ul>';
                } else {
                    html += '<p class="small text-muted ps-2">{{ __('messages.no_loans') }}</p>';
                }

                summaryContent.html(html);
            }

            function populateDropdown(selectElement, accounts, type) {
                selectElement.prop('disabled', accounts.length === 0);
                if (accounts.length > 0) {
                    selectElement.empty().append('<option value="">{{ __('messages.select_account') }}</option>');
                    accounts.forEach(acc => {
                        let text = (type === 'savings') ?
                            `${acc.account_no} (${acc.scheme_type})` :
                            `${acc.account_no} ({{ __('messages.due') }}: ${(parseFloat(acc.total_payable) - parseFloat(acc.total_paid)).toFixed(2)})`;
                        selectElement.append(new Option(text, acc.id));
                    });
                }
            }
        });
    </script>
@endpush
