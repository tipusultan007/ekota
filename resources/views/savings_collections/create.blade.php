@extends('layout.master')

@push('plugin-styles')
    <link rel="stylesheet" href="{{ asset('build/plugins/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}">
@endpush

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('messages.savings_collection') }}</li>
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
                <div class="card-header bg-primary">
                    <h5 class="card-title text-white mb-0 ">{{ __('messages.make_new_savings_collection') }}</h5>
                </div>
                <div class="card-body">

                    <form action="{{ route('savings-collections.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">{{ __('messages.select_member_and_account') }} <span class="text-danger">*</span></label>
                                <select name="savings_account_id" id="savings_account_id" class="form-select" required>
                                    <option value="">-- {{ __('messages.select') }} --</option>
                                    @foreach ($members as $member)
                                        <optgroup label="{{ $member->name }} (ID: {{ $member->id }})">
                                            @foreach ($member->savingsAccounts as $account)
                                                <option value="{{ $account->id }}">
                                                    {{ $account->account_no }} ({{ $account->scheme_type }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('messages.collection_date') }}</label>
                                <input type="text" name="collection_date" class="form-control flatpickr" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('messages.amount') }} <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="amount" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Deposit To Account <span class="text-danger">*</span></label>
                                <select name="account_id" class="form-select" required>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}" {{ old('account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.notes') }}</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">{{ __('messages.submit_collection') }}</button>
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
                        <p>Select a savings account to view details.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Recent Collections List Section --}}
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center mb-4">
                    <h6 class="card-title mb-0">{{ __('messages.recent_savings_collections') }}</h6>
                    <a href="{{ route('savings-collections.index') }}" class="btn btn-secondary btn-sm">
                        {{ __('messages.view_all_collections') }}
                    </a>
                </div>
                <div class="card-body">


                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>{{ __('messages.receipt_no') }}</th>
                                <th>{{ __('messages.member_name') }}</th>
                                <th>{{ __('messages.account_no') }}</th>
                                <th class="text-end">{{ __('messages.amount') }}</th>
                                <th>{{ __('messages.date') }}</th>
                                <th>{{ __('messages.collected_by') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($recentCollections as $collection)
                                <tr>
                                    <td>{{ $collection->receipt_no ?? $collection->id }}</td>
                                    <td>{{ $collection->member->name }}</td>
                                    <td>{{ $collection->savingsAccount->account_no }}</td>
                                    <td class="text-end">{{ number_format($collection->amount, 2) }}</td>
                                    <td>{{ $collection->collection_date->format('d M, Y') }}</td>
                                    <td>{{ $collection->collector->name }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">{{ __('messages.no_recent_collections_found') }}</td>
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

    {{--<script>
        $(document).ready(function() {
            if ($("#savings_account_id").length) {
                $("#savings_account_id").select2({
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
    </script>--}}

    <script>
        $(document).ready(function() {
            const savingsAccountSelect = $("#savings_account_id");
            const summaryContent = $("#summary_content");

            savingsAccountSelect.select2({
                placeholder: "{{ __('messages.search_and_select_account') }}",
                width: '100%',
            });

            $(".flatpickr").flatpickr({
                altInput: true,
                dateFormat: "Y-m-d",
                altFormat: "d/m/Y"
            });

            savingsAccountSelect.on('change', function() {
                const accountId = $(this).val();

                if (!accountId) {
                    summaryContent.html('<p class="text-muted">Select a savings account to view details.</p>');
                    return;
                }

                // Show loading state
                summaryContent.html('<div class="spinner-border spinner-border-sm" role="status"></div>');

                // Fetch account details via API
                $.ajax({
                    url: `/api/savings-accounts/${accountId}/details`,
                    type: 'GET',
                    success: function(response) {
                        const member = response.member;
                        const nextDueDate = response.next_due_date ? new Date(response.next_due_date).toLocaleDateString('en-GB') : 'N/A';

                        // Build and display summary HTML
                        let html = `
                            <div class="text-center mb-3">
                                <img src="${member.photo_url || 'https://placehold.co/80x80'}" class="rounded-circle" width="80" height="80" alt="Member Photo">
                            </div>
                            <h6 class="text-center">${member.name}</h6>
                            <p class="text-muted text-center small mb-3">
                                ${member.mobile_no}
                            </p>
                            <hr>
                            <h6 class="mb-3">Account Details</h6>
                            <dl class="row">
                                <dt class="col-sm-6">Account No</dt>
                                <dd class="col-sm-6">${response.account_no}</dd>

                                <dt class="col-sm-6">Current Balance</dt>
                                <dd class="col-sm-6 fw-bold">${parseFloat(response.current_balance).toFixed(2)}</dd>

                                <dt class="col-sm-6">Scheme</dt>
                                <dd class="col-sm-6">${response.scheme_type}</dd>

                                <dt class="col-sm-6">Frequency</dt>
                                <dd class="col-sm-6">${response.collection_frequency}</dd>

                                <dt class="col-sm-6">Next Due Date</dt>
                                <dd class="col-sm-6 text-danger">${nextDueDate}</dd>
                            </dl>
                        `;
                        summaryContent.html(html);
                    },
                    error: function() {
                        summaryContent.html('<p class="text-danger">Failed to load account details.</p>');
                    }
                });
            });
        });
    </script>
@endpush
