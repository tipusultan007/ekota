@extends('layout.master')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Outstanding Loan Report</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Filter Report</h6>
                    {{-- এই রিপোর্টটি GET রিকোয়েস্ট ব্যবহার করবে, তাই ফর্মের মেথড GET --}}
                    <form action="{{ route('reports.outstanding_loan') }}" method="GET">
                        <div class="row">
                            @role('Admin')
                            <div class="col-md-5">
                                <label for="area_id" class="form-label">Filter by Area</label>
                                <select name="area_id" id="area_id" class="form-select">
                                    <option value="">All Areas</option>
                                    @foreach ($areas as $area)
                                        <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>
                                            {{ $area->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label for="collector_id" class="form-label">Filter by Field Worker</label>
                                <select name="collector_id" id="collector_id" class="form-select">
                                    <option value="">All Field Workers</option>
                                    @foreach ($collectors as $collector)
                                        <option value="{{ $collector->id }}" {{ request('collector_id') == $collector->id ? 'selected' : '' }}>
                                            {{ $collector->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @endrole
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Outstanding Loan Report</h5>
                    <p class="text-muted">List of all running loans with their due amounts.</p>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>Member Name</th>
                                <th>Area</th>
                                <th>Loan Amount</th>
                                <th>Total Payable</th>
                                <th>Total Paid</th>
                                <th class="text-danger">Due Amount</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($outstandingLoans as $loan)
                                <tr>
                                    <td>
                                        <a href="{{ route('members.show', $loan->member_id) }}">{{ $loan->member->name }}</a>
                                    </td>
                                    <td>{{ $loan->member->area->name ?? 'N/A' }}</td>
                                    <td>{{ number_format($loan->loan_amount, 2) }}</td>
                                    <td>{{ number_format($loan->total_payable, 2) }}</td>
                                    <td>{{ number_format($loan->total_paid, 2) }}</td>
                                    <td class="text-danger fw-bold">{{ number_format($loan->due_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No outstanding loans found based on the selected criteria.</td>
                                </tr>
                            @endforelse
                            </tbody>
                            <tfoot>
                            <tr class="table-light fw-bold">
                                <td colspan="5" class="text-end">Total Due on this Page:</td>
                                <td class="text-danger">{{ number_format($outstandingLoans->sum('due_amount'), 2) }}</td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{-- Pagination লিঙ্কগুলো ফিল্টারসহ কাজ করার জন্য appends() ব্যবহার করা হয়েছে --}}
                        {{ $outstandingLoans->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
