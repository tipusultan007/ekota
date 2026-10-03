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

    <div class="premium-card">
        <div class="premium-header">
            <i data-lucide="edit-3"></i>
            <h4 class="mb-0">{{ __('messages.edit_member') ?? 'Edit Member Profile' }} : {{ $member->name }}</h4>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('members.update', $member->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- ======================================================= --}}
                {{-- ============== ১. ব্যক্তিগত তথ্য সেকশন ============== --}}
                <div class="section-card personal">
                    <div class="section-title">
                        <i data-lucide="user"></i>
                        <span>{{ __('messages.personal_info') }}</span>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="account_no" class="form-label">{{ __('messages.account_no') }}</label>
                            <input type="text" id="account_no" name="account_no" class="form-control" value="{{ old('account_no', $member->account_no) }}" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">{{ __('messages.name') }} <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $member->name) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="father_name" class="form-label">{{ __('messages.father_name') }}</label>
                            <input type="text" id="father_name" name="father_name" class="form-control" value="{{ old('father_name', $member->father_name) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mother_name" class="form-label">{{ __('messages.mother_name') }}</label>
                            <input type="text" id="mother_name" name="mother_name" class="form-control" value="{{ old('mother_name', $member->mother_name) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="spouse_name" class="form-label">{{ __('messages.spouse_name') }}</label>
                            <input type="text" id="spouse_name" name="spouse_name" class="form-control" value="{{ old('spouse_name', $member->spouse_name) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="date_of_birth" class="form-label">{{ __('messages.date_of_birth') }}</label>
                            <input type="text" id="date_of_birth" name="date_of_birth" class="form-control flatpickr" value="{{ old('date_of_birth', $member->date_of_birth ? $member->date_of_birth->format('Y-m-d') : '') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="gender" class="form-label">{{ __('messages.gender') }}</label>
                            <select id="gender" name="gender" class="form-select">
                                <option value="male" {{ (old('gender', $member->gender) == 'male') ? 'selected' : '' }}>{{ __('messages.male') }}</option>
                                <option value="female" {{ (old('gender', $member->gender) == 'female') ? 'selected' : '' }}>{{ __('messages.female') }}</option>
                                <option value="other" {{ (old('gender', $member->gender) == 'other') ? 'selected' : '' }}>{{ __('messages.other') }}</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="marital_status" class="form-label">{{ __('messages.marital_status') }}</label>
                            <select id="marital_status" name="marital_status" class="form-select">
                                <option value="single" {{ (old('marital_status', $member->marital_status) == 'single') ? 'selected' : '' }}>{{ __('messages.single') }}</option>
                                <option value="married" {{ (old('marital_status', $member->marital_status) == 'married') ? 'selected' : '' }}>{{ __('messages.married') }}</option>
                                <option value="divorced" {{ (old('marital_status', $member->marital_status) == 'divorced') ? 'selected' : '' }}>{{ __('messages.divorced') }}</option>
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
                            <input type="text" id="mobile_no" name="mobile_no" class="form-control" value="{{ old('mobile_no', $member->mobile_no) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">{{ __('messages.email_address') }}</label>
                            <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $member->email) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="present_address" class="form-label">{{ __('messages.present_address') }}</label>
                            <textarea id="present_address" name="present_address" class="form-control" rows="3">{{ old('present_address', $member->present_address) }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="permanent_address" class="form-label">{{ __('messages.permanent_address') }}</label>
                            <textarea id="permanent_address" name="permanent_address" class="form-control" rows="3">{{ old('permanent_address', $member->permanent_address) }}</textarea>
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
                            <input type="text" id="nid_no" name="nid_no" class="form-control" value="{{ old('nid_no', $member->nid_no) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="occupation" class="form-label">{{ __('messages.occupation') }}</label>
                            <input type="text" id="occupation" name="occupation" class="form-control" value="{{ old('occupation', $member->occupation) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="work_place" class="form-label">{{ __('messages.work_place') }}</label>
                            <input type="text" id="work_place" name="work_place" class="form-control" value="{{ old('work_place', $member->work_place) }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="nationality" class="form-label">{{ __('messages.nationality') }}</label>
                            <input type="text" id="nationality" name="nationality" class="form-control" value="{{ old('nationality', $member->nationality ?? 'Bangladeshi') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="religion" class="form-label">{{ __('messages.religion') }}</label>
                            <input type="text" id="religion" name="religion" class="form-control" value="{{ old('religion', $member->religion) }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="blood_group" class="form-label">{{ __('messages.blood_group') }}</label>
                            <input type="text" id="blood_group" name="blood_group" class="form-control" value="{{ old('blood_group', $member->blood_group) }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="joining_date" class="form-label">{{ __('messages.joining_date') }}</label>
                            <input type="text" id="joining_date" name="joining_date" class="form-control flatpickr" value="{{ old('joining_date', $member->joining_date ? $member->joining_date->format('Y-m-d') : '') }}">
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
                            <input type="text" name="nominee_name" class="form-control" value="{{ old('nominee_name', $member->nominee_name) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">{{ __('messages.nominee_phone') }}</label>
                            <input type="text" name="nominee_phone" class="form-control" value="{{ old('nominee_phone', $member->nominee_phone) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">{{ __('messages.nominee_relation') }}</label>
                            <input type="text" name="nominee_relation" class="form-control" value="{{ old('nominee_relation', $member->nominee_relation) }}">
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label">{{ __('messages.nominee_address') }}</label>
                            <input type="text" name="nominee_address" class="form-control" value="{{ old('nominee_address', $member->nominee_address) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">{{ __('messages.nominee_nid') }}</label>
                            <input type="text" name="nominee_nid" class="form-control" value="{{ old('nominee_nid', $member->nominee_nid) }}">
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
                            <label for="area_id" class="form-label">{{ __('messages.area') }}</label>
                            @if(Auth::user()->hasRole('Admin'))
                                <select name="area_id" class="form-select" required>
                                    @foreach ($areas as $area)
                                        <option value="{{ $area->id }}" {{ (old('area_id', $member->area_id) == $area->id) ? 'selected' : '' }}>{{ $area->name }}</option>
                                    @endforeach
                                </select>
                            @else
                                <select name="area_id" class="form-select" required>
                                    @foreach (Auth::user()->areas as $area)
                                        <option value="{{ $area->id }}" {{ (old('area_id', $member->area_id) == $area->id) ? 'selected' : '' }}>{{ $area->name }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="photo" class="form-label">{{ __('messages.photo') }}</label>
                            <input type="file" id="photo" name="photo" class="form-control">
                            @if($member->getFirstMediaUrl('member_photo'))
                                <div class="mt-2">
                                    <small class="text-white opacity-75">Current Photo:</small>
                                    <img src="{{ $member->getFirstMediaUrl('member_photo') }}" alt="Photo" class="d-block mt-1 rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                </div>
                            @endif
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="signature" class="form-label">{{ __('messages.signature') }}</label>
                            <input type="file" id="signature" name="signature" class="form-control">
                            @if($member->getFirstMediaUrl('member_signature'))
                                <div class="mt-2">
                                    <small class="text-white opacity-75">Current Signature:</small>
                                    <img src="{{ $member->getFirstMediaUrl('member_signature') }}" alt="Signature" class="d-block mt-1 rounded bg-white p-1" style="width: 100px; height: 40px; object-fit: contain;">
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-onboard btn-lg">
                        <i data-lucide="check-circle" class="me-2"></i>
                        {{ __('messages.update_member') ?? 'Update Member Profile' }}
                    </button>
                    <a href="{{ route('members.show', $member->id) }}" class="btn btn-light btn-lg ms-2 text-primary fw-bold" style="border-radius: 14px; padding: 16px 30px;">
                        {{ __('messages.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('custom-scripts')
    <script>
        $(".flatpickr").flatpickr({
            altInput: true,
            dateFormat: 'Y-m-d',
            altFormat: 'd/m/Y'
        })
    </script>
@endpush
