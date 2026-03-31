<?php

namespace App\Http\Controllers\Head;

use App\Http\Controllers\Controller;
use App\Models\Manpower;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ManpowerController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Employees (same department)
        $employees = User::where('department_id', $user->department_id)
            ->select('id', 'first_name', 'last_name')
            ->orderBy('first_name')
            ->get();

        $query = Manpower::whereHas('user', function ($q) use ($user) {
            $q->where('department_id', $user->department_id);
        })
        ->with([
            'user',
            'approvalStatuses.user',
            'approvalStatuses.status'
        ]);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('position_type', 'like', "%{$search}%");
        }

        // Employee filter
        if ($request->filled('employee_id')) {
            $query->where('user_id', $request->employee_id);
        }

        $manpowers = $query->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(function ($item) {

                $leaderEntry = $item->approvalStatuses
                    ->first(fn ($log) => $log->user?->user_type_id == 3);

                $hrEntry = $item->approvalStatuses
                    ->first(fn ($log) => $log->user?->user_type_id == 1);

                return [
                    'id' => $item->id,
                    'employee_name' => $item->user->first_name . ' ' . $item->user->last_name,

                    'date_filed' => $item->created_at->format('M d, Y'),
                    'date_required' => Carbon::parse($item->date_required)->format('M d, Y'),

                    'position_type' => $item->position_type,
                    'report_to' => $item->report_to,
                    'job_description' => $item->job_description,
                    'justification' => $item->justification,

                    'status_type' => $item->status_type,
                    'payment_type' => $item->payment_type,

                    // ✅ SAME AS LEAVE
                    'leader_status_name' => $leaderEntry?->status?->name ?? 'Pending',
                    'hr_status_name' => $hrEntry?->status?->name ?? 'Pending',
                ];
            });

        return Inertia::render('management/Head/ManpowerList', [
            'items' => $manpowers,
            'employeeOptions' => $employees,
            'filters' => $request->only(['search', 'employee_id']),
        ]);
    }

    // ✅ SAME APPROVAL LOGIC AS LEAVE
    public function approve(Request $request, $id)
    {
        $request->validate([
            'status_id' => 'required|in:7,8', // 7=Approved, 8=Rejected
        ]);

        $manpower = Manpower::findOrFail($id);

        DB::table('manpower_statuses')->updateOrInsert(
            [
                'manpower_id' => $manpower->id,
                'user_id' => $request->user()->id,
            ],
            [
                'status_id' => $request->status_id,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return back()->with('message', 'Manpower request updated.');
    }
}
