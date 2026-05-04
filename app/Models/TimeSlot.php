<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Timetable;

class TimeSlot extends Model
{
    protected $fillable = [
        'tenant_id',
        'uuid',
        'institution_id',
        'name',
        'start_time',
        'end_time',
        'is_break',
        'order',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'is_break' => 'boolean',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function timetables()
    {
        return $this->hasMany(Timetable::class);
    }
}
