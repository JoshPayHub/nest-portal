<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use App\Models\Status;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class EmployeeListController extends Controller
{
    public function index(Request $request)
    {
        $employees = User::with(['department', 'position', 'status'])
            ->where('user_type_id', 2)
            ->when($request->search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->department, fn($q, $d) => $q->where('department_id', $d))
            ->when($request->status, fn($q, $s) => $q->where('status_id', $s))
            ->latest()
            ->get(); // <-- Get all without pagination

        return Inertia::render('management/HR/Employees', [
            'employees' => $employees,
            'departments' => Department::all(), // All for disabled options
            'positions' => Position::all(),     // All for disabled options
            'statuses' => Status::whereIn('id', [1, 2])->get(),
            'filters' => $request->only(['search', 'department', 'status'])
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status_id' => 'required|exists:statuses,id',
            'department_id' => $request->status_id == 2 ? 'nullable' : [
                'required',
                Rule::exists('departments', 'id')->where(fn ($q) => $q->where('status_id', 1))
            ],
            'position_id' => $request->status_id == 2 ? 'nullable' : [
                'required',
                Rule::exists('positions', 'id')->where(fn ($q) => $q->where('status_id', 1))
            ],
        ], [
            'department_id.exists' => 'The selected department is currently inactive.',
            'position_id.exists' => 'The selected position is currently inactive.',
        ]);

        $user = User::findOrFail($id);
        $user->update($validated);

        return redirect()->back();
    }
}
