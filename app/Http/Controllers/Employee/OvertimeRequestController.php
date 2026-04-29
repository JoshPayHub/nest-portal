<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Overtime;
use App\Models\OvertimeList;
use App\Models\Status;
use App\Models\User;
use App\Models\OvertimeStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class OvertimeRequestController extends Controller
{
    public function index(Request $request)
{
    $user = $request->user();
    $allStatuses = Status::all();

    $overtimes = Overtime::where('user_id', $user->id)
        ->with([
            'activities',
            'approvalStatuses.user.userType',
            'approvalStatuses.status'
        ])
        ->orderBy('created_at', 'desc')
        ->paginate(10)
        ->through(function ($ot) {

            $leaderEntry = $ot->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 3);
            $hrEntry     = $ot->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 1);

            $firstActivity = $ot->activities->first();

            return [
                'id' => $ot->id,
                'created_at' => $ot->created_at?->format('M d, Y') ?? '',
                'cut_off_date' => $ot->cut_off_date
                    ? Carbon::parse($ot->cut_off_date)->format('M d, Y')
                    : '',

                'overtime_date' => $firstActivity
                    ? Carbon::parse($firstActivity->overtime_date)->format('M d, Y')
                    : 'N/A',

                'start_time'  => Carbon::parse($firstActivity->time_start)->format('h:i A'),
                'end_time'    => Carbon::parse($firstActivity->time_end)->format('h:i A'),
                'total_hours' => $ot->activities->sum('additional_hours_worked'),
                'reason'      => $firstActivity?->description ?? 'No reason provided',

                'leader_status_name' => $leaderEntry?->status?->name ?? 'Pending',
                'leader_approver_id' => $leaderEntry?->user_id,

                'hr_status_name'     => $hrEntry?->status?->name ?? 'Pending',
                'hr_approver_id'     => $hrEntry?->user_id,

                'activities_count' => $ot->activities->count(),
                'activities' => $ot->activities->map(function ($item) {
                    return [
                        'date'        => Carbon::parse($item->overtime_date)->format('M d, Y'),
                        'description' => $item->description,
                        'time_start'  => Carbon::parse($item->time_start)->format('h:i A'),
                        'time_end'    => Carbon::parse($item->time_end)->format('h:i A'),
                        'hours'       => $item->additional_hours_worked,
                    ];
                }),
            ];
        });

    return Inertia::render('management/Employee/OvertimeList', [
        'overtimes' => $overtimes,
        'statuses'  => $allStatuses,
        'auth_user_type_id' => auth()->user()->user_type_id,
    ]);
}

    public function create(Request $request)
    {
        $user = $request->user()->load(['department', 'position']);
        return Inertia::render('management/Employee/OvertimeForm', [
            'authUser' => [
                'name' => $user->first_name . ' ' . $user->last_name,
                'department' => $user->department?->name ?? 'N/A',
                'position' => $user->position?->name ?? 'N/A',
            ],
            'auth_user_type_id' => auth()->user()->user_type_id,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cut_off_date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.overtime_date' => ['required', 'date'],
            'items.*.description' => ['required', 'string'],
            'items.*.time_start' => ['required'],
            'items.*.time_end' => ['required'],
            'items.*.hours' => ['required', 'numeric'],
        ]);

        DB::transaction(function () use ($request, $validated) {
            $overtime = Overtime::create([
                'user_id' => $request->user()->id,
                'department_id' => $request->user()->department_id,
                'position_id' => $request->user()->position_id,
                'cut_off_date' => $validated['cut_off_date'],
            ]);

            foreach ($validated['items'] as $item) {
                $overtime->activities()->create([
                    'overtime_date' => $item['overtime_date'],
                    'description' => $item['description'],
                    'time_start' => $item['time_start'],
                    'time_end' => $item['time_end'],
                    'additional_hours_worked' => $item['hours'],
                ]);
            }

            // Initialize Approval Statuses (Leader: 3, HR: 1)
            foreach ([3, 1] as $typeId) {
                $approver = User::where('user_type_id', $typeId)->first();
                if ($approver) {
                    OvertimeStatus::create([
                        'overtime_id' => $overtime->id,
                        'user_id' => $approver->id,
                        'status_id' => 4, // Pending
                    ]);
                }
            }
            $this->notifyUsers(
                $request,
                $overtime,
                "New Overtime request",
                "A new Overtime request has been submitted by " . $request->user()->first_name
            );
        });

        return redirect()->back()->with('message', 'Overtime request submitted successfully!');
    }

    public function edit(Request $request, $id)
    {
        $overtime = Overtime::with(['activities', 'approvalStatuses.status', 'approvalStatuses.user'])->findOrFail($id);

        if ($overtime->user_id !== $request->user()->id) {
            abort(403);
        }

        $approvals = $overtime->approvalStatuses;
        $isLeaderApproved = $approvals->contains(fn($a) => $a->user->user_type_id == 3 && $a->status_id == 7);
        $isHRApproved = $approvals->contains(fn($a) => $a->user->user_type_id == 1 && $a->status_id == 7);
        $hasAnyRejection = $approvals->contains(fn($a) => $a->status_id == 8);

        if (($isLeaderApproved || $isHRApproved) && !$hasAnyRejection) {
            return redirect()->back()->with('error', 'Overtime cannot be edited once approval has started.');
        }

        return Inertia::render('management/Employee/OvertimeForm', [
            'overtime' => $overtime,
            'isEditing' => true,
            'authUser' => [
                'name' => $request->user()->name,
                'department' => $request->user()->department?->name ?? 'N/A',
                'position' => $request->user()->position?->name ?? 'N/A',
            ],
            'auth_user_type_id' => auth()->user()->user_type_id,
        ]);
    }

    public function update(Request $request, $id)
    {
        $overtime = Overtime::findOrFail($id);

        if ($overtime->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'cut_off_date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.overtime_date' => ['required', 'date'],
            'items.*.description' => ['required', 'string'],
            'items.*.time_start' => ['required'],
            'items.*.time_end' => ['required'],
            'items.*.hours' => ['required', 'numeric'],
        ]);

        DB::transaction(function () use ($request, $overtime, $validated) {
            $overtime->update(['cut_off_date' => $validated['cut_off_date']]);

            // Reset all approval statuses to Pending (Status 4)
            OvertimeStatus::where('overtime_id', $overtime->id)->update([
                'status_id' => 4,
                'updated_at' => now()
            ]);

            $overtime->activities()->delete();
            foreach ($validated['items'] as $item) {
                $overtime->activities()->create([
                    'overtime_date' => $item['overtime_date'],
                    'description' => $item['description'],
                    'time_start' => $item['time_start'],
                    'time_end' => $item['time_end'],
                    'additional_hours_worked' => $item['hours'],
                ]);
            }
            $this->notifyUsers(
                $request,
                $overtime,
                "Overtime Updated",
                $request->user()->first_name . " has updated their Overtime and is awaiting re-approval."
            );
        });

        $userTypeId = $request->user()->user_type_id;

        $routeMap = [
            2 => 'employee.overtimerequests.index',
            3 => 'head.overtimerequests.index',
        ];

        $routeName = $routeMap[$userTypeId];

        return redirect()->route($routeName)->with('message', 'Overtime updated and reset for approval.');
    }

    private function notifyUsers(Request $request, $report, $title, $message)
    {
        $employeeId = $report->user_id;
        $deptHeads = User::where('user_type_id', 3)
            ->where('department_id', $report->department_id)
            ->get();

        foreach ($deptHeads as $head) {
            Notification::create([
                'user_id'         => $employeeId,
                'user_type_id'    => 3,
                'title'           => $title,
                'message'         => $message,
                'route'           => '/head/overtime-request',
                'data'            => json_encode(['overtime_id' => $report->id]),
            ]);
        }

        $hrUsers = User::where('user_type_id', 1)->get();
        foreach ($hrUsers as $hr) {
            Notification::create([
                'user_id'         => $employeeId,
                'user_type_id'    => 1,
                'title'           => $title,
                'message'         => $message,
                'route'           => '/hr/overtime-request',
                'data'            => json_encode(['overtime_id' => $report->id]),
            ]);
        }
    }
}
