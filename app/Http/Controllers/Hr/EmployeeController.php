<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use App\Models\Status;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    public function index()
    {
        return Inertia::render('management/HR/AddEmployee', [
            'departments' => Department::all(), // Sending all to show disabled options
            'positions' => Position::all(),    // Sending all to show disabled options
            'statuses' => Status::whereIn('id', [1, 2])->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:Male,Female,Other,Prefer not to say',
            'address' => 'nullable|string|max:500',
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
            'department_id.exists' => 'The selected department is currently inactive and cannot be assigned.',
            'position_id.exists' => 'The selected position is currently inactive and cannot be assigned.',
        ]);

        $manilaNow = Carbon::now('Asia/Manila');

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make('password'),
            'user_type_id' => 2,
            'department_id' => $validated['department_id'],
            'position_id' => $validated['position_id'],
            'status_id' => $validated['status_id'],
            'phone' => $validated['phone'],
            'gender' => $validated['gender'],
            'address' => $validated['address'],
            'email_verified_at' => $manilaNow,
            'phone_verified_at' => $manilaNow,
        ]);

        return redirect()->back()->with('message', 'Employee added successfully!');
    }
}
