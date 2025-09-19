@extends('layout.master')

@push('plugin-styles')
    <link rel="stylesheet" href="{{ asset('build/plugins/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}">
@endpush

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="card">
        <div class="card-body">
            <h6 class="card-title">New Member Onboarding</h6>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('members.store_with_account') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- ======================================================= --}}
                {{-- ============== ১. ব্যক্তিগত তথ্য সেকশন ============== --}}
                {{-- ======================================================= --}}
                <div class="border p-3 mb-4 rounded">
                    <h5 class="mb-3 border-bottom pb-2">{{ __('messages.personal_info') }}</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">{{ __('messages.name') }} <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $member->name ?? '') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="father_name" class="form-label">{{ __('messages.father_name') }} <span class="text-danger">*</span></label>
                            <input type="text" id="father_name" name="father_name" class="form-control" value="{{ old('father_name', $member->father_name ?? '') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mother_name" class="form-label">{{ __('messages.mother_name') }} <span class="text-danger">*</span></label>
                            <input type="text" id="mother_name" name="mother_name" class="form-control" value="{{ old('mother_name', $member->mother_name ?? '') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="spouse_name" class="form-label">{{ __('messages.spouse_name') }}</label>
                            <input type="text" id="spouse_name" name="spouse_name" class="form-control" value="{{ old('spouse_name', $member->spouse_name ?? '') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="date_of_birth" class="form-label">{{ __('messages.date_of_birth') }} <span class="text-danger">*</span></label>
                            <input type="text" id="date_of_birth" name="date_of_birth" class="form-control flatpickr" value="{{ old('date_of_birth', isset($member) ? $member->date_of_birth->format('Y-m-d') : '') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="gender" class="form-label">{{ __('messages.gender') }}</label>
                            <select id="gender" name="gender" class="form-select">
                                <option value="male" {{ (old('gender', $member->gender ?? '') == 'male') ? 'selected' : '' }}>{{ __('messages.male') }}</option>
                                <option value="female" {{ (old('gender', $member->gender ?? '') == 'female') ? 'selected' : '' }}>{{ __('messages.female') }}</option>
                                <option value="other" {{ (old('gender', $member->gender ?? '') == 'other') ? 'selected' : '' }}>{{ __('messages.other') }}</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="marital_status" class="form-label">{{ __('messages.marital_status') }}</label>
                            <select id="marital_status" name="marital_status" class="form-select">
                                <option value="single" {{ (old('marital_status', $member->marital_status ?? '') == 'single') ? 'selected' : '' }}>{{ __('messages.single') }}</option>
                                <option value="married" {{ (old('marital_status', $member->marital_status ?? '') == 'married') ? 'selected' : '' }}>{{ __('messages.married') }}</option>
                                <option value="divorced" {{ (old('marital_status', $member->marital_status ?? '') == 'divorced') ? 'selected' : '' }}>{{ __('messages.divorced') }}</option>
                            </select>
                        </div>
                    </div>
                </div>


                {{-- ======================================================= --}}
                {{-- ============== ২. যোগাযোগের তথ্য সেকশন ============== --}}
                {{-- ======================================================= --}}
                <div class="border p-3 mb-4 rounded">
                    <h5 class="mb-3 border-bottom pb-2">{{ __('messages.contact_info') }}</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="mobile_no" class="form-label">{{ __('messages.mobile_no') }} <span class="text-danger">*</span></label>
                            <input type="text" id="mobile_no" name="mobile_no" class="form-control" value="{{ old('mobile_no', $member->mobile_no ?? '') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">{{ __('messages.email_address') }}</label>
                            <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $member->email ?? '') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="present_address" class="form-label">{{ __('messages.present_address') }} <span class="text-danger">*</span></label>
                            <textarea id="present_address" name="present_address" class="form-control" required rows="3">{{ old('present_address', $member->present_address ?? '') }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="permanent_address" class="form-label">{{ __('messages.permanent_address') }}</label>
                            <textarea id="permanent_address" name="permanent_address" class="form-control" rows="3">{{ old('permanent_address', $member->permanent_address ?? '') }}</textarea>
                        </div>
                    </div>
                </div>


                {{-- ======================================================= --}}
                {{-- ============== ৩. অতিরিক্ত তথ্য সেকশন ============== --}}
                {{-- ======================================================= --}}
                <div class="border p-3 mb-4 rounded">
                    <h5 class="mb-3 border-bottom pb-2">{{ __('messages.additional_info') }}</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="nid_no" class="form-label">{{ __('messages.nid_number') }}</label>
                            <input type="text" id="nid_no" name="nid_no" class="form-control" value="{{ old('nid_no', $member->nid_no ?? '') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="occupation" class="form-label">{{ __('messages.occupation') }}</label>
                            <input type="text" id="occupation" name="occupation" class="form-control" value="{{ old('occupation', $member->occupation ?? '') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="work_place" class="form-label">{{ __('messages.work_place') }}</label>
                            <input type="text" id="work_place" name="work_place" class="form-control" value="{{ old('work_place', $member->work_place ?? '') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="nationality" class="form-label">{{ __('messages.nationality') }}</label>
                            <input type="text" id="nationality" name="nationality" class="form-control" value="{{ old('nationality', $member->nationality ?? 'Bangladeshi') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="religion" class="form-label">{{ __('messages.religion') }}</label>
                            <input type="text" id="religion" name="religion" class="form-control" value="{{ old('religion', $member->religion ?? '') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="blood_group" class="form-label">{{ __('messages.blood_group') }}</label>
                            <input type="text" id="blood_group" name="blood_group" class="form-control" value="{{ old('blood_group', $member->blood_group ?? '') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="joining_date" class="form-label">{{ __('messages.joining_date') }} <span class="text-danger">*</span></label>
                            <input type="text" id="joining_date" name="joining_date" class="form-control flatpickr" value="{{ old('joining_date', isset($member) ? $member->joining_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                        </div>
                    </div>
                </div>


                {{-- ======================================================= --}}
                {{-- ============== ৪. ডকুমেন্টস সেকশন ============== --}}
                {{-- ======================================================= --}}
                <div class="border p-3 mb-4 rounded">
                    <h5 class="mb-3 border-bottom pb-2">{{ __('messages.documents') }}</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="area_id" class="form-label">{{ __('messages.area') }} <span class="text-danger">*</span></label>
                            @if(Auth::user()->hasRole('Admin'))
                                <select name="area_id" class="form-select" required>
                                    <option value="">Select Area...</option>
                                    @foreach ($areas as $area)
                                        <option value="{{ $area->id }}" {{ (isset($member) && $member->area_id == $area->id) ? 'selected' : '' }}>{{ $area->name }}</option>
                                    @endforeach
                                </select>
                            @else
                                {{-- মাঠকর্মীর জন্য লজিক --}}
                                <select name="area_id" class="form-select" required>
                                    @foreach (Auth::user()->areas as $area)
                                        <option value="{{ $area->id }}" {{ (isset($member) && $member->area_id == $area->id) ? 'selected' : '' }}>{{ $area->name }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="photo" class="form-label">{{ __('messages.photo') }}</label>
                            <input type="file" id="photo" name="photo" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="signature" class="form-label">{{ __('messages.signature') }}</label>
                            <input type="file" id="signature" name="signature" class="form-control">
                        </div>
                    </div>
                </div>



                {{-- Savings Account Section --}}
                <div class="border p-3 mb-4 rounded">
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" name="open_savings_account" id="open_savings_account" value="1" {{ old('open_savings_account', '1') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="open_savings_account">
                            <h5 class="mb-0">2. {{ __('messages.open_savings_account') }}</h5>
                        </label>
                    </div>
                    <div id="savings_account_fields" style="display: none">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('messages.scheme_type') }} <span class="text-danger">*</span></label>
                                <select name="scheme_type" id="scheme_type" class="form-control">
                                    <option value="daily">{{ __('messages.daily') }}</option>
                                    <option value="weekly">{{ __('messages.weekly') }}</option>
                                    <option value="monthly">{{ __('messages.monthly') }}</option>
                                    <option value="dps">{{ __('messages.dps') }}</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('messages.interest_rate') }} (%) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="interest_rate" class="form-control" value="{{ old('interest_rate') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('messages.opening_date') }} <span class="text-danger">*</span></label>
                                <input type="text" name="opening_date" class="form-control flatpickr" value="{{ old('opening_date', date('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('messages.installment_amount') }} <span class="text-danger">*</span></label>
                                <input type="number" step="1" name="installment_amount" class="form-control" value="{{ old('installment_amount') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('messages.initial_deposit') }}</label>
                                <input type="number" step="1" name="initial_deposit" class="form-control" value="{{ old('initial_deposit', '0') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('messages.nominee_name') }} <span class="text-danger">*</span></label>
                                <input type="text" name="nominee_name" class="form-control" value="{{ old('nominee_name') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('messages.nominee_phone') }} <span class="text-danger">*</span></label>
                                <input type="text" name="nominee_phone" class="form-control" value="{{ old('nominee_phone') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('messages.nominee_relation') }} <span class="text-danger">*</span></label>
                                <input type="text" name="nominee_relation" class="form-control" value="{{ old('nominee_relation') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('messages.nominee_address') }} <span class="text-danger">*</span></label>
                                <input type="text" name="nominee_address" class="form-control" value="{{ old('nominee_address') }}">
                            </div>
                        </div>
                    </div>
                </div>


                {{-- Loan Account Section --}}
                <div class="border p-3 rounded">
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" name="issue_loan_account" id="issue_loan_account" value="1" {{ old('issue_loan_account') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="issue_loan_account">
                            <h5 class="mb-0">3. Issue a New Loan?</h5>
                        </label>
                    </div>
                    <div id="loan_account_fields" style="display: none;">
                        {{-- loan_accounts/create.blade.php থেকে ঋণের ফর্মের কোড এখানে অন্তর্ভুক্ত করুন --}}
                        {{-- আমরা একটি পার্শিয়াল ভিউ তৈরি করতে পারি --}}
                        @include('loan_accounts._form_onboarding', ['loanAccount' => null])
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Onboard Member</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('custom-scripts')
    <script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('build/plugins/flatpickr/flatpickr.min.js') }}"></script>
    {{-- jQuery, Select2, Flatpickr JS --}}
    <script>
        $(document).ready(function() {
            $(".flatpickr").flatpickr({
                altInput: true,
                dateFormat: 'Y-m-d',
                altFormat: 'd/m/Y'
            })
            // Checkbox toggle logic
            function toggleFields(checkboxId, fieldsId) {
                const checkbox = $('#' + checkboxId);
                const fieldsDiv = $('#' + fieldsId);

                fieldsDiv.toggle(checkbox.is(':checked'));

                checkbox.on('change', function() {
                    fieldsDiv.slideToggle(this.checked);
                });
            }

            toggleFields('open_savings_account', 'savings_account_fields');
            toggleFields('issue_loan_account', 'loan_account_fields');

        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const guarantorTypeSelect = document.getElementById('guarantorType');
            const memberDiv = document.getElementById('memberGuarantor');
            const outsiderDiv = document.getElementById('outsiderGuarantor');

            function toggleGuarantorFields() {
                if (guarantorTypeSelect.value === 'member') {
                    memberDiv.style.display = 'block';
                    outsiderDiv.style.display = 'none';
                } else if (guarantorTypeSelect.value === 'outsider') {
                    memberDiv.style.display = 'none';
                    outsiderDiv.style.display = 'block';
                } else {
                    memberDiv.style.display = 'none';
                    outsiderDiv.style.display = 'none';
                }
            }

            guarantorTypeSelect.addEventListener('change', toggleGuarantorFields);

            // Initial call to set state on page load (for validation errors)
            toggleGuarantorFields();
        });


        document.getElementById('add-document-btn').addEventListener('click', function() {
            const wrapper = document.getElementById('loan-documents-wrapper');
            const newRow = document.createElement('div');
            newRow.className = 'row mb-2 align-items-center';
            newRow.innerHTML = `
        <div class="col-md-5"><input type="text" name="document_names[]" class="form-control" placeholder="Document Name"></div>
        <div class="col-md-5"><input type="file" name="loan_documents[]" class="form-control"></div>
        <div class="col-md-2"><button type="button" class="btn btn-sm btn-danger remove-document-btn">Remove</button></div>
    `;
            wrapper.appendChild(newRow);
        });

        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('remove-document-btn')) {
                e.target.closest('.row').remove();
            }
        });
    </script>
@endpush
