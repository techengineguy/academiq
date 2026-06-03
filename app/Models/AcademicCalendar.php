<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class AcademicCalendar extends Model
{
    use BelongsToTenant;

    protected $table = 'academic_calendar';

    protected $fillable = [
        'tenant_id',
        'uuid',
        'institution_id',
        'academic_year_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'type',
        'is_holiday',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_holiday' => 'boolean',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
