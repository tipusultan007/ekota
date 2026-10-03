@extends('layout.master')

@push('plugin-styles')
    <link rel="stylesheet" href="{{ asset('build/plugins/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}">
    <style>
        .premium-card {
            border: none;
            border-radius: 24px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            background: #fff;
            margin-bottom: 2rem;
        }

        .premium-header {
            background: linear-gradient(135deg, #0891b2 0%, #06b6d4 50%, #22d3ee 100%);
            padding: 2.5rem 2rem;
            color: white;
            position: relative;
        }

        .header-shape {
            position: absolute;
            top: 0;
            right: 0;
            opacity: 0.1;
            pointer-events: none;
        }

        .form-panel {
            background: #f0f9ff;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid #e0f2fe;
            transition: all 0.3s ease;
        }

        .form-panel:hover {
            border-color: #bae6fd;
            box-shadow: 0 5px 15px rgba(8, 145, 178, 0.05);
        }

        .panel-title {
            color: #0e7490;
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .form-label-premium {
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.6rem;
            font-size: 0.9rem;
        }

        .input-premium,
        .select2-container--bootstrap-5 .select2-selection--single {
            border-radius: 12px !important;
            padding: 0.6rem 1rem !important;
            border: 1px solid #bae6fd !important;
            background-color: #fff !important;
            transition: all 0.3s !important;
            min-height: 46px !important;
            display: flex !important;
            align-items: center !important;
            width: 100% !important;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple {
            border-radius: 12px !important;
            padding: 8px 12px !important;
            border: 1px solid #bae6fd !important;
            background-color: #fff !important;
            min-height: 46px !important;
            width: 100% !important;
            display: block !important;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__rendered {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 6px !important;
            padding: 0 !important;
            margin: 0 !important;
            list-style: none !important;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
            background-color: #0891b2 !important;
            border: none !important;
            color: white !important;
            border-radius: 6px !important;
            padding: 4px 10px !important;
            margin: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            font-size: 0.85rem !important;
            font-weight: 600 !important;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove {
            color: white !important;
            margin-right: 8px !important;
            border: none !important;
            font-size: 1.1rem !important;
            line-height: 1 !important;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove:hover {
            background-color: transparent !important;
            opacity: 0.8 !important;
            color: #fff !important;
        }

        .select2-container--bootstrap-5 .select2-search--inline .select2-search__field {
            margin: 0 !important;
            height: 30px !important;
            line-height: 30px !important;
        }

        .input-group {
            flex-wrap: nowrap !important;
        }

        .input-group-text-premium {
            background: #fff !important;
            border: 1px solid #bae6fd !important;
            color: #0891b2 !important;
            padding: 0.6rem 1rem !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-width: 50px !important;
        }

        .input-group > .input-group-text-premium:first-child {
            border-radius: 12px 0 0 12px !important;
            border-right: none !important;
        }

        .input-group > .input-group-text-premium:last-child {
            border-radius: 0 12px 12px 0 !important;
            border-left: none !important;
        }

        .input-group > .input-premium {
            flex: 1 1 auto !important;
            width: 1% !important;
            border-radius: 12px !important;
        }

        .input-group > .input-group-text-premium + .input-premium {
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
        }

        .input-group > .input-premium:not(:last-child) {
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
        }

        /* Select2 Premium Override for Input Groups */
        .input-group > .select2-container--default {
            flex: 1 1 auto !important;
            width: 1% !important;
        }

        .input-group > .input-group-text-premium + .select2-container--default .select2-selection--single {
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
        }

        .input-premium:focus,
        .select2-container--default.select2-container--focus .select2-selection {
            border-color: #06b6d4 !important;
            box-shadow: 0 0 0 4px rgba(6, 182, 212, 0.1) !important;
            z-index: 5 !important;
        }

        .btn-update {
            background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);
            border: none;
            padding: 0.8rem 2.5rem;
            border-radius: 12px;
            font-weight: 700;
            color: white;
            box-shadow: 0 4px 15px rgba(8, 145, 178, 0.3);
            transition: all 0.3s;
        }

        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(8, 145, 178, 0.4);
            color: white;
        }

        .image-preview-container {
            width: 100px;
            height: 100px;
            border-radius: 16px;
            overflow: hidden;
            border: 3px solid #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            margin-top: 0.5rem;
        }

        .status-select-wrapper .select2-container--bootstrap-5 .select2-selection {
            font-weight: 600;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="premium-card">
                <div class="premium-header">
                    <img src="data:image/svg+xml,%3Csvg width='200' height='200' viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 0h200v200H0z' fill='none'/%3E%3Cpath d='M200 0c0 110.457-89.543 200-200 200h200V0z' fill='white' fill-opacity='.05'/%3E%3C/svg%3E" class="header-shape">
                    <div class="d-flex justify-content-between align-items-center position-relative z-1">
                        <div>
                            <h3 class="fw-bold mb-1 text-white">{{ __('messages.edit_user') ?? 'Edit User' }}</h3>
                            <p class="mb-0 opacity-75 text-white">{{ $user->name }} - {{ $user->roles->pluck('name')->implode(', ') }}</p>
                        </div>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-header">
                            <i data-lucide="arrow-left" class="icon-sm me-2"></i> {{ __('messages.back_to_list') ?? 'Back to List' }}
                        </a>
                    </div>
                </div>

                <div class="card-body p-4 p-lg-5">
                    @if ($errors->any())
                        <div class="alert alert-danger rounded-3 border-0 mb-4" style="background: #fef2f2; color: #dc2626;">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Personal Information --}}
                        <div class="form-panel">
                            <h5 class="panel-title">
                                <i data-lucide="user" class="icon-sm"></i> {{ __('messages.personal_information') ?? 'Personal Information' }}
                            </h5>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label-premium">{{ __('messages.full_name') ?? 'Full Name' }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text-premium"><i data-lucide="info" class="icon-xs"></i></span>
                                        <input type="text" name="name" class="form-control input-premium @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-premium">{{ __('messages.phone_number') ?? 'Phone Number' }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text-premium"><i data-lucide="phone" class="icon-xs"></i></span>
                                        <input type="text" name="phone" class="form-control input-premium @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-premium">{{ __('messages.nid_number') ?? 'NID Number' }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text-premium"><i data-lucide="credit-card" class="icon-xs"></i></span>
                                        <input type="text" name="nid_no" class="form-control input-premium @error('nid_no') is-invalid @enderror" value="{{ old('nid_no', $user->nid_no) }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-premium">{{ __('messages.profile_photo') ?? 'Profile Photo' }}</label>
                                    <input type="file" name="photo" class="form-control input-premium @error('photo') is-invalid @enderror">
                                    @if($user->getFirstMediaUrl('user_photo'))
                                        <div class="image-preview-container">
                                            <img src="{{ $user->getFirstMediaUrl('user_photo', 'thumb') }}" alt="User Photo" class="w-100 h-100 object-fit-cover">
                                        </div>
                                    @endif
                                </div>
                                <div class="col-12">
                                    <label class="form-label-premium">{{ __('messages.address') ?? 'Address' }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text-premium"><i data-lucide="map-pin" class="icon-xs"></i></span>
                                        <textarea name="address" class="form-control input-premium" style="height: auto; min-height: 100px;">{{ old('address', $user->address) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Employment Details --}}
                        <div class="form-panel">
                            <h5 class="panel-title">
                                <i data-lucide="briefcase" class="icon-sm"></i> {{ __('messages.employment_details') ?? 'Employment Details' }}
                            </h5>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label-premium">{{ __('messages.joining_date') ?? 'Joining Date' }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text-premium"><i data-lucide="calendar" class="icon-xs"></i></span>
                                        <input type="text" name="joining_date" class="form-control input-premium flatpickr @error('joining_date') is-invalid @enderror" value="{{ old('joining_date', $user->joining_date ? $user->joining_date->format('Y-m-d') : '') }}">
                                    </div>
                                </div>
                                <div class="col-md-6 status-select-wrapper">
                                    <label class="form-label-premium">{{ __('messages.user_status') ?? 'User Status' }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text-premium"><i data-lucide="activity" class="icon-xs"></i></span>
                                        <select name="status" class="form-select select2-simple" required>
                                            <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }} data-icon="check-circle" class="text-success">Active</option>
                                            <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }} data-icon="pause-circle" class="text-warning">Inactive</option>
                                            <option value="terminated" {{ old('status', $user->status) == 'terminated' ? 'selected' : '' }} data-icon="x-circle" class="text-danger">Terminated</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label-premium text-muted small mb-2"><i data-lucide="file" class="icon-xs me-1"></i> {{ __('messages.other_documents') ?? 'Other Documents' }}</label>
                                    <input type="file" name="documents[]" class="form-control input-premium" multiple>
                                    @if($user->getMedia('user_documents')->count() > 0)
                                        <div class="mt-2 d-flex flex-wrap gap-2">
                                            @foreach($user->getMedia('user_documents') as $doc)
                                                <a href="{{ $doc->getUrl() }}" target="_blank" class="badge rounded-pill bg-light text-dark border p-2 text-decoration-none">
                                                    <i data-lucide="file-text" class="icon-xs me-1"></i> {{ Str::limit($doc->name, 20) }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Account & Access --}}
                        <div class="form-panel">
                            <h5 class="panel-title">
                                <i data-lucide="lock" class="icon-sm"></i> {{ __('messages.account_access') ?? 'Account & Access' }}
                            </h5>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label-premium">{{ __('messages.user_role') ?? 'User Role' }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text-premium"><i data-lucide="shield" class="icon-xs"></i></span>
                                        <select class="form-select select2-simple @error('role') is-invalid @enderror" name="role" id="userRole" required>
                                           <option value="">Select Role</option>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-premium">{{ __('messages.new_password') ?? 'New Password' }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text-premium"><i data-lucide="key" class="icon-xs"></i></span>
                                        <input type="password" name="password" id="password" class="form-control input-premium @error('password') is-invalid @enderror" placeholder="••••••••">
                                        <button class="input-group-text-premium border-start-0 toggle-password" type="button" data-target="password" style="border-radius: 0 12px 12px 0; cursor: pointer;">
                                            <i data-lucide="eye" class="icon-xs"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted small mt-1 d-block"><i data-lucide="help-circle" class="icon-xs me-1"></i> Leave blank to keep current password</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-premium">{{ __('messages.confirm_password') ?? 'Confirm Password' }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text-premium"><i data-lucide="key" class="icon-xs"></i></span>
                                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control input-premium" placeholder="••••••••">
                                        <button class="input-group-text-premium border-start-0 toggle-password" type="button" data-target="password_confirmation" style="border-radius: 0 12px 12px 0; cursor: pointer;">
                                            <i data-lucide="eye" class="icon-xs"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-12" id="area-select" style="display: {{ $user->hasRole('Field Worker') ? 'block' : 'none' }};">
                                    <label class="form-label-premium">{{ __('messages.assigned_areas') ?? 'Assigned Areas' }}</label>
                                    <select class="form-select select2-simple" name="areas[]" id="areas" multiple>
                                        @foreach ($areas as $area)
                                            <option value="{{ $area->id }}" {{ $user->areas->contains($area->id) ? 'selected' : '' }}>
                                                {{ $area->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-update px-5">
                                <i data-lucide="save" class="icon-sm me-2"></i> {{ __('messages.update_user_account') ?? 'Update User Account' }}
                            </button>
                        </div>
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
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Select2 for simple dropdowns
            $('.select2-simple').select2({
                width: '100%'
            });

            // Select2 for Areas (Multiple)
            if ($("#areas").length) {
                $("#areas").select2({
                    placeholder: "{{ __('messages.select_areas') ?? 'Select Areas' }}",
                    width: '100%',
                    allowClear: true
                });
            }

            // Flatpickr for Joining Date
            $(".flatpickr").flatpickr({
                altInput: true,
                dateFormat: "Y-m-d",
                altFormat: "d F, Y"
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

            // Password Toggle logic
            $('.toggle-password').each(function() {
                $(this).on('click', function() {
                    const targetId = $(this).data('target');
                    const targetInput = $('#' + targetId);
                    
                    if (targetInput.attr('type') === 'password') {
                        targetInput.attr('type', 'text');
                        $(this).html('<i data-lucide="eye-off" class="icon-xs"></i>');
                    } else {
                        targetInput.attr('type', 'password');
                        $(this).html('<i data-lucide="eye" class="icon-xs"></i>');
                    }
                    lucide.createIcons();
                });
            });
        });
    </script>
@endpush
