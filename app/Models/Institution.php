<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Institution extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'code',
        'logo',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'website',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function academicYears()
    {
        return $this->hasMany(AcademicYear::class);
    }

    public function classes()
    {
        return $this->hasMany(ClassModel::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    public function feeTypes()
    {
        return $this->hasMany(FeeType::class);
    }

    public function timeSlots()
    {
        return $this->hasMany(TimeSlot::class);
    }

    public function hostelBuildings()
    {
        return $this->hasMany(HostelBuilding::class);
    }

    public function leaveTypes()
    {
        return $this->hasMany(LeaveType::class);
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function gradeScales()
    {
        return $this->hasMany(GradeScale::class);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    public function admissionInquiries()
    {
        return $this->hasMany(AdmissionInquiry::class);
    }

    public function admissionApplications()
    {
        return $this->hasMany(AdmissionApplication::class);
    }

    // Alias for implicit nested route binding (parameter name: application)
    public function applications()
    {
        return $this->hasMany(AdmissionApplication::class);
    }

    public function documentTemplates()
    {
        return $this->hasMany(DocumentTemplate::class);
    }

    public function scholarships()
    {
        return $this->hasMany(Scholarship::class);
    }

    public function academicCalendars()
    {
        return $this->hasMany(AcademicCalendar::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function currentSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->whereIn('status', ['trial', 'active', 'past_due'])
            ->where(function ($query) {
                $query->where('ends_at', '>=', now())
                    ->orWhere(function ($q) {
                        $q->where('status', 'past_due')
                            ->whereRaw('DATE_ADD(ends_at, INTERVAL grace_period_days DAY) >= ?', [now()]);
                    });
            })
            ->latest();
    }

    public function hasUsedTrial(): bool
    {
        return $this->subscriptions()->whereNotNull('trial_ends_at')->exists();
    }

    public function hasActiveSubscription()
    {
        $subscription = $this->currentSubscription()->first();

        return $subscription && $subscription->hasAccess();
    }

    public function isOnTrial()
    {
        $subscription = $this->currentSubscription()->first();

        return $subscription && $subscription->isTrial();
    }

    public function trialDaysRemaining()
    {
        if (! $this->isOnTrial()) {
            return 0;
        }

        $subscription = $this->currentSubscription()->first();

        if (! $subscription || ! $subscription->trial_ends_at) {
            return 0;
        }

        $daysRemaining = $subscription->trial_ends_at->diffInDays(now(), false);

        // Return absolute value and round up for display
        return abs(ceil($daysRemaining));
    }

    /**
     * Check if the institution's active subscription plan includes a given feature.
     *
     * Feature names map to boolean columns on SubscriptionPlan:
     *   hostel_management, exam_management, assignment_management,
     *   advanced_reports, api_access, custom_branding, priority_support
     */
    public function hasFeature(string $feature): bool
    {
        $subscription = $this->currentSubscription()->with('plan')->first();

        if (! $subscription || ! $subscription->plan) {
            return false;
        }

        $column = 'has_'.$feature;

        return (bool) ($subscription->plan->{$column} ?? false);
    }

    public function hasReachedStudentLimit(): bool
    {
        $subscription = $this->currentSubscription()->with('plan')->first();

        if (! $subscription || ! $subscription->plan || $subscription->plan->isUnlimitedStudents()) {
            return false;
        }

        return Student::query()->where('institution_id', '=', $this->id)->count('*') >= $subscription->plan->max_students;
    }

    public function hasReachedTeacherLimit(): bool
    {
        $subscription = $this->currentSubscription()->with('plan')->first();

        if (! $subscription || ! $subscription->plan || $subscription->plan->isUnlimitedTeachers()) {
            return false;
        }

        return Teacher::query()->where('institution_id', '=', $this->id)->count('*') >= $subscription->plan->max_teachers;
    }

    public function hasReachedStaffLimit(): bool
    {
        $subscription = $this->currentSubscription()->with('plan')->first();

        if (! $subscription || ! $subscription->plan || $subscription->plan->isUnlimitedStaff()) {
            return false;
        }

        return Staff::query()->where('institution_id', '=', $this->id)->count('*') >= $subscription->plan->max_staff;
    }

    public function planLimitFor(string $type): ?int
    {
        $subscription = $this->currentSubscription()->with('plan')->first();

        if (! $subscription || ! $subscription->plan) {
            return null;
        }

        return match ($type) {
            'students' => $subscription->plan->max_students,
            'teachers' => $subscription->plan->max_teachers,
            'staff' => $subscription->plan->max_staff,
            default => null,
        };
    }
}
