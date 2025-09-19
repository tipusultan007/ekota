@extends('layout.master2')

@section('content')
    <div class="row w-100 mx-0 auth-page">
        <div class="col-md-8 col-xl-6 mx-auto">
            <div class="card">
                <div class="row">
                    <div class="col-md-4 pe-md-0">
                        {{-- You can change the background image URL here --}}
                        <div class="auth-side-wrapper" style="background-image: url({{ asset('build/images/img6.webp') }})">
                        </div>
                    </div>
                    <div class="col-md-8 ps-md-0">
                        <div class="auth-form-wrapper px-4 py-5">
                            <a href="{{ url('/') }}" class="sidebar-brand d-block mb-2">S&L<span>Pro</span></a>
                            <h5 class="text-secondary fw-normal mb-4">Welcome back! Log in to your account.</h5>

                            {{-- The form now points to the 'login' route and uses the POST method --}}
                            <form class="forms-sample" method="POST" action="{{ route('login') }}">
                                @csrf

                                {{-- Email Address Field --}}
                                <div class="mb-3">
                                    <label for="phone" class="form-label">{{ __('Phone Number') }}</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                           id="phone" name="phone" value="{{ old('phone') }}" required autocomplete="phone" autofocus placeholder="Phone">

                                    @error('phone')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>

                                {{-- Password Field --}}
                                <div class="mb-3">
                                    <label for="password" class="form-label">{{ __('Password') }}</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                           id="password" name="password" required autocomplete="current-password" placeholder="Password">

                                    @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>

                                {{-- Remember Me & Forgot Password --}}
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="remember">
                                            {{ __('Remember me') }}
                                        </label>
                                    </div>
                                    @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}">
                                            {{ __('Forgot Your Password?') }}
                                        </a>
                                    @endif
                                </div>

                                {{-- Submit Buttons --}}
                                <div>
                                    <button type="submit" class="btn btn-primary me-2 mb-2 mb-md-0">
                                        {{ __('Login') }}
                                    </button>
                                    {{-- The Google Sign-in button can be implemented later with Laravel Socialite --}}
                                    {{-- <a href="#" class="btn btn-outline-primary btn-icon-text mb-2 mb-md-0">...</a> --}}
                                </div>

                                {{-- Sign Up Link --}}
                                <p class="mt-3 text-secondary">Don't have an account? <a href="{{ route('register') }}">Sign up</a></p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
