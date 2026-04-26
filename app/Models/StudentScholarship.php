<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scholarship;

class StudentScholarship extends Model
{
    protected $fillable = [
        'uuid',
        'student_id',
        'scholarship_id',
        'academic_year_id',
        'discount_amount',
        'granted_date',
        'status',
        'remarks',
        'granted_by',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'granted_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function grantedBy()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}
