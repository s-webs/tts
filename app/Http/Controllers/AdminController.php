<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkDay;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function reports()
    {
        $today = Carbon::today();

        // Загружаем пользователей с рабочими днями и их паузами
        $usersToday = User::with(['workDayToday.pauses'])->get();

        // Преобразуем данные для удобного отображения
        $data = $usersToday->map(function ($user) {
            $workDay = $user->workDayToday;

            $workDayDuration = null;
            $totalPauseDuration = null;
            $netWorkDayDuration = null;

            if ($workDay && $workDay->end_time) {
                // Рассчитываем продолжительность рабочего дня
                $workDayDuration = $workDay->start_time->diffInSeconds($workDay->end_time);

                // Рассчитываем продолжительность завершенных пауз
                $completedPauses = $workDay->pauses->filter(function ($pause) {
                    return $pause->end_time !== null;
                });

                $totalPauseDuration = $completedPauses->reduce(function ($carry, $pause) {
                    return $carry + $pause->start_time->diffInSeconds($pause->end_time);
                }, 0);

                // Рассчитываем чистую продолжительность рабочего дня (с учетом пауз)
                $netWorkDayDuration = gmdate("H:i:s", $workDayDuration - $totalPauseDuration);
            }

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'workDay' => $workDay ? [
                    'start_time' => $workDay->start_time,
                    'end_time' => $workDay->end_time,
                    'latitude_start' => $workDay->latitude_start,
                    'longitude_start' => $workDay->longitude_start,
                    'latitude_end' => $workDay->latitude_end,
                    'longitude_end' => $workDay->longitude_end,
                    'duration_workday' => $netWorkDayDuration,
                    'pauses' => $workDay->pauses->map(function ($pause) {
                        if ($pause->end_time === null) {
                            return null; // Возвращаем null для паузы без end_time
                        }

                        return [
                            'start_time' => $pause->start_time,
                            'end_time' => $pause->end_time,
                            'latitude_start' => $pause->latitude_start,
                            'longitude_start' => $pause->longitude_start,
                            'latitude_end' => $pause->latitude_end,
                            'longitude_end' => $pause->longitude_end,
                            'duration' => gmdate('H:i:s', $pause->start_time->diffInSeconds($pause->end_time)),
                        ];
                    })->filter(), // Убираем null из итогового массива пауз
                    'duration_pauses' => $totalPauseDuration ? gmdate('H:i:s', $totalPauseDuration) : '-',
                ] : null,
            ];
        });

        // Передаем данные в Vue-компонент
        return Inertia::render('Admin/Reports/Index', [
            'usersToday' => $data,
        ]);
    }



    public function getReports(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        $users = User::all(); // Получаем всех пользователей
        $dates = [];
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            $dates[] = $date->copy();
        }

        $reports = [];
        foreach ($dates as $date) {
            $dailyData = [
                'date' => $date->format('Y-m-d'),
                'users' => $users->map(function ($user) use ($date) {
                    $workDay = $user->workDays()
                        ->whereDate('start_time', $date)
                        ->first();

                    $completedPauses = collect(); // Инициализация как пустая коллекция
                    $workDayDuration = null;
                    $totalPauseDuration = null;
                    $netWorkDayDuration = null;

                    if ($workDay && $workDay->end_time) {
                        // Рассчитываем продолжительность рабочего дня
                        $workDayDuration = $workDay->start_time->diffInSeconds($workDay->end_time);

                        // Фильтруем завершенные паузы
                        $completedPauses = $workDay->pauses->filter(function ($pause) {
                            return $pause->end_time !== null;
                        });

                        // Рассчитываем общую продолжительность пауз
                        $totalPauseDuration = $completedPauses->reduce(function ($carry, $pause) {
                            return $carry + $pause->start_time->diffInSeconds($pause->end_time);
                        }, 0);

                        // Рассчитываем чистую продолжительность рабочего дня
                        $netWorkDayDuration = gmdate("H:i:s", $workDayDuration - $totalPauseDuration);
                    }

                    return [
                        'name' => $user->name,
                        'work_day_today' => $workDay ? [
                            'start_time' => $workDay->start_time,
                            'end_time' => $workDay->end_time,
                            'longitude_start' => $workDay->longitude_start,
                            'latitude_start' => $workDay->latitude_start,
                            'longitude_end' => $workDay->longitude_end,
                            'latitude_end' => $workDay->latitude_end,
                            'duration_workday' => isset($netWorkDayDuration) ? $netWorkDayDuration : '-',
                            'duration_pauses' => isset($totalPauseDuration) ? gmdate('H:i:s', $totalPauseDuration) : '-',
                            'pauses' => $completedPauses->map(function ($pause) {
                                return [
                                    'start_time' => $pause->start_time,
                                    'end_time' => $pause->end_time,
                                    'latitude_start' => $pause->latitude_start,
                                    'longitude_start' => $pause->longitude_start,
                                    'latitude_end' => $pause->latitude_end,
                                    'longitude_end' => $pause->longitude_end,
                                    'duration' => gmdate('H:i:s', $pause->start_time->diffInSeconds($pause->end_time)),
                                ];
                            }),
                        ] : null,
                    ];
                }),
            ];
            $reports[] = $dailyData;
        }

        return response()->json($reports);
    }



    public function getEmployeeReport(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        $employeeId = $request->employee_id;

        // Получаем сотрудника
        $employee = User::find($employeeId);

        // Получаем диапазон дат
        $period = CarbonPeriod::create($startDate, $endDate);

        // Формируем отчет
        $report = collect($period)->map(function ($date) use ($employee) {
            // Ищем рабочий день для данной даты
            $workDay = $employee->workDays()->whereDate('start_time', $date)->first();

            $completedPauses = collect(); // Инициализируем как пустую коллекцию по умолчанию
            $workDayDuration = null;
            $totalPauseDuration = null;
            $netWorkDayDuration = null;

            if ($workDay) {
                $startTime = $workDay->start_time;
                $endTime = $workDay->end_time;

                // Рассчитываем продолжительность рабочего дня
                if ($startTime && $endTime) {
                    $workDayDuration = $startTime->diffInSeconds($endTime);

                    // Фильтруем завершенные паузы
                    $completedPauses = $workDay->pauses->filter(function ($pause) {
                        return $pause->end_time !== null;
                    });

                    // Рассчитываем общую продолжительность пауз
                    $totalPauseDuration = $completedPauses->reduce(function ($carry, $pause) {
                        return $carry + $pause->start_time->diffInSeconds($pause->end_time);
                    }, 0);

                    // Рассчитываем чистую продолжительность рабочего дня
                    $netWorkDayDuration = $workDayDuration - $totalPauseDuration;
                }

                return [
                    'date' => $date->toDateString(),
                    'name' => $employee->name,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'work_duration' => $workDayDuration ? gmdate("H:i:s", $workDayDuration) : '-',
                    'duration_pauses' => $totalPauseDuration ? gmdate("H:i:s", $totalPauseDuration) : '-',
                    'duration_workDay' => $netWorkDayDuration ? gmdate("H:i:s", $netWorkDayDuration) : '-',
                    'latitude_start' => $workDay->latitude_start,
                    'longitude_start' => $workDay->longitude_start,
                    'latitude_end' => $workDay->latitude_end,
                    'longitude_end' => $workDay->longitude_end,
                    'pauses' => $completedPauses->map(function ($pause) {
                        return [
                            'start_time' => $pause->start_time,
                            'end_time' => $pause->end_time,
                            'latitude_start' => $pause->latitude_start,
                            'longitude_start' => $pause->longitude_start,
                            'latitude_end' => $pause->latitude_end,
                            'longitude_end' => $pause->longitude_end,
                            'duration' => gmdate("H:i:s", $pause->start_time->diffInSeconds($pause->end_time)),
                        ];
                    }),
                ];
            }

            // Если рабочий день отсутствует
            return [
                'date' => $date->toDateString(),
                'name' => $employee->name,
                'start_time' => null,
                'end_time' => null,
                'work_duration' => '-',
                'duration_pauses' => '-',
                'duration_workDay' => '-',
                'latitude_start' => null,
                'longitude_start' => null,
                'latitude_end' => null,
                'longitude_end' => null,
                'pauses' => [],
            ];
        });

        return response()->json($report);
    }

    public function getAllUsers()
    {
        $users = User::select('id', 'name', 'email')->get();
        return response()->json($users);
    }
}
