<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ClassModel;

class ClassSubject extends Model
{
    protected $fillable = [
        'uuid',
        'class_id',
        'subject_id',
        'teacher_id',
        'periods_per_week',
        'is_compulsory',
    ];

    protected $casts = [
        'is_compulsory' => 'boolean',
    ];

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
