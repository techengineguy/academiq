<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Institution;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\BelongsToTenant;

class ClassModel extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $table = 'classes';

    protected $fillable = [
        'tenant_id',
        'uuid',
        'institution_id',
        'academic_year_id',
        'name',
        'code',
        'capacity',
        'description',
        'status',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function sections()
    {
        return $this->hasMany(Section::class, 'class_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'class_subjects', 'class_id', 'subject_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function feeStructures()
    {
        return $this->hasMany(FeeStructure::class);
    }

    public function timetables()
    {
        return $this->hasMany(Timetable::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function examSchedules()
    {
        return $this->hasMany(ExamSchedule::class);
    }

    public function admissionInquiries()
    {
        return $this->hasMany(AdmissionInquiry::class);
    }

    public function admissionApplications()
    {
        return $this->hasMany(AdmissionApplication::class);
    }

    public function lessonPlans()
    {
        return $this->hasMany(LessonPlan::class);
    }

    public function promotionsFrom()
    {
        return $this->hasMany(StudentPromotion::class, 'from_class_id');
    }

    public function promotionsTo()
    {
        return $this->hasMany(StudentPromotion::class, 'to_class_id');
    }
}
