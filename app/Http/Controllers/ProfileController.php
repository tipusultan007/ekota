<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile_edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // ভ্যালিডেটেড ডেটা দিয়ে ইউজার অবজেক্ট পূরণ করুন
        $user->fill($request->validated());

        // যদি ইমেল পরিবর্তন করা হয়, তাহলে ভেরিফিকেশন রিসেট করুন
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // নতুন ফিল্ডগুলো আপডেট করুন
        $user->phone = $request->phone;
        $user->address = $request->address;
        $user->nid_no = $request->nid_no;
        $user->joining_date = $request->joining_date;

        // প্রোফাইল ছবি আপলোড করুন
        if ($request->hasFile('photo')) {
            $user->addMediaFromRequest('photo')->toMediaCollection('user_photo');
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
