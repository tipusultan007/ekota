@extends('layout.master')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('members.show', $savingsAccount->member_id) }}">@lang('messages.member_details')</a></li>
            <li class="breadcrumb-item"><a href="{{ route('savings_accounts.show', $savingsAccount->id) }}">@lang('messages.account_details')</a></li>
            <li class="breadcrumb-item active" aria-current="page">@lang('messages.edit_savings_account')</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">@lang('messages.edit_savings_account')</h6>
                    <p class="text-muted">
                        @lang('messages.editing_account', ['account' => $savingsAccount->account_no])
                        @lang('messages.for_member')
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
                                <h5 class="mb-3">@lang('messages.account_information')</h5>
                                <div class="mb-3">
                                    <label for="scheme_type" class="form-label">@lang('messages.scheme_type') <span class="text-danger">*</span></label>
                                    <select name="scheme_type" id="scheme_type" class="form-select form-control" required>
                                        <option value="daily" {{ $savingsAccount->scheme_type === 'daily' ? 'selected':'' }}>@lang('messages.daily')</option>
                                        <option value="weekly" {{ $savingsAccount->scheme_type === 'weekly' ? 'selected':'' }}>@lang('messages.weekly')</option>
                                        <option value="monthly" {{ $savingsAccount->scheme_type === 'monthly' ? 'selected':'' }}>@lang('messages.monthly')</option>
                                        <option value="dps" {{ $savingsAccount->scheme_type === 'dps' ? 'selected':'' }}>@lang('messages.dps')</option>
                                    </select>
                                    @error('scheme_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">@lang('messages.installment')</label>
                                    <input type="number" name="installment" class="form-control @error('installment') is-invalid @enderror" value="{{ $savingsAccount->installment }}" required>
                                    @error('installment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">@lang('messages.collection_frequency') <span class="text-danger">*</span></label>
                                    <select name="collection_frequency" class="form-select" required>
                                        <option value="daily" {{ $savingsAccount->collection_frequency == 'daily' ? 'selected' : '' }}>@lang('messages.daily')</option>
                                        <option value="weekly" {{ $savingsAccount->collection_frequency == 'weekly' ? 'selected' : '' }}>@lang('messages.weekly')</option>
                                        <option value="monthly" {{ $savingsAccount->collection_frequency == 'monthly' ? 'selected' : '' }}>@lang('messages.monthly')</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="interest_rate" class="form-label">@lang('messages.interest_rate') (%) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="interest_rate" id="interest_rate" class="form-control @error('interest_rate') is-invalid @enderror"
                                           value="{{ old('interest_rate', $savingsAccount->interest_rate) }}" required>
                                    @error('interest_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="opening_date" class="form-label">@lang('messages.opening_date') <span class="text-danger">*</span></label>
                                    <input type="date" name="opening_date" id="opening_date" class="form-control @error('opening_date') is-invalid @enderror"
                                           value="{{ old('opening_date', $savingsAccount->opening_date->format('Y-m-d')) }}" required>
                                    @error('opening_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="status" class="form-label">@lang('messages.account_status') <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="active" {{ old('status', $savingsAccount->status) == 'active' ? 'selected' : '' }}>@lang('messages.active')</option>
                                        <option value="closed" {{ old('status', $savingsAccount->status) == 'closed' ? 'selected' : '' }}>@lang('messages.closed')</option>
                                        <option value="matured" {{ old('status', $savingsAccount->status) == 'matured' ? 'selected' : '' }}>@lang('messages.matured')</option>
                                    </select>
                                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            {{-- Nominee Information Section --}}
                            <div class="col-md-6">
                                <h5 class="mb-3">@lang('messages.nominee_information')</h5>
                                <div class="mb-3">
                                    <label for="nominee_name" class="form-label">@lang('messages.nominee_name') <span class="text-danger">*</span></label>
                                    <input type="text" name="nominee_name" id="nominee_name" class="form-control @error('nominee_name') is-invalid @enderror"
                                           value="{{ old('nominee_name', $savingsAccount->nominee_name) }}" required>
                                    @error('nominee_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="nominee_relation" class="form-label">@lang('messages.relation') <span class="text-danger">*</span></label>
                                    <input type="text" name="nominee_relation" id="nominee_relation" class="form-control @error('nominee_relation') is-invalid @enderror"
                                           value="{{ old('nominee_relation', $savingsAccount->nominee_relation) }}" required>
                                    @error('nominee_relation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="nominee_phone" class="form-label">@lang('messages.nominee_phone')</label>
                                    <input type="text" name="nominee_phone" id="nominee_phone" class="form-control"
                                           value="{{ old('nominee_phone', $savingsAccount->nominee_phone) }}">
                                </div>
                                <div class="mb-3">
                                    <label for="nominee_photo" class="form-label">@lang('messages.change_nominee_photo')</label>
                                    <input type="file" name="nominee_photo" class="form-control @error('nominee_photo') is-invalid @enderror">
                                    @if($savingsAccount->getFirstMediaUrl('nominee_photo'))
                                        <small class="form-text text-muted">
                                            @lang('messages.current_photo'):
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
                            <button type="submit" class="btn btn-primary">@lang('messages.update_account')</button>
                            <a href="{{ route('savings_accounts.show', $savingsAccount->id) }}" class="btn btn-secondary">@lang('messages.cancel')</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection