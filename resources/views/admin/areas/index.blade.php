@extends('layout.master')

@push('plugin-styles')
    <style>
        /* Royal Blue to Indigo Gradient Theme */
        .premium-header {
            background: linear-gradient(135deg, #4338ca 0%, #312e81 100%);
            padding: 2.5rem 2rem;
            border-radius: 24px;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(67, 56, 202, 0.1), 0 10px 10px -5px rgba(67, 56, 202, 0.04);
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

        .table-premium {
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        .table-premium thead th {
            background-color: #f8fafc;
            border: none;
            color: #64748b;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 1.25rem 1rem;
        }

        .table-premium tbody tr {
            background-color: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            transition: all 0.2s;
        }

        .table-premium tbody tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            background-color: #f1f5f9;
        }

        .table-premium td {
            padding: 1.25rem 1rem;
            border: none;
            vertical-align: middle;
            color: #475569;
            font-weight: 500;
        }

        /* High Contrast Status Badges */
        .badge-status {
            padding: 0.4rem 0.7rem;
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            color: #fff !important;
        }
        .badge-status-active { background: #059669; }
        .badge-status-inactive { background: #dc2626; }

        .form-label-premium {
            font-weight: 600;
            color: #4b5563;
            font-size: 0.85rem;
            margin-bottom: 0.4rem;
        }

        .input-premium {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            padding: 0.6rem 0.9rem;
            font-size: 0.9rem;
            transition: all 0.2s;
            background-color: #f8fafc;
        }

        .input-premium:focus {
            border-color: #4338ca;
            box-shadow: 0 0 0 3px rgba(67, 56, 202, 0.1);
            background-color: #fff;
        }

        .btn-premium {
            background: linear-gradient(135deg, #4338ca 0%, #312e81 100%);
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            color: white !important;
            transition: all 0.3s;
        }

        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px -3px rgba(67, 56, 202, 0.3);
            color: white!important;
        }

        .btn-soft-primary {
            background: rgba(67, 56, 202, 0.1);
            color: #4338ca;
            border: none;
            transition: all 0.2s;
        }

        .btn-soft-primary:hover {
            background: #4338ca;
            color: #fff;
        }

        .btn-soft-danger {
            background: rgba(225, 29, 72, 0.1);
            color: #e11d48;
            border: none;
            transition: all 0.2s;
        }

        .btn-soft-danger:hover {
            background: #e11d48;
            color: #fff;
        }

        /* Modal Styling */
        .modal-content-premium {
            border-radius: 20px;
            border: none;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .modal-header-premium {
            background: linear-gradient(135deg, #4338ca 0%, #312e81 100%);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 1.5rem 2rem;
            border-bottom: none;
        }

        .modal-body-premium {
            padding: 2rem;
        }

        .icon-xs { width: 16px; height: 16px; }
        .icon-sm { width: 20px; height: 20px; }
    </style>
@endpush

@section('content')
    <div class="premium-header mb-4">
        <div class="row align-items-center">
            <div class="col-md-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2" style="background: transparent; padding: 0;">
                        <li class="breadcrumb-item"><a href="#" class="text-white-50">{{ __('messages.dashboard') ?? 'Dashboard' }}</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">{{ __('messages.area_management') }}</li>
                    </ol>
                </nav>
                <h2 class="fw-bold mb-0">
                    <i data-lucide="map" class="me-2"></i> {{ __('messages.area_management') }}
                </h2>
                <p class="text-white-50 mt-2 mb-0">Manage geographical distribution for field operations in one place</p>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center">
            <i data-lucide="check-circle" class="me-2 icon-sm"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="row">
        {{-- Area Create Form (Sidebar style) --}}
        <div class="col-lg-4 mb-4">
            <div class="card premium-card border-top border-4 border-indigo">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-indigo mb-4 d-flex align-items-center">
                        <i data-lucide="plus-circle" class="icon-sm me-2"></i> {{ __('messages.create_new_area') }}
                    </h5>
                    <form action="{{ route('admin.areas.store') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label-premium">Area Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control input-premium @error('name') is-invalid @enderror" 
                                   placeholder="e.g. Uttara Zone 1" value="{{ old('name') }}" required>
                            @error('name') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label-premium">Area Code</label>
                            <input type="text" name="code" class="form-control input-premium @error('code') is-invalid @enderror" 
                                   placeholder="e.g. UZ1" value="{{ old('code') }}">
                            @error('code') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" class="btn btn-premium w-100">
                            {{ __('messages.create_area') ?? 'Create Area' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Areas List --}}
        <div class="col-lg-8">
            <div class="card premium-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-premium mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>{{ __('messages.area_name') }}</th>
                                    <th>{{ __('messages.area_code') ?? 'Code' }}</th>
                                    <th class="text-center">{{ __('messages.status') }}</th>
                                    <th class="text-center pe-4">{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($areas as $key => $area)
                                    <tr>
                                        <td class="ps-4 text-muted fw-bold">#{{ $areas->firstItem() + $key }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-soft-primary p-2 rounded-3 me-3">
                                                    <i data-lucide="navigation" class="icon-sm text-indigo"></i>
                                                </div>
                                                <span class="fw-bold text-dark">{{ $area->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <code class="px-2 py-1 bg-light rounded text-indigo small">{{ $area->code ?? 'N/A' }}</code>
                                        </td>
                                        <td class="text-center">
                                            @if ($area->is_active)
                                                <span class="badge badge-status badge-status-active">{{ __('messages.active') }}</span>
                                            @else
                                                <span class="badge badge-status badge-status-inactive">{{ __('messages.inactive') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center pe-4">
                                            <div class="d-flex justify-content-center gap-2">
                                                <button type="button" 
                                                   class="btn btn-soft-primary btn-sm rounded-3 px-3 edit-area-btn" 
                                                   title="{{ __('messages.edit') }}"
                                                   data-id="{{ $area->id }}"
                                                   data-name="{{ $area->name }}"
                                                   data-code="{{ $area->code }}"
                                                   data-status="{{ $area->is_active }}">
                                                    <i data-lucide="edit-3" class="icon-xs"></i>
                                                </button>
                                                <form id="delete-area-{{ $area->id }}" action="{{ route('admin.areas.destroy', $area) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-soft-danger btn-sm rounded-3 px-3 delete-area-btn" 
                                                            title="{{ __('messages.delete') }}" data-form-id="delete-area-{{ $area->id }}">
                                                        <i data-lucide="trash-2" class="icon-xs"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="text-muted">
                                                <i data-lucide="map-pin-off" class="icon-lg mb-3"></i>
                                                <p>No areas found. Start by adding one!</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($areas->hasPages())
                        <div class="p-4 border-top">
                            {{ $areas->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Area Modal --}}
    <div class="modal fade" id="editAreaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-premium">
                <div class="modal-header modal-header-premium">
                    <h5 class="modal-title fw-bold">
                        <i data-lucide="edit-3" class="me-2 icon-sm"></i> {{ __('messages.edit_area') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editAreaForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body modal-body-premium">
                        <div class="mb-4">
                            <label class="form-label-premium">{{ __('messages.area_name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_name" class="form-control input-premium" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label-premium">Area Code</label>
                            <input type="text" name="code" id="edit_code" class="form-control input-premium">
                        </div>
                        <div class="mb-4">
                            <label class="form-label-premium">{{ __('messages.status') }}</label>
                            <select name="is_active" id="edit_status" class="form-select input-premium">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">{{ __('messages.cancel') ?? 'Cancel' }}</button>
                        <button type="submit" class="btn btn-premium px-4">
                            <i data-lucide="save" class="icon-xs me-2"></i> {{ __('messages.update') }}
                        </button>
                    </div>
                </form>
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

            // Edit Modal Handler
            $('.edit-area-btn').on('click', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const code = $(this).data('code');
                const status = $(this).data('status');

                $('#edit_name').val(name);
                $('#edit_code').val(code);
                $('#edit_status').val(status);
                
                // Update form action URL
                const updateUrl = "{{ route('admin.areas.update', ':id') }}".replace(':id', id);
                $('#editAreaForm').attr('action', updateUrl);

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('editAreaModal'));
                modal.show();
                
                // Refresh icons in modal
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            });

            // Delete Confirmation
            $('.delete-area-btn').on('click', function(e) {
                e.preventDefault();
                var btn = $(this);
                var formId = btn.data('form-id');
                var form = $('#' + formId);
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "Deleting this area might affect associated members and workers!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#4338ca',
                        cancelButtonColor: '#e11d48',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            btn.prop('disabled', true);
                            btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
                            form.submit();
                        }
                    });
                } else if (confirm('Are you sure you want to delete this area?')) {
                    form.submit();
                }
            });
        });
    </script>
@endpush
