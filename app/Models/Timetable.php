<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TimeSlot;

class Timetable extends Model
{
    protected $fillable = [
        'tenant_id',
        'uuid',
        'class_id',
        'section_id',
        'subject_id',
        'teacher_id',
        'time_slot_id',
        'academic_year_id',
        'day',
        'room',
    ];

    public function class()
    {
        return $this->belongsTo(ClassModel::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
