<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        //dd($request);
        $credentials = $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // --- ধাপ ২: Auth::attempt-এ phone ব্যবহার করুন ---
        // $request->authenticate() এর পরিবর্তে আমরা ম্যানুয়ালি চেষ্টা করব
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            // যদি অথেনটিকেশন ব্যর্থ হয়, একটি এরর থ্রো করুন
            throw ValidationException::withMessages([
                'phone' => trans('auth.failed'), // এররটি phone ফিল্ডের জন্য দেখান
            ]);
        }


        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
