@extends('layout.master')
@push('plugin-styles')
    <link rel="stylesheet" href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}">
@endpush
@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Savings Collection History</li>
        </ol>
    </nav>


    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="card-title">Savings Collection Log</h6>
                        <a href="{{ route('savings-collections.create') }}" class="btn btn-primary btn-sm">
                            <i data-lucide="plus" class="icon-sm me-2"></i> Make New Collection
                        </a>
                    </div>
                    <!-- Date Range Filter Form -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <form method="GET" action="{{ route('savings-collections.index') }}" class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="text" class="form-control flatpickr" id="start_date" name="start_date"
                                           value="{{ request('start_date') }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="text" class="form-control flatpickr" id="end_date" name="end_date"
                                           value="{{ request('end_date') }}">
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                    <a href="{{ route('savings-collections.index') }}" class="btn btn-secondary">Reset</a>
                                </div>
                                @if(request()->has('start_date') || request()->has('end_date'))
                                    <div class="col-md-3 text-end">
                                    <span class="text-muted">
                                        Filtered Results
                                    </span>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>Receipt #</th>
                                <th>Member Name</th>
                                <th>Account No</th>
                                <th>Amount (BDT)</th>
                                <th>Collection Date</th>
                                <th>Collected By</th>
                                @role('Admin')<th>Actions</th>@endrole
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($collections as $collection)
                                <tr>
                                    <td>{{ $collection->receipt_no ?? $collection->id }}</td>
                                    <td>
                                        <a href="{{ route('members.show', $collection->member_id) }}">{{ $collection->member->name }}</a>
                                    </td>
                                    <td>{{ $collection->savingsAccount->account_no }}</td>
                                    <td class="text-end">{{ number_format($collection->amount, 2) }}</td>
                                    <td>{{ $collection->collection_date->format('d M, Y') }}</td>
                                    <td>{{ $collection->collector->name }}</td>
                                    @role('Admin')
                                    <td>
                                        <a href="{{ route('savings-collections.edit', $collection->id) }}" class="btn btn-primary btn-xs">Edit</a>
                                        <form action="{{ route('savings-collections.destroy', $collection->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-xs">Delete</button>
                                        </form>
                                    </td>
                                    @endrole
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No savings collections found.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>

                        <div class="mt-4">
                            {{ $collections->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('custom-scripts')
    <script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('build/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script>
        $(".flatpickr").flatpickr({
            altInput: true,
            dateFormat: 'Y-m-d',
            altFormat: 'd/m/Y'
        })
    </script>
@endpush
