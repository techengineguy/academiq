<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Institution;
use App\Models\Concerns\BelongsToTenant;

class FeeType extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'uuid',
        'institution_id',
        'name',
        'code',
        'description',
        'is_refundable',
        'status',
    ];

    protected $casts = [
        'is_refundable' => 'boolean',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function feeStructures()
    {
        return $this->hasMany(FeeStructure::class);
    }

    public function feeInvoiceItems()
    {
        return $this->hasMany(FeeInvoiceItem::class);
    }
}
