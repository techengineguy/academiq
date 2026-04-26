<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TeacherAttendance;
use App\Models\ClassSubject;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'institution_id',
        'employee_id',
        'joining_date',
        'designation',
        'department',
        'qualification',
        'specialization',
        'salary',
        'employment_type',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
        'status',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'salary' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function teacherAttendances()
    {
        return $this->hasMany(TeacherAttendance::class);
    }

    public function classSubjects()
    {
        return $this->hasMany(ClassSubject::class, 'teacher_id');
    }

    public function timetables()
    {
        return $this->hasMany(Timetable::class, 'teacher_id');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'teacher_id');
    }

    public function lessonPlans()
    {
        return $this->hasMany(LessonPlan::class, 'teacher_id');
    }

    public function sections()
    {
        return $this->hasMany(Section::class, 'class_teacher_id');
    }
}
