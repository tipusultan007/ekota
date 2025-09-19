@extends('layout.master')

@push('plugin-styles')
    <link href="{{ asset('build/plugins/datatables.net-bs5/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
    <style>
        .dt-layout-full{
            padding: 0 !important;
        }
        label[for="dt-length-0"] {
            display: none;
        }
        @media(max-width: 767px) {
            .dt-search {
                display: flex;
                flex-direction: column;
            }
            .dt-length{
                display: none;
            }
        }
    </style>
@endpush
@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">My Collections</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-header bg-primary">
                    <h6 class="card-title mb-0 text-white">My Collection History</h6>
                </div>
                <div class="card-body px-0">

                    <ul class="nav nav-tabs nav-tabs-line" id="myCollectionTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="savings-tab" data-bs-toggle="tab" href="#savings" role="tab" aria-controls="savings" aria-selected="true">Savings Collections</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="loans-tab" data-bs-toggle="tab" href="#loans" role="tab" aria-controls="loans" aria-selected="false">Loan Installments</a>
                        </li>
                    </ul>

                    <div class="tab-content border border-top-0 px-0" id="myCollectionTabContent">

                        {{-- Savings Collections Tab --}}
                        <div class="tab-pane fade show active" id="savings" role="tabpanel" aria-labelledby="savings-tab">
                            <div class="table-responsive">
                                <table id="savingsDataTable" class="table">
                                    <thead class="table-secondary">
                                    <tr>
                                        <th>Date</th>
                                        <th>Member</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                    </thead>
                                    {{-- tbody খালি থাকবে --}}
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Loan Installments Tab --}}
                        <div class="tab-pane fade" id="loans" role="tabpanel" aria-labelledby="loans-tab">
                            <div class="table-responsive">
                                <table id="loansDataTable" class="table">
                                    <thead class="table-secondary">
                                    <tr>
                                        <th>Date</th>
                                        <th>Name</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
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
    <script src="{{ asset('build/plugins/datatables.net/dataTables.min.js') }}"></script>
    <script src="{{ asset('build/plugins/datatables.net-bs5/dataTables.bootstrap5.min.js') }}"></script>
@endpush

@push('custom-scripts')
    <script>
        $(document).ready(function() {
            // Initialize DataTable for Savings (Server-Side)
            $('#savingsDataTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('api.collections.savings.data') }}',
                columns: [
                    { data: 'collection_date', name: 'collection_date' },
                    { data: 'name', name: 'name' },
                    { data: 'amount', name: 'amount', className: 'text-end' }
                ],
                "order": [[ 0, "desc" ]],
                "language": { "search": "Search:" }
            });

            // Initialize DataTable for Loans (Server-Side)
            $('#loansDataTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('api.collections.loans.data') }}',
                columns: [
                    { data: 'payment_date', name: 'payment_date' },
                    { data: 'name', name: 'name' },
                    { data: 'paid_amount', name: 'paid_amount', className: 'text-end' }
                ],
                "order": [[ 0, "desc" ]],
                "language": { "search": "Search:" }
            });
        });
    </script>
@endpush
