<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HostelBuilding extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'uuid',
        'institution_id',
        'name',
        'code',
        'type',
        'address',
        'total_floors',
        'warden_id',
        'facilities',
        'status',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function warden()
    {
        return $this->belongsTo(User::class, 'warden_id');
    }

    public function rooms()
    {
        return $this->hasMany(HostelRoom::class);
    }
}
