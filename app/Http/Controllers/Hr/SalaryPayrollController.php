<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\PayrollCutOff;
use App\Models\SalaryPayroll;
use App\Models\Status;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalaryPayrollController extends Controller
{
    public function index(Request $request)
    {
        $cutoffs = PayrollCutOff::where('payroll_cut_offs.status_id', 1)
            ->join('statuses', 'payroll_cut_offs.status_id', '=', 'statuses.id')
            ->select('payroll_cut_offs.*', 'statuses.name as status_name')
            ->withCount(['salaryPayrolls as pending_count' => function ($query) {
                $query->where('status_id', 4);
            }])
            ->withCount(['salaryPayrolls as approved_count' => function ($query) {
                $query->where('status_id', 7);
            }])
            ->when($request->search, function ($query, $search) {
                $query->where('payroll_cut_offs.name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('management/HR/SalaryPayroll', [
            'cutoffs' => $cutoffs,
            'filters' => $request->only(['search'])
        ]);
    }

    public function list(Request $request, $id)
    {
        $cutoff = PayrollCutOff::findOrFail($id);

        $payrolls = SalaryPayroll::whereHas('attendanceEmployee', function ($query) use ($id) {
                $query->where('payroll_cut_off_id', $id);
            })
            ->with(['user.salaryEmployee', 'department', 'position', 'status']) // Added salaryEmployee for rates
            ->when($request->search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('management/HR/SalaryPayrollList', [
            'cutoff' => $cutoff,
            'payrolls' => $payrolls,
            'filters' => $request->only(['search'])
        ]);
    }

    public function update(Request $request, $id)
{
    $payroll = SalaryPayroll::findOrFail($id);

    // Strict Rule: Once approved, no more edits
    if ($payroll->status_id === 7) {
        return back()->with('error', 'Approved payrolls are locked and cannot be edited.');
    }

    $validated = $request->validate([
        'status_id' => 'required|integer',
        'regular_pay' => 'numeric',
        'absence_with_pay' => 'numeric',
        'regular_ot' => 'numeric',
        'rdot' => 'numeric',
        'regular_holiday_ot' => 'numeric',
        'special_holiday_ot' => 'numeric',
        'rd_regular_holiday_ot' => 'numeric',
        'rd_special_holiday_ot' => 'numeric',
        'night_differential' => 'numeric',
        'regular_holiday' => 'numeric',
        'special_holiday' => 'numeric',
        'rd_regular_holiday' => 'numeric',
        'rd_special_holiday' => 'numeric',
        'adjustment' => 'numeric',
        'allowance' => 'numeric',
        'sss' => 'numeric',
        'pag_ibig' => 'numeric',
        'philhealth' => 'numeric',
        'tax' => 'numeric',
        'salary_loan' => 'numeric',
        'cash_advance' => 'numeric',
        'undertime' => 'numeric',
        'absence_without_pay' => 'numeric',
        'flu_vaccine' => 'numeric',
        'food' => 'numeric',
        'total_earning' => 'required|numeric',
        'total_deduction' => 'required|numeric',
        'total_home_pay' => 'required|numeric',
    ]);

    $payroll->update($validated);

    return back()->with('success', 'Payroll status and values updated.');
}
}
