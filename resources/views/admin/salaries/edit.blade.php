@extends('layout.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Edit Salary Payment</h5>

            {{-- সাধারণ এরর মেসেজ দেখানোর জন্য --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="alert alert-secondary">
                <p class="mb-1"><strong>Employee:</strong> {{ $salary->user->name }}</p>
                <p class="mb-0"><strong>Salary for Month:</strong> {{ $salary->salary_month }}</p>
            </div>
            <hr>

            <form action="{{ route('admin.salaries.update', $salary->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" id="amount"
                               class="form-control @error('amount') is-invalid @enderror"
                               value="{{ old('amount', $salary->amount) }}" required>
                        @error('amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" id="payment_date"
                               class="form-control @error('payment_date') is-invalid @enderror"
                               value="{{ old('payment_date', $salary->payment_date->format('Y-m-d')) }}" required>
                        @error('payment_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ======== পেমেন্ট অ্যাকাউন্ট ফিল্ড যোগ করা হলো ======== --}}
                    <div class="col-md-12 mb-3">
                        <label for="account_id" class="form-label">Payment From Account <span class="text-danger">*</span></label>
                        @php
                            // Get the current payment account from the transaction
                            $currentAccountId = $salary->expense ? ($salary->expense->transactions()->where('type', 'debit')->first()->account_id ?? '') : '';
                        @endphp
                        <select name="account_id" id="account_id" class="form-select @error('account_id') is-invalid @enderror" required>
                            <option value="">Select Account...</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ old('account_id', $currentAccountId) == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('account_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- =============================================== --}}

                    <div class="col-md-12 mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea name="notes" id="notes" class="form-control" rows="2">{{ old('notes', $salary->notes) }}</textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Update Payment</button>
                <a href="{{ route('admin.salaries.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection

@push('plugin-scripts')
    {{-- Select2 বা অন্য কোনো প্লাগইন লাগলে এখানে যোগ করুন --}}
@endpush
