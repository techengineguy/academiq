<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $fillable = [
        'uuid',
        'student_id',
        'type',
        'certificate_number',
        'issue_date',
        'purpose',
        'content',
        'issued_by',
        'file_path',
    ];

    protected $casts = [
        'issue_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
