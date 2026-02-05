<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use App\Models\Announcement;
use App\Models\Policies;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('management/HR/Index', [
            // Employee summary
            'employeeStats' => [
                'total' => User::where('user_type_id', 2)->count(),
                'active' => User::where('user_type_id', 2)->where('status_id', 1)->count(),
                'inactive' => User::where('user_type_id', 2)->where('status_id', 2)->count(),
            ],

            // Departments summary
            'departmentStats' => [
                'total' => Department::count(),
                'active' => Department::where('status_id', 1)->count(),
                'inactive' => Department::where('status_id', 2)->count(),
            ],

            // Positions summary
            'positionStats' => [
                'total' => Position::count(),
                'active' => Position::where('status_id', 1)->count(),
                'inactive' => Position::where('status_id', 2)->count(),
            ],

            // Announcements & Policies summary
            'announcementCount' => Announcement::count(),
            'policyCount' => Policies::count(),
        ]);
    }
}
