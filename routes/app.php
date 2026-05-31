<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentController;
use Laravel\Fortify\Features;
use Laravel\Fortify\Http\Controllers\ConfirmablePasswordController;
use Laravel\Fortify\Http\Controllers\ConfirmedPasswordStatusController;
use Laravel\Fortify\Http\Controllers\ConfirmedTwoFactorAuthenticationController;
use Laravel\Fortify\Http\Controllers\PasswordController;
use Laravel\Fortify\Http\Controllers\ProfileInformationController;
use Laravel\Fortify\Http\Controllers\RecoveryCodeController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController;
use Laravel\Fortify\Http\Controllers\TwoFactorQrCodeController;
use Laravel\Fortify\Http\Controllers\TwoFactorSecretKeyController;

// App Domain Routes - Protected pages (require authentication)
Route::domain(config('domain.app'))->middleware(['auth', 'verified', 'web', 'redirect.users'])->group(function () {
    Route::livewire('dashboard', 'pages::app.dashboard.index')->name('dashboard');

    // Profile Information
    if (Features::enabled(Features::updateProfileInformation())) {
        Route::put('/user/profile-information', [ProfileInformationController::class, 'update'])
            ->name('user-profile-information.update');
    }

    // Passwords
    if (Features::enabled(Features::updatePasswords())) {
        Route::put('/user/password', [PasswordController::class, 'update'])
            ->name('user-password.update');
    }

    // Password Confirmation
    Route::get('/user/confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::get('/user/confirmed-password-status', [ConfirmedPasswordStatusController::class, 'show'])
        ->name('password.confirmation');

    Route::post('/user/confirm-password', [ConfirmablePasswordController::class, 'store'])
        ->name('password.confirm.store');

    // Two Factor Authentication
    if (Features::enabled(Features::twoFactorAuthentication())) {
        $twoFactorMiddleware = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')
            ? ['password.confirm']
            : [];

        Route::post('/user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'store'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.enable');

        Route::post('/user/confirmed-two-factor-authentication', [ConfirmedTwoFactorAuthenticationController::class, 'store'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.confirm');

        Route::delete('/user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'destroy'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.disable');

        Route::get('/user/two-factor-qr-code', [TwoFactorQrCodeController::class, 'show'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.qr-code');

        Route::get('/user/two-factor-secret-key', [TwoFactorSecretKeyController::class, 'show'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.secret-key');

        Route::get('/user/two-factor-recovery-codes', [RecoveryCodeController::class, 'index'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.recovery-codes');

        Route::post('/user/two-factor-recovery-codes', [RecoveryCodeController::class, 'store'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.regenerate-recovery-codes');
    }

    // Academic Management Routes
    Route::livewire('academic-years', 'pages::app.academic.academic-years.index')->name('academic-years.index');
    Route::livewire('academic-years/create', 'pages::app.academic.academic-years.create')->name('academic-years.create');
    Route::livewire('academic-years/{id}/edit', 'pages::app.academic.academic-years.edit')->name('academic-years.edit');

    Route::livewire('classes', 'pages::app.academic.classes.index')->name('classes.index');
    Route::livewire('classes/create', 'pages::app.academic.classes.create')->name('classes.create');
    Route::livewire('classes/{id}/edit', 'pages::app.academic.classes.edit')->name('classes.edit');

    Route::livewire('sections', 'pages::app.academic.sections.index')->name('sections.index');
    Route::livewire('sections/create', 'pages::app.academic.sections.create')->name('sections.create');
    Route::livewire('sections/{id}/edit', 'pages::app.academic.sections.edit')->name('sections.edit');

    Route::livewire('subjects', 'pages::app.academic.subjects.index')->name('subjects.index');
    Route::livewire('subjects/create', 'pages::app.academic.subjects.create')->name('subjects.create');
    Route::livewire('subjects/{id}/edit', 'pages::app.academic.subjects.edit')->name('subjects.edit');

    Route::livewire('timetables', 'pages::app.academic.timetables.index')->name('timetables.index');
    Route::livewire('timetables/create', 'pages::app.academic.timetables.create')->name('timetables.create');
    Route::livewire('timetables/{id}/edit', 'pages::app.academic.timetables.edit')->name('timetables.edit');

    Route::livewire('time-slots', 'pages::app.academic.time-slots.index')->name('time-slots.index');
    Route::livewire('time-slots/create', 'pages::app.academic.time-slots.create')->name('time-slots.create');
    Route::livewire('time-slots/{id}/edit', 'pages::app.academic.time-slots.edit')->name('time-slots.edit');

    Route::livewire('lesson-plans', 'pages::app.academic.lesson-plans.index')->name('lesson-plans.index');
    Route::livewire('lesson-plans/create', 'pages::app.academic.lesson-plans.create')->name('lesson-plans.create');
    Route::livewire('lesson-plans/{id}/edit', 'pages::app.academic.lesson-plans.edit')->name('lesson-plans.edit');

    Route::livewire('academic/trash', 'pages::app.academic.trash.index')->name('academic.trash');

    // Student Management Routes
    Route::livewire('students', 'pages::app.students.index')->name('students.index');
    Route::livewire('students/create', 'pages::app.students.create')->name('students.create');
    Route::livewire('students/{id}/edit', 'pages::app.students.edit')->name('students.edit');

    // Parent Management Routes
    Route::livewire('parents', 'pages::app.parents.index')->name('parents.index');
    Route::livewire('parents/create', 'pages::app.parents.create')->name('parents.create');
    Route::livewire('parents/{id}/edit', 'pages::app.parents.edit')->name('parents.edit');

    // Parent Management Routes
    Route::livewire('parents', 'pages::app.parents.index')->name('parents.index');
    Route::livewire('parents/create', 'pages::app.parents.create')->name('parents.create');
    Route::livewire('parents/{id}/edit', 'pages::app.parents.edit')->name('parents.edit');

    Route::livewire('admission-applications', 'pages::app.admissions.index')->name('admission-applications.index');
    Route::livewire('admission-applications/create', 'pages::app.admissions.create')->name('admission-applications.create');
    Route::livewire('admission-applications/{id}/edit', 'pages::app.admissions.edit')->name('admission-applications.edit');

    Route::livewire('scholarships', 'pages::app.scholarships.index')->name('scholarships.index');
    Route::livewire('scholarships/create', 'pages::app.scholarships.create')->name('scholarships.create');
    Route::livewire('scholarships/{id}/edit', 'pages::app.scholarships.edit')->name('scholarships.edit');

    Route::livewire('scholarship-awards', 'pages::app.scholarships.awards.index')->name('scholarship-awards.index');
    Route::livewire('scholarship-awards/create', 'pages::app.scholarships.awards.create')->name('scholarship-awards.create');
    Route::livewire('scholarship-awards/{id}/edit', 'pages::app.scholarships.awards.edit')->name('scholarship-awards.edit');

    Route::livewire('promotions', 'pages::app.promotions.index')->name('promotions.index');
    Route::livewire('promotions/create', 'pages::app.promotions.create')->name('promotions.create');
    Route::livewire('promotions/{id}/edit', 'pages::app.promotions.edit')->name('promotions.edit');

    // Staff Management Routes
    Route::livewire('teachers', 'pages::app.staff.teachers.index')->name('teachers.index');
    Route::livewire('teachers/create', 'pages::app.staff.teachers.create')->name('teachers.create');
    Route::livewire('teachers/{id}/edit', 'pages::app.staff.teachers.edit')->name('teachers.edit');
    Route::livewire('staff/trash', 'pages::app.staff.trash.index')->name('staff.trash');

    Route::livewire('staff', 'pages::app.staff.staff.index')->name('staffs.index');
    Route::livewire('staff/create', 'pages::app.staff.staff.create')->name('staff.create');
    Route::livewire('staff/{id}/edit', 'pages::app.staff.staff.edit')->name('staff.edit');

    Route::livewire('payroll', 'pages::app.staff.payroll.index')->name('payroll.index');
    Route::livewire('payroll/create', 'pages::app.staff.payroll.create')->name('payroll.create');
    Route::livewire('payroll/{id}/edit', 'pages::app.staff.payroll.edit')->name('payroll.edit');

    // Attendance Routes
    Route::livewire('attendance', 'pages::app.attendance.student.index')->name('attendance.index');
    Route::livewire('attendance/create', 'pages::app.attendance.student.create')->name('attendance.create');

    Route::livewire('staff-attendance', 'pages::app.attendance.staff.index')->name('staff-attendance.index');
    Route::livewire('staff-attendance/create', 'pages::app.attendance.staff.create')->name('staff-attendance.create');

    // Exam & Results Routes
    Route::livewire('exams', 'pages::app.exams.index')->name('exams.index');
    Route::livewire('exams/create', 'pages::app.exams.create')->name('exams.create');
    Route::livewire('exams/{id}/edit', 'pages::app.exams.edit')->name('exams.edit');

    Route::livewire('exam-schedules', 'pages::app.exams.schedules.index')->name('exam-schedules.index');
    Route::livewire('exam-schedules/create', 'pages::app.exams.schedules.create')->name('exam-schedules.create');
    Route::livewire('exam-schedules/{id}/edit', 'pages::app.exams.schedules.edit')->name('exam-schedules.edit');

    Route::livewire('results', 'pages::app.exams.results.index')->name('results.index');
    Route::livewire('results/create', 'pages::app.exams.results.create')->name('results.create');
    Route::livewire('results/check', 'pages::app.exams.results.check')->name('results.check');
    Route::livewire('results/{id}/edit', 'pages::app.exams.results.edit')->name('results.edit');

    Route::livewire('grade-scales', 'pages::app.exams.grade-scales.index')->name('grade-scales.index');
    Route::livewire('grade-scales/create', 'pages::app.exams.grade-scales.create')->name('grade-scales.create');
    Route::livewire('grade-scales/{id}/edit', 'pages::app.exams.grade-scales.edit')->name('grade-scales.edit');

    // Fee Management Routes
    Route::livewire('fee-types', 'pages::app.fees.fee-types.index')->name('fee-types.index');
    Route::livewire('fee-types/create', 'pages::app.fees.fee-types.create')->name('fee-types.create');
    Route::livewire('fee-types/{id}/edit', 'pages::app.fees.fee-types.edit')->name('fee-types.edit');

    Route::livewire('fee-structures', 'pages::app.fees.fee-structures.index')->name('fee-structures.index');
    Route::livewire('fee-structures/create', 'pages::app.fees.fee-structures.create')->name('fee-structures.create');
    Route::livewire('fee-structures/{id}/edit', 'pages::app.fees.fee-structures.edit')->name('fee-structures.edit');

    Route::livewire('fee-invoices', 'pages::app.fees.fee-invoices.index')->name('fee-invoices.index');
    Route::livewire('fee-invoices/create', 'pages::app.fees.fee-invoices.create')->name('fee-invoices.create');
    Route::livewire('fee-invoices/{id}/edit', 'pages::app.fees.fee-invoices.edit')->name('fee-invoices.edit');

    Route::livewire('fee-payments', 'pages::app.fees.fee-payments.index')->name('fee-payments.index');
    Route::livewire('fee-payments/create', 'pages::app.fees.fee-payments.create')->name('fee-payments.create');

    // Assignment Routes
    Route::livewire('assignments', 'pages::app.assignments.index')->name('assignments.index');
    Route::livewire('assignments/create', 'pages::app.assignments.create')->name('assignments.create');
    Route::livewire('assignments/{id}/edit', 'pages::app.assignments.edit')->name('assignments.edit');

    Route::livewire('submissions', 'pages::app.assignments.submissions.index')->name('submissions.index');
    Route::livewire('submissions/{id}', 'pages::app.assignments.submissions.show')->name('submissions.show');

    // Leave Management Routes
    Route::livewire('leave-types', 'pages::app.leave.leave-types.index')->name('leave-types.index');
    Route::livewire('leave-types/create', 'pages::app.leave.leave-types.create')->name('leave-types.create');
    Route::livewire('leave-types/{id}/edit', 'pages::app.leave.leave-types.edit')->name('leave-types.edit');

    Route::livewire('leave-applications', 'pages::app.leave.applications.index')->name('leave-applications.index');
    Route::livewire('leave-applications/create', 'pages::app.leave.applications.create')->name('leave-applications.create');
    Route::livewire('leave-applications/{id}/edit', 'pages::app.leave.applications.edit')->name('leave-applications.edit');

    // Hostel Management Routes
    Route::livewire('hostel-buildings', 'pages::app.hostel.buildings.index')->name('hostel-buildings.index');
    Route::livewire('hostel-buildings/create', 'pages::app.hostel.buildings.create')->name('hostel-buildings.create');
    Route::livewire('hostel-buildings/{id}/edit', 'pages::app.hostel.buildings.edit')->name('hostel-buildings.edit');

    Route::livewire('hostel-rooms', 'pages::app.hostel.rooms.index')->name('hostel-rooms.index');
    Route::livewire('hostel-rooms/create', 'pages::app.hostel.rooms.create')->name('hostel-rooms.create');
    Route::livewire('hostel-rooms/{id}/edit', 'pages::app.hostel.rooms.edit')->name('hostel-rooms.edit');

    Route::livewire('hostel-allocations', 'pages::app.hostel.allocations.index')->name('hostel-allocations.index');
    Route::livewire('hostel-allocations/create', 'pages::app.hostel.allocations.create')->name('hostel-allocations.create');
    Route::livewire('hostel-allocations/{id}/edit', 'pages::app.hostel.allocations.edit')->name('hostel-allocations.edit');

    Route::livewire('hostel-visitors', 'pages::app.hostel.visitors.index')->name('hostel-visitors.index');
    Route::livewire('hostel-visitors/create', 'pages::app.hostel.visitors.create')->name('hostel-visitors.create');

    // Communications Routes
    Route::livewire('announcements', 'pages::app.communications.announcements.index')->name('announcements.index');
    Route::livewire('announcements/create', 'pages::app.communications.announcements.create')->name('announcements.create');
    Route::livewire('announcements/{id}/edit', 'pages::app.communications.announcements.edit')->name('announcements.edit');

    Route::livewire('events', 'pages::app.communications.events.index')->name('events.index');
    Route::livewire('events/create', 'pages::app.communications.events.create')->name('events.create');
    Route::livewire('events/{id}/edit', 'pages::app.communications.events.edit')->name('events.edit');

    Route::livewire('messages', 'pages::app.communications.messages.index')->name('messages.index');
    Route::livewire('messages/create', 'pages::app.communications.messages.create')->name('messages.create');
    Route::livewire('messages/{id}', 'pages::app.communications.messages.show')->name('messages.show');

    Route::livewire('notifications', 'pages::app.communications.notifications.index')->name('notifications.index');

    // Documents Routes
    Route::livewire('certificates', 'pages::app.documents.certificates.index')->name('certificates.index');
    Route::livewire('certificates/create', 'pages::app.documents.certificates.create')->name('certificates.create');
    Route::livewire('certificates/{id}/edit', 'pages::app.documents.certificates.edit')->name('certificates.edit');
    Route::get('certificates/{id}/download', [DocumentController::class, 'downloadCertificate'])->name('certificates.download');

    Route::livewire('id-cards', 'pages::app.documents.id-cards.index')->name('id-cards.index');
    Route::livewire('id-cards/create', 'pages::app.documents.id-cards.create')->name('id-cards.create');
    Route::get('id-cards/{id}/download', [DocumentController::class, 'downloadIdCard'])->name('id-cards.download');

    Route::get('results/{studentId}/{examId}/download', [DocumentController::class, 'downloadResultSheet'])->name('results.download');

    Route::livewire('document-templates', 'pages::app.documents.templates.index')->name('document-templates.index');
    Route::livewire('document-templates/create', 'pages::app.documents.templates.create')->name('document-templates.create');
    Route::livewire('document-templates/{id}/edit', 'pages::app.documents.templates.edit')->name('document-templates.edit');

    // More Routes
    Route::livewire('complaints', 'pages::app.complaints.index')->name('complaints.index');
    Route::livewire('complaints/{id}', 'pages::app.complaints.show')->name('complaints.show');

    Route::livewire('backups', 'pages::app.backups.index')->name('backups.index');

    Route::livewire('activity-logs', 'pages::app.activity-logs.index')->name('activity-logs.index');

    // Roles & Permissions Routes
    Route::livewire('roles', 'pages::app.roles.index')->name('roles.index');
    Route::livewire('roles/create', 'pages::app.roles.create')->name('roles.create');
    Route::livewire('roles/{id}/edit', 'pages::app.roles.edit')->name('roles.edit');
});

// Student Portal Routes
Route::domain(config('domain.app'))->middleware(['auth', 'verified', 'web'])->prefix('student')->group(function () {
    Route::livewire('/', 'pages::student.dashboard')->name('student.dashboard');
    Route::livewire('/attendance', 'pages::student.attendance')->name('student.attendance');
    Route::livewire('/results', 'pages::student.results')->name('student.results');
    Route::livewire('/fees', 'pages::student.fees')->name('student.fees');
    Route::livewire('/assignments', 'pages::student.assignments')->name('student.assignments');
    Route::livewire('/timetable', 'pages::student.timetable')->name('student.timetable');
    Route::livewire('/announcements', 'pages::student.announcements')->name('student.announcements');
    Route::livewire('/notifications', 'pages::student.notifications')->name('student.notifications');
    Route::livewire('/documents', 'pages::student.documents')->name('student.documents');
    Route::livewire('/events', 'pages::student.events')->name('student.events');
    Route::livewire('/messages', 'pages::student.messages.index')->name('student.messages');
    Route::livewire('/messages/create', 'pages::student.messages.create')->name('student.messages.create');
    Route::livewire('/messages/{id}', 'pages::student.messages.show')->name('student.messages.show');
});

// Teacher Portal Routes
Route::domain(config('domain.app'))->middleware(['auth', 'verified', 'web'])->prefix('teacher')->group(function () {
    Route::livewire('/', 'pages::teacher.dashboard')->name('teacher.dashboard');
    Route::livewire('/my-classes', 'pages::teacher.my-classes')->name('teacher.my-classes');
    Route::livewire('/attendance', 'pages::teacher.attendance')->name('teacher.attendance');
    Route::livewire('/assignments', 'pages::teacher.assignments')->name('teacher.assignments');
    Route::livewire('/results', 'pages::teacher.results')->name('teacher.results');
    Route::livewire('/lesson-plans', 'pages::teacher.lesson-plans')->name('teacher.lesson-plans');
    Route::livewire('/timetable', 'pages::teacher.timetable')->name('teacher.timetable');
    Route::livewire('/leave', 'pages::teacher.leave')->name('teacher.leave');
    Route::livewire('/announcements', 'pages::teacher.announcements')->name('teacher.announcements');
    Route::livewire('/notifications', 'pages::teacher.notifications')->name('teacher.notifications');
    Route::livewire('/documents', 'pages::teacher.documents')->name('teacher.documents');
    Route::livewire('/events', 'pages::teacher.events')->name('teacher.events');
    Route::livewire('/messages', 'pages::teacher.messages.index')->name('teacher.messages');
    Route::livewire('/messages/create', 'pages::teacher.messages.create')->name('teacher.messages.create');
    Route::livewire('/messages/{id}', 'pages::teacher.messages.show')->name('teacher.messages.show');
});

// Parent Portal Routes
Route::domain(config('domain.app'))->middleware(['auth', 'verified', 'web'])->prefix('parent')->group(function () {
    Route::livewire('/', 'pages::parent.dashboard')->name('parent.dashboard');
    Route::livewire('/children', 'pages::parent.children')->name('parent.children');
    Route::livewire('/attendance', 'pages::parent.attendance')->name('parent.attendance');
    Route::livewire('/results', 'pages::parent.results')->name('parent.results');
    Route::livewire('/assignments', 'pages::parent.assignments')->name('parent.assignments');
    Route::livewire('/timetable', 'pages::parent.timetable')->name('parent.timetable');
    Route::livewire('/fees', 'pages::parent.fees')->name('parent.fees');
    Route::livewire('/leave', 'pages::parent.leave')->name('parent.leave');
    Route::livewire('/messages', 'pages::parent.messages.index')->name('parent.messages');
    Route::livewire('/messages/create', 'pages::parent.messages.create')->name('parent.messages.create');
    Route::livewire('/messages/{id}', 'pages::parent.messages.show')->name('parent.messages.show');
    Route::livewire('/announcements', 'pages::parent.announcements')->name('parent.announcements');
    Route::livewire('/events', 'pages::parent.events')->name('parent.events');
    Route::livewire('/notifications', 'pages::parent.notifications')->name('parent.notifications');
});
