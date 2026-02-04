<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\Status;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PositionController extends Controller
{
    public function index()
    {
        return Inertia::render('management/HR/Position', [
            'positions' => Position::join('statuses', 'positions.status_id', '=', 'statuses.id')
                ->select('positions.*', 'statuses.name as status_name')
                ->get(),
            'statuses' => Status::whereIn('id', [1, 2])->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:positions,name',
            'status_id' => 'required|integer'
        ]);

        Position::create($validated);

        return redirect()->back()->with('message', 'Position added successfully!');
    }

    public function update(Request $request, $id)
    {
        $position = Position::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:positions,name,' . $id,
            'status_id' => 'required|integer'
        ]);

        $position->update($validated);

        return redirect()->back()->with('message', 'Position updated successfully!');
    }
}
