@extends('layout.master')
@push('plugin-styles')
@endpush
@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('messages.user_management') }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="card-title">{{ __('messages.users_list') }}</h6>
                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                            <i data-lucide="plus" class="icon-sm me-2"></i>{{ __('messages.add_new_user') }}
                        </a>
                    </div>

                    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
                    @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>{{ __('messages.serial') }}</th>
                                <th>{{ __('messages.name') }}</th>
                                <th>{{ __('messages.email') }} / {{ __('messages.phone_number') }}</th>
                                <th>{{ __('messages.role') }}</th>
                                <th>{{ __('messages.area') }}</th>
                                <th>{{ __('messages.actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($users as $key => $user)
                                <tr>
                                    <td>{{ $users->firstItem() + $key }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $user->getFirstMediaUrl('user_photo', 'thumb') ?: 'https://placehold.co/40x40' }}"
                                                 alt="Photo" class="rounded-circle me-2" width="40" height="40" style="object-fit: cover;">
                                            {{ $user->name }}
                                        </div>
                                    </td>
                                    <td>
                                        {{ $user->email }}
                                        @if($user->phone)
                                            <br><small class="text-muted">{{ $user->phone }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @foreach ($user->roles as $role)
                                            <span class="badge bg-primary">{{ $role->name }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        {{-- একাধিক এলাকার নাম দেখানোর জন্য --}}
                                        @forelse ($user->areas as $area)
                                            <span class="badge bg-secondary">{{ $area->name }}</span>
                                        @empty
                                            <span class="text-muted">N/A</span>
                                        @endforelse
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary btn-xs me-1">{{ __('messages.edit') }}</a>

                                            {{-- অ্যাডমিন নিজেকে ডিলিট করতে পারবে না --}}
                                            @if(Auth::id() != $user->id)
                                                <form id="delete-user-{{ $user->id }}" action="{{ route('admin.users.destroy', $user->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-danger btn-xs" onclick="showDeleteConfirm('delete-user-{{ $user->id }}')">
                                                        {{ __('messages.delete') }}
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">{{ __('messages.no_users_found') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                        <div class="mt-3">
                            {{ $users->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-scripts')

@endpush
