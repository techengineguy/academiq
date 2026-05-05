<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Institution;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $institutions = Institution::all();

        foreach ($institutions as $institution) {
            $teacherUsers = User::where('tenant_id', $institution->uuid)
                ->where('role', 'teacher')
                ->get();

            foreach ($teacherUsers as $user) {
                Teacher::create([
                    'tenant_id' => $institution->uuid,
                    'institution_id' => $institution->id,
                    'uuid' => Str::uuid(),
                    'user_id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => 'teacher_' . $user->id . '@' . strtolower(str_replace(' ', '', $institution->name)) . '.edu',
                    'employee_id' => 'EMP-TCH-' . strtoupper(Str::random(6)),
                    'joining_date' => Carbon::createFromDate(2015, rand(1, 12), rand(1, 28)),
                    'designation' => 'Teacher',
                    'department' => 'Academic',
                    'qualification' => 'B.Ed',
                    'specialization' => 'General',
                    'employment_type' => 'full-time',
                    'status' => 'active',
                ]);
            }
        }
    }
}
