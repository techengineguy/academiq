<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Payroll;

class PayrollDeduction extends Model
{
    protected $fillable = [
        'uuid',
        'payroll_id',
        'type',
        'amount',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }
}
