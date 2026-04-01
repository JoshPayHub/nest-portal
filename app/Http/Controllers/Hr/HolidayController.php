<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\Status;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $holidays = Holiday::join('statuses', 'holidays.status_id', '=', 'statuses.id')
            ->select('holidays.*', 'statuses.name as status_name')
            ->when($request->search, function ($query, $search) {
                $query->where('holidays.name', 'like', "%{$search}%");
            })
            ->latest('holidays.created_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('management/HR/Holiday', [
            'holidays' => $holidays,
            'statuses' => Status::whereIn('id', [1, 2])->get(),
            'filters' => $request->only(['search'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:holidays,name',
            'type' => 'required|string|in:regular,special',
            'date' => 'required|date|unique:holidays,date',
            'status_id' => 'required|exists:statuses,id'
        ]);

        Holiday::create($validated);

        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        $holiday = Holiday::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:holidays,name,' . $id,
            'type' => 'required|string|in:regular,special',
            'date' => 'required|date|unique:holidays,date,' . $id,
            'status_id' => 'required|exists:statuses,id'
        ]);

        $holiday->update($validated);

        return redirect()->back();
    }
}
