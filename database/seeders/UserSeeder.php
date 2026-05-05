<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Institution;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $institutions = Institution::all();

        foreach ($institutions as $institution) {
            // Create Admin
            User::create([
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

            // Create Accountant
            User::create([
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

            // Create Staff
            User::create([
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

            // Create Teachers
            for ($i = 1; $i <= 5; $i++) {
                User::create([
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
            }

            // Create Parents
            for ($i = 1; $i <= 3; $i++) {
                User::create([
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
            }
        }
    }
}
