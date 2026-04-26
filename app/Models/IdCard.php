<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdCard extends Model
{
    protected $table = 'id_cards';

    protected $fillable = [
        'uuid',
        'user_id',
        'card_number',
        'type',
        'issue_date',
        'expiry_date',
        'qr_code',
        'barcode',
        'status',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
