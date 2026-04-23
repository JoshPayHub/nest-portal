<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\PayrollCutOff;
use App\Models\SalaryPayroll;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalaryPayrollController extends Controller
{
    public function index(Request $request)
    {
        // Get the authenticated user ID from the session
        $userId = $request->user()->id;

        // Fetch cutoffs that have payroll records for THIS user
        $cutoffs = PayrollCutOff::where('status_id', 1) // Active cutoffs
            ->whereHas('salaryPayrolls', function ($query) use ($userId) {
                // Table prefix added to resolve ambiguity
                $query->where('salary_payroll.user_id', $userId);
            })
            ->with(['salaryPayrolls' => function ($query) use ($userId) {
                // Table prefix added here as well
                $query->where('salary_payroll.user_id', $userId)->with('status');
            }])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('management/Employee/SalaryPayroll', [
            'cutoffs' => $cutoffs,
            'filters' => $request->only(['search'])
        ]);
    }

    public function show(Request $request, $cutoffId)
    {
        $userId = $request->user()->id;

        $payroll = SalaryPayroll::with(['status', 'user'])
            // Prefixed for safety, even if no join is currently visible here
            ->where('salary_payroll.user_id', $userId)
            ->whereHas('attendanceEmployee', function ($q) use ($cutoffId) {
                $q->where('payroll_cut_off_id', $cutoffId);
            })
            ->firstOrFail();

        // Check if payroll is approved (assuming 7 is the 'Approved' status)
        if ($payroll->status_id !== 7) {
            return response()->json([
                'locked' => true,
                'status' => $payroll->status->name ?? 'Pending',
                'message' => 'Payroll details are hidden until approved.'
            ]);
        }

        return response()->json([
            'locked' => false,
            'payroll' => $payroll
        ]);
    }
}
