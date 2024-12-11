<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class WorkDay extends Model
{
    protected $fillable = [
        'user_id',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pauses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Pause::class);
    }

    /**
     * Рассчитать общее время работы без учёта перерывов
     */
    public function getTotalWorkTimeAttribute(): string
    {
        if (!$this->start_time || !$this->end_time) {
            return '00:00:00'; // Возвращаем "ноль", если данных недостаточно
        }

        // Общая продолжительность работы
        $totalWorkSeconds = $this->end_time->diffInSeconds($this->start_time);

        // Вычитаем время всех пауз
        $totalPauseSeconds = $this->pauses->reduce(function ($carry, $pause) {
            if ($pause->start_time && $pause->end_time) {
                return $carry + $pause->end_time->diffInSeconds($pause->start_time);
            }
            return $carry;
        }, 0);

        // Учитываем только положительное значение
        $totalWorkSeconds = max(0, $totalWorkSeconds - $totalPauseSeconds);

        // Преобразуем в формат H:i:s
        return gmdate('H:i:s', $totalWorkSeconds);
    }

    /**
     * Рассчитать общее время перерывов
     */
    public function getTotalPauseTimeAttribute(): string
    {
        // Вычисляем общую длительность всех пауз
        $totalPauseSeconds = $this->pauses->reduce(function ($carry, $pause) {
            if ($pause->start_time && $pause->end_time) {
                Log::info("Добавляем паузу: Start: {$pause->start_time}, End: {$pause->end_time}, DiffInSeconds: {$pause->start_time->diffInSeconds($pause->end_time)} секунд");
                $duration = $pause->start_time->diffInSeconds($pause->end_time);

                // Логируем каждую паузу
                Log::info("Добавляем паузу: Start: {$pause->start_time}, End: {$pause->end_time}, Duration: {$duration} секунд");

                return $carry + $duration;
            }

            // Если одна из временных меток отсутствует
            return $carry;
        }, 0);

        // Логируем итоговое время всех пауз
        Log::info("Итоговое время перерывов: {$totalPauseSeconds} секунд");

        // Преобразуем общее время из секунд в формат H:i:s
        return gmdate('H:i:s', $totalPauseSeconds);
    }


}
