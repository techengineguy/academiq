<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Institution;
use App\Models\Role;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $institutions = Institution::all();

        foreach ($institutions as $institution) {
            $sections = Section::whereHas('class', function ($query) use ($institution) {
                $query->where('tenant_id', $institution->uuid);
            })->get();

            $currentAcademicYear = AcademicYear::where('tenant_id', $institution->uuid)
                ->where('is_current', true)
                ->first();

            if (! $currentAcademicYear) {
                $currentAcademicYear = AcademicYear::where('tenant_id', $institution->uuid)->first();
            }

            // Get or create the student role for this institution
            $studentRole = Role::firstOrCreate(
                ['slug' => 'student', 'tenant_id' => $institution->uuid],
                [
                    'uuid' => Str::uuid(),
                    'tenant_id' => $institution->uuid,
                    'institution_id' => $institution->id,
                    'name' => 'Student',
                    'slug' => 'student',
                    'description' => 'View own results, attendance, and fee status',
                ]
            );

            // Assign view-only permissions to student role if not already assigned
            if ($studentRole->permissions()->count() === 0) {
                $viewPermissions = \App\Models\Permission::where('slug', 'like', 'view-%')
                    ->whereIn('module', ['students', 'exams', 'attendance', 'fees', 'assignments'])
                    ->pluck('id')
                    ->all();

                $pivotData = collect($viewPermissions)->mapWithKeys(fn (int $permId) => [
                    $permId => [
                        'tenant_id' => $institution->uuid,
                        'uuid' => Str::uuid(),
                    ],
                ])->all();

                $studentRole->permissions()->syncWithoutDetaching($pivotData);
            }

            $studentCounter = 1;

            foreach ($sections as $section) {
                // Create 2 students per section for faster demo seeding
                for ($i = 1; $i <= 2; $i++) {
                    // Create student user
                    $studentUser = User::create([
                        'tenant_id' => $institution->uuid,
                        'institution_id' => $institution->id,
                        'uuid' => Str::uuid(),
                        'username' => 'student' . $studentCounter . '_' . $institution->code,
                        'email' => 'student' . $studentCounter . '@' . strtolower(str_replace(' ', '', $institution->name)) . '.edu',
                        'password' => Hash::make('password'),
                        'role' => 'student',
                        'first_name' => 'Student' . $studentCounter,
                        'last_name' => 'Number ' . $studentCounter,
                        'phone' => '98' . rand(10000000, 99999999),
                        'gender' => collect(['male', 'female'])->random(),
                        'date_of_birth' => Carbon::createFromDate(2008 + rand(0, 3), rand(1, 12), rand(1, 28)),
                        'address' => 'Address ' . Str::random(10),
                        'city' => 'City Name',
                        'state' => 'State',
                        'country' => 'Country',
                        'postal_code' => rand(100000, 999999),
                        'is_active' => true,
                    ]);

                    // Assign student role
                    $studentUser->roles()->syncWithoutDetaching([
                        $studentRole->id => [
                            'tenant_id' => $institution->uuid,
                            'uuid' => Str::uuid(),
                        ],
                    ]);

                    // Create student record
                    Student::create([
                        'tenant_id' => $institution->uuid,
                        'institution_id' => $institution->id,
                        'uuid' => Str::uuid(),
                        'user_id' => $studentUser->id,
                        'first_name' => 'Student' . $studentCounter,
                        'last_name' => 'Number ' . $studentCounter,
                        'email' => $studentUser->email,
                        'section_id' => $section->id,
                        'class_id' => $section->class_id,
                        'academic_year_id' => $currentAcademicYear->id,
                        'roll_number' => $section->code . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                        'admission_number' => 'ADM-' . Str::random(8),
                        'admission_date' => Carbon::now()->subYears(rand(1, 5)),
                        'status' => 'active',
                    ]);

                    $studentCounter++;
                }
            }
        }
    }
}
