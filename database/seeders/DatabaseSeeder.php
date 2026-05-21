<?php

namespace Database\Seeders;

use App\Models\User;
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
