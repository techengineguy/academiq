<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\HostelBuilding;
use Illuminate\Database\Eloquent\SoftDeletes;

class HostelRoom extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'uuid',
        'hostel_building_id',
        'room_number',
        'floor',
        'capacity',
        'occupied',
        'room_type',
        'rent_amount',
        'facilities',
        'status',
    ];

    protected $casts = [
        'rent_amount' => 'decimal:2',
    ];

    public function hostelBuilding()
    {
        return $this->belongsTo(HostelBuilding::class);
    }

    public function allocations()
    {
        return $this->hasMany(HostelAllocation::class);
    }
}
