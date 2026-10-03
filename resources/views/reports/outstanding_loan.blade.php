@extends('layout.master')
@section('title', __('messages.outstanding_loan_report') . ' | ' . config('app.name'))

@push('plugin-styles')
    <style>
        .premium-card {
            border: none;
            border-radius: 24px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            background: #fff;
        }

        .premium-header {
            background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 50%, #fcd34d 100%);
            padding: 2.5rem 2rem;
            color: white;
            position: relative;
        }

        .header-shape {
            position: absolute;
            top: 0;
            right: 0;
            opacity: 0.1;
            pointer-events: none;
        }

        .filter-panel {
            background: #f8fafc;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid #e2e8f0;
        }

        .form-label-premium {
            font-size: 0.75rem;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 0.5rem;
            letter-spacing: 0.025em;
        }

        .input-premium,
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 12px !important;
            padding: 0.6rem 1rem !important;
            border: 1px solid #e2e8f0 !important;
            background-color: #fff !important;
            transition: all 0.3s !important;
            height: auto !important;
            min-height: 46px !important;
            display: flex !important;
            align-items: center !important;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            padding: 0 !important;
            line-height: normal !important;
            color: #334155 !important;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
            top: 50% !important;
            transform: translateY(-50%) !important;
            right: 1rem !important;
        }

        .input-premium:focus,
        .select2-container--bootstrap-5.select2-container--focus .select2-selection {
            border-color: #f59e0b !important;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1) !important;
        }

        .table-premium thead th {
            background: #f8fafc;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: #64748b;
            padding: 1rem 1.5rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .table-premium tbody td {
            padding: 1.25rem 1.5rem;
            vertical-align: middle;
            color: #334155;
            font-size: 1rem;
        }

        .badge-soft-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #000!important;
        }

        .badge-soft-warning {
            background: rgba(245, 158, 11, 0.1);
            color: #92400e;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="premium-card">
                <div class="premium-header">
                    <img src="data:image/svg+xml,%3Csvg width='200' height='200' viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 0h200v200H0z' fill='none'/%3E%3Cpath d='M200 0c0 110.457-89.543 200-200 200h200V0z' fill='white' fill-opacity='.05'/%3E%3C/svg%3E" class="header-shape">
                    <div class="d-flex justify-content-between align-items-center position-relative z-1">
                        <div>
                            <h3 class="fw-bold mb-1 text-white">{{ __('messages.outstanding_loan_report') ?? 'Outstanding Loan Report' }}</h3>
                            <p class="mb-0 opacity-75 text-white">{{ __('messages.monitor_due_amounts') ?? 'Monitor and track all active loans with outstanding balances' }}</p>
                        </div>
                        <div class="d-flex gap-2">
                             <div class="bg-white bg-opacity-25 p-3 rounded-4 backdrop-blur shadow-sm d-flex align-items-center">
                                <i data-lucide="alert-circle" class="text-white me-3" style="width: 24px; height: 24px;"></i>
                                <div>
                                    <p class="text-white small opacity-75 mb-0 text-uppercase fw-bold">{{ __('messages.total_due_page') ?? 'Total Due (This Page)' }}</p>
                                    <h4 class="text-white fw-bold mb-0">{{ number_format($outstandingLoans->sum('due_amount'), 2) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4 p-lg-5">
                    <!-- Modern Filter Panel -->
                    <div class="filter-panel shadow-sm border-0">
                        <form action="{{ route('reports.outstanding_loan') }}" method="GET">
                            <div class="row g-3 align-items-end">
                                @role('Admin')
                                <div class="col-md-5">
                                    <label class="form-label-premium">{{ __('messages.filter_by_area') ?? 'Filter by Area' }}</label>
                                    <select name="area_id" id="area_id" class="form-select input-premium select2">
                                        <option value="">All Areas</option>
                                        @foreach ($areas as $area)
                                            <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>
                                                {{ $area->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label-premium">{{ __('messages.filter_by_collector') ?? 'Filter by Field Worker' }}</label>
                                    <select name="collector_id" id="collector_id" class="form-select input-premium select2">
                                        <option value="">All Field Workers</option>
                                        @foreach ($collectors as $collector)
                                            <option value="{{ $collector->id }}" {{ request('collector_id') == $collector->id ? 'selected' : '' }}>
                                                {{ $collector->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @endrole
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-warning text-white w-100 py-2 rounded-3 shadow-sm d-flex align-items-center justify-content-center fw-bold" style="background: #f59e0b; border: none;">
                                        <i data-lucide="filter" class="icon-sm me-2"></i> {{ __('messages.filter') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-premium table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.member_name') ?? 'Member Name' }}</th>
                                    <th>{{ __('messages.area') }}</th>
                                    <th class="text-end">{{ __('messages.loan_amount') }}</th>
                                    <th class="text-end">{{ __('messages.total_payable') }}</th>
                                    <th class="text-end">{{ __('messages.total_paid') }}</th>
                                    <th class="text-end text-danger">{{ __('messages.due_amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($outstandingLoans as $loan)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="p-2 bg-soft-warning rounded-3 me-3">
                                                    <i data-lucide="user" class="icon-xs text-warning"></i>
                                                </div>
                                                <div>
                                                    <a href="{{ route('members.show', $loan->member_id) }}" class="fw-bold text-dark text-decoration-none d-block">
                                                        {{ $loan->member->name }}
                                                    </a>
                                                    <span class="text-muted small">A/C: {{ $loan->member->account_no }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted small">
                                                <i data-lucide="map-pin" class="icon-xs me-1 opacity-50"></i>
                                                {{ $loan->member->area->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-semibold">{{ number_format($loan->loan_amount, 2) }}</td>
                                        <td class="text-end fw-semibold">{{ number_format($loan->total_payable, 2) }}</td>
                                        <td class="text-end text-success fw-semibold">{{ number_format($loan->total_paid, 2) }}</td>
                                        <td class="text-end">
                                            <span class="badge badge-soft-danger px-3 py-2 rounded-pill fw-bolder">
                                                {{ number_format($loan->due_amount, 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 opacity-50">
                                            <i data-lucide="database" class="icon-lg d-block mx-auto mb-3"></i>
                                            {{ __('messages.no_outstanding_loans_found') ?? 'No outstanding loans found based on the selected criteria.' }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if($outstandingLoans->isNotEmpty())
                            <tfoot>
                                <tr class="bg-light fw-bold">
                                    <td colspan="5" class="text-end py-3">{{ __('messages.total_due_page') ?? 'Total Due on this Page' }}:</td>
                                    <td class="text-end text-danger py-3">
                                        <h5 class="mb-0 fw-bold">{{ number_format($outstandingLoans->sum('due_amount'), 2) }}</h5>
                                    </td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>

                        <div class="mt-4">
                            {{ $outstandingLoans->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Lucide Icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Select2
            if (typeof $.fn.select2 !== 'undefined') {
                $('.select2').select2({
                    theme: 'bootstrap-5',
                    width: '100%'
                });
            }
        });
    </script>
@endpush
