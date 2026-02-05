<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Policies;
use App\Models\Department;
use App\Models\Status;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnnouncementPolicyController extends Controller
{
    public function index()
    {
        return Inertia::render('management/HR/AnnouncementAndPolicy', [
            'announcements' => Announcement::with(['department', 'status'])->latest()->get(),
            'policies' => Policies::with(['department', 'status'])->latest()->get(),
            'departments' => Department::all(),
            'statuses' => Status::all(),
        ]);
    }

   public function store(Request $request)
{
    $validated = $request->validate([
        'type' => 'required|in:announcement,policy',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'department_id' => 'required|exists:departments,id', // single department
        'status_id' => 'required|exists:statuses,id',
    ]);

    if ($request->type === 'announcement') {
        Announcement::create($validated);
    } else {
        Policies::create($validated);
    }

    return redirect()->back();
}

public function update(Request $request, $id)
{
    $validated = $request->validate([
        'type' => 'required|in:announcement,policy',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'department_id' => 'required|exists:departments,id', // single department
        'status_id' => 'required|exists:statuses,id',
    ]);

    $model = $request->type === 'announcement'
        ? Announcement::findOrFail($id)
        : Policies::findOrFail($id);

    $model->update($validated);

    return redirect()->back();
}

}
