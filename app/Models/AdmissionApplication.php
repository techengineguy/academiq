<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdmissionApplication extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'uuid',
        'institution_id',
        'academic_year_id',
        'class_id',
        'application_number',
        'application_date',
        'student_name',
        'date_of_birth',
        'gender',
        'father_name',
        'mother_name',
        'parent_phone',
        'parent_email',
        'address',
        'previous_school',
        'birth_certificate',
        'previous_marksheet',
        'transfer_certificate',
        'student_photo',
        'test_date',
        'test_marks',
        'interview_date',
        'interview_remarks',
        'status',
        'reviewed_by',
        'rejection_reason',
    ];

    protected $casts = [
        'application_date' => 'date',
        'date_of_birth' => 'date',
        'test_marks' => 'decimal:2',
        'test_date' => 'date',
        'interview_date' => 'date',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class);
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
