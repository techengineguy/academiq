<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'students' => ['View Students', 'Create Students', 'Edit Students', 'Delete Students'],
            'teachers' => ['View Teachers', 'Create Teachers', 'Edit Teachers', 'Delete Teachers'],
            'staff' => ['View Staff', 'Create Staff', 'Edit Staff', 'Delete Staff'],
            'classes' => ['View Classes', 'Create Classes', 'Edit Classes', 'Delete Classes'],
            'attendance' => ['View Attendance', 'Mark Attendance', 'Edit Attendance', 'Delete Attendance'],
            'exams' => ['View Exams', 'Create Exams', 'Edit Exams', 'Delete Exams', 'Publish Results'],
            'fees' => ['View Fees', 'Create Invoices', 'Record Payments', 'Edit Invoices', 'Delete Invoices'],
            'assignments' => ['View Assignments', 'Create Assignments', 'Edit Assignments', 'Grade Submissions'],
            'leave' => ['View Leave', 'Apply Leave', 'Approve Leave', 'Reject Leave'],
            'hostel' => ['View Hostel', 'Manage Allocations', 'Manage Rooms', 'Manage Visitors'],
            'communications' => ['View Announcements', 'Create Announcements', 'Send Messages', 'Manage Events'],
            'documents' => ['View Documents', 'Generate Certificates', 'Generate ID Cards'],
            'payroll' => ['View Payroll', 'Create Payroll', 'Edit Payroll'],
            'reports' => ['View Reports', 'Export Reports'],
            'settings' => ['Manage Roles', 'Manage Permissions', 'Manage Settings', 'View Activity Logs'],
        ];

        foreach ($modules as $module => $permissions) {
            foreach ($permissions as $permissionName) {
                Permission::updateOrCreate(
                    ['slug' => Str::slug($permissionName)],
                    [
                        'uuid' => Str::uuid(),
                        'tenant_id' => null,
                        'name' => $permissionName,
                        'module' => $module,
                        'description' => null,
                    ]
                );
            }
        }

        $this->command->info('Seeded ' . collect($modules)->flatten()->count() . ' permissions across ' . count($modules) . ' modules.');
    }
}
