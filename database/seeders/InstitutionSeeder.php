<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Institution;
use Illuminate\Support\Str;

class InstitutionSeeder extends Seeder
{
    public function run(): void
    {
        $institutions = [
            [
                'uuid' => Str::uuid(),
                'name' => 'Oxford International School',
                'code' => 'OIS',
                'logo' => 'ois-logo.png',
                'email' => 'info@oxfordschool.edu',
                'phone' => '+1234567890',
                'address' => '123 Main Street',
                'city' => 'New York',
                'state' => 'NY',
                'country' => 'USA',
                'postal_code' => '10001',
                'website' => 'https://oxfordschool.edu',
                'description' => 'A leading international school established in 2000.',
                'status' => 'active',
            ],
            [
                'uuid' => Str::uuid(),
                'name' => 'Cambridge Academy',
                'code' => 'CA',
                'logo' => 'ca-logo.png',
                'email' => 'contact@cambridgeacademy.edu',
                'phone' => '+9876543210',
                'address' => '456 Oak Avenue',
                'city' => 'Boston',
                'state' => 'MA',
                'country' => 'USA',
                'postal_code' => '02101',
                'website' => 'https://cambridgeacademy.edu',
                'description' => 'A prestigious academy established in 2005.',
                'status' => 'active',
            ],
        ];

        foreach ($institutions as $institution) {
            Institution::create($institution);
        }
    }
}
