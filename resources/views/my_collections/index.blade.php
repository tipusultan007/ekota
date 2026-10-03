@extends('layout.master')

@push('plugin-styles')
    <style>
        .card { border: none; border-radius: 12px; overflow: hidden; }
        .card-header { border-bottom: none; padding: 1.25rem; }
        .nav-tabs-line .nav-link { border-bottom: 3px solid transparent; font-weight: 600; color: #6c757d; padding: 12px 20px; }
        .nav-tabs-line .nav-link.active { border-bottom-color: #6571ff; color: #6571ff; background: transparent; }
        
        .filter-section { background: #fff; padding: 15px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        
        /* Attendance Sheet Styles */
        .attendance-container {
            position: relative;
            max-height: 70vh;
            overflow: auto;
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }
        .table-attendance {
            font-size: 0.75rem;
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }
        .table-attendance th, .table-attendance td {
            border: 1px solid #e9ecef;
            padding: 5px 3px !important;
            text-align: center;
            min-width: 30px;
        }
        .table-attendance thead th {
            position: sticky;
            top: 0;
            background-color: #f8f9fc;
            z-index: 10;
            font-weight: 700;
        }
        .sticky-col {
            position: sticky;
            left: 0;
            background-color: #fff;
            z-index: 5;
            min-width: 120px !important;
            text-align: left !important;
            padding-left: 10px !important;
        }
        .sticky-col-2 {
            position: sticky;
            left: 120px;
            background-color: #fff;
            z-index: 5;
            min-width: 60px !important;
        }
        .table-attendance thead th.sticky-col,
        .table-attendance thead th.sticky-col-2 {
            z-index: 15;
        }
        .total-col {
            background-color: #f1f5f9;
            font-weight: bold;
            min-width: 60px !important;
        }
        .day-cell { color: #444; }
        .day-cell.has-value { background-color: #eef2ff; font-weight: 700; color: #6366f1; }
        
        /* Print Header - Hidden by default */
        .print-header {
            display: none;
            text-align: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #333;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 8mm;
            }
            body {
                background: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .page-breadcrumb, .filter-section, .nav-tabs-line, .footer, .navbar, .sidebar-wrapper, .card-header, .d-print-none {
                display: none !important;
            }
            .main-wrapper { 
                margin: 0 !important; 
                padding: 0 !important; 
                display: block !important;
            }
            .page-wrapper {
                margin: 0 !important;
                padding: 0 !important;
            }
            .page-content {
                padding: 0 !important;
                margin: 0 !important;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
            }
            .attendance-container {
                max-height: none !important;
                overflow: visible !important;
                border: none !important;
                padding: 0 !important;
            }
            .table-attendance {
                font-size: 8pt !important;
                width: 100% !important;
            }
            .table-attendance th, .table-attendance td {
                border: 1px solid #000 !important;
                padding: 2px !important;
            }
            .sticky-col, .sticky-col-2 {
                position: static !important;
                background-color: transparent !important;
            }
            .print-header {
                display: block !important;
            }
            .tab-pane {
                display: none !important;
            }
            .tab-pane.active {
                display: block !important;
                opacity: 1 !important;
            }
            /* Adjust grand totals for print */
            tfoot tr {
                background-color: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
@endpush

@section('content')
    <nav class="page-breadcrumb d-print-none">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('messages.my_collection_history') }}</li>
        </ol>
    </nav>

    {{-- Month Filter Section --}}
    <div class="filter-section shadow-sm d-print-none">
        <form action="{{ route('my_collections.index') }}" method="GET" class="d-flex align-items-center gap-2 w-100">
            <div class="flex-grow-1">
                <label class="small text-muted mb-1 d-block">{{ __('messages.select_month') }}</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i data-lucide="calendar" style="width: 16px;"></i></span>
                    <input type="month" name="month" class="form-control border-start-0" value="{{ $month }}" onchange="this.form.submit()">
                </div>
            </div>
            <div class="align-self-end">
                <button type="button" class="btn btn-primary d-flex align-items-center gap-1" onclick="window.print()">
                    <i data-lucide="printer" style="width: 16px;"></i> {{ __('messages.print') }}
                </button>
            </div>
        </form>
    </div>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-print-none">
                    <h6 class="card-title mb-0 text-primary d-flex align-items-center">
                        <i class="link-icon me-2" data-lucide="history"></i>
                        {{ __('messages.collection_history') }}
                        <span class="ms-2 badge bg-soft-primary small" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($month . '-01')->format('F, Y') }}</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <ul class="nav nav-tabs nav-tabs-line px-3 d-print-none" id="myCollectionTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ !request()->has('loans_page') ? 'active' : '' }}" id="savings-tab" data-bs-toggle="tab" href="#savings" role="tab" aria-controls="savings" aria-selected="true">
                                <i class="link-icon me-1" data-lucide="piggy-bank" style="width: 16px; height: 16px;"></i>
                                {{ __('messages.savings_collections') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->has('loans_page') ? 'active' : '' }}" id="loans-tab" data-bs-toggle="tab" href="#loans" role="tab" aria-controls="loans" aria-selected="false">
                                <i class="link-icon me-1" data-lucide="banknote" style="width: 16px; height: 16px;"></i>
                                {{ __('messages.loan_installments') }}
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content" id="myCollectionTabContent">
                        {{-- Savings Collections Tab --}}
                        <div class="tab-pane fade show active" id="savings" role="tabpanel" aria-labelledby="savings-tab">
                            <div class="p-3">
                                {{-- Print Header --}}
                                <div class="print-header">
                                    <h2 class="mb-1">পদ্মা শ্রমজীবী সমবায় সমিতি লিমিটেড</h2>
                                    <p class="mb-2 text-muted">{{ __('messages.address') }}: ধানমন্ডি, ঢাকা-১২০৫ | {{ __('messages.phone') }}: ০১৭০০-০০০০০০</p>
                                    <div class="d-flex justify-content-between border-top pt-2 px-4">
                                        <span><strong>{{ __('messages.field_officer') }}:</strong> {{ auth()->user()->name }}</span>
                                        <span><strong>{{ __('messages.month') ?? 'Month' }}:</strong> {{ \Carbon\Carbon::parse($month . '-01')->format('F, Y') }}</span>
                                        <span><strong>{{ __('messages.type') }}:</strong> {{ __('messages.savings_collections') }}</span>
                                    </div>
                                </div>

                                <div class="attendance-container">
                                    <table class="table-attendance">
                                        <thead>
                                            <tr>
                                                <th class="sticky-col">{{ __('messages.member_name') }}</th>
                                                <th class="sticky-col-2">{{ __('messages.account_no') }}</th>
                                                @for($d = 1; $d <= $daysInMonth; $d++)
                                                    <th>{{ $d }}</th>
                                                @endfor
                                                <th class="total-col">{{ __('messages.total') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $dailyTotals = array_fill(1, $daysInMonth, 0); $grandTotal = 0; @endphp
                                            @forelse($savingsMatrix as $row)
                                                @php $grandTotal += $row['total']; @endphp
                                                <tr>
                                                    <td class="sticky-col text-truncate" style="max-width: 150px;">{{ $row['member_name'] }}</td>
                                                    <td class="sticky-col-2">{{ $row['account_no'] }}</td>
                                                    @for($d = 1; $d <= $daysInMonth; $d++)
                                                        @php 
                                                            $amt = $row['daily_amounts'][$d] ?? 0; 
                                                            $dailyTotals[$d] += $amt;
                                                        @endphp
                                                        <td class="day-cell {{ $amt > 0 ? 'has-value' : '' }}">
                                                            {{ $amt > 0 ? number_format($amt, 0) : '' }}
                                                        </td>
                                                    @endfor
                                                    <td class="total-col">{{ number_format($row['total'], 0) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ $daysInMonth + 3 }}" class="text-center py-4 text-muted">No data found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        @if($savingsMatrix->count() > 0)
                                            <tfoot>
                                                <tr class="fw-bold bg-light">
                                                    <td colspan="2" class="sticky-col text-end pe-3">{{ __('messages.daily_total') }}</td>
                                                    @for($d = 1; $d <= $daysInMonth; $d++)
                                                        <td>{{ $dailyTotals[$d] > 0 ? number_format($dailyTotals[$d], 0) : '' }}</td>
                                                    @endfor
                                                    <td class="total-col">{{ number_format($grandTotal, 0) }}</td>
                                                </tr>
                                            </tfoot>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Loan Installments Tab --}}
                        <div class="tab-pane fade" id="loans" role="tabpanel" aria-labelledby="loans-tab">
                            <div class="p-3">
                                {{-- Print Header --}}
                                <div class="print-header">
                                    <h2 class="mb-1">পদ্মা শ্রমজীবী সমবায় সমিতি লিমিটেড</h2>
                                    <p class="mb-2 text-muted">{{ __('messages.address') }}: ধানমন্ডি, ঢাকা-১২০৫ | {{ __('messages.phone') }}: ০১৭০০-০০০০০০</p>
                                    <div class="d-flex justify-content-between border-top pt-2 px-4">
                                        <span><strong>{{ __('messages.field_officer') }}:</strong> {{ auth()->user()->name }}</span>
                                        <span><strong>{{ __('messages.month') ?? 'Month' }}:</strong> {{ \Carbon\Carbon::parse($month . '-01')->format('F, Y') }}</span>
                                        <span><strong>{{ __('messages.type') }}:</strong> {{ __('messages.loan_installments') }}</span>
                                    </div>
                                </div>

                                <div class="attendance-container">
                                    <table class="table-attendance">
                                        <thead>
                                            <tr>
                                                <th class="sticky-col">{{ __('messages.member_name') }}</th>
                                                <th class="sticky-col-2">{{ __('messages.account_no') }}</th>
                                                @for($d = 1; $d <= $daysInMonth; $d++)
                                                    <th>{{ $d }}</th>
                                                @endfor
                                                <th class="total-col">{{ __('messages.total') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $dailyTotals = array_fill(1, $daysInMonth, 0); $grandTotal = 0; @endphp
                                            @forelse($loansMatrix as $row)
                                                @php $grandTotal += $row['total']; @endphp
                                                <tr>
                                                    <td class="sticky-col text-truncate" style="max-width: 150px;">{{ $row['member_name'] }}</td>
                                                    <td class="sticky-col-2">{{ $row['account_no'] }}</td>
                                                    @for($d = 1; $d <= $daysInMonth; $d++)
                                                        @php 
                                                            $amt = $row['daily_amounts'][$d] ?? 0; 
                                                            $dailyTotals[$d] += $amt;
                                                        @endphp
                                                        <td class="day-cell {{ $amt > 0 ? 'has-value' : '' }}">
                                                            {{ $amt > 0 ? number_format($amt, 0) : '' }}
                                                        </td>
                                                    @endfor
                                                    <td class="total-col">{{ number_format($row['total'], 0) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ $daysInMonth + 3 }}" class="text-center py-4 text-muted">No data found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        @if($loansMatrix->count() > 0)
                                            <tfoot>
                                                <tr class="fw-bold bg-light">
                                                    <td colspan="2" class="sticky-col text-end pe-3">{{ __('messages.daily_total') }}</td>
                                                    @for($d = 1; $d <= $daysInMonth; $d++)
                                                        <td>{{ $dailyTotals[$d] > 0 ? number_format($dailyTotals[$d], 0) : '' }}</td>
                                                    @endfor
                                                    <td class="total-col">{{ number_format($grandTotal, 0) }}</td>
                                                </tr>
                                            </tfoot>
                                        @endif
                                    </table>
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
