<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'institution_id',
        'created_by',
        'title',
        'content',
        'attachment',
        'target_audience',
        'target_classes',
        'publish_date',
        'expiry_date',
        'is_urgent',
        'send_notification',
        'status',
    ];

    protected $casts = [
        'target_classes' => 'json',
        'publish_date' => 'date',
        'expiry_date' => 'date',
        'is_urgent' => 'boolean',
        'send_notification' => 'boolean',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
