@extends('layout.master')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('members.show', $savingsAccount->member_id) }}">Member Details</a></li>
            <li class="breadcrumb-item"><a href="{{ route('savings_accounts.show', $savingsAccount->id) }}">Account Details</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Savings Account</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Edit Savings Account</h6>
                    <p class="text-muted">
                        Editing account <strong class="text-primary">{{ $savingsAccount->account_no }}</strong> for member
                        <a href="{{ route('members.show', $savingsAccount->member_id) }}">{{ $savingsAccount->member->name }}</a>.
                    </p>
                    @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
                    <hr>

                    <form action="{{ route('savings-accounts.update', $savingsAccount->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            {{-- Account Information Section --}}
                            <div class="col-md-6 border-end">
                                <h5 class="mb-3">Account Information</h5>
                                <div class="mb-3">
                                    <label for="scheme_type" class="form-label">Scheme Type <span class="text-danger">*</span></label>
                                    <select name="scheme_type" id="scheme_type" class="form-select form-control" required>
                                        <option value="daily" {{ $savingsAccount->scheme_type === 'daily' ? 'selected':'' }}>Daily</option>
                                        <option value="weekly" {{ $savingsAccount->scheme_type === 'weekly' ? 'selected':'' }}>Weekly</option>
                                        <option value="monthly" {{ $savingsAccount->scheme_type === 'monthly' ? 'selected':'' }}>Monthly</option>
                                        <option value="dps" {{ $savingsAccount->scheme_type === 'dps' ? 'selected':'' }}>DPS</option>
                                    </select>
                                    @error('scheme_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Installment</label>
                                    <input type="number" name="installment" class="form-control @error('installment') is-invalid @enderror" value="{{ $savingsAccount->installment }}" required>
                                    @error('installment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Collection Frequency <span class="text-danger">*</span></label>
                                    <select name="collection_frequency" class="form-select" required>
                                        <option value="daily" {{ $savingsAccount->collection_frequency == 'daily' ? 'selected' : '' }}>Daily</option>
                                        <option value="weekly" {{ $savingsAccount->collection_frequency == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                        <option value="monthly" {{ $savingsAccount->collection_frequency == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="interest_rate" class="form-label">Interest Rate (%) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="interest_rate" id="interest_rate" class="form-control @error('interest_rate') is-invalid @enderror"
                                           value="{{ old('interest_rate', $savingsAccount->interest_rate) }}" required>
                                    @error('interest_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="opening_date" class="form-label">Opening Date <span class="text-danger">*</span></label>
                                    <input type="date" name="opening_date" id="opening_date" class="form-control @error('opening_date') is-invalid @enderror"
                                           value="{{ old('opening_date', $savingsAccount->opening_date->format('Y-m-d')) }}" required>
                                    @error('opening_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="status" class="form-label">Account Status <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="active" {{ old('status', $savingsAccount->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="closed" {{ old('status', $savingsAccount->status) == 'closed' ? 'selected' : '' }}>Closed</option>
                                        <option value="matured" {{ old('status', $savingsAccount->status) == 'matured' ? 'selected' : '' }}>Matured</option>
                                    </select>
                                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            {{-- Nominee Information Section --}}
                            <div class="col-md-6">
                                <h5 class="mb-3">Nominee Information</h5>
                                <div class="mb-3">
                                    <label for="nominee_name" class="form-label">Nominee Name <span class="text-danger">*</span></label>
                                    <input type="text" name="nominee_name" id="nominee_name" class="form-control @error('nominee_name') is-invalid @enderror"
                                           value="{{ old('nominee_name', $savingsAccount->nominee_name) }}" required>
                                    @error('nominee_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="nominee_relation" class="form-label">Relation <span class="text-danger">*</span></label>
                                    <input type="text" name="nominee_relation" id="nominee_relation" class="form-control @error('nominee_relation') is-invalid @enderror"
                                           value="{{ old('nominee_relation', $savingsAccount->nominee_relation) }}" required>
                                    @error('nominee_relation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="nominee_phone" class="form-label">Nominee Phone</label>
                                    <input type="text" name="nominee_phone" id="nominee_phone" class="form-control"
                                           value="{{ old('nominee_phone', $savingsAccount->nominee_phone) }}">
                                </div>
                                <div class="mb-3">
                                    <label for="nominee_photo" class="form-label">Change Nominee Photo</label>
                                    <input type="file" name="nominee_photo" class="form-control @error('nominee_photo') is-invalid @enderror">
                                    @if($savingsAccount->getFirstMediaUrl('nominee_photo'))
                                        <small class="form-text text-muted">Current photo:
                                            <a href="{{ $savingsAccount->getFirstMediaUrl('nominee_photo') }}" target="_blank">
                                                <img src="{{ $savingsAccount->getFirstMediaUrl('nominee_photo') }}" width="40" class="img-thumbnail mt-1">
                                            </a>
                                        </small>
                                    @endif
                                    @error('nominee_photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Update Account</button>
                            <a href="{{ route('savings_accounts.show', $savingsAccount->id) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
