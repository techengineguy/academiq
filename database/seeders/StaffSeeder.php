<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Staff;
use App\Models\User;
use App\Models\Institution;
use Illuminate\Support\Str;
use Carbon\Carbon;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $institutions = Institution::all();

        foreach ($institutions as $institution) {
            $staffUsers = User::where('tenant_id', $institution->uuid)
                ->where('role', 'staff')
                ->get();

            foreach ($staffUsers as $user) {
                Staff::create([
                    'tenant_id' => $institution->uuid,
                    'institution_id' => $institution->id,
                    'uuid' => Str::uuid(),
                    'user_id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => 'staff_' . $user->id . '@' . strtolower(str_replace(' ', '', $institution->name)) . '.edu',
                    'employee_id' => 'EMP-STF-' . strtoupper(Str::random(6)),
                    'joining_date' => Carbon::createFromDate(2015, rand(1, 12), rand(1, 28)),
                    'designation' => 'Administrative Staff',
                    'department' => 'Administration',
                    'salary' => rand(25000, 45000),
                    'employment_type' => 'permanent',
                    'emergency_contact_name' => $user->first_name . ' Emergency Contact',
                    'emergency_contact_phone' => '03' . rand(100000000, 999999999),
                    'status' => 'active',
                ]);
            }
        }
    }
}
