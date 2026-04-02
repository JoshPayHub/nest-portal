<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\PayrollCutOff;
use App\Models\Status;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;

class PayrollCutOffController extends Controller
{
    public function index(Request $request)
    {
        $cutoffs = PayrollCutOff::join('statuses', 'payroll_cut_offs.status_id', '=', 'statuses.id')
            ->select('payroll_cut_offs.*', 'statuses.name as status_name')
            ->when($request->search, function ($query, $search) {
                $query->where('payroll_cut_offs.name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('management/HR/PayrollCutOff', [
            'cutoffs' => $cutoffs,
            'statuses' => Status::whereIn('id', [1, 2])->get(),
            'filters' => $request->only(['search'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|in:first_cutoff,second_cutoff',
            'from_cutoff_date' => 'required|date',
            'to_cutoff_date' => 'required|date|after_or_equal:from_cutoff_date',
            'status_id' => 'required|exists:statuses,id'
        ]);

        $this->checkForOverlap($validated['from_cutoff_date'], $validated['to_cutoff_date']);

        PayrollCutOff::create($validated);
        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        $cutoff = PayrollCutOff::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|in:first_cutoff,second_cutoff',
            'from_cutoff_date' => 'required|date',
            'to_cutoff_date' => 'required|date|after_or_equal:from_cutoff_date',
            'status_id' => 'required|exists:statuses,id'
        ]);

        $this->checkForOverlap($validated['from_cutoff_date'], $validated['to_cutoff_date'], $id);

        $cutoff->update($validated);
        return redirect()->back();
    }

    /**
     * Logic to prevent date overlaps
     */
    private function checkForOverlap($from, $to, $excludeId = null)
    {
        $overlap = PayrollCutOff::where(function ($query) use ($from, $to) {
            $query->whereBetween('from_cutoff_date', [$from, $to])
                  ->orWhereBetween('to_cutoff_date', [$from, $to])
                  ->orWhere(function ($q) use ($from, $to) {
                      $q->where('from_cutoff_date', '<=', $from)
                        ->where('to_cutoff_date', '>=', $to);
                  });
        })
        ->when($excludeId, function ($query) use ($excludeId) {
            $query->where('id', '!=', $excludeId);
        })
        ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'from_cutoff_date' => 'The selected date range overlaps with an existing cutoff period.'
            ]);
        }
    }
}
