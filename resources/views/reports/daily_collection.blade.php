@extends('layout.master')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Daily Collection Report</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Generate Daily Collection Report</h6>
                    <form action="{{ route('reports.daily_collection.generate') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-5">
                                <label for="report_date" class="form-label">Select Date</label>
                                <input type="date" name="report_date" id="report_date" class="form-control" value="{{ $reportDate->format('Y-m-d') ?? old('report_date', date('Y-m-d')) }}" required>
                            </div>
                            @role('Admin')
                            <div class="col-md-5">
                                <label for="collector_id" class="form-label">Filter by Field Worker (Optional)</label>
                                <select name="collector_id" id="collector_id" class="form-select">
                                    <option value="">All Field Workers</option>
                                    @foreach ($collectors as $collector)
                                        <option value="{{ $collector->id }}" {{ (isset($collectorId) && $collectorId == $collector->id) ? 'selected' : '' }}>
                                            {{ $collector->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @endrole
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Generate</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- রিপোর্ট ফলাফল প্রদর্শনের সেকশন --}}
    {{-- এই সেকশনটি শুধুমাত্র যখন কন্ট্রোলার থেকে রিপোর্ট ডেটা আসবে, তখনই দেখাবে --}}
    @if (isset($savingsCollections))
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Collection Report for: {{ $reportDate->format('d F, Y') }}</h5>
                        <div class="row text-center mb-4">
                            <div class="col-md-4">
                                <h6>Total Savings Collection</h6>
                                <h4 class="text-success">{{ number_format($totalSavings, 2) }}</h4>
                            </div>
                            <div class="col-md-4">
                                <h6>Total Loan Collection</h6>
                                <h4 class="text-primary">{{ number_format($totalLoanInstallments, 2) }}</h4>
                            </div>
                            <div class="col-md-4">
                                <h6>Grand Total</h6>
                                <h4>{{ number_format($grandTotal, 2) }}</h4>
                            </div>
                        </div>

                        {{-- সঞ্চয় আদায়ের টেবিল --}}
                        <h6 class="mt-4">Savings Collections</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead><tr><th>Member</th><th>Account No</th><th>Amount</th><th>Collected By</th></tr></thead>
                                <tbody>
                                @forelse ($savingsCollections as $collection)
                                    <tr><td>{{ $collection->member->name }}</td><td>{{ $collection->savingsAccount->account_no }}</td><td class="text-end">{{ number_format($collection->amount, 2) }}</td><td>{{ $collection->collector->name }}</td></tr>
                                @empty
                                    <tr><td colspan="4" class="text-center">No savings collected on this date.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- ঋণ কিস্তি আদায়ের টেবিল --}}
                        <h6 class="mt-4">Loan Installments</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead><tr><th>Member</th><th>Account No</th><th>Amount</th><th>Collected By</th></tr></thead>
                                <tbody>
                                @forelse ($loanInstallments as $installment)
                                    <tr><td>{{ $installment->member->name }}</td><td>{{ $installment->loanAccount->account_no }}</td><td class="text-end">{{ number_format($installment->paid_amount, 2) }}</td><td>{{ $installment->collector->name }}</td></tr>
                                @empty
                                    <tr><td colspan="4" class="text-center">No loan installments collected on this date.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
