<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Assignment;

class AssignmentSubmission extends Model
{
    protected $fillable = [
        'uuid',
        'assignment_id',
        'student_id',
        'content',
        'attachment',
        'submitted_at',
        'marks_obtained',
        'feedback',
        'status',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'marks_obtained' => 'decimal:2',
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
