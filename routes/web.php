<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
});



Route::get('/login', function () {
    return Inertia::render('Auth/Login');
});

Route::get('/register', function () {
    return Inertia::render('Auth/Register');
});

Route::get('/forgot-password', function () {
    return Inertia::render('Auth/Forgot_Password');
});

Route::get('/management', function () {
    return Inertia::render('management/Dashboard');
});

Route::get('/management/AnnouncementAndPolicy', function () {
    return Inertia::render('management/AnnouncementAndPolicy');
});

Route::get('/management/Employees', function () {
    return Inertia::render('management/Employees');
});

Route::get('/management/Recruitment', function () {
    return Inertia::render('management/Recruitment');
});

Route::get('/management/AttendanceLeave', function () {
    return Inertia::render('management/AttendanceLeave');
});

Route::get('/management/TrainingDevelopment', function () {
    return Inertia::render('management/TrainingDevelopment');
});

Route::get('/management/MedicalWellness', function () {
    return Inertia::render('management/MedicalWellness');
});

Route::get('/management/DisciplineCases', function () {
    return Inertia::render('management/DisciplineCases');
});

Route::get('/management/Reports', function () {
    return Inertia::render('management/Reports');
});

Route::get('/management/Notification', function () {
    return Inertia::render('management/Notification');
});

Route::get('/management/register', function () {
    return Inertia::render('management/Register');
});
