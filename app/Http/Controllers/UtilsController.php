<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class UtilsController extends Controller
{
    public function closeAllWorkday()
    {
        $today = Carbon::today();

        $usersToday = User::with(['workDayToday.pauses'])->get();

        foreach ($usersToday as $user) {
            if ($user->workDayToday && $user->workDayToday->end_time === null) {
                $workDay = $user->workDayToday;
                $pauses = $workDay->pauses;

                foreach ($pauses as $pause) {
                    if ($pause->end_time === null) {
                        $pause->end_time = Carbon::now();
                        $pause->save();
                        dump('PAUSE_END: ' . $pause);
                    }
                }

                $workDay->end_time = Carbon::now();
                $workDay->save();
                dump('WORK_DAY_END: ' . $workDay);
            }
        }
    }
}
