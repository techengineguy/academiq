<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostelAllocation extends Model
{
    protected $fillable = [
        'tenant_id',
        'uuid',
        'student_id',
        'hostel_room_id',
        'academic_year_id',
        'allocated_date',
        'vacated_date',
        'bed_number',
        'status',
        'remarks',
    ];

    protected $casts = [
        'allocated_date' => 'date',
        'vacated_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function hostelRoom()
    {
        return $this->belongsTo(HostelRoom::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
