@extends('layout.master')
@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ __('messages.open_new_savings_account_for', ['name' => $member->name]) }}</h5>
            {{-- Form will be multipart/form-data because we are uploading files --}}
            <form action="{{ route('members.savings-accounts.store', $member->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3">{{ __('messages.account_information') }}</h6>
                        <div class="row">
                            <div class="col-8">
                                <div class="mb-3">
                                    <label for="account_no" class="form-label">{{ __('messages.account_no') }}</label>
                                    {{-- name attribute will now be dynamic --}}
                                   <div class="input-group mb-3">
                                        {{-- Prefix --}}
                                        <span class="input-group-text" id="account_no_prefix">SAV-</span>
                                        {{-- Number field for user input --}}
                                        <input type="number" name="account_no_suffix" id="account_no_suffix" 
                                            class="form-control @error('account_no') is-invalid @enderror" 
                                            value="{{ old('account_no_suffix') }}" 
                                            placeholder="{{ __('messages.enter_number_part') }}">
                                    </div>
                                    @error('account_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-4 d-flex align-items-center">
                                <div class="form-check mt-2">
                                    {{-- This checkbox doesn't need any name attribute --}}
                                    <input type="checkbox" class="form-check-input" id="auto_generate_checkbox" checked>
                                    <label class="form-check-label" for="auto_generate_checkbox">
                                        {{ __('messages.auto_generate') }}
                                    </label>
                                    {{-- Add a hidden input --}}
                                    <input type="hidden" name="auto_generate_account_no" id="auto_generate_hidden_input" value="1">
                                </div>
                            </div>
                        </div>
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
                            <input type="date" name="opening_date" class="form-control @error('opening_date') is-invalid @enderror" value="{{ old('opening_date', date('Y-m-d')) }}" required>
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
                            <label class="form-label">{{ __('messages.relation_with_member') }}</label>
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
        $(document).ready(function() {
            const autoGenerateCheckbox = $('#auto_generate_checkbox');
            const accountNoSuffixInput = $('#account_no_suffix'); // Number input
            const autoGenerateHidden = $('#auto_generate_hidden_input');

            function toggleAccountNoInput() {
                if (autoGenerateCheckbox.is(':checked')) {
                    // If auto generate:
                    accountNoSuffixInput.prop('disabled', true); // Disable the field
                    accountNoSuffixInput.val('{{ __("messages.auto") }}');
                    autoGenerateHidden.val('1');
                } else {
                    // If manual input:
                    accountNoSuffixInput.prop('disabled', false); // Enable the field
                    accountNoSuffixInput.val('').focus();
                    autoGenerateHidden.val('0');
                }
            }
            toggleAccountNoInput();
            autoGenerateCheckbox.on('change', toggleAccountNoInput);
        });
    </script>
@endpush