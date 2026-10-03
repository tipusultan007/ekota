@extends('layout.master')

@push('plugin-styles')
    <link rel="stylesheet" href="{{ asset('build/plugins/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}">
    <style>
        .premium-card {
            border: none;
            border-radius: 24px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            background: #fff;
            margin-bottom: 2rem;
        }

        .premium-header {
            background: linear-gradient(135deg, #e11d48 0%, #f43f5e 50%, #fb7185 100%);
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
            background: #fdf2f8;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid #fce7f3;
        }

       
        .input-premium,
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 12px !important;
            padding: 0.6rem 1rem !important;
            border: 1px solid #f9a8d4 !important;
            background-color: #fff !important;
            transition: all 0.3s !important;
            min-height: 46px !important;
            display: block !important;
            width: 100% !important;
        }
.input-group {
            flex-wrap: nowrap !important;
        }
        .input-group-text-premium {
            background: #fff !important;
            border: 1px solid #f9a8d4 !important;
            border-right: none !important;
            border-radius: 12px 0 0 12px !important;
            color: #9f1239 !important;
            padding: 0.5rem 0.8rem !important;
            display: flex !important;
            align-items: center !important;
        }

        .input-group > .input-premium {
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
            flex: 1 1 auto !important;
            width: 1% !important;
        }

        .table-premium thead th {
            background: #fdf2f8;
            color: #9f1239;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 1.25rem 1.5rem;
            border: none;
        }

        .table-premium tbody td {
            padding: 1.25rem 1.5rem;
            vertical-align: middle;
            color: #334155;
            font-weight: 500;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-premium tbody tr:hover {
            background-color: #fff1f2;
        }

        .btn-soft-primary { background: rgba(37, 99, 235, 0.1); color: #2563eb; }
        .btn-soft-primary:hover { background: #2563eb; color: #fff; }
        
        .btn-soft-danger { background: rgba(225, 29, 72, 0.1); color: #e11d48; }
        .btn-soft-danger:hover { background: #e11d48; color: #fff; }

        .btn-header {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            backdrop-filter: blur(4px);
            transition: all 0.3s;
        }

        .btn-header:hover {
            background: white;
            color: #e11d48;
            transform: translateY(-2px);
        }

        .pagination-premium .page-link {
            border-radius: 8px;
            margin: 0 3px;
            color: #e11d48;
            border: 1px solid #f9a8d4;
        }

        .pagination-premium .page-item.active .page-link {
            background: #e11d48;
            border-color: #e11d48;
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
                            <h3 class="fw-bold mb-1 text-white">{{ __('messages.salary_payment_history') ?? 'Salary Payment History' }}</h3>
                            <p class="mb-0 opacity-75 text-white">{{ __('messages.review_and_manage_employee_salaries') ?? 'Review and manage employee salary payments and history' }}</p>
                        </div>
                        <a href="{{ route('admin.salaries.create') }}" class="btn btn-header">
                            <i data-lucide="plus-circle" class="icon-sm me-2"></i> {{ __('messages.pay_new_salary') ?? 'Pay New Salary' }}
                        </a>
                    </div>
                </div>

                <div class="card-body p-4 p-lg-5">
                    {{-- Filter Panel --}}
                    <div class="filter-panel shadow-sm">
                        <form action="{{ route('admin.salaries.index') }}" method="GET">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text-premium"><i data-lucide="users" class="icon-xs"></i></span>
                                        <select name="user_id" class="form-select select2">
                                            <option value="">{{ __('messages.all_employees') ?? 'All Employees' }}</option>
                                            @foreach($employees as $employee)
                                                <option value="{{ $employee->id }}" {{ request('user_id') == $employee->id ? 'selected' : '' }}>
                                                    {{ $employee->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text-premium"><i data-lucide="calendar-range" class="icon-xs"></i></span>
                                        <input type="text" name="salary_month" id="month_picker" class="form-control input-premium" value="{{ request('salary_month') }}" placeholder="{{ __('messages.select_month') ?? 'Select Month' }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-pay w-100" style="background: #e11d48; color: white; border-radius: 12px; height: 42px; border: none; font-weight: 600;">
                                        <i data-lucide="filter" class="icon-xs me-2"></i> {{ __('messages.filter') ?? 'Filter' }}
                                    </button>
                                </div>
                                <div class="col-md-2 text-center text-md-start">
                                    <a href="{{ route('admin.salaries.index') }}" class="text-rose fw-bold text-decoration-none small">
                                        <i data-lucide="refresh-cw" class="icon-xs me-1"></i> {{ __('messages.reset') ?? 'Reset' }}
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-premium table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.payment_date') ?? 'Date' }}</th>
                                    <th>{{ __('messages.employee') ?? 'Employee' }}</th>
                                    <th class="text-center">{{ __('messages.salary_month') ?? 'Month' }}</th>
                                    <th class="text-end">{{ __('messages.amount') ?? 'Amount' }}</th>
                                    <th class="text-center">{{ __('messages.actions') ?? 'Actions' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($salaries as $salary)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i data-lucide="calendar" class="icon-xs text-muted me-2"></i>
                                                {{ $salary->payment_date->format('d M, Y') }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-soft-rose rounded-circle p-2 me-3" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; background: #fff1f2; color: #e11d48;">
                                                    <i data-lucide="user" class="icon-xs"></i>
                                                </div>
                                                <span class="fw-bold">{{ $salary->user->name }}</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge rounded-pill px-3 py-2" style="background: #fdf2f8; color: #9f1239; font-weight: 600;">
                                                <i data-lucide="clock" class="icon-xs me-1"></i> {{ $salary->salary_month }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold text-dark">
                                            {{ number_format($salary->amount, 2) }}
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="{{ route('admin.salaries.edit', $salary->id) }}" class="btn btn-soft-primary btn-sm rounded-3 px-3" title="{{ __('messages.edit') }}">
                                                    <i data-lucide="edit-3" class="icon-xs"></i>
                                                </a>
                                                <form action="{{ route('admin.salaries.destroy', $salary->id) }}" method="POST" class="d-inline delete-confirm">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-soft-danger btn-sm rounded-3 px-3" title="{{ __('messages.delete') }}">
                                                        <i data-lucide="trash-2" class="icon-xs"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="p-5 text-center text-muted">
                                            <i data-lucide="inbox" class="d-block mx-auto mb-3 opacity-25" style="width: 48px; height: 48px;"></i>
                                            <p class="mb-0">{{ __('messages.no_salary_records_found') ?? 'No salary records found for this period.' }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 pagination-premium">
                        {{ $salaries->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('plugin-scripts')
    <script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('build/plugins/select2/select2.min.js') }}"></script>
    <script src="{{ asset('build/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            $('.select2').select2({
                width: '100%',
                placeholder: "-- Select Employee --",
                allowClear: true
            });

            $("#month_picker").flatpickr({
                altInput: true,
                dateFormat: 'Y-m',
                altFormat: 'F Y'
            });

            $('.delete-confirm').on('submit', function(e) {
                if(!confirm('{{ __('messages.confirm_delete') ?? 'Are you sure you want to delete this record?' }}')) {
                    e.preventDefault();
                }
            });
        });
    </script>
@endpush
