<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ClassModel;
use App\Models\AcademicYear;
use App\Models\Institution;
use Illuminate\Support\Str;

class ClassSeeder extends Seeder
{
    public function run(): void
    {
        $institutions = Institution::all();
        $classLevels = ['6th Grade', '7th Grade', '8th Grade', '9th Grade', '10th Grade', '11th Grade', '12th Grade'];

        foreach ($institutions as $institution) {
            $academicYears = AcademicYear::where('tenant_id', $institution->uuid)->get();

            foreach ($academicYears as $year) {
                foreach ($classLevels as $index => $level) {
                    ClassModel::create([
                        'tenant_id' => $institution->uuid,
                        'institution_id' => $institution->id,
                        'uuid' => Str::uuid(),
                        'academic_year_id' => $year->id,
                        'name' => $level,
                        'code' => 'C' . ($index + 6),
                        'capacity' => 45,
                        'status' => 'active',
                    ]);
                }
            }
        }
    }
}
