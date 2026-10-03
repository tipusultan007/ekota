@extends('layout.master')

@section('content')
<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('members.index') }}">{{ __('messages.member_management') }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">Bulk Data Import</li>
    </ol>
</nav>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm border-0" style="border-radius: 15px; overflow: hidden;">
            <div class="card-header bg-white py-0 border-0">
                <ul class="nav nav-tabs nav-tabs-line" id="lineTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active py-3" id="member-tab" data-bs-toggle="tab" href="#member" role="tab" aria-controls="member" aria-selected="true">
                            <i data-lucide="users" class="icon-sm me-1"></i> Member & Loan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-3" id="savings-tab" data-bs-toggle="tab" href="#savings" role="tab" aria-controls="savings" aria-selected="false">
                            <i data-lucide="piggy-bank" class="icon-sm me-1"></i> Savings Collection
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-3" id="loan-tab" data-bs-toggle="tab" href="#loan" role="tab" aria-controls="loan" aria-selected="false">
                            <i data-lucide="banknote" class="icon-sm me-1"></i> Loan Payment
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body p-4">
                @if (session('error'))
                    <div class="alert alert-danger border-0 shadow-sm mb-4">
                        <i data-lucide="alert-circle" class="me-2"></i>
                        {{ session('error') }}
                    </div>
                @endif

                <div class="tab-content mt-3" id="lineTabContent">
                    {{-- Tab 1: Member & Loan --}}
                    <div class="tab-pane fade show active" id="member" role="tabpanel" aria-labelledby="member-tab">
                        <div class="row">
                            <div class="col-md-7">
                                <h5 class="mb-4 d-flex align-items-center"><i data-lucide="plus-circle" class="me-2 text-primary"></i> Create Members & Initial Loans</h5>
                                <form action="{{ route('members.import.store') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-4">
                                        <label for="member_file" class="form-label fw-bold">Select Excel/CSV File</label>
                                        <input type="file" class="form-control" id="member_file" name="file" required>
                                        <p class="text-muted small mt-2">Required headers: <code>date, account_no, name, mobile_no, area, loan_amount</code></p>
                                    </div>
                                    <button type="submit" class="btn btn-primary px-4 py-2"><i data-lucide="upload" class="me-1 icon-sm"></i> Start Member Import</button>
                                </form>
                            </div>
                            <div class="col-md-5">
                                <div class="p-4 rounded-4" style="background: rgba(99, 102, 241, 0.05); border: 1px dashed rgba(99, 102, 241, 0.2);">
                                    <h6 class="fw-bold text-primary mb-3">Logic applied:</h6>
                                    <ul class="small mb-0 text-muted">
                                        <li class="mb-2"><strong>Member</strong>: Created with provided info.</li>
                                        <li class="mb-2"><strong>Savings</strong>: Automatic General account created.</li>
                                        <li class="mb-2"><strong>Loan</strong>: If <code>loan_amount</code> > 0, creates daily loan with 15% interest & 2% fee.</li>
                                        <li><strong>Area</strong>: Matches existing or creates new one.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tab 2: Savings Collection --}}
                    <div class="tab-pane fade" id="savings" role="tabpanel" aria-labelledby="savings-tab">
                        <div class="row">
                            <div class="col-md-7">
                                <h5 class="mb-4 d-flex align-items-center"><i data-lucide="save" class="me-2 text-success"></i> Bulk Savings Collection</h5>
                                <form action="{{ route('members.import.savings') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-4">
                                        <label for="savings_file" class="form-label fw-bold">Select Excel/CSV File</label>
                                        <input type="file" class="form-control" id="savings_file" name="file" required>
                                        <p class="text-muted small mt-2">Required headers: <code>account_no, date, deposit</code></p>
                                    </div>
                                    <button type="submit" class="btn btn-success text-white px-4 py-2"><i data-lucide="upload" class="me-1 icon-sm"></i> Start Savings Import</button>
                                </form>
                            </div>
                            <div class="col-md-5">
                                <div class="p-4 rounded-4" style="background: rgba(16, 185, 129, 0.05); border: 1px dashed rgba(16, 185, 129, 0.2);">
                                    <h6 class="fw-bold text-success mb-3">Logic applied:</h6>
                                    <ul class="small mb-0 text-muted">
                                        <li class="mb-2"><strong>Target</strong>: Direct deposit to member's 'General' savings account.</li>
                                        <li class="mb-2"><strong>Accounting</strong>: Records Cash (Debit) and Savings Payable (Credit).</li>
                                        <li><strong>Automation</strong>: Updates balance and auto-calculates next due date.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tab 3: Loan Payment --}}
                    <div class="tab-pane fade" id="loan" role="tabpanel" aria-labelledby="loan-tab">
                        <div class="row">
                            <div class="col-md-7">
                                <h5 class="mb-4 d-flex align-items-center"><i data-lucide="credit-card" class="me-2 text-warning"></i> Bulk Loan Repayment</h5>
                                <form action="{{ route('members.import.loans') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-4">
                                        <label for="loan_file" class="form-label fw-bold">Select Excel/CSV File</label>
                                        <input type="file" class="form-control" id="loan_file" name="file" required>
                                        <p class="text-muted small mt-2">Required headers: <code>account_no, date, loan_paid</code></p>
                                    </div>
                                    <button type="submit" class="btn btn-warning text-white px-4 py-2"><i data-lucide="upload" class="me-1 icon-sm"></i> Start Loan Import</button>
                                </form>
                            </div>
                            <div class="col-md-5">
                                <div class="p-4 rounded-4" style="background: rgba(245, 158, 11, 0.05); border: 1px dashed rgba(245, 158, 11, 0.2);">
                                    <h6 class="fw-bold text-warning mb-3">Logic applied:</h6>
                                    <ul class="small mb-0 text-muted">
                                        <li class="mb-2"><strong>Target</strong>: Applied to member's active 'running' loan account.</li>
                                        <li class="mb-2"><strong>Accounting</strong>: Auto-splits payment between Principal and Interest.</li>
                                        <li><strong>Automation</strong>: Updates total paid, checks if loan is fully cleared, and sets next due date.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('custom-scripts')
<script>
    $(document).ready(function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
@endpush
