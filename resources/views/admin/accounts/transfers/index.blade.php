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
        }

        .premium-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #a855f7 100%);
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
            background: #f8fafc;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2.5rem;
            border: 1px solid #e2e8f0;
        }

        .form-label-premium {
            font-size: 0.75rem;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 0.5rem;
            letter-spacing: 0.025em;
        }

        .input-premium,
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 12px !important;
            padding: 0.6rem 1rem !important;
            border: 1px solid #e2e8f0 !important;
            background-color: #fff !important;
            transition: all 0.3s !important;
            height: auto !important;
            min-height: 46px !important;
            display: flex !important;
            align-items: center !important;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            padding: 0 !important;
            line-height: normal !important;
            color: #334155 !important;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
            top: 50% !important;
            transform: translateY(-50%) !important;
            right: 1rem !important;
        }

        .input-premium:focus,
        .select2-container--bootstrap-5.select2-container--focus .select2-selection {
            border-color: #7c3aed !important;
            box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.1) !important;
        }

        .table-premium thead th {
            background: #f8fafc;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: #64748b;
            padding: 1rem 1.5rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .table-premium tbody td {
            padding: 1.25rem 1.5rem;
            vertical-align: middle;
            color: #334155;
            font-size: 0.875rem;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s;
            border: none;
        }

        .btn-soft-primary { background: rgba(79, 70, 229, 0.1); color: #4f46e5; }
        .btn-soft-primary:hover { background: #4f46e5; color: #fff; }

        .btn-soft-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .btn-soft-danger:hover { background: #ef4444; color: #fff; }

        .transfer-icon-box {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(124, 58, 237, 0.1);
            color: #7c3aed;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="premium-card mb-4">
                <div class="premium-header">
                    <img src="data:image/svg+xml,%3Csvg width='200' height='200' viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 0h200v200H0z' fill='none'/%3E%3Cpath d='M200 0c0 110.457-89.543 200-200 200h200V0z' fill='white' fill-opacity='.05'/%3E%3C/svg%3E" class="header-shape">
                    <div class="d-flex justify-content-between align-items-center position-relative z-1">
                        <div>
                            <h3 class="fw-bold mb-1 text-white">{{ __('messages.balance_transfer') }}</h3>
                            <p class="mb-0 opacity-75 text-white">{{ __('messages.move_funds_between_accounts') ?? 'Transfer funds securely between different internal accounts' }}</p>
                        </div>
                        <div class="bg-white bg-opacity-25 p-3 rounded-4 backdrop-blur shadow-sm">
                            <i data-lucide="arrow-left-right" class="text-white" style="width: 32px; height: 32px;"></i>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4 p-lg-5">
                    @if (session('success')) <div class="alert alert-success d-flex align-items-center"><i data-lucide="check-circle" class="icon-sm me-2"></i> {{ session('success') }}</div> @endif
                    @if (session('error')) <div class="alert alert-danger d-flex align-items-center"><i data-lucide="alert-circle" class="icon-sm me-2"></i> {{ session('error') }}</div> @endif

                    {{-- Modern Transfer Form --}}
                    <div class="form-panel shadow-sm border-0">
                        <h5 class="fw-bold mb-4 d-flex align-items-center">
                            <i data-lucide="plus-circle" class="icon-sm me-2 text-primary"></i>
                            {{ __('messages.new_transfer') ?? 'Create New Transfer' }}
                        </h5>
                        <form action="{{ route('admin.account-transfers.store') }}" method="POST">
                            @csrf
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label-premium">{{ __('messages.transfer_from') }} <span class="text-danger">*</span></label>
                                    <select name="from_account_id" id="from_account_id" class="form-select select2" required>
                                        <option value="">-- {{ __('messages.select_source_account') }} --</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->name }} ({{ __('messages.balance') }}: {{ number_format($account->balance, 2) }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-premium">{{ __('messages.transfer_to') }} <span class="text-danger">*</span></label>
                                    <select name="to_account_id" id="to_account_id" class="form-select select2" required>
                                        <option value="">-- {{ __('messages.select_destination_account') }} --</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label-premium">{{ __('messages.amount') }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 border-premium"><i data-lucide="dollar-sign" class="icon-xs"></i></span>
                                        <input type="number" step="0.01" name="amount" class="form-control input-premium border-start-0" placeholder="0.00" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label-premium">{{ __('messages.transfer_date') }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 border-premium"><i data-lucide="calendar" class="icon-xs"></i></span>
                                        <input type="text" name="transfer_date" class="form-control input-premium flatpickr border-start-0" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-premium">{{ __('messages.notes') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 border-premium"><i data-lucide="file-text" class="icon-xs"></i></span>
                                        <input type="text" name="notes" class="form-control input-premium border-start-0" placeholder="{{ __('messages.add_optional_notes') ?? 'Add any optional notes here...' }}">
                                    </div>
                                </div>
                                <div class="col-12 mt-4 text-end">
                                    <button type="submit" class="btn btn-primary px-5 py-2 rounded-3 shadow-sm fw-bold">
                                        <i data-lucide="send" class="icon-sm me-2"></i> {{ __('messages.submit_transfer') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Recent Transfers Table --}}
                    <div class="section-header mb-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">{{ __('messages.recent_transfers') }}</h5>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-premium table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.date') }}</th>
                                    <th>{{ __('messages.from_account') }}</th>
                                    <th>{{ __('messages.to_account') }}</th>
                                    <th class="text-end">{{ __('messages.amount') }}</th>
                                    <th class="text-center">{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transfers as $transfer)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i data-lucide="calendar" class="icon-xs text-muted me-2"></i>
                                                {{ $transfer->transfer_date->format('d M, Y') }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="transfer-icon-box bg-soft-danger me-2" style="width: 32px; height: 32px;">
                                                    <i data-lucide="minus" class="icon-xs text-danger"></i>
                                                </div>
                                                {{ $transfer->fromAccount->name }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="transfer-icon-box bg-soft-success me-2" style="width: 32px; height: 32px; background: rgba(16, 185, 129, 0.1); color: #10b981;">
                                                    <i data-lucide="plus" class="icon-xs" style="color: #10b981;"></i>
                                                </div>
                                                {{ $transfer->toAccount->name }}
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold">
                                            {{ number_format($transfer->amount, 2) }}
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="{{ route('admin.account-transfers.edit', $transfer) }}" class="action-btn btn-soft-primary" title="{{ __('messages.edit') }}">
                                                    <i data-lucide="edit-3" class="icon-xs"></i>
                                                </a>
                                                <form id="delete-transfer-{{ $transfer->id }}" action="{{ route('admin.account-transfers.destroy', $transfer->id) }}" method="POST">
                                                    @csrf @method('DELETE')
                                                    <button type="button" class="action-btn btn-soft-danger" onclick="showDeleteConfirm('delete-transfer-{{ $transfer->id }}', '{{ __('messages.are_you_sure') }}', '{{ __('messages.confirm_delete_transfer') }}')" title="{{ __('messages.delete') }}">
                                                        <i data-lucide="trash-2" class="icon-xs"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 opacity-50">
                                            <i data-lucide="layers" class="icon-lg d-block mx-auto mb-3"></i>
                                            {{ __('messages.no_transfers_found') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $transfers->links() }}
                    </div>
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
            // Lucide Icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Select2 initialization
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: "-- {{ __('messages.select') }} --",
                allowClear: true
            });

            // Flatpickr
            $(".flatpickr").flatpickr({
                altInput: true,
                dateFormat: 'Y-m-d',
                altFormat: 'd/m/Y'
            });
        });
    </script>
@endpush
