<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CloseAllWorkday extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workday:close-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Закрытие всех незавершенных рабочих дней и пауз';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        // Получаем пользователей с их рабочими днями и паузами
        $usersToday = User::with(['workDayToday.pauses'])->get();

        foreach ($usersToday as $user) {
            if ($user->workDayToday && $user->workDayToday->end_time === null) {
                $workDay = $user->workDayToday;
                $pauses = $workDay->pauses;

                // Закрываем незавершенные паузы
                foreach ($pauses as $pause) {
                    if ($pause->end_time === null) {
                        $pause->end_time = Carbon::now();
                        $pause->save();
                        $this->info('PAUSE_END: ' . $pause);
                    }
                }

                // Закрываем рабочий день
                $workDay->end_time = Carbon::now();
                $workDay->save();
                $this->info('WORK_DAY_END: ' . $workDay);
            }
        }

        $this->info('Все незавершенные рабочие дни и паузы закрыты.');
    }
}
