<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Institution;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeType extends Model
{
    use SoftDeletes;

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
