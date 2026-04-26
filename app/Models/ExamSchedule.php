<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Exam;

class ExamSchedule extends Model
{
    protected $fillable = [
        'uuid',
        'exam_id',
        'class_id',
        'subject_id',
        'exam_date',
        'start_time',
        'end_time',
        'room',
        'total_marks',
        'passing_marks',
        'instructions',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function results()
    {
        return $this->hasMany(ExamResult::class);
    }
}
