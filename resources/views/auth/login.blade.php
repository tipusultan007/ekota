@extends('layout.master2')

@push('plugin-styles')
<style>
    .auth-page {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: linear-gradient(-45deg, #4f46e5, #7c3aed, #db2777, #2563eb);
        background-size: 400% 400%;
        animation: gradientBG 15s ease infinite;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        z-index: 9999;
        overflow-y: auto;
    }

    @keyframes gradientBG {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    .auth-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
        border-radius: 28px;
        border: 1px solid rgba(255, 255, 255, 0.4);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
        width: 100%;
        max-width: 440px;
        padding: 40px;
        position: relative;
    }

    .company-title {
        font-size: 2.2rem;
        font-weight: 800;
        background: linear-gradient(to right, #4f46e5, #7c3aed);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 8px;
        text-align: center;
        line-height: 1.25;
    }

    .auth-subtitle {
        color: #6b7280;
        font-size: 0.95rem;
        text-align: center;
        margin-bottom: 30px;
    }

    .form-label {
        font-weight: 700;
        color: #374151;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        display: block;
    }

    .input-group-custom {
        position: relative;
        margin-bottom: 24px;
    }

    .form-control {
        border-radius: 14px;
        padding: 14px 45px 14px 16px;
        border: 2px solid #e5e7eb;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: #f9fafb;
        font-size: 1rem;
        font-weight: 500;
    }

    .form-control:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
        background: #fff;
        outline: none;
    }

    .input-icon {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        pointer-events: none;
        width: 20px;
        height: 20px;
    }

    .btn-login {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        border: none;
        border-radius: 14px;
        padding: 16px;
        font-weight: 800;
        color: #fff;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4);
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 10px;
    }

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 20px 25px -5px rgba(79, 70, 229, 0.5);
        background: linear-gradient(135deg, #4338ca, #6d28d9);
        color: #fff;
    }

    .btn-login i {
        margin-left: 10px;
        transition: transform 0.3s ease;
    }

    .btn-login:hover i {
        transform: translateX(5px);
    }

    .lang-switcher {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
    }

    .lang-btn {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 12px;
        padding: 8px 16px;
        color: #fff;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .lang-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        color: #fff;
    }

    .dropdown-menu {
        border-radius: 15px;
        border: none;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        padding: 10px;
    }

    .dropdown-item {
        border-radius: 10px;
        padding: 10px 15px;
        font-weight: 500;
    }

    .w-20px { width: 20px; }
    
    .input-icon-wrapper {
        position: relative;
    }
    
    .input-icon-wrapper i {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
    }
</style>
@endpush

@section('content')
<div class="auth-page">
    {{-- Language Switcher --}}
    <div class="lang-switcher">
        <div class="dropdown">
            <button class="btn lang-btn dropdown-toggle d-flex align-items-center" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                @if(session()->get('locale') == 'bn')
                    <img src="{{ url('build/images/flags/bd.svg') }}" class="w-20px" alt="বাংলা">
                    <span class="ms-2">বাংলা</span>
                @else
                    <img src="{{ url('build/images/flags/us.svg') }}" class="w-20px" alt="English">
                    <span class="ms-2">English</span>
                @endif
            </button>
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
    </div>

    <div class="auth-card">
        <h1 class="company-title">
            পদ্মা শ্রমজীবী সমবায় সমিতি লিমিটেড
        </h1>
        <p class="auth-subtitle">সমিতি ব্যবস্থাপনা সফটওয়্যার</p>

        <h5 class="text-center mb-4 fw-bold" style="color: #374151;">
            {{ __('auth.welcome') }}
        </h5>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- Phone --}}
            <div class="mb-2">
                <label for="phone" class="form-label">{{ __('auth.phone') }}</label>
                <div class="input-group-custom">
                    <input type="text" class="form-control @error('phone') is-invalid @enderror"
                        id="phone" name="phone" value="{{ old('phone') }}"
                        placeholder="{{ __('auth.phone_placeholder') }}" required autofocus>
                    <i data-lucide="phone" class="input-icon"></i>
                    @error('phone')
                        <div class="text-danger small mt-1"><strong>{{ $message }}</strong></div>
                    @enderror
                </div>
            </div>

            {{-- Password --}}
            <div class="mb-2">
                <label for="password" class="form-label">{{ __('auth.password_field') }}</label>
                <div class="input-group-custom">
                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                        id="password" name="password"
                        placeholder="{{ __('auth.password_placeholder') }}" required>
                    <i data-lucide="lock" class="input-icon"></i>
                    @error('password')
                        <div class="text-danger small mt-1"><strong>{{ $message }}</strong></div>
                    @enderror
                </div>
            </div>

            {{-- Remember Me --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="remember" id="remember" style="cursor: pointer;">
                    <label class="form-check-label small" for="remember" style="cursor: pointer; color: #4b5563;">
                        {{ __('auth.remember') }}
                    </label>
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn btn-login">
                <span>{{ __('auth.login') }}</span>
                <i data-lucide="arrow-right" style="width: 20px; height: 20px;"></i>
            </button>
        </form>
    </div>
</div>
@endsection

@push('custom-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.lucide) {
            lucide.createIcons();
        }
    });
</script>
@endpush