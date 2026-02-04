<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Status;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    public function index()
    {
        return Inertia::render('management/HR/Department', [
            'departments' => Department::join('statuses', 'departments.status_id', '=', 'statuses.id')
                ->select('departments.*', 'statuses.name as status_name')
                ->get(),
            'statuses' => Status::whereIn('id', [1, 2])->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'status_id' => 'required|integer'
        ]);

        Department::create($validated);

        return redirect()->back()->with('message', 'Department added successfully!');
    }

    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $id,
            'status_id' => 'required|integer'
        ]);

        $department->update($validated);

        return redirect()->back()->with('message', 'Department updated successfully!');
    }
}
