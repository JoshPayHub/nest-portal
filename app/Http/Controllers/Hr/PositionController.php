<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\Status;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PositionController extends Controller
{
    public function index(Request $request)
    {
        $positions = Position::join('statuses', 'positions.status_id', '=', 'statuses.id')
            ->select('positions.*', 'statuses.name as status_name')
            ->when($request->search, function ($query, $search) {
                $query->where('positions.name', 'like', "%{$search}%");
            })
            ->latest('positions.created_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('management/HR/Position', [
            'positions' => $positions,
            'statuses' => Status::whereIn('id', [1, 2])->get(),
            'filters' => $request->only(['search'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:positions,name',
            'status_id' => 'required|integer'
        ]);

        Position::create($validated);
        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        $position = Position::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:positions,name,' . $id,
            'status_id' => 'required|integer'
        ]);

        $position->update($validated);
        return redirect()->back();
    }
}
