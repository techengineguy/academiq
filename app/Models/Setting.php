<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Setting extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
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
