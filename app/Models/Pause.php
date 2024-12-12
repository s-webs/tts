<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pause extends Model
{
    protected $fillable = [
        'work_day_id',
        'start_time',
        'end_time',
        'latitude_start',
        'longitude_start',
        'latitude_end',
        'longitude_end',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];


    public function workDay(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(WorkDay::class);
    }
}
