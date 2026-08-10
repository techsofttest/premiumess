<?php

namespace App\Models;

use App\Enums\TaskType;
use Illuminate\Database\Eloquent\Model;

class Timesheet extends Model
{
    protected $fillable = [
        'staff_user_id',
        'task',
        'description',
        'date',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'task' => TaskType::class,
    ];

    public function staffUser()
    {
        return $this->belongsTo(User::class, 'staff_user_id');
    }

    public function getDurationInMinutesAttribute()
    {
        if (!$this->end_time) {
            return null;
        }

        return $this->start_time->diffInMinutes($this->end_time);
    }

    public function getDurationFormattedAttribute()
    {
        if (!$this->end_time) {
            return 'Running';
        }

        $diffMinutes = $this->getDurationInMinutesAttribute();

        if ($diffMinutes < 60) {
            $minuteLabel = $diffMinutes === 1 ? 'Minute' : 'Minutes';
            return "{$diffMinutes} {$minuteLabel}";
        }

        $hours = $diffMinutes / 60;
        $roundedHours = round($hours, 1);

        if (fmod($hours, 1) === 0.0) {
            $hourLabel = $hours === 1 ? 'hour' : 'hours';
            return "{$hours} {$hourLabel}";
        }

        $hourLabel = $roundedHours === 1.0 ? 'hour' : 'hours';
        return "{$roundedHours} {$hourLabel}";
    }

    public function getTimeRangeFormattedAttribute()
    {
        $start = $this->start_time->format('g:i A');
        
        if (!$this->end_time) {
            return "{$start} - Running";
        }

        $end = $this->end_time->format('g:i A');
        return "{$start} - {$end}";
    }
}
