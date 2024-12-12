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
    public function startWork()
    {
        $user = Auth::user();

        // Проверка, существует ли рабочий день, начатый сегодня
        $today = now()->startOfDay();
        $activeWorkDay = $user->workDays()
            ->where('start_time', '>=', $today)
            ->first();

        if ($activeWorkDay) {
            return response()->json(['message' => 'Вы уже начали рабочий день сегодня.'], 400);
        }

        // Создание нового рабочего дня
        $newWorkDay = WorkDay::create([
            'user_id' => $user->id,
            'start_time' => now(),
        ]);

        return response()->json([
            'message' => 'Рабочий день начат.',
            'workDay' => $newWorkDay,
        ]);
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
                'message' => 'Нет данных за текущий день.',
                'actions' => []
            ]);
        }

        // Формируем список действий
        $actions = [];

        // Добавляем начало рабочего дня
        $actions[] = [
            'type' => 'Начало рабочего дня',
            'time' => $workDay->start_time,
        ];

        // Добавляем перерывы с их продолжительностью
        foreach ($workDay->pauses as $pause) {
            $actions[] = [
                'type' => 'Начало перерыва',
                'time' => $pause->start_time,
            ];
            if ($pause->end_time) {
                $actions[] = [
                    'type' => 'Конец перерыва',
                    'time' => $pause->end_time,
                    'duration' => gmdate('H:i:s', $pause->start_time->diffInSeconds($pause->end_time)), // Возвращаем отформатированное время
                ];
            }
        }

        // Добавляем окончание рабочего дня
        if ($workDay->end_time) {
            $actions[] = [
                'type' => 'Окончание рабочего дня',
                'time' => $workDay->end_time,
            ];
        }

        // Сортируем действия по времени
        usort($actions, fn($a, $b) => strtotime($a['time']) <=> strtotime($b['time']));

        return response()->json([
            'message' => 'Сводка за день.',
            'actions' => $actions,
        ]);
    }

}
