<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkDay;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function reports()
    {
        $today = Carbon::today();

        // Получаем записи workDays за сегодня с привязанным пользователем
        $todayReports = WorkDay::with('user')
            ->whereDate('start_time', $today)
            ->get();

        return Inertia::render('Admin/Reports/Index', compact('todayReports'));
    }

}
