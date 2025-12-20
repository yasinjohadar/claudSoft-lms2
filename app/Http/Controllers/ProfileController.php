<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request)
    {
        $user = $request->user();
        
        // Redirect based on user role
        if ($user->hasRole('student')) {
            return redirect()->route('student.profile.edit');
        }
        
        if ($user->hasRole('admin')) {
            // For admin, redirect to user edit page (admin can edit their own profile there)
            return redirect()->route('users.edit', $user->id);
        }
        
        // Fallback: if no specific role, redirect to dashboard
        return redirect()->route('dashboard');
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Redirect based on user role
        if ($user->hasRole('student')) {
            return Redirect::route('student.profile.edit')->with('status', 'profile-updated');
        }
        
        if ($user->hasRole('admin')) {
            return Redirect::route('users.edit', $user->id)->with('status', 'profile-updated');
        }

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
