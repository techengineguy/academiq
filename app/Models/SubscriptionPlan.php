<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'description',
        'price',
        'billing_cycle',
        'features',
        'max_students',
        'max_teachers',
        'max_staff',
        'has_hostel_management',
        'has_accountant_management',
        'has_exam_management',
        'has_assignment_management',
        'has_advanced_reports',
        'has_api_access',
        'has_custom_branding',
        'has_priority_support',
        'is_active',
        'sort_order',
        'paystack_monthly_plan_code',
        'paystack_yearly_plan_code',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'has_hostel_management' => 'boolean',
        'has_accountant_management' => 'boolean',
        'has_exam_management' => 'boolean',
        'has_assignment_management' => 'boolean',
        'has_advanced_reports' => 'boolean',
        'has_api_access' => 'boolean',
        'has_custom_branding' => 'boolean',
        'has_priority_support' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($plan) {
            if (empty($plan->uuid)) {
                $plan->uuid = Str::uuid();
            }
            if (empty($plan->slug)) {
                $plan->slug = Str::slug($plan->name);
            }
        });
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function getFormattedPriceAttribute()
    {
        return '₦'.number_format($this->price, 2);
    }

    public function paystackPlanCode(string $cycle): ?string
    {
        return $cycle === 'yearly'
            ? $this->paystack_yearly_plan_code
            : $this->paystack_monthly_plan_code;
    }

    public function priceForCycle(string $cycle): float
    {
        if ($cycle === 'yearly') {
            // 20% discount for yearly billing (monthly × 12 × 0.8)
            return round((float) $this->price * 12 * 0.8, 2);
        }

        return (float) $this->price;
    }

    public function formattedPriceForCycle(string $cycle): string
    {
        return '₦'.number_format($this->priceForCycle($cycle), 2);
    }

    public function getBillingCycleLabelAttribute()
    {
        return $this->billing_cycle === 'monthly' ? 'per month' : 'per year';
    }

    public function isUnlimitedStudents()
    {
        return is_null($this->max_students);
    }

    public function isUnlimitedTeachers()
    {
        return is_null($this->max_teachers);
    }

    public function isUnlimitedStaff()
    {
        return is_null($this->max_staff);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }
}
