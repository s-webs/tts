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

        $usersToday = User::with('workDayToday')->get();

        // Получаем записи workDays за сегодня с привязанным пользователем
        $todayReports = WorkDay::with('user')
            ->whereDate('start_time', $today)
            ->get();

        return Inertia::render('Admin/Reports/Index', compact('usersToday'));
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

                    return [
                        'name' => $user->name,
                        'work_day_today' => $workDay ? [
                            'start_time' => $workDay->start_time,
                            'end_time' => $workDay->end_time,
                            'longitude_start' => $workDay->longitude_start,
                            'latitude_start' => $workDay->latitude_start,
                            'longitude_end' => $workDay->longitude_end,
                            'latitude_end' => $workDay->latitude_end,
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

            if ($workDay) {
                $startTime = $workDay->start_time;
                $endTime = $workDay->end_time;
                $duration = $startTime && $endTime ? $startTime->diff($endTime) : null;

                return [
                    'date' => $date->toDateString(),
                    'name' => $employee->name,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'work_duration' => $duration ? $duration->format('%H:%I:%S') : '-',
                    'latitude_start' => $workDay->latitude_start,
                    'longitude_start' => $workDay->longitude_start,
                    'latitude_end' => $workDay->latitude_end,
                    'longitude_end' => $workDay->longitude_end,
                ];
            }

            // Если рабочий день отсутствует
            return [
                'date' => $date->toDateString(),
                'name' => $employee->name,
                'start_time' => null,
                'end_time' => null,
                'work_duration' => '-',
                'latitude_start' => null,
                'longitude_start' => null,
                'latitude_end' => null,
                'longitude_end' => null,
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
