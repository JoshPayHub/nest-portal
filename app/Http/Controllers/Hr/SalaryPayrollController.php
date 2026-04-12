<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
class SalaryPayrollController extends Controller
{
    public function index(Request $request)
    {

        return Inertia::render('management/HR/SalaryPayroll');
    }
}
