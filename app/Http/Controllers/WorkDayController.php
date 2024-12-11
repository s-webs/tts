<?php

namespace App\Http\Controllers;

use App\Models\Pause;
use App\Models\WorkDay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class WorkDayController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Получение текущего рабочего дня или создание нового
        $currentWorkDay = $user->workDays()
            ->whereNull('end_time')
            ->with('pauses')
            ->first();

        // Получение истории рабочих дней
        $workDays = $user->workDays()
            ->with('pauses')
            ->orderBy('start_time', 'desc')
            ->get()
            ->map(function ($workDay) {
                return [
                    'id' => $workDay->id,
                    'start_time' => $workDay->start_time,
                    'end_time' => $workDay->end_time,
                    'total_work_time' => $workDay->total_work_time,
                    'total_pause_time' => $workDay->total_pause_time,
                ];
            });

        return Inertia::render('Worker/Index', compact('workDays', 'currentWorkDay'));
    }

    /**
     * Начать рабочий день
     */
    public function startWork()
    {
        $user = Auth::user();

        // Проверка, не начат ли уже рабочий день
        $activeWorkDay = $user->workDays()->whereNull('end_time')->first();

        if ($activeWorkDay) {
            return response()->json(['message' => 'Вы уже начали рабочий день.'], 400);
        }

        // Создание нового рабочего дня
        WorkDay::create([
            'user_id' => $user->id,
            'start_time' => now(),
        ]);

        return response()->json(['message' => 'Рабочий день начат.'], 200);
    }

    /**
     * Закончить рабочий день
     */
    public function endWork()
    {
        $user = Auth::user();

        $activeWorkDay = $user->workDays()->whereNull('end_time')->first();

        if (!$activeWorkDay) {
            return response()->json(['message' => 'Вы не начали рабочий день.'], 400);
        }

        // Проверка, не находитесь ли вы на перерыве
        $activePause = $activeWorkDay->pauses()->whereNull('end_time')->first();

        if ($activePause) {
            return response()->json(['message' => 'Закончите текущий перерыв перед завершением рабочего дня.'], 400);
        }

        // Завершение рабочего дня
        $activeWorkDay->update([
            'end_time' => now(),
        ]);

        return response()->json(['message' => 'Рабочий день завершен.'], 200);
    }

    /**
     * Начать перерыв
     */
    public function startPause()
    {
        $user = Auth::user();

        $activeWorkDay = $user->workDays()->whereNull('end_time')->first();

        if (!$activeWorkDay) {
            return response()->json(['message' => 'Вы не начали рабочий день.'], 400);
        }

        // Проверка, не начат ли уже перерыв
        $activePause = $activeWorkDay->pauses()->whereNull('end_time')->first();

        if ($activePause) {
            return response()->json(['message' => 'Вы уже находитесь на перерыве.'], 400);
        }

        // Начало нового перерыва
        Pause::create([
            'work_day_id' => $activeWorkDay->id,
            'start_time' => now(),
        ]);

        return response()->json(['message' => 'Перерыв начат.'], 200);
    }

    /**
     * Закончить перерыв
     */
    public function endPause()
    {
        $user = Auth::user();

        $activeWorkDay = $user->workDays()->whereNull('end_time')->first();

        if (!$activeWorkDay) {
            return response()->json(['message' => 'Вы не начали рабочий день.'], 400);
        }

        $activePause = $activeWorkDay->pauses()->whereNull('end_time')->first();

        if (!$activePause) {
            return response()->json(['message' => 'Вы не находитесь на перерыве.'], 400);
        }

        // Завершение перерыва
        $activePause->update([
            'end_time' => now(),
        ]);

        return response()->json(['message' => 'Перерыв завершен.'], 200);
    }
}
