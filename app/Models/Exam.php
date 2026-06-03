<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Institution;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use App\Models\Concerns\BelongsToTenant;

class Exam extends Model
{
    use SoftDeletes;
    use LogsActivity;
    use BelongsToTenant;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'type', 'status', 'result_published', 'start_date', 'end_date'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName) => "Exam {$eventName}");
    }

    protected $fillable = [
        'tenant_id',
        'uuid',
        'institution_id',
        'academic_year_id',
        'name',
        'type',
        'start_date',
        'end_date',
        'description',
        'result_published',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'result_published' => 'boolean',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function schedules()
    {
        return $this->hasMany(ExamSchedule::class);
    }
}
