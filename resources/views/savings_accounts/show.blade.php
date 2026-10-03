@extends('layout.master')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('members.show', $savingsAccount->member_id) }}">{{ $savingsAccount->member->name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('messages.savings_account_details') }}</li>
        </ol>
    </nav>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        {{-- Account Summary & Details Section --}}
        <div class="col-md-5 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">{{ __('messages.account_summary') }}</h5>
                        @role('Admin')
                        {{-- শুধুমাত্র 'active' অ্যাকাউন্টগুলোর জন্য Actions ড্রপডাউন দেখান --}}
                        @if ($savingsAccount->status == 'active')
                            <div class="dropdown">
                                <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="actionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    {{ __('messages.actions') }}
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="actionsDropdown">
                                    <li><a class="dropdown-item" href="{{ route('savings-accounts.edit', $savingsAccount->id) }}">{{ __('messages.edit_account') }}</a></li>
                                    <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#partialWithdrawModal">{{ __('messages.partial_withdrawal') }}</button></li>
                                    <li><button type="button" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#finalWithdrawModal">{{ __('messages.final_withdrawal_close') }}</button></li>
                                </ul>
                            </div>
                        @endif
                        @endrole
                    </div>

                    <div class="text-center mb-4">
                        <h6 class="text-muted">{{ __('messages.current_balance') }}</h6>
                        <h2 class="fw-bolder text-success">{{ number_format($savingsAccount->current_balance, 2) }}
                            <small>BDT</small></h2>
                    </div>

                    <table class="table table-sm table-borderless">
                        <tbody>
                        <tr>
                            <td class="fw-bold" style="width: 40%;">{{ __('messages.account_no') }}</td>
                            <td>{{ $savingsAccount->account_no }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">{{ __('messages.member') }}</td>
                            <td>
                                <a href="{{ route('members.show', $savingsAccount->member_id) }}">{{ $savingsAccount->member->name }}</a>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-bold">{{ __('messages.scheme_type') }}</td>
                            <td>@lang('messages.' .$savingsAccount->scheme_type)</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">{{ __('messages.collection_frequency') }}</td>
                            <td><span class="badge bg-info">{{ ucfirst(__('messages.' . $savingsAccount->collection_frequency)) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-bold">{{ __('messages.interest_rate') }}</td>
                            <td>{{ $savingsAccount->interest_rate }} %</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">{{ __('messages.opening_date') }}</td>
                            <td>{{ $savingsAccount->opening_date->format('d M, Y') }}</td>
                        </tr>
                        @if($savingsAccount->status != 'closed')
                            <tr>
                                <td class="fw-bold">{{ __('messages.next_due_date') }}</td>
                                <td>
                                    @php
                                        // আজকের তারিখ (শুধুমাত্র তারিখ, সময় ছাড়া)
                                        $today = \Carbon\Carbon::today();
                                        // ডাটাবেস থেকে আসা পরবর্তী কিস্তির তারিখ
                                        $nextDueDate = $savingsAccount->next_due_date ? \Carbon\Carbon::parse($savingsAccount->next_due_date) : null;
                                    @endphp

                                    @if ($nextDueDate)
                                        {{-- তারিখটি ফরম্যাট করে দেখান --}}
                                        {{ $nextDueDate->format('d M, Y') }}

                                        {{-- যদি কিস্তির তারিখ পার হয়ে যায়, তাহলে "Overdue" ব্যাজ দেখান --}}
                                        @if ($nextDueDate->isPast() && !$nextDueDate->isToday())
                                            <span class="badge bg-danger ms-2">{{ __('messages.overdue') }}</span>

                                            {{-- যদি আজকের তারিখই কিস্তির তারিখ হয় --}}
                                        @elseif ($nextDueDate->isToday())
                                            <span class="badge bg-warning ms-2">{{ __('messages.due_today') }}</span>
                                        @endif

                                    @else
                                        {{-- যদি কোনো next_due_date না থাকে --}}
                                        N/A
                                    @endif
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td class="fw-bold">{{ __('messages.status') }}:</td>
                            <td><span
                                    class="badge bg-{{ $savingsAccount->status == 'active' ? 'success' : 'secondary' }}">{{ ucfirst($savingsAccount->status) }}</span>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <hr>
                    <h6 class="mt-4">{{ __('messages.nominee_details') }}</h6>
                    <table class="table table-sm table-borderless">
                        <tbody>
                        <tr>
                            <td class="fw-bold" style="width: 40%;">{{ __('messages.nominee_name') }}</td>
                            <td>{{ $savingsAccount->nominee_name }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">{{ __('messages.relation') }}</td>
                            <td>{{ $savingsAccount->nominee_relation }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">{{ __('messages.phone') }}</td>
                            <td>{{ $savingsAccount->nominee_phone ?? 'N/A' }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Transaction History Section --}}
        <div class="col-md-7 ">
            @if($savingsAccount->status != 'closed')
                <div class="card my-3">
                    <div class="card-header bg-primary">
                        <h4 class="card-title text-white mb-0">{{ __('messages.collection_form') }}</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('savings-collections.store') }}" method="POST">
                            @csrf

                            <input type="hidden" name="savings_account_id" value="{{ $savingsAccount->id }}">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">{{ __('messages.collection_date') }}</label>
                                    <input type="text" name="collection_date" class="form-control flatpickr"
                                           value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">{{ __('messages.deposit') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="amount" class="form-control" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Account <span class="text-danger">*</span></label>
                                    <select name="account_id" class="form-select" required>
                                        @foreach ($accounts as $account)
                                            <option
                                                value="{{ $account->id }}" {{ old('account_id') == $account->id ? 'selected' : '' }}>
                                                {{ $account->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('messages.notes') }}</label>
                                        <textarea name="notes" class="form-control" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">{{ __('messages.submit_collection') }}</button>
                        </form>
                    </div>
                </div>
            @endif
            <div class="card">
                <div class="card-header bg-success">
                    <h5 class="card-title mb-0 text-white">{{ __('messages.transaction_history') }}</h5>
                </div>
                <div class="card-body">

                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                            <tr>
                                <th>{{ __('messages.date') }}</th>
                                <th class="text-end">{{ __('messages.amount') }}</th>
                                <th class="text-end">{{ __('messages.collected_by') }}</th>
                                @role('Admin')
                                <th class="text-center">{{ __('messages.actions') }}</th>
                                @endrole
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($collections as $collection)
                                <tr>
                                    <td>{{ $collection->collection_date->format('d M, Y') }}</td>
                                   {{-- <td>
                                        --}}{{-- transactionable রিলেশন ব্যবহার করে আমরা উৎস জানতে পারি --}}{{--
                                        @if ($tx->transactionable_type === \App\Models\SavingsCollection::class)
                                            Savings Deposit
                                        @elseif ($tx->transactionable_type === \App\Models\SavingsWithdrawal::class)
                                            Savings Withdrawal
                                        @else
                                            {{ $tx->description }}
                                        @endif
                                    </td>--}}

                                    <td class="text-end">{{ number_format($collection->amount, 2) }}</td>
                                    <td class="text-end">{{ $collection->collector->name }}</td>
                                    @role('Admin')
                                    <td class="text-center">
                                        <div class="d-inline-flex">
                                            {{-- Edit Button --}}
                                            <a href="{{ route('savings-collections.edit', $collection->id) }}" class="btn btn-primary btn-xs me-1" title="Edit Installment">
                                                <i data-lucide="edit" class="icon-xs"></i>
                                            </a>

                                            {{-- Delete Button --}}
                                            <form id="delete-installment-{{ $collection->id }}"
                                                  action="{{ route('savings-collections.destroy', $collection->id) }}"
                                                  method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-danger btn-xs"
                                                        title="Delete Installment"
                                                        onclick="showDeleteConfirm('delete-installment-{{ $collection->id }}')">
                                                    <i data-lucide="trash-2" class="icon-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                    @endrole
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ Auth::user()->hasRole('Admin') ? '4' : '3' }}" class="text-center">{{ __('messages.no_transactions_found') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                        <div class="mt-4">
                            {{ $collections->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

   

    @if ($savingsAccount->status == 'active')
        {{-- Partial Withdrawal Modal --}}
        <div class="modal fade" id="partialWithdrawModal" tabindex="-1" aria-labelledby="partialWithdrawModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title">{{ __('messages.process_partial_withdrawal') }}</h5>...</div>
                    <form method="POST" action="{{ route('admin.savings.withdraw.partial', $savingsAccount->id) }}">
                        @csrf
                        <div class="modal-body">
                            <p>{{ __('messages.current_balance') }}: <strong>{{ number_format($savingsAccount->current_balance, 2) }}</strong> BDT</p>
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.withdrawal_amount') }} <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="withdrawal_amount" class="form-control" required max="{{ $savingsAccount->current_balance }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.add_profit_amount_optional') }}</label>
                                <input type="number" step="0.01" name="profit_amount" class="form-control" placeholder="0.00">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.payment_from_account') }} <span class="text-danger">*</span></label>
                                <select name="account_id" class="form-select" required>
                                    <option value="">{{ __('messages.select_account') }}...</option>
                                    @foreach (\App\Models\Account::active()->payment()->get() as $paymentAccount)
                                        <option value="{{ $paymentAccount->id }}">{{ $paymentAccount->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.withdrawal_date') }} <span class="text-danger">*</span></label>
                                <input type="date" name="withdrawal_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="modal-footer"><button type="submit" class="btn btn-primary">{{ __('messages.process_withdrawal') }}</button></div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Final Withdrawal Modal --}}
        <div class="modal fade" id="finalWithdrawModal" tabindex="-1" aria-labelledby="finalWithdrawModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title">{{ __('messages.process_final_withdrawal') }}</h5>...</div>
                    <form class="final-withdraw-form" method="POST" action="{{ route('admin.savings.withdraw.final', $savingsAccount->id) }}">
                        @csrf
                        <div class="modal-body">
                            <p>{{ __('messages.current_balance') }}: <strong class="current-balance-text">{{ number_format($savingsAccount->current_balance, 2) }}</strong> BDT</p>
                            <div class="alert alert-warning">{{ __('messages.final_withdrawal_warning') }}</div>
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.add_profit_amount_optional') }}</label>
                                <input type="number" step="0.01" name="profit_amount" class="form-control profit-amount-input" placeholder="0.00">
                            </div>
                            <div class="mt-3"><h5>{{ __('messages.total_amount_to_pay') }}: <span class="fw-bold text-success total-payable-display"></span> BDT</h5></div>
                            <div class="mb-3 mt-3">
                                <label class="form-label">{{ __('messages.payment_from_account') }} <span class="text-danger">*</span></label>
                                <select name="account_id" class="form-select" required>
                                    <option value="">{{ __('messages.select_account') }}...</option>
                                    @foreach (\App\Models\Account::active()->payment()->get() as $paymentAccount)
                                        <option value="{{ $paymentAccount->id }}">{{ $paymentAccount->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3 mt-3">
                                <label class="form-label">{{ __('messages.withdrawal_date') }} <span class="text-danger">*</span></label>
                                <input type="date" name="withdrawal_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="modal-footer"><button type="submit" class="btn btn-danger">{{ __('messages.confirm_close_account') }}</button></div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('custom-scripts')
    <script>
        $(".flatpickr").flatpickr({
            altInput: true,
            dateFormat: 'Y-m-d',
            altFormat: 'd/m/Y'
        })
    </script>

    <script>
        $(document).ready(function() {
            // Final Withdrawal Modal Logic for a single account page
            const finalWithdrawForm = $('.final-withdraw-form');
            if (finalWithdrawForm.length) {
                const currentBalanceText = finalWithdrawForm.find('.current-balance-text').text().replace(/,/g, '');
                const currentBalance = parseFloat(currentBalanceText);
                const profitInput = finalWithdrawForm.find('.profit-amount-input');
                const totalPayableDisplay = finalWithdrawForm.find('.total-payable-display');

                function updateTotal() {
                    let profit = parseFloat(profitInput.val()) || 0;
                    let totalPayable = currentBalance + profit;
                    totalPayableDisplay.text(totalPayable.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
                }

                // Initialize display on modal open
                $('#finalWithdrawModal').on('show.bs.modal', function() {
                    profitInput.val('');
                    updateTotal();
                });

                // Calculate total on profit input change
                profitInput.on('input', updateTotal);
            }
        });
    </script>
@endpush
