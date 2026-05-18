<?php

use App\Http\Controllers\Employee\NotificationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Route::get('/', function () {
//     return Inertia::render('Home');
// });

Route::get('/register', function () {
    return Inertia::render('Auth/Register');
});

Route::get('/forgot-password', function () {
    return Inertia::render('Auth/Forgot_Password');
});

Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');

Route::get('/storage-link', function () {
    $target = storage_path('app/public');
    $link = public_path('storage');
    if (is_link($link)) {
        return 'Symlink already exists!';
    }

    if (is_dir($link)) {
        rmdir($link);
    }

    if (symlink($target, $link)) {
        return 'Symlink created successfully!';
    }
});

require __DIR__ . '/auth.php';
require __DIR__ . '/hr.php';
require __DIR__ . '/employee.php';
require __DIR__ . '/head.php';


