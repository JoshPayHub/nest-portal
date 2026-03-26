<?php

namespace App\Http\Controllers\Head;

use App\Http\Controllers\Controller;
use App\Models\ChangeOff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class ChangeOffController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Fetch employees in the same department for the filter dropdown
        $employees = User::where('department_id', $user->department_id)
            ->select('id', 'first_name', 'last_name', 'username')
            ->orderBy('first_name', 'asc')
            ->get();

        $requestsQuery = ChangeOff::join('users', 'change_offs.user_id', '=', 'users.id')
            ->where('users.department_id', $user->department_id)
            ->with([
                'user',
                'label.off',
                'label.originalDay',
                'label.newDay',
                'approvalStatuses.user.userType',
                'approvalStatuses.status'
            ])
            ->select('change_offs.*');

        // Apply Employee Filter
        if ($request->has('employee_id') && $request->employee_id != '') {
            $requestsQuery->where('change_offs.user_id', $request->employee_id);
        }

        $requests = $requestsQuery->orderBy('change_offs.created_at', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(function ($req) {
                $leaderEntry = $req->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 3);
                $hrEntry     = $req->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 1);

                return [
                    'id'            => $req->id,
                    'employee_name' => $req->user->first_name . ' ' . $req->user->last_name,
                    'date_filed'    => $req->created_at->format('M d, Y'),
                    'request_type'  => $req->label->off->name ?? 'N/A',
                    'original_date' => $req->label ? Carbon::parse($req->label->original_date)->format('M d, Y') : 'N/A',
                    'original_day'  => $req->label->originalDay->name ?? 'N/A',
                    'original_time' => $req->label->original_time ?? 'N/A',
                    'new_date'      => $req->label ? Carbon::parse($req->label->new_date)->format('M d, Y') : 'N/A',
                    'new_day'       => $req->label->newDay->name ?? 'N/A',
                    'new_time'      => $req->label->new_time ?? 'N/A',
                    'leader_status' => $leaderEntry?->status?->name ?? 'Pending',
                    'hr_status'     => $hrEntry?->status?->name ?? 'Pending',
                ];
            });

        return Inertia::render('management/Head/ChangeOffList', [
            'requests' => $requests,
            'employeeOptions' => $employees,
            'filters' => $request->only(['employee_id'])
        ]);
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'status_id' => 'required|in:7,8', // 7=Approved, 8=Rejected
        ]);

        $changeOff = ChangeOff::findOrFail($id);

        // Update or Create the approval status for this Head/User
        DB::table('change_off_statuses')->updateOrInsert(
            [
                'change_off_id' => $changeOff->id,
                'user_id' => $request->user()->id,
            ],
            [
                'status_id' => $request->status_id,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return redirect()->back()->with('message', 'Change Off request processed.');
    }
}
