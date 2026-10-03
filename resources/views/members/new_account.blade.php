@extends('layout.master')

@push('plugin-styles')
    <link rel="stylesheet" href="{{ asset('build/plugins/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}">
    <style>
        :root {
            --accent-personal: #3b82f6;
            --accent-contact: #10b981;
            --accent-additional: #f59e0b;
            --accent-nominee: #8b5cf6;
            --accent-documents: #06b6d4;
            --accent-savings: #6366f1;
            --accent-loan: #ec4899;
        }

        .premium-card {
            border-radius: 16px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            margin-bottom: 24px;
        }

        .premium-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 24px;
            border-radius: 16px 16px 0 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-card {
            border: none;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            color: white !important;
        }

        .section-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
        }

        /* Section Specific Gradients */
        .section-card.personal { background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); }
        .section-card.contact { background: linear-gradient(135deg, #064e3b 0%, #10b981 100%); }
        .section-card.additional { background: linear-gradient(135deg, #78350f 0%, #f59e0b 100%); }
        .section-card.nominee { background: linear-gradient(135deg, #4c1d95 0%, #8b5cf6 100%); }
        .section-card.documents { background: linear-gradient(135deg, #164e63 0%, #06b6d4 100%); }
        .section-card.savings { background: linear-gradient(135deg, #312e81 0%, #6366f1 100%); }
        .section-card.loan { background: linear-gradient(135deg, #831843 0%, #ec4899 100%); }

        .section-title {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
            font-weight: 800;
            font-size: 1.25rem;
            color: white !important;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .section-title i, .section-title svg {
            width: 24px;
            height: 24px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }

        .form-label {
            font-weight: 600;
            color: rgba(255, 255, 255, 0.95) !important;
            font-size: 0.95rem;
            margin-bottom: 8px;
            display: block;
        }

        .text-muted, .text-danger, small {
            color: #cbd5e1 !important; /* Lighter color for readability on dark backgrounds */
        }
        
        .text-danger {
            color: #fca5a5 !important; /* Soft red for visibility */
            font-weight: 700;
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.95) !important;
            color: #1e293b !important;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            background: #ffffff !important;
            box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.3);
            border-color: white;
            outline: none;
        }

        .btn-onboard {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border: none;
            padding: 16px 40px;
            border-radius: 14px;
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
            transition: all 0.3s ease;
        }

        .btn-onboard:hover {
            transform: scale(1.05);
            box-shadow: 0 15px 30px rgba(79, 70, 229, 0.4);
            color: white;
        }

        .input-group-text {
            background-color: rgba(255, 255, 255, 0.9);
            border-color: rgba(255, 255, 255, 0.3);
            color: #475569;
            font-weight: 600;
        }

        /* Custom checkbox style for visibility */
        .form-check-input {
            width: 1.2rem;
            height: 1.2rem;
            cursor: pointer;
            border: 2px solid rgba(255, 255, 255, 0.5);
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .form-check-input:checked {
            background-color: white;
            border-color: white;
        }
    </style>
@endpush

@section('content')
    {{-- Global Notifications --}}
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow mb-4" style="border-left: 8px solid #ef4444 !important; background: #ffffff !important; color: #b91c1c !important; position: relative; z-index: 1000;">
            <div class="d-flex align-items-center mb-3">
                <i data-lucide="alert-triangle" class="me-3" style="width: 28px; height: 28px; color: #ef4444;"></i>
                <div>
                    <h5 class="mb-0 fw-bold" style="color: #b91c1c !important;">{{ __('messages.error') ?? 'Validation Error' }}</h5>
                    <p class="mb-0 opacity-75">Please fix the following issues to proceed.</p>
                </div>
            </div>
            <ul class="mb-0 ps-4">
                @foreach ($errors->all() as $error)
                    <li class="fw-bold mb-1" style="color: #b91c1c !important;">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger border-0 shadow mb-4" style="border-left: 8px solid #ef4444 !important; background: #ffffff !important; color: #b91c1c !important; position: relative; z-index: 1000;">
            <div class="d-flex align-items-center">
                <i data-lucide="x-octagon" class="me-3" style="width: 28px; height: 28px; color: #ef4444;"></i>
                <span class="fw-bold fs-5">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success border-0 shadow mb-4" style="border-left: 8px solid #10b981 !important; background: #ffffff !important; color: #065f46 !important; position: relative; z-index: 1000;">
            <div class="d-flex align-items-center">
                <i data-lucide="check-circle-2" class="me-3" style="width: 28px; height: 28px; color: #10b981;"></i>
                <span class="fw-bold fs-5">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <div class="premium-card">
        <div class="premium-header">
            <i data-lucide="user-plus"></i>
            <h4 class="mb-0">{{ __('messages.new_member_onboarding') }}</h4>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('members.store_with_account') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- ======================================================= --}}
                {{-- ============== ১. ব্যক্তিগত তথ্য সেকশন ============== --}}
                <div class="section-card personal">
                    <div class="section-title">
                        <i data-lucide="user"></i>
                        <span>{{ __('messages.personal_info') }}</span>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="account_no" class="form-label">{{ __('messages.account_no') }} <small class="text-muted">{{ __('messages.account_no_helper') }}</small></label>
                            <input type="text" id="account_no" name="account_no" class="form-control" value="{{ old('account_no', $next_account_no ?? '') }}" placeholder="{{ __('messages.account_no_placeholder') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">{{ __('messages.name') }} <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $member->name ?? '') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="father_name" class="form-label">{{ __('messages.father_name') }}</label>
                            <input type="text" id="father_name" name="father_name" class="form-control" value="{{ old('father_name', $member->father_name ?? '') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mother_name" class="form-label">{{ __('messages.mother_name') }}</label>
                            <input type="text" id="mother_name" name="mother_name" class="form-control" value="{{ old('mother_name', $member->mother_name ?? '') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="spouse_name" class="form-label">{{ __('messages.spouse_name') }}</label>
                            <input type="text" id="spouse_name" name="spouse_name" class="form-control" value="{{ old('spouse_name', $member->spouse_name ?? '') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="date_of_birth" class="form-label">{{ __('messages.date_of_birth') }}</label>
                            <input type="text" id="date_of_birth" name="date_of_birth" class="form-control flatpickr" value="{{ old('date_of_birth', isset($member) ? $member->date_of_birth->format('Y-m-d') : '') }}">
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
                <div class="section-card contact">
                    <div class="section-title">
                        <i data-lucide="phone"></i>
                        <span>{{ __('messages.contact_info') }}</span>
                    </div>
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
                            <label for="present_address" class="form-label">{{ __('messages.present_address') }}</label>
                            <textarea id="present_address" name="present_address" class="form-control" rows="3">{{ old('present_address', $member->present_address ?? '') }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="permanent_address" class="form-label">{{ __('messages.permanent_address') }}</label>
                            <textarea id="permanent_address" name="permanent_address" class="form-control" rows="3">{{ old('permanent_address', $member->permanent_address ?? '') }}</textarea>
                        </div>
                    </div>
                </div>


                {{-- ======================================================= --}}
                {{-- ============== ৩. অতিরিক্ত তথ্য সেকশন ============== --}}
                <div class="section-card additional">
                    <div class="section-title">
                        <i data-lucide="info"></i>
                        <span>{{ __('messages.additional_info') }}</span>
                    </div>
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
                            <label for="joining_date" class="form-label">{{ __('messages.joining_date') }}</label>
                            <input type="text" id="joining_date" name="joining_date" class="form-control flatpickr" value="{{ old('joining_date', isset($member) ? $member->joining_date->format('Y-m-d') : date('Y-m-d')) }}">
                        </div>
                    </div>
                </div>

                {{-- Nominee Information Section --}}
                <div class="section-card nominee">
                    <div class="section-title">
                        <i data-lucide="heart"></i>
                        <span>{{ __('messages.nominee_information') }}</span>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">{{ __('messages.nominee_name') }}</label>
                            <input type="text" name="nominee_name" class="form-control" value="{{ old('nominee_name') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">{{ __('messages.nominee_phone') }}</label>
                            <input type="text" name="nominee_phone" class="form-control" value="{{ old('nominee_phone') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">{{ __('messages.nominee_relation') }}</label>
                            <input type="text" name="nominee_relation" class="form-control" value="{{ old('nominee_relation') }}">
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label">{{ __('messages.nominee_address') }}</label>
                            <input type="text" name="nominee_address" class="form-control" value="{{ old('nominee_address') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">{{ __('messages.nominee_nid') }}</label>
                            <input type="text" name="nominee_nid" class="form-control" value="{{ old('nominee_nid') }}">
                        </div>
                    </div>
                </div>


                {{-- ======================================================= --}}
                {{-- ============== ৪. ডকুমেন্টস সেকশন ============== --}}
                <div class="section-card documents">
                    <div class="section-title">
                        <i data-lucide="file-text"></i>
                        <span>{{ __('messages.documents') }}</span>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="area_id" class="form-label">{{ __('messages.area') }} <span class="text-danger">*</span></label>
                            @if(Auth::user()->hasRole('Admin'))
                                <select name="area_id" class="form-select" required>
                                    <option value="">{{ __('messages.select_area') }}...</option>
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



                {{-- Automatic Savings Info --}}
                <div class="section-card savings">
                    <div class="section-title">
                        <i data-lucide="wallet"></i>
                        <span>{{ __('messages.automatic_savings_title') }}</span>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-0">{{ __('messages.automatic_savings_help') }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('messages.initial_deposit') }}</label>
                            <input type="number" step="1" name="initial_deposit" class="form-control" value="{{ old('initial_deposit', '0') }}">
                        </div>
                    </div>
                </div>


                {{-- Loan Account Section --}}
                <div class="section-card loan">
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" name="issue_loan_account" id="issue_loan_account" value="1" {{ old('issue_loan_account') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="issue_loan_account">
                            <div class="section-title mb-0">
                                <i data-lucide="landmark"></i>
                                <span>3. {{ __('messages.issue_loan_account') }}</span>
                            </div>
                        </label>
                    </div>
                    <div id="loan_account_fields" style="display: none;">
                        {{-- loan_accounts/create.blade.php থেকে ঋণের ফর্মের কোড এখানে অন্তর্ভুক্ত করুন --}}
                        {{-- আমরা একটি পার্শিয়াল ভিউ তৈরি করতে পারি --}}
                        @include('loan_accounts._form_onboarding', ['loanAccount' => null])
                    </div>
                </div>

                <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-onboard btn-lg text-white">
                        <i data-lucide="rocket" class="me-2"></i>
                        {{ __('messages.onboard_member') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('custom-scripts')
    {{-- jQuery and Select2 are loaded in master layout --}}
    <script>
        $(document).ready(function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
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

            $('.js-select2').select2({
                width: '100%'
            });
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
