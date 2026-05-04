<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentTemplate extends Model
{
    protected $fillable = [
        'tenant_id',
        'uuid',
        'institution_id',
        'name',
        'type',
        'content',
        'variables',
        'is_default',
    ];

    protected $casts = [
        'variables' => 'json',
        'is_default' => 'boolean',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
}
