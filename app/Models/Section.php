<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ClassModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Section extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'class_id',
        'name',
        'capacity',
        'class_teacher_id',
        'description',
        'status',
    ];

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function classTeacher()
    {
        return $this->belongsTo(User::class, 'class_teacher_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function timetables()
    {
        return $this->hasMany(Timetable::class);
    }

    public function promotionsFrom()
    {
        return $this->hasMany(StudentPromotion::class, 'from_section_id');
    }

    public function promotionsTo()
    {
        return $this->hasMany(StudentPromotion::class, 'to_section_id');
    }
}
