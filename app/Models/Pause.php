<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pause extends Model
{
    protected $fillable = [
        'work_day_id',
        'start_time',
        'end_time',
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
