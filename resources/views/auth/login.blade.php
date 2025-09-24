@extends('layout.master2')
@push('styles')
<style>
    .company-title {
        font-family: "Hind Siliguri", sans-serif;
        font-size: 1.8rem;
        font-weight: 700;
        color: #2c3e50;
        letter-spacing: 1px;
    }

    .company-title span {
        color: #198754;
    }
</style>
@section('content')
<div class="auth-hero d-flex align-items-center justify-content-center">
    {{-- Language Switch Dropdown --}}
    <div class="dropdown text-center mb-4" style="position: absolute; top: 20px; right: 20px;">
        <a class="btn btn-outline-secondary btn-sm dropdown-toggle d-inline-flex align-items-center"
            href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            @if(session()->get('locale') == 'bn')
            <img src="{{ url('build/images/flags/bd.svg') }}" class="w-20px" alt="বাংলা">
            <span class="ms-2">বাংলা</span>
            @else
            <img src="{{ url('build/images/flags/us.svg') }}" class="w-20px" alt="English">
            <span class="ms-2">English</span>
            @endif
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
            <li>
                <form action="{{ route('language.switch') }}" method="POST">
                    @csrf
                    <input type="hidden" name="lang" value="en">
                    <button type="submit" class="dropdown-item d-flex align-items-center">
                        <img src="{{ url('build/images/flags/us.svg') }}" class="w-20px" alt="English">
                        <span class="ms-2">English</span>
                    </button>
                </form>
            </li>
            <li>
                <form action="{{ route('language.switch') }}" method="POST">
                    @csrf
                    <input type="hidden" name="lang" value="bn">
                    <button type="submit" class="dropdown-item d-flex align-items-center">
                        <img src="{{ url('build/images/flags/bd.svg') }}" class="w-20px" alt="বাংলা">
                        <span class="ms-2">বাংলা</span>
                    </button>
                </form>
            </li>
        </ul>
    </div>

    <div class="card shadow-lg border-0" style="max-width: 450px; width: 100%;">
        <div class="card-body p-4">

            {{-- Company Title --}}
            <div class="text-center py-4 bg-light border-bottom mb-3">
                <h1 class="company-title mb-0">
                    পদ্মা শ্রমজীবী <span class="text-primary">সমবায় সমিতি</span> লিমিটেড
                </h1>
            </div>


            {{-- Subtitle --}}
            <h5 class="text-secondary fw-normal text-center mb-4">
                {{ __('auth.welcome') }}
            </h5>

            {{-- Login Form --}}
            <form class="forms-sample" method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Phone --}}
                <div class="mb-3">
                    <label for="phone" class="form-label">{{ __('auth.phone') }}</label>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror"
                        id="phone" name="phone" value="{{ old('phone') }}"
                        placeholder="{{ __('auth.phone_placeholder') }}" required autofocus>
                    @error('phone')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="mb-3">
                    <label for="password" class="form-label">{{ __('auth.password') }}</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                        id="password" name="password"
                        placeholder="{{ __('auth.password_placeholder') }}" required>
                    @error('password')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                {{-- Remember Me & Forgot Password --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="remember" id="remember">
                        <label class="form-check-label" for="remember">
                            {{ __('auth.remember') }}
                        </label>
                    </div>

                </div>

                {{-- Submit --}}
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        {{ __('auth.login') }}
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection