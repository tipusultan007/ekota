@extends('layout.master')

@push('plugin-styles')
    <link rel="stylesheet" href="{{ asset('build/plugins/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}">
@endpush

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">{{ __('messages.user_management') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('messages.edit_user') }}</li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">{{ __('messages.edit_user') }}: {{ $user->name }}</h6>
                    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- User Information Section --}}
                        <div class="border p-3 mb-4 rounded">
                            <h5 class="mb-3">{{ __('messages.user_information') }}</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">{{ __('messages.full_name') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $user->name) }}" required>
                                    @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">{{ __('messages.phone_number') }}</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', $user->phone) }}">
                                    @error('phone') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="nid_no" class="form-label">{{ __('messages.nid_number') }}</label>
                                    <input type="text" class="form-control @error('nid_no') is-invalid @enderror" name="nid_no" value="{{ old('nid_no', $user->nid_no) }}">
                                    @error('nid_no') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="joining_date" class="form-label">{{ __('messages.joining_date') }}</label>
                                    <input type="text" class="form-control flatpickr @error('joining_date') is-invalid @enderror" name="joining_date" value="{{ old('joining_date', $user->joining_date ? $user->joining_date->format('Y-m-d') : '') }}">
                                    @error('joining_date') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="address" class="form-label">{{ __('messages.address') }}</label>
                                    <textarea name="address" class="form-control">{{ old('address', $user->address) }}</textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="photo" class="form-label">{{ __('messages.profile_photo') }}</label>
                                    <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror">
                                    @if($user->getFirstMediaUrl('user_photo'))
                                        <small class="form-text text-muted">Current: <a href="{{ $user->getFirstMediaUrl('user_photo') }}" target="_blank">View Photo</a></small>
                                    @endif
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="documents" class="form-label">{{ __('messages.other_documents') }}</label>
                                    <input type="file" name="documents[]" class="form-control" multiple>
                                </div>
                            </div>
                        </div>

                        {{-- Account Information Section --}}
                        <div class="border p-3 rounded">
                            <h5 class="mb-3">{{ __('messages.account_information') }}</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">{{ __('messages.email_address') }} <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $user->email) }}" required>
                                    @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="role" class="form-label">{{ __('messages.role') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('role') is-invalid @enderror" name="role" id="userRole" required>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('role') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">{{ __('messages.password') }}</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" name="password">
                                    <small class="form-text text-muted">{{ __('messages.leave_password_blank') }}</small>
                                    @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label">{{ __('messages.confirm_password') }}</label>
                                    <input type="password" class="form-control" name="password_confirmation">
                                </div>
                                <div class="col-md-12 mb-3" id="area-select" style="display: {{ $user->hasRole('Field Worker') ? 'block' : 'none' }};">
                                    <label for="areas" class="form-label">{{ __('messages.assign_areas') }}</label>
                                    <select class="form-select" name="areas[]" id="areas" multiple>
                                        @foreach ($areas as $area)
                                            <option value="{{ $area->id }}" {{ $user->areas->contains($area->id) ? 'selected' : '' }}>
                                                {{ $area->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mt-4">{{ __('messages.update_user') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('plugin-scripts')
    <script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('build/plugins/select2/select2.min.js') }}"></script>
    <script src="{{ asset('build/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Select2 for Areas
            if ($("#areas").length) {
                $("#areas").select2({
                    placeholder: "{{ __('messages.assign_areas') }}",
                    width: '100%'
                });
            }

            // Flatpickr for Joining Date
            $(".flatpickr").flatpickr({
                altInput: true,
                dateFormat: "Y-m-d",
                altFormat: "d/m/Y"
            });

            // Show/Hide Area Select based on Role
            $('#userRole').on('change', function () {
                var areaSelect = $('#area-select');
                if (this.value === 'Field Worker') {
                    areaSelect.slideDown();
                } else {
                    areaSelect.slideUp();
                }
            });
        });
    </script>
@endpush
