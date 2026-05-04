<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Institution;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicYear extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'uuid',
        'institution_id',
        'name',
        'start_date',
        'end_date',
        'is_current',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function classes()
    {
        return $this->hasMany(ClassModel::class);
    }

    public function feeStructures()
    {
        return $this->hasMany(FeeStructure::class);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function timetables()
    {
        return $this->hasMany(Timetable::class);
    }

    public function hostelAllocations()
    {
        return $this->hasMany(HostelAllocation::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function admissionInquiries()
    {
        return $this->hasMany(AdmissionInquiry::class);
    }

    public function admissionApplications()
    {
        return $this->hasMany(AdmissionApplication::class);
    }

    public function studentPromotions()
    {
        return $this->hasMany(StudentPromotion::class);
    }

    public function studentScholarships()
    {
        return $this->hasMany(StudentScholarship::class);
    }

    public function academicCalendars()
    {
        return $this->hasMany(AcademicCalendar::class);
    }
}
