@extends('layout.master')
@push('plugin-styles')
    <link rel="stylesheet" href="{{ asset('build/plugins/select2/select2.min.css') }}">
@endpush
@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ __('messages.pay_employee_salary') }}</h5>
            <form action="{{ route('admin.salaries.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('messages.select_employee') }} <span class="text-danger">*</span></label>
                        <select name="user_id" id="employee_id" class="form-select" required>
                            <option value="">{{ __('messages.select_option') }}</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" data-salary="{{ $employee->salary }}">
                                    {{ $employee->name }} ({{ __('messages.salary') }}: {{ number_format($employee->salary) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('messages.salary_for_month') }} <span class="text-danger">*</span></label>
                        <input type="month" name="salary_month_input" class="form-control" value="{{ date('Y-m') }}" required>
                        {{-- Hidden field to store formatted month name --}}
                        <input type="hidden" name="salary_month" id="salary_month_formatted">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('messages.amount') }} <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" id="salary_amount" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Salary From Account <span class="text-danger">*</span></label>
                        <select name="account_id" class="form-select" required>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}" {{ old('account_id') == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('messages.payment_date') }} <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">{{ __('messages.notes') }}</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">{{ __('messages.pay_salary') }}</button>
            </form>
        </div>
    </div>
@endsection


@push('plugin-scripts')
    <script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('build/plugins/select2/select2.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#employee_id').select2({ placeholder: "Select an Employee" });

            $('#employee_id').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const salary = selectedOption.data('salary');
                $('#salary_amount').val(salary);
            });

            // Format month name before form submission
            $('form').on('submit', function() {
                const monthInput = $('input[name="salary_month_input"]').val();
                if(monthInput) {
                    const date = new Date(monthInput + '-02'); // Add a day to avoid timezone issues
                    const monthName = date.toLocaleString('default', { month: 'long' });
                    const year = date.getFullYear();
                    $('#salary_month_formatted').val(monthName + ', ' + year);
                }
            });
        });
    </script>
@endpush
