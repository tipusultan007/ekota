@extends('layout.master')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('loan_accounts.index') }}">{{ __('messages.loan_accounts') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('messages.account_details') }}</li>
        </ol>
    </nav>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        {{-- Loan Main Info --}}
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                        {{-- Left Side: Title and Member Info --}}
                        <div class="mb-3 mb-md-0">
                            <h5 class="card-title">{{ __('messages.loan_summary') }}: {{ $loanAccount->account_no }}</h5>
                            <p class="text-muted mb-0">
                                {{ __('messages.member') }}: <a href="{{ route('members.show', $loanAccount->member_id) }}">{{ $loanAccount->member->name }}</a>
                            </p>
                        </div>

                        {{-- Right Side: Actions and Status --}}
                        <div class="d-flex align-items-center">
                            @role('Admin')
                            @if($loanAccount->status == 'running' && ($loanAccount->total_payable - $loanAccount->total_paid) > 0)
                                <button type="button" class="btn btn-success btn-sm me-2" data-bs-toggle="modal"
                                        data-bs-target="#payOffModal">
                                    <i data-lucide="check-circle" class="icon-sm me-1"></i> {{ __('messages.pay_off_loan') }}
                                </button>
                            @endif
                            @endrole
                            <span class="badge bg-{{ $loanAccount->status == 'running' ? 'warning' : ($loanAccount->status == 'paid' ? 'success' : 'danger') }}" style="font-size: 0.9rem;">
                            {{ ucfirst($loanAccount->status) }}
                        </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-2">
        <div class="col-md-2 col-6 grid-margin">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <p class="text-muted mb-1 small">{{ __('messages.loan_amount') }}</p>
                    <h5 class="mb-0">{{ number_format($loanAccount->loan_amount, 2) }}</h5>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-6 grid-margin">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <p class="text-muted mb-1 small">{{ __('messages.total_installments') }}</p>
                    <h5 class="mb-0">{{ $loanAccount->number_of_installments }}</h5>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-6 grid-margin">
            <div class="card bg-info">
                <div class="card-body text-center">
                    <p class="text-muted mb-1 small">{{ __('messages.total_payable') }}</p>
                    <h5 class="mb-0">{{ number_format($loanAccount->total_payable, 2) }}</h5>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-6 grid-margin">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <p class="mb-1 small">{{ __('messages.total_paid') }}</p>
                    <h5 class="mb-0">{{ number_format($loanAccount->total_paid, 2) }}</h5>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-6 grid-margin">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <p class="mb-1 small">{{ __('messages.grace_amount') }}</p>
                    <h5 class="mb-0">{{ number_format($loanAccount->grace_amount, 2) }}</h5>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-6 grid-margin">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <p class="mb-1 small">{{ __('messages.due_amount') }}</p>
                    <h5 class="mb-0">
                        @if($loanAccount->status == 'paid')
                            0.00
                        @else
                            {{ number_format($loanAccount->total_payable - $loanAccount->total_paid - $loanAccount->grace_amount, 2) }}
                        @endif
                    </h5>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Guarantor & Documents Section --}}
        <div class="col-md-5 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">{{ __('messages.guarantor_information') }}</h6>
                    @if($loanAccount->guarantor)
                        @if($loanAccount->guarantor->member)
                            <p><strong>{{ __('messages.existing_member') }}</strong></p>
                            <p><strong>{{ __('messages.member') }}:</strong> <a href="{{ route('members.show', $loanAccount->guarantor->member->id) }}">{{ $loanAccount->guarantor->member->name }}</a></p>
                            <p><strong>Phone:</strong> {{ $loanAccount->guarantor->member->mobile_no }}</p>
                        @else
                            <p><strong>{{ __('messages.outside_person') }}</strong></p>
                            <p><strong>{{ __('messages.member') }}:</strong> {{ $loanAccount->guarantor->name }}</p>
                            <p><strong>Phone:</strong> {{ $loanAccount->guarantor->phone }}</p>
                            <p><strong>Address:</strong> {{ $loanAccount->guarantor->address }}</p>
                        @endif
                        <hr>
                        <p><strong>{{ __('messages.loan_documents') }}</strong></p>
                    @else
                        <p class="text-muted">No guarantor information found.</p>
                    @endif

                    <ul class="list-unstyled">
                        @forelse($loanAccount->getMedia('loan_documents') as $media)
                            <li><a href="{{ $media->getUrl() }}" target="_blank">{{ $media->getCustomProperty('document_name', $media->name) }}</a></li>
                        @empty
                            <li>No documents uploaded.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        {{-- Installment History Section --}}
        <div class="col-md-7">
            @if($loanAccount->status != 'paid')
                <div class="card my-3">
                    <div class="card-header bg-primary">
                        <h5 class="card-title mb-0 text-white">{{ __('messages.make_new_loan_collection') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('loan-installments.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="loan_account_id" value="{{ $loanAccount->id }}">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">{{ __('messages.payment_date') }}</label>
                                    <input type="text" name="payment_date" class="form-control flatpickr" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">{{ __('messages.amount') }} <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="paid_amount" id="paid_amount_input" class="form-control" required>
                                </div>
                                <div class="col-md-4 mb-3" id="grace_amount_wrapper">
                                    <label class="form-label">{{ __('messages.grace_amount') }}</label>
                                    <input type="number" step="0.01" name="grace_amount" id="grace_amount_input" class="form-control" placeholder="0.00">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">{{ __('messages.deposit_to') }} <span class="text-danger">*</span></label>
                                    <select name="account_id" class="form-select" required>
                                        @foreach ($accounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">{{ __('messages.notes') }}</label>
                                    <textarea name="notes" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">{{ __('messages.submit_installment') }}</button>
                        </form>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header bg-success">
                    <h6 class="card-title mb-0 text-white">{{ __('messages.installment_history') }}</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                            <tr>
                                <th>{{ __('messages.payment_date') }}</th>
                                <th>{{ __('messages.amount') }}</th>
                                <th>{{ __('messages.grace_amount') }}</th>
                                <th>{{ __('messages.member') }}</th>
                                @role('Admin')
                                <th class="text-center">{{ __('messages.actions') }}</th>
                                @endrole
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($loanAccount->installments->sortByDesc('payment_date') as $installment)
                                <tr>
                                    <td>{{ $installment->payment_date->format('d M, Y') }}</td>
                                    <td>{{ number_format($installment->paid_amount, 2) }}</td>
                                    <td>{{ number_format($installment->grace_amount, 2) }}</td>
                                    <td>{{ $installment->collector->name ?? 'N/A' }}</td>
                                    @role('Admin')
                                    <td class="text-center">
                                        <div class="d-inline-flex">
                                            <a href="{{ route('loan-installments.edit', $installment->id) }}" class="btn btn-primary btn-xs me-1" title="{{ __('messages.actions') }}">
                                                <i data-lucide="edit" class="icon-xs"></i>
                                            </a>
                                            <form id="delete-installment-{{ $installment->id }}" action="{{ route('loan-installments.destroy', $installment->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-danger btn-xs" title="{{ __('messages.actions') }}" onclick="showDeleteConfirm('delete-installment-{{ $installment->id }}')">
                                                    <i data-lucide="trash-2" class="icon-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                    @endrole
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ Auth::user()->hasRole('Admin') ? '5' : '4' }}" class="text-center">{{ __('messages.no_installments') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Pay Off Modal --}}
    @role('Admin')
    <div class="modal fade" id="payOffModal" tabindex="-1" aria-labelledby="payOffModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="payOffModalLabel">{{ __('messages.confirm_loan_pay_off') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.loan_accounts.pay_off', $loanAccount->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>{{ __('messages.confirm_pay_off_text') }}</p>
                        <div class="alert alert-secondary">
                            <div class="d-flex justify-content-between">
                                <span>{{ __('messages.remaining_due') }}</span>
                                <strong id="dueAmountDisplay">{{ number_format($loanAccount->total_payable - $loanAccount->total_paid, 2) }}</strong>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between">
                                <span>{{ __('messages.final_amount_to_collect') }}</span>
                                <strong id="finalPaymentDisplay" class="text-success" style="font-size: 1.2rem;">{{ number_format($loanAccount->total_payable - $loanAccount->total_paid, 2) }}</strong>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="grace_amount" class="form-label">{{ __('messages.grace_amount') }} ({{ __('messages.discount') }})</label>
                            <input type="number" step="0.01" name="grace_amount" id="grace_amount_input" class="form-control" placeholder="0.00">
                        </div>
                        <div class="mb-3">
                            <label for="account_id" class="form-label">{{ __('messages.deposit_to') }} <span class="text-danger">*</span></label>
                            <select name="account_id" class="form-select" required>
                                <option value="">{{ __('messages.select_account') }}</option>
                                @foreach (\App\Models\Account::where('is_active', true)->get() as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="payment_date" class="form-label">{{ __('messages.payment_date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">{{ __('messages.notes') }} ({{ __('messages.optional') }})</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="{{ __('messages.notes_placeholder') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                        <button type="submit" class="btn btn-success">{{ __('messages.confirm_and_pay_off') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endrole

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
        document.addEventListener('DOMContentLoaded', function () {
            const payOffModal = document.getElementById('payOffModal');
            if (payOffModal) {
                const dueAmount = parseFloat({{ $loanAccount->total_payable - $loanAccount->total_paid }});
                const graceInput = payOffModal.querySelector('#grace_amount_input');
                const finalPaymentDisplay = payOffModal.querySelector('#finalPaymentDisplay');

                graceInput.addEventListener('input', function () {
                    let grace = parseFloat(this.value) || 0;
                    if (grace > dueAmount) {
                        grace = dueAmount;
                        this.value = dueAmount.toFixed(2);
                    }
                    let finalPayment = dueAmount - grace;
                    finalPaymentDisplay.textContent = finalPayment.toFixed(2);
                });
            }
        });
    </script>
@endpush
