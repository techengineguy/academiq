<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'uuid',
        'institution_id',
        'key',
        'value',
        'group',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
}
