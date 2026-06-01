<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Subscription extends Model
{
    protected $fillable = [
        'uuid',
        'institution_id',
        'subscription_plan_id',
        'status',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'next_billing_date',
        'amount',
        'billing_cycle',
        'plan_features',
        'grace_period_days',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'trial_ends_at' => 'date',
        'starts_at' => 'date',
        'ends_at' => 'date',
        'next_billing_date' => 'date',
        'amount' => 'decimal:2',
        'plan_features' => 'array',
        'cancelled_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($subscription) {
            if (empty($subscription->uuid)) {
                $subscription->uuid = Str::uuid();
            }
        });
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function isActive()
    {
        return $this->status === 'active' && $this->ends_at->isFuture();
    }

    public function isTrial()
    {
        return $this->status === 'trial' && $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function isExpired()
    {
        return in_array($this->status, ['expired', 'cancelled']) || $this->ends_at->isPast();
    }

    public function isPastDue()
    {
        return $this->status === 'past_due';
    }

    public function isInGracePeriod()
    {
        if (! $this->isPastDue()) {
            return false;
        }

        $gracePeriodEnd = $this->ends_at->addDays($this->grace_period_days);

        return $gracePeriodEnd->isFuture();
    }

    public function hasAccess()
    {
        return $this->isActive() || $this->isTrial() || $this->isInGracePeriod();
    }

    public function daysUntilExpiry()
    {
        if ($this->isTrial() && $this->trial_ends_at) {
            $days = $this->trial_ends_at->diffInDays(now(), false);
        } else {
            $days = $this->ends_at->diffInDays(now(), false);
        }

        // Return absolute value and round up for display
        return abs(ceil($days));
    }

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'trial' => 'Trial',
            'active' => 'Active',
            'past_due' => 'Past Due',
            'cancelled' => 'Cancelled',
            'expired' => 'Expired',
            default => 'Unknown',
        };
    }

    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'trial' => 'blue',
            'active' => 'green',
            'past_due' => 'yellow',
            'cancelled' => 'red',
            'expired' => 'red',
            default => 'gray',
        };
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeTrial($query)
    {
        return $query->where('status', 'trial');
    }

    public function scopeExpired($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'expired')
                ->orWhere('ends_at', '<', now());
        });
    }

    public function cancel($reason = null)
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }

    public function renew($newEndDate = null)
    {
        $endDate = $newEndDate ?: $this->ends_at->addMonth();

        $this->update([
            'status' => 'active',
            'ends_at' => $endDate,
            'next_billing_date' => $endDate,
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ]);
    }
}
