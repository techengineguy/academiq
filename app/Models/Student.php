<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'uuid',
        'user_id',
        'institution_id',
        'class_id',
        'section_id',
        'academic_year_id',
        'admission_number',
        'admission_date',
        'roll_number',
        'blood_group',
        'nationality',
        'religion',
        'category',
        'previous_school',
        'birth_certificate',
        'transfer_certificate',
        'medical_conditions',
        'allergies',
        'house',
        'status',
    ];

    protected $casts = [
        'admission_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function parents()
    {
        return $this->belongsToMany(StudentParent::class, 'student_parents');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function feeInvoices()
    {
        return $this->hasMany(FeeInvoice::class);
    }

    public function feePayments()
    {
        return $this->hasMany(FeePayment::class);
    }

    public function assignmentSubmissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function examResults()
    {
        return $this->hasMany(ExamResult::class);
    }

    public function hostelAllocations()
    {
        return $this->hasMany(HostelAllocation::class);
    }

    public function hostelVisitors()
    {
        return $this->hasMany(HostelVisitor::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
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
}
