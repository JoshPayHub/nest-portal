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

        // Check if the user type matches (e.g., 'employee', 'head', 'hr')
        if (strtolower($userType) === strtolower($type)) {

            // Users that must complete profile first
            $restrictedTypes = ['employee', 'head', 'hr'];

            if (
                in_array(strtolower($userType), $restrictedTypes) &&
                $user->status_id == 4
            ) {
              $profileRoutes = [
    'employee' => ['employee.profile', 'employee.profile.update', 'employee.profile.change-password'],
    'head'     => ['head.profile', 'head.profile.update', 'head.profile.change-password'],
    'hr'       => ['hr.profile', 'hr.profile.update', 'hr.profile.change-password'],
];

               $route = $profileRoutes[strtolower($userType)] ?? null;

if ($route && !$request->routeIs(...$route)) {
    return redirect()->route($route[0]) // redirect to the GET profile route
        ->with('message', 'Please complete your profile first.');
}
            }

            return $next($request);
        }

        abort(403, 'You do not have permission to access this area.');
    }
}
