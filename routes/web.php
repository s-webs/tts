<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [\App\Http\Controllers\GuestController::class, 'index']);

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
});
