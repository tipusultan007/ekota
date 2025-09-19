@extends('layout.master')

@push('plugin-styles')
    {{-- কোনো নির্দিষ্ট প্লাগইন লাগলে এখানে যোগ করুন --}}
@endpush

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Profile</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-12 col-xl-8 mx-auto">

            {{-- প্রোফাইল তথ্য আপডেট সেকশন --}}
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Profile Information</h6>
                    <p class="text-muted mb-4">Update your account's profile information and email address.</p>

                    @if (session('status') === 'profile-updated')
                        <div class="alert alert-success" role="alert">
                            Profile has been updated successfully.
                        </div>
                    @endif

                    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('patch')

                        <div class="row">
                            <div class="col-md-12 text-center mb-4">
                                <img src="{{ $user->getFirstMediaUrl('user_photo') ?: 'https://placehold.co/150x150' }}"
                                     alt="Profile Photo" class="rounded-circle" width="150" height="150" style="object-fit: cover;">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus>
                                @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                                @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input id="phone" name="phone" type="text" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}">
                                @error('phone') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="nid_no" class="form-label">NID Number</label>
                                <input id="nid_no" name="nid_no" type="text" class="form-control @error('nid_no') is-invalid @enderror" value="{{ old('nid_no', $user->nid_no) }}">
                                @error('nid_no') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="joining_date" class="form-label">Joining Date</label>
                                <input id="joining_date" name="joining_date" type="date" class="form-control @error('joining_date') is-invalid @enderror" value="{{ old('joining_date', $user->joining_date ? $user->joining_date->format('Y-m-d') : '') }}">
                                @error('joining_date') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="photo" class="form-label">Change Profile Photo</label>
                                <input id="photo" name="photo" type="file" class="form-control @error('photo') is-invalid @enderror">
                                @error('photo') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea id="address" name="address" class="form-control @error('address') is-invalid @enderror">{{ old('address', $user->address) }}</textarea>
                                @error('address') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- পাসওয়ার্ড আপডেট সেকশন --}}
            <div class="card mt-4">
                <div class="card-body">
                    <h6 class="card-title">Update Password</h6>
                    <p class="text-muted mb-4">Ensure your account is using a long, random password to stay secure.</p>

                    @if (session('status') === 'password-updated')
                        <div class="alert alert-success" role="alert">
                            Password has been updated successfully.
                        </div>
                    @endif

                    {{-- পাসওয়ার্ড আপডেটের জন্য একটি আলাদা রুট এবং কন্ট্রোলার মেথড ব্যবহার করা হয় --}}
                    <form method="post" action="{{ route('password.update') }}">
                        @csrf
                        @method('put')

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input id="current_password" name="current_password" type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" autocomplete="current-password">
                            @error('current_password', 'updatePassword') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input id="password" name="password" type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" autocomplete="new-password">
                            @error('password', 'updatePassword') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" autocomplete="new-password">
                            @error('password_confirmation', 'updatePassword') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary">Update Password</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('plugin-scripts')
    {{-- কোনো নির্দিষ্ট প্লাগইন লাগলে এখানে যোগ করুন --}}
@endpush

@push('custom-scripts')
    {{-- কোনো কাস্টম জাভাস্ক্রিপ্ট লাগলে এখানে যোগ করুন --}}
@endpush
