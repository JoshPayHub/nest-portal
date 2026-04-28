<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        $middleware->alias([
            'user_type' => \App\Http\Middleware\CheckUserType::class,
        ]);

        // STRICT NAMED ROUTE REDIRECTS
        $middleware->redirectTo(
            guests: '/',
            users: function (Request $request) {
                $user = $request->user();
                if (!$user) return '/';

                return match ((int) $user->user_type_id) {
                    1 => route('hr.dashboard'),
                    2 => route('employee.dashboard'),
                    3 => route('head.dashboard'),
                };
            },
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
