<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class ChangeOffController extends Controller
{
    public function index()
    {
        return Inertia::render('management/Employee/ChangeOff');
    }
}
