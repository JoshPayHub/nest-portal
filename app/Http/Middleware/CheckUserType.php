<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserType
{
    public function handle(Request $request, Closure $next, string $type): Response
    {
        if (!Auth::check()) {
            return redirect('/');
        }

        $user = Auth::user();
        $userType = $user->userType?->name;

        // Check if the user type matches (e.g., 'employee')
        if (strtolower($userType) === strtolower($type)) {

            // ADD THIS: Redirect pending employees to profile if they try to access other employee pages
            if ($userType === 'employee' && $user->status_id == 4 && !$request->routeIs('employee.profile')) {
                return redirect()->route('employee.profile')
                    ->with('message', 'Please complete your profile first.');
            }

            return $next($request);
        }

        abort(403, 'You do not have permission to access this area.');
    }
}
