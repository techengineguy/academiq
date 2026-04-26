<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdmissionInquiry extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'institution_id',
        'academic_year_id',
        'inquiry_number',
        'student_name',
        'date_of_birth',
        'gender',
        'class_id',
        'parent_name',
        'parent_phone',
        'parent_email',
        'address',
        'previous_school',
        'inquiry_date',
        'status',
        'assigned_to',
        'remarks',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'inquiry_date' => 'date',
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

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
