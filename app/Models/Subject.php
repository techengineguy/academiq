<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ClassSubject;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\BelongsToTenant;

class Subject extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'uuid',
        'institution_id',
        'name',
        'code',
        'type',
        'description',
        'status',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function classes()
    {
        return $this->belongsToMany(ClassModel::class, 'class_subjects', 'subject_id', 'class_id');
    }

    public function classSubjects()
    {
        return $this->hasMany(ClassSubject::class);
    }

    public function timetables()
    {
        return $this->hasMany(Timetable::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function examSchedules()
    {
        return $this->hasMany(ExamSchedule::class);
    }

    public function lessonPlans()
    {
        return $this->hasMany(LessonPlan::class);
    }
}
