<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AcademicYear;
use App\Models\Institution;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        $institutions = Institution::all();

        foreach ($institutions as $institution) {
            $academicYears = [
                [
                    'tenant_id' => $institution->uuid,
                    'institution_id' => $institution->id,
                    'uuid' => Str::uuid(),
                    'name' => '2024-2025',
                    'start_date' => Carbon::parse('2024-04-01'),
                    'end_date' => Carbon::parse('2025-03-31'),
                    'is_current' => true,
                    'status' => 'active',
                ],
                [
                    'tenant_id' => $institution->uuid,
                    'institution_id' => $institution->id,
                    'uuid' => Str::uuid(),
                    'name' => '2025-2026',
                    'start_date' => Carbon::parse('2025-04-01'),
                    'end_date' => Carbon::parse('2026-03-31'),
                    'is_current' => false,
                    'status' => 'active',
                ],
            ];

            foreach ($academicYears as $year) {
                AcademicYear::create($year);
            }
        }
    }
}
