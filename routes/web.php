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

    $info = [
        'target_path' => $target,
        'link_path' => $link,
        'target_exists' => file_exists($target),
        'link_is_symlink' => is_link($link),
        'link_is_dir' => is_dir($link),
        'link_exists' => file_exists($link),
    ];

    if (is_link($link)) {
        return response()->json(array_merge($info, ['status' => '⚠️ Symlink already exists!']));
    }

    if (is_dir($link)) {
        rmdir($link);
    }

    $result = symlink($target, $link);

    return response()->json(array_merge($info, [
        'status' => $result ? '✅ Symlink created!' : '❌ symlink() failed',
        'symlink_enabled' => function_exists('symlink'),
    ]));
});

require __DIR__ . '/auth.php';
require __DIR__ . '/hr.php';
require __DIR__ . '/employee.php';
require __DIR__ . '/head.php';


