@extends('layout.master')
@section('content')
    <div class="card">
        <div class="card-header bg-primary">
            <h4 class="card-title mb-0 text-white">{{ __('messages.new_savings_account') }}</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('savings.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label for="member_id" class="form-label">{{ __('messages.select_member') }} <span class="text-danger">*</span></label>
                            <select name="member_id" id="member_id" class="form-select" required>
                                <option value="">{{ __('messages.search_select_member') }}</option>
                                @foreach ($members as $member)
                                    <option value="{{ $member->id }}" {{ old('member_id') == $member->id ? 'selected' : '' }}>
                                        {{ $member->name }} (ID: {{ $member->id }})
                                    </option>
                                @endforeach
                            </select>
                            @error('member_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <h6 class="mb-3">{{ __('messages.account_information') }}</h6>
                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.scheme_type') }}</label>
                            <select name="scheme_type" id="scheme_type" class="form-select form-control">
                                <option value="daily">{{ __('messages.daily') }}</option>
                                <option value="weekly">{{ __('messages.weekly') }}</option>
                                <option value="monthly">{{ __('messages.monthly') }}</option>
                                <option value="dps">{{ __('messages.dps') }}</option>
                            </select>
                            @error('scheme_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.installment') }}</label>
                            <input type="number" name="installment" class="form-control @error('installment') is-invalid @enderror" value="{{ old('installment') }}" required>
                            @error('installment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.collection_frequency') }} <span class="text-danger">*</span></label>
                            <select name="collection_frequency" class="form-select" required>
                                <option value="daily" selected>{{ __('messages.daily') }}</option>
                                <option value="weekly">{{ __('messages.weekly') }}</option>
                                <option value="monthly">{{ __('messages.monthly') }}</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.interest_rate') }} (%)</label>
                            <input type="number" step="0.01" name="interest_rate" class="form-control @error('interest_rate') is-invalid @enderror" value="{{ old('interest_rate') }}" required>
                            @error('interest_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.opening_date') }}</label>
                            <input type="text" name="opening_date" class="form-control flatpickr @error('opening_date') is-invalid @enderror" value="{{ old('opening_date', date('Y-m-d')) }}" required>
                            @error('opening_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h6 class="mb-3">{{ __('messages.nominee_information') }}</h6>
                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.nominee_name') }}</label>
                            <input type="text" name="nominee_name" class="form-control @error('nominee_name') is-invalid @enderror" value="{{ old('nominee_name') }}" required>
                            @error('nominee_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.nominee_relation') }}</label>
                            <input type="text" name="nominee_relation" class="form-control @error('nominee_relation') is-invalid @enderror" value="{{ old('nominee_relation') }}" required>
                            @error('nominee_relation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.nominee_nid') }}</label>
                            <input type="text" name="nominee_nid" class="form-control @error('nominee_nid') is-invalid @enderror" value="{{ old('nominee_nid') }}">
                            @error('nominee_nid')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.nominee_phone') }}</label>
                            <input type="text" name="nominee_phone" class="form-control @error('nominee_phone') is-invalid @enderror" value="{{ old('nominee_phone') }}">
                            @error('nominee_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.nominee_photo') }}</label>
                            <input type="file" name="nominee_photo" class="form-control @error('nominee_photo') is-invalid @enderror">
                            @error('nominee_photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-3">{{ __('messages.create_account') }}</button>
            </form>
        </div>
    </div>

@endsection

@push('custom-scripts')
    <script>
        $(".form-select").select2({
            width: "100%",
        })

        $(".flatpickr").flatpickr({
            altInput: true,
            dateFormat: 'Y-m-d',
            altFormat: 'd/m/Y'
        })
    </script>
@endpush
