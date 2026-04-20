<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\DeductionSetting;
use App\Models\SssContribution;
use App\Models\TaxBracket;

class SalaryDeductionsController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('management/HR/SalaryDeductions', [
            'sss' => SssContribution::orderBy('min_salary')->get(),
            'tax' => TaxBracket::orderBy('min_salary')->get(),
            'settings' => DeductionSetting::all(),
            'activeTab' => $request->query('tab', 'sss'),
        ]);
    }

    public function updateSetting(Request $request, $id)
    {
        DeductionSetting::findOrFail($id)
            ->update($request->only('value'));

        return back();
    }

    public function updateSSS(Request $request, $id)
    {
        SssContribution::findOrFail($id)->update([
            'min_salary' => $request->min_salary,
            'max_salary' => $request->max_salary,
            'msc' => $request->msc,
            'ee_share' => $request->ee_share,
            'er_share' => $request->er_share,
            'wisp_ee' => $request->wisp_ee,
            'wisp_er' => $request->wisp_er,
            'ec_er' => $request->ec_er,
        ]);

        return back();
    }

    public function updateTax(Request $request, $id)
    {
        TaxBracket::findOrFail($id)->update([
            'min_salary' => $request->min_salary,
            'max_salary' => $request->max_salary,
            'base_tax' => $request->base_tax,
            'excess_rate' => $request->excess_rate,
            'over_amount' => $request->over_amount,
        ]);

        return back();
    }
}
