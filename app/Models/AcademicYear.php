<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicYear extends Model
{
    use BelongsToTenant, SoftDeletes;

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

    protected static function booted()
    {
        // Automatically handle setting a new current academic year
        static::saving(function ($academicYear) {
            if ($academicYear->is_current) {
                // Remove is_current from all other academic years in the same institution
                static::where('institution_id', $academicYear->institution_id)
                    ->where('id', '!=', $academicYear->id)
                    ->update(['is_current' => false]);
            }
        });
    }

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
        return $this->admissionApplications(AdmissionApplication::class);
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

    /**
     * Scope a query to only include the current academic year.
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * Scope a query to only include active academic years.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
