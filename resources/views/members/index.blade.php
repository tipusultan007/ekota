@extends('layout.master')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('messages.member_management') }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="card-title">{{ __('messages.all_members') }}</h6>
                        <div>
                            <a href="{{ route('members.create') }}"
                               class="btn btn-secondary btn-sm">{{ __('messages.add_member_only') }}</a>
                            <a href="{{ route('members.create_with_account') }}" class="btn btn-primary btn-sm">
                                <i data-lucide="plus"
                                   class="icon-sm me-2"></i> {{ __('messages.add_member_and_account') }}
                            </a>
                        </div>
                    </div>
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    {{-- ======== ফিল্টার ফর্ম ======== --}}
                    <form action="{{ route('members.index') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3 mb-2"><input type="text" name="name" class="form-control"
                                                              value="{{ request('name') }}"
                                                              placeholder="{{ __('search_by_name') }}..."></div>
                            <div class="col-md-3 mb-2"><input type="text" name="mobile_no" class="form-control"
                                                              value="{{ request('mobile_no') }}"
                                                              placeholder="{{ __('search_by_mobile') }}..."></div>

                            @role('Admin')
                            <div class="col-md-2 mb-2">
                                <select name="area_id" class="form-select">
                                    <option value="">{{ __('all_areas') }}</option>
                                    @foreach($areas as $area)
                                        <option
                                            value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endrole

                            <div class="col-md-2 mb-2">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                                        Active
                                    </option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>
                                        Inactive
                                    </option>
                                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>
                                        Suspended
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <div class="d-flex">
                                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                                    <a href="{{ route('members.index') }}" class="btn btn-secondary">Reset</a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>{{ __('messages.serial') }}</th>
                                <th>{{ __('messages.photo') }}</th>
                                <th>{{ __('messages.name') }}</th>
                                <th>{{ __('messages.mobile_no') }}</th>
                                <th>{{ __('messages.area') }}</th>
                                <th>{{ __('messages.joining_date') }}</th>
                                <th>{{ __('messages.status') }}</th>
                                <th>{{ __('messages.actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($members as $key => $member)
                                <tr>
                                    <td>{{ $members->firstItem() + $key }}</td>
                                    <td>
                                        <img
                                            src="{{ $member->getFirstMediaUrl('member_photo', 'thumb') ?: 'https://placehold.co/40x40' }}"
                                            alt="Photo" class="rounded-circle" width="40" height="40"
                                            style="object-fit: cover;">
                                    </td>
                                    <td>{{ $member->name }}</td>
                                    <td>{{ $member->mobile_no }}</td>
                                    <td>{{ $member->area->name ?? 'N/A' }}</td>
                                    <td>{{ $member->joining_date->format('d M, Y') }}</td>
                                    <td>
                                        @php
                                            $statusClass = 'danger';
                                            if ($member->status == 'active') $statusClass = 'success';
                                            elseif ($member->status == 'inactive') $statusClass = 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $statusClass }}">
                                            {{-- স্ট্যাটাসের টেক্সট অনুবাদ করার জন্য --}}
                                            {{ __( 'messages.' . strtolower($member->status) ) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            <a href="{{ route('members.show', $member->id) }}"
                                               class="btn btn-info btn-xs me-1">{{ __('messages.view') }}</a>
                                            <a href="{{ route('members.edit', $member->id) }}"
                                               class="btn btn-primary btn-xs me-1">{{ __('messages.edit') }}</a>
                                            @role('Admin')
                                            <form action="{{ route('members.destroy', $member->id) }}" method="POST"
                                                  onsubmit="return confirm('{{ __('messages.confirm_delete_member') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-danger btn-xs">{{ __('messages.delete') }}</button>
                                            </form>
                                            @endrole
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">{{ __('messages.no_members_found') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>

                        <div class="mt-4">
                            {{ $members->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
