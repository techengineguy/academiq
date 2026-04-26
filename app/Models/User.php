<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable([
    'uuid',
    'institution_id',
    'username',
    'email',
    'email_verified_at',
    'password',
    'role',
    'first_name',
    'last_name',
    'middle_name',
    'phone',
    'photo',
    'gender',
    'date_of_birth',
    'address',
    'city',
    'state',
    'country',
    'postal_code',
    'is_active',
    'last_login_at',
    'two_factor_secret',
    'two_factor_recovery_codes',
    'two_factor_confirmed_at',
    'remember_token',
])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'last_login_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->first_name . ' ' . $this->last_name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

     // Relations

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function staff()
    {
        return $this->hasOne(Staff::class);
    }

    public function parent()
    {
        return $this->hasOne(StudentParent::class);
    }

    public function attendancesMarked()
    {
        return $this->hasMany(Attendance::class, 'marked_by');
    }

    public function teacherAttendancesMarked()
    {
        return $this->hasMany(TeacherAttendance::class, 'marked_by');
    }

    public function teacherAttendances()
    {
        return $this->hasMany(TeacherAttendance::class, 'teacher_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function leaveApplications()
    {
        return $this->hasMany(LeaveApplication::class);
    }

    public function leaveApplicationsApproved()
    {
        return $this->hasMany(LeaveApplication::class, 'approved_by');
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    public function payrollsProcessed()
    {
        return $this->hasMany(Payroll::class, 'processed_by');
    }

    public function assignmentsCreated()
    {
        return $this->hasMany(Assignment::class, 'teacher_id');
    }

    public function examResultsEntered()
    {
        return $this->hasMany(ExamResult::class, 'entered_by');
    }

    public function certificatesIssued()
    {
        return $this->hasMany(Certificate::class, 'issued_by');
    }

    public function announcementsCreated()
    {
        return $this->hasMany(Announcement::class, 'created_by');
    }

    public function eventsOrganized()
    {
        return $this->hasMany(Event::class, 'organized_by');
    }

    public function eventParticipations()
    {
        return $this->belongsToMany(Event::class, 'event_participants');
    }

    public function complaintsSubmitted()
    {
        return $this->hasMany(Complaint::class, 'submitted_by');
    }

    public function complaintsAssigned()
    {
        return $this->hasMany(Complaint::class, 'assigned_to');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function smsLogs()
    {
        return $this->hasMany(SmsLog::class);
    }

    public function emailLogs()
    {
        return $this->hasMany(EmailLog::class);
    }

    public function idCards()
    {
        return $this->hasMany(IdCard::class);
    }

    public function feePaymentsReceived()
    {
        return $this->hasMany(FeePayment::class, 'received_by');
    }

    public function hostelBuildingsWardenOf()
    {
        return $this->hasMany(HostelBuilding::class, 'warden_id');
    }

    public function hostelVisitorsApproved()
    {
        return $this->hasMany(HostelVisitor::class, 'approved_by');
    }

    public function classesTeaching()
    {
        return $this->hasMany(Section::class, 'class_teacher_id');
    }

    public function timetablesTeaching()
    {
        return $this->hasMany(Timetable::class, 'teacher_id');
    }

    public function admissionInquiriesAssigned()
    {
        return $this->hasMany(AdmissionInquiry::class, 'assigned_to');
    }

    public function admissionApplicationsReviewed()
    {
        return $this->hasMany(AdmissionApplication::class, 'reviewed_by');
    }

    public function studentPromotionsProcessed()
    {
        return $this->hasMany(StudentPromotion::class, 'processed_by');
    }

    public function studentScholarshipsGranted()
    {
        return $this->hasMany(StudentScholarship::class, 'granted_by');
    }

    public function backupsCreated()
    {
        return $this->hasMany(Backup::class, 'created_by');
    }

    public function lessonPlans()
    {
        return $this->hasMany(LessonPlan::class, 'teacher_id');
    }
}
