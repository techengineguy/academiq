<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Institution;

class Scholarship extends Model
{
    protected $fillable = [
        'tenant_id',
        'uuid',
        'institution_id',
        'name',
        'description',
        'type',
        'value',
        'eligibility_criteria',
        'valid_from',
        'valid_to',
        'status',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'eligibility_criteria' => 'json',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function studentScholarships()
    {
        return $this->hasMany(StudentScholarship::class);
    }
}
