<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Institution;
use App\Models\Concerns\BelongsToTenant;

class LeaveType extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'uuid',
        'institution_id',
        'name',
        'max_days',
        'requires_approval',
        'applicable_to',
    ];

    protected $casts = [
        'requires_approval' => 'boolean',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function applications()
    {
        return $this->hasMany(LeaveApplication::class);
    }
}
