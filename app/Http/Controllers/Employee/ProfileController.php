<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{

    public function index()
    {
        // Eager load the relationships so their names are available
        return Inertia::render('management/Employee/ProfileEdit', [
            'employee' => Auth::user()->load(['status', 'department', 'position']),
        ]);
    }

   public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:50',
            'gender' => 'required|in:Male,Female,Other,Prefer not to say',
            'date_birth' => 'required|date',
            'civil_status' => 'required|in:Married,Single,Other',
            'nationality' => 'required|string',
            'personal_email' => 'required|email|unique:users,personal_email,' . $user->id,
            'mobile_number' => 'required|string|max:20|unique:users,mobile_number,' . $user->id,
            'telephone_number' => 'nullable|string|max:20|unique:users,telephone_number,' . $user->id,
            'present_address' => 'required|string',
            'permanent_address' => 'required|string',
            'sss_number' => 'nullable|string|max:50',
            'philhealth_number' => 'nullable|string|max:50',
            'pagibig_number' => 'nullable|string|max:50',
            'tin_number' => 'nullable|string|max:50',
            'contact_person' => 'required|string|max:255',
            'relationship' => 'required|string|max:100',
            'contact_number' => 'required|string|max:20',
            'address' => 'required|string',
            // DYNAMIC REQUIREMENT: If user already has a file, it's nullable. If not, it's required.
            'resume' => ($user->resume ? 'nullable' : 'required') . '|file|mimes:pdf,doc,docx|max:2048',
            'profile_photo' => ($user->profile_photo ? 'nullable' : 'required') . '|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Handle Resume Upload
        if ($request->hasFile('resume')) {
            if ($user->resume) Storage::disk('public')->delete($user->resume);
            $validated['resume'] = $request->file('resume')->store('resumes', 'public');
        } else {
            // Keep the old one if no new file provided
            unset($validated['resume']);
        }

        // Handle Photo Upload
        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo) Storage::disk('public')->delete($user->profile_photo);
            $validated['profile_photo'] = $request->file('profile_photo')->store('profiles', 'public');
        } else {
            // Keep the old one if no new file provided
            unset($validated['profile_photo']);
        }

        if ($user->status_id == 4) {
            $validated['status_id'] = 1;
        }

        $user->update($validated);

        return redirect()->back()->with('message', 'Profile updated successfully!');
    }
}
