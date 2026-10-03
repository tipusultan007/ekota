@extends('layout.master')

@push('plugin-styles')
    <link rel="stylesheet" href="{{ asset('build/plugins/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}">
    <style>
        /* Premium Card Design */
        .card-premium {
            border: none;
            border-radius: 1.25rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            background: #fff;
        }

        /* Gradient Header */
        .premium-gradient-header {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 50%, #d946ef 100%);
            padding: 2.5rem 2rem;
            position: relative;
            border: none;
        }

        .premium-gradient-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at top right, rgba(255,255,255,0.2), transparent);
            pointer-events: none;
        }

        /* Tab Navigation Premium */
        .nav-tabs-premium {
            border: none;
            gap: 0.5rem;
            background: rgba(0, 0, 0, 0.03);
            padding: 0.4rem;
            border-radius: 50rem;
            display: inline-flex;
        }

        .nav-tabs-premium .nav-link {
            border: none;
            border-radius: 50rem;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            color: #64748b;
            transition: all 0.3s ease;
            font-size: 0.875rem;
        }

        .nav-tabs-premium .nav-link.active {
            background: #fff;
            color: #6366f1;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        /* Info Item for Summary */
        .info-item-premium {
            background: #f8fafc;
            border-radius: 1rem;
            padding: 1rem;
            border: 1px solid rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
            height: 100%;
        }

        .info-item-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.04);
            border-color: #6366f1;
        }

        .info-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.35rem;
        }

        .info-value {
            font-weight: 700;
            color: #1e293b;
            font-size: 1.1rem;
        }

        /* Table Premium - Bordered Version */
        .table-premium {
            border-collapse: collapse;
            width: 100%;
        }

        .table-premium thead th {
            border: 1px solid #e2e8f0;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: #64748b;
            padding: 1rem;
            background-color: #f8fafc;
        }

        .table-premium tbody tr {
            background: #fff;
            transition: all 0.2s ease;
        }

        .table-premium tbody tr:hover {
            background-color: #f1f5f9;
        }

        .table-premium tbody td {
            border: 1px solid #e2e8f0;
            padding: 0.75rem 1rem;
            vertical-align: middle;
        }

        /* Premium Action Buttons */
        .btn-action {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            cursor: pointer;
        }

        .btn-action-edit {
            background-color: rgba(99, 102, 241, 0.1);
            color: #6366f1;
        }

        .btn-action-edit:hover {
            background-color: #6366f1;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .btn-action-delete {
            background-color: rgba(244, 63, 94, 0.1);
            color: #f43f5e;
        }

        .btn-action-delete:hover {
            background-color: #f43f5e;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(244, 63, 94, 0.3);
        }

        /* Floating Summary Section */
        .summary-sticky {
            position: sticky;
            top: 2rem;
        }

        /* Custom Form Controls */
        .form-control-premium {
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .form-control-premium:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .btn-premium {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            border: none;
            border-radius: 0.75rem;
            padding: 0.8rem 2rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #fff !important;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
            color: #fff;
        }

        .select2-container--default .select2-selection--single {
            border-radius: 0.75rem;
            padding: 0px 12px;
            border: 1px solid #e2e8f0;
            height: 39px;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 43px;
        }

        .bg-light-soft {
            background-color: rgba(248, 250, 252, 0.8);
        }
    </style>
@endpush

@section('content')
    <div class="row g-3 g-lg-4">
        {{-- Main Collection Area --}}
        <div class="col-lg-8">
            <div class="card card-premium mb-3 mb-lg-4">
                <div class="premium-gradient-header text-center py-4 py-lg-5">
                    <h3 class="fw-bold text-white mb-1">{{ __('messages.collection_entry') }}</h3>
                    <p class="text-white opacity-75 mb-0 small">{{ __('messages.collect_savings_and_loan_installments') ?? 'Collect savings and loan installments effectively' }}</p>
                </div>
                <div class="card-body p-3 p-lg-4">
                    <div id="form-message-alert" class="mb-3 mb-lg-4"></div>
                    
                    {{-- Integrated Collection Form --}}
                    <form action="{{ route('collections.store') }}" method="POST" id="integrated-collection-form">
                        @csrf
                        
                        <div class="row g-3 g-lg-4">
                            {{-- Member Selection --}}
                            <div class="col-md-8">
                                <label class="form-label text-muted fw-bold small text-uppercase tracking-wider">{{ __('messages.select_member') }} <span class="text-danger">*</span></label>
                                <select name="member_id" id="member_selector" class="form-select select2" required>
                                    <option value="">{{ __('messages.search_member') }}</option>
                                    @foreach ($members as $member)
                                        <option value="{{ $member['id'] }}">{{ $member['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Collection Date --}}
                            <div class="col-md-4">
                                <label class="form-label text-muted fw-bold small text-uppercase tracking-wider">{{ __('messages.collection_date') }}</label>
                                @role('Admin')
                                    <input type="text" name="date" class="form-control flatpickr form-control-premium" value="{{ old('date', date('Y-m-d')) }}" required>
                                @else
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i data-lucide="calendar" class="icon-sm"></i></span>
                                        <input type="text" class="form-control form-control-premium bg-light" value="{{ date('d/m/Y') }}" readonly>
                                    </div>
                                    <input type="hidden" name="date" value="{{ date('Y-m-d') }}">
                                @endrole
                            </div>

                            <div class="col-12"><hr class="my-1 opacity-10"></div>

                            {{-- Savings Section --}}
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">{{ __('messages.deposit') }} {{ __('messages.amount') }}</label>
                                <div class="input-group shadow-sm rounded-3 overflow-hidden">
                                    <span class="input-group-text bg-white border-end-0 text-muted small fw-bold">{{ __('messages.bdt') }}</span>
                                    <input type="number" step="0.01" name="amount" class="form-control form-control-premium border-start-0" placeholder="0.00">
                                </div>
                            </div>

                            {{-- Loan Section --}}
                            <div class="col-md-6">
                                <div class="row g-2">
                                        <div class="col-7">
                                            <label class="form-label small fw-bold text-muted">{{ __('messages.loan_installment') }}</label>
                                            <div class="input-group shadow-sm rounded-3 overflow-hidden">
                                                <span class="input-group-text bg-white border-end-0 text-muted small fw-bold">{{ __('messages.bdt') }}</span>
                                                <input type="number" step="0.01" name="loan_installment" class="form-control form-control-premium border-start-0" placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="col-5">
                                            <label class="form-label small fw-bold text-muted">{{ __('messages.grace_amount') ?? 'Grace' }}</label>
                                            <div class="input-group shadow-sm rounded-3 overflow-hidden">
                                                <input type="number" step="0.01" name="grace_amount" class="form-control form-control-premium" placeholder="0.00">
                                            </div>
                                        </div>
                                    </div>
                            </div>

                            {{-- Deposit To Account --}}
                            <div class="col-md-6">
                                <label class="form-label text-muted fw-bold small text-uppercase tracking-wider">{{ __('messages.deposit_to_account') }} <span class="text-danger">*</span></label>
                                <select name="account_id" id="payment_account_id" class="form-select select2" required>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Notes --}}
                            <div class="col-md-6">
                                <label class="form-label text-muted fw-bold small text-uppercase tracking-wider">{{ __('messages.notes') }}</label>
                                <input name="notes" class="form-control form-control-premium" placeholder="{{ __('messages.enter_notes') ?? 'Optional collector notes...' }}">
                            </div>

                            {{-- Collector Selection --}}
                            <div class="col-md-12">
                                <label class="form-label text-muted fw-bold small text-uppercase tracking-wider">{{ __('messages.collector') ?? 'Collector' }} <span class="text-danger">*</span></label>
                                <select name="collector_id" id="collector_selector" class="form-select select2" required>
                                    @if(auth()->user()->hasRole('Admin'))
                                        <option value="">{{ __('messages.select_collector') ?? 'Select Collector' }}</option>
                                    @endif
                                    @foreach ($collectors as $collector)
                                        <option value="{{ $collector->id }}" {{ $collector->id == Auth::id() ? 'selected' : '' }}>{{ $collector->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Submit Button --}}
                            <div class="col-12 text-center mt-3 mt-lg-4">
                                <button type="submit" class="btn btn-premium w-100 py-3 d-flex align-items-center justify-content-center">
                                    <i data-lucide="check-circle" class="me-2 icon-sm"></i> {{ __('messages.submit_collection') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Member Summary Area --}}
        <div class="col-lg-4">
            <div class="summary-sticky">
                <div class="card card-premium shadow-sm border-0">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                        <h5 class="fw-bold mb-0 d-flex align-items-center">
                            <i data-lucide="info" class="me-2 text-primary"></i> {{ __('messages.member_summary') }}
                        </h5>
                    </div>
                    <div class="card-body p-4" id="member_summary_container" style="max-height: 700px; overflow-y:auto">
                        <div id="member_summary_content" class="text-center text-muted py-5">
                            <div class="bg-light-soft rounded-circle p-4 d-inline-block mb-3">
                                <i data-lucide="user-plus" class="text-muted" style="width: 48px; height: 48px;"></i>
                            </div>
                            <p class="small fw-medium">{{ __('messages.select_member_summary') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Today's Collections Section --}}
    <div class="row mt-3 mt-lg-5">
        <div class="col-12">
            <div class="card card-premium shadow-sm border-0">
                <div class="card-body p-3 p-lg-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 d-flex align-items-center">
                            <i data-lucide="list" class="me-2 text-primary"></i> 
                            {{ __('messages.today_collections') ?? "Today's Collections" }} 
                            <span class="ms-2 badge bg-soft-primary px-3 rounded-pill fw-medium fs-6 border-0 text-primary">
                                {{ \Carbon\Carbon::today()->format('d M, Y') }}
                            </span>
                        </h5>
                    </div>
                    
                    {{-- Tabs for Today's Collections --}}
                    <ul class="nav nav-pills nav-pills-premium mb-4" id="collectionListTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="savings-list-tab" data-bs-toggle="tab" data-bs-target="#savings-list-pane" type="button" role="tab" aria-controls="savings-list-pane" aria-selected="true">
                                <i data-lucide="piggy-bank" class="me-2 icon-sm"></i> {{ __('messages.savings_collections') ?? 'Savings Collections' }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="loans-list-tab" data-bs-toggle="tab" data-bs-target="#loans-list-pane" type="button" role="tab" aria-controls="loans-list-pane" aria-selected="false">
                                <i data-lucide="banknote" class="me-2 icon-sm"></i> {{ __('messages.loan_installments') ?? 'Loan Installments' }}
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="collectionListTabContent">
                        {{-- Savings List --}}
                        <div class="tab-pane fade show active" id="savings-list-pane" role="tabpanel" aria-labelledby="savings-list-tab">
                            <div class="table-responsive rounded-4 overflow-hidden border border-light">
                                <table class="table table-premium mb-0">
                                    <thead class="bg-light-soft text-uppercase small tracking-wider whitespace-nowrap">
                                        <tr>
                                            <th class="py-3 text-nowrap">{{ __('messages.date') }}</th>
                                            <th class="py-3 text-nowrap">{{ __('messages.member') }}</th>
                                            <th class="py-3 text-end text-nowrap">{{ __('messages.deposit') }}</th>
                                            <th class="py-3 text-center text-nowrap text-muted">{{ __('messages.collector') }}</th>
                                            @role('Admin')<th class="py-3 text-center text-nowrap">{{ __('messages.actions') }}</th>@endrole
                                        </tr>
                                    </thead>
                                    <tbody id="today-savings-table-body">
                                        {{-- AJAX data --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Loans List --}}
                        <div class="tab-pane fade" id="loans-list-pane" role="tabpanel" aria-labelledby="loans-list-tab">
                            <div class="table-responsive rounded-4 overflow-hidden border border-light">
                                <table class="table table-premium mb-0">
                                    <thead class="bg-light-soft text-uppercase small tracking-wider whitespace-nowrap">
                                        <tr>
                                            <th class="py-3 text-nowrap">{{ __('messages.date') }}</th>
                                            <th class="py-3 text-nowrap">{{ __('messages.member') }}</th>
                                            <th class="py-3 text-end text-nowrap">{{ __('messages.loan_installment') }}</th>
                                            <th class="py-3 text-end text-danger text-nowrap">L. Due</th>
                                            <th class="py-3 text-end text-nowrap">{{ __('messages.grace_amount') }}</th>
                                            <th class="py-3 text-center text-nowrap text-muted">{{ __('messages.collector') }}</th>
                                            @role('Admin')<th class="py-3 text-center text-nowrap">{{ __('messages.actions') }}</th>@endrole
                                        </tr>
                                    </thead>
                                    <tbody id="today-loans-table-body">
                                        {{-- AJAX data --}}
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
            
            $('#payment_account_id').select2({
                width: '100%'
            });

            $('#collector_selector').select2({
                width: '100%'
            });

            $(".flatpickr").flatpickr({
                altInput: true,
                dateFormat: "Y-m-d",
                altFormat: "d M, Y"
            });

            const summaryContent = $('#member_summary_content');
            const collectionForm = $('#integrated-collection-form');
            const submitButton = collectionForm.find('button[type="submit"]');
            const originalButtonText = submitButton.html();
            const messageAlert = $('#form-message-alert');
            const memberSelector = $('#member_selector');

            const IS_ADMIN = @json(Auth::user()->hasRole('Admin'));

            // Member selection change event
            memberSelector.on('change', function() {
                const memberId = $(this).val();
                resetFormsAndSummary();

                if (!memberId) return;

                summaryContent.html(
                    '<div class="py-5 text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 small text-muted">{{ __('messages.loading_details') }}</p></div>'
                );

                $.ajax({
                    url: `/api/members/${memberId}/accounts`,
                    type: 'GET',
                    success: function(response) {
                        populateSummary(response);
                    },
                    error: function() {
                        summaryContent.html(
                            '<div class="py-5 text-center text-danger"><i data-lucide="alert-circle"></i><p class="mt-2">{{ __('messages.failed_load_details') }}</p></div>'
                        );
                        lucide.createIcons();
                    }
                });
            });

            collectionForm.on('submit', function(e) {
                e.preventDefault();
                messageAlert.html('');
                
                submitButton.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-2"></span> {{ __('messages.processing') }}'
                );

                const formData = $(this).serialize();

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            messageAlert.html(`<div class="alert alert-soft-success border-0 rounded-4 p-3 d-flex align-items-center"><i data-lucide="check-circle" class="me-2 text-success"></i> ${response.message}</div>`);
                            lucide.createIcons();
                            resetFullForm();
                            loadTodayCollections();
                            
                            // Scroll to top of form to see message
                            $('html, body').animate({ scrollTop: $("#form-message-alert").offset().top - 100 }, 200);
                        }
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON;
                        let errorMessage = 'An error occurred. Please try again.';
                        if (errors && errors.message) errorMessage = errors.message;
                        messageAlert.html(`<div class="alert alert-soft-danger border-0 rounded-4 p-3 d-flex align-items-center"><i data-lucide="alert-circle" class="me-2 text-danger"></i> ${errorMessage}</div>`);
                        lucide.createIcons();
                    },
                    complete: function() {
                        submitButton.prop('disabled', false).html(originalButtonText);
                    }
                });
            });

            function resetFullForm() {
                collectionForm[0].reset();
                memberSelector.val(null).trigger('change');
            }

            function loadTodayCollections() {
                const savingsBody = $('#today-savings-table-body');
                const loansBody = $('#today-loans-table-body');
                const colspanSavings = IS_ADMIN ? 5 : 4;
                const colspanLoans = IS_ADMIN ? 7 : 6;

                savingsBody.html(`<tr><td colspan="${colspanSavings}" class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2"></div> Loading...</td></tr>`);
                loansBody.html(`<tr><td colspan="${colspanLoans}" class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2"></div> Loading...</td></tr>`);

                $.ajax({
                    url: '{{ route('api.collections.today') }}',
                    type: 'GET',
                    success: function(response) {
                        savingsBody.html(response.savings_html);
                        loansBody.html(response.loans_html);
                        lucide.createIcons();
                    }
                });
            }

            loadTodayCollections();

            function resetFormsAndSummary() {
                summaryContent.html(`
                    <div class="text-center text-muted py-5">
                        <div class="bg-light-soft rounded-circle p-4 d-inline-block mb-3">
                            <i data-lucide="user-plus" class="text-muted" style="width: 48px; height: 48px;"></i>
                        </div>
                        <p class="small fw-medium">{{ __('messages.select_member_summary') }}</p>
                    </div>
                `);
                lucide.createIcons();
            }

            function populateSummary(data) {
                const member = data.member;
                let html = `
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            <img src="${member.photo_url}" class="rounded-4 shadow-sm object-fit-cover" width="100" height="100" alt="Member Photo">
                            <span class="position-absolute bottom-0 end-0 p-2 bg-success border border-2 border-white rounded-circle shadow-sm"></span>
                        </div>
                        <h5 class="fw-bold mt-3 mb-1 text-primary">{{ __('messages.name') }}: ${member.name}</h5>
                        <p class="text-muted small mb-0 d-flex align-items-center justify-content-center">
                            <i data-lucide="phone" class="icon-xs me-1"></i> {{ __('messages.phone') }}: ${member.phone}
                        </p>
                        <span class="badge bg-soft-primary px-3 rounded-pill mt-2 fw-medium border-0 text-primary">{{ __('messages.account_no') }}: ${member.account_no}</span>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold small text-uppercase tracking-wider text-muted mb-3 d-flex align-items-center">
                            <i data-lucide="piggy-bank" class="me-2 icon-xs text-primary"></i> {{ __('messages.savings_accounts') }}
                        </h6>
                        <div class="d-grid gap-2">`;
                
                if (data.savings.length > 0) {
                    data.savings.forEach(acc => {
                        html += `
                            <div class="info-item-premium p-3">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <span class="fw-bold text-dark small">${acc.account_no}</span>
                                    <span class="badge bg-soft-info border-0 rounded-pill small" style="font-size: 0.65rem;">${acc.scheme_type}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <span class="text-muted small fw-medium">{{ __('messages.balance') }}</span>
                                    <span class="fw-bold text-primary">${parseFloat(acc.current_balance).toLocaleString(undefined, {minimumFractionDigits: 2})}</span>
                                </div>
                            </div>`;
                    });
                } else {
                    html += `<div class="p-3 text-center border rounded-4 text-muted small italic">{{ __('messages.no_savings') }}</div>`;
                }
                
                html += `</div></div>

                    <div class="mb-2">
                        <h6 class="fw-bold small text-uppercase tracking-wider text-muted mb-3 d-flex align-items-center">
                            <i data-lucide="banknote" class="me-2 icon-xs text-danger"></i> {{ __('messages.loan_accounts') }}
                        </h6>
                        <div class="d-grid gap-2">`;

                if (data.loans.length > 0) {
                    data.loans.forEach(acc => {
                        const due = parseFloat(acc.total_payable) - parseFloat(acc.total_paid);
                        html += `
                            <div class="info-item-premium p-3 border-start border-danger border-4">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <span class="fw-bold text-dark small">${acc.account_no}</span>
                                    <span class="badge bg-soft-danger border-0 rounded-pill small" style="font-size: 0.65rem;">Running</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <span class="text-muted small fw-medium">{{ __('messages.installment') }}</span>
                                    <span class="fw-bold text-dark">${parseFloat(acc.installment_amount).toLocaleString(undefined, {minimumFractionDigits: 2})}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <span class="text-danger small fw-bold">{{ __('messages.total_due') }}</span>
                                    <span class="fw-bold text-danger">${due.toLocaleString(undefined, {minimumFractionDigits: 2})}</span>
                                </div>
                            </div>`;
                    });
                } else {
                    html += `<div class="p-3 text-center border rounded-4 text-muted small italic">{{ __('messages.no_loans') }}</div>`;
                }
                
                html += `</div></div>`;
                
                summaryContent.html(html);
                lucide.createIcons();
            }
        });
    </script>
@endpush
