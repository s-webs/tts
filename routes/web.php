<?php

use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [\App\Http\Controllers\GuestController::class, 'index']);

Route::get('/forgot-password', function () {
    abort(404);
});
Route::post('/forgot-password', function () {
    abort(404);
});
Route::get('/register', function () {
    abort(404);
});
Route::post('/register', function () {
    abort(404);
});
Route::get('/reset-password', function () {
    abort(404);
});
Route::post('/reset-password/{token}', function () {
    abort(404);
});

Route::get('/two-factor-challenge', function () {
    abort(404);
});

Route::post('/two-factor-challenge', function () {
    abort(404);
});

Route::delete('/user', function () {
    abort(404);
});
Route::get('user/confirm-password', function () {
    abort(404);
});
Route::post('user/confirm-password', function () {
    abort(404);
});

Route::get('/user/confirmed-password-status', function () {
    abort(404);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\WorkDayController::class, 'index'])->name('dashboard');

    Route::get('/time-entries', [\App\Http\Controllers\WorkDayController::class, 'index'])->name('time-entries.index');

    Route::post('/api/time-entries/start-work', [\App\Http\Controllers\WorkDayController::class, 'startWork'])->name('time-entries.startWork');
    Route::post('/api/time-entries/end-work', [\App\Http\Controllers\WorkDayController::class, 'endWork'])->name('time-entries.endWork');
    Route::post('/api/time-entries/start-pause', [\App\Http\Controllers\WorkDayController::class, 'startPause'])->name('time-entries.startPause');
    Route::post('/api/time-entries/end-pause', [\App\Http\Controllers\WorkDayController::class, 'endPause'])->name('time-entries.endPause');
    Route::get('/api/time-entries/day-summary', [\App\Http\Controllers\WorkDayController::class, 'getDaySummary']);

    Route::middleware(['role:admin'])->group(function () {
        Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
        Route::post('/users', [\App\Http\Controllers\UserController::class, 'store'])->name('users.store'); // Добавление нового пользователя
        Route::put('/users/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('users.update'); // Обновление пользователя
        Route::delete('/users/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy'); // Удаление пользователя
        Route::get('/reports', [\App\Http\Controllers\AdminController::class, 'reports'])->name('reports.index');
        Route::get('/api/reports', [\App\Http\Controllers\AdminController::class, 'getReports'])->name('reports.getReports');
        Route::get('/api/reports/employee', [\App\Http\Controllers\AdminController::class, 'getEmployeeReport']);
        Route::get('/api/users', [\App\Http\Controllers\AdminController::class, 'getAllUsers']);
    });
});
