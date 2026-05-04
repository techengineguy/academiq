<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $tables = [
            'users',
            'academic_years',
            'roles',
            'subjects',
            'students',
            'teachers',
            'staff',
            'time_slots',
            'timetables',
            'attendances',
            'fee_types',
            'fee_structures',
            'fee_invoices',
            'fee_invoice_items',
            'fee_payments',
            'exams',
            'exam_schedules',
            'exam_results',
            'assignments',
            'assignment_submissions',
            'grade_scales',
            'classes',
            'sections',
            'class_subjects',
            'parents',
            'student_parents',
            'hostel_buildings',
            'hostel_rooms',
            'hostel_allocations',
            'hostel_visitors',
            'leave_types',
            'leave_applications',
            'payrolls',
            'payroll_allowances',
            'payroll_deductions',
            'announcements',
            'events',
            'event_participants',
            'messages',
            'certificates',
            'id_cards',
            'complaints',
            'admission_inquiries',
            'admission_applications',
            'document_templates',
            'settings',
            'academic_calendar',
            'scholarships',
            'student_scholarships',
            'student_promotions',
            'lesson_plans',
            'backups',
            'activity_logs',
            'notifications',
            'sms_logs',
            'email_logs',
            'user_roles',
            'role_permissions',
            'teacher_attendances',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'tenant_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->uuid('tenant_id')->nullable()->after('id');
                    $table->foreign('tenant_id')
                        ->references('uuid')
                        ->on('institutions')
                        ->onDelete('cascade');
                    $table->index('tenant_id');
                });
            }
        }
    }

    public function down()
    {
        $tables = [
            'users',
            'academic_years',
            'roles',
            'subjects',
            'students',
            'teachers',
            'staff',
            'time_slots',
            'timetables',
            'attendances',
            'fee_types',
            'fee_structures',
            'fee_invoices',
            'fee_invoice_items',
            'fee_payments',
            'exams',
            'exam_schedules',
            'exam_results',
            'assignments',
            'assignment_submissions',
            'grade_scales',
            'classes',
            'sections',
            'class_subjects',
            'parents',
            'student_parents',
            'hostel_buildings',
            'hostel_rooms',
            'hostel_allocations',
            'hostel_visitors',
            'leave_types',
            'leave_applications',
            'payrolls',
            'payroll_allowances',
            'payroll_deductions',
            'announcements',
            'events',
            'event_participants',
            'messages',
            'certificates',
            'id_cards',
            'complaints',
            'admission_inquiries',
            'admission_applications',
            'document_templates',
            'settings',
            'academic_calendar',
            'scholarships',
            'student_scholarships',
            'student_promotions',
            'lesson_plans',
            'backups',
            'activity_logs',
            'notifications',
            'sms_logs',
            'email_logs',
            'user_roles',
            'role_permissions',
            'teacher_attendances',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'tenant_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['tenant_id']);
                    $table->dropIndex(['tenant_id']);
                    $table->dropColumn('tenant_id');
                });
            }
        }
    }
};
