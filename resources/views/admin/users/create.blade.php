@extends('layout.master')

@push('plugin-styles')
    <link rel="stylesheet" href="{{ asset('build/plugins/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}">
    <style>
        /* Premium Design System */
        .premium-header {
            background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);
            padding: 2.5rem 2rem;
            border-radius: 24px;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(6, 182, 212, 0.1), 0 10px 10px -5px rgba(6, 182, 212, 0.04);
        }

        .premium-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            filter: blur(60px);
        }

        .premium-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            background: #ffffff;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Form Styling */
        .form-section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0e7490;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #ecfeff;
        }

        .form-label-premium {
            font-weight: 600;
            color: #4b5563;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }

        .input-premium {
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            padding: 0.6rem 1rem;
            font-size: 0.95rem;
            transition: all 0.2s;
            background-color: #f9fafb;
        }

        .input-premium:focus {
            border-color: #06b6d4;
            box-shadow: 0 0 0 4px rgba(6, 182, 212, 0.1);
            background-color: #fff;
        }

        .input-group-text-premium {
            background-color: #ecfeff;
            border: 1px solid #e5e7eb;
            color: #0891b2;
            padding: 0.6rem 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .input-group > .input-group-text-premium:first-child {
            border-radius: 12px 0 0 12px;
            border-right: none;
        }

        .input-group > .input-group-text-premium:last-child {
            border-radius: 0 12px 12px 0;
            border-left: none;
        }

        .input-group {
            flex-wrap: nowrap !important;
        }

        .input-group > .input-premium {
            flex: 1 1 auto;
            width: 1%;
            border-radius: 12px;
        }

        .input-group > .input-group-text-premium + .input-premium {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        .input-group > .input-premium:not(:last-child) {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        /* Select2 Premium Override */
        .select2-container--default .select2-selection--single,
        .select2-container--default .select2-selection--multiple {
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            background-color: #f9fafb !important;
            min-height: 46px;
            padding: 5px;
            display: flex;
            align-items: center;
        }

        .input-group > .select2-container--default {
            flex: 1 1 auto;
            width: 1%;
        }

        .input-group > .input-group-text-premium + .select2-container--default .select2-selection--single {
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
        }

        /* Buttons */
        .btn-create {
            background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);
            border: none;
            padding: 0.8rem 2.5rem;
            border-radius: 14px;
            font-weight: 700;
            color: white;
            box-shadow: 0 10px 15px -3px rgba(6, 182, 212, 0.3);
            transition: all 0.3s;
        }

        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 20px -5px rgba(6, 182, 212, 0.4);
            color: white;
        }

        /* Icon size */
        .icon-xs { width: 16px; height: 16px; }
    </style>
@endpush

@section('content')
    <div class="premium-header mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2" style="background: transparent; padding: 0;">
                        <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}" class="text-white-50">{{ __('messages.user_management') }}</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">{{ __('messages.create_new_user') }}</li>
                    </ol>
                </nav>
                <h2 class="fw-bold mb-0">
                    <i data-lucide="user-plus" class="me-2"></i> {{ __('messages.create_new_user') }}
                </h2>
                <p class="text-white-50 mt-2 mb-0">Onboard a new administrator or field worker</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="{{ route('admin.users.index') }}" class="btn btn-light rounded-pill px-4 fw-bold">
                    <i data-lucide="arrow-left" class="icon-xs me-1"></i> {{ __('messages.back_to_list') }}
                </a>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row">
            {{-- Left Column: Personal and Employment Information --}}
            <div class="col-lg-8">
                {{-- Panel 1: Personal Information --}}
                <div class="card premium-card mb-4 mt-0">
                    <div class="card-body p-4">
                        <div class="form-section-title">
                            <i data-lucide="user"></i> {{ __('messages.personal_info') }}
                        </div>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label-premium">{{ __('messages.full_name') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text-premium"><i data-lucide="user" class="icon-xs"></i></span>
                                    <input type="text" name="name" class="form-control input-premium @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-premium">{{ __('messages.phone_number') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text-premium"><i data-lucide="phone" class="icon-xs"></i></span>
                                    <input type="text" name="phone" class="form-control input-premium @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-premium">{{ __('messages.nid_number') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text-premium"><i data-lucide="credit-card" class="icon-xs"></i></span>
                                    <input type="text" name="nid_no" class="form-control input-premium @error('nid_no') is-invalid @enderror" value="{{ old('nid_no') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-premium">{{ __('messages.profile_photo') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text-premium"><i data-lucide="camera" class="icon-xs"></i></span>
                                    <input type="file" name="photo" class="form-control input-premium @error('photo') is-invalid @enderror">
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label-premium">{{ __('messages.address') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text-premium"><i data-lucide="map-pin" class="icon-xs"></i></span>
                                    <textarea name="address" class="form-control input-premium" rows="2">{{ old('address') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Panel 2: Employment Details --}}
                <div class="card premium-card border-top border-5 border-info">
                    <div class="card-body p-4">
                        <div class="form-section-title">
                            <i data-lucide="briefcase"></i> {{ __('messages.employment_details') ?? 'Employment Details' }}
                        </div>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label-premium">{{ __('messages.joining_date') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text-premium"><i data-lucide="calendar" class="icon-xs"></i></span>
                                    <input type="text" name="joining_date" class="form-control input-premium flatpickr @error('joining_date') is-invalid @enderror" value="{{ old('joining_date', date('Y-m-d')) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-premium">{{ __('messages.monthly_salary') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text-premium"><i data-lucide="banknote" class="icon-xs"></i></span>
                                    <input type="number" step="0.01" name="salary" class="form-control input-premium @error('salary') is-invalid @enderror" value="{{ old('salary', '0.00') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-premium">{{ __('messages.other_documents') }} (Max 2MB)</label>
                                <div class="input-group">
                                    <span class="input-group-text-premium"><i data-lucide="file-text" class="icon-xs"></i></span>
                                    <input type="file" name="documents[]" class="form-control input-premium" multiple>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-premium">{{ __('messages.status') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text-premium"><i data-lucide="activity" class="icon-xs"></i></span>
                                    <select name="status" class="form-select input-premium">
                                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Account and Access Information --}}
            <div class="col-lg-4">
                <div class="card premium-card h-100">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="form-section-title">
                            <i data-lucide="shield-check"></i> {{ __('messages.account_information') }}
                        </div>
                        <div class="row g-4 flex-grow-1">
                            <div class="col-12">
                                <label class="form-label-premium">{{ __('messages.role') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text-premium"><i data-lucide="users" class="icon-xs"></i></span>
                                    <select name="role" id="userRole" class="form-select select2-simple" required>
                                        <option value="" disabled selected>{{ __('messages.select_role') }}</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-12" id="area-select-wrapper" style="display: {{ old('role') == 'Field Worker' ? 'block' : 'none' }};">
                                <label class="form-label-premium">{{ __('messages.assign_areas') }}</label>
                                <select name="areas[]" id="areas" class="form-select select2-simple" multiple>
                                    @foreach ($areas as $area)
                                        <option value="{{ $area->id }}" {{ in_array($area->id, old('areas', [])) ? 'selected' : '' }}>{{ $area->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label-premium">{{ __('messages.password') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text-premium"><i data-lucide="lock" class="icon-xs"></i></span>
                                    <input type="password" name="password" id="password" class="form-control input-premium @error('password') is-invalid @enderror" required>
                                    <button class="input-group-text-premium border-start-0 toggle-password" type="button" data-target="password" style="border-radius: 0 12px 12px 0; cursor: pointer;">
                                        <i data-lucide="eye" class="icon-xs"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label-premium">{{ __('messages.confirm_password') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text-premium"><i data-lucide="check-square" class="icon-xs"></i></span>
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control input-premium" required>
                                    <button class="input-group-text-premium border-start-0 toggle-password" type="button" data-target="password_confirmation" style="border-radius: 0 12px 12px 0; cursor: pointer;">
                                        <i data-lucide="eye" class="icon-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5">
                            <button type="submit" class="btn btn-create w-100 py-3 mt-auto">
                                <i data-lucide="save" class="icon-xs me-2"></i> {{ __('messages.create_user') }}
                            </button>
                            <p class="text-center text-muted small mt-3">All starred (*) fields are mandatory.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('plugin-scripts')
    <script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('build/plugins/select2/select2.min.js') }}"></script>
    <script src="{{ asset('build/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Select2 Initializations
            $('.select2-simple').select2({
                width: '100%'
            });

            if ($("#areas").length) {
                $("#areas").select2({
                    placeholder: "{{ __('messages.assign_areas') }}",
                    width: '100%'
                });
            }

            // Flatpickr Initializations
            $(".flatpickr").flatpickr({
                altInput: true,
                dateFormat: "Y-m-d",
                altFormat: "d/m/Y",
                allowInput: true
            });

            // Role based logic
            $('#userRole').on('change', function () {
                var areaWrapper = $('#area-select-wrapper');
                if (this.value === 'Field Worker') {
                    areaWrapper.slideDown();
                } else {
                    areaWrapper.slideUp();
                }
            });

            // Password Toggle logic
            $('.toggle-password').each(function() {
                $(this).on('click', function() {
                    const targetId = $(this).data('target');
                    const targetInput = $('#' + targetId);
                    const icon = $(this).find('i');
                    
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

            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
@endpush
