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

        // Получить текущий рабочий день
        $today = now()->startOfDay();
        $currentWorkDay = $user->workDays()
            ->where('start_time', '>=', $today)
            ->with('pauses')
            ->first();

        return Inertia::render('Worker/Index', [
            'currentWorkDay' => $currentWorkDay,
        ]);
    }


    /**
     * Начать рабочий день
     */
    public function startWork(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $today = now()->startOfDay();
        $activeWorkDay = $user->workDays()
            ->where('start_time', '>=', $today)
            ->first();

        if ($activeWorkDay) {
            return response()->json(['message' => 'alreadyStartedToday'], 400);
        }

        $newWorkDay = WorkDay::create([
            'user_id' => $user->id,
            'start_time' => now(),
            'latitude_start' => $request->latitude,
            'longitude_start' => $request->longitude,
        ]);

        return response()->json([
            'message' => 'workingDayStarted',
            'workDay' => $newWorkDay,
        ]);
    }



    /**
     * Закончить рабочий день
     */
    public function endWork(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $activeWorkDay = $user->workDays()->whereNull('end_time')->first();

        if (!$activeWorkDay) {
            return response()->json(['message' => 'dontStartedWorkingDay'], 400);
        }

        $activePause = $activeWorkDay->pauses()->whereNull('end_time')->first();

        if ($activePause) {
            return response()->json(['message' => 'finishCurrentBreakBefore'], 400);
        }

        $activeWorkDay->update([
            'end_time' => now(),
            'latitude_end' => $request->latitude,
            'longitude_end' => $request->longitude,
        ]);

        return response()->json(['message' => 'workingDayOver', 'workDay' => $activeWorkDay]);
    }


    /**
     * Начать перерыв
     */
    public function startPause(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $activeWorkDay = $user->workDays()->whereNull('end_time')->first();

        if (!$activeWorkDay) {
            return response()->json(['message' => 'dontStartedWorkingDay'], 400);
        }

        $activePause = $activeWorkDay->pauses()->whereNull('end_time')->first();

        if ($activePause) {
            return response()->json(['message' => 'youAlreadyOnBreak'], 400);
        }

        $pause = Pause::create([
            'work_day_id' => $activeWorkDay->id,
            'start_time' => now(),
            'latitude_start' => $request->latitude,
            'longitude_start' => $request->longitude,
        ]);

        return response()->json(['message' => 'breakStarted', 'pause' => $pause]);
    }


    /**
     * Закончить перерыв
     */
    public function endPause(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $activeWorkDay = $user->workDays()->whereNull('end_time')->first();

        if (!$activeWorkDay) {
            return response()->json(['message' => 'dontStartedWorkingDay'], 400);
        }

        $activePause = $activeWorkDay->pauses()->whereNull('end_time')->first();

        if (!$activePause) {
            return response()->json(['message' => 'youNotBreak'], 400);
        }

        $activePause->update([
            'end_time' => now(),
            'latitude_end' => $request->latitude,
            'longitude_end' => $request->longitude,
        ]);

        return response()->json(['message' => 'breakIsOver', 'pause' => $activePause]);
    }


    public function getDaySummary()
    {
        $user = Auth::user();
        $today = now()->startOfDay();

        // Получаем текущий рабочий день
        $workDay = $user->workDays()
            ->where('start_time', '>=', $today)
            ->with('pauses')
            ->first();

        if (!$workDay) {
            return response()->json([
                'message' => 'thereNotDataCurrentDay',
                'actions' => []
            ]);
        }

        // Формируем список действий
        $actions = [];

        // Добавляем начало рабочего дня
        $actions[] = [
            'type' => 'theBeginningWorkingDay',
            'time' => $workDay->start_time,
        ];

        // Добавляем перерывы с их продолжительностью
        foreach ($workDay->pauses as $pause) {
            $actions[] = [
                'type' => 'theBeginningBreak',
                'time' => $pause->start_time,
            ];
            if ($pause->end_time) {
                $actions[] = [
                    'type' => 'endOfBreak',
                    'time' => $pause->end_time,
                    'duration' => gmdate('H:i:s', $pause->start_time->diffInSeconds($pause->end_time)), // Возвращаем отформатированное время
                ];
            }
        }

        // Добавляем окончание рабочего дня
        if ($workDay->end_time) {
            $actions[] = [
                'type' => 'endOfWorkingDay',
                'time' => $workDay->end_time,
            ];
        }

        // Сортируем действия по времени
        usort($actions, fn($a, $b) => strtotime($a['time']) <=> strtotime($b['time']));

        return response()->json([
            'message' => 'daySummary',
            'actions' => $actions,
        ]);
    }

}
