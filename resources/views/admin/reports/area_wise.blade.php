@extends('layout.master')

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
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%);
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

        .input-premium {
            border-radius: 12px;
            padding: 0.6rem 1rem;
            border: 1px solid #e2e8f0;
            background: #fff;
            transition: all 0.3s;
        }

        .input-premium:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        /* Select2 Premium Styling */
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

        .select2-container--bootstrap-5.select2-container--focus .select2-selection {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1) !important;
        }

        /* Select2 Dropdown and Hover Styling */
        .select2-dropdown {
            border-radius: 12px !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
            overflow: hidden !important;
            z-index: 9999 !important;
        }

        .select2-search--dropdown .select2-search__field {
            padding: 0.6rem 1rem !important;
            border-radius: 8px !important;
            border: 1px solid #e2e8f0 !important;
            margin: 0.5rem !important;
            width: calc(100% - 1rem) !important;
        }

        .select2-results__option {
            padding: 0.8rem 1rem !important;
            font-size: 0.875rem !important;
        }

        .select2-results__option--highlighted.select2-results__option--selectable {
            background-color: #3b82f6 !important;
            color: white !important;
        }

        .summary-widget {
            border-radius: 20px;
            padding: 1.5rem;
            border: 1px solid #f1f5f9;
            transition: transform 0.2s;
            height: 100%;
            background: #fff;
        }

        .summary-widget:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        }

        .widget-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            margin-bottom: 1rem;
        }

        .bg-soft-blue { background: rgba(59, 130, 246, 0.1); color: #2563eb; }
        .bg-soft-emerald { background: rgba(16, 185, 129, 0.1); color: #059669; }
        .bg-soft-amber { background: rgba(245, 158, 11, 0.1); color: #d97706; }
        .bg-soft-rose { background: rgba(244, 63, 94, 0.1); color: #e11d48; }
        .bg-soft-indigo { background: rgba(79, 70, 229, 0.1); color: #4f46e5; }

        .report-section-title {
            font-size: 0.875rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f1f5f9;
            display: flex;
            align-items: center;
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
                            <h3 class="fw-bold mb-1 text-white">{{ __('messages.area_wise_report') }}</h3>
                            <p class="mb-0 opacity-75 text-white">{{ __('messages.analyze_performance_by_location') }}</p>
                        </div>
                        <div class="d-flex gap-2 text-white">
                            @if($selectedArea)
                                <div class="text-end">
                                    <span class="badge bg-white text-primary px-3 py-2 rounded-pill fw-bold shadow-sm">
                                        <i data-lucide="map-pin" class="icon-xs me-1"></i> {{ $selectedArea->name }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-body p-4 p-lg-5">
                    <!-- Modern Filter Panel -->
                    <div class="filter-panel shadow-sm border-0">
                        <form action="{{ route('admin.reports.area_wise') }}" method="GET">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label-premium">{{ __('messages.select_area') }} <span class="text-danger">*</span></label>
                                    <select name="area_id" class="form-select input-premium select2" required>
                                        <option value="">-- {{ __('messages.select') }} --</option>
                                        @foreach($areas as $area)
                                            <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>
                                                {{ $area->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label-premium">{{ __('messages.start_date') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 border-premium"><i data-lucide="calendar" class="icon-xs"></i></span>
                                        <input type="text" name="start_date" class="form-control input-premium border-start-0 flatpickr" value="{{ request('start_date') }}" placeholder="YYYY-MM-DD">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label-premium">{{ __('messages.end_date') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 border-premium"><i data-lucide="calendar" class="icon-xs"></i></span>
                                        <input type="text" name="end_date" class="form-control input-premium border-start-0 flatpickr" value="{{ request('end_date') }}" placeholder="YYYY-MM-DD">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 shadow-sm d-flex align-items-center justify-content-center fw-bold">
                                        <i data-lucide="bar-chart-2" class="icon-sm me-2"></i> {{ __('messages.generate_report') }}
                                    </button>
                                </div>
                            </div>
                            <div class="mt-3">
                                <p class="text-muted small mb-0 d-flex align-items-center">
                                    <i data-lucide="info" class="icon-xs me-2"></i>
                                    {{ __('messages.report_filter_hint') }}
                                </p>
                            </div>
                        </form>
                    </div>

                    @if($selectedArea)
                        <div class="report-content">
                            <!-- Period Indicator -->
                            <div class="mb-4 text-center">
                                @if(request('start_date') && request('end_date'))
                                    <h5 class="text-muted fw-normal">
                                        {{ __('messages.summary_for_period', [
                                            'start' => \Carbon\Carbon::parse(request('start_date'))->format('d M, Y'), 
                                            'end' => \Carbon\Carbon::parse(request('end_date'))->format('d M, Y')
                                        ]) }}
                                    </h5>
                                @else
                                    <h5 class="text-muted fw-normal">{{ __('messages.all_time_summary') }}</h5>
                                @endif
                            </div>

                            <!-- Savings Section -->
                            <h6 class="report-section-title">
                                <i data-lucide="piggy-bank" class="icon-sm me-2"></i> {{ __('messages.savings_summary') }}
                            </h6>
                            <div class="row g-4 mb-5">
                                <div class="col-md-4">
                                    <div class="summary-widget shadow-sm border-0">
                                        <div class="widget-icon bg-soft-emerald">
                                            <i data-lucide="arrow-down-left" class="icon-lg"></i>
                                        </div>
                                        <h3 class="fw-bold mb-1">{{ number_format($summary['total_savings_collected'], 2) }}</h3>
                                        <p class="text-muted mb-0 small text-uppercase fw-bold">{{ __('messages.total_savings_collected') }}</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="summary-widget shadow-sm border-0">
                                        <div class="widget-icon bg-soft-rose">
                                            <i data-lucide="arrow-up-right" class="icon-lg"></i>
                                        </div>
                                        <h3 class="fw-bold mb-1">{{ number_format($summary['total_withdrawn'], 2) }}</h3>
                                        <p class="text-muted mb-0 small text-uppercase fw-bold">{{ __('messages.total_amount_withdrawn') }}</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="summary-widget shadow-sm border-0 bg-soft-blue border-0">
                                        <div class="widget-icon bg-white shadow-sm text-primary">
                                            <i data-lucide="wallet" class="icon-lg"></i>
                                        </div>
                                        <h3 class="fw-bold mb-1 text-primary">{{ number_format($summary['total_savings_collected'] - $summary['total_withdrawn'], 2) }}</h3>
                                        <p class="text-primary mb-0 small text-uppercase fw-bold">{{ __('messages.net_savings') }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Loan Section -->
                            <h6 class="report-section-title">
                                <i data-lucide="trending-up" class="icon-sm me-2"></i> {{ __('messages.loan_summary') }}
                            </h6>
                            <div class="row g-4">
                                <div class="col-md-3">
                                    <div class="summary-widget shadow-sm border-0">
                                        <div class="widget-icon bg-soft-indigo">
                                            <i data-lucide="landmark" class="icon-lg"></i>
                                        </div>
                                        <h3 class="fw-bold mb-1">{{ number_format($summary['total_loan_disbursed'], 2) }}</h3>
                                        <p class="text-muted mb-0 small text-uppercase fw-bold">{{ __('messages.total_loan_disbursed') }}</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="summary-widget shadow-sm border-0">
                                        <div class="widget-icon bg-soft-amber">
                                            <i data-lucide="receipt" class="icon-lg"></i>
                                        </div>
                                        <h3 class="fw-bold mb-1">{{ number_format($summary['total_payable'], 2) }}</h3>
                                        <p class="text-muted mb-0 small text-uppercase fw-bold">{{ __('messages.total_payable') }}</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="summary-widget shadow-sm border-0">
                                        <div class="widget-icon bg-soft-emerald">
                                            <i data-lucide="check-check" class="icon-lg"></i>
                                        </div>
                                        <h3 class="fw-bold mb-1 text-success">{{ number_format($summary['total_installments_paid'], 2) }}</h3>
                                        <p class="text-muted mb-0 small text-uppercase fw-bold">{{ __('messages.total_installments_paid') }}</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="summary-widget shadow-sm border-0">
                                        <div class="widget-icon bg-soft-rose">
                                            <i data-lucide="alert-triangle" class="icon-lg"></i>
                                        </div>
                                        <h3 class="fw-bold mb-1 text-danger">{{ number_format($summary['loan_on_field'], 2) }}</h3>
                                        <p class="text-muted mb-0 small text-uppercase fw-bold">{{ __('messages.loan_on_field') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5 opacity-50">
                            <i data-lucide="pie-chart" class="icon-lg d-block mx-auto mb-3" style="width: 64px; height: 64px;"></i>
                            <h5 class="fw-bold">{{ __('messages.no_area_selected') }}</h5>
                            <p class="mb-0">{{ __('messages.use_filters_above') }}</p>
                        </div>
                    @endif
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
                    width: '100%',
                    placeholder: '-- {{ __("messages.select") }} --',
                    allowClear: true
                });
            }

            // Flatpickr
            if (typeof flatpickr !== 'undefined') {
                $(".flatpickr").flatpickr({
                    altInput: true,
                    altFormat: "d M, Y",
                    dateFormat: "Y-m-d",
                    allowInput: true
                });
            }
        });
    </script>
@endpush