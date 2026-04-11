<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\SalaryEmployee;
use App\Models\Status;
use App\Models\User;
use App\Models\Department;
use App\Models\SssContribution;
use App\Models\TaxBracket;
use App\Models\DeductionSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalaryEmployeeController extends Controller
{
    public function index(Request $request)
    {
        $salaryEmployees = SalaryEmployee::with(['user.department', 'status'])
            ->whereHas('user', fn($q) => $q->where('status_id', 1))
            ->when($request->search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
                });
            })
            ->when($request->department_id, function ($query, $deptId) {
                $query->whereHas('user', fn($q) => $q->where('department_id', $deptId));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $availableEmployees = User::with('department')
            ->where('status_id', 1)
            ->whereDoesntHave('salaryEmployee')
            ->orderBy('last_name')
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'full_name' => "{$user->first_name} {$user->last_name} — " . ($user->department->name ?? 'No Dept')
            ]);

        return Inertia::render('management/HR/SalaryEmployee', [
            'salaryEmployees' => $salaryEmployees,
            'availableEmployees' => $availableEmployees,
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'statuses' => Status::whereIn('id', [1, 2])->get(['id', 'name']),
            'filters' => $request->only(['search', 'department_id']),
            'sssTable' => SssContribution::orderBy('min_salary')->get(),
            'taxTable' => TaxBracket::orderBy('min_salary')->get(),
            'deductionSettings' => DeductionSetting::pluck('value', 'key'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id|unique:salary_employees,user_id',
            'salary_amount' => 'required|numeric|min:0',
            'de_minimis' => 'nullable|numeric|min:0',
            'type' => 'required|in:monthly,daily',
            'status_id' => 'required|exists:statuses,id'
        ], [
            'user_id.unique' => 'This employee already has a salary record.'
        ]);

        SalaryEmployee::create([
            'user_id' => $validated['user_id'],
            'basic_pay' => $validated['salary_amount'], // ✅ map here
            'de_minimis' => $validated['de_minimis'] ?? 0,
            'type' => $validated['type'],
            'status_id' => $validated['status_id'],
        ]);

        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        $salary = SalaryEmployee::findOrFail($id);

        $validated = $request->validate([
            'salary_amount' => 'required|numeric|min:0',
            'de_minimis' => 'nullable|numeric|min:0',
            'type' => 'required|in:monthly,daily',
            'status_id' => 'required|exists:statuses,id'
        ]);

        $salary->update([
            'basic_pay' => $validated['salary_amount'], // ✅ map here
            'de_minimis' => $validated['de_minimis'] ?? 0,
            'type' => $validated['type'],
            'status_id' => $validated['status_id'],
        ]);

        return redirect()->back();
    }

    public function destroy($id)
    {
        SalaryEmployee::findOrFail($id)->delete();
        return redirect()->back();
    }
}
