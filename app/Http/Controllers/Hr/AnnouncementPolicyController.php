<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\AnnouncementPolicy;
use App\Models\AnnouncementPolicyFilter;
use App\Models\Department;
use App\Models\Position;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AnnouncementPolicyController extends Controller
{
  public function index(Request $request)
{
    $query = AnnouncementPolicy::with(['status', 'filters.department', 'filters.position'])
        ->when($request->search, function ($query, $search) {
            $query->where('title', 'like', "%{$search}%");
        })
        ->when($request->tab && $request->tab !== 'all', function ($query) use ($request) {
            $query->where('types', $request->tab);
        })
        ->when($request->status, function ($query, $status) {
            $query->where('status_id', $status);
        })
        ->latest('announcements_policies.created_at')
        ->paginate(10)
        ->withQueryString()
        ->through(function ($item) {
            // Everything is handled here in one go
            return [
                'id' => $item->id,
                'types' => $item->types,
                'title' => $item->title,
                'description' => $item->description,
                'status_id' => $item->status_id,
                // Accessing the object here works fine because $item is still a Model
                'status' => $item->status ? $item->status->name : 'No Status',
                'status_name' => $item->status ? $item->status->name : 'Unknown',
                'filters' => $item->filters,
                'created_at' => $item->created_at->format('F j, Y'),
            ];
        });

    return Inertia::render('management/HR/AnnouncementAndPolicy', [
        'data' => $query,
        'departments' => Department::all(),
        'positions' => Position::all(),
        'statuses' => Status::whereIn('id', [1, 2])->get(),
        'filters' => $request->only(['search', 'tab', 'status'])
    ]);
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'types' => 'required|in:announcements,policies',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status_id' => 'required|integer',
            'selected_departments' => 'nullable|array',
            'selected_positions' => 'nullable|array',
        ]);

        DB::transaction(function () use ($validated) {
            $record = AnnouncementPolicy::create([
                'types' => $validated['types'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'status_id' => $validated['status_id'],
            ]);

            $this->saveFilters($record->id, $validated);
        });

        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'types' => 'required|string',
            'description' => 'nullable|string',
            'status_id' => 'required|exists:statuses,id',
            'selected_departments' => 'nullable|array',
            'selected_positions' => 'nullable|array',
        ]);

        DB::transaction(function () use ($validated, $id) {
            $record = AnnouncementPolicy::findOrFail($id);
            $record->update([
                'types' => $validated['types'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'status_id' => $validated['status_id'],
            ]);

            // Clean and re-save filters to keep it consistent with store
            AnnouncementPolicyFilter::where('announcement_policy_id', $id)->delete();
            $this->saveFilters($id, $validated);
        });

        return redirect()->back();
    }

    private function saveFilters($recordId, $validated)
    {
        $deptIds = !empty($validated['selected_departments']) ? $validated['selected_departments'] : [null];
        $posIds = !empty($validated['selected_positions']) ? $validated['selected_positions'] : [null];

        foreach ($deptIds as $dId) {
            foreach ($posIds as $pId) {
                AnnouncementPolicyFilter::create([
                    'announcement_policy_id' => $recordId,
                    'department_id' => $dId,
                    'position_id' => $pId,
                ]);
            }
        }
    }
}
