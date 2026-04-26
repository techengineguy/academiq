<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonPlan extends Model
{
    protected $fillable = [
        'uuid',
        'teacher_id',
        'class_id',
        'subject_id',
        'lesson_date',
        'topic',
        'objectives',
        'content',
        'teaching_method',
        'resources',
        'attachment',
        'homework',
        'remarks',
    ];

    protected $casts = [
        'lesson_date' => 'date',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
