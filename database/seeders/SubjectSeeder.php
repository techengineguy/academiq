<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\Institution;
use Illuminate\Support\Str;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $institutions = Institution::all();
        $subjectData = [
            ['name' => 'English', 'type' => 'theory'],
            ['name' => 'Mathematics', 'type' => 'theory'],
            ['name' => 'Science', 'type' => 'both'],
            ['name' => 'Physics', 'type' => 'both'],
            ['name' => 'Chemistry', 'type' => 'both'],
            ['name' => 'Biology', 'type' => 'both'],
            ['name' => 'History', 'type' => 'theory'],
            ['name' => 'Geography', 'type' => 'theory'],
            ['name' => 'Computer Science', 'type' => 'both'],
            ['name' => 'Physical Education', 'type' => 'practical'],
        ];

        foreach ($institutions as $institutionIndex => $institution) {
            foreach ($subjectData as $index => $subject) {
                // Make code unique per institution by adding institution prefix
                $code = substr($subject['name'], 0, 3) . '-' . $institution->code . '-' . str_pad($index + 1, 2, '0', STR_PAD_LEFT);
                
                Subject::create([
                    'tenant_id' => $institution->uuid,
                    'uuid' => Str::uuid(),
                    'institution_id' => $institution->id,
                    'name' => $subject['name'],
                    'code' => $code,
                    'type' => $subject['type'],
                    'status' => 'active',
                ]);
            }
        }
    }
}
