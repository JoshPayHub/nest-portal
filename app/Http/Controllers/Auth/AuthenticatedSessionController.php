<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page
     */
    public function create()
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Handle login request
     */


    public function store(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'username' => 'The provided credentials do not match our records.',
            ]);
        }

        $request->session()->regenerate();
        $user = Auth::user();

        // Check if employee is Pending (status_id 4)
        if ($user->user_type_id == 2 && $user->status_id == 4) {
            return redirect()->route('employee.profile');
        }

        /**
         * Redirect based on user type for Active users
         */
        return redirect()->intended(
            match ((int) $user->user_type_id) {
                1 => route('hr.dashboard'),
                2 => route('employee.dashboard'),
                3 => route('head.dashboard'),
                default => '/',
            }
        );
    }

    /**
     * Logout
     */
    public function destroy(Request $request)
{
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/'); // Goes back to your root login
}
}
