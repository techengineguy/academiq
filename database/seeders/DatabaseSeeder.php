<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed in dependency order
        $this->call([
            OwnerUserSeeder::class,
            InstitutionSeeder::class,
            AcademicYearSeeder::class,
            PermissionSeeder::class,
            UserSeeder::class,
            SubjectSeeder::class,
            ClassSeeder::class,
            SectionSeeder::class,
            TeacherSeeder::class,
            StaffSeeder::class,
            StudentSeeder::class,
        ]);
    }
}
