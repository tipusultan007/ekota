@extends('layout.master')
@section('title', __('messages.all_members') . ' | ' . config('app.name'))

@push('plugin-styles')
    <link href="{{ asset('build/plugins/select2/select2.min.css') }}" rel="stylesheet" />
    <style>
        .premium-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            background: #fff;
            margin-bottom: 2rem;
            position: relative;
        }

        .premium-header {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 50%, #d946ef 100%);
            padding: 1.75rem 2.25rem;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
        }

        .premium-header::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at top right, rgba(255,255,255,0.1) 0%, transparent 60%);
            pointer-events: none;
        }

        .premium-header h4 {
            text-shadow: 0 2px 4px rgba(0,0,0,0.15);
            letter-spacing: -0.01em;
        }

        .premium-header i {
            width: 28px;
            height: 28px;
            margin-right: 14px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }

        .filter-section {
            background: #f8fafc;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid #e2e8f0;
        }

        .table-premium {
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        .table-premium thead th {
            background: #f1f5f9;
            border: none;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: #64748b;
            padding: 1rem;
        }

        .table-premium tbody tr {
            background: #fff;
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .table-premium tbody tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            background: #fdfdfd;
        }

        .table-premium td {
            padding: 1.2rem 1rem;
            vertical-align: middle;
            border-top: 1px solid #f1f5f9;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-premium td:first-child {
            border-left: 1px solid #f1f5f9;
            border-top-left-radius: 10px;
            border-bottom-left-radius: 10px;
        }

        .table-premium td:last-child {
            border-right: 1px solid #f1f5f9;
            border-top-right-radius: 10px;
            border-bottom-right-radius: 10px;
        }

        .member-avatar {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, #e0e7ff 0%, #ede9fe 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4f46e5;
            font-weight: 600;
            margin-right: 12px;
        }

        .badge-status {
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .btn-action {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            margin-right: 4px;
            transition: all 0.2s;
        }

        .btn-action:hover {
            transform: scale(1.1);
        }

        .btn-premium-gradient {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white !important;
            border: none;
            box-shadow: 0 4px 15px rgba(217, 119, 6, 0.4);
            transition: all 0.3s ease;
        }

        .btn-premium-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(217, 119, 6, 0.6);
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        }

        .bg-soft-primary { background-color: rgba(79, 70, 229, 0.1); }
        .bg-soft-success { background-color: rgba(16, 185, 129, 0.1); }
        .bg-soft-info { background-color: rgba(6, 182, 212, 0.1); }
        .bg-soft-danger { background-color: rgba(239, 68, 68, 0.1); }
        .bg-soft-secondary { background-color: rgba(100, 116, 139, 0.1); }

        .text-primary { color: #4f46e5 !important; }
        .text-success { color: #10b981 !important; }
        .text-info { color: #06b6d4 !important; }
        .text-danger { color: #ef4444 !important; }
        .text-secondary { color: #64748b !important; }
        
        /* Fix for dropdown being hidden behind other rows */
        .table-premium tr:has(.show) {
            position: relative;
            z-index: 5;
        }

        .table-premium tr:hover {
            z-index: 2;
        }

        .select2-container--default .select2-selection--single {
            border-color: #e2e8f0;
            height: 38px;
            border-radius: 8px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
            padding-left: 12px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
    </style>
@endpush

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('messages.member_management') }}</li>
        </ol>
    </nav>

    <div class="premium-card">
        <div class="premium-header">
            <div class="d-flex align-items-center">
                <i data-lucide="users"></i>
                <h4 class="mb-0 fw-bold">{{ __('messages.all_members') }} <span class="badge bg-light text-primary ms-2 fs-6">{{ $totalMembers }}</span></h4>
            </div>
            <div>
                <button type="button" id="bulkDeleteBtn" class="btn btn-danger btn-sm fw-bold px-3 py-2 me-2 d-none">
                    <i data-lucide="trash-2" class="icon-sm me-1"></i> <span id="bulkDeleteText">{{ __('messages.delete_selected') }}</span>
                </button>
                <a href="{{ route('members.import') }}" class="btn btn-outline-light btn-sm fw-bold px-3 py-2 me-2">
                    <i data-lucide="upload" class="icon-sm me-1"></i> {{ __('messages.import') ?? 'Import' }}
                </a>
                <a href="{{ route('members.create_with_account') }}" class="btn btn-premium-gradient btn-sm fw-bold px-3 py-2">
                    <i data-lucide="plus-circle" class="icon-sm me-1"></i> {{ __('messages.add_member_and_account') }}
                </a>
            </div>
        </div>

        <div class="card-body p-4">
            @if (session('success'))
                <div class="alert alert-success border-0 shadow-sm mb-4" style="border-left: 4px solid #10b981 !important; background: #ecfdf5; color: #065f46;">
                    <div class="d-flex align-items-center">
                        <i data-lucide="check-circle" class="me-2" style="width: 20px; height: 20px;"></i>
                        <span class="fw-bold">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session('skippedLoans'))
                <div class="alert alert-warning border-0 shadow-sm mb-4" style="border-left: 4px solid #f59e0b !important; background: #fffbeb; color: #92400e;">
                    <div class="d-flex align-items-center mb-2">
                        <i data-lucide="alert-triangle" class="me-2" style="width: 20px; height: 20px;"></i>
                        <span class="fw-bold">The following loans were skipped (already exist on the same date):</span>
                    </div>
                    <ul class="mb-0 mt-2">
                        @foreach(session('skippedLoans') as $skipped)
                            <li>{{ $skipped['name'] }} (Acc: {{ $skipped['account_no'] }}) - {{ number_format($skipped['loan_amount'], 2) }} on {{ $skipped['date'] }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ======== ফিল্টার ফর্ম ======== --}}
            <div class="filter-section">
                <form action="{{ route('members.index') }}" method="GET">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted text-uppercase tracking-wider mb-2" style="font-size: 0.7rem;">{{ __('messages.account_no') }}</label>
                            <select name="account_no" id="accountNoFilter" class="form-select select2-ajax">
                                @if(request('account_no'))
                                    <option value="{{ request('account_no') }}" selected>{{ request('account_no') }}</option>
                                @else
                                    <option value="">{{ __('messages.search_by_account_no') ?? 'Search Account No...' }}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted text-uppercase tracking-wider mb-2" style="font-size: 0.7rem;">{{ __('messages.name') }}</label>
                            <input type="text" name="name" class="form-control" value="{{ request('name') }}" placeholder="{{ __('messages.search_by_name') }}...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase tracking-wider mb-2" style="font-size: 0.7rem;">{{ __('messages.mobile_no') }}</label>
                            <input type="text" name="mobile_no" class="form-control" value="{{ request('mobile_no') }}" placeholder="{{ __('messages.search_by_mobile') }}...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase tracking-wider mb-2" style="font-size: 0.7rem;">{{ __('messages.area') }}</label>
                            <select name="area_id" class="form-select">
                                <option value="">{{ __('messages.all_areas') }}</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase tracking-wider mb-2" style="font-size: 0.7rem;">{{ __('messages.status') }}</label>
                            <select name="status" class="form-select">
                                <option value="">{{ __('messages.all_statuses') }}</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>{{ __('messages.suspended') }}</option>
                            </select>
                        </div>
                        <div class="col-md-12 d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-primary px-4 me-2">
                                <i data-lucide="filter" class="icon-sm me-1"></i> {{ __('messages.filter') }}
                            </button>
                            <a href="{{ route('members.index') }}" class="btn btn-secondary px-4">
                                <i data-lucide="refresh-cw" class="icon-sm me-1"></i> {{ __('messages.reset') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-premium">
                    <thead>
                        <tr>
                            <th style="width: 40px;">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="selectAllMembers">
                                </div>
                            </th>
                            <th>{{ __('messages.member') }}</th>
                            <th>{{ __('messages.account_no') }}</th>
                            <th>{{ __('messages.mobile_no') }}</th>
                            <th>{{ __('messages.area') }}</th>
                            <th>{{ __('messages.joining_date') }}</th>
                            <th>{{ __('messages.status') }}</th>
                            <th>{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($members as $member)
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input member-checkbox" value="{{ $member->id }}">
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="member-avatar">
                                            {{ substr($member->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $member->name }}</div>
                                            
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-primary fw-bold" style="font-size: 0.9rem; padding: 6px 10px;">
                                        {{ $member->account_no }}
                                    </span>
                                </td>
                                <td>{{ $member->mobile_no }}</td>
                                <td>
                                    <span class="text-muted"><i data-lucide="map-pin" class="icon-xs me-1"></i>{{ $member->area->name ?? 'N/A' }}</span>
                                </td>
                                <td>{{ $member->joining_date->format('d M, Y') }}</td>
                                <td>
                                    @php
                                        $statusClass = 'bg-soft-danger text-danger';
                                        if ($member->status == 'active') $statusClass = 'bg-soft-success text-success';
                                        elseif ($member->status == 'inactive') $statusClass = 'bg-soft-secondary text-secondary';
                                    @endphp
                                    <div class="dropdown status-dropdown" id="status-container-{{ $member->id }}">
                                        <span class="badge-status {{ $statusClass }} dropdown-toggle" role="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false" style="cursor: pointer;">
                                            {{ __('messages.' . strtolower($member->status)) }}
                                        </span>
                                        <ul class="dropdown-menu shadow-sm border-0 rounded-3">
                                            <li><a class="dropdown-item change-status-btn {{ $member->status == 'active' ? 'active' : '' }}" href="javascript:void(0)" data-id="{{ $member->id }}" data-status="active">{{ __('messages.active') }}</a></li>
                                            <li><a class="dropdown-item change-status-btn {{ $member->status == 'inactive' ? 'active' : '' }}" href="javascript:void(0)" data-id="{{ $member->id }}" data-status="inactive">{{ __('messages.inactive') }}</a></li>
                                            <li><a class="dropdown-item change-status-btn {{ $member->status == 'suspended' ? 'active' : '' }}" href="javascript:void(0)" data-id="{{ $member->id }}" data-status="suspended">{{ __('messages.suspended') }}</a></li>
                                        </ul>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex">
                                        <a href="{{ route('members.show', $member->id) }}" class="btn-action bg-soft-info text-info" title="{{ __('messages.view') }}">
                                            <i data-lucide="eye" style="width: 16px;"></i>
                                        </a>
                                        <a href="{{ route('members.edit', $member->id) }}" class="btn-action bg-soft-primary text-primary" title="{{ __('messages.edit') }}">
                                            <i data-lucide="edit-3" style="width: 16px;"></i>
                                        </a>
                                        @role('Admin')
                                        <form action="{{ route('members.destroy', $member->id) }}" method="POST" id="delete-member-{{ $member->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="showDeleteConfirm('delete-member-{{ $member->id }}')" class="btn-action bg-soft-danger text-danger" title="{{ __('messages.delete') }}">
                                                <i data-lucide="trash-2" style="width: 16px;"></i>
                                            </button>
                                        </form>
                                        @endrole
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i data-lucide="users" class="mb-3 opacity-25" style="width: 48px; height: 48px;"></i>
                                    <p class="text-muted fw-medium">{{ __('messages.no_members_found') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-4">
                    {{ $members->appends(request()->input())->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-scripts')
    <script src="{{ asset('build/plugins/select2/select2.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            $('.select2-ajax').select2({
                placeholder: "{{ __('messages.search_by_account_no') ?? 'Search Account No...' }}",
                allowClear: true,
                ajax: {
                    url: "{{ route('members.search') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            term: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    id: item.account_no,
                                    text: item.account_no + ' - ' + item.name
                                };
                            })
                        };
                    },
                    cache: true
                },
                minimumInputLength: 1
            });

            // --- Bulk Delete Logic ---
            const selectAll = $('#selectAllMembers');
            const memberCheckboxes = $('.member-checkbox');
            const bulkDeleteBtn = $('#bulkDeleteBtn');

            function updateBulkDeleteBtn() {
                const checkedCount = $('.member-checkbox:checked').length;
                if (checkedCount > 0) {
                    bulkDeleteBtn.removeClass('d-none');
                    $('#bulkDeleteText').text(`{{ __('messages.delete_selected') }} (${checkedCount})`);
                } else {
                    bulkDeleteBtn.addClass('d-none');
                }
            }

            selectAll.on('change', function() {
                memberCheckboxes.prop('checked', this.checked);
                updateBulkDeleteBtn();
            });

            memberCheckboxes.on('change', function() {
                const allChecked = memberCheckboxes.length === $('.member-checkbox:checked').length;
                selectAll.prop('checked', allChecked);
                updateBulkDeleteBtn();
            });

            bulkDeleteBtn.on('click', function() {
                const selectedIds = $('.member-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();

                if (selectedIds.length === 0) return;

                let confirmTitle = "{{ __('messages.bulk_delete_title') }}";
                let confirmText = "{{ __('messages.bulk_delete_text', ['count' => ':count']) }}";
                confirmText = confirmText.replace(':count', selectedIds.length);

                Swal.fire({
                    title: confirmTitle,
                    text: confirmText,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: "{{ __('messages.bulk_delete_confirm') }}",
                    cancelButtonText: "{{ __('messages.cancel') }}"
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: "{{ __('messages.processing') }}",
                            text: "{{ __('messages.please_wait') ?? 'Please wait...' }}",
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: "{{ route('members.bulk_destroy') }}",
                            method: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                ids: selectedIds
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: "{{ __('messages.bulk_delete_success_title') }}",
                                        text: response.message,
                                        icon: 'success'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire("{{ __('messages.error') }}", response.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                let errorMsg = "{{ __('messages.error_occurred') ?? 'An error occurred.' }}";
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMsg = xhr.responseJSON.message;
                                }
                                Swal.fire("{{ __('messages.error') }}", errorMsg, 'error');
                            }
                        });
                    }
                });
            });

            // --- Change Status Logic ---
            $(document).on('click', '.change-status-btn', function() {
                const memberId = $(this).data('id');
                const newStatus = $(this).data('status');
                const $container = $(`#status-container-${memberId}`);
                const $badge = $container.find('.badge-status');

                // Don't do anything if the status is the same
                if ($(this).hasClass('active')) return;

                Swal.fire({
                    title: "{{ __('messages.confirm_status_change') ?? 'Confirm Status Change' }}",
                    text: "{{ __('messages.confirm_status_change_text') ?? 'Are you sure you want to change this member\'s status?' }}",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: "{{ __('messages.yes_change_it') ?? 'Yes, change it' }}",
                    cancelButtonText: "{{ __('messages.cancel') }}"
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: "{{ __('messages.updating') ?? 'Updating...' }}",
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: `{{ url('members') }}/${memberId}/update-status`,
                            method: "PATCH",
                            data: {
                                _token: "{{ csrf_token() }}",
                                status: newStatus
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: "{{ __('messages.success') }}",
                                        text: response.message,
                                        icon: 'success',
                                        timer: 1500,
                                        showConfirmButton: false
                                    });

                                    // Update UI
                                    let statusClass = 'bg-soft-danger text-danger';
                                    if (newStatus === 'active') statusClass = 'bg-soft-success text-success';
                                    else if (newStatus === 'inactive') statusClass = 'bg-soft-secondary text-secondary';

                                    $badge.removeClass('bg-soft-success text-success bg-soft-secondary text-secondary bg-soft-danger text-danger')
                                          .addClass(statusClass)
                                          .text(response.status_label);
                                    
                                    // Update dropdown active state
                                    $container.find('.change-status-btn').removeClass('active');
                                    $container.find(`.change-status-btn[data-status="${newStatus}"]`).addClass('active');
                                }
                            },
                            error: function(xhr) {
                                let errorMsg = "{{ __('messages.error_occurred') ?? 'An error occurred.' }}";
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMsg = xhr.responseJSON.message;
                                }
                                Swal.fire("{{ __('messages.error') }}", errorMsg, 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
