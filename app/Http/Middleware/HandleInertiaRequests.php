<?php

namespace App\Http\Middleware;

use App\Models\Notification;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     */
    protected $rootView = 'app';

    /**
     * Define the props that are shared by default.
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $notifications = [];

    if ($user) {
        $query = Notification::query();

        if ($user->user_type_id == 1) {
            $query->where('user_type_id', 1);

        } elseif ($user->user_type_id == 3) {
            $query->where(function ($q) use ($user) {

                $q->where(function ($sub) use ($user) {
                    $sub->where('user_type_id', 3)
                        ->whereHas('employee', function ($emp) use ($user) {
                            $emp->where('department_id', $user->department_id);
                        });
                })

                ->orWhere(function ($sub) use ($user) {
                    $sub->where('user_id', $user->id)
                        ->whereNull('user_type_id');
                });

            });

        } else {
            $query->where('user_id', $user->id)
                ->whereNull('user_type_id');
        }

        $notifications = $query
            ->with('employee')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();
    }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id'           => $user->id,
                    'name'         => $user->name,
                    'user_type_id' => $user->user_type_id,
                    'type'         => $user->userType?->name,
                    'status_id'    => $user->status_id,
                    'department_id'=> $user->department_id,
                ] : null,
            ],
            'notifications' => $notifications,
            'flash' => [
                'message'       => $request->session()->get('message'),
                'success'       => $request->session()->get('success'),
                'error'         => $request->session()->get('error'),
                'reset_success' => $request->session()->get('reset_success'),
            ],
        ]);
    }
}
