<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Institution;

class Role extends Model
{
    protected $fillable = [
        'uuid',
        'institution_id',
        'name',
        'slug',
        'description',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }
}
