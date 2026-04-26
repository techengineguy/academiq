<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\FeeInvoice;

class FeePayment extends Model
{
    protected $fillable = [
        'uuid',
        'fee_invoice_id',
        'student_id',
        'receipt_number',
        'amount',
        'payment_date',
        'payment_method',
        'transaction_id',
        'reference_number',
        'received_by',
        'remarks',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function feeInvoice()
    {
        return $this->belongsTo(FeeInvoice::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
