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

        .form-panel {
            background: #fdf2f8; /* Light pink tint */
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2.5rem;
            border: 1px solid #fce7f3;
        }

        .form-label-premium {
            font-size: 0.75rem;
            color: #9f1239; /* Deep rose */
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 0.5rem;
            letter-spacing: 0.025em;
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

        .input-group > .input-premium {
            flex: 1 1 auto !important;
            width: 1% !important;
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
        }

        textarea.input-premium {
            height: auto !important;
            min-height: 100px !important;
        }

        .input-group-text-premium {
            background: #fff !important;
            border: 1px solid #f9a8d4 !important;
            border-right: none !important;
            border-radius: 12px 0 0 12px !important;
            color: #9f1239 !important;
            padding: 0.6rem 1rem !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-width: 50px !important;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            padding: 0 !important;
            line-height: normal !important;
            color: #334155 !important;
            display: block !important;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
            top: 50% !important;
            transform: translateY(-50%) !important;
            right: 0.8rem !important;
        }

        .input-premium:focus,
        .select2-container--bootstrap-5.select2-container--focus .select2-selection {
            border-color: #e11d48 !important;
            box-shadow: 0 0 0 4px rgba(225, 29, 72, 0.1) !important;
            z-index: 5 !important;
        }

        .section-title {
            color: #881337;
            font-weight: 800;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #fce7f3;
            padding-bottom: 0.5rem;
        }

        .section-title i {
            margin-right: 0.5rem;
            width: 18px;
            height: 18px;
        }

        .btn-pay {
            background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
            border: none;
            padding: 1rem 3rem;
            border-radius: 14px;
            font-weight: 700;
            color: white !important;
            transition: all 0.3s;
            box-shadow: 0 10px 20px rgba(225, 29, 72, 0.2);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px rgba(225, 29, 72, 0.3);
            filter: brightness(1.1);
        }
    </style>
@endpush

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="premium-card">
                <div class="premium-header">
                    <img src="data:image/svg+xml,%3Csvg width='200' height='200' viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 0h200v200H0z' fill='none'/%3E%3Cpath d='M200 0c0 110.457-89.543 200-200 200h200V0z' fill='white' fill-opacity='.05'/%3E%3C/svg%3E" class="header-shape">
                    <div class="d-flex justify-content-between align-items-center position-relative z-1">
                        <div>
                            <h3 class="fw-bold mb-1 text-white">{{ __('messages.pay_employee_salary') ?? 'Pay Employee Salary' }}</h3>
                            <p class="mb-0 opacity-75 text-white">{{ __('messages.process_salary_payments_efficiently') ?? 'Process and record employee salary payments securely' }}</p>
                        </div>
                        <div class="bg-white bg-opacity-25 p-3 rounded-4 backdrop-blur shadow-sm">
                            <i data-lucide="banknote" class="text-white" style="width: 32px; height: 32px;"></i>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4 p-lg-5">
                    
                    @if (session('success')) 
                        <div class="alert alert-success d-flex align-items-center rounded-4 border-0 shadow-sm mb-4">
                            <i data-lucide="check-circle" class="icon-sm me-2"></i> {{ session('success') }}
                        </div> 
                    @endif
                    @if (session('error')) 
                        <div class="alert alert-danger d-flex align-items-center rounded-4 border-0 shadow-sm mb-4">
                            <i data-lucide="alert-circle" class="icon-sm me-2"></i> {{ session('error') }}
                        </div> 
                    @endif

                    <form action="{{ route('admin.salaries.store') }}" method="POST" id="salaryForm">
                        @csrf
                        
                        {{-- Section 1: Employee & Month --}}
                        <div class="form-panel shadow-sm border-0">
                            <h6 class="section-title">
                                <i data-lucide="user-check" class="icon-sm"></i>
                                {{ __('messages.employee_selection') ?? 'Employee & Period' }}
                            </h6>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label-premium">{{ __('messages.select_employee') ?? 'Select Employee' }} <span class="text-danger">*</span></label>
                                    <select name="user_id" id="employee_id" class="form-select select2" required>
                                        <option value="">{{ __('messages.select_option') ?? '-- Select --' }}</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}" data-salary="{{ $employee->salary }}">
                                                {{ $employee->name }} ({{ __('messages.salary') ?? 'Salary' }}: {{ number_format($employee->salary, 2) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-premium">{{ __('messages.salary_for_month') ?? 'Salary for Month' }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text-premium"><i data-lucide="calendar-range" class="icon-xs"></i></span>
                                        <input type="text" name="salary_month_input" id="salary_month_picker" class="form-control input-premium" value="{{ date('Y-m') }}" required>
                                    </div>
                                    <input type="hidden" name="salary_month" id="salary_month_formatted">
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Payment Details --}}
                        <div class="form-panel shadow-sm border-0">
                            <h6 class="section-title">
                                <i data-lucide="credit-card" class="icon-sm"></i>
                                {{ __('messages.payment_details') ?? 'Payment Details' }}
                            </h6>
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="form-label-premium">{{ __('messages.amount') ?? 'Amount' }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text-premium"><i data-lucide="dollar-sign" class="icon-xs"></i></span>
                                        <input type="number" step="0.01" name="amount" id="salary_amount" class="form-control input-premium" placeholder="0.00" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label-premium">{{ __('messages.salary_from_account') ?? 'Salary From Account' }} <span class="text-danger">*</span></label>
                                    <select name="account_id" class="form-select select2" required>
                                        @foreach ($accounts as $account)
                                            <option value="{{ $account->id }}" {{ old('account_id') == $account->id ? 'selected' : '' }}>
                                                {{ $account->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label-premium">{{ __('messages.payment_date') ?? 'Payment Date' }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text-premium"><i data-lucide="calendar" class="icon-xs"></i></span>
                                        <input type="text" name="payment_date" class="form-control input-premium flatpickr" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section 3: Notes --}}
                        <div class="form-panel shadow-sm border-0">
                            <h6 class="section-title">
                                <i data-lucide="message-square" class="icon-sm"></i>
                                {{ __('messages.additional_notes') ?? 'Additional Notes' }}
                            </h6>
                            <div class="row g-4">
                                <div class="col-12">
                                    <div class="input-group align-items-start">
                                        <span class="input-group-text-premium pt-3"><i data-lucide="file-text" class="icon-xs"></i></span>
                                        <textarea name="notes" class="form-control input-premium pt-3 h-auto" rows="3" placeholder="{{ __('messages.add_any_additional_remarks') ?? 'Add any additional remarks here...' }}"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-pay">
                                <i data-lucide="send" class="icon-sm"></i> {{ __('messages.pay_salary') ?? 'Pay Salary' }}
                            </button>
                        </div>
                    </form>
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
            // Lucide Icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Select2 initialization
            $('.select2').select2({
                width: '100%',
                placeholder: "-- Select --",
                allowClear: true
            });

            // Flatpickr for Date
            $(".flatpickr").flatpickr({
                altInput: true,
                dateFormat: 'Y-m-d',
                altFormat: 'd/m/Y'
            });

            // Flatpickr for Month
            $("#salary_month_picker").flatpickr({
                altInput: true,
                dateFormat: 'Y-m',
                altFormat: 'F Y',
                // For a true month picker without a plugin, we can just use the config
                // to restrict selection or use the MonthSelect plugin if it's available.
                // Since I'm not sure if the plugin is installed, I'll use standard flatpickr 
                // but try to make it feel better.
            });

            // Employee Salary Auto-fill
            $('#employee_id').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const salary = selectedOption.data('salary');
                $('#salary_amount').val(salary);
            });

            // Format month name before form submission
            $('#salaryForm').on('submit', function() {
                const monthInput = $('#salary_month_picker').val();
                if(monthInput) {
                    const parts = monthInput.split('-');
                    const year = parts[0];
                    const month = parseInt(parts[1]) - 1;
                    const date = new Date(year, month, 15);
                    const monthName = date.toLocaleString('default', { month: 'long' });
                    $('#salary_month_formatted').val(monthName + ', ' + year);
                }
            });
        });
    </script>
@endpush
