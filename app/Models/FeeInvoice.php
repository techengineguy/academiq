<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Student;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeInvoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'uuid',
        'student_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'total_amount',
        'discount_amount',
        'late_fee',
        'paid_amount',
        'balance',
        'status',
        'remarks',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'late_fee' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function items()
    {
        return $this->hasMany(FeeInvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(FeePayment::class);
    }
}
