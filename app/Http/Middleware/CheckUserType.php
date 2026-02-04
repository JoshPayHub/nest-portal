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
        // 1. Check if user is even logged in
        if (!Auth::check()) {
            return redirect('/');
        }

        $userType = $request->user()->userType?->name;

        if (strtolower($userType) === strtolower($type)) {
            return $next($request);
        }

        abort(403, 'You do not have permission to access this area.');
    }
}
