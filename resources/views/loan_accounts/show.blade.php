@extends('layout.master')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('loan_accounts.index') }}">{{ __('messages.loan_accounts') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('messages.account_details') }}</li>
        </ol>
    </nav>

    @if (session('success'))
        <div class="alert alert-success shadow-sm border-0 rounded-3 mb-4">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger shadow-sm border-0 rounded-3 mb-4">{{ session('error') }}</div>
    @endif
    
    @push('plugin-styles')
    <link rel="stylesheet" href="{{ asset('build/plugins/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}">
    <style>
        /* Premium Design System - Red/Pink Loans Theme */
        .premium-card {
            border: none;
            border-radius: 1.25rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            background: #fff;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .premium-header {
            background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
            padding: 1.5rem 2rem;
            position: relative;
        }
        
        .premium-header-green {
             background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .premium-header::before, .premium-header-green::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at top right, rgba(255,255,255,0.2), transparent);
            pointer-events: none;
        }

        .premium-title {
            color: #fff;
            font-weight: 700;
            margin: 0;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
        }

        .form-control-premium, .form-select {
            border-radius: 0.75rem;
            padding: 0.65rem 1rem;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            transition: all 0.2s ease;
        }

        .form-control-premium:focus, .form-select:focus {
            background-color: #fff;
            border-color: #f43f5e;
            box-shadow: 0 0 0 4px rgba(244, 63, 94, 0.1);
        }

        .btn-submit {
            background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
            border: none;
            border-radius: 0.75rem;
            padding: 0.75rem 2rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(225, 29, 72, 0.3);
            color: white;
        }
        
        .table-premium {
            border-collapse: separate;
            border-spacing: 0 0.5rem;
        }

        .table-premium thead th {
             border: none;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: #64748b;
            padding: 1rem;
            background: transparent;
            font-weight: 600;
        }

        .table-premium tbody tr {
            background: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
            border-radius: 0.75rem;
            transition: all 0.2s ease;
        }
        
        .table-premium tbody td {
            border: none;
            padding: 1rem;
            vertical-align: middle;
            color: #334155;
            font-weight: 500;
        }
        .table-premium tbody td:first-child { border-top-left-radius: 0.75rem; border-bottom-left-radius: 0.75rem; }
        .table-premium tbody td:last-child { border-top-right-radius: 0.75rem; border-bottom-right-radius: 0.75rem; }

        .detail-item {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            margin-bottom: 0.5rem;
        }
        .detail-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 0.25rem;
            display: block;
        }
        .detail-value {
            font-size: 1rem;
            color: #1e293b;
            font-weight: 600;
        }
        .summary-card {
            border: none;
            border-radius: 1rem;
            color: white;
            padding: 1.5rem;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        .summary-card::after {
            content: '';
            position: absolute;
            width: 100px; height: 100px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            top: -20px; right: -20px;
        }
    </style>
    @endpush

    <div class="row">
        {{-- Loan Main Info --}}
        <div class="col-12 grid-margin">
            <div class="card premium-card">
                <div class="premium-header d-flex justify-content-between align-items-center">
                     <div>
                        <h5 class="premium-title mb-1">
                            <i data-lucide="info" class="icon-sm me-2"></i> {{ $loanAccount->account_no }}
                        </h5>
                        <p class="text-white-50 mb-0 ms-4 ps-1 small">
                             {{ __('messages.member') }}: {{ $loanAccount->member->name }}
                        </p>
                    </div>
                
                    <div class="d-flex align-items-center">
                        @role('Admin')
                        @if($loanAccount->status == 'running' && ($loanAccount->total_payable - $loanAccount->total_paid) > 0)
                            <button type="button" class="btn btn-light text-success fw-bold shadow-sm btn-sm me-2" data-bs-toggle="modal"
                                    data-bs-target="#payOffModal">
                                <i data-lucide="check-circle" class="icon-sm me-1"></i> {{ __('messages.pay_off_loan') }}
                            </button>
                        @endif
                        @endrole
                        <span class="badge bg-white text-{{ $loanAccount->status == 'running' ? 'warning' : ($loanAccount->status == 'paid' ? 'success' : 'danger') }} border-0 shadow-sm px-3 py-2 rounded-pill fw-bold">
                            {{ ucfirst($loanAccount->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-2 col-6">
            <div class="summary-card" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                <p class="mb-1 small text-white-50 text-uppercase fw-bold">{{ __('messages.loan_amount') }}</p>
                <h5 class="mb-0 fw-bold">{{ number_format($loanAccount->loan_amount, 2) }}</h5>
            </div>
        </div>

        <div class="col-md-2 col-6">
             <div class="summary-card" style="background: linear-gradient(135deg, #64748b 0%, #475569 100%);">
                <p class="mb-1 small text-white-50 text-uppercase fw-bold">{{ __('messages.total_installments') }}</p>
                <h5 class="mb-0 fw-bold">{{ $loanAccount->number_of_installments }}</h5>
            </div>
        </div>

        <div class="col-md-2 col-6">
             <div class="summary-card" style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);">
                <p class="mb-1 small text-white-50 text-uppercase fw-bold">{{ __('messages.total_payable') }}</p>
                <h5 class="mb-0 fw-bold">{{ number_format($loanAccount->total_payable, 2) }}</h5>
            </div>
        </div>

        <div class="col-md-2 col-6">
             <div class="summary-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <p class="mb-1 small text-white-50 text-uppercase fw-bold">{{ __('messages.total_paid') }}</p>
                <h5 class="mb-0 fw-bold">{{ number_format($loanAccount->total_paid, 2) }}</h5>
            </div>
        </div>

        <div class="col-md-2 col-6">
             <div class="summary-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                <p class="mb-1 small text-white-50 text-uppercase fw-bold">{{ __('messages.grace_amount') }}</p>
                <h5 class="mb-0 fw-bold">{{ number_format($loanAccount->grace_amount, 2) }}</h5>
            </div>
        </div>

        <div class="col-md-2 col-6">
             <div class="summary-card" style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);">
                <p class="mb-1 small text-white-50 text-uppercase fw-bold">{{ __('messages.due_amount') }}</p>
                <h5 class="mb-0 fw-bold">
                    @if($loanAccount->status == 'paid')
                        0.00
                    @else
                        {{ number_format($loanAccount->total_payable - $loanAccount->total_paid - $loanAccount->grace_amount, 2) }}
                    @endif
                </h5>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Guarantor & Documents Section --}}
        <div class="col-md-5 grid-margin stretch-card">

            <div class="card premium-card h-100">
                <div class="card-body p-4">
                    {{-- Loan Information --}}
                    <h6 class="pb-3 text-primary fw-bold text-uppercase small" style="border-bottom: 2px solid #f1f5f9;">{{ __('messages.loan_details') }}</h6>
                    <div class="row g-2 mt-2">
                        <div class="col-sm-6">
                            <div class="detail-item">
                                <span class="detail-label">{{ __('messages.disbursement_date') }}</span>
                                <span class="detail-value">{{ $loanAccount->disbursement_date->format('d M, Y') }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                             <div class="detail-item">
                                <span class="detail-label">{{ __('messages.interest_rate') }}</span>
                                <span class="detail-value">{{ $loanAccount->interest_rate }} %</span>
                            </div>
                        </div>
                         <div class="col-sm-6">
                            <div class="detail-item">
                                <span class="detail-label">{{ __('messages.number_of_installments') }}</span>
                                <span class="detail-value">{{ $loanAccount->number_of_installments }}</span>
                            </div>
                        </div>
                         <div class="col-sm-6">
                            <div class="detail-item">
                                <span class="detail-label">{{ __('messages.installment_amount') }}</span>
                                <span class="detail-value">{{ number_format($loanAccount->installment_amount, 2) }}</span>
                            </div>
                        </div>
                         <div class="col-sm-6">
                             <div class="detail-item">
                                <span class="detail-label">{{ __('messages.installment_frequency') }}</span>
                                <span class="detail-value text-capitalize">{{ $loanAccount->installment_frequency }}</span>
                            </div>
                        </div>
                         <div class="col-sm-6">
                             <div class="detail-item">
                                <span class="detail-label">{{ __('messages.next_due_date') }}</span>
                                <span class="detail-value">
                                    @if($loanAccount->next_due_date)
                                        {{ \Carbon\Carbon::parse($loanAccount->next_due_date)->format('d M, Y') }}
                                        @if(\Carbon\Carbon::parse($loanAccount->next_due_date)->isPast() && $loanAccount->status == 'running')
                                            <span class="text-danger small ms-1">(Overdue)</span>
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>


                    {{-- Guarantor Information --}}
                    <h6 class="mt-4 pb-3 text-primary fw-bold text-uppercase small" style="border-bottom: 2px solid #f1f5f9;">{{ __('messages.guarantor_information') }}</h6>
                    @if($loanAccount->guarantor)
                         <div class="row g-2 mt-2">
                            <div class="col-12">
                                <div class="detail-item">
                                    @if($loanAccount->guarantor->member)
                                         <span class="detail-label">{{ __('messages.existing_member') }}</span>
                                         <span class="detail-value"><a href="{{ route('members.show', $loanAccount->guarantor->member->id) }}">{{ $loanAccount->guarantor->member->name }}</a></span>
                                    @else
                                        <span class="detail-label">{{ __('messages.outside_person') }}</span>
                                        <span class="detail-value">
                                            {{ $loanAccount->guarantor->name }} <br>
                                            <span class="text-muted small"><i data-lucide="phone" class="icon-xs me-1"></i> {{ $loanAccount->guarantor->phone }}</span>
                                            @if($loanAccount->guarantor->address)
                                                <br><span class="text-muted small"><i data-lucide="map-pin" class="icon-xs me-1"></i> {{ $loanAccount->guarantor->address }}</span>
                                            @endif
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            @if($loanAccount->guarantor->getMedia('guarantor_documents')->count() > 0)
                                <div class="col-12 mt-2">
                                     <h6 class="text-uppercase small fw-bold text-muted mb-2" style="font-size: 0.7rem;">{{ __('messages.guarantor_documents') }}</h6>
                                     <ul class="list-unstyled">
                                        @foreach($loanAccount->guarantor->getMedia('guarantor_documents') as $media)
                                            <li class="mb-1">
                                                <a href="{{ $media->getUrl() }}" target="_blank" class="d-flex align-items-center text-decoration-none text-dark small">
                                                    <i data-lucide="file" class="icon-xs me-2 text-secondary"></i>
                                                    {{ $media->file_name }}
                                                </a>
                                            </li>
                                        @endforeach
                                     </ul>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-muted small mt-2">No guarantor information found.</p>
                    @endif

                    {{-- Loan Documents --}}
                    <h6 class="mt-4 pb-3 text-primary fw-bold text-uppercase small" style="border-bottom: 2px solid #f1f5f9;">{{ __('messages.loan_documents') }}</h6>
                    <ul class="list-unstyled mt-2">
                        @forelse($loanAccount->getMedia('loan_documents') as $media)
                            <li class="mb-2">
                                <a href="{{ $media->getUrl() }}" target="_blank" class="d-flex align-items-center p-2 rounded bg-light border text-decoration-none text-dark">
                                    <i data-lucide="file-text" class="icon-sm me-2 text-primary"></i>
                                    {{ $media->getCustomProperty('document_name', $media->name) }}
                                </a>
                            </li>
                        @empty
                            <li class="text-muted small">No documents uploaded.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        {{-- Installment History Section --}}

        <div class="col-md-7">
            @if($loanAccount->status != 'paid')
                <div class="card premium-card mb-4">
                    <div class="premium-header">
                        <h5 class="premium-title">
                            <i data-lucide="banknote" class="icon-sm me-2"></i> {{ __('messages.make_new_loan_collection') }}
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        @include('loan_accounts.partials._collection_form', [
                            'loanAccount' => $loanAccount,
                            'accounts' => $accounts
                        ])
                    </div>
                </div>
            @endif

            <div class="card premium-card">
                 <div class="premium-header premium-header-green">
                    <h5 class="premium-title">
                        <i data-lucide="history" class="icon-sm me-2"></i> {{ __('messages.installment_history') }}
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-premium mb-0">
                            <thead>
                            <tr>
                                <th class="ps-4">{{ __('messages.payment_date') }}</th>
                                <th class="text-end">{{ __('messages.amount') }}</th>
                                <th class="text-end">{{ __('messages.grace_amount') }}</th>
                                <th>{{ __('messages.collector') }}</th>
                                @role('Admin')
                                <th class="text-center pe-4">{{ __('messages.actions') }}</th>
                                @endrole
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($loanAccount->installments->sortByDesc('payment_date') as $installment)
                                <tr>
                                    <td class="ps-4 fw-medium">{{ $installment->payment_date->format('d M, Y') }}</td>
                                    <td class="text-end fw-bold text-success">{{ number_format($installment->paid_amount, 2) }}</td>
                                    <td class="text-end">{{ number_format($installment->grace_amount, 2) }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $installment->collector->name ?? 'N/A' }}</span></td>
                                    @role('Admin')
                                    <td class="text-center pe-4">
                                        <div class="dropdown">
                                             <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                {{ __('messages.actions') }}
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                                                 <li>
                                                    <a href="{{ route('loan-installments.edit', $installment->id) }}" class="dropdown-item d-flex align-items-center py-2">
                                                        <i data-lucide="edit" class="icon-xs me-2 text-primary"></i> {{ __('messages.edit') }}
                                                    </a>
                                                </li>
                                                <li>
                                                     <form id="delete-installment-{{ $installment->id }}" action="{{ route('loan-installments.destroy', $installment->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="dropdown-item d-flex align-items-center py-2 text-danger" onclick="showDeleteConfirm('delete-installment-{{ $installment->id }}')">
                                                            <i data-lucide="trash-2" class="icon-xs me-2"></i> {{ __('messages.delete') }}
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                    @endrole
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ Auth::user()->hasRole('Admin') ? '5' : '4' }}" class="text-center py-5 text-muted">
                                         <div class="d-flex flex-column align-items-center">
                                            <i data-lucide="calendar-off" class="icon-lg text-light mb-2"></i>
                                            <span>{{ __('messages.no_installments') }}</span>
                                        </div>
                                    </td>
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
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="payment_date" class="form-label">{{ __('messages.payment_date') }} <span class="text-danger">*</span></label>
                            <input type="text" name="payment_date" class="form-control flatpickr" value="{{ date('Y-m-d') }}" required>
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
    <script src="{{ asset('build/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script>
        if (typeof flatpickr !== 'undefined') {
            $(".flatpickr").flatpickr({
                altInput: true,
                dateFormat: 'Y-m-d',
                altFormat: 'd/m/Y'
            });
        }
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
