<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Event;

class EventParticipant extends Model
{
    protected $fillable = [
        'uuid',
        'event_id',
        'user_id',
        'rsvp_status',
        'remarks',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
