@extends('layout.master')

@push('plugin-styles')
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

        .table-premium thead th {
            background: #ecfeff;
            color: #0e7490;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 1.25rem 1.5rem;
            border: none;
        }

        .table-premium tbody td {
            padding: 1.25rem 1.5rem;
            vertical-align: middle;
            color: #334155;
            font-weight: 500;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-premium tbody tr:hover {
            background-color: #f0f9ff;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        /* Deep Status Badges */
        .badge-status {
            padding: 0.5rem 0.8rem;
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            color: #fff !important;
            border: none;
        }
        .badge-status-active { background: #059669; }
        .badge-status-inactive { background: #d97706; }
        .badge-status-terminated { background: #dc2626; }
        
        /* Area & Role Badges */
        .badge-area {
            background: #ecfeff;
            color: #0891b2 !important;
            border: 1px solid #bae6fd;
            font-weight: 600;
            font-size: 0.7rem;
        }

        .role-badge {
            background: #f0f9ff;
            color: #0e7490 !important;
            font-weight: 700;
            border-radius: 6px;
            padding: 0.25rem 0.6rem;
            font-size: 0.7rem;
            border: 1px solid #bae6fd;
        }

        .btn-soft-primary { background: rgba(37, 99, 235, 0.1); color: #2563eb; }
        .btn-soft-primary:hover { background: #2563eb; color: #fff; }
        
        .btn-soft-danger { background: rgba(225, 29, 72, 0.1); color: #e11d48; }
        .btn-soft-danger:hover { background: #e11d48; color: #fff; }

        .btn-header {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            backdrop-filter: blur(4px);
            transition: all 0.3s;
        }

        .btn-header:hover {
            background: white;
            color: #0891b2;
            transform: translateY(-2px);
        }

        .pagination-premium .page-link {
            border-radius: 8px;
            margin: 0 3px;
            color: #0891b2;
            border: 1px solid #a5f3fc;
        }

        .pagination-premium .page-item.active .page-link {
            background: #0891b2;
            border-color: #0891b2;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="premium-card">
                <div class="premium-header">
                    <img src="data:image/svg+xml,%3Csvg width='200' height='200' viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 0h200v200H0z' fill='none'/%3E%3Cpath d='M200 0c0 110.457-89.543 200-200 200h200V0z' fill='white' fill-opacity='.05'/%3E%3C/svg%3E" class="header-shape">
                    <div class="d-flex justify-content-between align-items-center position-relative z-1">
                        <div>
                            <h3 class="fw-bold mb-1 text-white">{{ __('messages.user_management') ?? 'User Management' }}</h3>
                            <p class="mb-0 opacity-75 text-white">{{ __('messages.manage_system_users_roles_and_permissions') ?? 'Manage system users, their roles, permissions and account status.' }}</p>
                        </div>
                        <a href="{{ route('admin.users.create') }}" class="btn btn-header">
                            <i data-lucide="user-plus" class="icon-sm me-2"></i> {{ __('messages.add_new_user') ?? 'Add New User' }}
                        </a>
                    </div>
                </div>

                <div class="card-body p-4 p-lg-5">
                    @if (session('success'))
                        <div class="alert alert-success d-flex align-items-center rounded-3 border-0 mb-4" style="background: #ecfeff; color: #0891b2;">
                            <i data-lucide="check-circle" class="icon-sm me-2"></i> {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger d-flex align-items-center rounded-3 border-0 mb-4" style="background: #fef2f2; color: #dc2626;">
                            <i data-lucide="alert-circle" class="icon-sm me-2"></i> {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-premium table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ Lang::has('messages.name') ? __('messages.name') : 'User' }}</th>
                                    <th>{{ Lang::has('messages.contact_info') ? __('messages.contact_info') : 'Contact' }}</th>
                                    <th>{{ Lang::has('messages.roles_areas') ? __('messages.roles_areas') : 'Roles & Areas' }}</th>
                                    <th class="text-center">{{ Lang::has('messages.status') ? __('messages.status') : 'Status' }}</th>
                                    <th class="text-center">{{ Lang::has('messages.actions') ? __('messages.actions') : 'Actions' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $key => $user)
                                    <tr>
                                        <td class="text-muted small">#{{ $users->firstItem() + $key }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $user->getFirstMediaUrl('user_photo', 'thumb') ?: 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=0891b2&color=fff' }}"
                                                     alt="Photo" class="user-avatar me-3">
                                                <div>
                                                    <span class="fw-bold d-block text-dark">{{ $user->name }}</span>
                                                    <span class="badge role-badge mt-1">
                                                        {{ $user->roles->pluck('name')->implode(', ') ?: 'No Role' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="small d-flex align-items-center mb-1">
                                                    <i data-lucide="mail" class="icon-xs text-muted me-1"></i> {{ $user->email }}
                                                </span>
                                                @if($user->phone)
                                                    <span class="small d-flex align-items-center text-muted">
                                                        <i data-lucide="phone" class="icon-xs text-muted me-1"></i> {{ $user->phone }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @forelse ($user->areas as $area)
                                                    <span class="badge rounded-pill badge-area px-2 py-1">
                                                        <i data-lucide="map-pin" class="icon-xs me-1"></i> {{ $area->name }}
                                                    </span>
                                                @empty
                                                    <span class="text-muted small italic" style="font-size: 0.7rem;">N/A</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $statusKey = $user->status ?? 'active';
                                                $statusClass = "badge-status-{$statusKey}";
                                                $statusLabel = [
                                                    'active' => __('messages.active') ?? 'Active',
                                                    'inactive' => __('messages.inactive') ?? 'Inactive',
                                                    'terminated' => __('messages.terminated') ?? 'Terminated',
                                                ][$statusKey] ?? ucfirst($statusKey);
                                            @endphp
                                            <span class="badge badge-status {{ $statusClass }}">
                                                {{ $statusLabel }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-soft-primary btn-sm rounded-3 px-3" title="{{ __('messages.edit') }}">
                                                    <i data-lucide="edit-3" class="icon-xs"></i>
                                                </a>

                                                {{-- অ্যাডমিন নিজেকে ডিলিট করতে পারবে না --}}
                                                @if(Auth::id() != $user->id)
                                                    <form id="delete-user-{{ $user->id }}" action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-soft-danger btn-sm rounded-3 px-3 delete-user-btn" 
                                                                title="{{ __('messages.delete') }}" data-form-id="delete-user-{{ $user->id }}">
                                                            <i data-lucide="trash-2" class="icon-xs"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="p-5 text-center text-muted">
                                            <i data-lucide="users" class="d-block mx-auto mb-3 opacity-25" style="width: 48px; height: 48px;"></i>
                                            <p class="mb-0">{{ __('messages.no_users_found') ?? 'No users found in the system.' }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 pagination-premium">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('plugin-scripts')
    <script>
        $(document).ready(function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            $('.delete-user-btn').on('click', function(e) {
                e.preventDefault();
                var btn = $(this);
                var formId = btn.data('form-id');
                var form = $('#' + formId);
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This user and their associated data will be permanently deleted!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#0891b2',
                        cancelButtonColor: '#e11d48',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Manually trigger the "processing" state on the button
                            btn.prop('disabled', true);
                            const originalHtml = btn.html();
                            btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
                            
                            form.submit();
                        }
                    });
                } else if (confirm('Are you sure you want to delete this user?')) {
                    form.submit();
                }
            });
        });
    </script>
@endpush
