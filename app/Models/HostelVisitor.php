<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostelVisitor extends Model
{
    protected $fillable = [
        'uuid',
        'student_id',
        'visitor_name',
        'visitor_phone',
        'relation',
        'check_in_time',
        'check_out_time',
        'purpose',
        'approved_by',
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
