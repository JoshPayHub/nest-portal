<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function index()
    {
        return Inertia::render('management/HR/ProfileEdit', [
            'employee' => Auth::user(),
            'auth_user_type_id' => auth()->user()->user_type_id,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',

            'gender' => 'required|in:Male,Female,Other,Prefer not to say',
            'date_birth' => 'required|date',

            'profile_photo' =>
                ($user->profile_photo ? 'nullable' : 'required')
                . '|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Profile Photo Upload
        if ($request->hasFile('profile_photo')) {

            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            $validated['profile_photo'] = $request
                ->file('profile_photo')
                ->store('profiles', 'public');
        } else {
            unset($validated['profile_photo']);
        }

        $user->update($validated);

        return back()->with([
            'message' => 'Profile updated successfully!',
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers(),
            ],
        ], [
            'new_password.confirmed' => 'Password confirmation does not match.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Current password is incorrect.',
            ]);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with(
            'message',
            'Password changed successfully!'
        );
    }
}
