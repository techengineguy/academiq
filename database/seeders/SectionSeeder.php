<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Section;
use App\Models\ClassModel;
use App\Models\Institution;
use Illuminate\Support\Str;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $institutions = Institution::all();
        $sectionLetters = ['A', 'B', 'C', 'D'];

        foreach ($institutions as $institution) {
            $classes = ClassModel::where('tenant_id', $institution->uuid)->get();

            foreach ($classes as $class) {
                foreach ($sectionLetters as $letter) {
                    Section::create([
                        'tenant_id' => $institution->uuid,
                        'uuid' => Str::uuid(),
                        'class_id' => $class->id,
                        'name' => $letter,
                        'capacity' => 35,
                        'status' => 'active',
                    ]);
                }
            }
        }
    }
}
