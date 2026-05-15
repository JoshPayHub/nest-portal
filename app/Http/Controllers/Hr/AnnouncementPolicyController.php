<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\AnnouncementPolicy;
use App\Models\AnnouncementPolicyFilter;
use App\Models\Department;
use App\Models\Notification;
use App\Models\Position;
use App\Models\Status;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AnnouncementPolicyController extends Controller
{
    public function index(Request $request)
    {
        $query = AnnouncementPolicy::with([
            'status',
            'filters.department',
            'filters.position'
        ])
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->when($request->tab && $request->tab !== 'all', function ($query) use ($request) {
                $query->where('types', $request->tab);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status_id', $status);
            })
            ->latest('announcements_policies.created_at')
            ->paginate(10)
            ->withQueryString()
            ->through(function ($item) {
                return [
                    'id' => $item->id,
                    'types' => $item->types,
                    'title' => $item->title,
                    'description' => $item->description,
                    'status_id' => $item->status_id,
                    'status' => $item->status
                        ? $item->status->name
                        : 'No Status',
                    'status_name' => $item->status
                        ? $item->status->name
                        : 'Unknown',
                    'filters' => $item->filters,
                    'created_at' => $item->created_at->format('F j, Y'),
                ];
            });

        return Inertia::render(
            'management/HR/AnnouncementAndPolicy',
            [
                'data' => $query,
                'departments' => Department::all(),
                'positions' => Position::all(),
                'statuses' => Status::whereIn('id', [1, 2])->get(),
                'filters' => $request->only([
                    'search',
                    'tab',
                    'status'
                ])
            ]
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'types' => 'required|in:announcements,policies',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status_id' => 'required|integer',
            'selected_departments' => 'nullable|array',
            'selected_positions' => 'nullable|array',
        ]);

        DB::transaction(function () use (
            $validated,
            &$announcement
        ) {

            $announcement = AnnouncementPolicy::create([
                'types' => $validated['types'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'status_id' => $validated['status_id'],
            ]);

            $this->saveFilters(
                $announcement->id,
                $validated
            );

            $typeName = $validated['types'] === 'policies'
                ? 'Policies'
                : 'Announcements';

            $this->notifyUsers(
                $announcement,
                "New {$typeName}",
                "A {$typeName} has been created",
                $validated
            );
        });

        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'types' => 'required|string',
            'description' => 'nullable|string',
            'status_id' => 'required|exists:statuses,id',
            'selected_departments' => 'nullable|array',
            'selected_positions' => 'nullable|array',
        ]);

        DB::transaction(function () use (
            $validated,
            $id
        ) {

            $announcement = AnnouncementPolicy::findOrFail($id);

            $announcement->update([
                'types' => $validated['types'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'status_id' => $validated['status_id'],
            ]);

            AnnouncementPolicyFilter::where(
                'announcement_policy_id',
                $id
            )->delete();

            $this->saveFilters($id, $validated);

            $typeName = $validated['types'] === 'policies'
                ? 'Policies'
                : 'Announcements';

            $this->notifyUsers(
                $announcement,
                "Updated {$typeName}",
                "A {$typeName} has been updated",
                $validated
            );
        });

        return redirect()->back();
    }

    private function saveFilters(
        $recordId,
        $validated
    ) {
        $deptIds = !empty(
            $validated['selected_departments']
        )
            ? $validated['selected_departments']
            : [null];

        $posIds = !empty(
            $validated['selected_positions']
        )
            ? $validated['selected_positions']
            : [null];

        foreach ($deptIds as $dId) {
            foreach ($posIds as $pId) {

                AnnouncementPolicyFilter::create([
                    'announcement_policy_id' => $recordId,
                    'department_id' => $dId,
                    'position_id' => $pId,
                ]);
            }
        }
    }

    private function notifyUsers(
        $announcement,
        $title,
        $message,
        $validated
    ) {
        $departmentIds =
            $validated['selected_departments'] ?? [];

        $positionIds =
            $validated['selected_positions'] ?? [];

        $usersQuery = User::query()
            ->whereIn('user_type_id', [2, 3]);

        /**
         * Department filter
         * If empty = ALL departments
         */
        if (!empty($departmentIds)) {
            $usersQuery->whereIn(
                'department_id',
                $departmentIds
            );
        }

        /**
         * Position filter
         * If empty = ALL positions
         */
        if (!empty($positionIds)) {
            $usersQuery->whereIn(
                'position_id',
                $positionIds
            );
        }

        $users = $usersQuery->get();

        foreach ($users as $user) {

            $userTypePrefix =
                $user->user_type_id == 3
                ? 'head'
                : 'employee';

            $notification = Notification::where(
                'user_id',
                $user->id
            )
                ->where(
                    'data',
                    'LIKE',
                    '%announcement_id%'
                )
                ->where(
                    'data',
                    'LIKE',
                    '%' . $announcement->id . '%'
                )
                ->first();

            if ($notification) {

                $notification->update([
                    'title' => $title,
                    'message' => $message,
                    'is_read' => 0,
                    'read_at' => null,
                    'updated_at' => now(),
                ]);

            } else {

                Notification::create([
                    'user_id' => $user->id,
                    'user_type_id' => $user->user_type_id,
                    'title' => $title,
                    'message' => $message,
                    'route' => "/{$userTypePrefix}/announcements-policies",
                    'data' => json_encode([
                        'announcement_id' => $announcement->id
                    ]),
                ]);
            }
        }
    }
}
