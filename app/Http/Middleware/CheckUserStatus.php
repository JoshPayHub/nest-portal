<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect('/');
        }

        $user = Auth::user();
        $allowedStatuses = [1, 4];

        if (!in_array($user->status_id, $allowedStatuses)) {

            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/')
                ->with('error', 'Your account is inactive.');
        }

        return $next($request);
    }
}
