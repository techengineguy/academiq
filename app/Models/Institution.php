<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class Institution extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'code',
        'logo',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'website',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function academicYears()
    {
        return $this->hasMany(AcademicYear::class);
    }

    public function classes()
    {
        return $this->hasMany(ClassModel::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    public function feeTypes()
    {
        return $this->hasMany(FeeType::class);
    }

    public function timeSlots()
    {
        return $this->hasMany(TimeSlot::class);
    }

    public function hostelBuildings()
    {
        return $this->hasMany(HostelBuilding::class);
    }

    public function leaveTypes()
    {
        return $this->hasMany(LeaveType::class);
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function gradeScales()
    {
        return $this->hasMany(GradeScale::class);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    public function admissionInquiries()
    {
        return $this->hasMany(AdmissionInquiry::class);
    }

    public function admissionApplications()
    {
        return $this->hasMany(AdmissionApplication::class);
    }

    // Alias for implicit nested route binding (parameter name: application)
    public function applications()
    {
        return $this->hasMany(AdmissionApplication::class);
    }

    public function documentTemplates()
    {
        return $this->hasMany(DocumentTemplate::class);
    }

    public function scholarships()
    {
        return $this->hasMany(Scholarship::class);
    }

    public function academicCalendars()
    {
        return $this->hasMany(AcademicCalendar::class);
    }
}
