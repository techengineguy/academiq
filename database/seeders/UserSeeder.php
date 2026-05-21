<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Institution;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $institutions = Institution::all();

        foreach ($institutions as $institution) {
            // Create roles for this institution
            $roles = $this->createRoles($institution);

            // Create Admin
            $admin = User::create([
                'tenant_id' => $institution->uuid,
                'uuid' => Str::uuid(),
                'institution_id' => $institution->id,
                'username' => 'admin_' . $institution->code,
                'email' => 'admin@' . strtolower(str_replace(' ', '', $institution->name)) . '.edu',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'first_name' => 'Admin',
                'last_name' => $institution->name,
                'is_active' => true,
            ]);
            $this->assignRole($admin, $roles['admin'], $institution);

            // Create Accountant
            $accountant = User::create([
                'tenant_id' => $institution->uuid,
                'uuid' => Str::uuid(),
                'institution_id' => $institution->id,
                'username' => 'accountant_' . $institution->code,
                'email' => 'accountant@' . strtolower(str_replace(' ', '', $institution->name)) . '.edu',
                'password' => Hash::make('password'),
                'role' => 'accountant',
                'first_name' => 'Accountant',
                'last_name' => $institution->name,
                'is_active' => true,
            ]);
            $this->assignRole($accountant, $roles['accountant'], $institution);

            // Create Staff
            $staff = User::create([
                'tenant_id' => $institution->uuid,
                'uuid' => Str::uuid(),
                'institution_id' => $institution->id,
                'username' => 'staff_' . $institution->code,
                'email' => 'staff@' . strtolower(str_replace(' ', '', $institution->name)) . '.edu',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'first_name' => 'Staff',
                'last_name' => $institution->name,
                'is_active' => true,
            ]);
            $this->assignRole($staff, $roles['staff'], $institution);

            // Create Teachers
            for ($i = 1; $i <= 5; $i++) {
                $teacher = User::create([
                    'tenant_id' => $institution->uuid,
                    'uuid' => Str::uuid(),
                    'institution_id' => $institution->id,
                    'username' => 'teacher' . $i . '_' . $institution->code,
                    'email' => 'teacher' . $i . '@' . strtolower(str_replace(' ', '', $institution->name)) . '.edu',
                    'password' => Hash::make('password'),
                    'role' => 'teacher',
                    'first_name' => 'Teacher',
                    'last_name' => 'Number ' . $i,
                    'is_active' => true,
                ]);
                $this->assignRole($teacher, $roles['teacher'], $institution);
            }

            // Create Parents
            for ($i = 1; $i <= 3; $i++) {
                $parent = User::create([
                    'tenant_id' => $institution->uuid,
                    'uuid' => Str::uuid(),
                    'institution_id' => $institution->id,
                    'username' => 'parent' . $i . '_' . $institution->code,
                    'email' => 'parent' . $i . '@' . strtolower(str_replace(' ', '', $institution->name)) . '.edu',
                    'password' => Hash::make('password'),
                    'role' => 'parent',
                    'first_name' => 'Parent',
                    'last_name' => 'Number ' . $i,
                    'is_active' => true,
                ]);
                $this->assignRole($parent, $roles['parent'], $institution);
            }
        }
    }

    private function createRoles(Institution $institution): array
    {
        $allPermissions = Permission::pluck('id')->all();

        $roleDefinitions = [
            'admin' => [
                'name' => 'Administrator',
                'description' => 'Full access to all modules',
                'modules' => null, // all permissions
            ],
            'accountant' => [
                'name' => 'Accountant',
                'description' => 'Manage fees, payments, and payroll',
                'modules' => ['fees', 'payroll', 'reports', 'students'],
            ],
            'teacher' => [
                'name' => 'Teacher',
                'description' => 'Manage classes, attendance, assignments, and exams',
                'modules' => ['attendance', 'assignments', 'exams', 'classes', 'students', 'leave', 'communications'],
            ],
            'staff' => [
                'name' => 'Staff',
                'description' => 'General staff access',
                'modules' => ['attendance', 'leave', 'communications', 'students'],
            ],
            'parent' => [
                'name' => 'Parent',
                'description' => 'View student information, fees, and results',
                'modules' => ['students', 'fees', 'exams', 'attendance', 'communications'],
            ],
        ];

        $roles = [];

        foreach ($roleDefinitions as $key => $definition) {
            $role = Role::firstOrCreate(
                ['slug' => $key, 'tenant_id' => $institution->uuid],
                [
                    'uuid' => Str::uuid(),
                    'tenant_id' => $institution->uuid,
                    'institution_id' => $institution->id,
                    'name' => $definition['name'],
                    'slug' => $key,
                    'description' => $definition['description'],
                ]
            );

            // Assign permissions
            if ($definition['modules'] === null) {
                // Admin gets all permissions
                $permissionIds = $allPermissions;
            } else {
                // Filter permissions by allowed modules
                $permissionIds = Permission::whereIn('module', $definition['modules'])
                    ->pluck('id')
                    ->all();

                // For non-admin roles, only give "View" permissions for some modules
                if (in_array($key, ['parent'])) {
                    $permissionIds = Permission::whereIn('module', $definition['modules'])
                        ->where('slug', 'like', 'view-%')
                        ->pluck('id')
                        ->all();
                }
            }

            $pivotData = collect($permissionIds)->mapWithKeys(fn (int $permId) => [
                $permId => [
                    'tenant_id' => $institution->uuid,
                    'uuid' => Str::uuid(),
                ],
            ])->all();

            $role->permissions()->syncWithoutDetaching($pivotData);

            $roles[$key] = $role;
        }

        return $roles;
    }

    private function assignRole(User $user, Role $role, Institution $institution): void
    {
        $user->roles()->syncWithoutDetaching([
            $role->id => [
                'tenant_id' => $institution->uuid,
                'uuid' => Str::uuid(),
            ],
        ]);
    }
}
