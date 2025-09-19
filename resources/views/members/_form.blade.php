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


<div class="mt-4">
    <button type="submit" class="btn btn-primary">{{ $buttonText ?? 'Save Member' }}</button>
</div>
