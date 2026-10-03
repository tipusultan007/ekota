@extends('layout.master')
@section('title', __('messages.loan_installments') . ' | ' . config('app.name'))

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
            background: linear-gradient(135deg, #059669 0%, #10b981 50%, #34d399 100%);
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
            border-color: #10b981 !important;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1) !important;
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
    padding: 0.25rem 0.5rem;
    vertical-align: middle;
    color: #334155;
    font-size: 0.875rem;
}

        .badge-soft-success {
            background: rgba(34, 197, 94, 0.1);
            color: #166534;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s;
            border: none;
        }

        .btn-edit-soft {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
        }

        .btn-edit-soft:hover {
            background: #059669;
            color: white;
        }

        .btn-delete-soft {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .btn-delete-soft:hover {
            background: #ef4444;
            color: white;
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
                            <h3 class="fw-bold mb-1 text-white">{{ __('messages.loan_installment_history') }}</h3>
                            <p class="mb-0 opacity-75 text-white">{{ __('messages.monitor_verify_repayments') }}</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('collections.create') }}" class="btn btn-white bg-white text-success px-4 py-2 rounded-3 shadow-sm d-flex align-items-center fw-bold">
                                <i data-lucide="plus" class="icon-sm me-2"></i> {{ __('messages.new_repayment') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4 p-lg-5">
                    <!-- Modern Filter Panel -->
                    <div class="filter-panel shadow-sm border-0">
                        <form action="{{ route('loan-installments.index') }}" method="GET">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label-premium">{{ __('messages.date_range') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 border-premium"><i data-lucide="calendar" class="icon-xs"></i></span>
                                        <input type="text" name="start_date" class="form-control input-premium border-start-0 flatpickr" 
                                               value="{{ request('start_date') }}" placeholder="Start">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 border-premium"><i data-lucide="calendar" class="icon-xs"></i></span>
                                        <input type="text" name="end_date" class="form-control input-premium border-start-0 flatpickr" 
                                               value="{{ request('end_date') }}" placeholder="End">
                                    </div>
                                </div>

                                @role('Admin')
                                <div class="col-md-3">
                                    <label class="form-label-premium">{{ __('messages.area') }}</label>
                                    <select name="area_id" class="form-select input-premium">
                                        <option value="">{{ __('messages.all_areas') }}</option>
                                        @foreach ($areas as $area)
                                            <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>
                                                {{ $area->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label-premium">{{ __('messages.collected_by') }}</label>
                                    <select name="collector_id" class="form-select input-premium">
                                        <option value="">{{ __('messages.all_collectors') }}</option>
                                        @foreach ($collectors as $collector)
                                            <option value="{{ $collector->id }}" {{ request('collector_id') == $collector->id ? 'selected' : '' }}>
                                                {{ $collector->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @endrole

                                <div class="col-md-6 mb-2">
                                    <label class="form-label-premium">{{ __('messages.member') }}</label>
                                    <select name="member_id" id="member_filter" class="form-select input-premium select2">
                                        <option value="">{{ __('messages.all_members') }}</option>
                                        @foreach ($members as $member)
                                            <option value="{{ $member->id }}" {{ request('member_id') == $member->id ? 'selected' : '' }}>
                                                {{ $member->name }} ({{ $member->account_no }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 text-end">
                                    <button type="submit" class="btn btn-emerald text-white px-4 py-2 rounded-3 shadow-sm d-inline-flex align-items-center" style="background-color: #059669; border: none;">
                                        <i data-lucide="filter" class="icon-sm me-2"></i> {{ __('messages.filter') }}
                                    </button>
                                    <a href="{{ route('loan-installments.index') }}" class="btn btn-light px-4 py-2 rounded-3 border d-inline-flex align-items-center ms-2">
                                        <i data-lucide="refresh-cw" class="icon-sm me-2"></i> {{ __('messages.reset') }}
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center">
                            <i data-lucide="check-circle" class="me-3 text-success"></i>
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center">
                            <i data-lucide="alert-circle" class="me-3 text-danger"></i>
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-premium table-hover mb-0 table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.member') }}</th>
                                    <th>{{ __('messages.loan_account_no') }}</th>
                                    <th class="text-end">{{ __('messages.amount') }} (BDT)</th>
                                    <th class="text-end">{{ __('messages.grace_amount') }}</th>
                                    <th>{{ __('messages.payment_date') }}</th>
                                    <th>{{ __('messages.collected_by') }}</th>
                                    @role('Admin')
                                        <th class="text-center">{{ __('messages.actions') }}</th>
                                    @endrole
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($installments as $installment)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <a href="{{ route('members.show', $installment->member_id) }}" class="fw-bold text-dark text-decoration-none">
                                                        {{ $installment->member->name }}
                                                    </a>
                                                    <p class="mb-0 small text-muted">{{ $installment->member->area->name ?? '' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-bold">
                                                {{ $installment->loanAccount->account_no }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            {{ number_format($installment->paid_amount, 2) }}
                                        </td>
                                        <td class="text-end">
                                            @if($installment->grace_amount > 0)
                                                <span class="text-info-emphasis fw-medium">{{ number_format($installment->grace_amount, 2) }}</span>
                                            @else
                                                <span class="text-muted opacity-25">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center text-muted small">
                                                <i data-lucide="clock" class="icon-xs me-2 opacity-50"></i>
                                                {{ $installment->payment_date->format('d M, Y') }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="small fw-medium">{{ $installment->collector->name }}</span>
                                        </td>
                                        @role('Admin')
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <a href="{{ route('loan-installments.edit', ['loan_installment' => $installment->id]) }}" 
                                                       class="action-btn btn-edit-soft" title="Edit">
                                                        <i data-lucide="edit-3" class="icon-xs"></i>
                                                    </a>
                                                    <form id="delete-installment-{{ $installment->id }}" action="{{ route('loan-installments.destroy', ['loan_installment' => $installment->id]) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="action-btn btn-delete-soft delete-btn" title="Delete" onclick="showDeleteConfirm('delete-installment-{{ $installment->id }}')">
                                                            <i data-lucide="trash-2" class="icon-xs"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        @endrole
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ Auth::user()->hasRole('Admin') ? '7' : '6' }}" class="text-center py-5 opacity-50">
                                            <i data-lucide="database" class="icon-lg d-block mx-auto mb-3"></i>
                                            {{ __('messages.no_loan_installments_found') }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="p-4 border-top">
                            {{ $installments->appends(request()->query())->links() }}
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

            // Flatpickr
            if (typeof flatpickr !== 'undefined') {
                $(".flatpickr").flatpickr({
                    altInput: true,
                    dateFormat: 'Y-m-d',
                    altFormat: 'd/m/Y'
                });
            }

            // Select2
            if (typeof $.fn.select2 !== 'undefined') {
                $('.select2').select2({
                    width: '100%'
                });
            }
        });

        function showDeleteConfirm(formId) {
            Swal.fire({
                title: "{{ __('messages.are_you_sure') ?? 'Are you sure?' }}",
                text: "{{ __('messages.delete_warning') ?? 'This action cannot be undone!' }}",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#ef4444",
                cancelButtonColor: "#64748b",
                confirmButtonText: "{{ __('messages.yes_delete') ?? 'Yes, delete it' }}"
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            });
        }
    </script>
@endpush
