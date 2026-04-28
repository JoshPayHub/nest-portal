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

            // Logic for HR (1) and Dept Head (3)
            if (in_array($user->user_type_id, [1, 3])) {
                $query->where('user_type_id', $user->user_type_id);

                // If user is a Department Head, filter by their department
                if ($user->user_type_id == 3) {
                    $query->whereHas('employee', function ($q) use ($user) {
                        $q->where('department_id', $user->department_id);
                    });
                }
            } else {
                // Logic for regular Employee (2)
                // They only see notifications where they are the owner and user_type_id is null
                $query->where('user_id', $user->id)
                      ->whereNull('user_type_id');
            }

            // Get the 10 latest notifications for the bell icon
            $notifications = $query->with('employee')->latest()->take(10)->get();
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
