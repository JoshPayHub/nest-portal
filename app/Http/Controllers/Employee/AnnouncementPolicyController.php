<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\AnnouncementPolicy;
use App\Models\Status;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnnouncementPolicyController extends Controller
{
    public function index(Request $request)
    {
        $employeeDepartmentId = auth()->user()->department_id;

        $query = AnnouncementPolicy::with(['status', 'filters'])
            // Only pull Active/Published records for employees
            ->whereHas('status', function ($q) {
                $q->where('name', 'Active');
            })
            ->when($request->search, function ($q, $search) {
                $q->where('title', 'like', "%{$search}%");
            })
            ->when($request->tab && $request->tab !== 'all', function ($q) use ($request) {
                $q->where('types', $request->tab);
            })
            // Target Department logic: check if department matches employee OR is null (All Departments)
            ->where(function ($q) use ($employeeDepartmentId) {
                $q->whereDoesntHave('filters') // No filters means All Departments
                  ->orWhereHas('filters', function ($fQuery) use ($employeeDepartmentId) {
                      $fQuery->whereNull('department_id')
                             ->orWhere('department_id', $employeeDepartmentId);
                  });
            })
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString()
            ->through(function ($item) {
                return [
                    'id' => $item->id,
                    'types' => $item->types,
                    'title' => $item->title,
                    'description' => $item->description,
                    'status_name' => $item->status ? $item->status->name : 'Unknown',
                    'created_at' => $item->created_at->format('F j, Y'),
                ];
            });

        return Inertia::render('management/Employee/AnnouncementAndPolicy', [
            'data' => $query,
            'statuses' => Status::whereIn('id', [1, 2])->get(),
            'filters' => $request->only(['search', 'tab']),
            'auth_user_type_id' => auth()->user()->user_type_id,
        ]);
    }
}
