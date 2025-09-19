@extends('layout.master')

@section('content')
    <div class="row">
        <div class="col-md-12">
            {{-- সদস্যের প্রাথমিক তথ্য এবং স্টেটমেন্ট জেনারেটর --}}
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title">Member Details: {{ $member->name }}</h5>
                        <a href="{{ route('members.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
                    </div>
                    <p class="card-text">
                        <strong>Phone:</strong> {{ $member->mobile_no }} |
                        <strong>Area:</strong> {{ $member->area->name }} |
                        <strong>Joining Date:</strong> {{ $member->joining_date->format('d M, Y') }}
                    </p>
                    <hr>
                    <h6 class="card-title">Generate Account Statement</h6>
                    <form action="{{ route('reports.member_statement', $member->id) }}" method="POST" target="_blank">
                        @csrf
                        <div class="row align-items-end">
                            <div class="col-md-5"><input type="date" name="start_date" class="form-control" required></div>
                            <div class="col-md-5"><input type="date" name="end_date" class="form-control" value="{{ date('Y-m-d') }}" required></div>
                            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Generate PDF</button></div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ট্যাব নেভিগেশন --}}
            <ul class="nav nav-tabs nav-tabs-line" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="savings-tab" data-bs-toggle="tab" href="#savings" role="tab" aria-controls="savings" aria-selected="true">Savings Accounts</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="loans-tab" data-bs-toggle="tab" href="#loans" role="tab" aria-controls="loans" aria-selected="false">Loan Accounts</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="withdrawals-tab" data-bs-toggle="tab" href="#withdrawals" role="tab" aria-controls="withdrawals" aria-selected="false">Withdrawal History</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="profile-tab" data-bs-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Profile Details</a>
                </li>
            </ul>

            {{-- ট্যাব কন্টেন্ট --}}
            <div class="tab-content border border-top-0 p-3" id="myTabContent">

                {{-- Savings Accounts Tab --}}
                <div class="tab-pane fade show active" id="savings" role="tabpanel" aria-labelledby="savings-tab">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6>All Savings Accounts</h6>
                        <a href="{{ route('members.savings-accounts.create', $member->id) }}" class="btn btn-primary btn-sm">Open New Account</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead><tr><th>Account No</th><th>Scheme</th><th>Balance</th><th>Nominee</th><th>Actions</th></tr></thead>
                            <tbody>
                            @forelse ($member->savingsAccounts as $account)
                                <tr>
                                    <td><a href="{{ route('savings_accounts.show', $account->id) }}">{{ $account->account_no }}</a></td>
                                    <td>{{ $account->scheme_type }}</td>
                                    <td>{{ number_format($account->current_balance, 2) }}</td>
                                    <td><strong>{{ $account->nominee_name }}</strong> <br><small>Relation: {{ $account->nominee_relation }}</small></td>
                                    <td>
                                        @role('Admin')
                                        <button type="button" class="btn btn-danger btn-xs" data-bs-toggle="modal" data-bs-target="#withdrawModal" data-account-id="{{ $account->id }}" data-account-no="{{ $account->account_no }}" data-current-balance="{{ $account->current_balance }}">
                                            Withdraw
                                        </button>
                                        @endrole
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center">No savings accounts found.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Loan Accounts Tab --}}
                <div class="tab-pane fade" id="loans" role="tabpanel" aria-labelledby="loans-tab">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6>All Loan Accounts</h6>
                        @role('Admin')
                        <a href="{{ route('members.loan-accounts.create', $member->id) }}" class="btn btn-danger btn-sm">Issue New Loan</a>
                        @endrole
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead><tr><th>Account No</th><th>Loan Amount</th><th>Payable</th><th>Paid</th><th>Due</th><th>Status</th></tr></thead>
                            <tbody>
                            @forelse ($member->loanAccounts as $account)
                                <tr>
                                    <td><a href="{{ route('loan-accounts.show', $account->id) }}">{{$account->account_no}}</a></td>
                                    <td>{{ number_format($account->loan_amount, 2) }}</td>
                                    <td>{{ number_format($account->total_payable, 2) }}</td>
                                    <td>{{ number_format($account->total_paid, 2) }}</td>
                                    <td class="text-danger">{{ number_format($account->total_payable - $account->total_paid, 2) }}</td>
                                    <td><span class="badge bg-{{ $account->status == 'running' ? 'warning' : 'success' }}">{{ ucfirst($account->status) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center">No loan accounts found.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Withdrawal History Tab (নতুন ট্যাব) --}}
                <div class="tab-pane fade" id="withdrawals" role="tabpanel" aria-labelledby="withdrawals-tab">
                    <h6 class="mb-3">All Savings Withdrawal History</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead><tr><th>Date</th><th>Account No</th><th>Principal</th><th>Profit</th><th>Total Paid</th><th>Processed By</th></tr></thead>
                            <tbody>
                            @forelse ($member->withdrawals()->latest()->get() as $withdrawal)
                                <tr>
                                    <td>{{ $withdrawal->withdrawal_date->format('d M, Y') }}</td>
                                    <td><a href="{{ route('savings_accounts.show', $withdrawal->savings_account_id) }}">{{ $withdrawal->savingsAccount->account_no }}</a></td>
                                    <td>{{ number_format($withdrawal->withdrawal_amount, 2) }}</td>
                                    <td>{{ number_format($withdrawal->profit_amount, 2) }}</td>
                                    <td class="fw-bold">{{ number_format($withdrawal->total_amount, 2) }}</td>
                                    <td>{{ $withdrawal->processedBy->name }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center">No withdrawal history found.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Profile Details Tab --}}
                {{-- Profile Details Tab --}}
                <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="card-title">Member's Profile Information</h6>
                        @role('Admin')
                        <a href="{{ route('members.edit', $member->id) }}" class="btn btn-primary btn-sm">
                            <i data-lucide="edit" class="icon-sm me-2"></i> Edit Profile
                        </a>
                        @endrole
                    </div>

                    <div class="row">
                        {{-- সদস্যের ছবি ও স্বাক্ষর --}}
                        <div class="col-md-4">
                            <div class="mb-3 text-center">
                                <label class="form-label d-block">Member's Photo</label>
                                <img src="{{ $member->getFirstMediaUrl('member_photo') ?: 'https://placehold.co/200x200' }}"
                                     alt="Member Photo" class="img-fluid rounded" style="width: 200px; height: 200px; object-fit: cover;">
                            </div>
                            <div class="mb-3 text-center">
                                <label class="form-label d-block">Member's Signature</label>
                                @if($member->getFirstMediaUrl('member_signature'))
                                    <img src="{{ $member->getFirstMediaUrl('member_signature') }}"
                                         alt="Member Signature" class="img-fluid rounded border p-2" style="max-width: 200px; background-color: #f8f9fa;">
                                @else
                                    <p class="text-muted border p-4" style="background-color: #f8f9fa;">Signature not uploaded.</p>
                                @endif
                            </div>
                        </div>

                        {{-- সদস্যের বিস্তারিত তথ্য --}}
                        <div class="col-md-8">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-sm">
                                    <tbody>
                                    <tr><th colspan="2" class="bg-light">{{ __('messages.personal_info') }}</th></tr>
                                    <tr><th style="width: 35%;">{{ __('messages.name') }}</th><td>{{ $member->name }}</td></tr>
                                    <tr><th>{{ __('messages.father_name') }}</th><td>{{ $member->father_name }}</td></tr>
                                    <tr><th>{{ __('messages.mother_name') }}</th><td>{{ $member->mother_name }}</td></tr>
                                    <tr><th>{{ __('messages.spouse_name') }}</th><td>{{ $member->spouse_name ?? 'N/A' }}</td></tr>
                                    <tr><th>{{ __('messages.date_of_birth') }}</th><td>{{ $member->date_of_birth ? $member->date_of_birth->format('d F, Y') : 'N/A' }}</td></tr>
                                    <tr><th>{{ __('messages.gender') }}</th><td>{{ ucfirst($member->gender ?? 'N/A') }}</td></tr>
                                    <tr><th>{{ __('messages.marital_status') }}</th><td>{{ ucfirst($member->marital_status ?? 'N/A') }}</td></tr>
                                    <tr><th>{{ __('messages.religion') }}</th><td>{{ $member->religion ?? 'N/A' }}</td></tr>
                                    <tr><th>{{ __('messages.blood_group') }}</th><td>{{ $member->blood_group ?? 'N/A' }}</td></tr>
                                    <tr><th>{{ __('messages.nationality') }}</th><td>{{ $member->nationality ?? 'N/A' }}</td></tr>

                                    <tr><th colspan="2" class="bg-light mt-3">{{ __('messages.contact_info') }}</th></tr>
                                    <tr><th>{{ __('messages.mobile_no') }}</th><td>{{ $member->mobile_no }}</td></tr>
                                    <tr><th>{{ __('messages.email_address') }}</th><td>{{ $member->email ?? 'N/A' }}</td></tr>
                                    <tr><th>{{ __('messages.present_address') }}</th><td>{{ $member->present_address }}</td></tr>
                                    <tr><th>{{ __('messages.permanent_address') }}</th><td>{{ $member->permanent_address ?? 'N/A' }}</td></tr>

                                    <tr><th colspan="2" class="bg-light mt-3">{{ __('messages.additional_info') }}</th></tr>
                                    <tr><th>{{ __('messages.nid_number') }}</th><td>{{ $member->nid_no ?? 'N/A' }}</td></tr>
                                    <tr><th>{{ __('messages.occupation') }}</th><td>{{ $member->occupation ?? 'N/A' }}</td></tr>
                                    <tr><th>{{ __('messages.work_place') }}</th><td>{{ $member->work_place ?? 'N/A' }}</td></tr>
                                    <tr><th>{{ __('messages.joining_date') }}</th><td>{{ $member->joining_date->format('d F, Y') }}</td></tr>
                                    <tr><th>{{ __('messages.area') }}</th><td>{{ $member->area->name ?? 'N/A' }}</td></tr>
                                    <tr>
                                        <th>{{ __('messages.status') }}</th>
                                        <td>
                                            @php
                                                $statusClass = 'danger';
                                                if ($member->status == 'active') $statusClass = 'success';
                                                elseif ($member->status == 'inactive') $statusClass = 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $statusClass }}">{{ __( 'messages.' . strtolower($member->status) ) }}</span>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Withdrawal Modal (আপনার বিদ্যমান কোডটি এখানে অপরিবর্তিত থাকবে) --}}
    @role('Admin')
    <div class="modal fade" id="withdrawModal" tabindex="-1" aria-labelledby="withdrawModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="withdrawModalLabel">Process Final Withdrawal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="withdrawForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <h6 class="mb-2">Account No: <span id="modalAccountNo" class="fw-bold"></span></h6>
                        <p>Current Balance: <strong id="modalCurrentBalance" class="text-primary"></strong> BDT</p>
                        <hr>

                        <div class="alert alert-info">
                            This process will withdraw the <strong>full current balance</strong> and close the account.
                        </div>

                        {{-- শুধুমাত্র মুনাফার পরিমাণ ইনপুট নেওয়া হবে --}}
                        <div class="mb-3">
                            <label for="profit_amount" class="form-label">Add Profit Amount (Optional)</label>
                            <input type="number" step="0.01" name="profit_amount" class="form-control" placeholder="0.00" id="profit_amount_input">
                            <div class="form-text">সদস্যকে অতিরিক্ত কত টাকা মুনাফা হিসেবে দেওয়া হবে?</div>
                        </div>

                        {{-- ব্যবহারকারীকে দেখানো হবে সে মোট কত টাকা পাবে --}}
                        <div class="mt-3">
                            <h5>Total Amount to Pay Member: <span id="total_payable_display" class="fw-bold text-success"></span> BDT</h5>
                        </div>

                        <div class="mb-3 mt-3">
                            <label for="withdrawal_date" class="form-label">Withdrawal Date <span class="text-danger">*</span></label>
                            <input type="date" name="withdrawal_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3 mt-3">
                            <label class="form-label">Payment From Account <span class="text-danger">*</span></label>
                            <select name="account_id" class="form-select" required>
                                <option value="">Select Account...</option>
                                @foreach (\App\Models\Account::where('is_active', true)->get() as $paymentAccount)
                                    <option value="{{ $paymentAccount->id }}">{{ $paymentAccount->name }} (Balance: {{ number_format($paymentAccount->balance) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Process Final Withdrawal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endrole
@endsection

@push('custom-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var withdrawModal = document.getElementById('withdrawModal');

            withdrawModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var accountId = button.getAttribute('data-account-id');
                var accountNo = button.getAttribute('data-account-no');
                var currentBalance = parseFloat(button.getAttribute('data-current-balance'));

                var modalAccountNo = withdrawModal.querySelector('#modalAccountNo');
                var modalCurrentBalance = withdrawModal.querySelector('#modalCurrentBalance');
                var withdrawForm = withdrawModal.querySelector('#withdrawForm');
                var profitInput = withdrawModal.querySelector('#profit_amount_input');
                var totalPayableDisplay = withdrawModal.querySelector('#total_payable_display');

                // Reset profit input on modal open
                profitInput.value = '';

                modalAccountNo.textContent = accountNo;
                modalCurrentBalance.textContent = currentBalance.toFixed(2);
                totalPayableDisplay.textContent = currentBalance.toFixed(2);

                var url = "{{ url('savings-accounts') }}/" + accountId + "/withdraw";
                withdrawForm.setAttribute('action', url);

                // মুনাফা ইনপুট পরিবর্তনের সাথে সাথে মোট প্রদেয় পরিমাণ গণনা করুন
                profitInput.addEventListener('input', function() {
                    var profit = parseFloat(this.value) || 0;
                    var totalPayable = currentBalance + profit;
                    totalPayableDisplay.textContent = totalPayable.toFixed(2);
                });
            });
        });
    </script>
@endpush
