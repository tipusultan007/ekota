@extends('layout.master')
@section('title', __('messages.activity_logs') . ' | ' . config('app.name'))

@push('plugin-styles')
    <link rel="stylesheet" href="{{ asset('build/plugins/select2/select2.min.css') }}">
    <style>
        .log-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        .log-header {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 12px 12px 0 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .table thead th {
            background-color: #f8fafc;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            font-weight: 700;
            color: #64748b;
            border-top: none;
        }
        .badge-user { background-color: #0284c7; color: #ffffff; font-weight: 600; }
        .badge-action { background-color: #475569; color: #ffffff; font-weight: 600; }
        .badge-model { background-color: #d97706; color: #ffffff; font-weight: 600; }
        .log-details pre {
            background: #f8fafc;
            padding: 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            max-height: 200px;
            overflow-y: auto;
        }
    </style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">{{ __('messages.system_activity_logs') }}</h3>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('messages.activity_logs') }}</li>
        </ol>
    </nav>
</div>

<div class="card log-card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.activity_logs.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label small fw-bold">{{ __('messages.collector') }} ({{ __('messages.name') }})</label>
                <select name="causer_id" class="form-select js-select2">
                    <option value="">{{ __('messages.all_users') }}</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('causer_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">{{ __('messages.action') }}/{{ __('messages.description') }}</label>
                <input type="text" name="description" class="form-control" value="{{ request('description') }}" placeholder="e.g. created, updated">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">{{ __('messages.log_name') }}</label>
                <input type="text" name="log_name" class="form-control" value="{{ request('log_name') }}" placeholder="e.g. default">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i data-lucide="search" class="me-2" style="width: 16px;"></i> {{ __('messages.filter_logs') }}
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card log-card">
    <div class="log-header">
        <div class="d-flex align-items-center">
            <i data-lucide="activity" class="me-2"></i>
            <h5 class="mb-0">{{ __('messages.recent_activities') }}</h5>
        </div>
        <span class="badge bg-light text-dark">{{ $activities->total() }} {{ __('messages.serial') }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">{{ __('messages.time') }}</th>
                        <th>{{ __('messages.collector') }}</th>
                        <th>{{ __('messages.action') }}</th>
                        <th>{{ __('messages.subject') }}</th>
                        <th>{{ __('messages.view_changes') }}</th>
                        <th class="text-end pe-4">{{ __('messages.id') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold">{{ $activity->created_at->format('d M, Y') }}</div>
                                <small class="text-muted">{{ $activity->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                @if($activity->causer)
                                    <span class="badge badge-user p-2">
                                        <i data-lucide="user" class="me-1" style="width: 12px;"></i>
                                        {{ $activity->causer->name }}
                                    </span>
                                @else
                                    <span class="text-muted small">System</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-action p-2 text-capitalize">{{ $activity->description }}</span>
                            </td>
                            <td>
                                @if($activity->subject)
                                    <span class="badge badge-model p-2">
                                        {{ class_basename($activity->subject_type) }}: {{ $activity->subject_id }}
                                    </span>
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if(!empty($activity->changes['attributes']))
                                    <button class="btn btn-xs btn-outline-info" type="button" data-bs-toggle="collapse" data-bs-target="#details-{{ $activity->id }}">
                                        {{ __('messages.view_changes') }}
                                    </button>
                                @else
                                    <span class="text-muted small">No attributes logged</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <small class="text-muted">#{{ $activity->id }}</small>
                            </td>
                        </tr>
                        @if(!empty($activity->changes['attributes']))
                            <tr>
                                <td colspan="6" class="p-0 border-0">
                                    <div class="collapse log-details" id="details-{{ $activity->id }}">
                                        <div class="p-3 bg-light border-bottom">
                                            <div class="row">
                                                @if(isset($activity->changes['old']))
                                                    <div class="col-md-6">
                                                        <h6 class="small fw-bold text-danger">{{ __('messages.old_values') }}</h6>
                                                        <pre class="mb-0"><code>{{ json_encode($activity->changes['old'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                                    </div>
                                                @endif
                                                <div class="col-{{ isset($activity->changes['old']) ? '6' : '12' }}">
                                                    <h6 class="small fw-bold text-success">{{ __('messages.new_values') }}</h6>
                                                    <pre class="mb-0"><code>{{ json_encode($activity->changes['attributes'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i data-lucide="database" class="text-muted mb-2" style="width: 48px; height: 48px;"></i>
                                <p class="text-muted">{{ __('messages.no_activity_logs_found') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white py-3">
        {{ $activities->links() }}
    </div>
</div>
@endsection

@push('custom-scripts')
    <script src="{{ asset('build/plugins/select2/select2.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.js-select2').select2({
                width: '100%'
            });
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
@endpush
