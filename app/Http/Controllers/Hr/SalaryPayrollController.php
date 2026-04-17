<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\PayrollCutOff;
use App\Models\SalaryPayroll;
use App\Models\Status;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

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

    $payrolls = SalaryPayroll::with(['user.department', 'status'])
        ->whereHas('attendanceEmployee', function ($query) use ($id) {
            $query->where('payroll_cut_off_id', $id);
        })
        ->whereHas('user', fn($q) => $q->where('status_id', 1))
        
        // 1. Search Filter
        ->when($request->search, function ($query, $search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where(fn($sub) => 
                    $sub->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                );
            });
        })

        // 2. Department Filter
        ->when($request->department_id, function ($query, $deptId) {
            $query->whereHas('user', fn($q) => $q->where('department_id', $deptId));
        })

        // 3. Employee Filter (THIS WAS MISSING)
        ->when($request->user_id, function ($query, $userId) {
            $query->where('user_id', $userId);
        })

        // 4. Status Filter
        ->when($request->status_id, function ($query, $statusId) {
            $query->where('status_id', $statusId);
        })

        ->latest()
        ->paginate(10)
        ->withQueryString();

    $employees = User::where('status_id', 1)
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'department_id']);

    return inertia('management/HR/SalaryPayrollList', [
        'cutoff' => $cutoff,
        'payrolls' => $payrolls,
        'employees' => $employees,
        'departments' => Department::orderBy('name')->get(['id', 'name']),
        'filters' => $request->only(['search', 'department_id', 'user_id', 'status_id']), // Added user_id here
    ]);
}
    /* =========================
       UPDATE PAYROLL
    ========================= */
    public function update(Request $request, $id)
    {
        $payroll = SalaryPayroll::findOrFail($id);

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

    /* =========================
       EXPORT APPROVED PAYROLL (PDF)
       ONLY STATUS = 7
    ========================= */
    public function export(Request $request, $id)
    {
        $cutoff = PayrollCutOff::findOrFail($id);

        $approvedPayrolls = SalaryPayroll::whereHas('attendanceEmployee', function ($query) use ($id) {
                $query->where('payroll_cut_off_id', $id);
            })
            ->with(['user', 'status'])
            ->where('status_id', 7)
            ->get();

        if ($approvedPayrolls->isEmpty()) {
            return back()->with('error', 'No approved payrolls to export.');
        }

        $pdf = Pdf::loadView('pdf.salary-payroll-approved', [
            'cutoff' => $cutoff,
            'payrolls' => $approvedPayrolls
        ]);

        return $pdf->download(
            'approved-payroll-' . $cutoff->id . '.pdf'
        );
    }
}
