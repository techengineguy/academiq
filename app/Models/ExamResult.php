<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ExamSchedule;

class ExamResult extends Model
{
    protected $fillable = [
        'tenant_id',
        'uuid',
        'exam_schedule_id',
        'student_id',
        'marks_obtained',
        'total_marks',
        'grade',
        'remarks',
        'is_absent',
        'entered_by',
    ];

    protected $casts = [
        'marks_obtained' => 'decimal:2',
        'total_marks' => 'decimal:2',
        'is_absent' => 'boolean',
    ];

    public function examSchedule()
    {
        return $this->belongsTo(ExamSchedule::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function enteredBy()
    {
        return $this->belongsTo(User::class, 'entered_by');
    }
}
