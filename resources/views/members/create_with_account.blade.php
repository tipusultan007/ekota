@extends('layout.master')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('members.index') }}">Members</a></li>
            <li class="breadcrumb-item active" aria-current="page">Add Member & Open Account</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">New Member Registration with Initial Savings Account</h6>
                    <form action="{{ route('members.store_with_account') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- Member Details Section --}}
                        <div class="border p-3 mb-4 rounded">
                            <h5 class="mb-3">1. Member's Information</h5>
                            {{-- সদস্য তৈরির ফর্মের কমন অংশটি এখানে অন্তর্ভুক্ত করুন --}}
                            @include('members._form', ['member' => null])
                        </div>

                        {{-- Savings Account Section --}}
                        <div class="border p-3 rounded">
                            <h5 class="mb-3">2. Savings Account (Optional)</h5>

                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" name="open_savings_account" id="open_savings_account" value="1" checked>
                                <label class="form-check-label" for="open_savings_account">
                                    Create a new savings account for this member
                                </label>
                            </div>

                            <div id="savings_account_fields">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Scheme Type <span class="text-danger">*</span></label>
                                        <input type="text" name="scheme_type" class="form-control" value="{{ old('scheme_type', 'General Savings') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Interest Rate (%) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" name="interest_rate" class="form-control" value="{{ old('interest_rate', '5.5') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Opening Date <span class="text-danger">*</span></label>
                                        <input type="date" name="opening_date" class="form-control" value="{{ old('opening_date', date('Y-m-d')) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Initial Deposit</label>
                                        <input type="number" step="0.01" name="initial_deposit" class="form-control" value="{{ old('initial_deposit', '0') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Nominee Name <span class="text-danger">*</span></label>
                                        <input type="text" name="nominee_name" class="form-control" value="{{ old('nominee_name') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Nominee Relation <span class="text-danger">*</span></label>
                                        <input type="text" name="nominee_relation" class="form-control" value="{{ old('nominee_relation') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Save Member & Account</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkbox = document.getElementById('open_savings_account');
            const fieldsDiv = document.getElementById('savings_account_fields');

            checkbox.addEventListener('change', function() {
                fieldsDiv.style.display = this.checked ? 'block' : 'none';
            });

            // initial check on page load
            fieldsDiv.style.display = checkbox.checked ? 'block' : 'none';
        });
    </script>
@endpush
