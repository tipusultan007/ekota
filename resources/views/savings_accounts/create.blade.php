@extends('layout.master')
@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Open New Savings Account for {{ $member->name }}</h5>
            {{-- ফর্মটি এখন multipart/form-data হবে কারণ আমরা ফাইল আপলোড করছি --}}
            <form action="{{ route('members.savings-accounts.store', $member->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3">Account Information</h6>
                        <div class="mb-3">
                            <label class="form-label">Scheme Type</label>
                            <select name="scheme_type" id="scheme_type" class="form-select form-control">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="dps">DPS</option>
                            </select>
                            @error('scheme_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Installment</label>
                            <input type="number" name="installment" class="form-control @error('installment') is-invalid @enderror" value="{{ old('installment') }}" required>
                            @error('installment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Collection Frequency <span class="text-danger">*</span></label>
                            <select name="collection_frequency" class="form-select" required>
                                <option value="daily" selected>Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Interest Rate (%)</label>
                            <input type="number" step="0.01" name="interest_rate" class="form-control @error('interest_rate') is-invalid @enderror" value="{{ old('interest_rate') }}" required>
                            @error('interest_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Opening Date</label>
                            <input type="date" name="opening_date" class="form-control @error('opening_date') is-invalid @enderror" value="{{ old('opening_date', date('Y-m-d')) }}" required>
                            @error('opening_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h6 class="mb-3">Nominee Information</h6>
                        <div class="mb-3">
                            <label class="form-label">Nominee Name</label>
                            <input type="text" name="nominee_name" class="form-control @error('nominee_name') is-invalid @enderror" value="{{ old('nominee_name') }}" required>
                            @error('nominee_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Relation with Member</label>
                            <input type="text" name="nominee_relation" class="form-control @error('nominee_relation') is-invalid @enderror" value="{{ old('nominee_relation') }}" required>
                            @error('nominee_relation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nominee NID</label>
                            <input type="text" name="nominee_nid" class="form-control @error('nominee_nid') is-invalid @enderror" value="{{ old('nominee_nid') }}">
                            @error('nominee_nid')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nominee Phone</label>
                            <input type="text" name="nominee_phone" class="form-control @error('nominee_phone') is-invalid @enderror" value="{{ old('nominee_phone') }}">
                            @error('nominee_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nominee Photo</label>
                            <input type="file" name="nominee_photo" class="form-control @error('nominee_photo') is-invalid @enderror">
                            @error('nominee_photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-3">Create Account</button>
            </form>
        </div>
    </div>
@endsection
